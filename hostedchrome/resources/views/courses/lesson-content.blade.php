<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $lesson->lesson_num }}: {{ $lesson->lesson_title }} - {{ $course->title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        html, body {
            height: 100vh;
            width: 100vw;
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #f8fafc;
            color: #0f172a;
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            -webkit-font-smoothing: antialiased;
        }

        .study-viewport {
            display: flex;
            flex-direction: column;
            height: 100vh;
            width: 100vw;
            overflow: hidden;
        }

        /* TOP NAVIGATION BAR */
        .study-top-nav {
            height: 64px;
            min-height: 64px;
            background: #ffffff;
            border-bottom: 1.5px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 20px;
            gap: 16px;
            z-index: 50;
            flex-shrink: 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .nav-left-group { display: flex; align-items: center; gap: 10px; min-width: 0; }
        
        .btn-nav-back {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            color: #4f46e5;
            text-decoration: none;
            font-size: 12.5px;
            font-weight: 700;
            background: #f5f3ff;
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid #e0e7ff;
            transition: all 0.15s;
            cursor: pointer;
            white-space: nowrap;
        }
        .btn-nav-back:hover { background: #ede9fe; color: #4338ca; }
        
        .btn-toggle-curriculum { 
            background: #ffffff; 
            border: 1.5px solid #cbd5e1; 
            color: #334155; 
            border-radius: 8px; 
            padding: 7px 12px; 
            font-size: 12px; 
            font-weight: 700; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 6px; 
            transition: all 0.15s; 
            white-space: nowrap;
        }
        .btn-toggle-curriculum:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }
        .btn-toggle-curriculum.active-drawer { background: #ede9fe; border-color: #c7d2fe; color: #4f46e5; }

        .nav-topic-title-group {
            display: flex;
            flex-direction: column;
            overflow: hidden;
        }
        .nav-topic-title-group h2 { 
            font-size: 14px; 
            font-weight: 800; 
            color: #0f172a; 
            line-height: 1.2; 
            margin: 0; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }
        .nav-topic-title-group span { 
            font-size: 11px; 
            color: #64748b; 
            font-weight: 600; 
            white-space: nowrap; 
            overflow: hidden; 
            text-overflow: ellipsis; 
        }

        /* 3 MODE TABS IN HEADER */
        .mode-tabs-center { 
            display: flex; 
            align-items: center; 
            background: #f1f5f9; 
            padding: 4px; 
            border-radius: 10px; 
            gap: 4px; 
            flex-shrink: 0;
        }
        .mode-tab-btn {
            border: none;
            background: transparent;
            padding: 7px 14px;
            border-radius: 7px;
            font-size: 12px;
            font-weight: 700;
            color: #64748b;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .mode-tab-btn:hover { color: #0f172a; }
        .mode-tab-btn.active {
            background: #ffffff;
            color: #4f46e5;
            box-shadow: 0 2px 5px rgba(0,0,0,0.06);
        }

        .nav-right-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }

        .status-badge-chip { 
            font-size: 11.5px; 
            font-weight: 800; 
            padding: 6px 12px; 
            border-radius: 7px; 
            display: inline-flex; 
            align-items: center; 
            gap: 5px; 
            white-space: nowrap;
        }
        .badge-done-green { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-inprog-blue { background: #ede9fe; color: #4f46e5; border: 1px solid #ddd6fe; }

        .btn-download-pdf-top {
            background: #ffffff;
            border: 1.5px solid #4f46e5;
            color: #4f46e5;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-decoration: none;
            transition: all 0.15s;
            white-space: nowrap;
        }
        .btn-download-pdf-top:hover { background: #4f46e5; color: #ffffff; }

        .btn-ai-autofill-top {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 7px 13px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            transition: opacity 0.15s;
            white-space: nowrap;
        }
        .btn-ai-autofill-top:hover { opacity: 0.9; }

        .btn-edit-content-top {
            background: #ffffff;
            color: #334155;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            white-space: nowrap;
            transition: all 0.15s;
        }
        .btn-edit-content-top:hover { background: #f8fafc; border-color: #94a3b8; }

        /* BODY LAYOUT (DRAWER + CONTENT) */
        .study-body-wrap { 
            display: flex; 
            flex: 1; 
            height: calc(100vh - 64px); 
            overflow: hidden; 
            position: relative; 
            width: 100%; 
        }

        /* TOPIC DRAWER (SIDEBAR) */
        .topic-drawer { 
            width: 320px; 
            min-width: 320px; 
            max-width: 320px;
            background: #ffffff; 
            border-right: 1.5px solid #e2e8f0; 
            height: 100%; 
            overflow-y: auto; 
            display: flex; 
            flex-direction: column; 
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1), min-width 0.25s cubic-bezier(0.16, 1, 0.3, 1), max-width 0.25s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.2s ease; 
            z-index: 10; 
            flex-shrink: 0;
        }
        .topic-drawer.collapsed { 
            width: 0 !important; 
            min-width: 0 !important; 
            max-width: 0 !important; 
            padding: 0 !important;
            border-right: none !important; 
            overflow: hidden !important; 
            opacity: 0 !important; 
            pointer-events: none !important;
            visibility: hidden !important;
        }
        .btn-drawer-collapse { 
            background: #f1f5f9; 
            border: 1px solid #cbd5e1; 
            color: #64748b; 
            border-radius: 6px; 
            width: 24px; 
            height: 24px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            cursor: pointer; 
            font-size: 11px; 
            font-weight: 800; 
            transition: all 0.15s; 
        }
        .btn-drawer-collapse:hover { background: #e2e8f0; color: #0f172a; }

        .drawer-header { padding: 14px 18px; border-bottom: 1px solid #f1f5f9; display: flex; align-items: center; justify-content: space-between; flex-shrink: 0; }
        .drawer-header strong { font-size: 13px; font-weight: 800; color: #0f172a; }
        .drawer-module-group { padding: 8px 12px; overflow-y: auto; flex: 1; }
        .drawer-mod-title { font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; padding: 8px 8px 4px 8px; margin-top: 6px; }
        .drawer-lesson-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 9px 12px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
            color: #334155;
            text-decoration: none;
            margin-bottom: 3px;
            transition: all 0.15s;
        }
        .drawer-lesson-item:hover { background: #f8fafc; color: #4f46e5; }
        .drawer-lesson-item.active { background: #ede9fe; color: #4f46e5; font-weight: 800; }
        .drawer-check-icon { font-size: 12px; }
        .drawer-check-done { color: #10b981; font-weight: 800; }

        /* MAIN CONTENT AREA */
        .study-main-pane { 
            flex: 1; 
            height: 100%; 
            overflow-y: auto; 
            padding: 24px 36px; 
            background: #f8fafc; 
        }

        /* TAB 1: LESSON STUDY UI */
        .lesson-notes-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 32px 36px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            margin-bottom: 24px;
        }
        .lesson-hero-header { 
            display: flex; 
            align-items: flex-start; 
            justify-content: space-between; 
            margin-bottom: 20px; 
            border-bottom: 1px solid #f1f5f9; 
            padding-bottom: 18px; 
            gap: 16px;
        }
        .lesson-hero-header h1 { font-size: 22px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
        .lesson-hero-header p { font-size: 13.5px; color: #64748b; line-height: 1.5; }

        .lesson-notes-body { 
            font-size: 14px; 
            color: #334155; 
            line-height: 1.75; 
            margin-bottom: 24px; 
            font-family: inherit; 
        }
        .lesson-notes-body h2 {
            font-size: 17px;
            font-weight: 800;
            color: #0f172a;
            margin: 24px 0 10px 0;
            padding-bottom: 6px;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .lesson-notes-body h3 {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
            margin: 20px 0 8px 0;
        }
        .lesson-notes-body h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            margin: 14px 0 6px 0;
        }
        .lesson-notes-body p {
            margin-bottom: 12px;
            font-size: 14px;
            color: #334155;
            line-height: 1.75;
        }
        .lesson-notes-body ul, .lesson-notes-body ol {
            margin: 8px 0 16px 20px;
            padding-left: 6px;
        }
        .lesson-notes-body li {
            margin-bottom: 6px;
            line-height: 1.7;
            color: #334155;
        }
        .lesson-notes-body strong {
            color: #0f172a;
            font-weight: 700;
        }
        .lesson-notes-body code {
            background: #f1f5f9;
            color: #4f46e5;
            padding: 2px 6px;
            border-radius: 4px;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12px;
            border: 1px solid #e2e8f0;
        }
        
        .code-example-box { 
            background: #0f172a; 
            border-radius: 12px; 
            overflow: hidden; 
            margin: 20px 0 24px; 
            border: 1px solid #1e293b; 
        }
        .code-box-header { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding: 10px 16px; 
            background: #1e293b; 
            color: #94a3b8; 
            font-size: 12px; 
            font-weight: 700; 
            font-family: 'JetBrains Mono', monospace; 
        }
        .btn-copy-code { 
            background: transparent; 
            border: 1px solid #475569; 
            color: #cbd5e1; 
            border-radius: 6px; 
            padding: 3px 8px; 
            font-size: 11px; 
            cursor: pointer; 
            font-family: inherit; 
            transition: all 0.15s;
        }
        .btn-copy-code:hover { background: #334155; color: #ffffff; }
        .code-snippet-pre { 
            padding: 18px; 
            color: #38bdf8; 
            font-family: 'JetBrains Mono', monospace; 
            font-size: 12.5px; 
            line-height: 1.6; 
            overflow-x: auto; 
            white-space: pre; 
            margin: 0; 
        }

        .takeaways-alert { 
            background: #eff6ff; 
            border: 1.5px solid #bfdbfe; 
            border-radius: 12px; 
            padding: 16px 20px; 
            margin-bottom: 24px; 
            font-size: 13px; 
            color: #1e40af; 
            line-height: 1.6; 
        }
        .takeaways-alert strong { display: block; font-weight: 800; margin-bottom: 4px; color: #1e3a8a; }

        .study-bottom-action-bar { 
            display: flex; 
            align-items: center; 
            justify-content: space-between; 
            padding-top: 20px; 
            border-top: 1px solid #f1f5f9; 
            gap: 12px; 
            flex-wrap: wrap; 
        }
        .btn-mark-completed { 
            background: #10b981; 
            color: #ffffff; 
            border: none; 
            border-radius: 10px; 
            padding: 10px 20px; 
            font-size: 13px; 
            font-weight: 800; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            transition: background 0.15s; 
        }
        .btn-mark-completed:hover { background: #059669; }
        .btn-next-mcq-tab { 
            background: #4f46e5; 
            color: #ffffff; 
            border: none; 
            border-radius: 10px; 
            padding: 10px 20px; 
            font-size: 13px; 
            font-weight: 800; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            gap: 8px; 
            transition: background 0.15s;
        }
        .btn-next-mcq-tab:hover { background: #4338ca; }

        /* TAB 2: MCQ PRACTICE UI */
        .mcq-practice-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 28px 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
        .mcq-header-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px; }
        .mcq-header-row h2 { font-size: 17px; font-weight: 800; color: #0f172a; margin: 0; }
        
        .mcq-question-block { background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 18px 20px; margin-bottom: 16px; }
        .mcq-q-title { font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 12px; line-height: 1.4; }
        .mcq-options-list { display: flex; flex-direction: column; gap: 8px; }
        .mcq-opt-label {
            display: flex;
            align-items: center;
            gap: 10px;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            padding: 9px 14px;
            font-size: 12.5px;
            font-weight: 600;
            color: #334155;
            cursor: pointer;
            transition: all 0.15s;
        }
        .mcq-opt-label:hover { border-color: #6366f1; background: #faf5ff; }
        .mcq-opt-label.selected { border-color: #4f46e5; background: #f5f3ff; color: #4f46e5; font-weight: 700; }
        .mcq-opt-label.correct { border-color: #10b981; background: #ecfdf5; color: #065f46; font-weight: 700; }
        .mcq-opt-label.incorrect { border-color: #ef4444; background: #fef2f2; color: #991b1b; }
        .mcq-expl-box { margin-top: 10px; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 8px; padding: 10px 12px; font-size: 12px; color: #475569; line-height: 1.5; }

        .mcq-scorecard-banner {
            background: #f0fdf4;
            border: 1.5px solid #86efac;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .scorecard-left strong { font-size: 16px; font-weight: 800; color: #166534; display: block; }
        .scorecard-left span { font-size: 12px; color: #15803d; }

        .btn-retry-mcq { background: #ffffff; border: 1.5px solid #4f46e5; color: #4f46e5; border-radius: 8px; padding: 8px 16px; font-size: 12.5px; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
        .btn-retry-mcq:hover { background: #4f46e5; color: #ffffff; }

        /* TAB 3: CODING PRACTICE UI (SPLIT VIEW) */
        .coding-split-container { display: grid; grid-template-columns: 420px 1fr; gap: 16px; height: calc(100vh - 120px); }
        .code-problem-panel {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 22px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
        }
        .prob-title-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .prob-title-row h2 { font-size: 16px; font-weight: 800; color: #0f172a; margin: 0; }
        .diff-badge { font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px; background: #ecfdf5; color: #059669; }
        
        .prob-desc-text { font-size: 13px; color: #334155; line-height: 1.6; margin-bottom: 16px; }
        .prob-specs-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px; margin-bottom: 14px; font-size: 12px; }
        .prob-specs-box strong { color: #0f172a; display: block; margin-bottom: 4px; }
        .prob-specs-box pre { font-family: 'JetBrains Mono', monospace; font-size: 11.5px; background: #ffffff; color: #0f172a; padding: 8px; border-radius: 6px; border: 1px solid #e2e8f0; margin-top: 4px; margin-bottom: 0; }

        /* EDITOR PANEL */
        .code-editor-panel {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }
        .editor-top-toolbar { height: 46px; min-height: 46px; background: #1e293b; display: flex; align-items: center; justify-content: space-between; padding: 0 16px; }
        .editor-lang-select { background: #0f172a; color: #f8fafc; border: 1px solid #334155; border-radius: 6px; padding: 4px 10px; font-size: 12px; font-weight: 700; outline: none; }
        
        .editor-textarea-wrap { flex: 1; display: flex; background: #0f172a; overflow: hidden; position: relative; min-height: 200px; }
        .code-input-area { width: 100%; height: 100%; border: none; outline: none; resize: none; background: #0f172a; color: #f8fafc; font-family: 'JetBrains Mono', monospace; font-size: 13px; line-height: 1.6; padding: 16px; }

        .editor-bottom-bar { padding: 10px 16px; background: #ffffff; border-top: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
        .btn-run-code { background: #f1f5f9; border: 1.5px solid #cbd5e1; color: #0f172a; border-radius: 8px; padding: 8px 16px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-run-code:hover { background: #e2e8f0; }
        .btn-submit-code { background: #10b981; border: none; color: #ffffff; border-radius: 8px; padding: 8px 18px; font-size: 12px; font-weight: 800; cursor: pointer; display: flex; align-items: center; gap: 6px; }
        .btn-submit-code:hover { background: #059669; }

        .test-cases-output-panel { padding: 14px 16px; background: #ffffff; border-top: 1.5px solid #e2e8f0; max-height: 220px; overflow-y: auto; }
        .tc-mini-grid { display: flex; flex-direction: column; gap: 8px; }
        .tc-row-card { display: flex; align-items: center; justify-content: space-between; padding: 8px 12px; border-radius: 8px; font-size: 11.5px; }
        .tc-row-pass { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
        .tc-row-fail { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }

        /* PRINCIPAL MODAL STYLES */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 99999;
            padding: 20px;
        }
        .modal-overlay.active { display: flex; }
        .modal-card {
            background: #ffffff;
            border-radius: 18px;
            width: 100%;
            max-width: 750px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            border: 1px solid #e2e8f0;
        }
        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 18px 24px;
            border-bottom: 1.5px solid #e2e8f0;
            background: #ffffff;
        }
        .modal-body {
            flex: 1;
            overflow-y: auto;
            padding: 24px;
            background: #f8fafc;
            color: #0f172a;
        }
        .modal-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px 24px;
            border-top: 1.5px solid #e2e8f0;
            background: #ffffff;
        }

        /* HIDDEN LANDSCAPE PDF TEMPLATE (ONLY LESSON NOTES, NO MCQS, NO CODING) */
        #printable-lesson-export {
            display: none;
        }
    </style>
</head>
<body>

    <div class="study-viewport">
        <!-- TOP NAVIGATION BAR -->
        <header class="study-top-nav">
            <div class="nav-left-group">
                <a href="{{ route('courses.show', $course->course_id) }}" class="btn-nav-back" title="Return to Course Overview">
                    <span>←</span>
                    <span>Back to Course</span>
                </a>
                <button class="btn-toggle-curriculum active-drawer" id="btn-curriculum-toggle" onclick="toggleTopicDrawer()" title="Show/Hide Course Curriculum Sidebar">
                    <span>☰</span>
                    <span>Curriculum</span>
                </button>
                <div class="nav-topic-title-group">
                    <h2>{{ $lesson->lesson_num }}: {{ $lesson->lesson_title }}</h2>
                    <span>Module {{ $lesson->module_num }}: {{ $lesson->module_title }}</span>
                </div>
            </div>

            <!-- 3 MODE SWITCHER TABS IN HEADER -->
            <div class="mode-tabs-center">
                <button class="mode-tab-btn active" id="tab-btn-study" onclick="switchStudyMode('study')">
                    <span>📖</span> <span>1. Study Lesson</span>
                </button>
                <button class="mode-tab-btn" id="tab-btn-mcq" onclick="switchStudyMode('mcq')">
                    <span>📝</span> <span>2. MCQ Practice ({{ $mcqs->count() }})</span>
                </button>
                <button class="mode-tab-btn" id="tab-btn-code" onclick="switchStudyMode('code')">
                    <span>💻</span> <span>3. Coding Practice</span>
                </button>
            </div>

            <div class="nav-right-actions">
                <!-- INSTANT LANDSCAPE PDF DOWNLOAD (LESSON ONLY, NO NAVIGATING AWAY) -->
                <button type="button" id="btn-download-pdf-top" onclick="downloadLessonPDF()" class="btn-download-pdf-top" title="Download Lesson Notes in Landscape PDF">
                    <span>📄 Download PDF</span>
                </button>

                <!-- Status Badge -->
                <span class="status-badge-chip {{ $lesson->is_completed ? 'badge-done-green' : 'badge-inprog-blue' }}" id="lbl-lesson-status">
                    {{ $lesson->is_completed ? 'Completed ✓' : 'In Progress ⌛' }}
                </span>

                <!-- Principal / Admin Only: Manage & AI Tools -->
                @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                    <button type="button" id="btnAiAutoFillLesson" onclick="autoGenerateCurrentLesson()" class="btn-ai-autofill-top" title="Auto-generate complete notes, MCQs & coding challenge with Gemini">
                        <span id="aiAutoFillBtnText">✨ AI Auto-Fill</span>
                    </button>
                    <button type="button" onclick="openPrincipalEditorModal()" class="btn-edit-content-top" title="Edit lecture notes directly">
                        <span>⚙️ Edit Content</span>
                    </button>
                @endif
            </div>
        </header>

        <!-- BODY LAYOUT (DRAWER + MAIN TAB CONTENT) -->
        <div class="study-body-wrap">
            
            <!-- TOPIC DRAWER (SIDEBAR) -->
            <aside class="topic-drawer" id="topic-drawer">
                @php
                    $completedCnt = $allLessons->where('is_completed', 1)->count();
                @endphp
                <div class="drawer-header">
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 14px;">📚</span>
                        <strong>Course Curriculum</strong>
                    </div>
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 11px; color: #64748b;">{{ $completedCnt }}/{{ $allLessons->count() }} Done</span>
                        <button class="btn-drawer-collapse" onclick="toggleTopicDrawer()" title="Collapse Sidebar">
                            ❮
                        </button>
                    </div>
                </div>
                <div class="drawer-module-group">
                    @php
                        $grouped = $allLessons->groupBy('module_title');
                    @endphp
                    @foreach($grouped as $modTitle => $modLessons)
                        <div class="drawer-mod-title">Module {{ $modLessons->first()->module_num }}: {{ $modTitle }}</div>
                        @foreach($modLessons as $l)
                            <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $l->lesson_num]) }}" 
                               class="drawer-lesson-item {{ $l->lesson_num === $lesson->lesson_num ? 'active' : '' }}">
                                <span>{{ $l->lesson_num }} {{ $l->lesson_title }}</span>
                                <span class="drawer-check-icon {{ $l->is_completed ? 'drawer-check-done' : '' }}">
                                    {{ $l->is_completed ? '✓' : '○' }}
                                </span>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </aside>

            <!-- MAIN CONTENT AREA -->
            <main class="study-main-pane">

                <!-- 1. LESSON STUDY TAB -->
                <div id="pane-study-lesson" style="display: block;">
                    <div class="lesson-notes-card" id="lesson-study-card">
                        <div class="lesson-hero-header">
                            <div>
                                <h1>{{ $lesson->lesson_title }}</h1>
                                <p>{{ $content->concept_summary ?? 'Foundational concepts and principles.' }}</p>
                            </div>
                            <div style="display: flex; align-items: center; gap: 8px;">
                                <button type="button" onclick="downloadLessonPDF()" class="btn-download-pdf-top" style="padding: 6px 14px; font-size: 12px;">
                                    <span>📥 Download PDF (Landscape)</span>
                                </button>
                                <span class="status-badge-chip badge-inprog-blue">
                                    <span>Study &amp; Re-watch Mode 🔄</span>
                                </span>
                            </div>
                        </div>

                        <!-- Theory & Content -->
                        <div class="lesson-notes-body">{!! $content->detailed_notes ?? '<p>No detailed notes published yet for this topic. Use AI Lesson Studio or Edit Content to write lecture notes.</p>' !!}</div>

                        <!-- Code Example Block -->
                        @if(!empty(trim($content->code_example ?? '')))
                        <div class="code-example-box">
                            <div class="code-box-header">
                                <span>Code Walkthrough ({{ $course->language ?? 'Programming' }})</span>
                                <button type="button" class="btn-copy-code" onclick="copyCodeSnippet()">Copy Code</button>
                            </div>
                            <pre class="code-snippet-pre" id="lbl-code-snippet">{{ $content->code_example }}</pre>
                        </div>
                        @endif

                        <!-- Key Takeaways -->
                        @if(!empty(trim($content->key_takeaways ?? '')))
                        <div class="takeaways-alert">
                            <strong>💡 Key Takeaways &amp; Exam Tips:</strong>
                            <div style="margin-top: 4px; line-height: 1.6;">{!! $content->key_takeaways !!}</div>
                        </div>
                        @endif

                        <!-- Action Bar -->
                        <div class="study-bottom-action-bar">
                            <button type="button" class="btn-mark-completed" id="btn-toggle-complete" onclick="markLessonComplete()">
                                <span>✓</span>
                                <span id="txtMarkComplete">{{ $lesson->is_completed ? 'Mark as In-Progress' : 'Mark Completed & Update Progress' }}</span>
                            </button>
                            <button type="button" class="btn-next-mcq-tab" onclick="switchStudyMode('mcq')">
                                <span>Proceed to 2. MCQ Practice ({{ $mcqs->count() }})</span>
                                <span>→</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- 2. MCQ PRACTICE TAB -->
                <div id="pane-mcq-practice" style="display: none;">
                    <div class="mcq-practice-card">
                        <div class="mcq-header-row">
                            <div>
                                <h2>📝 Topic Practice Quizzes &amp; MCQs</h2>
                                <p style="font-size: 12px; color: #64748b; margin-top: 2px;">Test your conceptual understanding with instant feedback.</p>
                            </div>
                            <button type="button" class="btn-retry-mcq" onclick="resetMcqQuiz()">
                                <span>🔄</span> <span>Retry Practice</span>
                            </button>
                        </div>

                        <!-- Scorecard Banner -->
                        <div class="mcq-scorecard-banner" id="banner-mcq-score" style="display: none;">
                            <div class="scorecard-left">
                                <strong id="lbl-mcq-score-title">Practice Score: 100%</strong>
                                <span>Great job! Review any explanations below.</span>
                            </div>
                            <button type="button" class="btn-next-mcq-tab" onclick="switchStudyMode('code')">
                                <span>Proceed to 3. Coding Practice →</span>
                            </button>
                        </div>

                        <!-- Questions List -->
                        <div id="container-mcq-questions">
                            @forelse($mcqs as $index => $q)
                                @php
                                    $opts = is_array($q->options_json) ? $q->options_json : json_decode($q->options_json, true) ?? [];
                                @endphp
                                <div class="mcq-question-block" id="mcq-block-{{ $q->question_number }}">
                                    <div class="mcq-q-title">Q{{ $q->question_number }}. {{ $q->question_text }}</div>
                                    <div class="mcq-options-list">
                                        @foreach($opts as $opt)
                                            <label class="mcq-opt-label" id="opt-label-{{ $q->question_number }}-{{ $opt['key'] }}" onclick="selectMcqOption({{ $q->question_number }}, '{{ $opt['key'] }}')">
                                                <input type="radio" name="mcq_q_{{ $q->question_number }}" value="{{ $opt['key'] }}" style="accent-color: #4f46e5;">
                                                <span><strong>{{ $opt['key'] }}.</strong> {{ $opt['text'] }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="mcq-expl-box" id="mcq-expl-{{ $q->question_number }}" style="display: none;" data-correct="{{ $q->correct_key }}" data-explanation="{{ $q->explanation }}"></div>
                                </div>
                            @empty
                                <div style="text-align: center; padding: 36px; color: #64748b;">
                                    No MCQs added for this lesson yet.
                                </div>
                            @endforelse
                        </div>

                        @if($mcqs->count() > 0)
                        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end;">
                            <button type="button" class="btn-next-mcq-tab" onclick="submitTopicMcqs()">
                                <span>Submit Practice Answers</span>
                                <span>✓</span>
                            </button>
                        </div>
                        @endif
                    </div>
                </div>

                <!-- 3. CODING PRACTICE TAB (SPLIT VIEW) -->
                <div id="pane-coding-practice" style="display: none;">
                    @if($coding)
                    <div class="coding-split-container">
                        <!-- LEFT PANEL: PROBLEM SPEC -->
                        <div class="code-problem-panel">
                            <div class="prob-title-row">
                                <h2>{{ $coding->title }}</h2>
                                <span class="diff-badge">{{ $coding->difficulty ?? 'Medium' }}</span>
                            </div>
                            <p class="prob-desc-text">{{ $coding->problem_statement }}</p>

                            @if(!empty($coding->constraints_text))
                            <div class="prob-specs-box">
                                <strong>Constraints:</strong>
                                <span>{{ $coding->constraints_text }}</span>
                            </div>
                            @endif

                            <div class="prob-specs-box">
                                <strong>Sample Input:</strong>
                                <pre>{{ $coding->sample_input ?: 'N/A' }}</pre>
                            </div>

                            <div class="prob-specs-box">
                                <strong>Sample Output:</strong>
                                <pre>{{ $coding->sample_output ?: 'N/A' }}</pre>
                            </div>

                            <div style="margin-top: auto; padding-top: 14px; border-top: 1px solid #f1f5f9; display: flex; justify-content: space-between;">
                                <button type="button" class="btn-retry-mcq" onclick="resetCodingStarterCode()" style="font-size: 11.5px; padding: 5px 12px;">
                                    <span>🔄 Reset Template</span>
                                </button>
                            </div>
                        </div>

                        <!-- RIGHT PANEL: CODE EDITOR & TEST RUNNER -->
                        <div class="code-editor-panel">
                            <div class="editor-top-toolbar">
                                <select class="editor-lang-select" id="sel-code-lang" onchange="handleLangChange()">
                                    <option value="cpp">C++ (GCC 15+ / C++20)</option>
                                    <option value="py">Python 3</option>
                                    <option value="java">Java 17 (OpenJDK)</option>
                                    <option value="js">JavaScript (Node.js 24)</option>
                                    <option value="c">C (GCC 15+ / C17)</option>
                                </select>
                                <span style="font-size: 11.5px; color: #a5b4fc; font-weight: 700;">⚡ Live Sandbox Compiler</span>
                            </div>

                            <div class="editor-textarea-wrap">
                                <textarea class="code-input-area" id="txt-practice-code" spellcheck="false">{{ $coding->starter_code_cpp ?: '// Write your code solution here\n' }}</textarea>
                            </div>

                            <!-- Test Runner Output Panel -->
                            <div class="test-cases-output-panel" id="panel-tc-results" style="display: none;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <strong style="font-size: 12px; color: #0f172a;" id="lbl-tc-verdict">Execution Results</strong>
                                    <span style="font-size: 11px; font-weight: 800; color: #10b981;" id="lbl-tc-passed-badge">All Passed ✓</span>
                                </div>
                                <div class="tc-mini-grid" id="container-tc-rows">
                                    <!-- Populated dynamically -->
                                </div>
                            </div>

                            <div class="editor-bottom-bar">
                                <button type="button" class="btn-run-code" onclick="runPracticeCodeSandbox(false)">
                                    <span>▶ Run Test Cases</span>
                                </button>
                                <button type="button" class="btn-submit-code" onclick="runPracticeCodeSandbox(true)">
                                    <span>✓ Submit Solution</span>
                                </button>
                            </div>
                        </div>
                    </div>
                    @else
                    <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 16px; text-align: center; padding: 48px; color: #64748b;">
                        <div style="font-size: 38px; margin-bottom: 10px;">💻</div>
                        <h3 style="color: #0f172a; font-size: 16px; margin-bottom: 6px; font-weight: 800;">No Coding Challenge Configured</h3>
                        <p style="font-size: 12.5px;">This lesson does not have a linked coding problem yet.</p>
                    </div>
                    @endif
                </div>

            </main>
        </div>
    </div>

    <!-- CLEAN LANDSCAPE PDF CONTAINER (ONLY LESSON SESSION NOTES, NO MCQS, NO CODING) -->
    <div id="printable-lesson-export" style="background: #ffffff; color: #0f172a; font-family: 'Plus Jakarta Sans', sans-serif; padding: 25px 35px; width: 1050px;">
        <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #e2e8f0; padding-bottom: 14px; margin-bottom: 18px;">
            <div>
                <div style="font-size: 11px; font-weight: 800; color: #4f46e5; text-transform: uppercase; letter-spacing: 0.5px;">{{ $course->title }} &bull; Module {{ $lesson->module_num }}: {{ $lesson->module_title }}</div>
                <h1 style="font-size: 22px; font-weight: 800; color: #0f172a; margin-top: 4px;">{{ $lesson->lesson_num }}: {{ $lesson->lesson_title }}</h1>
            </div>
            <div style="text-align: right;">
                <span style="font-size: 11px; font-weight: 700; background: #f1f5f9; color: #334155; padding: 5px 12px; border-radius: 6px;">ExamFort Academic Study Note</span>
            </div>
        </div>

        <div style="background: #f8fafc; border-left: 4px solid #4f46e5; padding: 12px 18px; border-radius: 6px; margin-bottom: 18px;">
            <strong style="font-size: 12px; color: #4f46e5; text-transform: uppercase;">Core Concept Summary</strong>
            <p style="font-size: 13px; color: #334155; margin-top: 4px; line-height: 1.5;">{{ $content->concept_summary ?? 'Foundational concepts and principles.' }}</p>
        </div>

        <div style="margin-bottom: 20px;">
            <h3 style="font-size: 14px; font-weight: 800; color: #0f172a; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 4px;">📖 Lecture &amp; Theory Notes</h3>
            <div class="lesson-notes-body" style="font-size: 13px; color: #334155; line-height: 1.7;">{!! $content->detailed_notes ?? '<p>No lecture notes recorded.</p>' !!}</div>
        </div>

        @if(!empty(trim($content->code_example ?? '')))
        <div style="background: #0f172a; border-radius: 10px; overflow: hidden; margin-bottom: 20px;">
            <div style="padding: 8px 16px; background: #1e293b; color: #94a3b8; font-size: 11px; font-weight: 700; font-family: monospace;">
                Code Walkthrough ({{ $course->language ?? 'Programming' }})
            </div>
            <pre style="padding: 16px; color: #38bdf8; font-family: 'JetBrains Mono', monospace; font-size: 12px; line-height: 1.5; margin: 0; white-space: pre;">{{ $content->code_example }}</pre>
        </div>
        @endif

        @if(!empty(trim($content->key_takeaways ?? '')))
        <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; font-size: 12.5px; color: #1e40af;">
            <strong style="color: #1e3a8a; display: block; margin-bottom: 4px;">💡 Key Takeaways &amp; Exam Tips:</strong>
            <div style="margin-top: 4px; line-height: 1.6;">{!! $content->key_takeaways !!}</div>
        </div>
        @endif

        <div style="border-top: 1px solid #e2e8f0; padding-top: 10px; display: flex; justify-content: space-between; font-size: 10px; color: #94a3b8;">
            <span>Generated via ExamFort Smart Learning Portal</span>
            <span>Duration: {{ $lesson->duration_text ?? '30 min' }} &bull; Status: {{ $lesson->is_completed ? 'Completed' : 'In Progress' }}</span>
        </div>
    </div>

    <!-- PRINCIPAL CONTENT EDITOR MODAL -->
    @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
    <div class="modal-overlay" id="principalEditorModal" onclick="if(event.target===this) closePrincipalEditorModal()">
        <div class="modal-card">
            <div class="modal-header">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="font-size: 20px;">⚙️</span>
                    <strong style="font-size: 16px; color: #0f172a;">Principal Management: Edit Lesson Content</strong>
                </div>
                <button type="button" onclick="closePrincipalEditorModal()" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">✕</button>
            </div>
            <form action="{{ route('courses.lessons.content.update', ['course_id' => $course->course_id, 'lesson_num' => $lesson->lesson_num]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Core Concept Summary *</label>
                        <textarea name="concept_summary" style="width: 100%; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 12.5px; color: #0f172a; min-height: 70px;" required>{{ old('concept_summary', $content->concept_summary ?? '') }}</textarea>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Detailed Theory Notes *</label>
                        <textarea name="detailed_notes" style="width: 100%; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #0f172a; min-height: 180px;" required>{{ old('detailed_notes', $content->detailed_notes ?? '') }}</textarea>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Code Demonstration</label>
                        <textarea name="code_example" style="width: 100%; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-family: 'JetBrains Mono', monospace; font-size: 12px; color: #0f172a; min-height: 100px;">{{ old('code_example', $content->code_example ?? '') }}</textarea>
                    </div>

                    <div style="margin-bottom: 14px;">
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Key Takeaways</label>
                        <input type="text" name="key_takeaways" style="width: 100%; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 8px 12px; font-size: 12.5px; color: #0f172a;" value="{{ old('key_takeaways', $content->key_takeaways ?? '') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" onclick="closePrincipalEditorModal()" class="btn-retry-mcq" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">Cancel</button>
                    <button type="submit" class="btn-next-mcq-tab">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
    @endif

<script>
    window.activeTab = "{{ request('tab', 'study') }}";
    window.selectedMcqAnswers = {};

    window.starterCodes = {
        cpp: {!! json_encode($coding?->starter_code_cpp ?: "#include <iostream>\n\nint main() {\n    // Write your C++ solution\n    return 0;\n}\n") !!},
        c: {!! json_encode("#include <stdio.h>\n\nint main() {\n    // Write your C solution\n    return 0;\n}\n") !!},
        py: {!! json_encode($coding?->starter_code_py ?: "import sys\n\ndef solve():\n    # Write your Python solution\n    pass\n\nif __name__ == '__main__':\n    solve()\n") !!},
        java: {!! json_encode($coding?->starter_code_java ?: "import java.util.Scanner;\n\npublic class Main {\n    public static void main(String[] args) {\n        // Write your Java solution\n    }\n}\n") !!},
        js: {!! json_encode($coding?->starter_code_js ?: "const fs = require('fs');\n// Write your JavaScript solution\n") !!}
    };

    // Tab switcher
    window.switchStudyMode = function(mode) {
        window.activeTab = mode;
        document.querySelectorAll('.mode-tab-btn').forEach(b => b.classList.remove('active'));
        const activeBtn = document.getElementById('tab-btn-' + mode);
        if (activeBtn) activeBtn.classList.add('active');

        const pStudy = document.getElementById('pane-study-lesson');
        const pMcq = document.getElementById('pane-mcq-practice');
        const pCode = document.getElementById('pane-coding-practice');

        if (pStudy) pStudy.style.display = (mode === 'study') ? 'block' : 'none';
        if (pMcq) pMcq.style.display = (mode === 'mcq') ? 'block' : 'none';
        if (pCode) pCode.style.display = (mode === 'code') ? 'block' : 'none';
    };

    window.toggleTopicDrawer = function() {
        const drawer = document.getElementById('topic-drawer');
        if (!drawer) return;
        drawer.classList.toggle('collapsed');
        const isCollapsed = drawer.classList.contains('collapsed');
        const toggleBtn = document.getElementById('btn-curriculum-toggle');
        if (toggleBtn) toggleBtn.classList.toggle('active-drawer', !isCollapsed);
    };

    window.openPrincipalEditorModal = function() {
        const modal = document.getElementById('principalEditorModal');
        if (modal) modal.classList.add('active');
    };

    window.closePrincipalEditorModal = function() {
        const modal = document.getElementById('principalEditorModal');
        if (modal) modal.classList.remove('active');
    };

    window.copyCodeSnippet = function() {
        const text = document.getElementById('lbl-code-snippet')?.textContent || '';
        navigator.clipboard.writeText(text).then(() => {
            alert('Code snippet copied to clipboard!');
        });
    };

    // DIRECT IN-PAGE LANDSCAPE PDF EXPORT (LESSON ONLY, NO MCQS, NO CODING, NO NAVIGATION)
    window.downloadLessonPDF = function() {
        const btnTop = document.getElementById('btn-download-pdf-top');
        const origContent = btnTop ? btnTop.innerHTML : '';
        if (btnTop) {
            btnTop.innerHTML = '<span>⏳ Downloading PDF...</span>';
            btnTop.disabled = true;
        }

        const element = document.getElementById('printable-lesson-export');
        if (!element) {
            alert('Lesson export element not found.');
            if (btnTop) {
                btnTop.innerHTML = origContent;
                btnTop.disabled = false;
            }
            return;
        }

        // Temporarily display element for snapshot
        element.style.display = 'block';

        const opt = {
            margin: [10, 10, 10, 10],
            filename: '{{ $lesson->lesson_num }}_{{ Str::slug($lesson->lesson_title) }}_LessonNotes.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                logging: false,
                letterRendering: true,
                scrollY: 0
            },
            jsPDF: { 
                unit: 'mm', 
                format: 'a4', 
                orientation: 'landscape' 
            }
        };

        html2pdf().set(opt).from(element).save().then(() => {
            element.style.display = 'none';
            if (btnTop) {
                btnTop.innerHTML = '<span>✓ Downloaded</span>';
                setTimeout(() => {
                    btnTop.innerHTML = origContent;
                    btnTop.disabled = false;
                }, 2000);
            }
        }).catch(err => {
            element.style.display = 'none';
            console.error('PDF export error:', err);
            alert('Could not export PDF: ' + err.message);
            if (btnTop) {
                btnTop.innerHTML = origContent;
                btnTop.disabled = false;
            }
        });
    };

    window.markLessonComplete = async function() {
        try {
            const res = await fetch("{{ route('courses.lessons.toggleComplete', ['course_id' => $course->course_id, 'lesson_num' => $lesson->lesson_num]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            const data = await res.json();
            if (data.success) {
                const statusEl = document.getElementById('lbl-lesson-status');
                const btnTxt = document.getElementById('txtMarkComplete');
                if (data.is_completed) {
                    if (statusEl) {
                        statusEl.textContent = 'Completed ✓';
                        statusEl.className = 'status-badge-chip badge-done-green';
                    }
                    if (btnTxt) btnTxt.textContent = 'Mark as In-Progress';
                } else {
                    if (statusEl) {
                        statusEl.textContent = 'In Progress ⌛';
                        statusEl.className = 'status-badge-chip badge-inprog-blue';
                    }
                    if (btnTxt) btnTxt.textContent = 'Mark Completed & Update Progress';
                }
            }
        } catch (e) {
            console.error(e);
        }
    };

    // MCQ Handling
    window.selectMcqOption = function(qNum, key) {
        window.selectedMcqAnswers[qNum] = key;
        document.querySelectorAll('[id^="opt-label-' + qNum + '-"]').forEach(l => l.classList.remove('selected'));
        const selectedLabel = document.getElementById('opt-label-' + qNum + '-' + key);
        if (selectedLabel) selectedLabel.classList.add('selected');
    };

    window.submitTopicMcqs = function() {
        let correctCount = 0;
        const total = {{ $mcqs->count() }};

        document.querySelectorAll('[id^="mcq-expl-"]').forEach(box => {
            const correctKey = box.getAttribute('data-correct');
            const explanation = box.getAttribute('data-explanation');
            const qNum = box.id.replace('mcq-expl-', '');
            const studentKey = window.selectedMcqAnswers[qNum];

            box.style.display = 'block';
            if (studentKey === correctKey) {
                correctCount++;
                box.innerHTML = '<strong style="color: #15803d;">✓ Correct Answer (' + correctKey + ')!</strong><br>' + (explanation || '');
                box.style.borderColor = '#86efac';
                box.style.background = '#f0fdf4';
            } else {
                box.innerHTML = '<strong style="color: #b91c1c;">✗ Incorrect. Correct answer is (' + correctKey + ')</strong><br>' + (explanation || '');
                box.style.borderColor = '#fca5a5';
                box.style.background = '#fef2f2';
            }
        });

        const pct = total > 0 ? Math.round((correctCount / total) * 100) : 0;
        const banner = document.getElementById('banner-mcq-score');
        if (banner) banner.style.display = 'flex';
        const titleEl = document.getElementById('lbl-mcq-score-title');
        if (titleEl) titleEl.textContent = 'Practice Score: ' + correctCount + ' / ' + total + ' (' + pct + '%)';
    };

    window.resetMcqQuiz = function() {
        const banner = document.getElementById('banner-mcq-score');
        if (banner) banner.style.display = 'none';
        document.querySelectorAll('[id^="mcq-expl-"]').forEach(b => b.style.display = 'none');
        document.querySelectorAll('.mcq-opt-label').forEach(l => l.classList.remove('selected', 'correct', 'incorrect'));
        document.querySelectorAll('input[type="radio"]').forEach(r => r.checked = false);
        window.selectedMcqAnswers = {};
    };

    // Coding Practice Handling
    window.handleLangChange = function() {
        const lang = document.getElementById('sel-code-lang')?.value || 'cpp';
        const txtArea = document.getElementById('txt-practice-code');
        if (txtArea) txtArea.value = window.starterCodes[lang] || '// Write solution';
        const panel = document.getElementById('panel-tc-results');
        if (panel) panel.style.display = 'none';
    };

    window.resetCodingStarterCode = function() {
        window.handleLangChange();
    };

    // LIVE SANDBOX COMPILER TEST RUNNER (GCC 15+, PYTHON 3, NODE.JS)
    window.runPracticeCodeSandbox = async function(isSubmit) {
        const panel = document.getElementById('panel-tc-results');
        const container = document.getElementById('container-tc-rows');
        const verdict = document.getElementById('lbl-tc-verdict');
        const badge = document.getElementById('lbl-tc-passed-badge');

        const lang = document.getElementById('sel-code-lang')?.value || 'cpp';
        const code = document.getElementById('txt-practice-code')?.value || '';

        if (!code.trim()) {
            alert('Please write code in the editor before running test cases.');
            return;
        }

        if (panel) panel.style.display = 'block';
        if (verdict) {
            verdict.textContent = 'Compiling & Running (' + lang.toUpperCase() + ')...';
            verdict.style.color = '#4f46e5';
        }
        if (badge) {
            badge.textContent = 'Executing...';
            badge.style.color = '#64748b';
        }
        if (container) {
            container.innerHTML = '<div style="padding: 12px; text-align: center; color: #64748b; font-size: 12px;">⚙️ Compiling code in live sandbox engine...</div>';
        }

        try {
            const res = await fetch("{{ route('courses.lessons.runSandbox', ['course_id' => $course->course_id, 'lesson_num' => $lesson->lesson_num]) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ language: lang, code: code })
            });

            const data = await res.json();

            // 1. Compilation or Syntax Error
            if (data.isCompileError) {
                if (verdict) {
                    verdict.textContent = 'Compilation / Syntax Error ✕';
                    verdict.style.color = '#ef4444';
                }
                if (badge) {
                    badge.textContent = 'Build Failed';
                    badge.style.color = '#ef4444';
                }
                if (container) {
                    container.innerHTML = `
                        <div style="background: #fef2f2; border: 1.5px solid #fca5a5; border-radius: 8px; padding: 12px; font-size: 11.5px; color: #991b1b; font-family: monospace; white-space: pre-wrap;">
                            <strong>[${data.errorType || 'Error'}]</strong>\n${data.errorMessage || 'Compilation failed'}
                        </div>
                    `;
                }
                return;
            }

            // 2. Test Case Results
            const results = data.results || [];
            const passedCount = data.passedCount || 0;
            const totalCount = data.totalCount || results.length;
            const allPassed = data.allPassed || (passedCount === totalCount && totalCount > 0);

            if (verdict) {
                verdict.textContent = allPassed ? 'All Test Cases Passed Successfully 🚀' : `${passedCount}/${totalCount} Test Cases Passed`;
                verdict.style.color = allPassed ? '#16a34a' : '#ea580c';
            }
            if (badge) {
                badge.textContent = `${passedCount}/${totalCount} Passed`;
                badge.style.color = allPassed ? '#16a34a' : '#ea580c';
            }

            if (container) {
                container.innerHTML = results.map((r, idx) => `
                    <div class="tc-row-card ${r.passed ? 'tc-row-pass' : 'tc-row-fail'}" style="flex-direction: column; align-items: flex-start; gap: 4px; padding: 10px 14px;">
                        <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                            <strong>${r.desc || 'Test Case #' + (idx + 1)}: ${r.passed ? 'Passed ✓' : 'Failed ✕'}</strong>
                            <span style="font-weight: 800; font-size: 11px;">${r.runtime || '0ms'}</span>
                        </div>
                        <div style="font-size: 11.5px; width: 100%; display: flex; flex-direction: column; gap: 3px; margin-top: 4px;">
                            <div><span style="color: #64748b; font-weight: 700;">Input:</span> <code>${r.input || ''}</code></div>
                            <div><span style="color: #64748b; font-weight: 700;">Expected:</span> <code>${r.expected || ''}</code></div>
                            ${!r.passed ? `<div style="color: #b91c1c;"><span style="font-weight: 700;">Your Output:</span> <code style="background: #fee2e2; color: #991b1b; padding: 1px 4px; border-radius: 4px;">${r.actual || ''}</code></div>` : ''}
                        </div>
                    </div>
                `).join('');
            }

        } catch (err) {
            console.error('Compiler run error:', err);
            if (verdict) {
                verdict.textContent = 'Sandbox Connection Error ✕';
                verdict.style.color = '#ef4444';
            }
            if (container) {
                container.innerHTML = '<div style="color: #ef4444; padding: 10px; font-size: 12px;">Could not connect to live sandbox service: ' + err.message + '</div>';
            }
        }
    };

    // AI Auto-Fill Current Lesson
    window.autoGenerateCurrentLesson = async function() {
        if (!confirm('Generate/Regenerate full lesson notes, MCQs, and coding exercise for "{{ addslashes($lesson->lesson_title) }}" using Gemini AI?')) {
            return;
        }

        const btn = document.getElementById('btnAiAutoFillLesson');
        const txt = document.getElementById('aiAutoFillBtnText');
        if (btn) btn.disabled = true;
        if (txt) txt.innerText = '✨ Generating with Gemini...';

        try {
            const response = await fetch("{{ route('courses.lessons.aiGenerate', $course->course_id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    lesson_topic: "{{ addslashes($lesson->lesson_title) }}",
                    module_num: {{ $lesson->module_num }},
                    module_title: "{{ addslashes($lesson->module_title) }}",
                    lesson_num: "{{ addslashes($lesson->lesson_num) }}",
                    difficulty: "{{ addslashes($course->level ?: 'Beginner') }}",
                    mcq_count: 5,
                    include_coding: true
                })
            });

            const data = await response.json();

            if (!data.success) {
                alert('Generation Error: ' + (data.message || 'Failed to generate.'));
                if (btn) btn.disabled = false;
                if (txt) txt.innerText = '✨ AI Auto-Fill';
                return;
            }

            // Save immediately
            const saveRes = await fetch("{{ route('courses.lessons.aiSave', $course->course_id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(data.lesson)
            });

            const saveData = await saveRes.json();
            if (saveData.success) {
                alert('Success: ' + (saveData.message || 'Lesson generated and saved!'));
                window.location.reload();
            } else {
                alert('Error saving: ' + (saveData.message || 'Unknown error'));
                if (btn) btn.disabled = false;
                if (txt) txt.innerText = '✨ AI Auto-Fill';
            }

        } catch (err) {
            alert('Request Failed: ' + err.message);
            if (btn) btn.disabled = false;
            if (txt) txt.innerText = '✨ AI Auto-Fill';
        }
    };

    // Initialize initial tab on load
    document.addEventListener('DOMContentLoaded', function() {
        window.switchStudyMode(window.activeTab);
    });
</script>
</body>
</html>
