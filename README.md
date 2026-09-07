# 🛡️ ExamFort — Secure Lockdown Examination Platform

ExamFort is an end-to-end, high-integrity AI and lockdown examination management suite designed for academic institutions and competitive testing.

---

## 🌟 Key Architecture & Components

- **Hosted Web Portal (`hostedchrome`)**: Full-featured institutional dashboard, course & exam creation, real-time proctoring telemetry view, question bank manager, candidate management. Powered by Laravel.
- **Lockdown Desktop Client (`client`)**: Secure electron client with hardware-level lockdown, multi-monitor blocking, process isolation, facial biometric verification, audio monitoring, and keystroke intelligence.
- **Backend & Telemetry Server (`server`)**: High-performance Node.js service handling real-time WebSocket telemetry, live biometric snapshots, code execution judge engine, AI essay grading, and violation broadcasts.

---

## 🚀 Quick Setup & Installation

### 1. Prerequisites
- **Node.js**: v18+ 
- **PHP & Composer**: (for Laravel portal)
- **PostgreSQL / Supabase**: Primary database
- **Redis Cloud**: Session & caching layer

### 2. Environment Configuration
Copy `.env.example` files to `.env` in both `hostedchrome/` and `server/` and configure your credentials.

### 3. Launching Platform
You can use the automated launcher:
```bash
Launch-Exam.bat
```
Or run services individually:
- **Server**: `cd server && npm start`
- **Client**: `cd client && npm start`
- **Web Portal**: `cd hostedchrome && php artisan serve`
