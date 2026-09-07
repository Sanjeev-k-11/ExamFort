@extends('layouts.admin')

@section('title', 'Edit Question: ' . $question->question_id)
@section('breadcrumb', 'Question Bank > Edit ' . $question->question_id)

@section('styles')
<style>
    .tc-card {
        background: rgba(15, 23, 42, 0.7);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-sm);
        padding: 14px;
        margin-bottom: 12px;
        position: relative;
        transition: border-color 0.2s;
    }
    .tc-card:hover {
        border-color: rgba(99, 102, 241, 0.4);
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
        padding: 2px 8px;
        border-radius: 4px;
    }
    .tc-badge.pub {
        background: rgba(16, 185, 129, 0.15);
        color: #34d399;
    }
    .tc-badge.hid {
        background: rgba(245, 158, 11, 0.15);
        color: #fbbf24;
    }
    .tc-badge.rubric {
        background: rgba(99, 102, 241, 0.15);
        color: #818cf8;
    }
    .btn-icon-del {
        background: none;
        border: none;
        color: #f87171;
        cursor: pointer;
        padding: 4px;
        border-radius: 4px;
        transition: background 0.15s;
    }
    .btn-icon-del:hover {
        background: rgba(239, 68, 68, 0.15);
    }
</style>
@endsection

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('questions.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Question Bank</span>
    </a>
    <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Edit Assessment Item</h1>
    <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
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
                <input type="text" class="form-control mono" value="{{ $question->exam_code }}" readonly style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label class="form-label">Question ID</label>
                <input type="text" class="form-control mono" value="{{ $question->question_id }}" readonly style="opacity: 0.7;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Question Type</label>
                <input type="text" class="form-control" value="{{ $question->type }}" readonly style="opacity: 0.7;">
            </div>

            <div class="form-group">
                <label class="form-label">Max Score (Marks) *</label>
                <input type="number" step="0.5" name="max_marks" class="form-control" value="{{ old('max_marks', $question->max_marks) }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $question->sort_order) }}">
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Problem Statement / Question Text *</label>
            <textarea name="question_text" class="form-control" style="min-height: 110px;" required>{{ old('question_text', $question->question_text) }}</textarea>
        </div>

        @if($question->type === 'MCQ')
            @php
                $opts = is_array($question->options) ? $question->options : json_decode($question->options, true) ?? [];
                $optMap = [];
                foreach($opts as $o) {
                    $optMap[$o['key']] = $o['text'];
                }
            @endphp
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-top: 24px;">
                <h4 style="font-size: 15px; font-weight: 700; color: #818cf8; margin-bottom: 16px;">
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
                        <option value="A" {{ $question->correct_answer === 'A' ? 'selected' : '' }}>Option A</option>
                        <option value="B" {{ $question->correct_answer === 'B' ? 'selected' : '' }}>Option B</option>
                        <option value="C" {{ $question->correct_answer === 'C' ? 'selected' : '' }}>Option C</option>
                        <option value="D" {{ $question->correct_answer === 'D' ? 'selected' : '' }}>Option D</option>
                    </select>
                </div>
            </div>
        @elseif($question->type === 'CODING')
            @php
                $starters = is_array($question->starter_code_json) ? $question->starter_code_json : json_decode($question->starter_code_json, true) ?? [];
            @endphp
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; margin-top: 24px;">
                <h4 style="font-size: 16px; font-weight: 700; color: #34d399; margin-bottom: 16px;">
                    Coding Challenge Specification
                </h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <div class="form-group">
                        <label class="form-label">Entry Function Name</label>
                        <input type="text" name="entry_function" class="form-control mono" value="{{ old('entry_function', $question->entry_function) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Public Cases Marks</label>
                        <input type="number" step="0.5" name="public_weightage_marks" class="form-control" value="{{ old('public_weightage_marks', $question->public_weightage_marks) }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Hidden Edge Cases Marks</label>
                        <input type="number" step="0.5" name="hidden_weightage_marks" class="form-control" value="{{ old('hidden_weightage_marks', $question->hidden_weightage_marks) }}">
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
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h5 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="eye" style="width: 16px; height: 16px; color: #34d399;"></i>
                                <span>Public Test Cases</span>
                            </h5>
                        </div>
                        <button type="button" onclick="addPublicTestCase()" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
                            <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                            <span>Add Public Test Case</span>
                        </button>
                    </div>
                    <div id="publicTestCasesContainer"></div>
                </div>

                <!-- VISUAL HIDDEN TEST CASES BUILDER -->
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                        <div>
                            <h5 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                                <i data-lucide="eye-off" style="width: 16px; height: 16px; color: #fbbf24;"></i>
                                <span>Hidden Edge Test Cases</span>
                            </h5>
                        </div>
                        <button type="button" onclick="addHiddenTestCase()" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
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
            <div style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; margin-top: 24px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div>
                        <h4 style="font-size: 16px; font-weight: 700; color: #fbbf24; display: flex; align-items: center; gap: 8px;">
                            <i data-lucide="file-text" style="width: 18px; height: 18px;"></i>
                            <span>Descriptive Answer Evaluation Rubrics</span>
                        </h4>
                    </div>
                    <button type="button" onclick="addRubricConcept()" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
                        <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                        <span>Add Evaluation Concept</span>
                    </button>
                </div>

                <div id="rubricConceptsContainer"></div>
                <textarea name="rubric_json_raw" id="rubric_json_raw" style="display:none;"></textarea>
            </div>
        @endif

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="{{ route('questions.index') }}" class="quick-action-btn secondary">Cancel</a>
            <button type="submit" class="quick-action-btn">
                <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                <span>Save Changes</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    let publicTestCases = {!! json_encode(is_array($question->public_test_cases) ? $question->public_test_cases : (json_decode($question->public_test_cases, true) ?? [])) !!};
    let hiddenTestCases = {!! json_encode(is_array($question->hidden_test_cases) ? $question->hidden_test_cases : (json_decode($question->hidden_test_cases, true) ?? [])) !!};
    
    @php
        $rawRubric = is_array($question->rubric_json) ? $question->rubric_json : (json_decode($question->rubric_json, true) ?? []);
        $rawConcepts = $rawRubric['concepts'] ?? [];
    @endphp
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

        lucide.createIcons();
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

        lucide.createIcons();
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

        lucide.createIcons();
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
            const rubricObj = {
                max_marks: {{ $question->max_marks }},
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
