@extends('layouts.admin')

@section('title', 'Edit Question #' . ($question->question_number ?? $question->id))
@section('breadcrumb', 'Question Bank > Edit Question')

@section('styles')
<style>
    .tc-card {
        background: rgba(248, 250, 252, 0.9);
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 14px;
        margin-bottom: 12px;
        position: relative;
        transition: all 0.2s;
    }
    .tc-card:hover {
        border-color: #818cf8;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.06);
    }
    .tc-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }
    .tc-badge {
        font-size: 11px;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 6px;
    }
    .tc-badge.pub {
        background: #ecfdf5;
        color: #059669;
        border: 1px solid #a7f3d0;
    }
    .tc-badge.hid {
        background: #fffbeb;
        color: #d97706;
        border: 1px solid #fde68a;
    }
    .tc-badge.rubric {
        background: #eef2ff;
        color: #4f46e5;
        border: 1px solid #c7d2fe;
    }
    .btn-icon-del {
        background: #fee2e2;
        border: none;
        color: #dc2626;
        cursor: pointer;
        padding: 5px;
        border-radius: 6px;
        transition: all 0.15s;
    }
    .btn-icon-del:hover {
        background: #fecaca;
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('questions.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Question Bank</span>
    </a>
    <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Edit Assessment Item</h1>
    <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
        Update question payload, evaluation parameters, test cases, and rubrics.
    </p>
</div>

<div class="glass-card" style="max-width: 960px;">
    <form action="{{ route('questions.update', $question->id) }}" method="POST" id="editQuestionForm" onsubmit="serializeAllDynamicFields()">
        @csrf
        @method('PUT')

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Exam Code</label>
                <input type="text" class="form-control mono" value="{{ $question->exam_code }}" readonly style="background: #f1f5f9; color: #64748b;">
            </div>

            <div class="form-group">
                <label class="form-label">Question Number *</label>
                <input type="number" name="question_number" class="form-control mono" value="{{ old('question_number', $question->question_number ?? 1) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Question / Problem Title *</label>
                <input type="text" name="title" class="form-control" value="{{ old('title', $question->title ?? ('Question #' . ($question->question_number ?? 1))) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Question Type</label>
                <input type="text" class="form-control" value="{{ $question->type }}" readonly style="background: #f1f5f9; color: #64748b;">
            </div>

            <div class="form-group">
                <label class="form-label">Max Score (Marks) *</label>
                <input type="number" step="0.5" name="max_marks" id="max_marks_input" class="form-control" value="{{ old('max_marks', $question->max_marks) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Problem Statement / Question Text *</label>
            <textarea name="question_text" class="form-control" style="min-height: 110px;" required>{{ old('question_text', $question->question_text) }}</textarea>
        </div>

        @if($question->type === 'MCQ')
            @php
                $rawOpts = $question->options;
                if (is_string($rawOpts)) {
                    $decoded = json_decode($rawOpts, true);
                    $rawOpts = is_array($decoded) ? $decoded : [];
                } elseif (!is_array($rawOpts)) {
                    $rawOpts = [];
                }

                $optMap = ['A' => '', 'B' => '', 'C' => '', 'D' => ''];
                $keys = ['A', 'B', 'C', 'D'];
                $idx = 0;
                foreach ($rawOpts as $k => $val) {
                    if (is_array($val)) {
                        $optKey = $val['key'] ?? ($keys[$idx] ?? chr(65 + $idx));
                        $optText = $val['text'] ?? $val['value'] ?? '';
                        $optMap[$optKey] = $optText;
                    } elseif (is_string($val)) {
                        if (isset($optMap[$k])) {
                            $optMap[$k] = $val;
                        } elseif (isset($keys[$idx])) {
                            $optMap[$keys[$idx]] = $val;
                        }
                    }
                    $idx++;
                }
            @endphp
            <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 20px; margin-top: 24px;">
                <h4 style="font-size: 15px; font-weight: 700; color: #4f46e5; margin-bottom: 16px;">
                    Multiple Choice Options
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Option A</label>
                        <input type="text" name="option_A" class="form-control" value="{{ old('option_A', $optMap['A'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option B</label>
                        <input type="text" name="option_B" class="form-control" value="{{ old('option_B', $optMap['B'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option C</label>
                        <input type="text" name="option_C" class="form-control" value="{{ old('option_C', $optMap['C'] ?? '') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Option D</label>
                        <input type="text" name="option_D" class="form-control" value="{{ old('option_D', $optMap['D'] ?? '') }}">
                    </div>
                </div>

                <div class="form-group" style="max-width: 240px; margin-top: 6px;">
                    <label class="form-label">Correct Answer Key *</label>
                    <select name="correct_answer" class="form-control">
                        <option value="A" {{ ($question->correct_answer ?? 'A') === 'A' ? 'selected' : '' }}>Option A</option>
                        <option value="B" {{ ($question->correct_answer ?? '') === 'B' ? 'selected' : '' }}>Option B</option>
                        <option value="C" {{ ($question->correct_answer ?? '') === 'C' ? 'selected' : '' }}>Option C</option>
                        <option value="D" {{ ($question->correct_answer ?? '') === 'D' ? 'selected' : '' }}>Option D</option>
                    </select>
                </div>
            </div>
        @elseif($question->type === 'CODING')
            @php
                $starters = $question->coding_starter_code ?? $question->starter_code_json ?? [];
                if (is_string($starters)) {
                    $starters = json_decode($starters, true) ?? [];
                }
            @endphp
            <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 22px; margin-top: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: #059669; margin-bottom: 16px;">
                    Coding Challenge Specification
                </h4>

                <div class="form-group">
                    <label class="form-label">Constraints</label>
                    <input type="text" name="constraints" class="form-control mono" value="{{ old('constraints', $question->constraints) }}" placeholder="e.g. 1 <= N <= 10^5">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Sample Input (STDIN)</label>
                        <textarea name="sample_input" class="form-control mono" rows="3">{{ old('sample_input', $question->sample_input) }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Sample Output (STDOUT)</label>
                        <textarea name="sample_output" class="form-control mono" rows="3">{{ old('sample_output', $question->sample_output) }}</textarea>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Entry Function Name</label>
                        <input type="text" name="entry_function" class="form-control mono" value="{{ old('entry_function', $question->entry_function ?? 'solve') }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Public Cases Marks</label>
                        <input type="number" step="0.5" name="public_weightage_marks" class="form-control" value="{{ old('public_weightage_marks', $question->public_weightage_marks ?? 10) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hidden Edge Cases Marks</label>
                        <input type="number" step="0.5" name="hidden_weightage_marks" class="form-control" value="{{ old('hidden_weightage_marks', $question->hidden_weightage_marks ?? 40) }}">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div class="form-group">
                        <label class="form-label">C++ Starter Code</label>
                        <textarea name="starter_cpp" class="form-control mono" style="min-height: 90px;">{{ $starters['cpp'] ?? '' }}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Python Starter Code</label>
                        <textarea name="starter_python" class="form-control mono" style="min-height: 90px;">{{ $starters['python'] ?? '' }}</textarea>
                    </div>
                </div>

                <!-- VISUAL PUBLIC TEST CASES BUILDER -->
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h5 style="font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="eye" style="width: 16px; height: 16px; color: #059669;"></i>
                                <span>Public Test Cases</span>
                            </h5>
                        </div>
                        <button type="button" onclick="addPublicTestCase()" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                            <span>Add Public Test Case</span>
                        </button>
                    </div>
                    <div id="publicTestCasesContainer"></div>
                </div>

                <!-- VISUAL HIDDEN TEST CASES BUILDER -->
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h5 style="font-size: 14px; font-weight: 700; color: #0f172a; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="eye-off" style="width: 16px; height: 16px; color: #d97706;"></i>
                                <span>Hidden Edge Test Cases</span>
                            </h5>
                        </div>
                        <button type="button" onclick="addHiddenTestCase()" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                            <span>Add Hidden Test Case</span>
                        </button>
                    </div>
                    <div id="hiddenTestCasesContainer"></div>
                </div>

                <textarea name="public_test_cases_json" id="public_test_cases_json" style="display:none;"></textarea>
                <textarea name="hidden_test_cases_json" id="hidden_test_cases_json" style="display:none;"></textarea>
            </div>
        @elseif($question->type === 'PARAGRAPH')
            <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 22px; margin-top: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #d97706; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                            <span>Descriptive Answer Evaluation Rubrics</span>
                        </h4>
                    </div>
                    <button type="button" onclick="addRubricConcept()" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>Add Evaluation Concept</span>
                    </button>
                </div>

                <div id="rubricConceptsContainer"></div>
                <textarea name="rubric_json_raw" id="rubric_json_raw" style="display:none;"></textarea>
            </div>
        @endif

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            <a href="{{ route('questions.index') }}" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    @php
        $pubCases = $question->public_test_cases ?? [];
        if (is_string($pubCases)) { $pubCases = json_decode($pubCases, true) ?? []; }
        if (!is_array($pubCases)) { $pubCases = []; }

        $hidCases = $question->hidden_test_cases ?? [];
        if (is_string($hidCases)) { $hidCases = json_decode($hidCases, true) ?? []; }
        if (!is_array($hidCases)) { $hidCases = []; }

        $rawRubric = $question->rubric_json ?? [];
        if (is_string($rawRubric)) { $rawRubric = json_decode($rawRubric, true) ?? []; }
        if (!is_array($rawRubric)) { $rawRubric = []; }
        $rawConcepts = is_array($rawRubric['concepts'] ?? null) ? $rawRubric['concepts'] : [];
    @endphp

    let publicTestCases = {!! json_encode($pubCases) !!};
    let hiddenTestCases = {!! json_encode($hidCases) !!};
    
    let rubricConcepts = {!! json_encode(array_map(function($c) {
        return [
            'id' => $c['id'] ?? 'c1',
            'name' => $c['name'] ?? '',
            'weight' => $c['weight'] ?? 5.0,
            'keywords' => is_array($c['keywords'] ?? null) ? implode(', ', $c['keywords']) : ($c['keywords'] ?? ''),
            'indicators' => is_array($c['explanation_indicators'] ?? null) ? implode(', ', $c['explanation_indicators']) : ($c['explanation_indicators'] ?? '')
        ];
    }, $rawConcepts)) !!};

    function renderPublicTestCases() {
        const container = document.getElementById('publicTestCasesContainer');
        if (!container) return;
        container.innerHTML = '';

        publicTestCases.forEach((tc, index) => {
            const div = document.createElement('div');
            div.className = 'tc-card';
            div.innerHTML = `
                <div class="tc-header">
                    <span class="tc-badge pub">Public Case #${index + 1} (${tc.id || 'pub_' + (index+1)})</span>
                    <button type="button" class="btn-icon-del" onclick="removePublicTestCase(${index})" title="Remove">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Input Data (STDIN)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.input)}" oninput="publicTestCases[${index}].input = this.value" placeholder="e.g. 5 10">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Expected Output (STDOUT)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.expected)}" oninput="publicTestCases[${index}].expected = this.value" placeholder="e.g. 15">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">Description / Hint</label>
                    <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(tc.desc || '')}" oninput="publicTestCases[${index}].desc = this.value" placeholder="e.g. Basic addition test">
                </div>
            `;
            container.appendChild(div);
        });

        if (window.lucide) { lucide.createIcons(); }
    }

    function addPublicTestCase() {
        publicTestCases.push({
            id: "pub_" + (publicTestCases.length + 1),
            input: "",
            expected: "",
            desc: ""
        });
        renderPublicTestCases();
    }

    function removePublicTestCase(index) {
        publicTestCases.splice(index, 1);
        renderPublicTestCases();
    }

    function renderHiddenTestCases() {
        const container = document.getElementById('hiddenTestCasesContainer');
        if (!container) return;
        container.innerHTML = '';

        hiddenTestCases.forEach((tc, index) => {
            const div = document.createElement('div');
            div.className = 'tc-card';
            div.innerHTML = `
                <div class="tc-header">
                    <span class="tc-badge hid">Hidden Edge Case #${index + 1} (${tc.id || 'hid_' + (index+1)})</span>
                    <button type="button" class="btn-icon-del" onclick="removeHiddenTestCase(${index})" title="Remove">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Secret Input Data (STDIN)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.input)}" oninput="hiddenTestCases[${index}].input = this.value" placeholder="e.g. -5 10">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Expected Secret Output (STDOUT)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.expected)}" oninput="hiddenTestCases[${index}].expected = this.value" placeholder="e.g. 5">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">Edge Case Evaluation Note</label>
                    <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(tc.desc || '')}" oninput="hiddenTestCases[${index}].desc = this.value" placeholder="e.g. Boundary condition">
                </div>
            `;
            container.appendChild(div);
        });

        if (window.lucide) { lucide.createIcons(); }
    }

    function addHiddenTestCase() {
        hiddenTestCases.push({
            id: "hid_" + (hiddenTestCases.length + 1),
            input: "",
            expected: "",
            desc: ""
        });
        renderHiddenTestCases();
    }

    function removeHiddenTestCase(index) {
        hiddenTestCases.splice(index, 1);
        renderHiddenTestCases();
    }

    function renderRubricConcepts() {
        const container = document.getElementById('rubricConceptsContainer');
        if (!container) return;
        container.innerHTML = '';

        rubricConcepts.forEach((c, index) => {
            const div = document.createElement('div');
            div.className = 'tc-card';
            div.innerHTML = `
                <div class="tc-header">
                    <span class="tc-badge rubric">Concept Criterion #${index + 1}</span>
                    <button type="button" class="btn-icon-del" onclick="removeRubricConcept(${index})" title="Remove">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 12px; margin-bottom: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Concept Name *</label>
                        <input type="text" class="form-control" style="font-size: 13px;" value="${escapeHtml(c.name)}" oninput="rubricConcepts[${index}].name = this.value">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Marks Weightage</label>
                        <input type="number" step="0.5" class="form-control" style="font-size: 13px;" value="${c.weight}" oninput="rubricConcepts[${index}].weight = parseFloat(this.value) || 0">
                    </div>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Mandatory Keywords (comma separated)</label>
                        <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(c.keywords)}" oninput="rubricConcepts[${index}].keywords = this.value">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Key Explanation Indicators (comma separated)</label>
                        <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(c.indicators)}" oninput="rubricConcepts[${index}].indicators = this.value">
                    </div>
                </div>
            `;
            container.appendChild(div);
        });

        if (window.lucide) { lucide.createIcons(); }
    }

    function addRubricConcept() {
        rubricConcepts.push({
            id: "c" + (rubricConcepts.length + 1),
            name: "",
            weight: 5.0,
            keywords: "",
            indicators: ""
        });
        renderRubricConcepts();
    }

    function removeRubricConcept(index) {
        rubricConcepts.splice(index, 1);
        renderRubricConcepts();
    }

    function serializeAllDynamicFields() {
        const pubField = document.getElementById('public_test_cases_json');
        if (pubField) pubField.value = JSON.stringify(publicTestCases);

        const hidField = document.getElementById('hidden_test_cases_json');
        if (hidField) hidField.value = JSON.stringify(hiddenTestCases);

        const rubricField = document.getElementById('rubric_json_raw');
        if (rubricField) {
            const maxMarksInput = document.getElementById('max_marks_input');
            const marksVal = maxMarksInput ? parseFloat(maxMarksInput.value) || 10 : 10;
            const rubricObj = {
                max_marks: marksVal,
                concepts: rubricConcepts.map(c => ({
                    id: c.id,
                    name: c.name,
                    weight: c.weight,
                    keywords: (typeof c.keywords === 'string' ? c.keywords.split(',') : c.keywords).map(s => String(s).trim()).filter(Boolean),
                    explanation_indicators: (typeof c.indicators === 'string' ? c.indicators.split(',') : c.indicators).map(s => String(s).trim()).filter(Boolean)
                }))
            };
            rubricField.value = JSON.stringify(rubricObj);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    renderPublicTestCases();
    renderHiddenTestCases();
    renderRubricConcepts();
</script>
@endsection
