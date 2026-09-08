@extends('layouts.admin')

@section('title', 'Exam Overview: ' . $exam->exam_code)
@section('breadcrumb', 'Exam: ' . $exam->exam_code)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('exams.index') }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Exams Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mono" style="font-size: 13px; font-weight: 800; color: #4f46e5; background: #eef2ff; padding: 4px 10px; border-radius: 6px; border: 1px solid #e0e7ff;">{{ $exam->exam_code }}</span>
                <span class="status-pill {{ strtolower($exam->status) }}">{{ $exam->status }}</span>
                @if($exam->is_results_published)
                    <span class="status-pill active" style="font-size: 11px;">RESULTS PUBLISHED</span>
                @endif
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 8px; letter-spacing: -0.02em;">{{ $exam->title }}</h1>
        </div>

        @php
            $canCreateExams = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canCreateExams()) : false;
            $canSetQuestions = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canSetQuestions()) : false;
            $canViewResults = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canViewResults()) : false;
        @endphp

        <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
            @if($canViewResults)
                <a href="{{ route('monitoring.index', ['exam_code' => $exam->exam_code]) }}" class="btn btn-secondary">
                    <i data-lucide="radio" style="width: 16px; height: 16px; color: #059669;"></i>
                    <span>Live Radar</span>
                </a>
                <form action="{{ route('exams.togglePublish', $exam->exam_code) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i data-lucide="{{ $exam->is_results_published ? 'eye-off' : 'send' }}" style="width: 16px; height: 16px;"></i>
                        <span>{{ $exam->is_results_published ? 'Unpublish Results' : 'Publish Results' }}</span>
                    </button>
                </form>
            @endif

            @if($canSetQuestions)
                <a href="{{ route('ai.generator', ['exam_code' => $exam->exam_code]) }}" class="btn btn-primary" style="background: linear-gradient(135deg, #4f46e5, #06b6d4); box-shadow: 0 4px 14px rgba(6, 182, 212, 0.35);">
                    <i data-lucide="sparkles" style="width: 16px; height: 16px;"></i>
                    <span>✨ AI Generate Paper</span>
                </a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code]) }}" class="btn btn-secondary">
                    <i data-lucide="plus" style="width: 16px; height: 16px;"></i>
                    <span>Add Question</span>
                </a>
            @endif

            @if($canCreateExams)
                <a href="{{ route('exams.edit', $exam->exam_code) }}" class="btn btn-secondary">
                    <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    <span>Edit Exam</span>
                </a>
            @endif
        </div>
    </div>
</div>

<!-- Metrics Bar -->
<div class="metrics-grid" style="margin-bottom: 28px;">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #4f46e5;">
        <div class="metric-icon-box" style="color: #4f46e5; background: #eef2ff;">
            <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $questionsCount }}</div>
            <div class="metric-label">Questions in Paper</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #059669;">
        <div class="metric-icon-box" style="color: #059669; background: #ecfdf5;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $exam->duration_minutes }}m</div>
            <div class="metric-label">Duration ({{ $exam->total_marks }} Marks)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #d97706;">
        <div class="metric-icon-box" style="color: #d97706; background: #fffbeb;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $submissionsCount }}</div>
            <div class="metric-label">Submissions (Avg: {{ number_format($avgScore, 1) }} pts)</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #dc2626;">
        <div class="metric-icon-box" style="color: #dc2626; background: #fef2f2;">
            <i data-lucide="shield-alert" style="width: 24px; height: 24px;"></i>
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
                <i data-lucide="list-checks" style="color: #4f46e5; width: 22px; height: 22px;"></i>
                <span>Question Bank & Marking Blueprint ({{ $questionsCount }} Items)</span>
            </div>
            <p style="color: #64748b; font-size: 12px; margin-top: 2px;">
                MCQs, Weighted Coding Challenges, and Paragraph / Essay Rubrics
            </p>
        </div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @if($canSetQuestions)
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'MCQ']) }}" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">+ Add MCQ</a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'CODING']) }}" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">+ Add Coding</a>
                <a href="{{ route('questions.create', ['exam_code' => $exam->exam_code, 'type' => 'PARAGRAPH']) }}" class="btn btn-secondary" style="font-size: 12px; padding: 6px 12px;">+ Add Essay/Rubric</a>
            @endif
        </div>
    </div>

    @if(!$canSetQuestions)
        <div style="background: #fef2f2; border: 1px dashed #fca5a5; border-radius: 10px; padding: 14px 18px; color: #b91c1c; font-size: 13px; display: flex; align-items: center; gap: 10px; margin-bottom: 16px;">
            <i data-lucide="lock" style="width: 18px; height: 18px; flex-shrink: 0; color: #dc2626;"></i>
            <span><strong>Confidential Examination:</strong> Paper questions, test cases, and answer keys are protected. Question authoring permissions are restricted by your Principal.</span>
        </div>
    @endif

    <div style="display: flex; flex-direction: column; gap: 14px; margin-top: 14px;">
        @if($canSetQuestions)
            @forelse($exam->questions as $q)
                <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px; display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; transition: all 0.2s ease;">
                    <div style="display: flex; gap: 14px; flex: 1;">
                        <div style="width: 36px; height: 36px; border-radius: 10px; background: #eef2ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 14px; flex-shrink: 0; border: 1px solid #e0e7ff;">
                            #{{ $q->question_number }}
                        </div>
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 8px;">
                                    {{ $q->type }}
                                </span>
                                <h4 style="font-size: 15px; font-weight: 700; color: #0f172a;">{{ $q->title }}</h4>
                                <span style="margin-left: auto; font-size: 13px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">
                                    {{ $q->max_marks }} Marks
                                </span>
                            </div>

                            <div style="font-size: 13px; color: #334155; margin-top: 8px; line-height: 1.5; white-space: pre-line;">
                                {{ Str::limit($q->question_text, 140) }}
                            </div>

                            @if($q->type === 'MCQ' && is_array($q->options))
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 8px; margin-top: 12px;">
                                    @foreach($q->options as $opt)
                                        <div style="font-size: 12px; padding: 6px 10px; background: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#f0fdf4' : '#ffffff' }}; border: 1px solid {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#86efac' : '#e2e8f0' }}; border-radius: 6px; color: {{ ($q->correct_answer === ($opt['key'] ?? '')) ? '#15803d' : '#475569' }};">
                                            <strong>{{ $opt['key'] ?? '' }}:</strong> {{ $opt['text'] ?? '' }}
                                            @if($q->correct_answer === ($opt['key'] ?? ''))
                                                <span style="font-size: 10px; font-weight: 700; color: #16a34a; margin-left: 4px;">✓ (CORRECT)</span>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            @elseif($q->type === 'CODING')
                                <div style="margin-top: 10px; font-size: 12px; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap;">
                                    <span>Entry Function: <code class="mono" style="color: #4f46e5; background: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{ $q->entry_function ?? 'solve' }}()</code></span>
                                    <span>Public Weightage: <strong style="color: #059669;">{{ $q->public_weightage_marks }} pts</strong></span>
                                    <span>Hidden Weightage: <strong style="color: #d97706;">{{ $q->hidden_weightage_marks }} pts</strong></span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div style="display: flex; gap: 6px;">
                        <a href="{{ route('questions.edit', $q->id) }}" class="action-btn" style="color: #4f46e5; background: #eef2ff;" title="Edit Question">
                            <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                        </a>
                        <form action="{{ route('questions.destroy', $q->id) }}" method="POST" onsubmit="return confirm('Remove question #{{ $q->question_number }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="action-btn" style="color: #dc2626; background: #fee2e2;" title="Delete Question">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 36px; color: #64748b; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
                    <i data-lucide="help-circle" style="width: 32px; height: 32px; margin-bottom: 8px; color: #94a3b8;"></i>
                    <p style="margin: 0; font-size: 14px; font-weight: 600;">No questions added to this examination yet.</p>
                </div>
            @endforelse
        @else
            <div style="text-align: center; padding: 28px; color: #64748b; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                <i data-lucide="shield" style="width: 32px; height: 32px; color: #4f46e5; margin-bottom: 8px;"></i>
                <p style="color: #0f172a; font-weight: 700; margin: 0;">{{ $questionsCount }} Questions Configured in this Exam</p>
                <p style="font-size: 12px; margin-top: 4px; color: #64748b;">To view or edit question content, ask your Principal to enable "Set Questions & AI Generator" permission for your faculty profile.</p>
            </div>
        @endif
    </div>
</div>

<!-- Candidate Submissions for This Exam -->
@if($canViewResults)
<div class="glass-card">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="check-circle-2" style="color: #059669; width: 22px; height: 22px;"></i>
            <span>Student Submissions & Paper Grading ({{ $submissionsCount }})</span>
        </div>
        <a href="{{ route('submissions.index', ['exam_code' => $exam->exam_code]) }}" style="color: #4f46e5; font-size: 13px; text-decoration: none; font-weight: 600;">View Full Grading Grid &rarr;</a>
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
                            <div style="font-weight: 700; color: #0f172a;">{{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}</div>
                            <div class="mono" style="font-size: 11px; color: #64748b;">{{ $sub->candidate_id }}</div>
                        </td>
                        <td>{{ number_format($sub->mcq_score, 1) }} pts</td>
                        <td>{{ number_format($sub->coding_public_score, 1) }} / {{ number_format($sub->coding_hidden_score, 1) }} pts</td>
                        <td>{{ number_format($sub->essay_score, 1) }} pts</td>
                        <td><strong style="font-size: 15px; color: #059669; font-family: 'Outfit', sans-serif;">{{ number_format($sub->total_score, 1) }} pts</strong></td>
                        <td style="font-size: 12px; color: #64748b;">{{ \Carbon\Carbon::parse($sub->submission_timestamp)->format('d M, H:i') }}</td>
                        <td style="text-align: right;">
                            <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-secondary" style="font-size: 11px; padding: 4px 10px;">
                                Inspect Paper &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 28px; color: #64748b;">
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
