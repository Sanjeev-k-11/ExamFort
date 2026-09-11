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
                <button type="button" class="btn btn-secondary" onclick="openRescheduleModal()" style="border-color: #cbd5e1; background: #ffffff;">
                    <i data-lucide="calendar-clock" style="width: 15px; height: 15px; color: #4f46e5;"></i>
                    <span>📅 Reschedule</span>
                </button>
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
    <a href="#questions-section" class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #4f46e5; text-decoration: none; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 16px;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px -8px rgba(99, 102, 241, 0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
        <div class="metric-icon-box" style="color: #4f46e5; background: #eef2ff;">
            <i data-lucide="help-circle" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $questionsCount }}</div>
            <div class="metric-label" style="display: flex; align-items: center; gap: 4px;">
                <span>Questions in Paper</span>
                <i data-lucide="arrow-down-right" style="width: 14px; height: 14px; opacity: 0.7;"></i>
            </div>
        </div>
    </a>

    <a href="javascript:void(0)" onclick="openRescheduleModal()" class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #059669; text-decoration: none; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 16px;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px -8px rgba(16, 185, 129, 0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';" title="Click to Reschedule Exam Date & Time">
        <div class="metric-icon-box" style="color: #059669; background: #ecfdf5;">
            <i data-lucide="clock" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $exam->duration_minutes }}m</div>
            <div class="metric-label" style="display: flex; align-items: center; gap: 4px;">
                <span>Duration ({{ $exam->total_marks }} Marks)</span>
                <i data-lucide="calendar-clock" style="width: 13px; height: 13px; opacity: 0.8; color: #059669;"></i>
            </div>
        </div>
    </a>

    <a href="#submissions-section" class="metric-card" style="--accent-gradient: linear-gradient(90deg, #f59e0b, #ec4899); --accent-color: #d97706; text-decoration: none; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 16px;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px -8px rgba(245, 158, 11, 0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
        <div class="metric-icon-box" style="color: #d97706; background: #fffbeb;">
            <i data-lucide="users" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $submissionsCount }}</div>
            <div class="metric-label" style="display: flex; align-items: center; gap: 4px;">
                <span>Submissions (Avg: {{ number_format($avgScore, 1) }} pts)</span>
                <i data-lucide="arrow-down-right" style="width: 14px; height: 14px; opacity: 0.7;"></i>
            </div>
        </div>
    </a>

    <a href="{{ route('violations.index', ['exam_code' => $exam->exam_code]) }}" class="metric-card" style="--accent-gradient: linear-gradient(90deg, #ef4444, #f97316); --accent-color: #dc2626; text-decoration: none; cursor: pointer; transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1); display: flex; align-items: center; gap: 16px;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 12px 24px -8px rgba(239, 68, 68, 0.25)';" onmouseout="this.style.transform='none'; this.style.boxShadow='none';">
        <div class="metric-icon-box" style="color: #dc2626; background: #fef2f2;">
            <i data-lucide="shield-alert" style="width: 24px; height: 24px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $exam->violations->count() }}</div>
            <div class="metric-label" style="display: flex; align-items: center; gap: 4px;">
                <span>Violations Logged</span>
                <i data-lucide="external-link" style="width: 13px; height: 13px; opacity: 0.7;"></i>
            </div>
        </div>
    </a>
</div>

<!-- Questions Bank for This Exam -->
<div id="questions-section" class="glass-card" style="margin-bottom: 32px; scroll-margin-top: 24px;">
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

                            <div style="font-size: 13.5px; color: #334155; margin-top: 8px; line-height: 1.55; white-space: pre-line;">
                                {{ $q->question_text }}
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
                                <div style="margin-top: 10px; font-size: 12px; color: #64748b; display: flex; gap: 16px; flex-wrap: wrap; align-items: center;">
                                    <span>Entry Function: <code class="mono" style="color: #4f46e5; background: #f1f5f9; padding: 2px 6px; border-radius: 4px; font-weight: 700;">{{ $q->entry_function ?? 'solve' }}()</code></span>
                                    <span>Public Weightage: <strong style="color: #059669;">{{ $q->public_weightage_marks ?? 10 }} pts</strong></span>
                                    <span>Hidden Weightage: <strong style="color: #d97706;">{{ $q->hidden_weightage_marks ?? 40 }} pts</strong></span>
                                </div>

                                @if(!empty($q->constraints))
                                    <div style="margin-top: 10px; font-size: 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 6px; padding: 6px 12px; color: #92400e; display: inline-flex; align-items: center; gap: 6px;">
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
                                    <div style="margin-top: 12px; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 10px;">
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
                                            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 12px; font-size: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.03);">
                                                <div style="font-weight: 700; color: #4f46e5; margin-bottom: 6px; display: flex; align-items: center; justify-content: space-between;">
                                                    <span>📋 Test Case {{ $idx + 1 }} (Public)</span>
                                                    <span style="font-size: 10.5px; font-weight: 600; color: #059669; background: #ecfdf5; padding: 1px 6px; border-radius: 4px;">Public Test</span>
                                                </div>
                                                <div style="margin-bottom: 6px; color: #334155;">
                                                    <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Input:</strong>
                                                    <code class="mono" style="display: block; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 4px; padding: 5px 8px; color: #0f172a; margin-top: 2px; white-space: pre-wrap; word-break: break-word;">{{ $inDisplay ?: '(empty)' }}</code>
                                                </div>
                                                <div style="color: #334155;">
                                                    <strong style="color: #64748b; font-size: 11px; text-transform: uppercase;">Expected Output:</strong>
                                                    <code class="mono" style="display: block; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; padding: 5px 8px; color: #15803d; margin-top: 2px; font-weight: 700; white-space: pre-wrap; word-break: break-word;">{{ $outDisplay ?: '(empty)' }}</code>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                @php
                                    $hiddenCases = is_array($q->hidden_test_cases) ? $q->hidden_test_cases : (json_decode($q->hidden_test_cases, true) ?? []);
                                @endphp
                                @if(!empty($hiddenCases))
                                    <div style="margin-top: 8px; font-size: 11.5px; color: #64748b; display: flex; align-items: center; gap: 6px;">
                                        <span>🔒 <strong>{{ count($hiddenCases) }} Confidential Hidden Edge Cases</strong> configured for grading ({{ $q->hidden_weightage_marks ?? 40 }} pts)</span>
                                    </div>
                                @endif
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
<div id="submissions-section" class="glass-card" style="scroll-margin-top: 24px;">
    <div class="card-header-flex" style="flex-wrap: wrap; gap: 14px;">
        <div>
            <div class="card-title">
                <i data-lucide="check-circle-2" style="color: #059669; width: 22px; height: 22px;"></i>
                <span>Student Submissions & Attempt History ({{ $submissionsCount }})</span>
            </div>
            <div style="display: flex; gap: 12px; align-items: center; margin-top: 4px; flex-wrap: wrap;">
                <span style="font-size: 12px; color: #64748b;">
                    👥 <strong>{{ $uniqueCandidatesCount ?? 0 }}</strong> Unique Candidate{{ ($uniqueCandidatesCount ?? 0) == 1 ? '' : 's' }}
                </span>
                <span style="font-size: 12px; color: #d97706; background: #fffbeb; padding: 2px 8px; border-radius: 6px; border: 1px solid #fde68a;">
                    🔄 <strong>{{ $reattemptedCandidatesCount ?? 0 }}</strong> Reattempted
                </span>
                @if(($reattemptedCandidatesCount ?? 0) > 0)
                    <span style="font-size: 12px; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px; border: 1px solid #a7f3d0;">
                        📈 Avg Reattempt Gain: <strong>+{{ number_format($avgImprovement ?? 0, 1) }} pts</strong>
                    </span>
                @endif
            </div>
        </div>

        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <!-- Attempt Filter Tabs -->
            <div style="display: flex; background: #f1f5f9; padding: 3px; border-radius: 8px; gap: 2px;">
                <button type="button" class="btn-sub-filter active" onclick="filterSubmissionsTab('all', this)">All ({{ $submissionsCount }})</button>
                <button type="button" class="btn-sub-filter" onclick="filterSubmissionsTab('initial', this)">1st Attempts</button>
                <button type="button" class="btn-sub-filter" onclick="filterSubmissionsTab('reattempts', this)">Reattempts ({{ $reattemptedCandidatesCount ?? 0 }})</button>
            </div>

            <!-- Batch Selective Reschedule Action -->
            <button type="button" id="btn-batch-reschedule" class="btn btn-secondary" style="display: none; font-size: 12px; padding: 6px 12px; background: #eef2ff; color: #4f46e5; border-color: #c7d2fe;" onclick="openSelectedRescheduleModal()">
                <i data-lucide="calendar-clock" style="width: 14px; height: 14px;"></i>
                <span id="lbl-batch-count">Reschedule Selected (0)</span>
            </button>

            <a href="{{ route('submissions.index', ['exam_code' => $exam->exam_code]) }}" style="color: #4f46e5; font-size: 13px; text-decoration: none; font-weight: 600;">
                Full Grid &rarr;
            </a>
        </div>
    </div>

    <div class="table-responsive" style="margin-top: 14px;">
        <table class="custom-table" id="table-exam-submissions">
            <thead>
                <tr>
                    <th style="width: 36px;">
                        <input type="checkbox" id="chk-select-all-subs" onchange="toggleSelectAllSubs(this)" title="Select all students">
                    </th>
                    <th>Candidate</th>
                    <th>Attempt</th>
                    <th>Score Comparison</th>
                    <th>Section Marks (MCQ / Code / Essay)</th>
                    <th>Total Score</th>
                    <th>Submitted At</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($exam->submissions as $sub)
                    @php
                        $attNum = $sub->computed_attempt_number ?? ($sub->attempt_number ?: 1);
                        $isReattempt = $attNum > 1;
                        $scoreDiff = $sub->score_diff_from_first ?? 0;
                        $firstScore = $sub->first_attempt_score ?? $sub->total_score;
                    @endphp
                    <tr class="sub-row {{ $isReattempt ? 'is-reattempt' : 'is-initial' }}">
                        <td>
                            <input type="checkbox" class="chk-sub-item" value="{{ $sub->candidate_id }}" data-cand-name="{{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}" onchange="updateBatchButtonState()">
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 34px; height: 34px; border-radius: 9px; background: {{ $isReattempt ? 'linear-gradient(135deg, #f59e0b, #ef4444)' : 'linear-gradient(135deg, #4f46e5, #06b6d4)' }}; color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 12px; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    {{ substr($sub->candidate->full_name ?? $sub->candidate_id, 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 700; color: #0f172a;">{{ $sub->candidate ? $sub->candidate->full_name : $sub->candidate_id }}</div>
                                    <div class="mono" style="font-size: 11px; color: #64748b;">{{ $sub->candidate_id }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($isReattempt)
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 800; color: #b45309; background: #fef3c7; border: 1px solid #fde68a; padding: 2px 8px; border-radius: 6px;">
                                    <span>🔄 Reattempt #{{ $attNum }}</span>
                                </span>
                                @if($sub->reattempt_reason)
                                    <div style="margin-top: 4px; font-size: 11px; color: #475569; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 5px; padding: 2px 6px; max-width: 220px;" title="Stated Reason: {{ $sub->reattempt_reason }}">
                                        <strong>Reason:</strong> {{ Str::limit($sub->reattempt_reason, 35) }}
                                    </div>
                                @endif
                            @else
                                <span style="display: inline-flex; align-items: center; gap: 4px; font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; border: 1px solid #e0e7ff; padding: 2px 8px; border-radius: 6px;">
                                    <span>Attempt #1</span>
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($isReattempt)
                                <div style="display: flex; flex-direction: column; gap: 2px;">
                                    <div style="font-size: 11px; color: #64748b;">
                                        1st: <span style="font-weight: 700; color: #334155;">{{ number_format($firstScore, 1) }} pts</span>
                                        &rarr; Reattempt: <strong style="color: #0f172a;">{{ number_format($sub->total_score, 1) }} pts</strong>
                                    </div>
                                    <div>
                                        @if($scoreDiff > 0)
                                            <span style="font-size: 10.5px; font-weight: 800; color: #15803d; background: #dcfce7; padding: 1px 6px; border-radius: 4px;">
                                                ▲ +{{ number_format($scoreDiff, 1) }} pts improvement
                                            </span>
                                        @elseif($scoreDiff < 0)
                                            <span style="font-size: 10.5px; font-weight: 800; color: #b91c1c; background: #fee2e2; padding: 1px 6px; border-radius: 4px;">
                                                ▼ {{ number_format($scoreDiff, 1) }} pts
                                            </span>
                                        @else
                                            <span style="font-size: 10.5px; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 1px 6px; border-radius: 4px;">
                                                = Same Score
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div style="font-size: 12px; color: #64748b;">
                                    Initial: <strong style="color: #0f172a;">{{ number_format($sub->total_score, 1) }} pts</strong>
                                </div>
                            @endif
                        </td>
                        <td style="font-size: 12px; color: #334155;">
                            <div>MCQ: <strong>{{ number_format($sub->mcq_score, 1) }}</strong> | Code: <strong>{{ number_format($sub->coding_public_score + $sub->coding_hidden_score, 1) }}</strong></div>
                            <div style="font-size: 11px; color: #64748b;">Essay: {{ number_format($sub->essay_score, 1) }} pts</div>
                        </td>
                        <td>
                            <strong style="font-size: 15px; color: #059669; font-family: 'Outfit', sans-serif;">
                                {{ number_format($sub->total_score, 1) }} pts
                            </strong>
                        </td>
                        <td style="font-size: 11.5px; color: #64748b;">
                            {{ \Carbon\Carbon::parse($sub->submission_timestamp)->format('d M, H:i') }}
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 6px; justify-content: flex-end; align-items: center;">
                                <a href="{{ route('submissions.show', $sub->id) }}" class="btn btn-secondary" style="font-size: 11px; padding: 4px 8px;" title="Inspect Paper Answers & Judge Verdicts">
                                    <span>Inspect</span> &rarr;
                                </a>
                                @if($canCreateExams)
                                    <button type="button" class="action-btn" style="background: #f1f5f9; color: #475569;" title="Reschedule or Authorize Reattempt for this Candidate" onclick="openSingleCandidateReschedule('{{ $sub->candidate_id }}', '{{ addslashes($sub->candidate ? $sub->candidate->full_name : $sub->candidate_id) }}')">
                                        <i data-lucide="calendar-clock" style="width: 14px; height: 14px;"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 32px; color: #64748b;">
                            No student submissions recorded for this examination yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif

<!-- Quick Reschedule Exam Modal Dialog (Whole Exam or Selected Students) -->
<div id="rescheduleModal" style="display: none; position: fixed; inset: 0; z-index: 9999; background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px); align-items: center; justify-content: center; padding: 16px;">
    <div style="background: #ffffff; width: 100%; max-width: 560px; border-radius: 20px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); border: 1px solid #e2e8f0; overflow: hidden; animation: modalPop 0.25s cubic-bezier(0.16, 1, 0.3, 1);">
        <!-- Modal Header -->
        <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; background: linear-gradient(135deg, #f8fafc 0%, #eef2ff 100%);">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #e0e7ff; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 20px;">
                    📅
                </div>
                <div>
                    <h3 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;" id="modal-reschedule-title">Reschedule Examination</h3>
                    <p style="font-size: 12px; color: #64748b; margin: 2px 0 0 0;">Exam Code: <span class="mono" style="font-weight: 700; color: #4f46e5;">{{ $exam->exam_code }}</span></p>
                </div>
            </div>
            <button type="button" onclick="closeRescheduleModal()" style="background: transparent; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 4px; border-radius: 6px;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
        </div>

        <!-- Target Scope Banner (All vs Selected Students) -->
        <div id="modal-scope-banner" style="background: #eff6ff; border-bottom: 1px solid #dbeafe; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; font-size: 12.5px; color: #1e40af;">
            <div style="display: flex; align-items: center; gap: 6px;">
                <span>🎯 <strong>Scope:</strong></span>
                <span id="modal-scope-text">All enrolled students in this examination</span>
            </div>
            <span id="modal-scope-tag" style="background: #dbeafe; color: #1d4ed8; font-weight: 700; font-size: 11px; padding: 2px 8px; border-radius: 4px;">Whole Batch</span>
        </div>

        <!-- Modal Form -->
        <form action="{{ route('exams.reschedule', $exam->exam_code) }}" method="POST" style="padding: 24px;" id="form-reschedule-exam">
            @csrf
            <div id="modal-selective-inputs"></div>

            <!-- Date Selection -->
            <div style="margin-bottom: 18px;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    <span>Select New Exam Date *</span>
                    <span id="modal-date-badge" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #ede9fe; padding: 2px 8px; border-radius: 6px;">{{ $exam->exam_date }}</span>
                </label>
                <input type="date" id="modal_picker_date" class="form-control" value="{{ date('Y-m-d', strtotime($exam->exam_date ?? now())) }}" required oninput="syncModalDate()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; width: 100%;">
                <input type="hidden" name="exam_date" id="modal_hidden_date" value="{{ $exam->exam_date }}">
                
                <!-- Quick Date Presets -->
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickDate(0)">Today</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickDate(1)">Tomorrow</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickDate(2)">+2 Days</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickDate(7)">+1 Week</button>
                </div>
            </div>

            <!-- Time Slot Selection -->
            <div style="margin-bottom: 18px;">
                <label style="display: flex; justify-content: space-between; align-items: center; font-size: 13px; font-weight: 700; color: #334155; margin-bottom: 6px;">
                    <span>Exam Time Slot *</span>
                    <span id="modal-time-badge" style="font-size: 11px; font-weight: 700; color: #059669; background: #ecfdf5; padding: 2px 8px; border-radius: 6px;">{{ $exam->exam_time ?? '10:00 AM - 12:00 PM' }}</span>
                </label>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <input type="time" id="modal_picker_start" class="form-control" value="10:00" required oninput="syncModalTime()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; flex: 1;">
                    <span style="color: #94a3b8; font-weight: 800; font-size: 12px; text-transform: uppercase;">TO</span>
                    <input type="time" id="modal_picker_end" class="form-control" value="12:00" required oninput="syncModalTime()" style="padding: 10px 12px; font-size: 14px; border: 1.5px solid #cbd5e1; border-radius: 8px; flex: 1;">
                </div>
                <input type="hidden" name="exam_time" id="modal_hidden_time" value="{{ $exam->exam_time ?? '10:00 AM - 12:00 PM' }}">

                <!-- Quick Time Slot Presets -->
                <div style="display: flex; gap: 6px; margin-top: 8px; flex-wrap: wrap;">
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickTime('09:00', '11:00')">Morning (9 - 11 AM)</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickTime('10:00', '13:00')">Morning (10 AM - 1 PM)</button>
                    <button type="button" class="btn-reschedule-preset" onclick="setModalQuickTime('14:00', '17:00')">Afternoon (2 - 5 PM)</button>
                </div>
            </div>

            <!-- Reattempt / Reset Options -->
            <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 12px 14px; margin-bottom: 20px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin-bottom: 0;">
                    <input type="checkbox" name="allow_reattempt" value="1" style="margin-top: 3px; accent-color: #4f46e5;">
                    <div>
                        <span style="font-size: 13px; font-weight: 700; color: #0f172a; display: block;">Allow Fresh Reattempt / Reset Submission</span>
                        <span style="font-size: 11.5px; color: #64748b;">If checked, candidates can take the test again and state their reason for reattempt.</span>
                    </div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeRescheduleModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" style="padding: 10px 20px; font-weight: 700; background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span id="lbl-confirm-btn">Confirm Reschedule</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    @keyframes modalPop {
        0% { transform: scale(0.95); opacity: 0; }
        100% { transform: scale(1); opacity: 1; }
    }
    .btn-reschedule-preset {
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 6px;
        padding: 4px 9px;
        font-size: 11px;
        font-weight: 600;
        color: #475569;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-reschedule-preset:hover {
        background: #ede9fe;
        color: #4f46e5;
        border-color: #c7d2fe;
    }
    .btn-sub-filter {
        background: transparent;
        border: none;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        transition: all 0.15s ease;
    }
    .btn-sub-filter.active {
        background: #ffffff;
        color: #0f172a;
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
</style>

<script>
    function openRescheduleModal() {
        const m = document.getElementById('rescheduleModal');
        if (m) {
            // Reset to whole batch scope
            document.getElementById('modal-reschedule-title').textContent = 'Reschedule Examination (Whole Batch)';
            document.getElementById('modal-scope-text').textContent = 'All enrolled students in this examination';
            document.getElementById('modal-scope-tag').textContent = 'Whole Batch';
            document.getElementById('modal-selective-inputs').innerHTML = '';
            document.getElementById('lbl-confirm-btn').textContent = 'Confirm Batch Reschedule';

            m.style.display = 'flex';
            syncModalDate();
            syncModalTime();
            if (window.lucide) lucide.createIcons();
        }
    }

    function openSingleCandidateReschedule(candidateId, candidateName) {
        const m = document.getElementById('rescheduleModal');
        if (m) {
            document.getElementById('modal-reschedule-title').textContent = 'Reschedule / Grant Reattempt for Student';
            document.getElementById('modal-scope-text').innerHTML = `Candidate: <strong>${candidateName}</strong> (${candidateId})`;
            document.getElementById('modal-scope-tag').textContent = 'Single Student';
            document.getElementById('modal-selective-inputs').innerHTML = `<input type="hidden" name="candidate_id" value="${candidateId}">`;
            document.getElementById('lbl-confirm-btn').textContent = `Reschedule for ${candidateName}`;

            m.style.display = 'flex';
            syncModalDate();
            syncModalTime();
            if (window.lucide) lucide.createIcons();
        }
    }

    function openSelectedRescheduleModal() {
        const selected = Array.from(document.querySelectorAll('.chk-sub-item:checked'));
        if (selected.length === 0) return;

        const m = document.getElementById('rescheduleModal');
        if (m) {
            document.getElementById('modal-reschedule-title').textContent = `Reschedule Selected Students (${selected.length})`;
            document.getElementById('modal-scope-text').innerHTML = `<strong>${selected.length}</strong> selected candidate(s) will be updated.`;
            document.getElementById('modal-scope-tag').textContent = `${selected.length} Selected`;
            
            const hiddenInputs = selected.map(el => `<input type="hidden" name="candidate_ids[]" value="${el.value}">`).join('');
            document.getElementById('modal-selective-inputs').innerHTML = hiddenInputs;
            document.getElementById('lbl-confirm-btn').textContent = `Reschedule for ${selected.length} Student(s)`;

            m.style.display = 'flex';
            syncModalDate();
            syncModalTime();
            if (window.lucide) lucide.createIcons();
        }
    }

    function closeRescheduleModal() {
        const m = document.getElementById('rescheduleModal');
        if (m) m.style.display = 'none';
    }

    window.onclick = function(event) {
        const m = document.getElementById('rescheduleModal');
        if (event.target === m) {
            closeRescheduleModal();
        }
    };

    function toggleSelectAllSubs(masterChk) {
        const items = document.querySelectorAll('.chk-sub-item');
        items.forEach(chk => chk.checked = masterChk.checked);
        updateBatchButtonState();
    }

    function updateBatchButtonState() {
        const selected = document.querySelectorAll('.chk-sub-item:checked');
        const batchBtn = document.getElementById('btn-batch-reschedule');
        const countLbl = document.getElementById('lbl-batch-count');
        if (batchBtn && countLbl) {
            if (selected.length > 0) {
                batchBtn.style.display = 'inline-flex';
                countLbl.textContent = `Reschedule Selected (${selected.length})`;
            } else {
                batchBtn.style.display = 'none';
            }
        }
    }

    function filterSubmissionsTab(tab, btn) {
        document.querySelectorAll('.btn-sub-filter').forEach(b => b.classList.remove('active'));
        if (btn) btn.classList.add('active');

        const rows = document.querySelectorAll('#table-exam-submissions tbody tr.sub-row');
        rows.forEach(row => {
            if (tab === 'all') {
                row.style.display = '';
            } else if (tab === 'initial') {
                row.style.display = row.classList.contains('is-initial') ? '' : 'none';
            } else if (tab === 'reattempts') {
                row.style.display = row.classList.contains('is-reattempt') ? '' : 'none';
            }
        });
    }

    function formatReadableDate(dateObj) {
        const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        const day = String(dateObj.getDate()).padStart(2, '0');
        const month = months[dateObj.getMonth()];
        const year = dateObj.getFullYear();
        return `${day} ${month} ${year}`;
    }

    function formatTime12(time24Str) {
        if (!time24Str) return '10:00 AM';
        const parts = time24Str.split(':');
        let h = parseInt(parts[0], 10);
        const m = parts[1] ? parts[1] : '00';
        const meridian = h >= 12 ? 'PM' : 'AM';
        h = h % 12;
        if (h === 0) h = 12;
        const hStr = String(h).padStart(2, '0');
        return `${hStr}:${m} ${meridian}`;
    }

    function syncModalDate() {
        const dateVal = document.getElementById('modal_picker_date').value;
        if (!dateVal) return;
        const parts = dateVal.split('-');
        const d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        const formatted = formatReadableDate(d);
        document.getElementById('modal_hidden_date').value = formatted;
        document.getElementById('modal-date-badge').textContent = formatted;
    }

    function syncModalTime() {
        const startVal = document.getElementById('modal_picker_start').value;
        const endVal = document.getElementById('modal_picker_end').value;
        if (!startVal || !endVal) return;
        const formatted = `${formatTime12(startVal)} - ${formatTime12(endVal)}`;
        document.getElementById('modal_hidden_time').value = formatted;
        document.getElementById('modal-time-badge').textContent = formatted;
    }

    function setModalQuickDate(daysFromNow) {
        const d = new Date();
        d.setDate(d.getDate() + daysFromNow);
        const yyyy = d.getFullYear();
        const mm = String(d.getMonth() + 1).padStart(2, '0');
        const dd = String(d.getDate()).padStart(2, '0');
        document.getElementById('modal_picker_date').value = `${yyyy}-${mm}-${dd}`;
        syncModalDate();
    }

    function setModalQuickTime(startTime, endTime) {
        document.getElementById('modal_picker_start').value = startTime;
        document.getElementById('modal_picker_end').value = endTime;
        syncModalTime();
    }
</script>
@endsection
