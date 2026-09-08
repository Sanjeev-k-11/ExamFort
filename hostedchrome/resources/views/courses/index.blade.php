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
                <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">Curriculum & Faculty Course Delegation</h1>
                <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                    Author course tracks, manage modules, and allocate curriculum tracks to departmental faculty.
                </p>
            @else
                <h1 style="font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em;">My Teaching Curriculum &amp; Courses</h1>
                <p style="color: #64748b; font-size: 14px; margin-top: 4px;">
                    Courses, lesson notes, topic MCQs, and coding challenges delegated to you or authored by you.
                </p>
            @endif
        </div>
        @if($canManageCurriculum)
            <a href="{{ route('courses.create') }}" class="btn btn-primary">
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
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px;">
                    <div style="width: 48px; height: 48px; border-radius: 12px; background: #eef2ff; border: 1px solid #e0e7ff; display: flex; align-items: center; justify-content: center; font-size: 24px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.1);">
                        {{ $c->icon ?? '💻' }}
                    </div>
                    <span class="mono" style="font-size: 11px; font-weight: 700; color: #4f46e5; background: #eef2ff; padding: 3px 8px; border-radius: 6px; border: 1px solid #e0e7ff;">
                        {{ $c->course_id }}
                    </span>
                </div>

                <h3 style="font-size: 18px; font-weight: 700; color: #0f172a;">{{ $c->title }}</h3>
                <p style="font-size: 13px; color: #475569; margin-top: 6px; line-height: 1.5;">
                    {{ Str::limit($c->description, 110) }}
                </p>

                <div style="display: flex; gap: 14px; margin: 16px 0; font-size: 12px; color: #64748b; background: rgba(248, 250, 252, 0.8); padding: 10px 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <span>Lessons: <strong style="color: #0f172a;">{{ $c->lessons_count }}</strong></span>
                    <span>Duration: <strong style="color: #4f46e5;">{{ $c->duration_text }}</strong></span>
                    <span>Level: <strong style="color: #059669;">{{ $c->level }}</strong></span>
                </div>

                <!-- Assigned Faculty Badge for Principal/Admin -->
                @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
                    <div style="background: #f8fafc; padding: 10px 12px; border-radius: 8px; font-size: 11.5px; color: #475569; margin-bottom: 12px; border: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-weight: 600;">Assigned Faculty:</span>
                        @if($c->teacherAssignments->count() > 0)
                            <div style="display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;">
                                @foreach($c->teacherAssignments as $a)
                                    <span style="background: #eef2ff; color: #4f46e5; padding: 2px 8px; border-radius: 4px; font-weight: 600; border: 1px solid #e0e7ff;">
                                        {{ $a->teacher->full_name ?? 'Faculty' }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="color: #d97706; font-weight: 600; margin-left: 4px;">Unassigned</span>
                        @endif
                    </div>
                @endif
            </div>

            <div style="display: flex; gap: 8px; padding-top: 14px; border-top: 1px solid #e2e8f0;">
                <a href="{{ route('courses.show', $c->course_id) }}" class="btn btn-primary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="book-open" style="width: 14px; height: 14px;"></i>
                    <span>Manage Modules & Notes</span>
                </a>
                @if($canManageCurriculum)
                    <a href="{{ route('courses.edit', $c->course_id) }}" class="btn btn-secondary" style="font-size: 12px; padding: 8px 12px;" title="Edit Course">
                        <i data-lucide="edit" style="width: 14px; height: 14px;"></i>
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: #64748b;" class="glass-card">
            <i data-lucide="book-x" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
            @if(in_array(session('auth_user_role'), ['PROCTOR', 'TEACHER', 'FACULTY']))
                <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No curriculum assigned to your account yet</p>
                <p style="font-size: 13px; margin-top: 4px;">Your College Dean / Principal will allocate specific courses or grant you course creation rights.</p>
                @if($canManageCurriculum)
                    <div style="margin-top: 14px;">
                        <a href="{{ route('courses.create') }}" class="btn btn-primary" style="display: inline-flex;">
                            <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                            <span>Create First Course Track</span>
                        </a>
                    </div>
                @endif
            @else
                <p style="font-size: 16px; font-weight: 700; color: #0f172a;">No courses created yet</p>
                <p style="font-size: 13px; margin-top: 4px;">Click "Create New Course" to add an institutional curriculum track.</p>
                <div style="margin-top: 14px;">
                    <a href="{{ route('courses.create') }}" class="btn btn-primary" style="display: inline-flex;">
                        <i data-lucide="plus-circle" style="width: 16px; height: 16px;"></i>
                        <span>Create New Course</span>
                    </a>
                </div>
            @endif
        </div>
    @endforelse
</div>
@endsection
