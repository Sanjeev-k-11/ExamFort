@extends('layouts.admin')

@section('title', 'AI Exam Paper Generator | ExamFort')
@section('breadcrumb', 'Exams > Google Gemini AI Exam Generator')

@section('styles')
<style>
    .ai-hero-card {
        background: linear-gradient(135deg, rgba(238, 242, 255, 0.95), rgba(240, 249, 255, 0.95), rgba(253, 242, 248, 0.9));
        border: 1px solid rgba(199, 210, 254, 0.8);
        border-radius: var(--radius-lg);
        padding: 32px;
        margin-bottom: 28px;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.08);
    }
    .preset-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid #c7d2fe;
        color: #4338ca;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .preset-pill:hover {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
    }
    .q-preview-card {
        background: rgba(255, 255, 255, 0.95);
        border: 1px solid #e2e8f0;
        border-radius: var(--radius-md);
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        transition: all 0.2s ease;
    }
    .q-preview-card:hover {
        border-color: #818cf8;
        box-shadow: 0 8px 20px rgba(99, 102, 241, 0.08);
    }
    .type-badge {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        padding: 4px 10px;
        border-radius: 6px;
        letter-spacing: 0.5px;
    }
    .type-badge.CODING {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .type-badge.MCQ {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .type-badge.PARAGRAPH {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .spinner {
        display: inline-block;
        width: 18px;
        height: 18px;
        border: 2px solid rgba(255,255,255,0.3);
        border-radius: 50%;
        border-top-color: #fff;
        animation: spin 0.8s ease-in-out infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }
</style>
@endsection

@section('content')
<!-- Hero Banner -->
<div class="ai-hero-card">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                <span style="font-size: 24px;">✨</span>
                <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Google Gemini AI Exam Paper Generator</h1>
                <span class="type-badge MCQ" style="font-size: 11px;">Powered by Gemini AI</span>
            </div>
            <p style="color: #475569; font-size: 14px; max-width: 720px; line-height: 1.5;">
                Provide your subject syllabus prompt and question distribution. Google Gemini AI will instantly author complete MCQs with options, Coding challenges with constraints & test cases, and Essay rubrics ready for deployment.
            </p>
        </div>

        <div>
            @if(!empty($currentUser->gemini_api_key) || !empty($currentUser->organization->gemini_api_key))
                <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 8px 16px; border-radius: 10px; display: flex; align-items: center; gap: 8px; color: #059669; font-size: 13px; font-weight: 600; box-shadow: 0 2px 6px rgba(16, 185, 129, 0.1);">
                    <i data-lucide="check-circle" style="width: 16px; height: 16px;"></i>
                    <span>Gemini API Key Active</span>
                </div>
            @else
                <a href="{{ route('dashboard') }}" style="background: #fef2f2; border: 1px solid #fecaca; padding: 8px 16px; border-radius: 10px; display: inline-flex; align-items: center; gap: 8px; color: #dc2626; font-size: 13px; font-weight: 600; text-decoration: none;">
                    <i data-lucide="alert-triangle" style="width: 16px; height: 16px;"></i>
                    <span>Set Gemini Key in Dashboard</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Generator Configuration Form -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div>
            <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sliders" style="width: 20px; height: 20px; color: #4f46e5;"></i>
                <span>Examination Paper Generation Parameters</span>
            </h3>
        </div>
    </div>

    <form id="aiGeneratorForm" onsubmit="handleGenerate(event)">
        <div style="display: grid; grid-template-columns: 1.2fr 1fr 1fr; gap: 20px; margin-bottom: 20px;">
            <div class="form-group">
                <label class="form-label">Target Examination Paper *</label>
                <select id="examCodeSelect" class="form-control" required>
                    <option value="">-- Choose Existing Examination Paper --</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->exam_code }}" {{ ($examCode ?? '') === $exam->exam_code ? 'selected' : '' }}>
                            {{ $exam->exam_code }} &bull; {{ $exam->title }} ({{ $exam->questions_count ?? 0 }} Existing Questions)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">AI Engine / Model Version *</label>
                <select id="aiModelSelect" class="form-control" style="color: #0284c7; font-weight: 700;">
                    <option value="gemini-3.6-flash" selected>⚡ Gemini 3.6 Flash (Ultra Fast & Recommended)</option>
                    <option value="gemini-3.5-flash">🚀 Gemini 3.5 Flash</option>
                    <option value="gemini-flash-latest">✨ Gemini Flash Latest</option>
                    <option value="gemini-3.7-flash">🔥 Gemini 3.7 Flash</option>
                    <option value="gemini-3.1-flash-lite">⚡ Gemini 3.1 Flash Lite</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Assessment Difficulty Level *</label>
                <select id="difficultySelect" class="form-control" required>
                    <option value="Medium" selected>Medium (Standard Mid/End-Sem)</option>
                    <option value="Easy">Easy (Foundational & Conceptual)</option>
                    <option value="Hard">Hard (Advanced Problem Solving)</option>
                    <option value="Competitive">Competitive (GATE / Industry Standard)</option>
                </select>
            </div>
        </div>

        <!-- Subject Topic Prompt -->
        <div class="form-group" style="margin-bottom: 20px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                <label class="form-label" style="margin-bottom: 0;">Syllabus Topic & Specific Guidelines Prompt *</label>
                <span style="font-size: 11px; color: #64748b;">Click preset or write custom prompt</span>
            </div>

            <textarea id="topicPromptInput" class="form-control" style="min-height: 90px; font-size: 13.5px;" placeholder="e.g. Data Structures & Algorithms in Python focusing on Binary Trees, Hash Tables, Dynamic Programming, and Graph Traversals for 3rd year engineering students..." required>Data Structures and Algorithms in C++ and Python covering Binary Trees, Sorting, and Dynamic Programming.</textarea>

            <!-- Quick Presets -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px;">
                <span class="preset-pill" onclick="setPreset('Data Structures & Algorithms: Trees, Graphs, Sorting, Dynamic Programming')">
                    🌳 DSA & Algorithms
                </span>
                <span class="preset-pill" onclick="setPreset('Database Management Systems (DBMS): Normalization, SQL Joins, ACID Transactions, and Indexing')">
                    🗄️ DBMS & SQL
                </span>
                <span class="preset-pill" onclick="setPreset('Operating Systems: Process Synchronization, CPU Scheduling, Deadlocks, and Paging')">
                    💻 Operating Systems
                </span>
                <span class="preset-pill" onclick="setPreset('Object Oriented Programming in Java: Polymorphism, Inheritance, Exception Handling, Interfaces')">
                    ☕ Java OOP & Collections
                </span>
                <span class="preset-pill" onclick="setPreset('Machine Learning & Deep Learning: Loss Functions, Backpropagation, CNNs, Transformers, and Overfitting')">
                    🤖 AI / Machine Learning
                </span>
            </div>
        </div>

        <!-- Question Type Distribution Counters -->
        <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 18px; margin-bottom: 24px;">
            <label class="form-label" style="font-weight: 700; color: #0f172a; margin-bottom: 14px;">
                Question Paper Composition & Counts
            </label>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px;">
                <!-- MCQ Counter -->
                <div style="background: #ffffff; border: 1px solid #c7d2fe; border-radius: 10px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="type-badge MCQ">Multiple Choice (MCQ)</span>
                        <span style="font-size: 11px; color: #64748b;">4 options + explanation</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="mcqCountInput" class="form-control mono" style="font-size: 18px; font-weight: 700; text-align: center;" value="4" min="0" max="20">
                        <span style="font-size: 13px; color: #475569; font-weight: 600;">Questions</span>
                    </div>
                </div>

                <!-- Coding Counter -->
                <div style="background: #ffffff; border: 1px solid #a7f3d0; border-radius: 10px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="type-badge CODING">Coding Challenges</span>
                        <span style="font-size: 11px; color: #64748b;">Constraints + Test Cases</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="codingCountInput" class="form-control mono" style="font-size: 18px; font-weight: 700; text-align: center;" value="2" min="0" max="10">
                        <span style="font-size: 13px; color: #475569; font-weight: 600;">Questions</span>
                    </div>
                </div>

                <!-- Descriptive / Essay Counter -->
                <div style="background: #ffffff; border: 1px solid #fde68a; border-radius: 10px; padding: 14px; box-shadow: 0 2px 6px rgba(0,0,0,0.02);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span class="type-badge PARAGRAPH">Descriptive / Essay</span>
                        <span style="font-size: 11px; color: #64748b;">Concept Rubrics</span>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <input type="number" id="paragraphCountInput" class="form-control mono" style="font-size: 18px; font-weight: 700; text-align: center;" value="1" min="0" max="10">
                        <span style="font-size: 13px; color: #475569; font-weight: 600;">Questions</span>
                    </div>
                </div>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 12px;">
            <button type="submit" id="btnGenerate" class="btn btn-primary" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); font-size: 14px; padding: 12px 28px; box-shadow: 0 6px 18px rgba(79, 70, 229, 0.3);">
                <i data-lucide="sparkles" id="btnGenIcon" style="width: 18px; height: 18px;"></i>
                <span id="btnGenText">Generate Full Exam Paper with Gemini AI</span>
            </button>
        </div>
    </form>
</div>

<!-- AI Generated Questions Review Container -->
<div id="reviewSection" style="display: none;">
    <div class="glass-card" style="margin-bottom: 32px; border-left: 4px solid #059669;">
        <div class="card-header-flex">
            <div>
                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="check-circle" style="width: 20px; height: 20px; color: #059669;"></i>
                    <span>AI Generated Paper Preview (<span id="lblGenCount">0</span> Questions Ready)</span>
                </h3>
                <p style="color: #64748b; font-size: 12px; margin-top: 2px;">
                    Review the generated questions, constraints, and test suites below. Click Save to automatically add them to the exam paper.
                </p>
            </div>

            <div style="display: flex; gap: 10px;">
                <button type="button" onclick="saveAllGeneratedQuestions()" id="btnSaveBatch" class="btn btn-success" style="font-size: 13px;">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save All Questions to Exam</span>
                </button>
            </div>
        </div>

        <div id="questionsPreviewList" style="margin-top: 20px;"></div>

        <div style="display: flex; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <button type="button" onclick="saveAllGeneratedQuestions()" class="btn btn-success" style="font-size: 14px; padding: 10px 24px;">
                <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                <span>Confirm & Add to Exam Paper</span>
            </button>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let generatedQuestions = [];

    function setPreset(text) {
        document.getElementById('topicPromptInput').value = text;
    }

    async function handleGenerate(e) {
        e.preventDefault();

        const examCode = document.getElementById('examCodeSelect').value;
        const aiModel = document.getElementById('aiModelSelect').value;
        const topic = document.getElementById('topicPromptInput').value;
        const difficulty = document.getElementById('difficultySelect').value;
        const mcqCount = document.getElementById('mcqCountInput').value;
        const codingCount = document.getElementById('codingCountInput').value;
        const paragraphCount = document.getElementById('paragraphCountInput').value;

        if (!examCode) {
            alert('Please select a Target Examination Paper.');
            return;
        }

        const btn = document.getElementById('btnGenerate');
        const btnText = document.getElementById('btnGenText');
        const btnIcon = document.getElementById('btnGenIcon');

        btn.disabled = true;
        btnText.textContent = `Generating with ${aiModel} (takes ~5-15s)...`;
        btnIcon.outerHTML = '<span class="spinner" id="btnGenIcon"></span>';

        try {
            const res = await fetch("{{ route('ai.generate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    exam_code: examCode,
                    ai_model: aiModel,
                    subject_topic: topic,
                    difficulty: difficulty,
                    mcq_count: mcqCount,
                    coding_count: codingCount,
                    paragraph_count: paragraphCount
                })
            });

            const data = await res.json();

            if (!data.success) {
                alert('⚠️ AI Generation Error: ' + (data.message || 'Unknown error.'));
                return;
            }

            generatedQuestions = data.questions || [];
            renderGeneratedPreview(generatedQuestions);

            // Scroll down to preview
            document.getElementById('reviewSection').style.display = 'block';
            document.getElementById('reviewSection').scrollIntoView({ behavior: 'smooth' });

        } catch (err) {
            alert('Network / Server Error: ' + err.message);
        } finally {
            btn.disabled = false;
            btnText.textContent = 'Generate Full Exam Paper with Gemini AI';
            const spin = document.getElementById('btnGenIcon');
            if (spin) {
                spin.outerHTML = '<i data-lucide="sparkles" id="btnGenIcon" style="width: 18px; height: 18px;"></i>';
                lucide.createIcons();
            }
        }
    }

    function renderGeneratedPreview(questions) {
        const container = document.getElementById('questionsPreviewList');
        document.getElementById('lblGenCount').textContent = questions.length;
        container.innerHTML = '';

        questions.forEach((q, idx) => {
            const card = document.createElement('div');
            card.className = 'q-preview-card';

            let typeBadge = `<span class="type-badge ${q.type}">${q.type}</span>`;
            let specificHtml = '';

            if (q.type === 'MCQ') {
                const opts = q.options || [];
                specificHtml = `
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 8px; margin-top: 12px;">
                        ${opts.map(o => `
                            <div style="background: ${o.key === q.correct_answer ? '#f0fdf4' : '#f8fafc'}; border: 1px solid ${o.key === q.correct_answer ? '#86efac' : '#e2e8f0'}; padding: 8px 12px; border-radius: 6px; font-size: 13px; color: ${o.key === q.correct_answer ? '#15803d' : '#334155'}; font-weight: ${o.key === q.correct_answer ? '700' : 'normal'};">
                                <strong>${o.key}:</strong> ${escapeHtml(o.text)} ${o.key === q.correct_answer ? ' ✓ (Correct)' : ''}
                            </div>
                        `).join('')}
                    </div>
                `;
            } else if (q.type === 'CODING') {
                const pubCount = (q.public_test_cases || []).length;
                const hidCount = (q.hidden_test_cases || []).length;
                specificHtml = `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 14px; border-radius: 8px; margin-top: 12px; font-size: 12.5px;">
                        <div style="color: #4f46e5; margin-bottom: 6px;"><strong>Constraints:</strong> <code style="background: #eef2ff; padding: 2px 6px; border-radius: 4px;">${escapeHtml(q.constraints || 'Standard')}</code></div>
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 8px;">
                            <div>
                                <span style="color: #64748b; font-size: 11px; font-weight: 600;">Sample Input:</span>
                                <pre class="mono" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 8px; border-radius: 6px; margin-top: 4px; font-size: 12px;">${escapeHtml(q.sample_input || 'N/A')}</pre>
                            </div>
                            <div>
                                <span style="color: #64748b; font-size: 11px; font-weight: 600;">Sample Output:</span>
                                <pre class="mono" style="background: #ffffff; border: 1px solid #cbd5e1; padding: 8px; border-radius: 6px; margin-top: 4px; font-size: 12px;">${escapeHtml(q.sample_output || 'N/A')}</pre>
                            </div>
                        </div>
                        <div style="margin-top: 10px; display: flex; gap: 14px; font-size: 11.5px; font-weight: 600; flex-wrap: wrap;">
                            <span style="color: #059669;">✓ ${pubCount} Public Test Cases</span>
                            <span style="color: #d97706;">✓ ${hidCount} Hidden Edge Cases</span>
                            <span style="color: #4f46e5;">✓ Multi-language Boilerplates</span>
                        </div>
                    </div>
                `;
            } else if (q.type === 'PARAGRAPH') {
                const concepts = q.rubric_json?.concepts || [];
                specificHtml = `
                    <div style="background: #fffbeb; border: 1px solid #fde68a; padding: 12px 14px; border-radius: 8px; margin-top: 12px; font-size: 12.5px;">
                        <div style="color: #b45309; font-weight: 700; margin-bottom: 6px;">Evaluation Rubric Concepts (${concepts.length}):</div>
                        ${concepts.map(c => `
                            <div style="margin-bottom: 4px; color: #78350f;">
                                &bull; <strong>${escapeHtml(c.name)}</strong> (${c.weight} Marks) - Keywords: <code>${(c.keywords || []).join(', ')}</code>
                            </div>
                        `).join('')}
                    </div>
                `;
            }

            card.innerHTML = `
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-weight: 800; font-size: 14px; color: #0f172a;">Q${idx + 1}.</span>
                        ${typeBadge}
                        <h4 style="font-size: 15px; font-weight: 700; color: #0f172a;">${escapeHtml(q.title || ('Question ' + (idx + 1)))}</h4>
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">${q.max_marks || 10} Marks</span>
                        <button type="button" onclick="removeGeneratedQ(${idx})" style="background: none; border: none; color: #dc2626; cursor: pointer;" title="Delete Question">
                            <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                        </button>
                    </div>
                </div>

                <div style="font-size: 13.5px; color: #334155; line-height: 1.5; white-space: pre-wrap;">${escapeHtml(q.question_text)}</div>

                ${specificHtml}
            `;

            container.appendChild(card);
        });

        lucide.createIcons();
    }

    function removeGeneratedQ(idx) {
        generatedQuestions.splice(idx, 1);
        renderGeneratedPreview(generatedQuestions);
    }

    async function saveAllGeneratedQuestions() {
        if (!generatedQuestions || generatedQuestions.length === 0) {
            alert('No questions to save.');
            return;
        }

        const examCode = document.getElementById('examCodeSelect').value;
        const btn = document.getElementById('btnSaveBatch');
        btn.disabled = true;
        btn.innerHTML = '<span>Saving to Examination...</span>';

        try {
            const res = await fetch("{{ route('ai.saveBatch') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    exam_code: examCode,
                    questions: generatedQuestions
                })
            });

            const data = await res.json();
            if (data.success) {
                alert(data.message || 'Questions saved successfully!');
                window.location.href = data.redirect_url || "{{ route('questions.index') }}";
            } else {
                alert('Error: ' + data.message);
                btn.disabled = false;
                btn.innerHTML = '<i data-lucide="save" style="width: 16px; height: 16px;"></i><span>Save All Questions to Exam</span>';
                lucide.createIcons();
            }
        } catch (err) {
            alert('Save failed: ' + err.message);
            btn.disabled = false;
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }
</script>
@endsection
