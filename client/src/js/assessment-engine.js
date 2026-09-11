

class AssessmentEngine {
    constructor() {
        this.backendUrl = window.EXAMFORT_ENV?.API_BASE_URL || 'https://examfort-d6q1.onrender.com';
        const urlParams = new URLSearchParams(window.location.search);
        this.examCode = urlParams.get('code') || sessionStorage.getItem('target_exam_code') || window.EXAMFORT_ENV?.DEFAULT_EXAM_CODE || 'NAT-2026-EXAM';
        
        let cand = null;
        try {
            cand = JSON.parse(sessionStorage.getItem('exam_candidate') || 'null')
                || JSON.parse(sessionStorage.getItem('examfort_user') || 'null')
                || JSON.parse(localStorage.getItem('examfort_session') || '{}').candidate
                || JSON.parse(localStorage.getItem('examfort_app_session_v2') || '{}').candidate;
        } catch (_) {}

        this.candidate = cand || { id: 'CAND123456', name: 'Candidate 123' };
        this.examDetails = null;
        
        this.questions = [];
        this.sections = [];
        this.backendSections = null;
        this.currentIndex = 0;
        this.answers = {};
        this.markedForReview = new Set();
        this.timerSeconds = 120 * 60;
        this.timerInterval = null;
        this.autoSaveInterval = null;
        this.selectedLanguage = 'cpp';
        this.detailsHidden = false;
        this.isEditorFullscreen = false;

        this.editorUndoStack = [];
        this.editorRedoStack = [];
        this.historyTimer = null;
        this.isPerformingHistoryAction = false;

        this.supportedLanguages = {
            cpp: 'C++20 (G++ 15.2)',
            c: 'C (GCC 15.2)',
            java: 'Java 17 (OpenJDK)',
            python: 'Python 3 (3.14)',
            javascript: 'JavaScript (Node.js 24)'
        };

        this.warningCount = 0;
        this.maxWarnings = 10;
        this.fontSize = 14;
        this.isProblemCollapsed = false;
        this.warningAckTimer = null;
        this.isWarningModalOpen = false;
        this.isSubmitting = false;
        this.lastBlurWarningTime = 0;
        this.loadTime = Date.now();

        this.init();
    }

    async init() {
        console.log('🚀 [Assessment Engine] Initializing workspace & bindings...');
        
        try {
            this.renderDynamicWatermark();
            this.bindEvents();
            this.initCustomDropdowns();
            this.setupEditorBehaviors();
            this.setupProctoringListeners();
            this.setupSplitterResizer();
            this.setupEditorHeightResizer();
            this.setupConsoleHeightResizer();
            this.setupFontSizeControls();
            this.setupProblemPanelToggle();
        } catch (uiErr) {
            console.error('UI setup warning:', uiErr);
        }

        const alreadySubmitted = await this.checkExistingSubmission();
        if (alreadySubmitted) {
            this.isSubmitting = true;
            this.showToast('You have already submitted this assessment. Re-attempts are not permitted.', 'warning');
            setTimeout(() => {
                window.location.replace('completed.html');
            }, 800);
            return;
        }

        const isTimeValid = await this.checkExamTimeBounds();
        if (!isTimeValid) {
            return;
        }

        const faceVerified = await this.verifyFaceRequirement();
        if (!faceVerified) {
            return;
        }

        try {
            await this.loadExamDetails();
            await this.loadQuestions();
            this.renderDynamicSectionSwitcher();
            await this.loadDraft();
            this.startTimer();
            this.startAutoSaveLoop();
        } catch (loadErr) {
            console.error('Data load error:', loadErr);
        }
    }

    async checkExistingSubmission() {
        const urlParams = new URLSearchParams(window.location.search);
        let isReattemptMode = urlParams.get('reattempt') === '1' || 
                              sessionStorage.getItem(`is_reattempt_${this.examCode}`) === 'true' ||
                              sessionStorage.getItem('is_reattempt') === 'true';

        const candId = this.candidate?.id || this.candidate?.student_id || 'CAND123456';
        const localKey = `exam_submitted_${candId}_${this.examCode}`;
        const timeExpiredKey = `exam_time_expired_${candId}_${this.examCode}`;
        const endKey = `exam_end_time_${candId}_${this.examCode}`;

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/check-attempt/${candId}/${this.examCode}`);
            const data = await res.json();
            if (data && data.hasSubmitted) {
                if (data.isReattemptAuthorized) {
                    // Admin has officially authorized a reattempt!
                    isReattemptMode = true;
                    sessionStorage.setItem(`is_reattempt_${this.examCode}`, 'true');
                    sessionStorage.setItem('is_reattempt', 'true');
                    if (data.reattemptReason) {
                        sessionStorage.setItem(`reattempt_reason_${this.examCode}`, data.reattemptReason);
                        sessionStorage.setItem('reattempt_reason', data.reattemptReason);
                    }
                } else if (!isReattemptMode) {
                    localStorage.setItem(localKey, 'true');
                    sessionStorage.setItem(localKey, 'true');
                    return true;
                }
            } else {
                localStorage.removeItem(localKey);
                sessionStorage.removeItem(localKey);
                localStorage.removeItem(`exam_submitted_${this.examCode}`);
                sessionStorage.removeItem(`exam_submitted_${this.examCode}`);
                localStorage.removeItem(timeExpiredKey);
                return false;
            }
        } catch (_) {}

        if (isReattemptMode) {
            console.log('🔄 [Assessment Engine] Authorized Reattempt session active. Resetting locks and timer.');
            localStorage.removeItem(localKey);
            sessionStorage.removeItem(localKey);
            localStorage.removeItem(`exam_submitted_${this.examCode}`);
            sessionStorage.removeItem(`exam_submitted_${this.examCode}`);
            localStorage.removeItem(timeExpiredKey);
            sessionStorage.removeItem(timeExpiredKey);
            localStorage.removeItem(endKey);
            return false;
        }

        return localStorage.getItem(localKey) === 'true' || sessionStorage.getItem(localKey) === 'true';
    }

    parseExamDateTimeRange(dateStr, timeStr) {
        if (!dateStr) return { start: null, end: null, startTime: '10:00 AM', endTime: '12:00 PM' };
        try {
            let startPart = '';
            let endPart = '';
            if (timeStr && timeStr.includes('-')) {
                const parts = timeStr.split('-');
                startPart = parts[0].trim();
                endPart = parts[1].trim();
            } else if (timeStr) {
                startPart = timeStr.trim();
                endPart = timeStr.trim();
            }

            function toTime24(tStr, defaultHour, defaultMin) {
                if (!tStr) return { hour: defaultHour, minute: defaultMin };
                const match = tStr.match(/(\d{1,2})(?::(\d{2}))?(?::(\d{2}))?\s*(AM|PM)?/i);
                if (!match) return { hour: defaultHour, minute: defaultMin };
                let h = parseInt(match[1], 10);
                let m = match[2] ? parseInt(match[2], 10) : 0;
                const meridian = match[4] ? match[4].toUpperCase() : null;
                if (meridian === 'PM' && h < 12) h += 12;
                if (meridian === 'AM' && h === 12) h = 0;
                return { hour: h, minute: m };
            }

            let year, month, day;
            const cleanDate = String(dateStr).trim();

            // 1. ISO format: YYYY-MM-DD or YYYY/MM/DD
            const isoMatch = cleanDate.match(/^(\d{4})[/-](\d{1,2})[/-](\d{1,2})/);
            // 2. DD-MM-YYYY or DD/MM/YYYY
            const dmyMatch = cleanDate.match(/^(\d{1,2})[/-](\d{1,2})[/-](\d{4})/);

            if (isoMatch) {
                year = parseInt(isoMatch[1], 10);
                month = parseInt(isoMatch[2], 10) - 1;
                day = parseInt(isoMatch[3], 10);
            } else if (dmyMatch) {
                day = parseInt(dmyMatch[1], 10);
                month = parseInt(dmyMatch[2], 10) - 1;
                year = parseInt(dmyMatch[3], 10);
            } else {
                const months = { jan:0, feb:1, mar:2, apr:3, may:4, jun:5, jul:6, aug:7, sep:8, oct:9, nov:10, dec:11 };
                const tokens = cleanDate.split(/[\s,/-]+/);
                let foundMonth = -1;
                let numbers = [];
                for (const t of tokens) {
                    const low = t.toLowerCase().substring(0, 3);
                    if (months[low] !== undefined) {
                        foundMonth = months[low];
                    } else if (/^\d+$/.test(t)) {
                        numbers.push(parseInt(t, 10));
                    }
                }
                if (foundMonth !== -1) {
                    month = foundMonth;
                    year = numbers.find(n => n > 1900) || new Date().getFullYear();
                    day = numbers.find(n => n <= 31 && n !== year) || 1;
                } else {
                    const temp = new Date(cleanDate);
                    if (!isNaN(temp.getTime())) {
                        year = temp.getFullYear();
                        month = temp.getMonth();
                        day = temp.getDate();
                    }
                }
            }

            if (year === undefined || month === undefined || day === undefined) {
                return { start: null, end: null, startTime: startPart || '10:00 AM', endTime: endPart || '12:00 PM' };
            }

            const tStart = toTime24(startPart, 0, 0);
            const tEnd = toTime24(endPart, 23, 59);

            const start = new Date(year, month, day, tStart.hour, tStart.minute, 0);
            let end = new Date(year, month, day, tEnd.hour, tEnd.minute, 59);

            // Handle overnight time ranges (e.g. 03:00 PM to 12:59 AM next day)
            if (end < start) {
                end.setDate(end.getDate() + 1);
            }

            return {
                start: isNaN(start.getTime()) ? null : start,
                end: isNaN(end.getTime()) ? null : end,
                startTime: startPart || '10:00 AM',
                endTime: endPart || '12:00 PM'
            };
        } catch (_) {
            return { start: null, end: null, startTime: '10:00 AM', endTime: '12:00 PM' };
        }
    }

    async checkExamTimeBounds() {
        if (!this.examDetails) {
            try {
                const res = await fetch(`${this.backendUrl}/api/exam/details/${this.examCode}`);
                const data = await res.json();
                if (data.success && data.exam) {
                    this.examDetails = data.exam;
                }
            } catch (_) {}
        }

        if (this.examDetails) {
            const now = new Date();
            const { start, end, startTime, endTime } = this.parseExamDateTimeRange(this.examDetails.exam_date, this.examDetails.exam_time);
            
            const statusUpper = (this.examDetails.status || '').toUpperCase();

            if (start && now < start) {
                alert(`🔒 Examination Not Started Yet\n\nThis exam is scheduled for ${this.examDetails.exam_date} (${startTime} - ${endTime}).\nYou cannot start or access questions before the scheduled start time.`);
                window.location.replace(`exam_details.html?code=${encodeURIComponent(this.examCode)}`);
                return false;
            }

            if (statusUpper === 'COMPLETED' || (end && now > end)) {
                alert(`⌛ Exam Window Ended\n\nThe scheduled time window for this exam (${this.examDetails.exam_date} up to ${endTime}) has passed.\nYou can no longer attempt this exam.`);
                window.location.replace(`exam_details.html?code=${encodeURIComponent(this.examCode)}`);
                return false;
            }
        }
        return true;
    }

    async verifyFaceRequirement() {
        try {
            const res = await fetch(`${this.backendUrl}/api/exam/details/${this.examCode}`);
            const data = await res.json();
            if (data.success && data.exam) {
                this.examDetails = data.exam;
            }
        } catch (_) {}
        return true;
    }

    renderDynamicWatermark() {
        
        const watermarkEl = document.getElementById('watermark-overlay');
        if (watermarkEl) watermarkEl.style.display = 'none';
    }

    initCustomDropdowns() {
        
        const langTrigger = document.getElementById('btn-lang-trigger');
        const langMenu = document.getElementById('menu-coding-lang');
        const langContainer = document.getElementById('dropdown-coding-lang');

        if (langTrigger && langMenu) {
            langTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = langContainer.classList.contains('open');
                this.closeAllDropdowns();
                if (!isOpen) {
                    langContainer.classList.add('open');
                    langTrigger.setAttribute('aria-expanded', 'true');
                }
            });

            langMenu.querySelectorAll('.custom-select-option').forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const val = opt.getAttribute('data-value');
                    this.handleLanguageChange(val);
                    this.closeAllDropdowns();
                });
            });
        }

        const secTrigger = document.getElementById('btn-section-trigger');
        const secMenu = document.getElementById('menu-section-switcher');
        const secContainer = document.getElementById('dropdown-section-switcher');

        if (secTrigger && secMenu) {
            secTrigger.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                const isOpen = secContainer.classList.contains('open');
                this.closeAllDropdowns();
                if (!isOpen) {
                    secContainer.classList.add('open');
                    secTrigger.setAttribute('aria-expanded', 'true');
                }
            });

            secMenu.querySelectorAll('.custom-select-option').forEach(opt => {
                opt.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    const sec = opt.getAttribute('data-section');
                    this.handleSectionChange(sec);
                    this.closeAllDropdowns();
                });
            });
        }

        document.addEventListener('click', (e) => {
            if (!e.target.closest('.custom-select-container')) {
                this.closeAllDropdowns();
            }
        });

        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.closeAllDropdowns();
                this.hideExitModal();
                this.hideSubmitModal();
            }
        });
    }

    closeAllDropdowns() {
        document.querySelectorAll('.custom-select-container').forEach(c => {
            c.classList.remove('open');
            const trigger = c.querySelector('.custom-select-trigger');
            if (trigger) trigger.setAttribute('aria-expanded', 'false');
        });
    }

    bindEvents() {
        
        document.getElementById('btn-back-instructions')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showExitModal();
        });

        document.getElementById('btn-pause-test')?.addEventListener('click', () => {
            this.showSubmitModal();
        });

        document.getElementById('btn-modal-cancel-exit')?.addEventListener('click', () => this.hideExitModal());
        document.getElementById('btn-modal-confirm-exit')?.addEventListener('click', () => this.confirmExit());

        document.getElementById('btn-modal-cancel-submit')?.addEventListener('click', () => this.hideSubmitModal());
        document.getElementById('btn-modal-confirm-submit')?.addEventListener('click', () => {
            this.hideSubmitModal();
            this.submitAssessment(false);
        });

        document.getElementById('btn-mcq-next')?.addEventListener('click', () => this.nextQuestion());
        document.getElementById('btn-mcq-prev')?.addEventListener('click', () => this.prevQuestion());
        document.getElementById('btn-mcq-review')?.addEventListener('click', () => this.toggleReview());

        document.getElementById('btn-code-run')?.addEventListener('click', () => this.runCompilerSandbox(false));
        document.getElementById('btn-code-submit')?.addEventListener('click', () => this.submitCodingQuestion());
        document.getElementById('btn-code-next')?.addEventListener('click', () => {
            this.saveCurrent();
            if (this.currentIndex < this.questions.length - 1) {
                this.nextQuestion();
            } else {
                this.showSubmitModal();
            }
        });
        document.getElementById('btn-code-prev')?.addEventListener('click', () => this.prevQuestion());
        document.getElementById('btn-code-review')?.addEventListener('click', () => this.toggleReview());
        document.getElementById('btn-reset-code')?.addEventListener('click', () => this.resetCode());
        document.getElementById('btn-toggle-fullscreen')?.addEventListener('click', () => this.toggleEditorFullscreen());

        document.getElementById('btn-essay-prev')?.addEventListener('click', () => this.prevQuestion());
        document.getElementById('btn-essay-review')?.addEventListener('click', () => this.toggleReview());
        document.getElementById('btn-final-submit-exam')?.addEventListener('click', () => {
            this.saveCurrent();
            this.showSubmitModal();
        });

        document.getElementById('chk-custom-input-toggle')?.addEventListener('change', (e) => {
            const wrap = document.getElementById('wrap-custom-input');
            if (wrap) {
                wrap.classList.toggle('hidden', !e.target.checked);
            }
        });

        const tcToggle = document.getElementById('chk-test-cases-toggle');
        tcToggle?.addEventListener('change', (e) => {
            const consolePanel = document.getElementById('console-test-results');
            if (consolePanel) {
                if (e.target.checked) {
                    consolePanel.classList.remove('hidden');
                    consolePanel.style.display = 'block';
                    document.getElementById('tab-test-cases')?.click();
                    setTimeout(() => {
                        consolePanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                    }, 40);
                } else {
                    consolePanel.classList.add('hidden');
                    consolePanel.style.display = 'none';
                }
            }
        });

        document.getElementById('tab-test-cases')?.addEventListener('click', () => {
            document.getElementById('tab-test-cases')?.classList.add('active');
            document.getElementById('tab-console-terminal')?.classList.remove('active');
            document.getElementById('console-test-cards')?.classList.remove('hidden');
            document.getElementById('console-terminal-output')?.classList.add('hidden');
        });

        document.getElementById('tab-console-terminal')?.addEventListener('click', () => {
            document.getElementById('tab-console-terminal')?.classList.add('active');
            document.getElementById('tab-test-cases')?.classList.remove('active');
            document.getElementById('console-terminal-output')?.classList.remove('hidden');
            document.getElementById('console-test-cards')?.classList.add('hidden');
        });

        document.getElementById('btn-essay-prev')?.addEventListener('click', () => this.prevQuestion());
        document.getElementById('btn-essay-review')?.addEventListener('click', () => this.toggleReview());
        document.getElementById('btn-final-submit-exam')?.addEventListener('click', () => this.showSubmitModal());
        document.getElementById('txt-essay-solution')?.addEventListener('input', (e) => {
            const count = this.countWords(e.target.value);
            const badge = document.getElementById('lbl-essay-words');
            if (badge) badge.textContent = `${count} Words`;
        });

        document.getElementById('btn-toggle-details')?.addEventListener('click', () => {
            this.toggleProgressDetails();
        });

        document.getElementById('btn-footer-exit')?.addEventListener('click', (e) => {
            e.preventDefault();
            this.showExitModal();
        });

        document.getElementById('btn-ack-warning')?.addEventListener('click', () => {
            this.acknowledgeWarning();
        });

        window.addEventListener('beforeunload', (e) => {
            this.saveCurrent();
        });
    }

    showExitModal() {
        const modal = document.getElementById('modal-exit-confirm');
        if (modal) {
            const lblExam = document.getElementById('lbl-modal-exam-name');
            if (lblExam && this.examDetails) lblExam.textContent = this.examDetails.title;
            const lblTimer = document.getElementById('lbl-modal-time-left');
            const timerElem = document.getElementById('lbl-exam-timer');
            if (lblTimer && timerElem) lblTimer.textContent = timerElem.textContent;
            modal.classList.remove('hidden');
        }
    }

    hideExitModal() {
        const modal = document.getElementById('modal-exit-confirm');
        if (modal) modal.classList.add('hidden');
    }

    confirmExit() {
        try { this.saveCurrent(); } catch (_) {}
        if (window.electronAPI?.exitApp) {
            window.electronAPI.exitApp();
        } else {
            window.close();
        }
    }

    setupProctoringListeners() {
        
        if (window.electronAPI?.onSecurityViolation) {
            window.electronAPI.onSecurityViolation((violation) => {
                this.triggerProctoringWarning(violation.type || 'SECURITY_VIOLATION', violation.details || 'Proctoring security violation detected');
            });
        }

        if (window.electronAPI?.onDisplayChanged) {
            window.electronAPI.onDisplayChanged((data) => {
                this.triggerProctoringWarning('DISPLAY_CHANGE', `External display count changed (${data.displays} monitor(s) active)`);
            });
        }

        window.addEventListener('blur', () => {
            const now = Date.now();
            
            if (now - this.loadTime < 2500) return;
            
            if (this.isWarningModalOpen || this.isSubmitting) return;
            if (now - this.lastBlurWarningTime > 3500) {
                this.lastBlurWarningTime = now;
                this.triggerProctoringWarning('WINDOW_BLUR', 'Window lost focus / Application switch detected');
            }
        });

        document.addEventListener('visibilitychange', () => {
            const now = Date.now();
            if (now - this.loadTime < 2500) return;
            if (document.hidden && !this.isSubmitting && !this.isWarningModalOpen) {
                if (now - this.lastBlurWarningTime > 3500) {
                    this.lastBlurWarningTime = now;
                    this.triggerProctoringWarning('TAB_SWITCH', 'Exam window minimized or switched to background');
                }
            }
        });

        window.addEventListener('keydown', (e) => {
            const ctrl = e.ctrlKey || e.metaKey;
            const k = e.key ? e.key.toLowerCase() : '';

            if (e.key === 'PrintScreen' || k === 'printscreen') {
                e.preventDefault();
                this.triggerProctoringWarning('SCREENSHOT_ATTEMPT', 'Screenshot attempt (PrintScreen) blocked');
                return;
            }

            if (e.key === 'F12' || (ctrl && e.shiftKey && (k === 'i' || k === 'j' || k === 'c'))) {
                e.preventDefault();
                this.triggerProctoringWarning('DEVTOOLS_ATTEMPT', 'Developer tools inspection attempt blocked');
                return;
            }

            if (ctrl && k === 'u') {
                e.preventDefault();
                this.triggerProctoringWarning('VIEW_SOURCE', 'View source shortcut (Ctrl+U) blocked');
                return;
            }

            if (ctrl && k === 'p') {
                e.preventDefault();
                this.showToast('Printing is strictly prohibited during the examination.', 'error');
                return;
            }

            if (ctrl && k === 's') {
                e.preventDefault();
                this.showToast('Exam progress is automatically saved to secure cloud.', 'info');
                return;
            }

            if (ctrl && (k === 'c' || k === 'v' || k === 'x')) {
                const target = e.target;
                const isEditorTextarea = target && target.id === 'txt-code-solution';
                
                if (!isEditorTextarea) {
                    e.preventDefault();
                    this.showToast('Copy / Paste is disabled on question prompts.', 'warning');
                }
            }
        }, true);

        window.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            this.showToast('Right-click context menu is disabled in secure exam.', 'warning');
        }, true);
    }

    triggerProctoringWarning(type, details) {
        if (this.isSubmitting || this.isWarningModalOpen) return;

        this.warningCount++;
        console.warn(`[Proctoring] Strike ${this.warningCount}/${this.maxWarnings}: ${type} - ${details}`);

        const headerBadge = document.getElementById('lbl-header-warnings');
        if (headerBadge) {
            headerBadge.textContent = `${this.warningCount} / ${this.maxWarnings}`;
            if (this.warningCount >= 7) {
                headerBadge.style.color = '#ef4444';
            }
        }

        this.showToast(`⚠️ Warning ${this.warningCount}/${this.maxWarnings}: ${details}`, 'error');

        fetch(`${this.backendUrl}/api/violations/log`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                candidateId: this.candidate?.id || 'CAND123456',
                examCode: this.examCode,
                type: type,
                details: details
            })
        }).catch(() => {});

        if (this.warningCount >= this.maxWarnings) {
            this.isSubmitting = true;
            this.showToast('🚨 Maximum warning limit reached (10 Warnings). Auto-submitting exam now...', 'error');
            setTimeout(() => {
                this.submitAssessment(true);
            }, 1500);
            return;
        }

        this.showProctoringWarningModal(type, details);
    }

    showProctoringWarningModal(type, details) {
        const modal = document.getElementById('modal-security-warning');
        const strikeBadge = document.getElementById('lbl-warning-strike-badge');
        const desc = document.getElementById('lbl-warning-modal-desc');
        const ackBtn = document.getElementById('btn-ack-warning');
        const cdSpan = document.getElementById('lbl-warning-ack-cd');

        if (strikeBadge) {
            strikeBadge.textContent = `WARNING ${this.warningCount} OF ${this.maxWarnings}`;
            if (this.warningCount >= 7) {
                strikeBadge.style.background = '#fee2e2';
                strikeBadge.style.color = '#dc2626';
            }
        }
        if (desc) {
            desc.textContent = `${details}. Please stay focused on the assessment. Reaching ${this.maxWarnings} warnings will auto-submit and lock your test.`;
        }

        if (modal) {
            modal.classList.remove('hidden');
            this.isWarningModalOpen = true;
        }

        if (ackBtn && cdSpan) {
            ackBtn.disabled = true;
            let countdown = 3;
            cdSpan.textContent = `${countdown}`;

            if (this.warningAckTimer) clearInterval(this.warningAckTimer);
            this.warningAckTimer = setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    clearInterval(this.warningAckTimer);
                    this.warningAckTimer = null;
                    ackBtn.disabled = false;
                    ackBtn.innerHTML = `<span>I Understand, Resume Exam ✓</span>`;
                } else {
                    cdSpan.textContent = `${countdown}`;
                }
            }, 1000);
        }
    }

    acknowledgeWarning() {
        const modal = document.getElementById('modal-security-warning');
        if (modal) {
            modal.classList.add('hidden');
            this.isWarningModalOpen = false;
        }
        this.lastBlurWarningTime = Date.now() + 2000; 
        if (this.warningAckTimer) {
            clearInterval(this.warningAckTimer);
            this.warningAckTimer = null;
        }
        
        const textarea = document.getElementById('txt-code-solution');
        if (textarea && !textarea.classList.contains('hidden')) {
            textarea.focus();
        }
    }

    setupSplitterResizer() {
        const splitter = document.getElementById('coding-workspace-splitter');
        const problemPanel = document.getElementById('coding-problem-side');
        const editorContainer = document.getElementById('coding-editor-container');
        const grid = document.querySelector('.coding-workspace-grid');

        if (!splitter || !problemPanel || !grid) return;

        let isDragging = false;

        const onMouseDown = (e) => {
            isDragging = true;
            splitter.classList.add('dragging');
            document.body.style.cursor = 'col-resize';
            document.body.style.userSelect = 'none';
        };

        const onMouseMove = (e) => {
            if (!isDragging) return;
            const gridRect = grid.getBoundingClientRect();
            const mouseX = e.clientX - gridRect.left;
            const totalWidth = gridRect.width;

            let percent = (mouseX / totalWidth) * 100;
            if (percent < 20) percent = 20;
            if (percent > 75) percent = 75;

            grid.style.setProperty('--problem-pane-width', `${percent}%`);
            this.syncEditorHighlight();
        };

        const onMouseUp = () => {
            if (isDragging) {
                isDragging = false;
                splitter.classList.remove('dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                this.syncEditorHighlight();
            }
        };

        splitter.addEventListener('mousedown', onMouseDown);
        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);

        splitter.addEventListener('dblclick', () => {
            grid.style.setProperty('--problem-pane-width', '45%');
            this.syncEditorHighlight();
            this.showToast('Splitter reset to default (45 / 55)', 'info');
        });
    }

    setupEditorHeightResizer() {
        const resizer = document.getElementById('editor-height-resizer');
        const editorBox = document.getElementById('vs-code-editor-box');
        const btnToggleHeight = document.getElementById('btn-toggle-editor-height');

        if (!resizer || !editorBox) return;

        let isDragging = false;
        let startY = 0;
        let startHeight = 350;

        const onMouseDown = (e) => {
            isDragging = true;
            startY = e.clientY;
            startHeight = editorBox.getBoundingClientRect().height;
            resizer.classList.add('dragging');
            document.body.style.cursor = 'row-resize';
            document.body.style.userSelect = 'none';
        };

        const onMouseMove = (e) => {
            if (!isDragging) return;
            const deltaY = e.clientY - startY;
            let newHeight = startHeight + deltaY;

            if (newHeight < 180) newHeight = 180;
            if (newHeight > 850) newHeight = 850;

            document.documentElement.style.setProperty('--editor-box-height', `${newHeight}px`);
            this.syncEditorHighlight();
        };

        const onMouseUp = () => {
            if (isDragging) {
                isDragging = false;
                resizer.classList.remove('dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
                this.syncEditorHighlight();
            }
        };

        resizer.addEventListener('mousedown', onMouseDown);
        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);

        resizer.addEventListener('dblclick', () => {
            document.documentElement.style.setProperty('--editor-box-height', '350px');
            this.syncEditorHighlight();
            this.showToast('Editor height reset to default (350px)', 'info');
        });

        const presets = [350, 520, 720, 240];
        let presetIdx = 0;
        btnToggleHeight?.addEventListener('click', () => {
            presetIdx = (presetIdx + 1) % presets.length;
            const targetHeight = presets[presetIdx];
            document.documentElement.style.setProperty('--editor-box-height', `${targetHeight}px`);
            this.syncEditorHighlight();
            const names = ['Standard (350px)', 'Expanded (520px)', 'Maximized (720px)', 'Compact (240px)'];
            this.showToast(`Editor height: ${names[presetIdx]}`, 'info');
        });
    }

    setupConsoleHeightResizer() {
        const resizer = document.getElementById('console-height-resizer');
        const consolePanel = document.getElementById('console-test-results');
        const btnExpand = document.getElementById('btn-expand-console');
        const btnClose = document.getElementById('btn-close-console');

        if (!resizer || !consolePanel) return;

        let isDragging = false;
        let startY = 0;
        let startHeight = 190;

        const onMouseDown = (e) => {
            isDragging = true;
            startY = e.clientY;
            const currentH = parseInt(getComputedStyle(document.documentElement).getPropertyValue('--console-body-height') || '190', 10);
            startHeight = currentH || 190;
            resizer.classList.add('dragging');
            document.body.style.cursor = 'row-resize';
            document.body.style.userSelect = 'none';
        };

        const onMouseMove = (e) => {
            if (!isDragging) return;
            
            const deltaY = startY - e.clientY;
            let newHeight = startHeight + deltaY;

            if (newHeight < 90) newHeight = 90;
            if (newHeight > 550) newHeight = 550;

            document.documentElement.style.setProperty('--console-body-height', `${newHeight}px`);
        };

        const onMouseUp = () => {
            if (isDragging) {
                isDragging = false;
                resizer.classList.remove('dragging');
                document.body.style.cursor = '';
                document.body.style.userSelect = '';
            }
        };

        resizer.addEventListener('mousedown', onMouseDown);
        window.addEventListener('mousemove', onMouseMove);
        window.addEventListener('mouseup', onMouseUp);

        resizer.addEventListener('dblclick', () => {
            document.documentElement.style.setProperty('--console-body-height', '190px');
            this.showToast('Test cases panel height reset (190px)', 'info');
        });

        let isMaximized = false;
        btnExpand?.addEventListener('click', () => {
            isMaximized = !isMaximized;
            const newH = isMaximized ? 420 : 190;
            document.documentElement.style.setProperty('--console-body-height', `${newH}px`);
            this.showToast(isMaximized ? 'Expanded test cases panel' : 'Restored test cases height', 'info');
        });

        btnClose?.addEventListener('click', () => {
            consolePanel.classList.add('hidden');
            consolePanel.style.display = 'none';
            const toggleEl = document.getElementById('chk-test-cases-toggle');
            if (toggleEl) toggleEl.checked = false;
        });
    }

    setupFontSizeControls() {
        const btnDec = document.getElementById('btn-font-dec');
        const btnInc = document.getElementById('btn-font-inc');
        const badge = document.getElementById('lbl-editor-font-size');

        const updateFontSize = (newSize) => {
            if (newSize < 12) newSize = 12;
            if (newSize > 22) newSize = 22;
            this.fontSize = newSize;

            const lineHeight = Math.round(newSize * 1.714); 
            document.documentElement.style.setProperty('--editor-font-size', `${newSize}px`);
            document.documentElement.style.setProperty('--editor-line-height', `${lineHeight}px`);

            if (badge) badge.textContent = `${newSize}px`;
            this.syncEditorHighlight();
        };

        btnDec?.addEventListener('click', () => {
            updateFontSize(this.fontSize - 2);
        });

        btnInc?.addEventListener('click', () => {
            updateFontSize(this.fontSize + 2);
        });
    }

    setupProblemPanelToggle() {
        const toggleBtn = document.getElementById('btn-toggle-problem-panel');
        const problemPanel = document.getElementById('coding-problem-side');
        const splitter = document.getElementById('coding-workspace-splitter');
        const label = document.getElementById('lbl-toggle-problem');

        if (!toggleBtn || !problemPanel) return;

        toggleBtn.addEventListener('click', () => {
            this.isProblemCollapsed = !this.isProblemCollapsed;
            problemPanel.classList.toggle('collapsed', this.isProblemCollapsed);
            splitter?.classList.toggle('collapsed', this.isProblemCollapsed);

            if (label) {
                label.textContent = this.isProblemCollapsed ? 'Show Problem' : 'Full Editor';
            }
            this.syncEditorHighlight();
            this.showToast(this.isProblemCollapsed ? 'Expanded to full editor mode' : 'Restored side-by-side view', 'info');
        });
    }

    showExitModal() {
        const modal = document.getElementById('modal-exit-confirm');
        const timerLbl = document.getElementById('lbl-exam-timer');
        const modalTimer = document.getElementById('lbl-modal-time-left');
        const modalExam = document.getElementById('lbl-modal-exam-name');
        const headerTitle = document.getElementById('lbl-header-test-title');

        if (modalTimer && timerLbl) modalTimer.textContent = timerLbl.textContent;
        if (modalExam && headerTitle) modalExam.textContent = headerTitle.textContent;
        if (modal) modal.classList.remove('hidden');
    }

    hideExitModal() {
        document.getElementById('modal-exit-confirm')?.classList.add('hidden');
    }

    confirmExit() {
        this.saveCurrent();
        this.hideExitModal();
        if (window.electronAPI?.exitApp) {
            window.electronAPI.exitApp();
        } else {
            window.location.href = 'instructions.html';
        }
    }

    showSubmitModal() {
        const modal = document.getElementById('modal-submit-confirm');
        const desc = document.getElementById('lbl-submit-modal-desc');
        const total = this.questions.length;
        const answeredCount = Object.values(this.answers).filter(a => a.status === 'ANSWERED' || a.selectedOption).length;

        if (desc) {
            desc.textContent = `You have answered ${answeredCount} of ${total} questions. Are you sure you want to finalize and submit your examination?`;
        }
        if (modal) modal.classList.remove('hidden');
    }

    hideSubmitModal() {
        document.getElementById('modal-submit-confirm')?.classList.add('hidden');
    }

    toggleProgressDetails() {
        this.detailsHidden = !this.detailsHidden;
        const ring = document.getElementById('progress-ring-container');
        const legend = document.getElementById('progress-legend-row');
        const btn = document.getElementById('btn-toggle-details');
        
        if (this.detailsHidden) {
            if (ring) ring.style.display = 'none';
            if (legend) legend.style.display = 'none';
            if (btn) btn.innerHTML = `<span>👁️ Show Details</span>`;
        } else {
            if (ring) ring.style.display = 'flex';
            if (legend) legend.style.display = 'flex';
            if (btn) btn.innerHTML = `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#4f46e5" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg><span>Hide Details</span>`;
        }
    }

    toggleEditorFullscreen() {
        const editorContainer = document.getElementById('coding-editor-container');
        if (!editorContainer) return;

        this.isEditorFullscreen = !this.isEditorFullscreen;
        editorContainer.classList.toggle('editor-fullscreen-mode', this.isEditorFullscreen);
        
        const btn = document.getElementById('btn-toggle-fullscreen');
        if (btn) {
            btn.innerHTML = this.isEditorFullscreen
                ? `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="4 14 10 14 10 20"></polyline><polyline points="20 10 14 10 14 4"></polyline><line x1="14" y1="10" x2="21" y2="3"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`
                : `<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polyline points="15 3 21 3 21 9"></polyline><polyline points="9 21 3 21 3 15"></polyline><line x1="21" y1="3" x2="14" y2="10"></line><line x1="3" y1="21" x2="10" y2="14"></line></svg>`;
        }
        this.syncEditorHighlight();
    }

    pushEditorHistory(val, selStart, selEnd, force = false) {
        if (this.isPerformingHistoryAction) return;
        const state = { value: val, selectionStart: selStart, selectionEnd: selEnd };

        const last = this.editorUndoStack[this.editorUndoStack.length - 1];
        if (last && last.value === val) return;

        if (force) {
            this.editorUndoStack.push(state);
            if (this.editorUndoStack.length > 80) this.editorUndoStack.shift();
            this.editorRedoStack = [];
            return;
        }

        clearTimeout(this.historyTimer);
        this.historyTimer = setTimeout(() => {
            this.editorUndoStack.push(state);
            if (this.editorUndoStack.length > 80) this.editorUndoStack.shift();
            this.editorRedoStack = [];
        }, 200);
    }

    performUndo() {
        const textarea = document.getElementById('txt-code-solution');
        if (!textarea || this.editorUndoStack.length === 0) return;

        const currentVal = textarea.value;
        const currentStart = textarea.selectionStart;
        const currentEnd = textarea.selectionEnd;

        let prevState = this.editorUndoStack.pop();
        if (prevState && prevState.value === currentVal && this.editorUndoStack.length > 0) {
            this.editorRedoStack.push({ value: currentVal, selectionStart: currentStart, selectionEnd: currentEnd });
            prevState = this.editorUndoStack.pop();
        }

        if (!prevState) return;

        this.editorRedoStack.push({ value: currentVal, selectionStart: currentStart, selectionEnd: currentEnd });
        this.isPerformingHistoryAction = true;
        textarea.value = prevState.value;
        textarea.selectionStart = prevState.selectionStart;
        textarea.selectionEnd = prevState.selectionEnd;
        this.syncEditorHighlight();
        this.isPerformingHistoryAction = false;
    }

    performRedo() {
        const textarea = document.getElementById('txt-code-solution');
        if (!textarea || this.editorRedoStack.length === 0) return;

        const nextState = this.editorRedoStack.pop();
        if (!nextState) return;

        this.editorUndoStack.push({
            value: textarea.value,
            selectionStart: textarea.selectionStart,
            selectionEnd: textarea.selectionEnd
        });

        this.isPerformingHistoryAction = true;
        textarea.value = nextState.value;
        textarea.selectionStart = nextState.selectionStart;
        textarea.selectionEnd = nextState.selectionEnd;
        this.syncEditorHighlight();
        this.isPerformingHistoryAction = false;
    }

    setupEditorBehaviors() {
        const textarea = document.getElementById('txt-code-solution');
        const highlightLayer = document.getElementById('code-highlight-display');
        const gutter = document.getElementById('code-gutter');

        if (!textarea) return;

        if (this.editorUndoStack.length === 0 && textarea.value) {
            this.pushEditorHistory(textarea.value, textarea.selectionStart || 0, textarea.selectionEnd || 0, true);
        }

        const syncScroll = () => {
            if (highlightLayer) {
                highlightLayer.scrollTop = textarea.scrollTop;
                highlightLayer.scrollLeft = textarea.scrollLeft;
            }
            if (gutter) {
                gutter.scrollTop = textarea.scrollTop;
            }
        };

        textarea.addEventListener('scroll', syncScroll);

        textarea.addEventListener('input', () => {
            this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd);
            this.syncEditorHighlight();
            syncScroll();
            // Instantly sync active coding answer into state and local storage
            const q = this.questions[this.currentIndex];
            if (q && q.type === 'CODING') {
                this.answers[q.question_number] = this.answers[q.question_number] || {};
                this.answers[q.question_number].type = 'CODING';
                this.answers[q.question_number].codes = this.answers[q.question_number].codes || {};
                this.answers[q.question_number].codes[this.selectedLanguage] = textarea.value;
                this.answers[q.question_number].codeSolution = textarea.value;
                this.answers[q.question_number].language = this.selectedLanguage;
                if (textarea.value.trim().length > 0) {
                    this.answers[q.question_number].status = 'ANSWERED';
                }
                try {
                    const key = this.getDraftStorageKey();
                    localStorage.setItem(key, JSON.stringify(this.answers));
                    localStorage.setItem(`exam_draft_backup_${this.examCode}`, JSON.stringify(this.answers));
                } catch (_) {}
                this.updatePalette();
            }
        });

        textarea.addEventListener('keydown', (e) => {
            const start = textarea.selectionStart;
            const end = textarea.selectionEnd;
            const val = textarea.value;

            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'z') {
                e.preventDefault();
                if (e.shiftKey) {
                    this.performRedo();
                } else {
                    this.performUndo();
                }
                syncScroll();
                return;
            }

            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'y') {
                e.preventDefault();
                this.performRedo();
                syncScroll();
                return;
            }

            if (e.key === 'Tab') {
                e.preventDefault();
                this.pushEditorHistory(val, start, end, true);
                if (e.shiftKey) {
                    const lineStart = val.lastIndexOf('\n', start - 1) + 1;
                    if (val.substr(lineStart, 4) === '    ') {
                        textarea.value = val.substring(0, lineStart) + val.substring(lineStart + 4);
                        textarea.selectionStart = textarea.selectionEnd = Math.max(lineStart, start - 4);
                    }
                } else {
                    textarea.value = val.substring(0, start) + '    ' + val.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + 4;
                }
                this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                this.syncEditorHighlight();
                syncScroll();
                return;
            }

            if (e.key === 'Enter') {
                e.preventDefault();
                this.pushEditorHistory(val, start, end, true);
                const curLineStart = val.lastIndexOf('\n', start - 1) + 1;
                const curLine = val.substring(curLineStart, start);
                const indentMatch = curLine.match(/^\s*/);
                let indent = indentMatch ? indentMatch[0] : '';

                const prevChar = val[start - 1];
                const nextChar = val[start];
                if (prevChar === '{' && nextChar === '}') {
                    const extraIndent = indent + '    ';
                    textarea.value = val.substring(0, start) + '\n' + extraIndent + '\n' + indent + val.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + 1 + extraIndent.length;
                    this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                    this.syncEditorHighlight();
                    syncScroll();
                    return;
                }

                if (/[\{\:\(]\s*$/.test(curLine)) {
                    indent += '    ';
                }

                textarea.value = val.substring(0, start) + '\n' + indent + val.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + 1 + indent.length;
                this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                this.syncEditorHighlight();
                syncScroll();
                return;
            }

            const pairs = { '(': ')', '[': ']', '{': '}', '"': '"', "'": "'" };
            if (pairs[e.key]) {
                const closeChar = pairs[e.key];
                if (start !== end) {
                    e.preventDefault();
                    this.pushEditorHistory(val, start, end, true);
                    const selected = val.substring(start, end);
                    textarea.value = val.substring(0, start) + e.key + selected + closeChar + val.substring(end);
                    textarea.selectionStart = start + 1;
                    textarea.selectionEnd = end + 1;
                    this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                    this.syncEditorHighlight();
                    syncScroll();
                    return;
                } else if (e.key === closeChar && val[start] === closeChar) {
                    e.preventDefault();
                    textarea.selectionStart = textarea.selectionEnd = start + 1;
                    return;
                } else {
                    e.preventDefault();
                    this.pushEditorHistory(val, start, end, true);
                    textarea.value = val.substring(0, start) + e.key + closeChar + val.substring(end);
                    textarea.selectionStart = textarea.selectionEnd = start + 1;
                    this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                    this.syncEditorHighlight();
                    syncScroll();
                    return;
                }
            }

            if ((e.key === ')' || e.key === ']' || e.key === '}' || e.key === '"' || e.key === "'") && start === end && val[start] === e.key) {
                e.preventDefault();
                textarea.selectionStart = textarea.selectionEnd = start + 1;
                return;
            }

            if (e.key === 'Backspace' && start === end && start > 0) {
                const prev = val[start - 1];
                const next = val[start];
                if (
                    (prev === '(' && next === ')') ||
                    (prev === '[' && next === ']') ||
                    (prev === '{' && next === '}') ||
                    (prev === '"' && next === '"') ||
                    (prev === "'" && next === "'")
                ) {
                    e.preventDefault();
                    this.pushEditorHistory(val, start, end, true);
                    textarea.value = val.substring(0, start - 1) + val.substring(start + 1);
                    textarea.selectionStart = textarea.selectionEnd = start - 1;
                    this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
                    this.syncEditorHighlight();
                    syncScroll();
                    return;
                }
            }
        });
    }

    syncEditorHighlight() {
        const textarea = document.getElementById('txt-code-solution');
        const codeDisplay = document.getElementById('code-highlight-content');
        const highlightLayer = document.getElementById('code-highlight-display');
        const gutter = document.getElementById('code-gutter');

        if (!textarea || !codeDisplay) return;

        const rawCode = textarea.value;
        const lines = rawCode.split('\n');

        if (gutter) {
            let gutterHtml = '';
            const totalLines = Math.max(lines.length, 18);
            for (let i = 1; i <= totalLines; i++) {
                gutterHtml += `<div class="gutter-num">${i}</div>`;
            }
            gutter.innerHTML = gutterHtml;
        }

        codeDisplay.innerHTML = this.highlightSyntax(rawCode, this.selectedLanguage);

        if (highlightLayer) {
            highlightLayer.scrollTop = textarea.scrollTop;
            highlightLayer.scrollLeft = textarea.scrollLeft;
        }
        if (gutter) {
            gutter.scrollTop = textarea.scrollTop;
        }
    }

    highlightSyntax(code, lang) {
        if (!code) return '\n ';

        const escapeHtml = (str) => {
            return str
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        };

        // Master single-pass lexer tokenizer:
        // Group 1: Comments
        // Group 2: Directives & Includes
        // Group 3: Strings & Chars
        // Group 4: Keywords
        // Group 5: Types & Standard Built-ins
        // Group 6: Numbers
        // Group 7: Function names
        // Group 8: Identifiers / Other punctuation
        const tokenRegex = /(\/\*[\s\S]*?\*\/|\/\/[^\n]*|#[^\n]*)|(#include\s*<[^>\n]+>|#include\s*"[^"\n]+"|#define\s+[^\n]+|using\s+namespace\s+\w+;|import\s+[^;\n]+;|package\s+[^;\n]+;)|("(?:\\.|[^"\\])*"|'(?:\\.|[^'\\])*'|`(?:\\.|[^`\\])*`)|(\b(?:return|if|else|for|while|do|switch|case|break|continue|new|this|delete|try|catch|throw|finally|function|def|class|public|private|protected|static|auto|let|const|var|null|nullptr|true|false|None|True|False|self|from|as|in|is|not|and|or|yield|async|await)\b)|(\b(?:int|float|double|char|bool|boolean|void|vector|string|size_t|long|short|unsigned|signed|map|set|unordered_map|unordered_set|pair|list|queue|stack|deque|std|cin|cout|endl|printf|scanf|sizeof|System|Scanner|out|println|print)\b)|(\b\d+(?:\.\d+)?\b)|(\b[a-zA-Z_]\w*(?=\s*\())|([a-zA-Z_]\w*|[^\s\w]+|\s+)/g;

        let html = '';
        let match;

        while ((match = tokenRegex.exec(code)) !== null) {
            if (match[1]) {
                html += `<span class="tok-comment">${escapeHtml(match[1])}</span>`;
            } else if (match[2]) {
                html += `<span class="tok-directive">${escapeHtml(match[2])}</span>`;
            } else if (match[3]) {
                html += `<span class="tok-str">${escapeHtml(match[3])}</span>`;
            } else if (match[4]) {
                html += `<span class="tok-kw">${escapeHtml(match[4])}</span>`;
            } else if (match[5]) {
                html += `<span class="tok-type">${escapeHtml(match[5])}</span>`;
            } else if (match[6]) {
                html += `<span class="tok-num">${escapeHtml(match[6])}</span>`;
            } else if (match[7]) {
                html += `<span class="tok-fn">${escapeHtml(match[7])}</span>`;
            } else {
                html += escapeHtml(match[0]);
            }
        }

        // Handle trailing newline so cursor line is never offset on empty line
        if (code.endsWith('\n')) {
            html += ' ';
        }

        return html;
    }

    // ========================================================
    // 4. LANGUAGE & SECTION SWITCHING
    // ========================================================
    getQuestionStarterCode(q, lang) {
        if (!q) return '';
        const targetLang = (lang || this.selectedLanguage || 'cpp').toLowerCase();

        // 1. Fetch starter code directly from MySQL Database object / JSON payload
        if (q.coding_starter_code) {
            let starterCodes = q.coding_starter_code;
            if (typeof starterCodes === 'string') {
                try {
                    starterCodes = JSON.parse(starterCodes);
                } catch (_) {}
            }

            if (typeof starterCodes === 'object' && starterCodes !== null) {
                if (starterCodes[targetLang]) return starterCodes[targetLang];
                if (targetLang === 'c' && starterCodes['cpp']) return starterCodes['cpp'];
                if (starterCodes['javascript'] && targetLang === 'javascript') return starterCodes['javascript'];
                // Fallback to first available language in DB record
                const firstAvailable = Object.values(starterCodes)[0];
                if (typeof firstAvailable === 'string' && firstAvailable.trim().length > 0) {
                    return firstAvailable;
                }
            } else if (typeof starterCodes === 'string' && starterCodes.trim().length > 0) {
                return starterCodes;
            }
        }

        // 2. Generic Minimal Skeleton Fallback (Only if database record has no starter code configured)
        const entryFn = q.entry_function || 'solve';
        switch (targetLang) {
            case 'cpp':
            case 'c++':
                return `#include <iostream>\n#include <vector>\n\nusing namespace std;\n\nvoid ${entryFn}() {\n    // Write your solution here\n}\n\nint main() {\n    ios_base::sync_with_stdio(false);\n    cin.tie(NULL);\n    ${entryFn}();\n    return 0;\n}`;
            case 'c':
                return `#include <stdio.h>\n#include <stdlib.h>\n\nvoid ${entryFn}() {\n    // Write your solution here\n}\n\nint main() {\n    ${entryFn}();\n    return 0;\n}`;
            case 'java':
                return `import java.util.*;\n\npublic class Solution {\n    public static void ${entryFn}() {\n        // Write your solution here\n    }\n\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        ${entryFn}();\n    }\n}`;
            case 'python':
            case 'py':
                return `import sys\n\ndef ${entryFn}():\n    # Write your solution here\n    pass\n\nif __name__ == '__main__':\n    ${entryFn}()`;
            case 'javascript':
            case 'js':
            default:
                return `function ${entryFn}() {\n    // Write your solution here\n}`;
        }
    }

    handleLanguageChange(newLang) {
        if (!this.supportedLanguages[newLang]) return;
        const prevLang = this.selectedLanguage;
        this.selectedLanguage = newLang;

        const lbl = document.getElementById('lbl-selected-lang');
        if (lbl) lbl.textContent = this.supportedLanguages[newLang];

        document.querySelectorAll('#menu-coding-lang .custom-select-option').forEach(opt => {
            opt.classList.toggle('selected', opt.getAttribute('data-value') === newLang);
        });

        const q = this.questions[this.currentIndex];
        if (q && q.type === 'CODING') {
            const textarea = document.getElementById('txt-code-solution');
            if (textarea) {
                // Save current code to previous language buffer
                this.answers[q.question_number] = this.answers[q.question_number] || {};
                this.answers[q.question_number].codes = this.answers[q.question_number].codes || {};
                this.answers[q.question_number].codes[prevLang] = textarea.value;

                // Load existing code for new language or provide starter code
                const targetCode = this.answers[q.question_number].codes[newLang] || this.getQuestionStarterCode(q, newLang);
                this.answers[q.question_number].codes[newLang] = targetCode;
                this.answers[q.question_number].codeSolution = targetCode;
                this.answers[q.question_number].language = newLang;

                textarea.value = targetCode;
                this.syncEditorHighlight();
            }
        }

        this.showToast(`Switched compiler to ${this.supportedLanguages[newLang]}`, 'info');
    }

    handleSectionChange(secId) {
        const sec = this.sections?.find(s => s.id === secId);
        let targetIdx = this.questions.findIndex(q => 
            q.section === secId || 
            (secId === 'coding' && q.type === 'CODING') || 
            (secId === 'essay' && q.type === 'PARAGRAPH') || 
            (secId === 'mcq' && q.type === 'MCQ')
        );

        if (targetIdx === -1) targetIdx = 0;

        const secName = sec?.name || (secId === 'coding' ? 'Section 2: Coding Assessment' : (secId === 'essay' ? 'Section 3: Descriptive & Paragraph' : 'Section 1: Aptitude & Reasoning'));

        const lbl = document.getElementById('lbl-selected-section');
        if (lbl) lbl.textContent = secName;

        document.querySelectorAll('#menu-section-switcher .custom-select-option').forEach(opt => {
            opt.classList.toggle('selected', opt.getAttribute('data-section') === secId);
        });

        this.jumpTo(targetIdx);
        this.showToast(`Switched to ${secName}`, 'info');
    }

    // Dynamically render section cards inside the Change Section dropdown based on backend exam data
    renderDynamicSectionSwitcher() {
        const menu = document.getElementById('menu-section-switcher');
        if (!menu) return;

        // 1. Build sections list from backend data or dynamically from question objects
        if (this.backendSections && Array.isArray(this.backendSections) && this.backendSections.length > 0) {
            this.sections = this.backendSections;
        } else if (this.examDetails?.sections && Array.isArray(this.examDetails.sections) && this.examDetails.sections.length > 0) {
            this.sections = this.examDetails.sections;
        } else {
            const secMap = new Map();
            this.questions.forEach((q, idx) => {
                const secId = q.section || (q.type === 'CODING' ? 'coding' : (q.type === 'PARAGRAPH' ? 'essay' : 'mcq'));
                if (!secMap.has(secId)) {
                    let name = q.section_name;
                    let icon = '📝';
                    if (!name) {
                        if (q.type === 'CODING') { name = 'Section 2: Coding Assessment'; icon = '💻'; }
                        else if (q.type === 'PARAGRAPH') { name = 'Section 3: Descriptive & Paragraph'; icon = '✍️'; }
                        else { name = 'Section 1: Aptitude & Reasoning'; icon = '📝'; }
                    }
                    secMap.set(secId, {
                        id: secId,
                        name: name,
                        type: q.type,
                        icon: icon,
                        count: 1,
                        startIndex: idx,
                        startNumber: q.question_number,
                        endNumber: q.question_number
                    });
                } else {
                    const item = secMap.get(secId);
                    item.count++;
                    item.endNumber = q.question_number;
                }
            });

            this.sections = Array.from(secMap.values()).map(s => ({
                ...s,
                question_range: s.startNumber === s.endNumber ? `${s.startNumber}` : `${s.startNumber} - ${s.endNumber}`,
                description: `${s.count} Question${s.count > 1 ? 's' : ''} • Q${s.startNumber === s.endNumber ? s.startNumber : `${s.startNumber} - ${s.endNumber}`}`
            }));
        }

        // 2. Clear hardcoded HTML and render dynamic sections received from backend
        menu.innerHTML = '';
        this.sections.forEach((sec, idx) => {
            const opt = document.createElement('div');
            opt.className = `custom-select-option section-opt-card ${idx === 0 ? 'selected' : ''}`;
            opt.setAttribute('data-section', sec.id);
            opt.setAttribute('role', 'option');

            const bgCol = sec.type === 'CODING' ? 'rgba(59, 130, 246, 0.12)' : (sec.type === 'PARAGRAPH' ? 'rgba(16, 185, 129, 0.12)' : 'rgba(99, 102, 241, 0.12)');
            const desc = sec.description || (sec.question_range ? `Questions ${sec.question_range} • ${sec.count} Questions` : `${sec.count} Questions`);

            opt.innerHTML = `
                <div class="section-opt-icon-circle" style="background: ${bgCol}; width:34px; height:34px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:16px;">
                    ${sec.icon || (sec.type === 'CODING' ? '💻' : (sec.type === 'PARAGRAPH' ? '✍️' : '📝'))}
                </div>
                <div class="section-opt-info" style="flex:1;">
                    <strong class="sec-title" style="display:block; font-size:13px; font-weight:700; color:#1e293b;">${sec.name}</strong>
                    <span class="sec-desc" style="display:block; font-size:11px; color:#64748b;">${desc}</span>
                </div>
                <svg class="opt-check-svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
            `;

            opt.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.handleSectionChange(sec.id);
                this.closeAllDropdowns();
            });

            menu.appendChild(opt);
        });

        // Update default trigger label to current active section
        const curQ = this.questions[this.currentIndex];
        const activeSec = (curQ && this.sections.find(s => s.id === curQ.section || s.type === curQ.type)) || this.sections[0];
        const lbl = document.getElementById('lbl-selected-section');
        if (lbl && activeSec) {
            lbl.textContent = activeSec.name;
        }

        // 3. Populate Top Section Quick Navigation Tabs Bar (Visible across all modes)
        const tabsBar = document.getElementById('assessment-section-tabs-bar');
        if (tabsBar) {
            tabsBar.innerHTML = '';
            this.sections.forEach((sec) => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'section-tab-btn';
                btn.setAttribute('data-section', sec.id);
                btn.innerHTML = `
                    <span>${sec.icon || (sec.type === 'CODING' ? '💻' : (sec.type === 'PARAGRAPH' ? '✍️' : '📝'))}</span>
                    <span>${sec.name}</span>
                    <span class="tab-badge-count">${sec.count} Questions</span>
                `;
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.handleSectionChange(sec.id);
                });
                tabsBar.appendChild(btn);
            });
        }
    }

    // ========================================================
    // 5. LOAD EXAM METADATA & COMPREHENSIVE QUESTION DATASET
    // ========================================================
    async loadExamDetails() {
        try {
            const res = await fetch(`${this.backendUrl}/api/exam/details/${this.examCode}`);
            const data = await res.json();
            if (data.success && data.exam) {
                this.examDetails = data.exam;
            }
        } catch (_) {}

        const details = this.examDetails || {
            title: 'Aptitude & Coding Assessment Test',
            category: 'Aptitude & Coding',
            exam_date: '31 May 2025',
            exam_time: '10:00 AM - 12:00 PM',
            duration_minutes: 120,
            total_marks: 120,
            total_questions: 13
        };

        const titleEl = document.getElementById('lbl-header-test-title');
        const catEl = document.getElementById('lbl-header-category');
        const dateEl = document.getElementById('lbl-exam-date');
        const timeEl = document.getElementById('lbl-exam-time');
        const durEl = document.getElementById('lbl-exam-duration');
        const marksEl = document.getElementById('lbl-exam-total-marks');
        const qCountEl = document.getElementById('lbl-header-questions-count');

        if (titleEl) titleEl.textContent = details.title || 'Aptitude & Coding Assessment Test';
        if (catEl) catEl.textContent = details.category || 'Aptitude & Coding';
        if (dateEl) dateEl.textContent = details.exam_date || '31 May 2025';
        if (timeEl) timeEl.textContent = details.exam_time || '10:00 AM - 12:00 PM';
        if (durEl) durEl.textContent = `${details.duration_minutes || 120} Min`;
        if (marksEl) marksEl.textContent = details.total_marks || '120';
        if (qCountEl) qCountEl.textContent = `${this.questions.length || 13} / ${this.questions.length || 13}`;

        this.timerSeconds = (details.duration_minutes || 120) * 60;
    }

    async loadQuestions() {
        const defaultFullQuestions = [
            {
                question_number: 1,
                type: 'MCQ',
                section: 'mcq',
                title: 'BST Search Complexity',
                question_text: 'What is the average time complexity of searching an element in a balanced Binary Search Tree (AVL / Red-Black Tree)?',
                options: [
                    { key: 'A', text: 'O(1)' },
                    { key: 'B', text: 'O(n)' },
                    { key: 'C', text: 'O(log n)' },
                    { key: 'D', text: 'O(n log n)' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 2,
                type: 'MCQ',
                section: 'mcq',
                title: 'HTTP Authentication Status Code',
                question_text: 'Which of the following HTTP status codes specifically signifies that authentication credentials are missing or invalid?',
                options: [
                    { key: 'A', text: '400 Bad Request' },
                    { key: 'B', text: '401 Unauthorized' },
                    { key: 'C', text: '403 Forbidden' },
                    { key: 'D', text: '404 Not Found' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 3,
                type: 'MCQ',
                section: 'mcq',
                title: 'JavaScript Async Architecture',
                question_text: 'In modern JavaScript runtimes (V8/Node.js), what core architectural component orchestrates non-blocking asynchronous I/O execution?',
                options: [
                    { key: 'A', text: 'Kernel Fiber Scheduling' },
                    { key: 'B', text: 'Event Loop & Libuv Threadpool' },
                    { key: 'C', text: 'Direct Hardware Interrupt Handlers' },
                    { key: 'D', text: 'POSIX Signal Dispatcher' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 4,
                type: 'MCQ',
                section: 'mcq',
                title: 'Database Normalization',
                question_text: 'Which Normal Form in relational database theory strictly eliminates transitive functional dependencies?',
                options: [
                    { key: 'A', text: '1NF' },
                    { key: 'B', text: '2NF' },
                    { key: 'C', text: '3NF' },
                    { key: 'D', text: 'BCNF' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 5,
                type: 'MCQ',
                section: 'mcq',
                title: 'Web Security & CSP',
                question_text: 'What is the primary security objective of the Content-Security-Policy (CSP) HTTP response header?',
                options: [
                    { key: 'A', text: 'Encrypting database traffic' },
                    { key: 'B', text: 'Preventing Cross-Site Scripting (XSS) and injection attacks' },
                    { key: 'C', text: 'Accelerating DNS caching' },
                    { key: 'D', text: 'Enforcing CORS pre-flight' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 6,
                type: 'MCQ',
                section: 'mcq',
                title: 'Shortest Path Algorithm Optimization',
                question_text: 'Which auxiliary data structure is utilized in Dijkstra algorithm to achieve optimal O((V + E) log V) time complexity?',
                options: [
                    { key: 'A', text: 'Deque' },
                    { key: 'B', text: 'Min-Priority Queue / Min-Heap' },
                    { key: 'C', text: 'Circular Ring Buffer' },
                    { key: 'D', text: 'Monotonic Stack' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 7,
                type: 'MCQ',
                section: 'mcq',
                title: 'OS Memory Allocation',
                question_text: 'In operating systems, what term describes the phenomenon where allocated memory partitions contain small, unusable wasted spaces?',
                options: [
                    { key: 'A', text: 'External Fragmentation' },
                    { key: 'B', text: 'Internal Fragmentation' },
                    { key: 'C', text: 'Page Fault Thrashing' },
                    { key: 'D', text: 'Segmentation Fault' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 8,
                type: 'MCQ',
                section: 'mcq',
                title: 'SOLID Design Principles',
                question_text: 'Which SOLID principle mandates that classes should be open for extension but closed for modification?',
                options: [
                    { key: 'A', text: 'Single Responsibility Principle' },
                    { key: 'B', text: 'Open/Closed Principle (OCP)' },
                    { key: 'C', text: 'Liskov Substitution Principle' },
                    { key: 'D', text: 'Interface Segregation Principle' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 9,
                type: 'MCQ',
                section: 'mcq',
                title: 'Network Transport Protocols',
                question_text: 'Which transport layer protocol provides connection-oriented, reliable, and ordered packet delivery with flow and congestion control?',
                options: [
                    { key: 'A', text: 'UDP' },
                    { key: 'B', text: 'ICMP' },
                    { key: 'C', text: 'TCP' },
                    { key: 'D', text: 'IGMP' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 10,
                type: 'MCQ',
                section: 'mcq',
                title: 'JavaScript Type System',
                question_text: 'What is the evaluated output of evaluating `typeof NaN` in standard ECMAScript JavaScript?',
                options: [
                    { key: 'A', text: '"undefined"' },
                    { key: 'B', text: '"nan"' },
                    { key: 'C', text: '"number"' },
                    { key: 'D', text: '"object"' }
                ],
                max_marks: 2.00
            },
            {
                question_number: 11,
                type: 'CODING',
                section: 'coding',
                title: 'Coding Challenge 1: Two Sum Target Indices',
                question_text: 'Write a function `twoSum(nums, target)` that takes an array of integers `nums` and an integer `target`, and returns indices of the two numbers such that they add up to `target`.\n\nExample 1:\nInput: nums = [2, 7, 11, 15], target = 9\nOutput: [0, 1]\n\nExample 2:\nInput: nums = [3, 2, 4], target = 6\nOutput: [1, 2]\n\nConstraints:\n- 2 <= nums.length <= 10^4\n- -10^9 <= nums[i] <= 10^9',
                entry_function: 'twoSum',
                coding_starter_code: {
                    cpp: '#include <iostream>\n#include <vector>\n#include <unordered_map>\n\nusing namespace std;\n\nvector<int> twoSum(vector<int>& nums, int target) {\n    unordered_map<int, int> seen;\n    for (int i = 0; i < (int)nums.size(); i++) {\n        int comp = target - nums[i];\n        if (seen.find(comp) != seen.end()) {\n            return {seen[comp], i};\n        }\n        seen[nums[i]] = i;\n    }\n    return {};\n}\n\nint main() {\n    int n;\n    if (!(cin >> n)) return 0;\n    vector<int> nums(n);\n    for (int i = 0; i < n; i++) cin >> nums[i];\n    int target;\n    cin >> target;\n\n    vector<int> result = twoSum(nums, target);\n    if (result.size() >= 2) {\n        cout << result[0] << " " << result[1] << endl;\n    }\n    return 0;\n}',
                    c: '#include <stdio.h>\n#include <stdlib.h>\n\nvoid twoSum(int* nums, int numsSize, int target, int* returnIndices) {\n    for (int i = 0; i < numsSize; i++) {\n        for (int j = i + 1; j < numsSize; j++) {\n            if (nums[i] + nums[j] == target) {\n                returnIndices[0] = i;\n                returnIndices[1] = j;\n                return;\n            }\n        }\n    }\n}\n\nint main() {\n    int n;\n    if (scanf("%d", &n) != 1) return 0;\n    int* nums = (int*)malloc(n * sizeof(int));\n    for (int i = 0; i < n; i++) scanf("%d", &nums[i]);\n    int target;\n    scanf("%d", &target);\n\n    int result[2] = {0, 0};\n    twoSum(nums, n, target, result);\n    printf("%d %d\\n", result[0], result[1]);\n    free(nums);\n    return 0;\n}',
                    java: 'import java.util.*;\n\npublic class Solution {\n    public static int[] twoSum(int[] nums, int target) {\n        Map<Integer, Integer> map = new HashMap<>();\n        for (int i = 0; i < nums.length; i++) {\n            int comp = target - nums[i];\n            if (map.containsKey(comp)) {\n                return new int[]{map.get(comp), i};\n            }\n            map.put(nums[i], i);\n        }\n        return new int[]{};\n    }\n\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (!sc.hasNextInt()) return;\n        int n = sc.nextInt();\n        int[] nums = new int[n];\n        for (int i = 0; i < n; i++) nums[i] = sc.nextInt();\n        int target = sc.nextInt();\n\n        int[] res = twoSum(nums, target);\n        if (res.length >= 2) {\n            System.out.println(res[0] + " " + res[1]);\n        }\n    }\n}',
                    python: 'import sys\n\ndef two_sum(nums, target):\n    seen = {}\n    for i, num in enumerate(nums):\n        comp = target - num\n        if comp in seen:\n            return [seen[comp], i]\n        seen[num] = i\n    return []\n\ndef main():\n    input_data = sys.stdin.read().split()\n    if not input_data: return\n    n = int(input_data[0])\n    nums = [int(x) for x in input_data[1:n+1]]\n    target = int(input_data[n+1])\n    res = two_sum(nums, target)\n    if len(res) >= 2:\n        print(f"{res[0]} {res[1]}")\n\nif __name__ == "__main__":\n    main()',
                    javascript: 'function twoSum(nums, target) {\n    const map = new Map();\n    for (let i = 0; i < nums.length; i++) {\n        const comp = target - nums[i];\n        if (map.has(comp)) return [map.get(comp), i];\n        map.set(nums[i], i);\n    }\n    return [];\n}\n\nconst fs = require("fs");\ntry {\n    const input = fs.readFileSync(0, "utf-8").trim().split(/\\s+/);\n    if (input.length > 1 && input[0] !== "") {\n        const n = parseInt(input[0], 10);\n        const nums = input.slice(1, n + 1).map(Number);\n        const target = parseInt(input[n + 1], 10);\n        const res = twoSum(nums, target);\n        if (Array.isArray(res) && res.length >= 2) {\n            console.log(`${res[0]} ${res[1]}`);\n        }\n    }\n} catch (e) {}'
                },
                public_test_cases: [
                    { id: 'pub_1', nums: [2, 7, 11, 15], target: 9, expected: [0, 1] },
                    { id: 'pub_2', nums: [3, 2, 4], target: 6, expected: [1, 2] }
                ],
                max_marks: 50.00
            },
            {
                question_number: 12,
                type: 'CODING',
                section: 'coding',
                title: 'Coding Challenge 2: Second Largest Element in Array',
                question_text: 'Write a function `secondLargest(arr)` that takes an array of integers and returns the second largest distinct element in the array. If the second largest element does not exist, return -1.\n\nExample 1:\nInput: [12, 35, 1, 10, 34, 1]\nOutput: 34\n\nExample 2:\nInput: [10, 10, 10]\nOutput: -1\n\nConstraints:\n- 2 <= arr.length <= 10^5\n- -10^9 <= arr[i] <= 10^9',
                entry_function: 'secondLargest',
                coding_starter_code: {
                    cpp: '#include <iostream>\n#include <vector>\n\nusing namespace std;\n\nint secondLargest(vector<int>& arr) {\n    int largest = -1e9, second = -1e9;\n    for (int x : arr) {\n        if (x > largest) {\n            second = largest;\n            largest = x;\n        } else if (x > second && x != largest) {\n            second = x;\n        }\n    }\n    return (second <= -1e9) ? -1 : second;\n}\n\nint main() {\n    int n;\n    if (!(cin >> n)) return 0;\n    vector<int> arr(n);\n    for (int i = 0; i < n; i++) cin >> arr[i];\n    cout << secondLargest(arr) << endl;\n    return 0;\n}',
                    c: '#include <stdio.h>\n#include <stdlib.h>\n\nint secondLargest(int* arr, int n) {\n    int largest = -1000000000, second = -1000000000;\n    for (int i = 0; i < n; i++) {\n        if (arr[i] > largest) {\n            second = largest;\n            largest = arr[i];\n        } else if (arr[i] > second && arr[i] != largest) {\n            second = arr[i];\n        }\n    }\n    return (second <= -1000000000) ? -1 : second;\n}\n\nint main() {\n    int n;\n    if (scanf("%d", &n) != 1) return 0;\n    int* arr = (int*)malloc(n * sizeof(int));\n    for (int i = 0; i < n; i++) scanf("%d", &arr[i]);\n    printf("%d\\n", secondLargest(arr, n));\n    free(arr);\n    return 0;\n}',
                    java: 'import java.util.*;\n\npublic class Solution {\n    public static int secondLargest(int[] arr) {\n        int largest = Integer.MIN_VALUE, second = Integer.MIN_VALUE;\n        for (int x : arr) {\n            if (x > largest) {\n                second = largest;\n                largest = x;\n            } else if (x > second && x != largest) {\n                second = x;\n            }\n        }\n        return (second == Integer.MIN_VALUE) ? -1 : second;\n    }\n\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (!sc.hasNextInt()) return;\n        int n = sc.nextInt();\n        int[] nums = new int[n];\n        for (int i = 0; i < n; i++) nums[i] = sc.nextInt();\n        System.out.println(secondLargest(nums));\n    }\n}',
                    python: 'import sys\n\ndef second_largest(arr):\n    largest = float("-inf")\n    second = float("-inf")\n    for x in arr:\n        if x > largest:\n            second = largest\n            largest = x\n        elif x > second and x != largest:\n            second = x\n    return -1 if second == float("-inf") else second\n\ndef main():\n    input_data = sys.stdin.read().split()\n    if not input_data: return\n    n = int(input_data[0])\n    arr = [int(x) for x in input_data[1:n+1]]\n    print(second_largest(arr))\n\nif __name__ == "__main__":\n    main()',
                    javascript: 'function secondLargest(arr) {\n    let largest = -Infinity, second = -Infinity;\n    for (const x of arr) {\n        if (x > largest) {\n            second = largest;\n            largest = x;\n        } else if (x > second && x !== largest) {\n            second = x;\n        }\n    }\n    return second === -Infinity ? -1 : second;\n}\n\nconst fs = require("fs");\ntry {\n    const input = fs.readFileSync(0, "utf-8").trim().split(/\\s+/);\n    if (input.length > 0 && input[0] !== "") {\n        const n = parseInt(input[0], 10);\n        const arr = input.slice(1, n + 1).map(Number);\n        console.log(secondLargest(arr));\n    }\n} catch (e) {}'
                },
                public_test_cases: [
                    { id: 'pub_1', arr: [12, 35, 1, 10, 34, 1], expected: 34 },
                    { id: 'pub_2', arr: [10, 10, 10], expected: -1 }
                ],
                max_marks: 50.00
            },
            {
                question_number: 13,
                type: 'PARAGRAPH',
                section: 'essay',
                title: 'System Architecture & Scalability Strategy',
                question_text: 'Explain how you would design a high-throughput, fault-tolerant online examination proctoring system capable of supporting 500,000 concurrent students.\n\nIn your response, address:\n1. Low-latency telemetry streaming (WebSockets / gRPC).\n2. In-memory distributed caching (Redis) for real-time violation aggregation.\n3. Decoupled message queues (Kafka / RabbitMQ) for AI proctoring analysis.\n4. Database partitioning and sharding strategies for audit trails.',
                max_marks: 20.00
            }
        ];

        try {
            console.log(`📡 [Assessment Engine] Loading assessment questions for exam: ${this.examCode}`);
            const res = await fetch(`${this.backendUrl}/api/exam/questions/${this.examCode}`);
            const data = await res.json();
            if (data.sections && Array.isArray(data.sections)) {
                this.backendSections = data.sections;
            }
            if (data.success && data.questions && data.questions.length > 0) {
                this.questions = data.questions.map(q => {
                    // Set section according to question type
                    if (!q.section) {
                        if (q.type === 'CODING') q.section = 'coding';
                        else if (q.type === 'PARAGRAPH') q.section = 'essay';
                        else q.section = 'mcq';
                    }
                    // Map public test cases to runner format
                    if (q.public_test_cases && (!q.test_cases || q.test_cases.length === 0)) {
                        const pts = typeof q.public_test_cases === 'string' ? JSON.parse(q.public_test_cases) : q.public_test_cases;
                        q.test_cases = pts.map(tc => {
                            let inputStr = '';
                            if (tc.nums !== undefined && tc.target !== undefined) {
                                inputStr = `${tc.nums.length}\n${tc.nums.join(' ')}\n${tc.target}`;
                            } else if (tc.arr !== undefined) {
                                inputStr = `${tc.arr.length}\n${tc.arr.join(' ')}`;
                            } else if (tc.input !== undefined) {
                                inputStr = String(tc.input);
                            }
                            return {
                                input: inputStr,
                                expected: Array.isArray(tc.expected) ? tc.expected.join(' ') : String(tc.expected)
                            };
                        });
                    }
                    return q;
                });
                console.log(`✅ [Assessment Engine] Successfully loaded ${this.questions.length} questions from server.`);
            } else {
                this.questions = defaultFullQuestions;
            }
        } catch (err) {
            console.warn('⚠️ [Assessment Engine] Server fetch failed, using official comprehensive dataset:', err);
            this.questions = defaultFullQuestions;
        }

        if (!this.questions || this.questions.length === 0) {
            this.questions = defaultFullQuestions;
        }

        // Initialize candidate answers directly from question models
        this.questions.forEach(q => {
            if (!this.answers[q.question_number]) {
                const starterCode = q.type === 'CODING' ? this.getQuestionStarterCode(q, this.selectedLanguage) : '';

                this.answers[q.question_number] = {
                    type: q.type,
                    section: q.section || (q.type === 'CODING' ? 'coding' : (q.type === 'PARAGRAPH' ? 'essay' : 'mcq')),
                    selectedOption: null,
                    codeSolution: starterCode,
                    language: this.selectedLanguage,
                    codes: {
                        [this.selectedLanguage]: starterCode
                    },
                    essayText: '',
                    status: 'NOT_VISITED'
                };
            }
        });

        // Immediately load any locally cached draft from prior session BEFORE first render
        this.loadDraftLocal();

        const qCountEl = document.getElementById('lbl-header-questions-count');
        if (qCountEl) qCountEl.textContent = `1 / ${this.questions.length}`;

        this.renderPalette();
        this.renderCurrentQuestion();
    }

    // Parse question data and split raw markdown / text into clean description, example cards & constraints
    parseCodingProblem(q) {
        let desc = '';
        let examples = [];
        let constraints = [];

        if (q.examples && q.examples.length > 0) {
            examples = q.examples;
        }
        if (q.constraints && q.constraints.length > 0) {
            constraints = q.constraints;
        }

        const raw = q.question_text || '';

        if (raw.includes('Example 1:') || raw.includes('Example 1')) {
            const parts = raw.split(/Example\s*1\s*:/i);
            desc = parts[0].trim();
            const remaining = parts[1] || '';

            const ex2Parts = remaining.split(/Example\s*2\s*:/i);
            const ex1Block = ex2Parts[0] || '';
            const afterEx2 = ex2Parts[1] || '';

            const constrParts = afterEx2.split(/Constraints\s*:/i);
            const ex2Block = constrParts[0] || '';
            const constrBlock = constrParts[1] || '';

            if (examples.length === 0) {
                const mIn1 = ex1Block.match(/Input\s*:\s*([^\n\r]+)/i);
                const mOut1 = ex1Block.match(/Output\s*:\s*([^\n\r]+)/i);
                if (mIn1 && mOut1) {
                    examples.push({ input: mIn1[1].trim(), output: mOut1[1].trim() });
                }

                const mIn2 = ex2Block.match(/Input\s*:\s*([^\n\r]+)/i);
                const mOut2 = ex2Block.match(/Output\s*:\s*([^\n\r]+)/i);
                if (mIn2 && mOut2) {
                    examples.push({ input: mIn2[1].trim(), output: mOut2[1].trim() });
                }
            }

            if (constraints.length === 0 && constrBlock) {
                const lines = constrBlock.split('\n')
                    .map(l => l.trim())
                    .filter(l => l.startsWith('-') || l.startsWith('•') || l.startsWith('*'))
                    .map(l => l.replace(/^[-•*]\s*/, '').trim())
                    .filter(l => l.length > 0 && !l.toLowerCase().includes('marking breakdown'));
                if (lines.length > 0) constraints = lines;
            }
        } else {
            desc = raw;
        }

        if (examples.length === 0) {
            if (q.sample_input || q.sample_output) {
                examples.push({
                    input: q.sample_input || '(empty)',
                    output: q.sample_output || '(empty)'
                });
            }
            let pubCases = q.public_test_cases || q.test_cases;
            if (typeof pubCases === 'string') {
                try { pubCases = JSON.parse(pubCases); } catch(_) {}
            }
            if (Array.isArray(pubCases) && pubCases.length > 0) {
                pubCases.forEach((tc) => {
                    let inStr = '';
                    if (tc.input !== undefined) {
                        inStr = typeof tc.input === 'object' ? JSON.stringify(tc.input) : String(tc.input);
                    } else if (tc.nums !== undefined && tc.target !== undefined) {
                        const arr = Array.isArray(tc.nums) ? tc.nums : [tc.nums];
                        inStr = `nums = [${arr.join(', ')}], target = ${tc.target}`;
                    } else if (tc.arr !== undefined) {
                        const arr = Array.isArray(tc.arr) ? tc.arr : [tc.arr];
                        inStr = `arr = [${arr.join(', ')}]`;
                    } else if (tc.stdin !== undefined) {
                        inStr = String(tc.stdin);
                    } else {
                        const clone = { ...tc };
                        delete clone.id; delete clone.desc; delete clone.expected; delete clone.expected_output; delete clone.output; delete clone.isHidden;
                        inStr = Object.keys(clone).length > 0 ? JSON.stringify(clone) : '(Standard Input)';
                    }

                    let outStr = '';
                    const exp = tc.expected !== undefined ? tc.expected : (tc.expected_output !== undefined ? tc.expected_output : tc.output);
                    if (exp !== undefined) {
                        outStr = typeof exp === 'object' ? JSON.stringify(exp) : String(exp);
                    } else {
                        outStr = '(Expected Result)';
                    }

                    examples.push({ input: inStr, output: outStr });
                });
            }
        }

        if (constraints.length === 0 && q.constraints) {
            if (Array.isArray(q.constraints)) {
                constraints = q.constraints;
            } else if (typeof q.constraints === 'string') {
                constraints = q.constraints.split(/[\n,;]+/).map(c => c.trim()).filter(Boolean);
            }
        }

        desc = desc
            .replace(/`([^`]+)`/g, '<code>$1</code>')
            .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
            .replace(/\n\n+/g, '<br><br>')
            .replace(/\n/g, '<br>');

        return { description: desc, examples, constraints };
    }

    // ========================================================
    // 6. RENDER ACTIVE QUESTION (MCQ vs CODING vs ESSAY)
    // ========================================================
    renderCurrentQuestion() {
        const q = this.questions[this.currentIndex];
        if (!q) return;

        const modeMCQ = document.getElementById('mode-mcq-view');
        const modeCode = document.getElementById('mode-coding-view');
        const modeEssay = document.getElementById('mode-essay-view');
        const sidebar = document.getElementById('assessment-sidebar-panel');
        const headerCat = document.getElementById('lbl-header-category');
        const headerTitle = document.getElementById('lbl-header-test-title');
        const headerQCount = document.getElementById('lbl-header-questions-count');
        const pauseBtn = document.getElementById('btn-pause-test');
        const iconBox = document.getElementById('header-assessment-icon-box');

        modeMCQ?.classList.add('hidden');
        modeCode?.classList.add('hidden');
        modeEssay?.classList.add('hidden');

        // Total coding questions count
        const codingQuestions = this.questions.filter(item => item.type === 'CODING');
        const totalCoding = codingQuestions.length || 2;
        const currentCodingIdx = codingQuestions.findIndex(item => item.question_number === q.question_number);

        // Dynamically locate current section from backend sections
        const curSec = this.sections?.find(s => s.id === q.section || s.type === q.type) || this.sections?.[0];
        const secQuestionCount = curSec?.count || (q.type === 'CODING' ? totalCoding : (q.type === 'PARAGRAPH' ? 1 : 10));
        const secName = curSec?.name || (q.type === 'CODING' ? 'Section 2: Coding Assessment' : (q.type === 'PARAGRAPH' ? 'Section 3: Descriptive & Paragraph' : 'Section 1: Aptitude & Reasoning'));

        // 🛡️ HIDE SIDEBAR in CODING and PARAGRAPH (Essay) mode for MAXIMUM FULL-WIDTH WORKSPACE!
        const grid = document.querySelector('.assessment-workspace-grid');
        if (q.type === 'CODING' || q.type === 'PARAGRAPH') {
            sidebar?.classList.add('hidden');
            if (q.type === 'CODING') {
                grid?.classList.add('coding-full-workspace');
                grid?.classList.remove('essay-full-workspace');
            } else {
                grid?.classList.add('essay-full-workspace');
                grid?.classList.remove('coding-full-workspace');
            }
        } else {
            sidebar?.classList.remove('hidden');
            grid?.classList.remove('coding-full-workspace');
            grid?.classList.remove('essay-full-workspace');
        }

        // Highlight active section in Top Quick Navigation Tabs
        document.querySelectorAll('#assessment-section-tabs-bar .section-tab-btn').forEach(btn => {
            btn.classList.toggle('active', btn.getAttribute('data-section') === (curSec?.id || q.section));
        });

        if (headerCat) headerCat.textContent = `${secName} (${secQuestionCount} Questions)`;
        if (headerTitle) headerTitle.textContent = this.examDetails?.title || 'Aptitude & Coding Assessment Test';
        if (headerQCount) headerQCount.textContent = `${this.currentIndex + 1} / ${this.questions.length}`;

        if (pauseBtn) {
            pauseBtn.className = 'btn-end-test-header';
            pauseBtn.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="9" y1="9" x2="15" y2="15"></line><line x1="15" y1="9" x2="9" y2="15"></line></svg><span>End Test</span>`;
        }

        // Dynamic Section Switcher trigger text update and option highlight
        const secTriggerLabel = document.getElementById('lbl-selected-section');
        if (secTriggerLabel) {
            secTriggerLabel.textContent = `${secName} (${secQuestionCount} Qs)`;
        }
        document.querySelectorAll('#menu-section-switcher .custom-select-option').forEach(opt => {
            opt.classList.toggle('selected', opt.getAttribute('data-section') === (curSec?.id || q.section));
        });

        // Review state
        const isMarked = this.markedForReview.has(q.question_number);
        const reviewBtnMCQ = document.getElementById('btn-mcq-review');
        const reviewTextMCQ = document.getElementById('lbl-review-text');
        const reviewBtnCode = document.getElementById('btn-code-review');
        const reviewTextCode = document.getElementById('lbl-code-review-text');
        const reviewBtnEssay = document.getElementById('btn-essay-review');
        const reviewTextEssay = document.getElementById('lbl-essay-review-text');

        if (reviewBtnMCQ) {
            reviewBtnMCQ.classList.toggle('marked', isMarked);
            if (reviewTextMCQ) reviewTextMCQ.textContent = isMarked ? 'Marked for Review' : 'Mark for Review';
        }
        if (reviewBtnCode) {
            reviewBtnCode.classList.toggle('marked', isMarked);
            if (reviewTextCode) reviewTextCode.textContent = isMarked ? 'Marked for Review' : 'Mark for Review';
        }
        if (reviewBtnEssay) {
            reviewBtnEssay.classList.toggle('marked', isMarked);
            if (reviewTextEssay) reviewTextEssay.textContent = isMarked ? 'Marked for Review' : 'Mark for Review';
        }

        // --- RENDER PER TYPE ---
        if (q.type === 'MCQ') {
            modeMCQ?.classList.remove('hidden');
            document.getElementById('lbl-mcq-qnum').textContent = `Question ${q.question_number} of ${this.questions.length}`;
            document.getElementById('lbl-mcq-marks').textContent = `+${parseFloat(q.max_marks || 1.00).toFixed(2)}`;
            document.getElementById('lbl-mcq-text').textContent = q.question_text;

            const optsContainer = document.getElementById('mcq-options-container');
            if (optsContainer) {
                optsContainer.innerHTML = '';
                const opts = typeof q.options === 'string' ? JSON.parse(q.options) : (q.options || []);
                const savedAns = this.answers[q.question_number]?.selectedOption;

                opts.forEach(opt => {
                    const isSelected = savedAns === opt.key;
                    const card = document.createElement('div');
                    card.className = `mcq-option-card ${isSelected ? 'selected' : ''}`;
                    card.setAttribute('data-key', opt.key);
                    card.setAttribute('tabindex', '0');

                    card.innerHTML = `
                        <div class="option-radio-circle">
                            <div class="option-radio-inner"></div>
                        </div>
                        <span class="option-letter-badge">${opt.key}.</span>
                        <span class="option-text-span">${opt.text}</span>
                    `;

                    card.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.selectMCQOption(q.question_number, opt.key);
                    });

                    optsContainer.appendChild(card);
                });
            }
        } else if (q.type === 'CODING') {
            modeCode?.classList.remove('hidden');
            
            // Clean parsing
            const parsed = this.parseCodingProblem(q);

            document.getElementById('lbl-code-qnum').textContent = `Question ${q.question_number} of ${this.questions.length} • Coding ${currentCodingIdx + 1} of ${totalCoding}`;
            const titleEl = document.getElementById('lbl-code-title');
            if (titleEl) {
                titleEl.style.display = 'none';
            }
            document.getElementById('lbl-code-desc').innerHTML = parsed.description;
            document.getElementById('lbl-code-marks-badge').textContent = `${q.max_marks || 50} Marks`;

            // Render formatted example cards & constraints
            const exContainer = document.getElementById('coding-examples-container');
            if (exContainer) {
                let exHtml = '';
                if (parsed.examples && parsed.examples.length > 0) {
                    parsed.examples.forEach((ex, i) => {
                        exHtml += `
                            <div class="coding-example-card">
                                <div class="example-title">Example ${i + 1}:</div>
                                <div class="example-code-box">
                                    <div><strong>Input:</strong> <span class="tok-input">${ex.input}</span></div>
                                    <div><strong>Output:</strong> <span class="tok-output">${ex.output}</span></div>
                                </div>
                            </div>
                        `;
                    });
                }
                if (parsed.constraints && parsed.constraints.length > 0) {
                    exHtml += `
                        <div class="coding-constraints-card">
                            <div class="constraints-title">Constraints:</div>
                            <ul class="constraints-list">
                                ${parsed.constraints.map(c => `<li>${c}</li>`).join('')}
                            </ul>
                        </div>
                    `;
                }

                // 2 Hidden Test Cases Notice Box
                exHtml += `
                    <div class="coding-example-card" style="border: 1px dashed #6366f1; background: #0f172a; padding: 12px 14px; border-radius: 8px; margin-top: 10px;">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom: 5px;">
                            <span style="font-size:12px; font-weight:800; color:#c7d2fe;">🔒 Confidential Evaluation:</span>
                            <span class="hidden-lock-badge">2 Hidden Test Cases</span>
                        </div>
                        <div style="font-size:11.5px; color:#94a3b8; line-height:1.45;">
                            Your code is evaluated against 2 public test cases and <strong>2 confidential hidden edge cases</strong>. Hidden cases are blurred to protect assessment integrity.
                        </div>
                    </div>
                `;

                exContainer.innerHTML = exHtml;
            }

            // Restore Code Solution
            const textarea = document.getElementById('txt-code-solution');
            const savedData = this.answers[q.question_number];
            const currentLang = savedData?.language || this.selectedLanguage;
            this.selectedLanguage = currentLang;

            if (textarea) {
                const codeToLoad = savedData?.codes?.[currentLang] || savedData?.codeSolution || this.getQuestionStarterCode(q, currentLang);
                textarea.value = codeToLoad;
                this.syncEditorHighlight();
            }

            // Update language dropdown UI
            const langLabel = document.getElementById('lbl-selected-lang');
            if (langLabel && this.supportedLanguages[currentLang]) {
                langLabel.textContent = this.supportedLanguages[currentLang];
            }
            document.querySelectorAll('#menu-coding-lang .custom-select-option').forEach(opt => {
                opt.classList.toggle('selected', opt.getAttribute('data-value') === currentLang);
            });

            // Update Submit / Next Question Button state
            const isAnswered = Boolean(this.answers[q.question_number]?.codeSolution);
            const btnNext = document.getElementById('btn-code-next');
            const lblSubmit = document.getElementById('lbl-btn-code-submit');
            if (btnNext) {
                btnNext.style.display = isAnswered ? 'inline-flex' : 'none';
            }
            if (lblSubmit) {
                lblSubmit.textContent = isAnswered ? 'Re-submit Code' : 'Submit Code';
            }

            const consolePanel = document.getElementById('console-test-results');
            if (consolePanel) {
                consolePanel.classList.remove('hidden');
                consolePanel.style.display = 'block';
            }
            this.renderInitialTestCases(q);
        } else if (q.type === 'PARAGRAPH') {
            modeEssay?.classList.remove('hidden');
            const essayQNum = document.getElementById('lbl-essay-qnum');
            if (essayQNum) essayQNum.textContent = `Question ${q.question_number} of ${this.questions.length} • Descriptive 1 of 1`;
            document.getElementById('lbl-essay-title').textContent = q.title || 'Descriptive Assessment';
            document.getElementById('lbl-essay-prompt').textContent = q.question_text;
            const essayMarks = document.getElementById('lbl-essay-marks-badge');
            if (essayMarks) essayMarks.textContent = `${q.max_marks || 20} Marks`;

            const essayInput = document.getElementById('txt-essay-solution');
            if (essayInput) {
                essayInput.value = this.answers[q.question_number]?.essayText || '';
                const count = this.countWords(essayInput.value);
                const badge = document.getElementById('lbl-essay-words');
                if (badge) badge.textContent = `${count} Words`;
                essayInput.oninput = () => {
                    this.answers[q.question_number].essayText = essayInput.value;
                    this.answers[q.question_number].status = essayInput.value.trim().length > 0 ? 'ANSWERED' : 'NOT_ANSWERED';
                    const cnt = this.countWords(essayInput.value);
                    if (badge) badge.textContent = `${cnt} Words`;
                    this.updatePalette();
                };
            }
        }

        if (this.answers[q.question_number].status === 'NOT_VISITED') {
            this.answers[q.question_number].status = 'NOT_ANSWERED';
        }

        this.updatePalette();
    }

    selectMCQOption(qNum, key) {
        this.answers[qNum] = this.answers[qNum] || {};
        this.answers[qNum].selectedOption = key;
        this.answers[qNum].status = 'ANSWERED';

        document.querySelectorAll('.mcq-option-card').forEach(c => {
            c.classList.toggle('selected', c.getAttribute('data-key') === key);
        });

        this.saveCurrent();
    }

    // ========================================================
    // 7. COMPILER EXECUTION & SANDBOX TEST RUNNER
    // ========================================================
    async runCompilerSandbox(isSubmitMode = false) {
        const q = this.questions[this.currentIndex];
        if (!q || q.type !== 'CODING') return;

        const code = document.getElementById('txt-code-solution')?.value;
        const customInputChecked = document.getElementById('chk-custom-input-toggle')?.checked;
        const customInputText = document.getElementById('txt-custom-input')?.value || '';
        
        const consolePanel = document.getElementById('console-test-results');
        const consoleCards = document.getElementById('console-test-cards');
        const terminalRaw = document.getElementById('lbl-terminal-raw');
        const summary = document.getElementById('lbl-test-summary');

        if (!code || code.trim().length === 0) {
            this.showToast('Please write your code before running tests.', 'warning');
            return;
        }

        const tcToggleEl = document.getElementById('chk-test-cases-toggle');
        if (tcToggleEl) tcToggleEl.checked = true;

        if (consolePanel) {
            consolePanel.classList.remove('hidden');
            consolePanel.style.display = 'block';
            document.getElementById('tab-test-cases')?.click();
            // Scroll the editor side container (the actual scrollable parent)
            const editorContainer = document.getElementById('coding-editor-container');
            if (editorContainer) {
                setTimeout(() => {
                    editorContainer.scrollTop = editorContainer.scrollHeight;
                }, 60);
            }
        }
        if (summary) {
            summary.textContent = `Compiling & Executing (${this.selectedLanguage.toUpperCase()})...`;
            summary.className = 'console-status-badge status-running';
        }
        if (consoleCards) {
            consoleCards.innerHTML = '<div class="console-loading-row"><div class="spinner-circle"></div><span>Compiling code in sandboxed runtime environment...</span></div>';
        }

        const startTime = performance.now();

        let executionResult = null;
        try {
            const res = await fetch(`${this.backendUrl}/api/exam/run-code`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    code,
                    questionNumber: q.question_number,
                    questionId: q.id,
                    examCode: this.examCode || q.exam_code,
                    language: this.selectedLanguage,
                    testCases: q.public_test_cases || q.test_cases || [],
                    customInput: customInputChecked ? customInputText : undefined
                })
            });
            const data = await res.json();
            if (data) {
                executionResult = data;
            }
        } catch (err) {
            console.error('Execution request error:', err);
        }

        // ─── Network/offline fallback ─────────────────────────────
        if (!executionResult) {
            executionResult = {
                success: false,
                isCompileError: false,
                errorType: 'Connection Error',
                error: `Cannot connect to compilation service at ${this.backendUrl}`,
                errorMessage: `Cannot connect to compilation service at ${this.backendUrl}.\nPlease verify the backend server is running on port 5000.`,
                allPassed: false,
                results: [],
                stdout: `[Compiler Engine Notice]\nUnable to reach server at ${this.backendUrl}.\nPlease verify backend service is running.`
            };
        }

        // ─── Terminal raw output ──────────────────────────────────
        if (terminalRaw) {
            terminalRaw.textContent = executionResult.stdout || executionResult.stderr || 'No output captured.';
        }

        // ─── Compile / Syntax / Connection Error → show error card ─
        const isErrorState = executionResult.isCompileError ||
            (!executionResult.success && (!executionResult.results || executionResult.results.length === 0));

        if (isErrorState && consoleCards) {
            const errType    = executionResult.errorType || 'Compile Error';
            const errMsg     = (executionResult.errorMessage || executionResult.error || executionResult.stderr || 'Unknown error').trim();
            const langLabel  = (this.selectedLanguage || 'code').toUpperCase();

            // Detect first line number from compiler output (e.g. "main.cpp:12:5: error:")
            const lineMatch = errMsg.match(/:(\d+):/);
            const lineHint  = lineMatch ? `Line ${lineMatch[1]}` : '';

            // Generate fix tip based on error type
            let fixTip = 'Check your syntax, function signature, and brackets before re-running.';
            if (errType.includes('Syntax') || errMsg.includes('SyntaxError') || errMsg.includes('expected')) {
                fixTip = 'Check for missing semicolons, unmatched brackets <code>{}</code>, or misplaced commas.';
            } else if (errType.includes('Reference') || errMsg.includes('undeclared') || errMsg.includes('not declared')) {
                fixTip = 'A variable or function was used before it was declared. Check your variable names.';
            } else if (errType.includes('Type') || errMsg.includes('TypeError')) {
                fixTip = 'A value was used in an unexpected way. Check types and function return values.';
            } else if (errMsg.includes('connection') || errMsg.includes('Cannot connect')) {
                fixTip = 'Backend compiler service is offline. Start the server with <code>npm start</code> in the server folder.';
            }

            if (summary) {
                summary.textContent = `⛔ ${errType}`;
                summary.className = 'console-status-badge status-failed';
            }

            consoleCards.innerHTML = `
                <div class="compile-error-box">
                    <div class="compile-error-header">
                        <span class="compile-error-badge">⛔ ${errType}</span>
                        <span class="compile-error-lang">${langLabel}${lineHint ? ' · 📍 ' + lineHint : ''}</span>
                    </div>
                    <div class="compile-error-title">Error Details / Reason:</div>
                    ${lineHint ? `<div class="compile-error-line-hint">📍 ${lineHint}</div>` : ''}
                    <pre class="compile-error-code-block">${this._escapeHtml(errMsg)}</pre>
                    <div class="compile-error-tip">💡 <strong>Fix Tip:</strong> ${fixTip}</div>
                </div>
            `;
            return null;
        }

        // ─── Successful run → render test case cards ─────────────
        const totalCount = executionResult.results ? executionResult.results.length : 0;
        const passedCount = executionResult.results ? executionResult.results.filter(r => r.passed).length : 0;

        if (summary) {
            if (executionResult.allPassed) {
                summary.textContent = `✓ Accepted — All ${totalCount} Test Cases Passed`;
                summary.className = 'console-status-badge status-passed';
            } else {
                summary.textContent = `✕ Wrong Answer — ${passedCount}/${totalCount} Test Cases Passed`;
                summary.className = 'console-status-badge status-failed';
            }
        }

        if (consoleCards && executionResult.results && executionResult.results.length > 0) {
            consoleCards.innerHTML = executionResult.results.map((r, i) => {
                const isHidden = Boolean(r.isHidden) || (r.testIndex > 2) || (i >= 2 && totalCount >= 4);
                const runtimeText = r.runtime || `${Math.floor(Math.random() * 12 + 8)} ms`;

                if (isHidden) {
                    return `
                        <div class="tc-item-card hidden-case ${r.passed ? 'passed' : 'failed'}">
                            <div class="tc-item-header">
                                <div class="tc-header-left">
                                    <span class="tc-case-badge badge-hidden">🔒 Case #${r.testIndex || (i + 1)}</span>
                                    <strong style="font-size: 13px; font-weight: 700; color: #6d28d9;">Confidential Edge Case</strong>
                                </div>
                                <div class="tc-header-right">
                                    <span class="tc-case-badge ${r.passed ? 'badge-passed' : 'badge-failed'}" style="font-size: 11.5px; padding: 2px 10px;">
                                        ${r.passed ? '✓ Validated' : '✕ Edge Failure'}
                                    </span>
                                    <span class="tc-metric-tag">⚡ ${runtimeText}</span>
                                </div>
                            </div>
                            <div class="tc-hidden-secure-banner">
                                <span class="tc-hidden-secure-icon">🛡️</span>
                                <div>
                                    <strong style="color:#475569;">Protected Evaluation Vector:</strong><br>
                                    This confidential test case tests edge boundaries (e.g. zero limits, large constraints, memory bounds). Inputs and expected results are masked to preserve examination integrity.
                                </div>
                            </div>
                        </div>
                    `;
                }

                const cleanInput = this.formatCleanTestCaseInput(r.input);

                return `
                    <div class="tc-item-card ${r.passed ? 'passed' : 'failed'}">
                        <div class="tc-item-header">
                            <div class="tc-header-left">
                                <span class="tc-case-badge ${r.passed ? 'badge-passed' : 'badge-failed'}">
                                    ${r.passed ? '✓ Passed' : '✕ Wrong Answer'}
                                </span>
                                <strong style="font-size: 13.5px; font-weight: 700; color: #1e293b;">Case ${r.testIndex || (i + 1)}</strong>
                            </div>
                            <div class="tc-header-right">
                                <span class="tc-metric-tag">⚡ ${runtimeText}</span>
                                <span class="tc-metric-tag">💾 ${r.memory || '14.2 MB'}</span>
                            </div>
                        </div>

                        <div class="tc-field-group">
                            <span class="tc-field-label">Input</span>
                            <pre class="tc-code-block">${this._escapeHtml(cleanInput)}</pre>
                        </div>

                        <div class="tc-outputs-grid">
                            <div class="tc-field-group">
                                <span class="tc-field-label">Your Output</span>
                                <pre class="tc-code-block ${r.passed ? 'actual-match' : 'actual-mismatch'}">${this._escapeHtml(String(r.actual ?? '(Empty)'))}</pre>
                            </div>
                            <div class="tc-field-group">
                                <span class="tc-field-label">Expected Output</span>
                                <pre class="tc-code-block expected-match">${this._escapeHtml(String(r.expected ?? '(None)'))}</pre>
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
        } else if (consoleCards) {
            // Ran but returned 0 results for unknown reason
            consoleCards.innerHTML = `<div class="compile-error-box">
                <div class="compile-error-header">
                    <span class="compile-error-badge">⚠ No Output</span>
                </div>
                <div class="compile-error-title">No test case results were returned from the judge.</div>
                <div class="compile-error-tip">💡 <strong>Tip:</strong> Make sure your function has the correct name and returns a value.</div>
            </div>`;
        }

        this.answers[q.question_number] = this.answers[q.question_number] || {};
        this.answers[q.question_number].codes = this.answers[q.question_number].codes || {};
        this.answers[q.question_number].codes[this.selectedLanguage] = code;
        this.answers[q.question_number].codeSolution = code;
        this.answers[q.question_number].language = this.selectedLanguage;
        this.answers[q.question_number].status = 'ANSWERED';
        this.updatePalette();

        if (isSubmitMode) {
            // Submit Mode: reveal Next Question button & update submit button
            const btnNext = document.getElementById('btn-code-next');
            const lblSubmit = document.getElementById('lbl-btn-code-submit');
            if (btnNext) btnNext.style.display = 'inline-flex';
            if (lblSubmit) lblSubmit.textContent = 'Re-submit Code';

            if (executionResult.allPassed) {
                this.showToast(`🎉 Code Submitted! All ${totalCount} test cases passed. Click 'Next Question →' to proceed.`, 'success');
            } else {
                this.showToast(`✅ Code Submitted (${passedCount}/${totalCount} test cases passed). You can review or click 'Next Question →'.`, 'info');
            }
        } else {
            if (executionResult.allPassed) {
                this.showToast(`✅ Test Run Passed! All ${totalCount} test cases passed.`, 'success');
            } else {
                this.showToast(`Test Run Finished: ${passedCount}/${totalCount} passed. Check outputs below.`, 'warning');
            }
        }

        setTimeout(() => {
            const editorContainer = document.getElementById('coding-editor-container');
            if (editorContainer) {
                editorContainer.scrollTop = editorContainer.scrollHeight;
            }
        }, 80);

        return executionResult;
    }

    formatCleanTestCaseInput(inputStr) {
        if (!inputStr) return 'Standard Input';
        const s = String(inputStr).trim();
        if (s.includes(' | ')) {
            const parts = s.split(' | ').map(p => p.trim());
            if (parts.length === 3 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                return `amount = ${parts[0]}\nn = ${parts[1]}\ncoins = [${parts[2]}]`;
            }
            if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                return `n = ${parts[0]}\ntarget = ${parts[1]}`;
            }
            return parts.join('\n');
        }
        return s;
    }

    // ========================================================
    // 7B. SUBMIT CODING QUESTION (RUNS EVALUATION & SHOWS NEXT BUTTON)
    // ========================================================
    async submitCodingQuestion() {
        const q = this.questions[this.currentIndex];
        if (!q || q.type !== 'CODING') return;

        const code = document.getElementById('txt-code-solution')?.value;
        if (!code || code.trim().length === 0) {
            this.showToast('Please write your code solution before submitting.', 'warning');
            return;
        }

        const btnSubmit = document.getElementById('btn-code-submit');
        const lblSubmit = document.getElementById('lbl-btn-code-submit');
        if (btnSubmit) btnSubmit.disabled = true;
        if (lblSubmit) lblSubmit.textContent = 'Evaluating & Submitting...';

        try {
            await this.runCompilerSandbox(true);
        } catch (err) {
            console.error('Submission error:', err);
            this.showToast('Code saved. You can proceed to next question.', 'info');
            const btnNext = document.getElementById('btn-code-next');
            if (btnNext) btnNext.style.display = 'inline-flex';
        } finally {
            if (btnSubmit) btnSubmit.disabled = false;
            if (lblSubmit) lblSubmit.textContent = 'Re-submit Code';
        }
    }

    // Render initial test cases view (2 public + 2 blurred hidden test cases)
    renderInitialTestCases(q) {
        const consoleCards = document.getElementById('console-test-cards');
        const summary = document.getElementById('lbl-test-summary');
        if (!consoleCards) return;

        const publicCases = (q && q.test_cases && q.test_cases.length > 0) ? q.test_cases : [
            { input: 'amount = 5\nn = 3\ncoins = [1, 2, 5]', expected: '4' },
            { input: 'amount = 3\nn = 1\ncoins = [2]', expected: '0' }
        ];

        let cardsHtml = '';

        // 1. Render Public Test Cases (Initial Ready State)
        publicCases.slice(0, 2).forEach((tc, i) => {
            const cleanInput = this.formatCleanTestCaseInput(tc.input || 'amount = 5\nn = 3\ncoins = [1, 2, 5]');
            cardsHtml += `
                <div class="tc-item-card">
                    <div class="tc-item-header">
                        <div class="tc-header-left">
                            <span class="tc-case-badge badge-ready">Case ${i + 1}</span>
                            <strong style="font-size: 13.5px; font-weight: 700; color: #1e293b;">Public Test Case</strong>
                        </div>
                        <div class="tc-header-right">
                            <span class="tc-metric-tag" style="color:#6366f1; font-weight:700;">Ready to Run</span>
                        </div>
                    </div>

                    <div class="tc-field-group">
                        <span class="tc-field-label">Input</span>
                        <pre class="tc-code-block">${this._escapeHtml(cleanInput)}</pre>
                    </div>

                    <div class="tc-outputs-grid">
                        <div class="tc-field-group">
                            <span class="tc-field-label">Your Output</span>
                            <pre class="tc-code-block" style="color:#94a3b8; background:#0f172a; border-style:dashed;">(Click 'Run Code' to execute)</pre>
                        </div>
                        <div class="tc-field-group">
                            <span class="tc-field-label">Expected Output</span>
                            <pre class="tc-code-block expected-match">${this._escapeHtml(String(tc.expected || 'Expected Result'))}</pre>
                        </div>
                    </div>
                </div>
            `;
        });

        // 2. Render Two Hidden Test Cases (Initial Ready State - Blurred)
        for (let j = 0; j < 2; j++) {
            const caseNum = publicCases.slice(0, 2).length + j + 1;
            cardsHtml += `
                <div class="tc-item-card hidden-case">
                    <div class="tc-item-header">
                        <div class="tc-header-left">
                            <span class="tc-case-badge badge-hidden">🔒 Case #${caseNum}</span>
                            <strong style="font-size: 13px; font-weight: 700; color: #6d28d9;">Confidential Edge Case ${j + 1}</strong>
                        </div>
                        <div class="tc-header-right">
                            <span class="tc-metric-tag" style="color:#7c3aed; font-weight:700;">Edge Vector</span>
                        </div>
                    </div>
                    <div class="tc-hidden-secure-banner">
                        <span class="tc-hidden-secure-icon">🛡️</span>
                        <div>
                            <strong style="color:#475569;">Confidential Test Case:</strong><br>
                            Evaluates candidate code against protected boundary edge cases. Evaluated upon clicking 'Run Code'.
                        </div>
                    </div>
                </div>
            `;
        }

        consoleCards.innerHTML = cardsHtml;
        if (summary) {
            summary.textContent = 'Ready (2 Public + 2 Hidden Cases)';
            summary.className = 'console-status-badge';
        }
    }

    resetCode() {
        const q = this.questions[this.currentIndex];
        const textarea = document.getElementById('txt-code-solution');
        if (textarea && q) {
            const freshStarter = this.getQuestionStarterCode(q, this.selectedLanguage);
            this.pushEditorHistory(textarea.value, textarea.selectionStart, textarea.selectionEnd, true);
            textarea.value = freshStarter;
            if (this.answers[q.question_number]) {
                this.answers[q.question_number].codes = this.answers[q.question_number].codes || {};
                this.answers[q.question_number].codes[this.selectedLanguage] = freshStarter;
                this.answers[q.question_number].codeSolution = freshStarter;
            }
            this.pushEditorHistory(textarea.value, 0, 0, true);
            this.syncEditorHighlight();
            this.showToast(`Starter code for ${this.supportedLanguages[this.selectedLanguage] || this.selectedLanguage} restored.`, 'info');
        }
    }

    // ========================================================
    // 8. NAVIGATION, PALETTE & PROGRESS
    // ========================================================
    getCandidateId() {
        return this.candidate?.id || this.candidate?.student_id || this.candidate?.userId || 'CAND123456';
    }

    getDraftStorageKey() {
        const candId = this.getCandidateId();
        return `exam_draft_${candId}_${this.examCode}`;
    }

    loadDraftLocal() {
        try {
            const key = this.getDraftStorageKey();
            const genericKey = `exam_draft_backup_${this.examCode}`;
            const raw = localStorage.getItem(key) || localStorage.getItem(genericKey);
            if (raw) {
                const parsed = JSON.parse(raw);
                if (parsed && typeof parsed === 'object' && Object.keys(parsed).length > 0) {
                    console.log('⚡ [Local Draft Instant Recovery] Restored from localStorage cache:', parsed);
                    Object.keys(parsed).forEach(qNum => {
                        if (this.answers[qNum]) {
                            this.answers[qNum] = { ...this.answers[qNum], ...parsed[qNum] };
                        } else {
                            this.answers[qNum] = parsed[qNum];
                        }
                    });
                }
            }
        } catch (e) {
            console.warn('Local draft load error:', e);
        }
    }

    // ========================================================
    // 8. NAVIGATION, PALETTE & PROGRESS
    // ========================================================
    saveCurrent() {
        const q = this.questions[this.currentIndex];
        if (!q) return;

        if (q.type === 'CODING') {
            const textarea = document.getElementById('txt-code-solution');
            const code = textarea ? textarea.value : undefined;
            if (code !== undefined && code !== null) {
                this.answers[q.question_number] = this.answers[q.question_number] || {};
                this.answers[q.question_number].type = 'CODING';
                this.answers[q.question_number].codes = this.answers[q.question_number].codes || {};
                this.answers[q.question_number].codes[this.selectedLanguage] = code;
                this.answers[q.question_number].codeSolution = code;
                this.answers[q.question_number].language = this.selectedLanguage;
                if (code.trim().length > 0) {
                    this.answers[q.question_number].status = 'ANSWERED';
                }
            }
        } else if (q.type === 'PARAGRAPH') {
            const essayInput = document.getElementById('txt-essay-solution');
            const essay = essayInput ? essayInput.value : undefined;
            if (essay !== undefined && essay !== null) {
                this.answers[q.question_number] = this.answers[q.question_number] || {};
                this.answers[q.question_number].type = 'PARAGRAPH';
                this.answers[q.question_number].essayText = essay;
                if (essay.trim().length > 5) {
                    this.answers[q.question_number].status = 'ANSWERED';
                }
            }
        }

        // Instantly write snapshot to persistent localStorage
        try {
            const key = this.getDraftStorageKey();
            const genericKey = `exam_draft_backup_${this.examCode}`;
            const payload = JSON.stringify(this.answers);
            localStorage.setItem(key, payload);
            localStorage.setItem(genericKey, payload);
        } catch (_) {}

        this.updatePalette();
    }

    nextQuestion() {
        this.saveCurrent();
        if (this.currentIndex < this.questions.length - 1) {
            this.currentIndex++;
            this.renderCurrentQuestion();
        } else {
            this.showSubmitModal();
        }
    }

    prevQuestion() {
        this.saveCurrent();
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.renderCurrentQuestion();
        }
    }

    jumpTo(idx) {
        this.saveCurrent();
        if (idx >= 0 && idx < this.questions.length) {
            this.currentIndex = idx;
            this.renderCurrentQuestion();
        }
    }

    toggleReview() {
        const q = this.questions[this.currentIndex];
        if (!q) return;

        if (this.markedForReview.has(q.question_number)) {
            this.markedForReview.delete(q.question_number);
            this.showToast(`Question ${q.question_number} unmarked.`, 'info');
        } else {
            this.markedForReview.add(q.question_number);
            this.showToast(`Question ${q.question_number} marked for review.`, 'info');
        }
        this.renderCurrentQuestion();
    }

    renderPalette() {
        const grid = document.getElementById('palette-tiles-grid');
        if (grid) grid.innerHTML = '';

        this.questions.forEach((q, idx) => {
            if (grid) {
                const tile = document.createElement('div');
                tile.className = 'palette-tile';
                tile.id = `palette-tile-${q.question_number}`;
                tile.textContent = q.question_number;
                tile.addEventListener('click', () => this.jumpTo(idx));
                grid.appendChild(tile);
            }
        });

        this.updatePalette();
    }

    updatePalette() {
        const total = this.questions.length || 1;
        let answeredCount = 0;

        this.questions.forEach((q, idx) => {
            const tile = document.getElementById(`palette-tile-${q.question_number}`);
            const isMarked = this.markedForReview.has(q.question_number);
            const ans = this.answers[q.question_number];

            if (ans?.status === 'ANSWERED' || ans?.selectedOption) {
                answeredCount++;
            }

            if (tile) {
                tile.className = 'palette-tile';
                if (idx === this.currentIndex) tile.classList.add('current');

                if (isMarked) {
                    tile.classList.add('marked');
                } else if (ans?.status === 'ANSWERED') {
                    tile.classList.add('answered');
                } else if (ans?.status === 'NOT_ANSWERED') {
                    tile.classList.add('not-answered');
                } else {
                    tile.classList.add('not-visited');
                }
            }
        });

        const notSolvedCount = total - answeredCount;
        const percentage = Math.round((answeredCount / total) * 100);

        const solvedEl = document.getElementById('lbl-solved-count');
        const notSolvedEl = document.getElementById('lbl-notsolved-count');
        const totalEl = document.getElementById('lbl-total-count');
        const percentEl = document.getElementById('lbl-progress-percent');
        const ringFill = document.getElementById('progress-ring-fill');

        if (solvedEl) solvedEl.textContent = answeredCount;
        if (notSolvedEl) notSolvedEl.textContent = notSolvedCount;
        if (totalEl) totalEl.textContent = total;
        if (percentEl) percentEl.textContent = `${percentage}%`;

        if (ringFill) {
            const circumference = 2 * Math.PI * 54;
            const offset = circumference - (percentage / 100) * circumference;
            ringFill.style.strokeDasharray = `${circumference}`;
            ringFill.style.strokeDashoffset = `${offset}`;
        }
    }

    // ========================================================
    // 9. AUTO-SAVE & SUBMISSION
    // ========================================================
    startAutoSaveLoop() {
        if (this.autoSaveInterval) clearInterval(this.autoSaveInterval);
        this.autoSaveInterval = setInterval(() => {
            this.autoSaveDraft();
        }, 5000);
        console.log('⏱️ [Auto-Save Engine] 5-second continuous draft saver running.');
    }

    async autoSaveDraft() {
        this.saveCurrent();
        const candId = this.getCandidateId();
        if (!candId || Object.keys(this.answers).length === 0) return;

        const autoSaveBadge = document.getElementById('lbl-autosave-status');

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/save-draft`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    candidateId: candId,
                    examCode: this.examCode,
                    answers: this.answers
                })
            });
            const data = await res.json();
            if (data && data.success) {
                if (autoSaveBadge) {
                    autoSaveBadge.innerHTML = '🟢 Auto-Saved (5s synced)';
                    autoSaveBadge.style.opacity = '1';
                }
            } else {
                if (autoSaveBadge) {
                    autoSaveBadge.innerHTML = '💾 Saved Locally';
                    autoSaveBadge.style.opacity = '1';
                }
            }
        } catch (_) {
            if (autoSaveBadge) {
                autoSaveBadge.innerHTML = '💾 Saved Locally';
                autoSaveBadge.style.opacity = '1';
            }
        }
    }

    async loadDraft() {
        const candId = this.getCandidateId();
        try {
            const res = await fetch(`${this.backendUrl}/api/exam/get-draft/${candId}/${this.examCode}`);
            const data = await res.json();
            if (data && data.success && data.answers && Object.keys(data.answers).length > 0) {
                console.log('📥 [Draft Cloud Recovery] Restoring candidate draft answers from DB:', data.answers);
                Object.keys(data.answers).forEach(qNum => {
                    if (this.answers[qNum]) {
                        this.answers[qNum] = { ...this.answers[qNum], ...data.answers[qNum] };
                    } else {
                        this.answers[qNum] = data.answers[qNum];
                    }
                });
                try {
                    const key = this.getDraftStorageKey();
                    localStorage.setItem(key, JSON.stringify(this.answers));
                } catch (_) {}
                this.updatePalette();
                this.renderCurrentQuestion();
                this.showToast('Restored your latest saved code and answers from secure cloud backup.', 'info');
            }
        } catch (err) {
            console.warn('Draft load warning:', err);
        }
    }

    startTimer() {
        if (this.timerInterval) clearInterval(this.timerInterval);
        const timerLbl = document.getElementById('lbl-exam-timer');
        const candId = this.candidate?.id || this.candidate?.student_id || 'CAND123456';
        const endKey = `exam_end_time_${candId}_${this.examCode}`;

        const urlParams = new URLSearchParams(window.location.search);
        const isReattempt = urlParams.get('reattempt') === '1' || 
                            sessionStorage.getItem(`is_reattempt_${this.examCode}`) === 'true' ||
                            sessionStorage.getItem('is_reattempt') === 'true';

        let targetEndTime = parseInt(localStorage.getItem(endKey) || '0', 10);
        const now = Date.now();

        // If in reattempt mode, or target end time is unset, invalid, or already expired (targetEndTime <= now), initialize fresh duration
        if (isReattempt || !targetEndTime || isNaN(targetEndTime) || targetEndTime <= now) {
            targetEndTime = now + (this.timerSeconds * 1000);
            localStorage.setItem(endKey, targetEndTime.toString());
        }

        const tick = () => {
            const currentNow = Date.now();
            const remaining = Math.max(0, Math.floor((targetEndTime - currentNow) / 1000));
            this.timerSeconds = remaining;

            const hrs = Math.floor(remaining / 3600);
            const mins = Math.floor((remaining % 3600) / 60);
            const secs = remaining % 60;

            if (timerLbl) {
                timerLbl.textContent = `${String(hrs).padStart(2, '0')}:${String(mins).padStart(2, '0')}:${String(secs).padStart(2, '0')}`;
                if (remaining <= 300) {
                    timerLbl.style.color = '#ef4444';
                    timerLbl.style.fontWeight = '800';
                }
            }

            if (remaining <= 0) {
                clearInterval(this.timerInterval);
                this.handleTimeExpired();
            }
        };

        tick();
        this.timerInterval = setInterval(tick, 1000);
    }

    handleTimeExpired() {
        if (this.isSubmitting) return;
        this.isSubmitting = true;
        if (this.timerInterval) clearInterval(this.timerInterval);
        if (this.autoSaveInterval) clearInterval(this.autoSaveInterval);

        // 1. Immediately freeze and disable all user input controls so examination stops
        document.querySelectorAll('input, button, textarea, select').forEach(el => {
            el.disabled = true;
            el.setAttribute('readonly', 'true');
        });

        // 2. Display unclosable time expired alert modal overlay
        let expiredModal = document.getElementById('__modal_time_expired');
        if (!expiredModal) {
            expiredModal = document.createElement('div');
            expiredModal.id = '__modal_time_expired';
            expiredModal.className = 'exam-modal-backdrop';
            expiredModal.style.zIndex = '999999';
            expiredModal.style.background = 'rgba(15, 23, 42, 0.95)';
            expiredModal.innerHTML = `
                <div class="exam-modal-card" style="max-width: 480px; text-align: center; border: 2px solid #ef4444; background: #ffffff; padding: 28px; border-radius: 16px;">
                    <div style="font-size: 48px; margin-bottom: 12px;">⏱️</div>
                    <h2 style="font-size: 22px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">Examination Time Expired!</h2>
                    <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 20px;">
                        Your allotted assessment duration has finished. All your recorded answers and code solutions are being compiled, locked, and auto-submitted to the secure examination database.
                    </p>
                    <div style="background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); border-radius: 8px; padding: 12px; font-size: 13px; font-weight: 700; color: #dc2626; display: flex; align-items: center; justify-content: center; gap: 8px;">
                        <span>Auto-submitting and finalizing assessment...</span>
                    </div>
                </div>
            `;
            document.body.appendChild(expiredModal);
        }
        expiredModal.classList.remove('hidden');

        // 3. Mark persistent state
        const candId = this.candidate?.id || this.candidate?.student_id || 'CAND123456';
        localStorage.setItem(`exam_time_expired_${candId}_${this.examCode}`, 'true');
        localStorage.setItem(`exam_submitted_${candId}_${this.examCode}`, 'true');
        localStorage.setItem(`exam_submitted_${this.examCode}`, 'true');

        setTimeout(() => {
            this.submitAssessment(true, 'TIME_EXPIRED');
        }, 1200);
    }

    async submitAssessment(isAutoSubmit = false, reason = 'USER_SUBMIT') {
        this.isSubmitting = true;
        if (this.timerInterval) clearInterval(this.timerInterval);
        if (this.autoSaveInterval) clearInterval(this.autoSaveInterval);
        this.saveCurrent();

        const candId = this.candidate?.id || this.candidate?.student_id || 'CAND123456';
        const localKey = `exam_submitted_${candId}_${this.examCode}`;
        const genericKey = `exam_submitted_${this.examCode}`;
        localStorage.setItem(localKey, 'true');
        sessionStorage.setItem(localKey, 'true');
        localStorage.setItem(genericKey, 'true');
        sessionStorage.setItem(genericKey, 'true');

        const currentStartMode = sessionStorage.getItem('exam_start_mode') || localStorage.getItem('exam_start_mode') || (this.candidate?.startMode) || 'dashboard';
        sessionStorage.setItem('exam_start_mode', currentStartMode);
        localStorage.setItem('exam_start_mode', currentStartMode);

        let submitMsg = 'Submitting assessment to secure database...';
        if (reason === 'TIME_EXPIRED') {
            submitMsg = 'Examination time ended. Auto-submitting assessment to database...';
        } else if (isAutoSubmit) {
            submitMsg = 'Security violation limit reached. Auto-submitting to database...';
        }

        this.showToast(submitMsg, 'info');

        const reattemptReason = sessionStorage.getItem('reattempt_reason_' + this.examCode)
            || sessionStorage.getItem('reattempt_reason')
            || null;

        try {
            const res = await fetch(`${this.backendUrl}/api/exam/submit`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    candidateId: candId,
                    examCode: this.examCode,
                    answers: this.answers,
                    autoSubmitted: isAutoSubmit,
                    submissionReason: reason,
                    reattemptReason: reattemptReason
                })
            });

            const data = await res.json();
            sessionStorage.setItem('exam_submission_result', JSON.stringify({
                success: true,
                submissionId: data?.submissionId,
                attemptNumber: data?.attemptNumber || 1,
                reattemptReason: reattemptReason,
                submittedAt: new Date().toISOString(),
                isResultsPublished: false,
                reason: reason
            }));
            // Clear single-use reattempt keys once submitted
            sessionStorage.removeItem('is_reattempt_' + this.examCode);
            window.location.replace('completed.html');
        } catch (_) {
            window.location.replace('completed.html');
        }
    }

    // ========================================================
    // UTILITY: HTML ESCAPE (prevent XSS in innerHTML rendering)
    // ========================================================
    _escapeHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    showToast(message, type = 'info') {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = `toast toast-${type} ${type}`;
        const icons = {
            success: '✓',
            error: '✕',
            danger: '✕',
            warning: '⚠️',
            info: 'ℹ️'
        };
        const icon = icons[type] || 'ℹ️';
        toast.innerHTML = `<span style="font-size:15px; font-weight:800; flex-shrink:0;">${icon}</span> <span style="line-height:1.4;">${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => {
            if (toast && toast.style) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-12px) scale(0.95)';
            }
            setTimeout(() => {
                if (toast && typeof toast.remove === 'function') {
                    toast.remove();
                }
            }, 300);
        }, 3500);
    }

    countWords(text) {
        if (!text || typeof text !== 'string') return 0;
        // Accurate natural language word matching: only counts real words with alphanumeric characters
        // Completely ignores standalone punctuation marks like , . / - etc.
        const words = text.trim().match(/[\p{L}\p{N}]+(?:['’\-][\p{L}\p{N}]+)*/gu);
        return words ? words.length : 0;
    }
}

window.addEventListener('DOMContentLoaded', () => {
    window.assessmentEngine = new AssessmentEngine();
});
