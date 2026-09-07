@extends('layouts.public')

@section('title', 'ExamFort - Next-Gen AI Secure Proctoring & Multi-Tenant Examination Cloud')

@section('styles')
<style>
    /* Glow & Animation Tokens */
    .hero-glow-sphere {
        position: absolute;
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, rgba(99, 102, 241, 0.22) 0%, rgba(59, 130, 246, 0.08) 50%, transparent 70%);
        top: -100px;
        left: 50%;
        transform: translateX(-50%);
        pointer-events: none;
        z-index: 0;
        filter: blur(40px);
    }
    
    .badge-pill-pulse {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(99, 102, 241, 0.12);
        border: 1px solid rgba(99, 102, 241, 0.35);
        padding: 6px 18px;
        border-radius: 30px;
        box-shadow: 0 0 20px rgba(99, 102, 241, 0.15);
    }

    .pulse-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #10b981;
        box-shadow: 0 0 10px #10b981;
        animation: pulseAnimation 2s infinite;
    }

    @keyframes pulseAnimation {
        0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { transform: scale(1.1); box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
        100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    /* Live Interactive Hero Radar Card */
    .hero-radar-card {
        background: rgba(15, 23, 42, 0.85);
        backdrop-filter: blur(20px);
        border: 1.5px solid rgba(99, 102, 241, 0.3);
        border-radius: 22px;
        padding: 24px;
        box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.5), 0 0 30px rgba(99, 102, 241, 0.15);
        max-width: 900px;
        margin: 48px auto 0;
        text-align: left;
        position: relative;
        overflow: hidden;
    }

    .hero-radar-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 2px;
        background: linear-gradient(90deg, #6366f1, #38bdf8, #10b981, #6366f1);
        background-size: 200% auto;
        animation: gradientMove 4s linear infinite;
    }

    @keyframes gradientMove {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }

    /* Interactive Demo Tabs */
    .demo-tab-btn {
        background: transparent;
        border: none;
        color: var(--text-secondary);
        font-weight: 700;
        font-size: 13.5px;
        padding: 10px 18px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .demo-tab-btn:hover {
        color: #fff;
        background: rgba(255, 255, 255, 0.05);
    }
    .demo-tab-btn.active {
        color: #fff;
        background: rgba(99, 102, 241, 0.2);
        border: 1px solid rgba(99, 102, 241, 0.4);
    }

    /* Course Library Card */
    .course-showcase-card {
        background: rgba(15, 23, 42, 0.7);
        backdrop-filter: blur(14px);
        border: 1px solid var(--border-color);
        border-radius: 18px;
        padding: 24px;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        text-align: left;
    }
    .course-showcase-card:hover {
        transform: translateY(-4px);
        border-color: rgba(99, 102, 241, 0.5);
        box-shadow: 0 16px 32px rgba(0, 0, 0, 0.3), 0 0 20px rgba(99, 102, 241, 0.2);
    }

    /* Comparison Table */
    .comparison-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        font-size: 13.5px;
    }
    .comparison-table th {
        padding: 16px 20px;
        background: rgba(30, 41, 59, 0.6);
        color: #fff;
        font-weight: 800;
        border-bottom: 1px solid var(--border-color);
    }
    .comparison-table td {
        padding: 16px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        color: var(--text-secondary);
    }
    .comparison-table tr:hover td {
        background: rgba(255, 255, 255, 0.02);
    }
</style>
@endsection

@section('content')

<!-- ========================================================
     1. HERO SECTION
======================================================== -->
<section style="padding: 100px 24px 80px; text-align: center; position: relative;">
    <div class="hero-glow-sphere"></div>

    <div style="max-width: 1080px; margin: 0 auto; position: relative; z-index: 1;">
        
        <!-- Live Architecture Badge -->
        <div class="badge-pill-pulse" style="margin-bottom: 24px;">
            <span class="pulse-dot"></span>
            <span style="font-size: 12.5px; font-weight: 800; color: #818cf8; letter-spacing: 0.6px; text-transform: uppercase;">
                EXAMFORT V3.4 • MULTI-TENANT PROCTORING & CURRICULUM CLOUD
            </span>
        </div>

        <!-- Hero Headline -->
        <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(38px, 5.5vw, 68px); font-weight: 900; line-height: 1.12; color: #fff; letter-spacing: -1.2px; margin-bottom: 24px;">
            Bypass-Proof AI Proctoring & <br>
            <span style="background: linear-gradient(135deg, #818cf8 0%, #38bdf8 50%, #34d399 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
                Institutional Examination Cloud
            </span>
        </h1>

        <!-- Subheading -->
        <p style="font-size: clamp(16px, 2vw, 19px); color: var(--text-secondary); line-height: 1.65; max-width: 800px; margin: 0 auto 38px;">
            Built specifically for Universities, Examination Boards, and Technical Institutes. Unifying multi-tenant Dean / Principal management, sub-second AI proctoring radar, 5-second delta candidate telemetry, and Gemini-powered curriculum authoring.
        </p>

        <!-- CTA Buttons -->
        <div style="display: flex; gap: 16px; justify-content: center; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('public.download') }}" class="btn-primary btn-emerald" style="padding: 14px 28px; font-size: 15.5px; border-radius: 12px; font-weight: 800;">
                <i data-lucide="download" style="width: 20px; height: 20px;"></i>
                <span>Download Secure Client (.exe)</span>
            </a>
            <a href="{{ route('login') }}" class="btn-primary" style="padding: 14px 28px; font-size: 15.5px; border-radius: 12px; font-weight: 800;">
                <i data-lucide="shield" style="width: 20px; height: 20px;"></i>
                <span>Launch Faculty / Admin Portal</span>
            </a>
            <a href="{{ route('public.contact') }}" class="btn-secondary" style="padding: 14px 24px; font-size: 15.5px; border-radius: 12px; font-weight: 700;">
                <i data-lucide="calendar" style="width: 18px; height: 18px;"></i>
                <span>Request Campus Demo</span>
            </a>
        </div>

        <!-- Trust Mini Badges -->
        <div style="display: flex; justify-content: center; gap: 28px; flex-wrap: wrap; margin-top: 40px; font-size: 13px; color: var(--text-muted);">
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="database" style="color: #34d399; width: 16px; height: 16px;"></i>
                <span>Pure MySQL Transactional Backend</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="lock" style="color: #818cf8; width: 16px; height: 16px;"></i>
                <span>IP Binding & Hardware Lockout</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="activity" style="color: #fbbf24; width: 16px; height: 16px;"></i>
                <span>5-Sec In-Flight Draft Telemetry</span>
            </div>
            <div style="display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sparkles" style="color: #a78bfa; width: 16px; height: 16px;"></i>
                <span>Gemini AI Lesson Studio</span>
            </div>
        </div>

        <!-- ========================================================
             DUMMY INTERACTIVE PROCTOR TELEMETRY PREVIEW CARD
        ======================================================== -->
        <div class="hero-radar-card">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border-color); padding-bottom: 16px; margin-bottom: 18px; flex-wrap: wrap; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #818cf8; box-shadow: 0 0 10px #818cf8;"></span>
                    <strong style="font-size: 14.5px; color: #fff; font-family: 'Outfit', sans-serif;">AI Proctor Radar &amp; Telemetry Feed (Simulation Demo)</strong>
                    <span style="font-size: 11px; padding: 3px 9px; border-radius: 6px; background: rgba(99, 102, 241, 0.2); color: #818cf8; font-weight: 800; letter-spacing: 0.5px;">DEMO PREVIEW</span>
                </div>
                <div style="font-size: 12px; color: var(--text-muted); font-family: 'JetBrains Mono', monospace;">
                    Simulated Delta: <span style="color: #38bdf8; font-weight: 700;">5s Interval</span> • Status: <span style="color: #34d399; font-weight: 700;">Sample Preview</span>
                </div>
            </div>

            <!-- Grid inside preview -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 16px;">
                <!-- Candidate Radar Node 1 -->
                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #fff;">Sample Candidate A (Demo)</span>
                        <span style="font-size: 10.5px; font-weight: 800; background: rgba(16, 185, 129, 0.15); color: #34d399; padding: 2px 6px; border-radius: 4px;">CLEAN • 0 FLAGS</span>
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-secondary); margin-bottom: 6px;">
                        Sample Test: <strong style="color: #f8fafc;">CS201 Data Structures</strong> (Q4 of 10)
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; background: rgba(0,0,0,0.4); padding: 6px 8px; border-radius: 6px;">
                        Draft Telemetry: <code>vector&lt;int&gt; adj[N];</code> (Simulated)
                    </div>
                </div>

                <!-- Candidate Radar Node 2 -->
                <div style="background: rgba(0,0,0,0.35); border: 1px solid rgba(245, 158, 11, 0.3); border-radius: 14px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #fff;">Sample Candidate B (Demo)</span>
                        <span style="font-size: 10.5px; font-weight: 800; background: rgba(245, 158, 11, 0.15); color: #fbbf24; padding: 2px 6px; border-radius: 4px;">GAZE WARN (1)</span>
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-secondary); margin-bottom: 6px;">
                        Sample Test: <strong style="color: #f8fafc;">EE304 Digital Circuits</strong> (Q7 of 15)
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; background: rgba(0,0,0,0.4); padding: 6px 8px; border-radius: 6px;">
                        AI Alert Simulation: <span style="color: #fbbf24;">Gaze off-screen (3.2s)</span>
                    </div>
                </div>

                <!-- Multi-Language Sandbox Status -->
                <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 12px; font-weight: 700; color: #fff;">Compiler Sandbox Engine</span>
                        <span style="font-size: 10.5px; font-weight: 800; background: rgba(59, 130, 246, 0.15); color: #38bdf8; padding: 2px 6px; border-radius: 4px;">SANDBOX READY</span>
                    </div>
                    <div style="font-size: 11.5px; color: var(--text-secondary); margin-bottom: 6px;">
                        Supported: <span style="color: #818cf8; font-weight: 700;">GCC 15.2 • Python 3.14 • Node 24</span>
                    </div>
                    <div style="font-size: 11px; color: var(--text-muted); font-family: monospace; background: rgba(0,0,0,0.4); padding: 6px 8px; border-radius: 6px;">
                        Sample Execution: <span style="color: #34d399;">5/5 Test Cases Passed (4ms)</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>


<!-- ========================================================
     2. TELEMETRY LIVE METRICS SECTION
======================================================== -->
<section style="max-width: 1240px; margin: 0 auto 90px; padding: 0 24px;">
    <div style="background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border-color); border-radius: 24px; padding: 36px 32px; display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 28px; text-align: center;">
        <div>
            <div style="font-family: 'Outfit', sans-serif; font-size: 42px; font-weight: 900; color: #818cf8; line-height: 1;">{{ $totalOrganizations }}</div>
            <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 8px; font-weight: 700;">Partner Institutions & Boards</div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Multi-Tenant Governance</div>
        </div>
        <div>
            <div style="font-family: 'Outfit', sans-serif; font-size: 42px; font-weight: 900; color: #34d399; line-height: 1;">{{ $totalTeachers }}</div>
            <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 8px; font-weight: 700;">Authorized Proctors & Faculty</div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Granular Quota Allocation</div>
        </div>
        <div>
            <div style="font-family: 'Outfit', sans-serif; font-size: 42px; font-weight: 900; color: #38bdf8; line-height: 1;">{{ $totalStudents }}</div>
            <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 8px; font-weight: 700;">Enrolled Candidates</div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Real-Time Roster & PIN Login</div>
        </div>
        <div>
            <div style="font-family: 'Outfit', sans-serif; font-size: 42px; font-weight: 900; color: #fbbf24; line-height: 1;">{{ $totalExams }}</div>
            <div style="font-size: 13.5px; color: var(--text-secondary); margin-top: 8px; font-weight: 700;">Assessments Conducted</div>
            <div style="font-size: 11.5px; color: var(--text-muted); margin-top: 2px;">Zero Compromise Recorded</div>
        </div>
    </div>
</section>





<!-- ========================================================
     4. HOW TO GET STARTED (3-STEP DEPLOYMENT)
======================================================== -->
<section id="download" style="max-width: 1280px; margin: 0 auto 100px; padding: 0 24px;">
    <div style="text-align: center; margin-bottom: 50px;">
        <span style="font-size: 12px; font-weight: 800; color: #10b981; letter-spacing: 1px; text-transform: uppercase;">SEAMLESS DEPLOYMENT</span>
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 36px; font-weight: 800; color: #fff; margin-top: 8px;">
            How to Deploy &amp; Conduct Assessments
        </h2>
        <p style="color: var(--text-secondary); font-size: 15px; margin-top: 8px; max-width: 600px; margin-left: auto; margin-right: auto;">
            Get up and running in less than 2 minutes. Install our secure kiosk client app or integrate directly into your campus infrastructure.
        </p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px;">
        <!-- Step 1: Download -->
        <div class="glass-panel" style="position: relative; overflow: hidden;">
            <div style="position: absolute; top: 16px; right: 20px; font-family: 'Outfit', sans-serif; font-size: 40px; font-weight: 900; color: rgba(255,255,255,0.05);">01</div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i data-lucide="download-cloud" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">
                1. Download Client App
            </h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                Download the official <strong>ExamFort Secure Proctor Client</strong> for Windows or launch the hosted web kiosk in Google Chrome.
            </p>
            <a href="{{ route('public.download') }}" class="btn-primary" style="width: 100%; justify-content: center;">
                <i data-lucide="download" style="width: 16px; height: 16px;"></i>
                <span>Download Client (.exe)</span>
            </a>
        </div>

        <!-- Step 2: Login / Enter PIN -->
        <div class="glass-panel" style="position: relative; overflow: hidden;">
            <div style="position: absolute; top: 16px; right: 20px; font-family: 'Outfit', sans-serif; font-size: 40px; font-weight: 900; color: rgba(255,255,255,0.05);">02</div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(16, 185, 129, 0.2); color: #34d399; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i data-lucide="key" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">
                2. Authenticate &amp; Hardware Check
            </h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                Candidates enter their Roll ID &amp; 6-digit PIN. The system runs automatic webcam, microphone, and fullscreen lockdown verification.
            </p>
            <div style="background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                <i data-lucide="check" style="width: 14px; height: 14px; color: #10b981;"></i>
                <span>Hardware check takes ~15 seconds</span>
            </div>
        </div>

        <!-- Step 3: Take Proctored Test -->
        <div class="glass-panel" style="position: relative; overflow: hidden;">
            <div style="position: absolute; top: 16px; right: 20px; font-family: 'Outfit', sans-serif; font-size: 40px; font-weight: 900; color: rgba(255,255,255,0.05);">03</div>
            <div style="width: 48px; height: 48px; border-radius: 12px; background: rgba(245, 158, 11, 0.2); color: #fbbf24; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <i data-lucide="check-circle-2" style="width: 24px; height: 24px;"></i>
            </div>
            <h3 style="font-family: 'Outfit', sans-serif; font-size: 20px; font-weight: 700; color: #fff; margin-bottom: 10px;">
                3. Attempt &amp; Instant Auto-Sync
            </h3>
            <p style="color: var(--text-secondary); font-size: 14px; line-height: 1.6; margin-bottom: 20px;">
                Take MCQs, solve multi-language coding questions in the sandbox, or write essays. Answers auto-save every 5 seconds to prevent data loss.
            </p>
            <div style="background: rgba(0,0,0,0.3); padding: 10px 14px; border-radius: 8px; border: 1px solid var(--border-color); font-size: 12px; color: var(--text-muted); display: flex; align-items: center; gap: 8px;">
                <i data-lucide="radio" style="width: 14px; height: 14px; color: #10b981;"></i>
                <span>Continuous proctor telemetry stream</span>
            </div>
        </div>
    </div>
</section>


<!-- ========================================================
     5. MULTI-TENANT INSTITUTIONAL HIERARCHY
======================================================== -->
<section style="max-width: 1280px; margin: 0 auto 100px; padding: 0 24px;">
    <div style="background: linear-gradient(135deg, rgba(15, 23, 42, 0.95), rgba(30, 41, 59, 0.85)); border: 1px solid var(--border-color); border-radius: 24px; padding: 48px 36px; box-shadow: 0 20px 40px rgba(0,0,0,0.3);">
        <div style="text-align: center; margin-bottom: 40px;">
            <span style="font-size: 12px; font-weight: 800; color: #818cf8; letter-spacing: 1px; text-transform: uppercase;">ENTERPRISE GOVERNANCE</span>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; color: #fff; margin-top: 8px;">
                Four-Tier Institutional Hierarchy
            </h2>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 8px; max-width: 650px; margin-left: auto; margin-right: auto;">
                Four-tier governance model ensuring end-to-end accountability across universities, academic departments, and candidate pools.
            </p>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px;">
            <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="font-size: 11px; font-weight: 800; color: #f87171; text-transform: uppercase; margin-bottom: 8px;">Tier 1: Apex Governance</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Super Administrator</h4>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5;">
                    Provisions partner organizations, configures institutional quotas, allocates Gemini API keys, and audits global platform telemetry.
                </p>
            </div>

            <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="font-size: 11px; font-weight: 800; color: #fbbf24; text-transform: uppercase; margin-bottom: 8px;">Tier 2: College Leadership</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Dean / Principal</h4>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5;">
                    Oversees college departments, delegates courses to teachers, authors AI curriculum with Gemini, and monitors student passing rates.
                </p>
            </div>

            <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="font-size: 11px; font-weight: 800; color: #818cf8; text-transform: uppercase; margin-bottom: 8px;">Tier 3: Academic Faculty</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Teacher / Proctor</h4>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5;">
                    Enrolls candidates, schedules exams, reviews live incident radars, conducts coding sandbox grading, and releases results.
                </p>
            </div>

            <div style="background: rgba(0,0,0,0.35); border: 1px solid var(--border-color); border-radius: 14px; padding: 22px;">
                <div style="font-size: 11px; font-weight: 800; color: #34d399; text-transform: uppercase; margin-bottom: 8px;">Tier 4: Examinee</div>
                <h4 style="font-size: 16px; font-weight: 700; color: #fff;">Student / Examinee</h4>
                <p style="font-size: 12.5px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5;">
                    Securely enters via 6-digit PIN, solves MCQs and code challenges with 5s delta saving, and reviews downloadable landscape notes.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- ========================================================
     6. COMPREHENSIVE PLATFORM CAPABILITIES
======================================================== -->
<section id="features" style="max-width: 1280px; margin: 0 auto 100px; padding: 0 24px;">
    <div style="text-align: center; margin-bottom: 50px;">
        <span style="font-size: 12px; font-weight: 800; color: #818cf8; letter-spacing: 1px; text-transform: uppercase;">INTELLIGENT SUITE</span>
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 36px; font-weight: 800; color: #fff; margin-top: 8px;">
            Comprehensive Assessment Capabilities
        </h2>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(360px, 1fr)); gap: 24px;">
        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(239, 68, 68, 0.15); color: #f87171; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="radio" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Real-Time Proctor Radar</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Sub-second incident detection for tab switches, dual-screen extensions, background application focus, and multi-face detections.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(59, 130, 246, 0.15); color: #38bdf8; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="code" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Multi-Language Sandbox</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Execute code in C++, C, Java, Python, and JavaScript with public test cases and hidden weighted edge-case evaluation.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(16, 185, 129, 0.15); color: #34d399; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="save" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">5-Second Draft Telemetry</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Continuous background delta sync allows proctors to inspect in-flight candidate drafts live even before final submission.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(245, 158, 11, 0.15); color: #fbbf24; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="sparkles" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Gemini AI Curriculum Generator</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Auto-generate structured lecture modules, formatted conceptual notes, practice MCQs, and coding problems directly via Google Gemini API.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(139, 92, 246, 0.15); color: #a78bfa; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="sliders" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Granular Quota Allocation</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                Enforce strict student addition and exam scheduling quotas on individual faculty accounts to control institutional infrastructure load.
            </p>
        </div>

        <div class="glass-panel">
            <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(236, 72, 153, 0.15); color: #f472b6; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                <i data-lucide="shield-alert" style="width: 22px; height: 22px;"></i>
            </div>
            <h3 style="font-size: 18px; font-weight: 700; color: #fff; margin-bottom: 8px;">Zero-Bypass Security</h3>
            <p style="font-size: 14px; color: var(--text-secondary); line-height: 1.6;">
                IP-bound session tracking, anti-brute force throttling, CSRF hardening, and secure kiosk window lockdowns.
            </p>
        </div>
    </div>
</section>


<!-- ========================================================
     7. COMPARISON MATRIX (EXAMFORT VS LEGACY LMS)
======================================================== -->
<section style="max-width: 1240px; margin: 0 auto 100px; padding: 0 24px;">
    <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: 24px; padding: 40px; overflow-x: auto;">
        <div style="text-align: center; margin-bottom: 32px;">
            <span style="font-size: 12px; font-weight: 800; color: #38bdf8; letter-spacing: 1px; text-transform: uppercase;">PLATFORM BENCHMARK</span>
            <h2 style="font-family: 'Outfit', sans-serif; font-size: 28px; font-weight: 800; color: #fff; margin-top: 6px;">
                Why Leading Universities Choose ExamFort
            </h2>
        </div>

        <table class="comparison-table">
            <thead>
                <tr>
                    <th>Feature / Capability</th>
                    <th style="color: #818cf8;">ExamFort Next-Gen</th>
                    <th>Legacy LMS Platforms</th>
                    <th>Generic Online Forms</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><strong style="color: #fff;">Real-Time Proctor Radar</strong></td>
                    <td style="color: #34d399; font-weight: 700;">✓ Sub-second event stream &amp; radar</td>
                    <td>✕ Delayed post-test logs</td>
                    <td>✕ None</td>
                </tr>
                <tr>
                    <td><strong style="color: #fff;">5-Sec Candidate Draft Telemetry</strong></td>
                    <td style="color: #34d399; font-weight: 700;">✓ Continuous live answer delta</td>
                    <td>✕ Only on submit button click</td>
                    <td>✕ None (Data loss on crash)</td>
                </tr>
                <tr>
                    <td><strong style="color: #fff;">Live Multi-Language Sandbox</strong></td>
                    <td style="color: #34d399; font-weight: 700;">✓ GCC 15, Python 3, Node.js, Java</td>
                    <td>✕ Plain textboxes only</td>
                    <td>✕ None</td>
                </tr>
                <tr>
                    <td><strong style="color: #fff;">AI Curriculum &amp; Lesson Studio</strong></td>
                    <td style="color: #34d399; font-weight: 700;">✓ Gemini AI Notes &amp; MCQs generation</td>
                    <td>✕ Manual authoring only</td>
                    <td>✕ None</td>
                </tr>
                <tr>
                    <td><strong style="color: #fff;">Offline Landscape PDF Notes</strong></td>
                    <td style="color: #34d399; font-weight: 700;">✓ Instant high-res landscape download</td>
                    <td>✕ Basic print stylesheets</td>
                    <td>✕ None</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>


<!-- ========================================================
     8. CALL TO ACTION BANNER
======================================================== -->
<section style="max-width: 1280px; margin: 0 auto 60px; padding: 0 24px;">
    <div style="background: var(--accent-gradient); border-radius: 24px; padding: 60px 36px; text-align: center; color: white; box-shadow: 0 20px 50px rgba(99, 102, 241, 0.4);">
        <h2 style="font-family: 'Outfit', sans-serif; font-size: 36px; font-weight: 900; margin-bottom: 16px;">
            Ready to Transform Your Institutional Assessments?
        </h2>
        <p style="font-size: 16px; opacity: 0.9; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">
            Deploy ExamFort in your college or university today. Experience military-grade security with seamless faculty and student workflows.
        </p>
        <div style="display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
            <a href="{{ route('public.contact') }}" class="btn-primary" style="background: white; color: #4338ca; font-weight: 800; padding: 14px 28px; font-size: 15px;">
                Request Institutional Demo &rarr;
            </a>
            <a href="{{ route('public.download') }}" class="btn-secondary" style="background: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.3); padding: 14px 28px; font-size: 15px;">
                Download Desktop Client
            </a>
        </div>
    </div>
</section>
@endsection
