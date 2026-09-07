@extends('layouts.admin')

@section('title', 'Exam Overview: ' . $exam->exam_code)
@section('breadcrumb', 'Exam: ' . $exam->exam_code)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('exams.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Exams Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mono" style="font-size: 14px; font-weight: 800; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 4px 10px; border-radius: 6px;">{{ $exam->exam_code }}</span>
                <span class="status-pill {{ strtolower($exam->status) }}">{{ $exam->status }}</span>
                @if($exam->is_results_published)
                    <span class="status-pill active" style="font-size: 11px;">RESULTS PUBLISHED</span>
                @endif
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #fff; margin-top: 8px;">{{ $exam->title }}</h1>
        </div>

        @php
            $canCreateExams = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canCreateExams()) : false;
            $canSetQuestions = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canSetQuestions()) : false;
            $canViewResults = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canViewResults()) : false;
        @endphp

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if($canViewResults)
                <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary">
                    <i data-lucide="radio" style="width: 16px; height: 16px; color: #10b981;"></i>
                    <span>Live Radar</span>
                </a>
                <form action="{{ route('exams.togglePublish', $exam->exam_code) }}" method="POST">
                    @csrf
                    <button type="submit" class="quick-action-btn secondary">
                        <i data-lucide="{{ $exam->is_results_published ? 'eye-off' : 'send' }}" style="width: 16px; height: 16px;"></i>
                        <span>{{ $exam->is_results_published ? 'Unpublish Results' : 'Publish Results' }}</span>
                    </button>
                </form>
            @endif

            @if($canSetQuestions)
                <a href="{{ route('ai.generator', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.4);">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                    <span>✨ AI Generate Paper</span>
                </a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code]) }}" class="quick-action-btn secondary">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    <span>Add Question</span>
                </a>
            @endif

            @if($canCreateExams)
                <a href="{{ route('exams.edit', $exam->exam_code) }}" class="quick-action-btn secondary">
                    <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    <span>Edit Exam</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Metrics Bar -->
<div class="metrics-grid" style="margin-bottom: 28px;">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #818cf8;">
        <div class="metric-icon-box">
            <i data-lucide="help-circle" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $questionsCount }}</div>
            <div class="metric-label">Questions in Paper</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #34d399;">
        <div class="metric-icon-box" style="color: #34d399;">
            <i data-lucide="clock" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $exam->duration_minutes }}m</div>
            <div class="metric-label">Total Duration ({{ $exam->total_marks }} Marks)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #fbbf24;">
        <div class="metric-icon-box" style="color: #fbbf24;">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $submissionsCount }}</div>
            <div class="metric-label">Submissions (Avg: {{ number_format($avgScore, 1) }} pts)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #f87171;">
        <div class="metric-icon-box" style="color: #f87171;">
            <i data-lucide="shield-alert" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $exam->violations->count() }}</div>
            <div class="metric-label">Violations Logged</div>
        </div>
    </div>
</div>

<!-- Questions Bank for This Exam -->
<div class="glass-card" style="margin-bottom: 32px;">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="list-checks" style="color: #6366f1; width: 22px; height: 22px;"></i>
                <span>Question Bank & Marking Blueprint ({{ $questionsCount }} Items)</span>
            </div>
            <p style="color: var(--text-muted); font-size: 12px; margin-top: 2px;">
                MCQs, Weighted Coding Challenges, and Paragraph / Essay Rubrics
            </p>
        </div>
        <div style="display: flex; gap: 8px;">
            @if($canSetQuestions)
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'MCQ']) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">+ Add MCQ</a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'CODING']) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">+ Add Coding</a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'PARAGRAPH']) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">+ Add Essay/Rubric</a>
            @endif
        </div>
    </div>

    @if(!$canSetQuestions)
        <div style="background: rgba(239, 68, 68, 0.08); border: 1px dashed rgba(239, 68, 68, 0.3); border-radius: 8px; padding: 14px 18px; color: #fca5a5; font-size: 13px; display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <i data-lucide="lock" style="width: 18px; height: 18px; flex-shrink: 0; color: #f87171;"></i>
            <span><strong>Confidential Examination:</strong> Paper questions, test cases, and answer keys are protected. Question authoring and blueprint modification permissions are restricted by your Principal.</span>
        </div>
    @endif

    <div style="display: flex; flex-direction: column; gap: 14px;">
        @if($canSetQuestions)
            @forelse($exam->questions as $q)
                <div style="background: rgba(15, 23, 42, 0.7); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 18px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;">
                    <div style="display: flex; gap: 14px; flex: 1;">
                        <div style="width: 36px; height: 36px; border-radius: 8px; background: rgba(99, 102, 241, 0.2); color: #818cf8; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0;">
                            #{{ $q->question_number }}
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 6px;">
                                    {{ $q->type }}
                                </span>
                                <h4 style="font-size: 15px; font-weight: 700; color: #fff;">{{ $q->title }}</h4>
                                <span style="margin-left: auto; font-size: 13px; font-weight: 700; color: #34d399;">
                                    {{ $q->max_marks }} Marks
                                </span>
                            </div>

                            <div style="font-size: 13px; color: #cbd5e1; margin-top: 8px; line-height: 1.5; white-space: pre-line;">
                                {{ Str::limit($q->question_text, 140) }}
                            </div>

                            @if($q->type === 'MCQ' && is_array($q->options))
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: 10px;">
                                    @foreach($q->options as $opt)
                                        <div style="font-size: 12px; padding: 6px 10px; background: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? 'rgba(16, 185, 129, 0.15)' : 'rgba(255,255,255,0.02)' }}; border: 1px solid {{ ($q->correct_answer === ($opt['key'] ?? '')) ? 'rgba(16, 185, 129, 0.4)' : 'rgba(255,255,255,0.05)' }}; border-radius: 6px; color: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#34d399' : '#94a3b8' }};">
                                            <strong>{{ $opt['key'] ?? '' }}:</strong> {{ $opt['text'] ?? '' }}
                                            @if($q->correct_answer === ($opt['key'] ?? ''))
                                                <span style="font-size: 10px; font-weight: 700; margin-left: 4px;">(CORRECT)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($q->type === 'CODING')
                                <div style="margin-top: 10px; font-size: 12px; color: var(--text-muted); display: flex; gap: 16px;">
                                    <span>Entry Function: <code class="mono" style="color: #818cf8;">{{ $q->entry_function ?? 'solve' }}()</code></span>
                                    <span>Public Weightage: <strong style="color: #34d399;">{{ $q->public_weightage_marks }} pts</strong></span>
                                    <span>Hidden Weightage: <strong style="color: #fbbf24;">{{ $q->hidden_weightage_marks }} pts</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('questions.edit', $q->id) }}" class="quick-action-btn secondary" style="padding: 6px 10px; font-size: 12px;">
                            <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                        </a>
                        <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Remove question #{{ $q->question_number }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="quick-action-btn danger" style="padding: 6px 10px; font-size: 12px;">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 32px; color: var(--text-muted);">
                    <i data-lucide="help-circle" style="width: 32px; height: 32px; margin-bottom: 8px;"></i>
                    <p>No questions added to this examination yet.</p>
                </div>
            @endforelse
        @else
            <div style="text-align: center; padding: 28px; color: var(--text-muted); background: rgba(0,0,0,0.15); border-radius: var(--radius-md);">
                <i data-lucide="shield" style="width: 32px; height: 32px; color: #6366f1; margin-bottom: 8px;"></i>
                <p style="color: #fff; font-weight: 600;">{{ $questionsCount }} Questions Configured in this Exam</p>
                <p style="font-size: 12px; margin-top: 4px;">To view or edit question content, ask your Principal to enable "Set Questions & AI Generator" permission for your faculty profile.</p>
            </div>
        @endif
    </div>
</div>

<!-- Candidate Submissions for This Exam -->
@if($canViewResults)
<div class="glass-card">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="check-circle-2" style="color: #10b981; width: 22px; height: 22px;"></i>
            <span>Student Submissions & Paper Grading ({{ $submissionsCount }})</span>
        </div>
        <a href="{{ route('submissions.index', ['exam_code' => $exam->exam_code]) }}" style="color: #818cf8; font-size: 13px; text-decoration: none; font-weight: 600;">View Full Grading Grid &rarr;</a>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>MCQ Score</th>
                    <th>Coding (Public / Hidden)</th>
                    <th>Essay Score</th>
                    <th>Total Score</th>
                    <th>Submitted At</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exam->submissions as $sub)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #fff;">{{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $sub->candidate_id }}</div>
                        </td>
                        <td>{{ number_format($sub->mcq_score, 1) }} pts</td>
                        <td>{{ number_format($sub->coding_public_score, 1) }} / {{ number_format($sub->coding_hidden_score, 1) }} pts</td>
                        <td>{{ number_format($sub->essay_score, 1) }} pts</td>
                        <td><strong style="font-size: 15px; color: #34d399; font-family: 'Outfit', sans-serif;">{{ number_format($sub->total_score, 1) }} pts</strong></td>
                        <td style="font-size: 12px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($sub->submission_timestamp)->format('d M, H:i') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('submissions.show', $sub->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                Inspect Paper &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No submissions received yet for this exam.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
