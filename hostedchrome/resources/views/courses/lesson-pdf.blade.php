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
        @page {
            size: A4 landscape;
            margin: 10mm;
        }
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            padding: 24px;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* FLOATING PRINT / DOWNLOAD CONTROLS (HIDDEN ON PRINT) */
        .no-print-bar {
            position: fixed;
            top: 16px;
            right: 24px;
            display: flex;
            gap: 10px;
            z-index: 99999;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(8px);
            padding: 8px 14px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            border: 1px solid #cbd5e1;
        }
        .btn-action-print {
            background: #4f46e5;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-action-print:hover { background: #4338ca; }
        .btn-action-close {
            background: #f1f5f9;
            color: #334155;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            padding: 8px 14px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-action-close:hover { background: #e2e8f0; }

        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0 !important; background: #ffffff !important; }
        }

        /* MAIN PDF CARD (EXACT SAME COLORS & LAYOUT) */
        .pdf-document-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 28px 36px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            max-width: 1100px;
            margin: 0 auto 30px auto;
        }

        /* HERO HEADER */
        .lesson-hero-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 1.5px solid #f1f5f9;
            padding-bottom: 18px;
            margin-bottom: 20px;
        }
        .badge-module-pill {
            display: inline-block;
            font-size: 11px;
            font-weight: 800;
            color: #4f46e5;
            background: #ede9fe;
            padding: 4px 10px;
            border-radius: 6px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }
        .lesson-hero-header h1 {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 6px;
        }
        .lesson-hero-header p {
            font-size: 13.5px;
            color: #64748b;
            line-height: 1.5;
        }

        /* NOTES BODY */
        .lesson-notes-body {
            font-size: 13.5px;
            color: #334155;
            line-height: 1.75;
            margin-bottom: 24px;
        }
        .lesson-notes-body h2 {
            font-size: 16.5px;
            font-weight: 800;
            color: #0f172a;
            margin: 20px 0 8px 0;
            padding-bottom: 4px;
            border-bottom: 1.5px solid #f1f5f9;
        }
        .lesson-notes-body h3 {
            font-size: 15px;
            font-weight: 800;
            color: #1e293b;
            margin: 16px 0 6px 0;
        }
        .lesson-notes-body h4 {
            font-size: 13.5px;
            font-weight: 700;
            color: #334155;
            margin: 12px 0 4px 0;
        }
        .lesson-notes-body p {
            margin-bottom: 10px;
            font-size: 13.5px;
            color: #334155;
            line-height: 1.7;
        }
        .lesson-notes-body ul, .lesson-notes-body ol {
            margin: 6px 0 14px 20px;
            padding-left: 6px;
        }
        .lesson-notes-body li {
            margin-bottom: 5px;
            line-height: 1.65;
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

        /* CODE SNIPPET BOX */
        .code-example-box {
            background: #0f172a;
            border-radius: 12px;
            overflow: hidden;
            margin: 20px 0 24px 0;
            border: 1px solid #1e293b;
        }
        .code-box-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 8px 16px;
            background: #1e293b;
            color: #94a3b8;
            font-size: 11.5px;
            font-weight: 700;
            font-family: 'JetBrains Mono', monospace;
        }
        .code-snippet-pre {
            padding: 16px 20px;
            color: #38bdf8;
            font-family: 'JetBrains Mono', monospace;
            font-size: 12.5px;
            line-height: 1.6;
            white-space: pre-wrap;
            margin: 0;
        }

        /* KEY TAKEAWAYS */
        .takeaways-alert {
            background: #eff6ff;
            border: 1.5px solid #bfdbfe;
            border-radius: 12px;
            padding: 16px 20px;
            margin-top: 20px;
            font-size: 13px;
            color: #1e40af;
            line-height: 1.6;
        }
        .takeaways-alert strong {
            display: block;
            font-weight: 800;
            margin-bottom: 4px;
            color: #1e3a8a;
        }

        /* PRACTICE MCQS SECTION */
        .mcq-section-card {
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            padding: 24px 30px;
            max-width: 1100px;
            margin: 0 auto;
            page-break-before: auto;
        }
        .mcq-q-block {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 18px;
            margin-bottom: 14px;
            font-size: 12.5px;
        }
        .mcq-q-title {
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 8px;
        }
        .mcq-opt-line {
            padding: 4px 0;
            color: #334155;
        }
        .mcq-ans-badge {
            display: inline-block;
            margin-top: 6px;
            background: #ecfdf5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 3px 8px;
            border-radius: 6px;
            font-weight: 700;
            font-size: 11.5px;
        }
    </style>
</head>
<body>
    <!-- FLOATING NO-PRINT ACTION BAR -->
    <div class="no-print-bar">
        <button onclick="downloadAsLandscapePdf()" class="btn-action-print">
            <span>📥 Download PDF (Landscape)</span>
        </button>
        <button onclick="window.print()" class="btn-action-print" style="background: #10b981;">
            <span>🖨️ Print Document</span>
        </button>
        <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $lesson->lesson_num]) }}" class="btn-action-close">
            <span>← Back to Lesson</span>
        </a>
    </div>

    <!-- MAIN LESSON PDF CARD -->
    <div class="pdf-document-card" id="pdf-capture-area">
        <div class="lesson-hero-header">
            <div>
                <span class="badge-module-pill">Lesson {{ $lesson->lesson_num }} &bull; Module {{ $lesson->module_num }}: {{ $lesson->module_title }}</span>
                <h1>{{ $lesson->lesson_title }}</h1>
                <p>{{ $content->concept_summary ?? 'Foundational concepts and principles.' }}</p>
            </div>
            <div style="text-align: right;">
                <strong style="font-size: 14px; color: #4f46e5; font-weight: 800; display: block;">ExamFort™ Certified Course</strong>
                <span style="font-size: 12px; color: #64748b;">{{ $course->title }}</span>
            </div>
        </div>

        <!-- Lecture Theory Notes -->
        <div class="lesson-notes-body">{!! $content->detailed_notes ?? '<p>Lesson notes published for this topic.</p>' !!}</div>

        <!-- Code Demonstration -->
        @if(!empty(trim($content->code_example ?? '')))
        <div class="code-example-box">
            <div class="code-box-header">
                <span>Code Walkthrough &amp; Demonstration ({{ $course->language ?? 'Programming' }})</span>
            </div>
            <pre class="code-snippet-pre">{{ $content->code_example }}</pre>
        </div>
        @endif

        <!-- Key Takeaways -->
        @if(!empty(trim($content->key_takeaways ?? '')))
        <div class="takeaways-alert">
            <strong>💡 Key Takeaways &amp; Exam Tips:</strong>
            <div style="margin-top: 4px; line-height: 1.6;">{!! $content->key_takeaways !!}</div>
        </div>
        @endif
    </div>

    <!-- PRACTICE MCQS & REVIEW SECTION (IF ANY) -->
    @if($mcqs && $mcqs->count() > 0)
    <div class="mcq-section-card">
        <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 14px; border-bottom: 1.5px solid #f1f5f9; padding-bottom: 8px;">
            📝 Practice Review Questions ({{ $mcqs->count() }} MCQs)
        </h3>
        @foreach($mcqs as $q)
            @php
                $opts = is_array($q->options_json) ? $q->options_json : json_decode($q->options_json, true) ?? [];
            @endphp
            <div class="mcq-q-block">
                <div class="mcq-q-title">Q{{ $q->question_number }}. {{ $q->question_text }}</div>
                @foreach($opts as $opt)
                    <div class="mcq-opt-line">
                        <strong>{{ $opt['key'] }}.</strong> {{ $opt['text'] }}
                    </div>
                @endforeach
                <div class="mcq-ans-badge">
                    ✓ Correct Answer: <strong>({{ $q->correct_key }})</strong> &bull; {{ $q->explanation }}
                </div>
            </div>
        @endforeach
    </div>
    @endif

    <script>
        async function downloadAsLandscapePdf() {
            const filename = "Lesson_{{ $lesson->lesson_num }}_{{ preg_replace('/[^a-zA-Z0-9_-]/', '_', $lesson->lesson_title) }}.pdf";
            const element = document.body;

            const opt = {
                margin:       [8, 8, 8, 8],
                filename:     filename,
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true, letterRendering: true, backgroundColor: '#f8fafc' },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
            };

            if (typeof html2pdf !== 'undefined') {
                await html2pdf().set(opt).from(element).save();
            } else {
                window.print();
            }
        }

        // Auto trigger download if ?auto=1 in URL
        if (new URLSearchParams(window.location.search).get('auto') === '1') {
            setTimeout(downloadAsLandscapePdf, 800);
        }
    </script>
</body>
</html>
