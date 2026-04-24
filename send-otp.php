<?php
require_once __DIR__ . '/config.php';
start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'error' => 'Method not allowed'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);
$email = strtolower(trim($input['email'] ?? ''));
$mode  = in_array($input['mode'] ?? '', ['signin', 'signup']) ? $input['mode'] : 'signin';
$ip    = get_client_ip();
$ua    = get_user_agent();

if (!is_valid_email($email)) {
    json_response(['success' => false, 'error' => 'Alamat email tidak valid.'], 400);
}

$pdo = db();

// Rate limiting (DB-backed)
$stmt = $pdo->prepare("SELECT count, UNIX_TIMESTAMP(window_start) as ws FROM rate_limits WHERE identifier = ? AND action = 'send_otp' LIMIT 1");
$stmt->execute([$email]);
$rate = $stmt->fetch();
$now = time();

if ($rate) {
    if ($now - $rate['ws'] > OTP_RATE_WINDOW) {
        $pdo->prepare("UPDATE rate_limits SET count = 0, window_start = NOW() WHERE identifier = ? AND action = 'send_otp'")->execute([$email]);
        $rate['count'] = 0;
    }
    if ($rate['count'] >= OTP_RATE_LIMIT) {
        $retry = OTP_RATE_WINDOW - ($now - $rate['ws']);
        json_response(['success' => false, 'error' => 'Terlalu banyak permintaan. Coba lagi dalam ' . ceil($retry / 60) . ' menit.'], 429);
    }
}

// Generate OTP
$otp = '';
for ($i = 0; $i < OTP_LENGTH; $i++) $otp .= random_int(0, 9);
$otp_hash = password_hash($otp, PASSWORD_DEFAULT);
$expires_at = date('Y-m-d H:i:s', $now + OTP_EXPIRY_SECONDS);

// Log to DB
$stmt = $pdo->prepare("INSERT INTO otp_logs (email, code_hash, mode, status, ip_address, user_agent, expires_at) VALUES (?, ?, ?, 'pending', ?, ?, ?)");
$stmt->execute([$email, $otp_hash, $mode, $ip, $ua, $expires_at]);
$log_id = $pdo->lastInsertId();

$_SESSION['otp_pending'] = ['log_id' => $log_id, 'email' => $email, 'mode' => $mode, 'expires_at' => $now + OTP_EXPIRY_SECONDS];

// Build email
$action_word = ($mode === 'signup') ? 'pendaftaran' : 'masuk';
$subject = "Kode OTP IOTECHS: $otp";
$html_body = '<!DOCTYPE html><html><head><meta charset="UTF-8"><style>body{margin:0;padding:0;background:#09090b;font-family:-apple-system,sans-serif}.c{max-width:480px;margin:40px auto;background:#18181b;border-radius:20px;padding:40px;border:1px solid rgba(255,255,255,.08)}.b{font-size:14px;font-weight:700;letter-spacing:2px;color:#14f195;text-transform:uppercase;margin-bottom:32px}.t{font-size:28px;font-weight:500;color:#fafafa;margin:0 0 16px}.p{font-size:15px;line-height:1.6;color:#a1a1aa;margin:0 0 24px}.o{background:#0a0a0a;border:1px solid rgba(20,241,149,.3);border-radius:12px;padding:24px;text-align:center;margin:24px 0}.code{font-size:40px;font-weight:600;letter-spacing:12px;color:#14f195;font-family:Consolas,monospace}.m{font-size:12px;color:#71717a;margin-top:32px;padding-top:24px;border-top:1px solid rgba(255,255,255,.06)}.f{text-align:center;font-size:11px;color:#52525b;margin-top:24px}</style></head><body><div class="c"><div class="b">&#9635; IOTECHS</div><h1 class="t">Kode '.$action_word.' kamu</h1><p class="p">Masukkan kode 6-digit ini untuk '.$action_word.' ke dashboard IOTECHS. Kode berlaku 10 menit.</p><div class="o"><div class="code">'.$otp.'</div></div><p class="p" style="font-size:13px">Kalau kamu tidak meminta kode ini, abaikan saja.</p><div class="m">Email: '.htmlspecialchars($email).'<br>IP: '.htmlspecialchars($ip).'<br>Waktu: '.date('d M Y, H:i').'</div></div><div class="f">IOTECHS — The Internet of Thinking, built on Solana.</div></body></html>';

$headers = implode("\r\n", [
    'MIME-Version: 1.0',
    'Content-Type: text/html; charset=UTF-8',
    'From: ' . SMTP_FROM_NAME . ' <' . SMTP_USERNAME . '>',
    'Reply-To: ' . SMTP_USERNAME,
]);

$sent = @mail($email, $subject, $html_body, $headers);

if ($sent) {
    $pdo->prepare("INSERT INTO rate_limits (identifier, action, count, window_start) VALUES (?, 'send_otp', 1, NOW()) ON DUPLICATE KEY UPDATE count = count + 1, last_request = NOW()")->execute([$email]);
    json_response(['success' => true, 'message' => 'Kode OTP telah dikirim ke ' . $email, 'expires_in' => OTP_EXPIRY_SECONDS]);
} else {
    $pdo->prepare("UPDATE otp_logs SET status = 'failed' WHERE id = ?")->execute([$log_id]);
    unset($_SESSION['otp_pending']);
    json_response(['success' => false, 'error' => 'Gagal mengirim email. Pastikan alamat email valid.'], 500);
}
