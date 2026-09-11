@extends('layouts.admin')

@section('title', 'Paper Inspection: ' . ($submission->candidate->full_name ?? $submission->candidate_id))
@section('breadcrumb', 'Submission Paper Evaluation')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('submissions.index', ['exam_code' => $submission->exam_code]) }}" style="color: #4f46e5; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Submissions Grid</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="display: flex; align-items: center; gap: 10px;">
                <span class="mono" style="font-size: 13px; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #e0e7ff;">
                    {{ $submission->exam_code }}
                </span>
                <span style="font-size: 13px; color: #64748b;">
                    Submitted: {{ \Carbon\Carbon::parse($submission->submission_timestamp)->format('d M Y, H:i:s') }}
                </span>
            </div>
            <h1 style="font-size: 24px; font-weight: 800; color: #0f172a; margin-top: 6px; letter-spacing: -0.02em;">
                {{ $submission->candidate ? $submission->candidate->full_name : $submission->candidate_id }}'s Answer Script
            </h1>
        </div>

        <div style="display: flex; align-items: center; gap: 16px;">
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; padding: 10px 24px; border-radius: var(--radius-md); text-align: center; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.1);">
                <div style="font-size: 11px; color: #059669; font-weight: 700; text-transform: uppercase;">Total Score</div>
                <div style="font-size: 26px; font-weight: 800; color: #065f46; font-family: 'Outfit', sans-serif;">
                    {{ number_format($submission->total_score, 1) }} pts
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Score Breakdown & Manual Override Form -->
<div class="glass-card" style="margin-bottom: 32px; background: rgba(255, 255, 255, 0.95);">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="sliders" style="color: #4f46e5; width: 20px; height: 20px;"></i>
            <span>Score Blueprint & Manual Grade Adjustments</span>
        </div>
    </div>

    <form action="{{ route('submissions.updateScores', $submission->id) }}" method="POST">
        @csrf
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 16px; align-items: flex-end; margin-top: 14px;">
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
                <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; padding: 12px;">
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
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-weight: 800; color: #4f46e5; font-size: 15px;">Question #{{ $q->question_number }}</span>
                    <span class="status-pill {{ $q->type === 'MCQ' ? 'active' : ($q->type === 'CODING' ? 'upcoming' : 'completed') }}" style="font-size: 10px; padding: 2px 8px;">
                        {{ $q->type }}
                    </span>
                    <h3 style="font-size: 16px; font-weight: 700; color: #0f172a;">{{ $q->title }}</h3>
                </div>
                <span style="font-size: 13px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 3px 10px; border-radius: 6px;">Max: {{ $q->max_marks }} pts</span>
            </div>

            <div style="font-size: 13.5px; color: #334155; margin-bottom: 16px; line-height: 1.5; white-space: pre-line;">
                {{ $q->question_text }}
            </div>

            @if($q->type === 'MCQ')
                @php
                    $mcqSelected = is_array($ans) ? ($ans['selectedOption'] ?? ($ans['option'] ?? ($ans['answer'] ?? null))) : $ans;
                    $isCorrect = !empty($mcqSelected) && strtoupper(trim((string)$mcqSelected)) === strtoupper(trim((string)$q->correct_answer));
                @endphp
                <div style="background: rgba(248, 250, 252, 0.9); border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 14px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <span style="font-size: 13px; color: #64748b; font-weight: 600;">Candidate Selected Option:</span>
                        <span class="status-pill {{ $isCorrect ? 'active' : 'danger' }}">
                            {{ $isCorrect ? 'CORRECT MATCH (+'.$q->max_marks.' pts)' : 'INCORRECT MATCH (0 pts)' }}
                        </span>
                    </div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a;">
                        Option {{ !empty($mcqSelected) ? $mcqSelected : 'NOT ATTEMPTED' }}
                        @if($q->correct_answer)
                            <span style="font-size: 12px; color: #64748b; font-weight: normal; margin-left: 10px;">
                                (Answer Key: <strong>{{ $q->correct_answer }}</strong>)
                            </span>
                        @endif
                    </div>
                </div>
            @elseif($q->type === 'CODING')
                @php
                    $codingCode = is_array($ans) ? ($ans['codeSolution'] ?? ($ans['code'] ?? ($ans['answer'] ?? null))) : $ans;
                    $codingEval = $evaluation['codingDetails']['Q'.$q->question_number] ?? null;
                @endphp
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: var(--radius-md); padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; font-weight: 700; color: #4f46e5;">Candidate Submitted Code:</span>
                        @if($codingEval)
                            <span style="font-size: 12px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 4px;">
                                Sandbox Score: {{ $codingEval['score'] ?? 0 }} pts
                            </span>
                        @endif
                    </div>
                    <pre class="mono" style="background: #0f172a; padding: 14px; border-radius: 8px; color: #38bdf8; font-size: 12px; overflow-x: auto; max-height: 280px; white-space: pre-wrap;">{{ !empty($codingCode) ? $codingCode : '// No code submitted' }}</pre>
                </div>
            @elseif($q->type === 'PARAGRAPH')
                @php
                    $essayText = is_array($ans) ? ($ans['essayText'] ?? ($ans['text'] ?? ($ans['answer'] ?? null))) : $ans;
                    $essayEval = $evaluation['essayDetails']['Q'.$q->question_number] ?? null;
                @endphp
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: var(--radius-md); padding: 16px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 13px; font-weight: 700; color: #d97706;">Candidate Written Essay Response:</span>
                        @if($essayEval)
                            <span style="font-size: 12px; font-weight: 700; color: #d97706; background: #fef3c7; padding: 2px 8px; border-radius: 4px;">
                                Auto Rubric: {{ $essayEval['score'] ?? 0 }} pts
                            </span>
                        @endif
                    </div>
                    <div style="background: #ffffff; border: 1px solid #fed7aa; padding: 14px; border-radius: 8px; color: #1e293b; font-size: 13px; line-height: 1.6; white-space: pre-wrap;">
                        {{ !empty($essayText) ? $essayText : 'No paragraph submitted.' }}
                    </div>
                </div>
            @endif
        </div>
    @empty
        <div class="glass-card" style="text-align: center; padding: 48px; color: #64748b;">
            <p style="margin: 0; font-size: 14px; font-weight: 600;">Exam questions Blueprint could not be matched for this paper.</p>
        </div>
    @endforelse
</div>
@endsection
