@extends('layouts.public')

@section('title', 'Help & Student Candidate Support - ExamFort')

@section('content')
<section style="padding: 70px 24px 40px; text-align: center;">
    <div style="max-width: 860px; margin: 0 auto;">
        <span style="font-size: 12px; font-weight: 800; color: #fbbf24; letter-spacing: 1px; text-transform: uppercase;">SUPPORT & CANDIDATE GUIDE</span>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(32px, 4vw, 48px); font-weight: 900; color: #fff; margin-top: 10px; margin-bottom: 16px;">
            Help Center & Technical Guidelines
        </h1>
        <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.6;">
            Find quick answers to common assessment questions, troubleshooting hardware issues, and understanding proctoring rules.
        </p>
    </div>
</section>

<!-- System Requirements Checklist -->
<section style="max-width: 1100px; margin: 0 auto 60px; padding: 0 24px;">
    <div class="glass-panel">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 20px;">
            System & Hardware Requirements
        </h2>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
            <div style="background: rgba(0,0,0,0.3); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Operating System</div>
                <div style="font-size: 15px; font-weight: 700; color: #fff; margin-top: 4px;">Windows 10/11, macOS, Ubuntu</div>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">WebCam</div>
                <div style="font-size: 15px; font-weight: 700; color: #34d399; margin-top: 4px;">720p HD Working Camera</div>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Microphone</div>
                <div style="font-size: 15px; font-weight: 700; color: #34d399; margin-top: 4px;">Internal or USB Mic</div>
            </div>
            <div style="background: rgba(0,0,0,0.3); padding: 16px; border-radius: 12px; border: 1px solid var(--border-color);">
                <div style="font-size: 12px; color: var(--text-muted);">Internet Bandwidth</div>
                <div style="font-size: 15px; font-weight: 700; color: #818cf8; margin-top: 4px;">Min 512 Kbps Stable Connection</div>
            </div>
        </div>
    </div>
</section>

<!-- Frequently Asked Questions Accordion -->
<section style="max-width: 1100px; margin: 0 auto 100px; padding: 0 24px;">
    <h2 style="font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 24px;">
        Frequently Asked Questions
    </h2>

    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div class="glass-panel" style="padding: 24px;">
            <h4 style="font-size: 17px; font-weight: 700; color: #fff;">What happens if my internet connection drops during the exam?</h4>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-top: 8px;">
                ExamFort features an embedded 5-second delta sync engine with local offline buffering. Your drafted answers are saved locally in real time. Once your connection resumes, your unsynced answers are automatically committed to the examination server without data loss.
            </p>
        </div>

        <div class="glass-panel" style="padding: 24px;">
            <h4 style="font-size: 17px; font-weight: 700; color: #fff;">What triggers an automatic Proctoring Violation?</h4>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-top: 8px;">
                Violations are logged if you:
                <br>&bull; Press Alt+Tab or switch browser tabs/windows
                <br>&bull; Connect external monitors or launch screen recording tools
                <br>&bull; Multiple human faces appear in your webcam feed
                <br>&bull; High-amplitude background conversation / speech is detected
            </p>
        </div>

        <div class="glass-panel" style="padding: 24px;">
            <h4 style="font-size: 17px; font-weight: 700; color: #fff;">How do I obtain my 6-digit Exam Access PIN?</h4>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6; margin-top: 8px;">
                Your institution's Dean, Principal, or assigned Faculty Proctor generates your 6-digit PIN upon enrolling you into the batch. It will appear on your candidate admit card or student portal.
            </p>
        </div>
    </div>
</section>
@endsection
