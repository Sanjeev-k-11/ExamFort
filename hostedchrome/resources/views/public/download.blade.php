@extends('layouts.public')

@section('title', 'Download ExamFort Client - Secure Proctored Examination Software')

@section('content')
<section style="padding: 70px 24px 50px; text-align: center;">
    <div style="max-width: 860px; margin: 0 auto;">
        <span style="font-size: 12px; font-weight: 800; color: #10b981; letter-spacing: 1px; text-transform: uppercase;">SECURE CLIENT PACKAGES</span>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(32px, 4vw, 48px); font-weight: 900; color: #fff; margin-top: 10px; margin-bottom: 16px;">
            Download ExamFort Secure Client
        </h1>
        <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.6;">
            Official proctored kiosk application for candidates. Featuring automatic fullscreen lockdown, clipboard isolation, and multi-modal proctoring stream.
        </p>
    </div>
</section>

<!-- Download Grid -->
<section style="max-width: 1200px; margin: 0 auto 80px; padding: 0 24px;">
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 28px;">
        <!-- Windows Desktop App -->
        <div class="glass-panel" style="border: 2px solid rgba(99, 102, 241, 0.4); background: linear-gradient(180deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.9)); position: relative;">
            <div style="position: absolute; top: -12px; right: 24px; background: var(--accent-gradient); color: white; font-size: 11px; font-weight: 800; padding: 4px 12px; border-radius: 20px; letter-spacing: 0.5px;">RECOMMENDED</div>

            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="monitor" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #fff;">Windows Desktop Client</h3>
                    <div style="font-size: 12px; color: var(--text-muted);">Windows 10 / 11 (64-bit) &bull; v3.4.2</div>
                </div>
            </div>

            <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; font-size: 13px; color: #cbd5e1;">
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Hardware Kiosk Lock (Alt+Tab & Taskbar Block)</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Virtual Machine & Remote Desktop Detection</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Embedded Offline-Resilient 5s Delta Sync</span>
                </li>
            </ul>

            <a href="javascript:alert('ExamFort-Client-Setup-v3.4.2.exe download started (68.4 MB).');" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px;">
                <i data-lucide="download" style="width: 18px; height: 18px;"></i>
                <span>Download for Windows (.exe)</span>
            </a>
            <div style="text-align: center; margin-top: 10px; font-size: 11px; color: var(--text-muted);">SHA-256: e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855</div>
        </div>

        <!-- Chrome Kiosk Extension -->
        <div class="glass-panel">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="globe" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #fff;">Chrome Kiosk Extension</h3>
                    <div style="font-size: 12px; color: var(--text-muted);">Chrome, Edge, Brave (Manifest V3)</div>
                </div>
            </div>

            <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; font-size: 13px; color: #cbd5e1;">
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Lightweight zero-install browser extension</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Tab switch & window focus blur tracker</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Webcam & audio real-time telemetry bridge</span>
                </li>
            </ul>

            <a href="javascript:alert('ExamFort Chrome Web Extension (v3.4) package loaded.');" class="btn-secondary" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px;">
                <i data-lucide="external-link" style="width: 18px; height: 18px;"></i>
                <span>Add to Chrome / Edge</span>
            </a>
            <div style="text-align: center; margin-top: 10px; font-size: 11px; color: var(--text-muted);">Compatible with Chromebooks & Linux Workstations</div>
        </div>

        <!-- macOS & Linux Builds -->
        <div class="glass-panel">
            <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 20px;">
                <div style="width: 54px; height: 54px; border-radius: 14px; background: rgba(59, 130, 246, 0.2); color: #38bdf8; display: flex; align-items: center; justify-content: center;">
                    <i data-lucide="terminal" style="width: 28px; height: 28px;"></i>
                </div>
                <div>
                    <h3 style="font-size: 20px; font-weight: 700; color: #fff;">macOS & Linux Client</h3>
                    <div style="font-size: 12px; color: var(--text-muted);">macOS 12+ (Apple / Intel) &bull; Ubuntu/Debian</div>
                </div>
            </div>

            <ul style="list-style: none; display: flex; flex-direction: column; gap: 10px; margin-bottom: 24px; font-size: 13px; color: #cbd5e1;">
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Native Apple Silicon ARM64 & Intel Universal DMG</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Ubuntu .deb package & Standalone .AppImage</span>
                </li>
                <li style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                    <span>Native accessibility & sandbox enforcement</span>
                </li>
            </ul>

            <div style="display: flex; gap: 10px;">
                <a href="javascript:alert('ExamFort-macOS-Universal.dmg download started.');" class="btn-secondary" style="flex: 1; justify-content: center; padding: 12px; font-size: 13px;">
                    <span>macOS (.dmg)</span>
                </a>
                <a href="javascript:alert('ExamFort-Linux-x86_64.AppImage download started.');" class="btn-secondary" style="flex: 1; justify-content: center; padding: 12px; font-size: 13px;">
                    <span>Linux (.deb)</span>
                </a>
            </div>
            <div style="text-align: center; margin-top: 10px; font-size: 11px; color: var(--text-muted);">Automated updates supported</div>
        </div>
    </div>
</section>

<!-- Step-by-Step Installation Instructions -->
<section style="max-width: 960px; margin: 0 auto 100px; padding: 0 24px;">
    <div class="glass-panel">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 24px;">
            Step-by-Step Installation & Verification Guide
        </h2>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div style="display: flex; gap: 16px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(99, 102, 241, 0.2); color: #818cf8; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    1
                </div>
                <div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Run Installer</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5;">
                        Double click <code class="mono" style="color: #818cf8;">ExamFort-Client-Setup.exe</code> and follow the onscreen prompt. No administrative administrator privileges required for user installations.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(16, 185, 129, 0.2); color: #34d399; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    2
                </div>
                <div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Grant Camera & Microphone Permissions</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5;">
                        Upon initial launch, click "Allow Access" for WebCam and Microphone. The diagnostic screen will display your live video feed and audio decibel gauge.
                    </p>
                </div>
            </div>

            <div style="display: flex; gap: 16px;">
                <div style="width: 32px; height: 32px; border-radius: 8px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; font-weight: 800; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                    3
                </div>
                <div>
                    <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Enter Candidate Roll Number & PIN</h4>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 4px; line-height: 1.5;">
                        Provide your Student Registration ID and the 6-digit access code provided by your course faculty or examination controller. The proctored session will initialize in locked fullscreen mode.
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
