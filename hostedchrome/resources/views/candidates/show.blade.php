@extends('layouts.admin')

@section('title', 'Student Profile: ' . $user->full_name)
@section('breadcrumb', 'Student: ' . $user->full_name)

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('candidates.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Student Roster</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Student Performance Dossier</h1>
        <div style="display: flex; gap: 10px; align-items: center;">
            @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                <button onclick="document.getElementById('assignSubjectTeacherModal').style.display='flex'" class="quick-action-btn" style="background: linear-gradient(135deg, #6366f1, #3b82f6);">
                    <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                    <span>Assign Subject Teacher</span>
                </button>
            @endif
            <a href="{{ route('candidates.edit', $user->id) }}" class="quick-action-btn secondary">
                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                <span>Edit Profile</span>
            </a>
            @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                <form action="{{ route('candidates.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Remove student record {{ $user->full_name }}?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="quick-action-btn danger">
                        <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                        <span>Delete</span>
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>

<!-- Student Profile Header Card -->
<div class="glass-card" style="margin-bottom: 28px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #4f46e5, #ec4899); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800;">
                {{ substr($user->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="font-size: 22px; font-weight: 800; color: #fff;">{{ $user->full_name }}</h2>
                    <span class="mono" style="font-size: 12px; background: rgba(99, 102, 241, 0.2); color: #818cf8; padding: 2px 8px; border-radius: 4px;">
                        {{ $user->student_id }}
                    </span>
                </div>
                <div style="font-size: 13px; color: #cbd5e1; margin-top: 4px;">
                    {{ $user->course }} &bull; {{ $user->stream }} (Batch {{ $user->batch_years }})
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                    {{ $user->college_name }}
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 24px; background: rgba(0,0,0,0.25); padding: 14px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Average Score</div>
                <div style="font-size: 20px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif;">{{ $user->average_score }}%</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Current Streak</div>
                <div style="font-size: 20px; font-weight: 800; color: #fbbf24; font-family: 'Outfit', sans-serif;">🔥 {{ $user->current_streak_days }}d</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Exam Access PIN</div>
                <div class="mono" style="font-size: 20px; font-weight: 800; color: #818cf8;">{{ $user->access_code ?? '123456' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Assigned Subject Faculty Section (Multi-Faculty Mapping per Student) -->
<div class="glass-card" style="margin-bottom: 28px; background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.8));">
    <div class="card-header-flex">
        <div>
            <div class="card-title">
                <i data-lucide="graduation-cap" style="color: #818cf8; width: 20px; height: 20px;"></i>
                <span>Assigned Subject Faculty & Mentors ({{ $user->subjectTeachers->count() }})</span>
            </div>
            <p style="color: var(--text-secondary); font-size: 12px; margin-top: 2px;">
                Different faculty professors assigned by Principal for individual subjects & modules
            </p>
        </div>
        @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
            <button onclick="document.getElementById('assignSubjectTeacherModal').style.display='flex'" class="quick-action-btn secondary" style="font-size: 12px; padding: 6px 12px;">
                <i data-lucide="plus" style="width: 14px; height: 14px;"></i>
                <span>Assign Subject Teacher</span>
            </button>
        @endif
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 14px;">
        @forelse($user->subjectTeachers as $mapping)
            <div style="background: rgba(0, 0, 0, 0.3); border: 1px solid var(--border-color); border-radius: var(--radius-md); padding: 14px 16px; display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <span class="mono" style="font-size: 11px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 6px; border-radius: 4px;">
                        {{ $mapping->subject_name }}
                    </span>
                    <h4 style="font-size: 14px; font-weight: 700; color: #fff; margin-top: 6px;">
                        Prof. {{ $mapping->teacher->full_name ?? 'Faculty' }}
                    </h4>
                    <div style="font-size: 11px; color: var(--text-muted); margin-top: 2px;">
                        {{ $mapping->teacher->department ?? 'General' }} &bull; {{ $mapping->academic_term }}
                    </div>
                </div>

                @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                    <form action="{{ route('candidates.unassignSubjectTeacher', ['student_id' => $user->id, 'assignment_id' => $mapping->id]) }}" method="POST" onsubmit="return confirm('Remove subject mapping?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="logout-btn" title="Unassign Teacher" style="color: #ef4444;">
                            <i data-lucide="trash-2" style="width: 15px; height: 15px;"></i>
                        </button>
                    </form>
                @endif
            </div>
        @empty
            <div style="grid-column: 1/-1; text-align: center; padding: 20px; color: var(--text-muted); font-size: 13px;">
                No subject-wise faculty assigned yet.
                @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                    Click "Assign Subject Teacher" to map subject professors to this student.
                @endif
            </div>
        @endforelse
    </div>
</div>

<!-- Dual Columns: Exam Submissions & Proctoring Violations -->
<div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 24px; margin-bottom: 28px;">
    <!-- Examination Submissions -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div class="card-title">
                <i data-lucide="check-circle" style="color: #34d399; width: 20px; height: 20px;"></i>
                <span>Assessment Submissions ({{ $user->submissions->count() }})</span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($user->submissions as $sub)
                <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(255, 255, 255, 0.02); border: 1px solid var(--border-color); border-radius: var(--radius-md);">
                    <div>
                        <div style="font-weight: 700; color: #fff; font-size: 14px;">{{ $sub->exam->title ?? $sub->exam_code }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">
                            Submitted {{ \Carbon\Carbon::parse($sub->submission_timestamp)->diffForHumans() }} &bull; Duration: {{ $sub->time_spent_minutes }}m
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-size: 16px; font-weight: 800; color: #34d399; font-family: 'Outfit', sans-serif;">
                            {{ number_format($sub->total_score, 1) }} pts
                        </div>
                        <a href="{{ route('submissions.show', $sub->id) }}" style="font-size: 11px; color: #818cf8; text-decoration: none;">Inspect Paper &rarr;</a>
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    No exam submissions recorded for this student yet.
                </div>
            @endforelse
        </div>
    </div>

    <!-- Proctoring Violations -->
    <div class="glass-card">
        <div class="card-header-flex">
            <div class="card-title">
                <i data-lucide="shield-alert" style="color: #ef4444; width: 20px; height: 20px;"></i>
                <span>Proctoring Threat Logs ({{ $user->violations->count() }})</span>
            </div>
        </div>

        <div style="display: flex; flex-direction: column; gap: 10px;">
            @forelse($user->violations as $violation)
                <div style="padding: 10px 12px; background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.2); border-radius: var(--radius-sm);">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; font-weight: 700; color: #f87171;">
                        <span>{{ $violation->violation_type }}</span>
                        <span style="font-size: 10px; color: var(--text-muted);">{{ \Carbon\Carbon::parse($violation->timestamp)->diffForHumans() }}</span>
                    </div>
                    <div style="font-size: 11px; color: #cbd5e1; margin-top: 4px;">
                        "{{ $violation->details }}"
                    </div>
                </div>
            @empty
                <div style="text-align: center; padding: 24px; color: var(--text-muted);">
                    <i data-lucide="shield-check" style="width: 24px; height: 24px; color: #10b981; margin-bottom: 6px;"></i>
                    <p>Clean proctoring track record. No violations recorded.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<!-- Modal: Assign Subject Teacher (Principal Action) -->
<div class="modal-overlay" id="assignSubjectTeacherModal">
    <div class="modal-box">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px;">
            <h3 style="font-size: 18px; font-weight: 700; color: #fff;">Assign Subject Faculty to {{ $user->full_name }}</h3>
            <button type="button" onclick="document.getElementById('assignSubjectTeacherModal').style.display='none'" class="logout-btn">
                <i data-lucide="x" style="width: 18px; height: 18px;"></i>
            </button>
        </div>

        <form action="{{ route('candidates.assignSubjectTeacher', $user->id) }}" method="POST">
            @csrf
            <div class="form-group">
                <label class="form-label">Subject / Topic Name *</label>
                <input type="text" name="subject_name" class="form-control" placeholder="e.g. Data Structures, Python, Operating Systems" required>
            </div>

            <div class="form-group">
                <label class="form-label">Select Faculty Professor *</label>
                <select name="teacher_id" class="form-control" required>
                    <option value="">-- Choose Subject Teacher --</option>
                    @foreach($availableTeachers as $t)
                        <option value="{{ $t->id }}">
                            {{ $t->full_name }} ({{ $t->department }} - {{ $t->college_name }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Link Curriculum Track (Optional)</label>
                <select name="course_id" class="form-control">
                    <option value="">-- None (Stand-alone Subject) --</option>
                    @foreach($availableCourses as $course)
                        <option value="{{ $course->course_id }}">
                            {{ $course->title }} ({{ $course->course_id }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Academic Semester / Term</label>
                <input type="text" name="academic_term" class="form-control" placeholder="e.g. Semester 5 - 2026" value="Semester 5 - 2026">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px;">
                <button type="button" onclick="document.getElementById('assignSubjectTeacherModal').style.display='none'" class="quick-action-btn secondary">Cancel</button>
                <button type="submit" class="quick-action-btn">Assign Subject Faculty</button>
            </div>
        </form>
    </div>
</div>
@endsection
