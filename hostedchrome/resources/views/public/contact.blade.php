@extends('layouts.public')

@section('title', 'Contact ExamFort - Schedule Institutional Campus Demo')

@section('content')
<section style="padding: 70px 24px 40px; text-align: center;">
    <div style="max-width: 860px; margin: 0 auto;">
        <span style="font-size: 12px; font-weight: 800; color: #34d399; letter-spacing: 1px; text-transform: uppercase;">GET IN TOUCH</span>
        <h1 style="font-family: 'Outfit', sans-serif; font-size: clamp(32px, 4vw, 48px); font-weight: 900; color: #fff; margin-top: 10px; margin-bottom: 16px;">
            Schedule an Institutional Demo or Contact Us
        </h1>
        <p style="font-size: 16px; color: var(--text-secondary); line-height: 1.6;">
            Speak with our academic technology specialists to explore custom multi-tenant deployments, student capacity licensing, and campus integration.
        </p>
    </div>
</section>

<!-- Contact Form & Institutional Info -->
<section style="max-width: 1100px; margin: 0 auto 100px; padding: 0 24px;">
    <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 32px;">
        <!-- Contact / Demo Form -->
        <div class="glass-panel">
            @if(session('success'))
                <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); border-radius: 12px; padding: 16px; margin-bottom: 24px; color: #34d399; font-size: 14px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                    <i data-lucide="check-circle" style="width: 20px; height: 20px;"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <h3 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #fff; margin-bottom: 20px;">
                Request Institutional Consultation
            </h3>

            <form action="{{ route('public.contact.submit') }}" method="POST">
                @csrf
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Your Name *</label>
                        <input type="text" name="full_name" class="mono" style="width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px;" placeholder="Dr. Rajesh Gupta" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Official Email *</label>
                        <input type="email" name="email" class="mono" style="width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px;" placeholder="dean@university.edu" required>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Phone / Mobile</label>
                        <input type="text" name="phone" class="mono" style="width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px;" placeholder="+91 98765 43210">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">College / Institution Name</label>
                        <input type="text" name="organization_name" style="width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px;" placeholder="National Institute of Technology">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Subject *</label>
                    <input type="text" name="subject" style="width: 100%; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px;" placeholder="Campus Proctoring Demo Request (2,000 Students)" required>
                </div>

                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;">Message & Assessment Requirements *</label>
                    <textarea name="message" style="width: 100%; min-height: 110px; padding: 10px 14px; background: rgba(0,0,0,0.3); border: 1px solid var(--border-color); border-radius: 8px; color: #fff; font-size: 14px; resize: vertical;" placeholder="Provide details regarding your expected student concurrency, upcoming exam dates..." required></textarea>
                </div>

                <button type="submit" class="btn-primary" style="width: 100%; justify-content: center; padding: 14px; font-size: 15px;">
                    <i data-lucide="send" style="width: 16px; height: 16px;"></i>
                    <span>Submit Inquiry & Request Demo</span>
                </button>
            </form>
        </div>

        <!-- Institutional Details -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="glass-panel">
                <h4 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 12px;">Enterprise Direct Channels</h4>
                <div style="display: flex; flex-direction: column; gap: 14px; font-size: 13px; color: #cbd5e1;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="mail" style="color: #818cf8; width: 16px; height: 16px;"></i>
                        <span>institutes@examfort.com</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="phone" style="color: #34d399; width: 16px; height: 16px;"></i>
                        <span>+91 11 4987 6500 (Academic Desk)</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <i data-lucide="clock" style="color: #fbbf24; width: 16px; height: 16px;"></i>
                        <span>Response SLA: 2 to 4 Business Hours</span>
                    </div>
                </div>
            </div>

            <div class="glass-panel">
                <h4 style="font-size: 17px; font-weight: 700; color: #fff; margin-bottom: 12px;">Institutional Deployment Support</h4>
                <p style="font-size: 13px; color: var(--text-secondary); line-height: 1.6;">
                    Our engineers provide full white-glove setup for college on-premise servers, campus Active Directory / LDAP integrations, and bulk student roster CSV imports.
                </p>
            </div>
        </div>
    </div>
</section>
@endsection
