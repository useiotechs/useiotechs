<p align="center">
  <img src="https://raw.githubusercontent.com/useiotechs/iotechs/main/assets/banner.svg" alt="IOTECHS" width="100%"/>
</p>

<p align="center">
  <strong>DePIN Infrastructure Layer for Solana</strong><br/>
  <sub>Connecting physical devices to on-chain AI intelligence</sub>
</p>

<p align="center">
  <a href="https://iotechs.xyz"><img src="https://img.shields.io/badge/🌐_Website-iotechs.xyz-14f195?style=for-the-badge&labelColor=09090b" alt="Website"/></a>
  <a href="https://iotechs.xyz/launch-app.html"><img src="https://img.shields.io/badge/🚀_Dashboard-Launch_App-9945ff?style=for-the-badge&labelColor=09090b" alt="Dashboard"/></a>
  <a href="#"><img src="https://img.shields.io/badge/📖_Docs-Coming_Soon-3b82f6?style=for-the-badge&labelColor=09090b" alt="Docs"/></a>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Solana-Mainnet-14f195?style=flat-square&logo=data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiI+PGNpcmNsZSBjeD0iOCIgY3k9IjgiIHI9IjYiIGZpbGw9IiMxNGYxOTUiLz48L3N2Zz4=&logoColor=white" alt="Solana"/>
  <img src="https://img.shields.io/badge/Token-$THINK-d946ef?style=flat-square" alt="Token"/>
  <img src="https://img.shields.io/badge/Status-Pre--Launch-f59e0b?style=flat-square" alt="Status"/>
  <img src="https://img.shields.io/badge/License-MIT-a1a1aa?style=flat-square" alt="License"/>
</p>

---

## What is IOTECHS?

IOTECHS is a **DePIN (Decentralized Physical Infrastructure Network) + AI infrastructure layer** built specifically for the Solana ecosystem.

We connect real-world IoT devices — sensors, vehicles, wearables, smart meters, gateways — to Solana, enabling machine-to-machine transactions, decentralized device identity, and real-time AI-powered intelligence.

```
┌─────────────────────────────────────────────────────────────────────┐
│                         IOTECHS ARCHITECTURE                        │
├─────────────────────────────────────────────────────────────────────┤
│                                                                     │
│   Physical World          IOTECHS Layer           Solana            │
│   ─────────────          ─────────────           ──────             │
│                                                                     │
│   🌡️ Sensors    ──→   ┌─────────────┐                              │
│   📍 GPS        ──→   │ IotechsNet  │ ──→  Settlement              │
│   ⚡ Meters     ──→   │  (Indexing)  │                              │
│   ⌚ Wearables  ──→   └──────┬──────┘                              │
│   📡 Gateways   ──→          │                                      │
│   🚁 Drones     ──→   ┌──────▼──────┐       ┌──────────────┐      │
│                        │ IotechsMind │ ──→   │   Solana      │      │
│                        │  (AI Layer) │       │   Programs    │      │
│                        └──────┬──────┘       └──────────────┘      │
│                               │                                     │
│                        ┌──────▼──────┐                              │
│                        │  IotechsID  │ ──→  NFT Identity            │
│                        │ (Identity)  │                              │
│                        └─────────────┘                              │
│                                                                     │
└─────────────────────────────────────────────────────────────────────┘
```

## Core Infrastructure

### IotechsNet — Indexing & Settlement

Real-time indexing of all machine transactions on Solana. Every device ping, every micropayment — sub-second finality.

- 4,200+ TPS for machine transactions
- Sub-second settlement via Solana
- Proof of Signal — cryptographic attestation of physical device origin

### IotechsID — Device Identity

Decentralized identity for every physical device. Privacy-first, composable, on-chain.

- Zero-knowledge proofs for device verification
- On-chain NFT identity (SPL-compatible)
- Reputation scoring based on signal quality + uptime
- Multi-owner support (DAO-owned infrastructure)

### IotechsMind — AI Layer

Transforms raw device signals into actionable intelligence in real-time.

- 24 active AI models across all Realms
- Signal pipeline: `Ingest → Clean → Analyze → Act`
- 97.3% accuracy on anomaly detection
- Triggers on-chain actions via Solana programs

### $THINK — Utility Token

The native SPL token powering the IOTECHS ecosystem.

| Utility | Description |
|---------|-------------|
| **Staking** | Secure the network, validate signals, earn protocol fees |
| **Governance** | Vote on Realms, models, fee parameters, upgrades |
| **Registration** | Burn $THINK to mint IotechsID NFTs |
| **Revenue Share** | Protocol fees distributed to stakers weekly |

> **Launch: April 28, 2026 · 6:00 PM UTC**

## Realms

Six live knowledge networks — each a category of real-time device data:

| Realm | Nodes | Description |
|-------|-------|-------------|
| 🚗 **Mobility** | 2.4M | Connected vehicles, GPS, mapping, fleet management |
| ⚡ **Energy** | 890K | Solar panels, smart meters, EV chargers, grid telemetry |
| ❤️ **Health** | 1.7M | Wearables, medical monitors, fitness sensors (E2E encrypted) |
| 📡 **Connectivity** | 5.1M | LoRaWAN, WiFi hotspots, 5G small cells, mesh networks |
| 🖥️ **Compute** | — | Distributed GPU, edge compute (coming soon) |
| 🌍 **Environment** | — | Weather stations, air quality, soil sensors (coming soon) |

## Quick Start

```bash
# Install SDK
npm install @iotechs/sdk

# Initialize
import { IotechsNet } from '@iotechs/sdk';

const net = new IotechsNet({
  cluster: 'mainnet-beta',
  apiKey: process.env.IOTECHS_KEY
});

# Query live signals
const signals = await net.querySignals({
  realm: 'mobility',
  limit: 100,
  since: '5m'
});

# Register a device
const id = await net.registerDevice({
  type: 'temperature_sensor',
  realm: 'environment',
  owner: wallet.publicKey
});
```

## Repository Structure

```
iotechs/
├── assets/              # Brand assets, banner
├── contracts/           # Solana programs (Anchor)
├── sdk/                 # @iotechs/sdk (TypeScript)
├── ai/                  # IotechsMind models
├── web/                 # Frontend (iotechs.xyz)
│   ├── index.html       # Landing page
│   ├── vision.html      # Vision page
│   ├── login.html       # Auth (OTP email)
│   ├── launch-app.html  # Dashboard
│   └── api/             # PHP backend
└── docs/                # Documentation
```

## Links

- **Website**: [iotechs.xyz](https://iotechs.xyz)
- **Dashboard**: [iotechs.xyz/launch-app.html](https://iotechs.xyz/launch-app.html)
- **Telegram**: [t.me/iotechs](https://t.me/iotechs)
- **X (Twitter)**: [x.com/iotechs](https://x.com/iotechs)

## Contributing

IOTECHS is currently in pre-launch phase. We're building in public.

If you want to contribute:
1. Fork this repo
2. Create a feature branch
3. Submit a PR

For major proposals, open an issue first.

## License

MIT License — see [LICENSE](LICENSE) for details.

---

<p align="center">
  <sub>IOTECHS — The Internet of Thinking · Built on Solana</sub><br/>
  <sub>$THINK launching April 28, 2026 · 6:00 PM UTC</sub>
</p>
