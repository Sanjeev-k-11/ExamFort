@extends('layouts.admin')

@section('title', 'Submissions & Paper Evaluation')
@section('breadcrumb', 'Submissions & Paper Grading')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Student Submissions & Evaluation</h1>
            <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                Review submitted answer papers, automated test case verdicts, rubric scoring breakdowns, and grade overrides.
            </p>
        </div>
    </div>
</div>

<!-- Filters Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('submissions.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 240px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: #94a3b8;"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search candidate name, ID, email..." value="{{ request('search') }}">
        </div>

        <select name="exam_code" class="form-control" style="width: auto; min-width: 200px;" onchange="this.form.submit()">
            <option value="">All Examinations</option>
            @foreach($exams as $ex)
                <option value="{{ $ex->exam_code }}" {{ $examCode == $ex->exam_code ? 'selected' : '' }}>
                    {{ $ex->exam_code }} - {{ Str::limit($ex->title, 26) }}
                </option>
            @endforeach
        </select>

        <select name="attempt_type" class="form-control" style="width: auto; min-width: 160px;" onchange="this.form.submit()">
            <option value="">All Attempt Types</option>
            <option value="initial" {{ request('attempt_type') === 'initial' ? 'selected' : '' }}>1st Attempts Only</option>
            <option value="reattempt" {{ request('attempt_type') === 'reattempt' ? 'selected' : '' }}>Reattempts Only</option>
        </select>

        <button type="submit" class="btn btn-secondary">Filter</button>
        @if(request('search') || $examCode || request('attempt_type'))
            <a href="{{ route('submissions.index') }}" class="btn btn-secondary" style="color: #dc2626; border-color: #fecaca;">Clear</a>
        @endif
    </form>
</div>

<!-- Submissions Table -->
<div class="glass-card">
    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Candidate</th>
                    <th>Exam Code</th>
                    <th>Attempt</th>
                    <th>Reattempt Reason</th>
                    <th>MCQ Score</th>
                    <th>Coding (Pub/Hid)</th>
                    <th>Essay Score</th>
                    <th>Total Score</th>
                    <th>Submitted At</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($submissions as $sub)
                    @php
                        $attNum = $sub->attempt_number ?: 1;
                        $isReattempt = $attNum > 1;
                    @endphp
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: {{ $isReattempt ? 'linear-gradient(135deg, #f59e0b, #ef4444)' : 'linear-gradient(135deg, #4f46e5, #06b6d4)' }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);">
                                    {{ substr($sub->candidate->full_name ?? $sub->candidate_id, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}</div>
                                    <div class="mono" style="font-size: 11px; color: #64748b;">{{ $sub->candidate_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="mono" style="font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; font-size: 11px; border: 1px solid #e0e7ff;">
                                {{ $sub->exam_code }}
                            </span>
                        </td>
                        <td>
                            @if($isReattempt)
                                <span style="font-size: 11px; font-weight: 800; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 6px;">
                                    🔄 Reattempt #{{ $attNum }}
                                </span>
                            @else
                                <span style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; border: 1px solid #e0e7ff; padding: 2px 8px; border-radius: 6px;">
                                    Attempt #1
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($sub->reattempt_reason)
                                <div style="font-size: 11.5px; color: #475569; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 6px; padding: 3px 8px; max-width: 220px;" title="{{ $sub->reattempt_reason }}">
                                    {{ Str::limit($sub->reattempt_reason, 30) }}
                                </div>
                            @else
                                <span style="font-size: 11px; color: #94a3b8;">—</span>
                            @endif
                        </td>
                        <td style="font-weight: 600; color: #334155;">{{ number_format($sub->mcq_score, 1) }} pts</td>
                        <td style="font-weight: 600; color: #059669;">{{ number_format($sub->coding_public_score, 1) }} / {{ number_format($sub->coding_hidden_score, 1) }} pts</td>
                        <td style="font-weight: 600; color: #4338ca;">{{ number_format($sub->essay_score, 1) }} pts</td>
                        <td>
                            <strong style="font-size: 15px; color: #059669; font-family: 'Outfit', sans-serif;">
                                {{ number_format($sub->total_score, 1) }} pts
                            </strong>
                        </td>
                        <td style="font-size: 12px; color: #64748b;">
                            {{ \Carbon\Carbon::parse($sub->submission_timestamp)->format('d M Y, H:i') }}
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-primary" style="font-size: 11px; padding: 6px 12px;">
                                <i data-lucide="eye" style="width: 13px; height: 13px;"></i>
                                <span>Inspect</span>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" style="text-align: center; padding: 48px; color: #64748b;">
                            <i data-lucide="inbox" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
                            <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No candidate submissions found</p>
                            <p style="font-size: 13px; margin-top: 4px;">Student assessment submissions will appear here once submitted.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="margin-top: 24px;">
    {{ $submissions->links() }}
</div>
@endsection
