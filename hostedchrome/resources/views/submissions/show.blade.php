@extends('layouts.admin')

@section('title', 'Paper Inspection: ' . ($submission->candidate->full_name ?? $submission->candidate_id))
@section('breadcrumb', 'Submission Paper Evaluation')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('submissions.index', ['exam_code' => $submission->exam_code]) }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Submissions Grid</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mono" style="font-size: 13px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 3px 8px; border-radius: 4px;">
                    {{ $submission->exam_code }}
                </span>
                <span style="font-size: 13px; color: var(--text-muted);">
                    Submitted: {{ \Carbon\Carbon::parse($submission->submission_timestamp)->format('d M Y, H:i:s') }}
                </span>
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #fff; margin-top: 6px;">
                {{ $submission->candidate ? $submission->candidate->full_name : $submission->candidate_id }}'s Answer Script
            </h1>
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.4); padding: 10px 20px; border-radius: var(--radius-md); text-align: center;">
                <div style="font-size: 11px; color: #34d399; font-weight: 700; text-transform: uppercase;">Total Score</div>
                <div style="font-size: 26px; font-weight: 800; color: #fff; font-family: 'Outfit', sans-serif;">
                    {{ number_format($submission->total_score, 1) }} pts
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Score Breakdown & Manual Override Form -->
<div class="glass-card" style="margin-bottom: 32px; background: linear-gradient(135deg, rgba(15, 23, 42, 0.9), rgba(30, 41, 59, 0.8));">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="sliders" style="color: #6366f1; width: 20px; height: 20px;"></i>
            <span>Score Blueprint & Manual Grade Adjustments</span>
        </div>
    </div>

    <form action="{{ route('submissions.updateScores', $submission->id) }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; align-items: flex-end;">
            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">MCQ Score</label>
                <input type="number" step="0.5" name="mcq_score" class="form-control" value="{{ old('mcq_score', $submission->mcq_score) }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Coding Public Cases</label>
                <input type="number" step="0.5" name="coding_public_score" class="form-control" value="{{ old('coding_public_score', $submission->coding_public_score) }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Coding Hidden Cases</label>
                <input type="number" step="0.5" name="coding_hidden_score" class="form-control" value="{{ old('coding_hidden_score', $submission->coding_hidden_score) }}" required>
            </div>

            <div class="form-group" style="margin-bottom: 0;">
                <label class="form-label">Essay / Rubric Score</label>
                <input type="number" step="0.5" name="essay_score" class="form-control" value="{{ old('essay_score', $submission->essay_score) }}" required>
            </div>

            <div>
                <button type="submit" class="quick-action-btn" style="width: 100%; justify-content: center; padding: 12px;">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Update Marks</span>
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Detailed Question Answers Breakdown -->
<div style="display: flex; flex-direction: column; gap: 20px;">
    @forelse($questions as $num => $q)
        @php
            $ans = $answers[$num] ?? ($answers[(string)$num] ?? ($answers[$q->id] ?? null));
        @endphp
        <div class="glass-card">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid var(--border-color); padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 800; color: #818cf8; font-size: 15px;">Question #{{ $q->question_number }}</span>
                    <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 6px;">
                        {{ $q->type }}
                    </span>
                    <h3 style="font-size: 16px; font-weight: 700; color: #fff;">{{ $q->title }}</h3>
                </div>
                <span style="font-size: 13px; font-weight: 700; color: #34d399;">Max: {{ $q->max_marks }} pts</span>
            </div>

            <div style="font-size: 13px; color: #cbd5e1; margin-bottom: 16px; line-height: 1.5; white-space: pre-line;">
                {{ $q->question_text }}
            </div>

            @if($q->type === 'MCQ')
                @php
                    $isCorrect = (string)$ans === (string)$q->correct_answer;
                @endphp
                <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: var(--text-muted); font-weight: 600;">Candidate Selected Option:</span>
                        <span class="status-pill {{ $isCorrect ? 'active' : 'danger' }}">
                            {{ $isCorrect ? 'CORRECT MATCH (+'.$q->max_marks.' pts)' : 'INCORRECT MATCH (0 pts)' }}
                        </span>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #fff;">
                        Option {{ $ans ?? 'NOT ATTEMPTED' }}
                        @if($q->correct_answer)
                            <span style="font-size: 12px; color: var(--text-muted); font-weight: normal; margin-left: 10px;">
                                (Answer Key: <strong>{{ $q->correct_answer }}</strong>)
                            </span>
                        @endif
                    </div>
                </div>
            @elseif($q->type === 'CODING')
                <div style="background: rgba(0, 0, 0, 0.35); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                    <div style="font-size: 13px; font-weight: 700; color: #818cf8; margin-bottom: 8px;">
                        Candidate Submitted Code:
                    </div>
                    <pre class="mono" style="background: rgba(10, 15, 29, 0.9); padding: 14px; border-radius: 8px; color: #38bdf8; font-size: 12px; overflow-x: auto; max-height: 280px; white-space: pre-wrap;">{{ is_array($ans) ? json_encode($ans, JSON_PRETTY_PRINT) : ($ans ?? '// No code submitted') }}</pre>
                </div>
            @elseif($q->type === 'PARAGRAPH')
                <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 16px;">
                    <div style="font-size: 13px; font-weight: 700; color: #fbbf24; margin-bottom: 8px;">
                        Candidate Written Essay Response:
                    </div>
                    <div style="background: rgba(10, 15, 29, 0.8); padding: 14px; border-radius: 8px; color: #fff; font-size: 13px; line-height: 1.6; white-space: pre-wrap;">
                        {{ $ans ?? 'No paragraph submitted.' }}
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="glass-card" style="text-align: center; padding: 48px; color: var(--text-muted);">
            <p>Exam questions Blueprint could not be matched for this paper.</p>
        </div>
    @endforelse
</div>
@endsection
