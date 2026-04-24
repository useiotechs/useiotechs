<?php
require_once __DIR__ . '/config.php';

define('ADMIN_KEY', 'iotechs_admin_2026_secret');

$key = $_GET['key'] ?? '';
if (!hash_equals(ADMIN_KEY, $key)) {
    http_response_code(403);
    die('Access denied');
}

$pdo = db();
$total    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$verified = $pdo->query("SELECT COUNT(*) FROM users WHERE email_verified = 1")->fetchColumn();
$today    = $pdo->query("SELECT COUNT(*) FROM users WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$week     = $pdo->query("SELECT COUNT(*) FROM users WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$otp_sent = $pdo->query("SELECT COUNT(*) FROM otp_logs WHERE DATE(created_at) = CURDATE()")->fetchColumn();
$otp_ok   = $pdo->query("SELECT COUNT(*) FROM otp_logs WHERE DATE(created_at) = CURDATE() AND status = 'verified'")->fetchColumn();
$sessions = $pdo->query("SELECT COUNT(*) FROM login_sessions WHERE logged_out_at IS NULL AND last_activity >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
$conv     = $otp_sent > 0 ? round(($otp_ok / $otp_sent) * 100, 1) : 0;

$recent = $pdo->query("SELECT email, signup_mode, login_count, created_at, last_login_at FROM users ORDER BY created_at DESC LIMIT 20")->fetchAll();
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><title>IOTECHS Admin</title>
<style>body{background:#09090b;color:#fafafa;font-family:-apple-system,sans-serif;padding:40px;max-width:1200px;margin:0 auto}h1{font-size:28px;margin-bottom:32px}.g{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:16px;margin-bottom:40px}.c{background:#18181b;border:1px solid rgba(255,255,255,.08);border-radius:12px;padding:20px}.l{font-size:11px;text-transform:uppercase;letter-spacing:.1em;color:#71717a;margin-bottom:8px}.v{font-size:28px;font-weight:500;color:#14f195}table{width:100%;border-collapse:collapse;background:#18181b;border-radius:12px;overflow:hidden}th,td{padding:12px 16px;text-align:left;border-bottom:1px solid rgba(255,255,255,.05);font-size:13px}th{background:rgba(255,255,255,.04);color:#a1a1aa;text-transform:uppercase;font-size:10px;letter-spacing:.1em}td{color:#d4d4d8}.badge{padding:2px 8px;border-radius:4px;font-size:10px;font-weight:500}.bs{background:rgba(20,241,149,.15);color:#14f195}.bi{background:rgba(59,130,246,.15);color:#3b82f6}</style></head>
<body>
<h1>&#9724; IOTECHS Admin Stats</h1>
<div class="g">
<div class="c"><div class="l">Total Users</div><div class="v"><?=$total?></div></div>
<div class="c"><div class="l">Verified</div><div class="v"><?=$verified?></div></div>
<div class="c"><div class="l">New Today</div><div class="v"><?=$today?></div></div>
<div class="c"><div class="l">This Week</div><div class="v"><?=$week?></div></div>
<div class="c"><div class="l">Active Sessions</div><div class="v"><?=$sessions?></div></div>
<div class="c"><div class="l">OTP Sent Today</div><div class="v"><?=$otp_sent?></div></div>
<div class="c"><div class="l">OTP Verified</div><div class="v"><?=$otp_ok?></div></div>
<div class="c"><div class="l">Conversion</div><div class="v"><?=$conv?>%</div></div>
</div>
<h2 style="font-size:18px;margin-bottom:16px">Recent Users</h2>
<table><thead><tr><th>Email</th><th>Mode</th><th>Logins</th><th>Created</th><th>Last Login</th></tr></thead><tbody>
<?php foreach($recent as $u):?>
<tr><td><?=htmlspecialchars($u['email'])?></td><td><span class="badge <?=$u['signup_mode']==='signup'?'bs':'bi'?>"><?=strtoupper($u['signup_mode'])?></span></td><td><?=$u['login_count']?></td><td><?=date('d M Y, H:i',strtotime($u['created_at']))?></td><td><?=$u['last_login_at']?date('d M Y, H:i',strtotime($u['last_login_at'])):'—'?></td></tr>
<?php endforeach;?>
</tbody></table>
<p style="margin-top:40px;font-size:11px;color:#52525b">Updated: <?=date('d M Y, H:i:s')?></p>
</body></html>
