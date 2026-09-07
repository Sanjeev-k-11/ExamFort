@extends('layouts.admin')

@section('title', 'Author Question | ExamFort')
@section('breadcrumb', 'Question Bank > Author Question')

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
    <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Author New Assessment Item</h1>
    <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
        Configure coding challenge problem description, constraints, sample I/O, automated test cases, and multi-language templates.
    </p>
</div>

<div class="glass-card" style="max-width: 960px;">
    <form action="{{ route('questions.store') }}" method="POST" id="questionForm" onsubmit="serializeAllDynamicFields()">
        @csrf

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Assign to Examination Paper *</label>
                <select name="exam_code" class="form-control" required>
                    <option value="">-- Choose Exam Code --</option>
                    @foreach($exams as $exam)
                        <option value="{{ $exam->exam_code }}" {{ (old('exam_code') ?? ($examCode ?? '')) === $exam->exam_code ? 'selected' : '' }}>
                            {{ $exam->exam_code }} &bull; {{ $exam->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Question Number in Paper *</label>
                <input type="number" name="question_number" class="form-control mono" value="{{ old('question_number', $nextNumber ?? 1) }}" required>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1.5fr 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Question / Problem Title *</label>
                <input type="text" name="title" class="form-control" placeholder="e.g. Area and Perimeter of Rectangle, Second Largest Element" value="{{ old('title') }}" required>
            </div>

            <div class="form-group">
                <label class="form-label">Question Type *</label>
                <select name="type" id="questionTypeSelect" class="form-control" onchange="toggleTypeSections()" required>
                    <option value="CODING" {{ (old('type') ?? ($type ?? '')) === 'CODING' ? 'selected' : '' }}>Coding Challenge (Compiler + Test Cases)</option>
                    <option value="MCQ" {{ (old('type') ?? ($type ?? '')) === 'MCQ' ? 'selected' : '' }}>Multiple Choice (MCQ)</option>
                    <option value="PARAGRAPH" {{ (old('type') ?? ($type ?? '')) === 'PARAGRAPH' ? 'selected' : '' }}>Descriptive / Essay (Rubric Graded)</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Max Score (Marks) *</label>
                <input type="number" step="0.5" name="max_marks" class="form-control" placeholder="10.0" value="{{ old('max_marks', 50.0) }}" required>
            </div>
        </div>

        <div class="form-group">
            <label class="form-label">Problem Statement / Question Description *</label>
            <textarea name="question_text" class="form-control" style="min-height: 110px;" placeholder="Describe the problem, task, and requirements for the candidate..." required>{{ old('question_text') }}</textarea>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION: Coding Challenge Specification (Constraints, Sample I/O, Test Cases) -->
        <!-- ========================================================================= -->
        <div id="sectionCoding" style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; margin-top: 24px;">
            <h4 style="font-size: 16px; font-weight: 700; color: #34d399; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="code-2" style="width: 20px; height: 20px;"></i>
                <span>Coding Problem Specification & Constraints (Rendered on Student Client)</span>
            </h4>

            <!-- Constraints Box -->
            <div class="form-group">
                <label class="form-label">Constraints (e.g. 1 &lt;= L, B &lt;= 10000 or 2 &lt;= arr.length &lt;= 10^5)</label>
                <input type="text" name="constraints" class="form-control mono" placeholder="1 <= L, B <= 10000" value="{{ old('constraints', '1 <= L, B <= 10000') }}">
            </div>

            <!-- Sample Input & Sample Output -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Sample Input (Example 1)</label>
                    <textarea name="sample_input" class="form-control mono" style="min-height: 60px;" placeholder="5 10">{{ old('sample_input', '5 10') }}</textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Sample Output (Example 1)</label>
                    <textarea name="sample_output" class="form-control mono" style="min-height: 60px;" placeholder="50 30">{{ old('sample_output', '50 30') }}</textarea>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Sample Explanation (Optional)</label>
                <input type="text" name="explanation" class="form-control" placeholder="Area is 5*10=50, Perimeter is 2*(5+10)=30" value="{{ old('explanation') }}">
            </div>

            <!-- Marks split & Entry function -->
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px; margin-top: 14px;">
                <div class="form-group">
                    <label class="form-label">Entry Function Name</label>
                    <input type="text" name="entry_function" class="form-control mono" placeholder="solve" value="{{ old('entry_function', 'solve') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Public Cases Marks</label>
                    <input type="number" step="0.5" name="public_weightage_marks" class="form-control" value="{{ old('public_weightage_marks', 10.0) }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Hidden Edge Cases Marks</label>
                    <input type="number" step="0.5" name="hidden_weightage_marks" class="form-control" value="{{ old('hidden_weightage_marks', 40.0) }}">
                </div>
            </div>

            <!-- Multi-Language Starter Boilerplate Tabs -->
            <div style="margin-top: 18px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                <label class="form-label" style="font-weight: 700; color: #fff; margin-bottom: 10px;">
                    Language Starter Code Templates for Students
                </label>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px;">C++ (G++ 15.2) Starter</label>
                        <textarea name="starter_cpp" class="form-control mono" style="min-height: 90px;">#include <iostream>
using namespace std;

int main() {
    int l, b;
    if (cin >> l >> b) {
        cout << (l * b) << " " << (2 * (l + b)) << endl;
    }
    return 0;
}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px;">Python 3 (3.14) Starter</label>
                        <textarea name="starter_python" class="form-control mono" style="min-height: 90px;">import sys

def solve():
    # Read L and B from input
    line = sys.stdin.read().split()
    if line:
        l, b = int(line[0]), int(line[1])
        print(f"{l * b} {2 * (l + b)}")

if __name__ == '__main__':
    solve()</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px;">Java 17 (OpenJDK) Starter</label>
                        <textarea name="starter_java" class="form-control mono" style="min-height: 90px;">import java.util.Scanner;

public class Solution {
    public static void main(String[] args) {
        Scanner sc = new Scanner(System.in);
        if (sc.hasNextInt()) {
            int l = sc.nextInt();
            int b = sc.nextInt();
            System.out.println((l * b) + " " + (2 * (l + b)));
        }
    }
}</textarea>
                    </div>

                    <div class="form-group">
                        <label class="form-label" style="font-size: 11px;">C (GCC 15.2) Starter</label>
                        <textarea name="starter_c" class="form-control mono" style="min-height: 90px;">#include <stdio.h>

int main() {
    int l, b;
    if (scanf("%d %d", &l, &b) == 2) {
        printf("%d %d\n", l * b, 2 * (l + b));
    }
    return 0;
}</textarea>
                    </div>
                </div>
            </div>

            <!-- VISUAL PUBLIC TEST CASES BUILDER -->
            <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div>
                        <h5 style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <i data-lucide="eye" style="width: 16px; height: 16px; color: #34d399;"></i>
                            <span>Public Test Cases (Visible to Students in Compiler Console)</span>
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
                            <span>Hidden Edge Test Cases (Secret Automated Grading Suite)</span>
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

        <!-- ========================================================================= -->
        <!-- SECTION: MCQ Options -->
        <!-- ========================================================================= -->
        <div id="sectionMCQ" style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 20px; margin-top: 24px; display: none;">
            <h4 style="font-size: 15px; font-weight: 700; color: #818cf8; margin-bottom: 16px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="check-square" style="width: 18px; height: 18px;"></i>
                <span>Multiple Choice Options & Correct Key</span>
            </h4>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div class="form-group">
                    <label class="form-label">Option A</label>
                    <input type="text" name="option_A" class="form-control" placeholder="Option A text..." value="{{ old('option_A') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Option B</label>
                    <input type="text" name="option_B" class="form-control" placeholder="Option B text..." value="{{ old('option_B') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Option C</label>
                    <input type="text" name="option_C" class="form-control" placeholder="Option C text..." value="{{ old('option_C') }}">
                </div>
                <div class="form-group">
                    <label class="form-label">Option D</label>
                    <input type="text" name="option_D" class="form-control" placeholder="Option D text..." value="{{ old('option_D') }}">
                </div>
            </div>

            <div class="form-group" style="max-width: 240px; margin-top: 6px;">
                <label class="form-label">Correct Answer Key *</label>
                <select name="correct_answer" class="form-control">
                    <option value="A">Option A</option>
                    <option value="B">Option B</option>
                    <option value="C">Option C</option>
                    <option value="D">Option D</option>
                </select>
            </div>
        </div>

        <!-- ========================================================================= -->
        <!-- SECTION: Essay / Descriptive Rubrics -->
        <!-- ========================================================================= -->
        <div id="sectionParagraph" style="background: rgba(0, 0, 0, 0.25); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 22px; margin-top: 24px; display: none;">
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label class="form-label">Minimum Expected Word Count</label>
                    <input type="number" id="rubric_min_words" class="form-control" value="50">
                </div>
                <div class="form-group">
                    <label class="form-label">Total Rubric Max Marks</label>
                    <input type="number" step="0.5" id="rubric_max_marks" class="form-control" value="20.0">
                </div>
            </div>

            <div id="rubricConceptsContainer"></div>
            <textarea name="rubric_json_raw" id="rubric_json_raw" style="display:none;"></textarea>
        </div>

        <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
            <a href="{{ route('questions.index') }}" class="quick-action-btn secondary">Cancel</a>
            <button type="submit" class="quick-action-btn">
                <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                <span>Save Question to Bank</span>
            </button>
        </div>
    </form>
</div>
@endsection

@section('scripts')
<script>
    function toggleTypeSections() {
        const type = document.getElementById('questionTypeSelect').value;
        document.getElementById('sectionCoding').style.display = (type === 'CODING') ? 'block' : 'none';
        document.getElementById('sectionMCQ').style.display = (type === 'MCQ') ? 'block' : 'none';
        document.getElementById('sectionParagraph').style.display = (type === 'PARAGRAPH') ? 'block' : 'none';
    }

    let publicTestCases = [
        { id: "pub_1", input: "5 10", expected: "50 30", desc: "Sample rectangle dimensions" },
        { id: "pub_2", input: "20 30", expected: "600 100", desc: "Larger dimensions" }
    ];

    let hiddenTestCases = [
        { id: "hid_1", input: "1 1", expected: "1 4", desc: "Minimum boundary condition" },
        { id: "hid_2", input: "10000 10000", expected: "100000000 40000", desc: "Maximum boundary condition" }
    ];

    let rubricConcepts = [
        { id: "c1_core", name: "Core Principle Explanation", weight: 10.0, keywords: "formula, logic, boundary", indicators: "correct area formula, correct perimeter formula" }
    ];

    function renderPublicTestCases() {
        const container = document.getElementById('publicTestCasesContainer');
        container.innerHTML = '';

        publicTestCases.forEach((tc, index) => {
            const div = document.createElement('div');
            div.className = 'tc-card';
            div.innerHTML = `
                <div class="tc-header">
                    <span class="tc-badge pub">Public Case #${index + 1} (${tc.id})</span>
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
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.expected)}" oninput="publicTestCases[${index}].expected = this.value" placeholder="e.g. 50 30">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">Test Case Hint / Note</label>
                    <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(tc.desc || '')}" oninput="publicTestCases[${index}].desc = this.value" placeholder="e.g. Standard positive dimensions">
                </div>
            `;
            container.appendChild(div);
        });

        lucide.createIcons();
    }

    function addPublicTestCase() {
        publicTestCases.push({ id: "pub_" + (publicTestCases.length + 1), input: "", expected: "", desc: "" });
        renderPublicTestCases();
    }

    function removePublicTestCase(index) {
        publicTestCases.splice(index, 1);
        renderPublicTestCases();
    }

    function renderHiddenTestCases() {
        const container = document.getElementById('hiddenTestCasesContainer');
        container.innerHTML = '';

        hiddenTestCases.forEach((tc, index) => {
            const div = document.createElement('div');
            div.className = 'tc-card';
            div.innerHTML = `
                <div class="tc-header">
                    <span class="tc-badge hid">Hidden Edge Case #${index + 1} (${tc.id})</span>
                    <button type="button" class="btn-icon-del" onclick="removeHiddenTestCase(${index})" title="Remove">
                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                    </button>
                </div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 10px;">
                    <div>
                        <label class="form-label" style="font-size: 11px;">Secret Input Data (STDIN)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.input)}" oninput="hiddenTestCases[${index}].input = this.value" placeholder="e.g. 1 1">
                    </div>
                    <div>
                        <label class="form-label" style="font-size: 11px;">Expected Secret Output (STDOUT)</label>
                        <input type="text" class="form-control mono" style="font-size: 13px;" value="${escapeHtml(tc.expected)}" oninput="hiddenTestCases[${index}].expected = this.value" placeholder="e.g. 1 4">
                    </div>
                </div>
                <div>
                    <label class="form-label" style="font-size: 11px;">Edge Case Evaluation Note</label>
                    <input type="text" class="form-control" style="font-size: 12px;" value="${escapeHtml(tc.desc || '')}" oninput="hiddenTestCases[${index}].desc = this.value" placeholder="e.g. Minimum boundary condition">
                </div>
            `;
            container.appendChild(div);
        });

        lucide.createIcons();
    }

    function addHiddenTestCase() {
        hiddenTestCases.push({ id: "hid_" + (hiddenTestCases.length + 1), input: "", expected: "", desc: "" });
        renderHiddenTestCases();
    }

    function removeHiddenTestCase(index) {
        hiddenTestCases.splice(index, 1);
        renderHiddenTestCases();
    }

    function renderRubricConcepts() {
        const container = document.getElementById('rubricConceptsContainer');
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
        rubricConcepts.push({ id: "c" + (rubricConcepts.length + 1), name: "", weight: 5.0, keywords: "", indicators: "" });
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
                max_marks: parseFloat(document.getElementById('rubric_max_marks')?.value || 20),
                min_words_soft_limit: parseInt(document.getElementById('rubric_min_words')?.value || 50),
                concepts: rubricConcepts.map(c => ({
                    id: c.id,
                    name: c.name,
                    weight: c.weight,
                    keywords: c.keywords.split(',').map(s => s.trim()).filter(Boolean),
                    explanation_indicators: c.indicators.split(',').map(s => s.trim()).filter(Boolean)
                }))
            };
            rubricField.value = JSON.stringify(rubricObj);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    toggleTypeSections();
    renderPublicTestCases();
    renderHiddenTestCases();
    renderRubricConcepts();
</script>
@endsection
