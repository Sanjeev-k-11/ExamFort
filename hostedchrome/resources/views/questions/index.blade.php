@extends('layouts.admin')

@section('title', 'Question Bank')
@section('breadcrumb', 'Question Bank')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Question Bank & Blueprint Repository</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                Manage MCQs, multi-language coding challenges, and paragraph rubrics across exams.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            <a href="{{ route('ai.generator') }}" class="btn btn-primary" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);">
                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                <span>✨ AI Exam Generator</span>
            </a>
            <a href="{{ route('questions.create') }}" class="btn btn-secondary">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                <span>Add Question</span>
            </a>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('questions.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <select name="exam_code" class="form-control" style="width: auto; min-width: 220px;" onchange="this.form.submit()">
            <option value="">All Examinations</option>
            @foreach($exams as $ex)
                <option value="{{ $ex->exam_code }}" {{ $examCode == $ex->exam_code ? 'selected' : '' }}>
                    {{ $ex->exam_code }} - {{ Str::limit($ex->title, 30) }}
                </option>
            @endforeach
        </select>

        <select name="type" class="form-control" style="width: auto; min-width: 150px;" onchange="this.form.submit()">
            <option value="">All Question Types</option>
            <option value="MCQ" {{ $type === 'MCQ' ? 'selected' : '' }}>MCQ (Multiple Choice)</option>
            <option value="CODING" {{ $type === 'CODING' ? 'selected' : '' }}>CODING (Programming)</option>
            <option value="PARAGRAPH" {{ $type === 'PARAGRAPH' ? 'selected' : '' }}>PARAGRAPH (Essay / Rubric)</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if($examCode || $type)
            <a href="{{ route('questions.index') }}" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">Clear Filters</a>
        @endif
    </form>
</div>

<!-- Questions List -->
<div style="display: flex; flex-direction: column; gap: 16px;">
    @forelse($questions as $q)
        <div class="glass-card" style="padding: 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                <div style="display: flex; gap: 14px; flex: 1;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex-shrink: 0; border: 1px solid #e0e7ff;">
                        #{{ $q->question_number }}
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="mono" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 2px 8px; border-radius: 6px; border: 1px solid #e0e7ff;">{{ $q->exam_code }}</span>
                            <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 8px;">
                                {{ $q->type }}
                            </span>
                            <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">{{ $q->title }}</h3>
                            <span style="margin-left: auto; font-size: 14px; font-weight: 800; color: #059669; font-family: 'Outfit', sans-serif; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">
                                {{ $q->max_marks }} pts
                            </span>
                        </div>

                        <div style="font-size: 13.5px; color: #334155; margin-top: 10px; line-height: 1.55; white-space: pre-line;">
                            {{ $q->question_text }}
                        </div>

                        @if($q->type === 'MCQ' && is_array($q->options))
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-top: 12px;">
                                @foreach($q->options as $opt)
                                    <div style="font-size: 12px; padding: 6px 10px; background: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#f0fdf4' : '#f8fafc' }}; border: 1px solid {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#86efac' : '#e2e8f0' }}; border-radius: 6px; color: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#15803d' : '#475569' }};">
                                        <strong>{{ $opt['key'] ?? '' }}:</strong> {{ $opt['text'] ?? '' }}
                                        @if($q->correct_answer === ($opt['key'] ?? ''))
                                            <span style="font-size: 10px; font-weight: 700; color: #16a34a; margin-left: 4px;">&check; (CORRECT)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($q->type === 'CODING')
                            <div style="margin-top: 10px; font-size: 12px; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                                <span>Entry: <code class="mono" style="color: #4f46e5; background: #eef2ff; padding: 2px 6px; border-radius: 4px; font-weight: 700;">{{ $q->entry_function ?? 'solve' }}()</code></span>
                                <span>Public Weightage: <strong style="color: #059669;">{{ $q->public_weightage_marks }} pts</strong></span>
                                <span>Hidden Weightage: <strong style="color: #d97706;">{{ $q->hidden_weightage_marks }} pts</strong></span>
                            </div>

                            @if(!empty($q->constraints))
                                <div style="margin-top: 8px; font-size: 11.5px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 4px 10px; color: #92400e; display: inline-flex; align-items: center; gap: 6px;">
                                    <strong>⚙️ Constraints:</strong>
                                    <span>{{ is_array($q->constraints) ? implode(', ', $q->constraints) : $q->constraints }}</span>
                                </div>
                            @endif

                            @php
                                $publicCases = is_array($q->public_test_cases) ? $q->public_test_cases : (json_decode($q->public_test_cases, true) ?? []);
                                if (empty($publicCases) && (!empty($q->sample_input) || !empty($q->sample_output))) {
                                    $publicCases = [[
                                        'input' => $q->sample_input ?? '',
                                        'expected' => $q->sample_output ?? ''
                                    ]];
                                }
                            @endphp

                            @if(!empty($publicCases))
                                <div style="margin-top: 10px; display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 8px;">
                                    @foreach($publicCases as $idx => $tc)
                                        @php
                                            $inDisplay = '';
                                            if (isset($tc['input'])) {
                                                $inDisplay = is_array($tc['input']) ? json_encode($tc['input']) : (string)$tc['input'];
                                            } elseif (isset($tc['nums']) && isset($tc['target'])) {
                                                $numsArr = is_array($tc['nums']) ? $tc['nums'] : [$tc['nums']];
                                                $inDisplay = 'nums = [' . implode(', ', $numsArr) . '], target = ' . $tc['target'];
                                            } elseif (isset($tc['arr'])) {
                                                $arrItems = is_array($tc['arr']) ? $tc['arr'] : [$tc['arr']];
                                                $inDisplay = 'arr = [' . implode(', ', $arrItems) . ']';
                                            } elseif (isset($tc['stdin'])) {
                                                $inDisplay = (string)$tc['stdin'];
                                            } else {
                                                $filtered = array_diff_key($tc, array_flip(['id', 'desc', 'expected', 'expected_output', 'output', 'isHidden']));
                                                $inDisplay = !empty($filtered) ? json_encode($filtered) : '(Standard Input)';
                                            }

                                            $expVal = $tc['expected'] ?? ($tc['expected_output'] ?? ($tc['output'] ?? ''));
                                            $outDisplay = is_array($expVal) ? json_encode($expVal) : (string)$expVal;
                                        @endphp
                                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; font-size: 11.5px;">
                                            <div style="font-weight: 700; color: #4f46e5; margin-bottom: 4px;">📋 Test Case {{ $idx + 1 }} (Public)</div>
                                            <div style="margin-bottom: 4px;">
                                                <strong style="color: #64748b; font-size: 10.5px;">INPUT:</strong>
                                                <code class="mono" style="display: block; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 3px 6px; color: #0f172a; margin-top: 2px;">{{ $inDisplay ?: '(empty)' }}</code>
                                            </div>
                                            <div>
                                                <strong style="color: #64748b; font-size: 10.5px;">EXPECTED OUTPUT:</strong>
                                                <code class="mono" style="display: block; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 3px 6px; color: #15803d; font-weight: 700; margin-top: 2px;">{{ $outDisplay ?: '(empty)' }}</code>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <div style="display: flex; gap: 6px;">
                    <a href="{{ route('questions.edit', $q->id) }}" class="action-btn" style="color: #4f46e5; background: #eef2ff;" title="Edit Question">
                        <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    </a>
                    <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Delete question #{{ $q->question_number }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="action-btn" style="color: #dc2626; background: #fee2e2;" title="Delete Question">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="glass-card" style="text-align: center; padding: 48px; color: #64748b;">
            <i data-lucide="help-circle" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
            <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No questions found matching your filter</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Add Question" to add items to your question bank.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $questions->links() }}
</div>
@endsection
