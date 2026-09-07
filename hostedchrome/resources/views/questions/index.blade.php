@extends('layouts.admin')

@section('title', 'Question Bank')
@section('breadcrumb', 'Question Bank')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Question Bank & Blueprint Repository</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Manage MCQs, multi-language coding challenges, and paragraph rubrics across exams.
            </p>
        </div>
        <div style="display: flex; gap: 10px; align-items: center;">
            <a href="{{ route('ai.generator') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);">
                <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                <span>✨ AI Exam Generator</span>
            </a>
            <a href="{{ route('questions.create') }}" class="quick-action-btn secondary">
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

        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if($examCode || $type)
            <a href="{{ route('questions.index') }}" class="quick-action-btn secondary" style="color: #f87171;">Clear Filters</a>
        @endif
    </form>
</div>

<!-- Questions List -->
<div style="display: flex; flex-direction: column; gap: 16px;">
    @forelse($questions as $q)
        <div class="glass-card" style="padding: 20px;">
            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                <div style="display: flex; gap: 14px; flex: 1;">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 15px; flex-shrink: 0;">
                        #{{ $q->question_number }}
                    </div>
                    <div style="flex: 1;">
                        <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                            <span class="mono" style="font-size: 11px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 8px; border-radius: 4px;">{{ $q->exam_code }}</span>
                            <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 8px;">
                                {{ $q->type }}
                            </span>
                            <h3 style="font-size: 16px; font-weight: 700; color: #fff;">{{ $q->title }}</h3>
                            <span style="margin-left: auto; font-size: 14px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif;">
                                {{ $q->max_marks }} pts
                            </span>
                        </div>

                        <div style="font-size: 13px; color: #cbd5e1; margin-top: 10px; line-height: 1.5; white-space: pre-line;">
                            {{ Str::limit($q->question_text, 180) }}
                        </div>

                        @if($q->type === 'MCQ' && is_array($q->options))
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-top: 12px;">
                                @foreach($q->options as $opt)
                                    <div style="font-size: 12px; padding: 6px 10px; background: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ ($q->correct_answer === ($opt['key'] ?? '')) ? 'rgba(16, 185, 129, 0.4)' : 'rgba(255,255,255,0.05)' }}; border-radius: 6px; color: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#34d399' : '#94a3b8' }};">
                                        <strong>{{ $opt['key'] ?? '' }}:</strong> {{ $opt['text'] ?? '' }}
                                        @if($q->correct_answer === ($opt['key'] ?? ''))
                                            <span style="font-size: 10px; font-weight: 700; margin-left: 4px;">&check; (CORRECT)</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @elseif($q->type === 'CODING')
                            <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted); display: flex; gap: 16px; flex-wrap: wrap;">
                                <span>Entry: <code class="mono" style="color: #818cf8;">{{ $q->entry_function ?? 'solve' }}()</code></span>
                                <span>Public Cases: <strong style="color: #34d399;">{{ is_array($q->public_test_cases) ? count($q->public_test_cases) : 0 }}</strong> ({{ $q->public_weightage_marks }} pts)</span>
                                <span>Hidden Cases: <strong style="color: #fbbf24;">{{ is_array($q->hidden_test_cases) ? count($q->hidden_test_cases) : 0 }}</strong> ({{ $q->hidden_weightage_marks }} pts)</span>
                            </div>
                        @endif
                    </div>
                </div>

                <div style="display: flex; gap: 6px;">
                    <a href="{{ route('questions.edit', $q->id) }}" class="quick-action-btn secondary" style="padding: 6px 10px; font-size: 12px;">
                        <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    </a>
                    <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Delete question #{{ $q->question_number }}?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="quick-action-btn danger" style="padding: 6px 10px; font-size: 12px;">
                            <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="glass-card" style="text-align: center; padding: 48px; color: var(--text-muted);">
            <i data-lucide="help-circle" style="width: 36px; height: 36px; margin-bottom: 8px;"></i>
            <p style="font-size: 15px; font-weight: 600; color: #fff;">No questions found matching your filter</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Add New Question" to add items to your question bank.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $questions->links() }}
</div>
@endsection
