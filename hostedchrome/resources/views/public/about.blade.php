@extends('layouts.public')

@section('title', 'About ExamFort - Institutional Proctored Assessment Technology')

@section('content')
<section style="padding: 70px 24px 40px; text-align: center;">
    <div style="max-width: 860px; margin: 0 auto;">
        <span style="font-size: 12px; font-weight: 800; color: #818cf8; letter-spacing: 1px; text-transform: uppercase;">MISSION & SECURITY WHITE-PAPER</span>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(32px, 4vw, 48px); font-weight: 900; color: #fff; margin-top: 10px; margin-bottom: 16px;">
            The Future of Credible, Scalable Academic Assessments
        </h1>
        <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.6;">
            ExamFort was engineered from the ground up to solve the most demanding online examination challenges for universities, technical colleges, and national certification authorities.
        </p>
    </div>
</section>

<!-- Mission & Pillars -->
<section style="max-width: 1200px; margin: 0 auto 80px; padding: 0 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <div class="glass-panel">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="shield" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">Zero Compromise Integrity</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                We believe online examinations must command equal credibility to physical examination halls. Multi-modal telemetry, background lockdown, and live violation analysis ensure genuine assessments.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="network" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">Institutional Federation</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Every university and college operates autonomously. Our 4-tier governance (Super Admin, Principal, Teacher, Student) provides granular quota management and institutional data privacy.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="cpu" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">Real-Time AI & Sandbox</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                From dynamic Rubric evaluation of descriptive essays to compiling complex multi-language code with hidden test cases, ExamFort automates tedious assessment workflows.
            </p>
        </div>
    </div>
</section>

<!-- Compliance & Security Architecture -->
<section style="max-width: 1000px; margin: 0 auto 100px; padding: 0 24px;">
    <div class="glass-panel" style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9));">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 20px;">
            Security & Compliance Standards
        </h2>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 24px;">
            <div style="background: rgba(0,0,0,0.3); padding: 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <i data-lucide="lock" style="color: #34d399; width: 18px; height: 18px;"></i>
                    <strong style="color: #fff; font-size: 15px;">256-Bit Data Encryption</strong>
                </div>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                    All proctoring video telemetry, audio streams, and candidate draft responses are transmitted via TLS 1.3 encryption.
                </p>
            </div>

            <div style="background: rgba(0,0,0,0.3); padding: 18px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <i data-lucide="database" style="color: #818cf8; width: 18px; height: 18px;"></i>
                    <strong style="color: #fff; font-size: 15px;">Pure MySQL Isolation</strong>
                </div>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.5;">
                    Zero mock data. 100% normalized relational architecture guaranteeing ACID transaction compliance for student scores.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
