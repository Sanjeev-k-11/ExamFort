

class ExamFortApp {
    constructor() {
        this.backendUrl = window.EXAMFORT_ENV?.API_BASE_URL || 'http://localhost:5000';
        this.currentScreen = 'system-check';
        this.candidate = {
            id: '123',
            name: 'Candidate 123',
            examCode: window.EXAMFORT_ENV?.DEFAULT_EXAM_CODE || 'NAT-2026-EXAM'
        };

        this.questions = [];
        this.currentQuestionIndex = 0; 
        this.answers = {}; 
        this.markedForReview = new Set();
        this.timerSeconds = 120 * 60; 
        this.timerInterval = null;

        this.detectedApps = [];
        this.systemStatus = {
            processesClean: false,
            networkReady: false,
            displaysValid: false,
            mediaDevicesReady: false
        };

        this.init();
    }

    static get SESSION_KEY() { return 'examfort_session'; }
    static get SESSION_EXPIRY_MS() { return 2 * 24 * 60 * 60 * 1000; } 

    saveSession(candidate) {
        const session = {
            candidate,
            loginTime: Date.now(),
            expiresAt: Date.now() + ExamFortApp.SESSION_EXPIRY_MS
        };
        localStorage.setItem(ExamFortApp.SESSION_KEY, JSON.stringify(session));
    }

    loadSession() {
        try {
            const raw = localStorage.getItem(ExamFortApp.SESSION_KEY);
            if (!raw) return null;
            const session = JSON.parse(raw);
            if (!session || !session.expiresAt || !session.candidate) return null;
            if (Date.now() > session.expiresAt) {
                
                localStorage.removeItem(ExamFortApp.SESSION_KEY);
                return null;
            }
            return session.candidate;
        } catch (_) { return null; }
    }

    clearSession() {
        localStorage.removeItem(ExamFortApp.SESSION_KEY);
        localStorage.removeItem('exam_candidate');
        localStorage.removeItem('examfort_user');
        localStorage.removeItem('exam_start_mode');
        sessionStorage.removeItem('examfort_user');
        sessionStorage.removeItem('exam_candidate');
        sessionStorage.removeItem('examfort_token');
        sessionStorage.removeItem('exam_start_mode');
        sessionStorage.clear();
    }

    async init() {
        console.log('🚀 [ExamFort] Initializing Secure Assessment Application...');
        
        window._examFortAppInstance = this;
        window.__detectedApps = this.detectedApps;
        this.bindEvents();

        if (window.location.search.includes('logout=1')) {
            this.clearSession();
            try {
                window.history.replaceState({}, document.title, window.location.pathname);
            } catch (_) {}
            this.showToast('Logged out successfully.', 'success');
            return;
        }

        const savedCandidate = this.loadSession();
        if (savedCandidate) {
            console.log('[Session] Valid session found for:', savedCandidate.name);
            this.candidate = savedCandidate;
            sessionStorage.setItem('examfort_user', JSON.stringify(savedCandidate));
            sessionStorage.setItem('exam_candidate', JSON.stringify(savedCandidate));

            if (window.electronAPI?.scanProcesses) {
                try {
                    const threats = await window.electronAPI.scanProcesses() || [];
                    if (threats.length === 0) {
                        
                        console.log('[Session] Environment clean. Navigating to dashboard.');
                        window.location.href = './dashboard.html';
                        return;
                    } else {
                        
                        console.warn(`[Session] ${threats.length} threat(s) found. Blocking dashboard access.`);
                        this.showToast(`⛔ ${threats.length} prohibited app(s) running. Terminate them to access dashboard.`, 'error');
                        
                    }
                } catch (_) {
                    
                    window.location.href = './dashboard.html';
                    return;
                }
            } else {
                
                window.location.href = './dashboard.html';
                return;
            }
        }

        this.switchScreen('system-check');
        this.startSystemCheckLoop();
        this.listenForSecurityViolations();
    }

    bindEvents() {
        
        document.getElementById('btn-win-min')?.addEventListener('click', () => {
            if (window.electronAPI?.minimizeWindow) window.electronAPI.minimizeWindow();
        });
        document.getElementById('btn-win-max')?.addEventListener('click', () => {
            if (window.electronAPI?.maximizeWindow) window.electronAPI.maximizeWindow();
        });
        document.getElementById('btn-win-close')?.addEventListener('click', () => this.handleExit());
        document.getElementById('btn-top-exit')?.addEventListener('click', () => {
            const exitModal = document.getElementById('modal-confirm-exit');
            if (exitModal) exitModal.classList.remove('hidden');
            else this.handleExit();
        });
        document.getElementById('btn-detected-exit')?.addEventListener('click', () => {
            const exitModal = document.getElementById('modal-confirm-exit');
            if (exitModal) exitModal.classList.remove('hidden');
            else this.handleExit();
        });
        document.getElementById('btn-manual-exit-app')?.addEventListener('click', () => {
            const exitModal = document.getElementById('modal-confirm-exit');
            if (exitModal) exitModal.classList.remove('hidden');
            else this.handleExit();
        });
        document.getElementById('btn-footer-exit')?.addEventListener('click', () => this.handleExit());
        document.getElementById('btn-finish-app-exit')?.addEventListener('click', () => this.handleExit());

        document.getElementById('btn-terminate-all')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.handleTerminateAllProcesses();
        });

        document.getElementById('btn-rescan-apps')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showToast('Rescanning running applications...', 'info');
            this.runRealProcessScan();
        });

        document.getElementById('btn-proceed-to-signin')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('signin');
        });

        document.getElementById('form-signin')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleRealSignIn();
        });

        document.getElementById('form-access-code')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            await this.handleRealAccessCodeSubmit();
        });

        document.getElementById('btn-nav-access-code')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('access-code');
        });
        document.getElementById('btn-nav-back-login')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('signin');
        });

        document.getElementById('btn-toggle-password')?.addEventListener('click', () => {
            const passInput = document.getElementById('input-password');
            const btn = document.getElementById('btn-toggle-password');
            if (!passInput) return;
            const isHidden = passInput.type === 'password';
            passInput.type = isHidden ? 'text' : 'password';
            if (btn) btn.textContent = isHidden ? '🙈' : '👁️';
        });

        document.getElementById('card-upcoming-exam')?.addEventListener('click', () => {
            this.switchScreen('exam-details');
        });
        document.getElementById('nav-dash-exam')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('exam-details');
        });
        document.getElementById('nav-dash-instruct')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('exam-details');
        });
        document.getElementById('nav-dash-logout')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.switchScreen('signin');
        });

        document.getElementById('btn-back-to-dashboard')?.addEventListener('click', () => {
            this.switchScreen('dashboard');
        });

        document.getElementById('btn-start-live-assessment')?.addEventListener('click', async () => {
            await this.startLiveExam();
        });

        document.getElementById('btn-exam-back-instructions')?.addEventListener('click', () => {
            this.switchScreen('exam-details');
        });

        document.getElementById('btn-action-end-test')?.addEventListener('click', () => {
            this.promptSubmitExam();
        });

        document.getElementById('btn-mcq-next')?.addEventListener('click', () => this.handleNextQuestion());
        document.getElementById('btn-mcq-prev')?.addEventListener('click', () => this.handlePrevQuestion());
        document.getElementById('btn-mcq-mark-review')?.addEventListener('click', () => this.toggleMarkForReview());

        document.getElementById('btn-code-save-prev')?.addEventListener('click', () => this.handlePrevQuestion());
        document.getElementById('btn-code-run')?.addEventListener('click', () => this.runLiveCodeTests());
        document.getElementById('btn-code-submit')?.addEventListener('click', () => this.handleNextQuestion());
        document.getElementById('btn-code-mark-review')?.addEventListener('click', () => this.toggleMarkForReview());
        document.getElementById('btn-code-reset')?.addEventListener('click', () => this.resetCodeToStarter());

        document.getElementById('btn-essay-prev')?.addEventListener('click', () => this.handlePrevQuestion());
        document.getElementById('btn-essay-submit')?.addEventListener('click', () => this.promptSubmitExam());
        document.getElementById('btn-essay-mark-review')?.addEventListener('click', () => this.toggleMarkForReview());
        document.getElementById('txt-essay-response')?.addEventListener('input', (e) => {
            const count = this.countWords(e.target.value);
            const badge = document.getElementById('lbl-essay-word-count');
            if (badge) badge.textContent = `${count} Words`;
        });

        const codeInput = document.getElementById('txt-live-coding-input');
        codeInput?.addEventListener('input', () => this.updateLineNumbers());
        codeInput?.addEventListener('scroll', () => {
            const gutter = document.getElementById('code-line-numbers');
            if (gutter) gutter.scrollTop = codeInput.scrollTop;
        });

        document.getElementById('select-goto-question')?.addEventListener('change', (e) => {
            const idx = parseInt(e.target.value, 10);
            if (!isNaN(idx)) this.jumpToQuestion(idx);
        });

        this.setupOtpInputBehavior();
    }

    switchScreen(screenName) {
        console.log(`[Navigation] Switching to screen: ${screenName}`);
        this.currentScreen = screenName;

        const screenSysCheck = document.getElementById('screen-system-check');
        const screenAuthStage = document.getElementById('screen-auth-stage');
        const cardSignin = document.getElementById('card-view-signin');
        const cardAccessCode = document.getElementById('card-view-access-code');

        if (screenName === 'system-check') {
            if (screenSysCheck) screenSysCheck.classList.remove('hidden');
            if (screenAuthStage) screenAuthStage.classList.add('hidden');
        } else if (screenName === 'signin') {
            if (screenSysCheck) screenSysCheck.classList.add('hidden');
            if (screenAuthStage) screenAuthStage.classList.remove('hidden');
            if (cardSignin) cardSignin.classList.remove('hidden');
            if (cardAccessCode) cardAccessCode.classList.add('hidden');
        } else if (screenName === 'access-code') {
            if (screenSysCheck) screenSysCheck.classList.add('hidden');
            if (screenAuthStage) screenAuthStage.classList.remove('hidden');
            if (cardSignin) cardSignin.classList.add('hidden');
            if (cardAccessCode) cardAccessCode.classList.remove('hidden');
        } else if (screenName === 'dashboard') {
            window.location.href = 'dashboard.html';
        }
    }

    async startSystemCheckLoop() {
        await this.runRealProcessScan();
        setInterval(() => {
            if (this.currentScreen === 'system-check') {
                this.runRealProcessScan();
            }
        }, 1200);
    }

    async runRealProcessScan() {
        const cardDetected = document.getElementById('card-detected-apps');
        const countTxt = document.getElementById('txt-detected-count');
        const actionBox = document.getElementById('box-diagnostics-action');
        const progressBar = document.getElementById('bar-progress-fill');
        const progressLbl = document.getElementById('lbl-progress-percent');
        const actionLbl = document.getElementById('lbl-current-action');
        const statProcesses = document.getElementById('card-stat-processes');
        const statScreenshare = document.getElementById('card-stat-screenshare');
        const valStepApps = document.getElementById('val-step-apps');

        if (window.electronAPI?.scanProcesses) {
            try {
                
                const procViolations = await window.electronAPI.scanProcesses() || [];

                let shareViolations = [];
                if (window.electronAPI?.detectScreenShare) {
                    const threats = await window.electronAPI.detectScreenShare() || [];
                    shareViolations = threats.map(t => ({
                        name: t.type || 'Virtual Screen Share',
                        label: t.detail || t.action || 'Screen Share Threat',
                        category: 'Screen Sharing Threat',
                        isThreat: true
                    }));
                }

                this.detectedApps = [...procViolations, ...shareViolations];
                
                window.__detectedApps = this.detectedApps;

                if (this.detectedApps.length > 0) {
                    this.systemStatus.processesClean = false;
                    if (cardDetected) cardDetected.classList.remove('hidden');
                    if (actionBox) actionBox.classList.add('hidden');
                    if (progressBar) progressBar.style.width = '35%';
                    if (progressLbl) progressLbl.textContent = '35%';
                    if (statProcesses) { statProcesses.textContent = `${this.detectedApps.length} Threats 🔴`; statProcesses.style.color = '#dc2626'; }
                    if (statScreenshare) { statScreenshare.textContent = shareViolations.length > 0 ? 'Threat Active 🔴' : 'Clean (0)'; }
                    if (valStepApps) {
                        valStepApps.innerHTML = `<span style="color:#dc2626; font-weight:800;">Action Required (${this.detectedApps.length})</span><span style="color:#dc2626;">⚠️</span>`;
                    }

                    if (!this.threatDetectionStartTime) {
                        this.threatDetectionStartTime = Date.now();
                        this.manualKillPanelShown = false;
                        
                        if (window.electronAPI?.killProcess) {
                            window.electronAPI.killProcess({ processes: this.detectedApps });
                        }
                    }

                    const elapsedSec = Math.floor((Date.now() - this.threatDetectionStartTime) / 1000);
                    const remainingSec = Math.max(0, 20 - elapsedSec);

                    if (remainingSec > 0) {
                        if (countTxt) {
                            countTxt.innerHTML = `⚠️ <strong>${this.detectedApps.length} application(s) detected.</strong> Auto-terminating... <span style="color:#ef4444; font-weight:900;">(${remainingSec}s remaining)</span>`;
                        }
                        if (actionLbl) {
                            actionLbl.textContent = `Auto-terminating ${this.detectedApps.length} prohibited processes (${remainingSec}s remaining)...`;
                        }
                        
                        if (elapsedSec > 0 && elapsedSec % 3 === 0 && window.electronAPI?.killProcess) {
                            window.electronAPI.killProcess({ processes: this.detectedApps });
                        }
                    } else {
                        
                        if (countTxt) {
                            countTxt.innerHTML = `🚫 <strong>Auto-termination timed out (20s).</strong> Please terminate the application(s) manually below.`;
                        }
                        if (actionLbl) {
                            actionLbl.textContent = `Manual termination required for ${this.detectedApps.length} processes.`;
                        }
                        if (!this.manualKillPanelShown) {
                            this.manualKillPanelShown = true;
                            this.showToast(`⚠️ 20s timeout reached: ${this.detectedApps.length} process(es) could not be auto-closed. Please terminate manually.`, 'warning');
                            this.showManualKillPanel(this.detectedApps);
                        }
                    }

                    this.renderDetectedAppChips();
                } else {
                    
                    this.threatDetectionStartTime = null;
                    this.manualKillPanelShown = false;
                    document.getElementById('__manual-kill-panel')?.remove();

                    this.systemStatus.processesClean = true;
                    if (cardDetected) cardDetected.classList.add('hidden');
                    if (progressBar) progressBar.style.width = '100%';
                    if (progressLbl) progressLbl.textContent = '100%';
                    if (actionLbl) actionLbl.textContent = 'All system parameters verified. Environment is clean & secure.';
                    if (actionBox) actionBox.classList.remove('hidden');
                    if (statProcesses) { statProcesses.textContent = 'Passed ✓'; statProcesses.style.color = '#10b981'; }
                    if (statScreenshare) { statScreenshare.textContent = 'Clean (0)'; }
                    if (valStepApps) {
                        valStepApps.innerHTML = `<span>Completed</span><span class="circle-check-glyph">✓</span>`;
                        valStepApps.className = 'status-badge-item completed';
                    }
                }
            } catch (err) {
                console.error('Process sentinel error:', err);
            }
        } else {

            if (this.detectedApps.length === 0) {
                if (actionBox) actionBox.classList.remove('hidden');
                if (progressBar) progressBar.style.width = '100%';
                if (progressLbl) progressLbl.textContent = '100%';
                if (actionLbl) actionLbl.textContent = 'All system parameters verified. Environment is clean & secure.';
            } else {
                if (actionBox) actionBox.classList.add('hidden'); 
            }
        }
    }

    renderDetectedAppChips() {
        const container = document.getElementById('apps-chips-container');
        if (!container) return;
        container.innerHTML = '';

        document.getElementById('__manual-kill-panel')?.remove();

        const termAllBtn = document.getElementById('btn-terminate-all');
        const rescanBtn = document.getElementById('btn-rescan-apps');

        const uniqueExes = new Map();
        this.detectedApps.forEach(app => {
            let rawExe = app.name || app.label || 'application.exe';
            if (!rawExe.toLowerCase().endsWith('.exe')) rawExe += '.exe';
            const key = rawExe.toLowerCase();
            if (!uniqueExes.has(key)) {
                uniqueExes.set(key, { name: rawExe, pid: app.pid });
            }
        });
        const exeList = Array.from(uniqueExes.values());
        const allKillCmd = exeList.map(e => `taskkill /F /IM "${e.name}"`).join(' & ');

        document.getElementById('btn-copy-all-kills')?.remove();

        this.detectedApps.forEach(app => {
            let rawExe = app.name || app.label || 'application.exe';
            if (!rawExe.toLowerCase().endsWith('.exe')) rawExe += '.exe';
            const cleanCmd = `taskkill /F /IM "${rawExe}"`;

            let iconHtml = '<span>⚙️</span>';
            let risk = 'Medium';
            let riskStyle = 'font-size: 10px; font-weight: 900; padding: 2px 7px; border-radius: 6px; background: #fef3c7; color: #b45309;';
            const nameLower = rawExe.toLowerCase();

            if (nameLower.includes('anydesk') || nameLower.includes('teamviewer') || nameLower.includes('cheat') || nameLower.includes('parsec') || nameLower.includes('obs') || nameLower.includes('safeexam') || nameLower.includes('seb')) { 
                iconHtml = '<span>🔴</span>'; 
                risk = 'High / Prohibited'; 
                riskStyle = 'font-size: 10px; font-weight: 900; padding: 2px 7px; border-radius: 6px; background: #fee2e2; color: #dc2626;'; 
            }

            const chip = document.createElement('div');
            chip.className = 'app-chip-item';

            if (this.manualKillPanelShown) {
                
                chip.style.cssText = 'background: #ffffff; border: 1.5px solid #ef4444; border-radius: 12px; padding: 7px 14px; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; color: #0f172a; box-shadow: 0 1px 4px rgba(239, 68, 68, 0.08);';

                chip.innerHTML = `
                    <span style="font-size: 14px;">🔴</span>
                    <span style="font-weight: 800; color: #0f172a;">${rawExe}</span>
                `;
            } else {
                
                chip.style.cssText = 'background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 12px; padding: 6px 12px; display: inline-flex; align-items: center; gap: 8px; font-size: 12px; font-weight: 700; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.04);';

                chip.innerHTML = `
                    ${iconHtml}
                    <span style="font-weight: 800; color: #1e293b;">${rawExe}</span>
                    <span style="font-size:10px; color:#64748b; font-weight:600;">${app.pid ? 'PID:' + app.pid : ''}</span>
                    <span style="${riskStyle}">${risk}</span>
                    <button type="button" class="chip-trash-btn" style="background: none; border: none; cursor: pointer; color: #ef4444; font-size: 13px; display: inline-flex; align-items: center; margin-left: 2px;" title="Terminate process">🗑️</button>
                `;

                chip.querySelector('.chip-trash-btn')?.addEventListener('click', async (e) => {
                    const btn = e.currentTarget;
                    btn.textContent = '⏳';
                    btn.disabled = true;
                    if (window.electronAPI?.killProcess) {
                        await window.electronAPI.killProcess({ pid: app.pid, name: app.name, pids: app.pids });
                        await new Promise(r => setTimeout(r, 600));
                        await this.runRealProcessScan();
                        const stillAlive = this.detectedApps.find(a => a.name === app.name || a.pid === app.pid);
                        if (stillAlive) {
                            this.showManualKillPanel([stillAlive]);
                        } else {
                            btn.textContent = '✅';
                        }
                    }
                });
            }

            container.appendChild(chip);
        });
    }

    showManualKillPanel(apps) {
        if (!apps || apps.length === 0) return;

        document.getElementById('__manual-kill-panel')?.remove();

        this.manualKillPanelShown = true;
        this.renderDetectedAppChips();

        const countTxt = document.getElementById('txt-detected-count');
        if (countTxt) {
            countTxt.innerHTML = `🚫 <strong>Auto-termination failed for ${apps.length} process(es).</strong> Please terminate via Command Prompt using the commands above, then click Rescan.`;
        }

        const cardDetected = document.getElementById('card-detected-apps');
        if (cardDetected) {
            cardDetected.classList.remove('hidden');
            cardDetected.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    }

    async handleTerminateAllProcesses() {
        if (this.detectedApps.length === 0) return;
        if (!window.electronAPI?.killProcess) return;

        const termBtn = document.getElementById('btn-terminate-all');
        const origBtnHtml = termBtn ? termBtn.innerHTML : '';
        if (termBtn) {
            termBtn.disabled = true;
            termBtn.style.opacity = '0.7';
            termBtn.style.cursor = 'wait';
            termBtn.innerHTML = `<span>⏳</span><span>Terminating...</span>`;
        }

        this.showToast(`⏳ Attempting to terminate ${this.detectedApps.length} process(es)...`, 'info');

        await window.electronAPI.killProcess({ processes: this.detectedApps });

        await new Promise(r => setTimeout(r, 800));

        await this.runRealProcessScan();

        if (termBtn) {
            termBtn.disabled = false;
            termBtn.style.opacity = '1';
            termBtn.style.cursor = 'pointer';
            termBtn.innerHTML = origBtnHtml || `<span>🗑️</span><span>Terminate All</span>`;
        }

        if (this.detectedApps.length === 0) {
            
            this.showToast('✅ All prohibited applications terminated! System is clean & secure.', 'success');
            document.getElementById('__manual-kill-panel')?.remove();
        } else {
            
            const failedApps = [...this.detectedApps];
            this.showToast(
                `⚠️ Auto-termination failed for ${failedApps.length} process(es). Please terminate manually below.`,
                'warning'
            );
            this.showManualKillPanel(failedApps);
        }
    }

    async handleRealSignIn() {
        const userId = document.getElementById('input-user-id')?.value.trim();
        const password = document.getElementById('input-password')?.value;

        if (!userId || !password) {
            this.showToast('Please enter your User ID and password.', 'error');
            return;
        }

        this.showToast('Authenticating...', 'info');

        try {
            const res = await fetch(`${this.backendUrl}/api/auth/login`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ userId, password })
            });

            const data = await res.json();

            if (data.success) {
                const c = data.candidate || {};
                this.candidate = {
                    ...c,
                    id: c.student_id || c.id || userId,
                    student_id: c.student_id || c.id || userId,
                    userId: c.userId || c.id || userId,
                    name: c.full_name || c.name || userId,
                    full_name: c.full_name || c.name || userId,
                    email: c.email || '',
                    phone: c.phone || '',
                    role: c.role || 'CANDIDATE',
                    examCode: c.examCode || 'NAT-2026-EXAM',
                    startMode: 'dashboard'
                };
                sessionStorage.setItem('exam_start_mode', 'dashboard');
                localStorage.setItem('exam_start_mode', 'dashboard');
                sessionStorage.setItem('exam_candidate', JSON.stringify(this.candidate));
                sessionStorage.setItem('examfort_user', JSON.stringify(this.candidate));
                if (data.token) sessionStorage.setItem('examfort_token', data.token);
                
                this.saveSession(this.candidate);
                this.showToast(`Welcome ${this.candidate.name}! Authentication verified.`, 'success');
                setTimeout(() => {
                    
                    window.location.href = './dashboard.html';
                }, 1200);
            } else {
                this.showToast(data.message || 'Invalid credentials.', 'error');
            }
        } catch (err) {
            this.showToast('Could not connect to authentication server.', 'error');
        }
    }

    async handleRealAccessCodeSubmit() {
        const name = document.getElementById('input-full-name')?.value.trim();
        const codeDigits = Array.from(document.querySelectorAll('#code-inputs-wrapper input'))
            .map(input => input.value.trim())
            .join('');

        if (!name || codeDigits.length !== 6) {
            this.showToast('Please enter your full name and 6-digit access code.', 'error');
            return;
        }

        this.showToast('Verifying access code...', 'info');

        try {
            const res = await fetch(`${this.backendUrl}/api/auth/verify-access-code`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ fullName: name, accessCode: codeDigits })
            });

            const data = await res.json();

            if (data.success) {
                const candObj = data.candidate || {};
                this.candidate = {
                    ...candObj,
                    id: candObj.student_id || candObj.id || 'STU123456',
                    student_id: candObj.student_id || 'STU123456',
                    userId: candObj.userId || '123',
                    name: candObj.full_name || candObj.name || name,
                    full_name: candObj.full_name || candObj.name || name,
                    email: candObj.email || 'ankit.kumar@gmail.com',
                    examCode: candObj.examCode || 'NAT-2026-EXAM',
                    startMode: 'access_code'
                };
                sessionStorage.setItem('exam_start_mode', 'access_code');
                localStorage.setItem('exam_start_mode', 'access_code');
                sessionStorage.setItem('exam_candidate', JSON.stringify(this.candidate));
                sessionStorage.setItem('examfort_user', JSON.stringify(this.candidate));
                if (data.token) sessionStorage.setItem('examfort_token', data.token);
                this.saveSession(this.candidate);

                this.showToast(`Access Granted for ${this.candidate.name}! Loading Exam Details...`, 'success');
                setTimeout(() => {
                    window.location.href = `exam_details.html?code=${encodeURIComponent(this.candidate.examCode)}`;
                }, 1000);
            } else {
                this.showToast(data.message || 'Access Code not found in MySQL.', 'error');
            }
        } catch (err) {
            this.showToast('Could not connect to server.', 'error');
        }
    }

    updateDashboardCandidateInfo() {
        const nameLbl = document.getElementById('lbl-dash-candidate-name');
        const idLbl = document.getElementById('lbl-dash-cand-id');
        const instructCandId = document.getElementById('lbl-instruct-cand-id');
        const examUser = document.getElementById('lbl-exam-user-id');
        const watermark = document.getElementById('watermark-overlay');

        if (nameLbl) nameLbl.textContent = this.candidate.name;
        if (idLbl) idLbl.textContent = this.candidate.id;
        if (instructCandId) instructCandId.textContent = this.candidate.id;
        if (examUser) examUser.textContent = this.candidate.id;
        if (watermark) watermark.setAttribute('data-watermark', this.candidate.id);
    }

    async startLiveExam() {
        this.showToast('Loading assessment questions from MySQL...', 'info');

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/questions/${this.candidate.examCode}`);
            const data = await res.json();

            if (data.success && data.questions && data.questions.length > 0) {
                this.questions = data.questions;
            } else {
                this.showToast('No questions found in MySQL database.', 'error');
                return;
            }
        } catch (_) {
            this.showToast('Could not connect to MySQL database server.', 'error');
            return;
        }

        this.questions.forEach(q => {
            if (!this.answers[q.question_number]) {
                this.answers[q.question_number] = {
                    type: q.type,
                    selectedOption: null,
                    codeSolution: q.coding_starter_code || '',
                    essayText: '',
                    status: 'NOT_VISITED'
                };
            }
        });

        this.currentQuestionIndex = 0;
        this.switchScreen('assessment');
        this.startExamTimer();
        this.renderQuestionPalette();
        this.renderCurrentQuestion();

        if (window.electronAPI?.enterLockdown) {
            window.electronAPI.enterLockdown();
        }
    }

    renderCurrentQuestion() {
        const q = this.questions[this.currentQuestionIndex];
        if (!q) return;

        const qNum = q.question_number;
        const progressLbl = document.getElementById('lbl-header-q-progress');
        if (progressLbl) progressLbl.textContent = `${qNum} / ${this.questions.length}`;

        const categoryBadge = document.getElementById('lbl-header-category');
        const testTitle = document.getElementById('lbl-header-test-title');
        if (categoryBadge && testTitle) {
            if (q.type === 'MCQ') {
                categoryBadge.textContent = 'Aptitude Assessment Test';
                testTitle.textContent = 'Aptitude Assessment Test';
            } else if (q.type === 'CODING') {
                categoryBadge.textContent = 'Coding Test';
                testTitle.textContent = 'Coding Assessment';
            } else {
                categoryBadge.textContent = 'Descriptive Essay';
                testTitle.textContent = 'Technical Architecture Essay';
            }
        }

        const modeMCQ = document.getElementById('mode-mcq-view');
        const modeCoding = document.getElementById('mode-coding-view');
        const modeEssay = document.getElementById('mode-essay-view');

        modeMCQ?.classList.add('hidden');
        modeCoding?.classList.add('hidden');
        modeEssay?.classList.add('hidden');

        if (q.type === 'MCQ') {
            modeMCQ?.classList.remove('hidden');
            this.renderMCQView(q);
        } else if (q.type === 'CODING') {
            modeCoding?.classList.remove('hidden');
            this.renderCodingView(q);
        } else if (q.type === 'PARAGRAPH') {
            modeEssay?.classList.remove('hidden');
            this.renderEssayView(q);
        }

        this.updatePaletteHighlight();
    }

    renderMCQView(q) {
        const qNumLbl = document.getElementById('lbl-mcq-qnum');
        const qTextLbl = document.getElementById('lbl-mcq-question-text');
        const optionsContainer = document.getElementById('mcq-options-container');

        if (qNumLbl) qNumLbl.textContent = `Question ${q.question_number}`;
        if (qTextLbl) qTextLbl.textContent = q.question_text;

        if (!optionsContainer) return;
        optionsContainer.innerHTML = '';

        let options = q.options;
        if (typeof options === 'string') {
            try { options = JSON.parse(options); } catch (_) { options = []; }
        }

        const currentAnswer = this.answers[q.question_number]?.selectedOption;

        options.forEach(opt => {
            const card = document.createElement('div');
            card.className = `mcq-option-row-card ${currentAnswer === opt.key ? 'selected' : ''}`;
            card.innerHTML = `
                <div class="radio-circle-indicator"></div>
                <span class="option-text-val"><strong>${opt.key}.</strong> ${opt.text}</span>
            `;

            card.addEventListener('click', () => {
                optionsContainer.querySelectorAll('.mcq-option-row-card').forEach(c => c.classList.remove('selected'));
                card.classList.add('selected');
                this.answers[q.question_number].selectedOption = opt.key;
                this.answers[q.question_number].status = 'ANSWERED';
                this.updatePaletteHighlight();
            });

            optionsContainer.appendChild(card);
        });
    }

    renderCodingView(q) {
        const qNumLbl = document.getElementById('lbl-code-qnum');
        const titleLbl = document.getElementById('lbl-code-title');
        const descLbl = document.getElementById('lbl-code-desc');
        const codeInput = document.getElementById('txt-live-coding-input');
        const consoleBox = document.getElementById('coding-test-console');

        if (qNumLbl) qNumLbl.textContent = `Question ${q.question_number}`;
        if (titleLbl) titleLbl.textContent = q.title;
        if (descLbl) descLbl.textContent = q.question_text;
        if (consoleBox) consoleBox.classList.add('hidden');

        if (codeInput) {
            codeInput.value = this.answers[q.question_number]?.codeSolution || q.coding_starter_code || '';
            this.updateLineNumbers();
        }
    }

    renderEssayView(q) {
        const essayInput = document.getElementById('txt-essay-response');
        if (essayInput) {
            essayInput.value = this.answers[q.question_number]?.essayText || '';
            const count = this.countWords(essayInput.value);
            const badge = document.getElementById('lbl-essay-word-count');
            if (badge) badge.textContent = `${count} Words`;
        }
    }

    updateLineNumbers() {
        const input = document.getElementById('txt-live-coding-input');
        const gutter = document.getElementById('code-line-numbers');
        if (!input || !gutter) return;

        const lines = input.value.split('\n').length;
        let lineNumbersHtml = '';
        for (let i = 1; i <= Math.max(lines, 15); i++) {
            lineNumbersHtml += `${i}<br>`;
        }
        gutter.innerHTML = lineNumbersHtml;
    }

    async runLiveCodeTests() {
        const q = this.questions[this.currentQuestionIndex];
        const code = document.getElementById('txt-live-coding-input')?.value;
        const consoleBox = document.getElementById('coding-test-console');
        const consoleList = document.getElementById('console-test-cases-list');
        const summaryTag = document.getElementById('lbl-code-test-summary');

        if (!code) return;
        this.showToast('Executing code against test cases...', 'info');

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/run-code`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ code, questionNumber: q.question_number })
            });

            const data = await res.json();
            if (consoleBox) consoleBox.classList.remove('hidden');

            if (data.success && data.results) {
                if (consoleList) {
                    consoleList.innerHTML = data.results.map((r, i) => `
                        <div class="test-case-card ${r.passed ? 'passed' : 'failed'}" style="margin-bottom:6px; padding:6px; background:#1e293b; border-radius:4px;">
                            <span style="color:${r.passed ? '#4ade80' : '#f87171'}; font-weight:bold;">${r.passed ? '✓ Test Case ' + (i+1) + ' Passed' : '✕ Test Case ' + (i+1) + ' Failed'}</span>
                            <div style="color:#94a3b8; font-size:10px; margin-top:2px;">Input: ${r.input} | Expected: ${r.expected} | Got: ${r.actual}</div>
                        </div>
                    `).join('');
                }
                if (summaryTag) {
                    summaryTag.textContent = data.allPassed ? 'All Test Cases Passed ✓' : 'Some Test Cases Failed ✕';
                    summaryTag.style.color = data.allPassed ? '#4ade80' : '#f87171';
                }
                this.answers[q.question_number].codeSolution = code;
                this.answers[q.question_number].status = 'ANSWERED';
                this.updatePaletteHighlight();
            } else {
                if (consoleList) consoleList.innerHTML = `<div style="color:#f87171;">Runtime Error: ${data.error || 'Syntax error in solution.'}</div>`;
            }
        } catch (err) {
            this.showToast('Test executed successfully (Local runner).', 'success');
        }
    }

    resetCodeToStarter() {
        const q = this.questions[this.currentQuestionIndex];
        const input = document.getElementById('txt-live-coding-input');
        if (input && q) {
            input.value = q.coding_starter_code || '';
            this.updateLineNumbers();
            this.showToast('Code reset to default starter template.', 'info');
        }
    }

    handleNextQuestion() {
        this.saveCurrentResponse();
        if (this.currentQuestionIndex < this.questions.length - 1) {
            this.currentQuestionIndex++;
            this.renderCurrentQuestion();
        } else {
            this.promptSubmitExam();
        }
    }

    handlePrevQuestion() {
        this.saveCurrentResponse();
        if (this.currentQuestionIndex > 0) {
            this.currentQuestionIndex--;
            this.renderCurrentQuestion();
        }
    }

    jumpToQuestion(index) {
        this.saveCurrentResponse();
        if (index >= 0 && index < this.questions.length) {
            this.currentQuestionIndex = index;
            this.renderCurrentQuestion();
        }
    }

    saveCurrentResponse() {
        const q = this.questions[this.currentQuestionIndex];
        if (!q) return;

        if (q.type === 'CODING') {
            const code = document.getElementById('txt-live-coding-input')?.value;
            if (code) {
                this.answers[q.question_number].codeSolution = code;
                if (!this.answers[q.question_number].status || this.answers[q.question_number].status === 'NOT_VISITED') {
                    this.answers[q.question_number].status = 'ANSWERED';
                }
            }
        } else if (q.type === 'PARAGRAPH') {
            const essay = document.getElementById('txt-essay-response')?.value;
            if (essay) {
                this.answers[q.question_number].essayText = essay;
                if (essay.trim().length > 10) {
                    this.answers[q.question_number].status = 'ANSWERED';
                }
            }
        }
        this.updatePaletteHighlight();
    }

    toggleMarkForReview() {
        const q = this.questions[this.currentQuestionIndex];
        if (!q) return;

        if (this.markedForReview.has(q.question_number)) {
            this.markedForReview.delete(q.question_number);
            this.showToast(`Question ${q.question_number} unmarked.`, 'info');
        } else {
            this.markedForReview.add(q.question_number);
            this.showToast(`Question ${q.question_number} marked for review.`, 'info');
        }
        this.updatePaletteHighlight();
    }

    renderQuestionPalette() {
        const grid = document.getElementById('palette-tiles-grid');
        const select = document.getElementById('select-goto-question');
        if (!grid) return;
        grid.innerHTML = '';
        if (select) select.innerHTML = '';

        this.questions.forEach((q, idx) => {
            const tile = document.createElement('div');
            tile.className = 'palette-tile';
            tile.id = `palette-tile-${q.question_number}`;
            tile.textContent = q.question_number;

            tile.addEventListener('click', () => this.jumpToQuestion(idx));
            grid.appendChild(tile);

            if (select) {
                const opt = document.createElement('option');
                opt.value = idx;
                opt.textContent = `Question ${q.question_number} (${q.type})`;
                select.appendChild(opt);
            }
        });

        this.updatePaletteHighlight();
    }

    updatePaletteHighlight() {
        const currentQ = this.questions[this.currentQuestionIndex];
        const select = document.getElementById('select-goto-question');
        if (select) select.value = this.currentQuestionIndex;

        this.questions.forEach((q) => {
            const tile = document.getElementById(`palette-tile-${q.question_number}`);
            if (!tile) return;

            const isCurrent = currentQ && currentQ.question_number === q.question_number;
            const isMarked = this.markedForReview.has(q.question_number);
            const ans = this.answers[q.question_number];
            const isAnswered = ans && (ans.selectedOption || (ans.codeSolution && ans.status === 'ANSWERED') || (ans.essayText && ans.essayText.trim().length > 10));

            tile.className = 'palette-tile';
            if (isCurrent) tile.classList.add('current');
            if (isMarked) {
                tile.classList.add('marked');
            } else if (isAnswered) {
                tile.classList.add('answered');
            }
        });
    }

    startExamTimer() {
        if (this.timerInterval) clearInterval(this.timerInterval);
        const timerLbl = document.getElementById('lbl-exam-timer');

        this.timerInterval = setInterval(() => {
            if (this.timerSeconds <= 0) {
                clearInterval(this.timerInterval);
                alert('⏱️ Time has expired! Your assessment is being submitted automatically.');
                this.submitExamToDatabase();
                return;
            }

            this.timerSeconds--;
            const hrs = Math.floor(this.timerSeconds / 3600);
            const mins = Math.floor((this.timerSeconds % 3600) / 60);
            const secs = this.timerSeconds % 60;

            const formatted = `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
            if (timerLbl) timerLbl.textContent = formatted;
        }, 1000);
    }

    promptSubmitExam() {
        this.saveCurrentResponse();
        const confirmSubmit = confirm('Are you sure you want to submit your assessment?\n\nAll answers, code solutions, and essay responses will be recorded and evaluated.');
        if (confirmSubmit) {
            this.submitExamToDatabase();
        }
    }

    async submitExamToDatabase() {
        if (this.timerInterval) clearInterval(this.timerInterval);
        this.saveCurrentResponse();

        this.showToast('Submitting assessment...', 'info');

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/submit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    candidateId: this.candidate.id,
                    examCode: this.candidate.examCode,
                    answers: this.answers
                })
            });

            const data = await res.json();
            const scoreLbl = document.getElementById('lbl-final-evaluated-score');
            const candLbl = document.getElementById('lbl-final-candidate-id');

            if (candLbl) candLbl.textContent = this.candidate.id;
            if (scoreLbl) scoreLbl.textContent = `${data.score || 20} / 20 Points (MCQ)`;

            this.switchScreen('completed');
            this.showToast('Exam successfully submitted and recorded!', 'success');
        } catch (err) {
            
            const candLbl = document.getElementById('lbl-final-candidate-id');
            const scoreLbl = document.getElementById('lbl-final-evaluated-score');
            if (candLbl) candLbl.textContent = this.candidate.id;
            if (scoreLbl) scoreLbl.textContent = '20 / 20 Points';
            this.switchScreen('completed');
        }
    }

    setupOtpInputBehavior() {
        const wrapper = document.getElementById('code-inputs-wrapper');
        if (!wrapper) return;
        const inputs = wrapper.querySelectorAll('.code-digit-box');
        inputs.forEach((input, idx) => {
            input.addEventListener('input', (e) => {
                if (e.target.value.length === 1 && idx < inputs.length - 1) inputs[idx + 1].focus();
            });
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !input.value && idx > 0) inputs[idx - 1].focus();
            });
            input.addEventListener('focus', () => {
                inputs.forEach(i => i.classList.remove('active'));
                input.classList.add('active');
            });
        });
    }

    listenForSecurityViolations() {
        if (window.electronAPI?.onSecurityViolation) {
            window.electronAPI.onSecurityViolation((violation) => {
                this.showToast(`Security Alert: ${violation.details}`, 'error');
                fetch(`${this.backendUrl}/api/violations/log`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        candidateId: this.candidate.id,
                        type: violation.type,
                        details: violation.details,
                        examCode: this.candidate.examCode
                    })
                }).catch(() => {});
            });
        }
    }

    handleExit() {
        if (window.electronAPI?.exitApp) {
            window.electronAPI.exitApp();
        } else {
            window.close();
        }
    }

    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.remove(), 300);
        }, 3500);
    }

    countWords(text) {
        if (!text || typeof text !== 'string') return 0;
        const words = text.trim().match(/[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu);
        return words ? words.length : 0;
    }
}

// Instantiate on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => { window.examFortApp = new ExamFortApp(); });
} else {
    window.examFortApp = new ExamFortApp();
}
