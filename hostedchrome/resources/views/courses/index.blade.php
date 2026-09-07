@extends('layouts.admin')

@section('title', 'Curriculum & Courses Management')
@section('breadcrumb', 'Curriculum & Courses')

@section('content')
@php
    $canManageCurriculum = $currentUser ? ($currentUser->isAdmin() || $currentUser->isPrincipal() || $currentUser->canManageCourses()) : false;
@endphp

<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Curriculum & Faculty Course Delegation</h1>
                <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                    Author course tracks, manage modules, and allocate curriculum tracks to departmental faculty.
                </p>
            @else
                <h1 style="font-size: 26px; font-weight: 800; color: #fff;">My Teaching Curriculum &amp; Courses</h1>
                <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                    Courses, lesson notes, topic MCQs, and coding challenges delegated to you or authored by you.
                </p>
            @endif
        </div>
        @if($canManageCurriculum)
            <a href="{{ route('courses.create') }}" class="quick-action-btn">
                <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                <span>Create New Course</span>
            </a>
        @endif
    </div>
</div>

<!-- Courses Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
    @forelse($courses as $c)
        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                    <div style="width: 44px; height: 44px; border-radius: 10px; background: rgba(255, 255, 255, 0.05); border: 1px solid var(--border-color); display: flex; align-items: center; justify-content: center; font-size: 22px;">
                        {{ $c->icon ?? '💻' }}
                    </div>
                    <span class="mono" style="font-size: 11px; font-weight: 700; color: #818cf8; background: rgba(99, 102, 241, 0.15); padding: 2px 6px; border-radius: 4px;">
                        {{ $c->course_id }}
                    </span>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; color: #fff;">{{ $c->title }}</h3>
                <p style="font-size: 13px; color: var(--text-secondary); margin-top: 6px; line-height: 1.5;">
                    {{ Str::limit($c->description, 110) }}
                </p>

                <div style="display: flex; gap: 14px; margin: 16px 0; font-size: 12px; color: var(--text-muted);">
                    <span>Lessons: <strong style="color: #fff;">{{ $c->lessons_count }}</strong></span>
                    <span>Duration: <strong style="color: #818cf8;">{{ $c->duration_text }}</strong></span>
                    <span>Level: <strong style="color: #34d399;">{{ $c->level }}</strong></span>
                </div>

                <!-- Assigned Faculty Badge for Principal/Admin -->
                @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                    <div style="background: rgba(0,0,0,0.25); padding: 10px 12px; border-radius: 8px; font-size: 11.5px; color: var(--text-secondary); margin-bottom: 12px;">
                        <span style="color: var(--text-muted);">Assigned Faculty:</span>
                        @if($c->teacherAssignments->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
                                @foreach($c->teacherAssignments as $a)
                                    <span style="background: rgba(99, 102, 241, 0.15); color: #818cf8; padding: 2px 6px; border-radius: 4px; font-weight: 600;">
                                        {{ $a->teacher->full_name ?? 'Faculty' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="color: #fbbf24; font-weight: 600; margin-left: 4px;">Unassigned</span>
                        @endif
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('courses.show', $c->course_id) }}" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="book-open" style="width: 14px; height: 14px;"></i>
                    <span>Manage Modules & Notes</span>
                </a>
                @if($canManageCurriculum)
                    <a href="{{ route('courses.edit', $c->course_id) }}" class="quick-action-btn secondary" style="font-size: 12px; padding: 8px 12px;" title="Edit Course">
                        <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--text-muted);" class="glass-card">
            <i data-lucide="book-x" style="width: 36px; height: 36px; margin-bottom: 8px;"></i>
            @if(in_array(session('auth_user_role'), ['PROCTOR', 'TEACHER', 'FACULTY']))
                <p style="font-size: 15px; font-weight: 600; color: #fff;">No curriculum assigned to your account yet</p>
                <p style="font-size: 13px; margin-top: 4px;">Your College Dean / Principal will allocate specific courses or grant you course creation rights.</p>
                @if($canManageCurriculum)
                    <div style="margin-top: 14px;">
                        <a href="{{ route('courses.create') }}" class="quick-action-btn" style="display: inline-flex;">
                            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                            <span>Create First Course Track</span>
                        </a>
                    </div>
                @endif
            @else
                <p style="font-size: 15px; font-weight: 600; color: #fff;">No courses created yet</p>
                <p style="font-size: 13px; margin-top: 4px;">Click "Create New Course" to add an institutional curriculum track.</p>
                <div style="margin-top: 14px;">
                    <a href="{{ route('courses.create') }}" class="quick-action-btn" style="display: inline-flex;">
                        <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                        <span>Create New Course</span>
                    </a>
                </div>
            @endif
        </div>
    @endforelse
</div>
@endsection
