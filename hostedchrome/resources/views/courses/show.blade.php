@extends('layouts.admin')

@section('title', 'Course: ' . $course->title)
@section('breadcrumb', 'Course: ' . $course->title)

@section('styles')
<!-- html2pdf bundle for landscape PDF download -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<style>
    /* WHITE / LIGHT THEME FOR COURSE DETAILS */
    .course-page-wrapper {
        background: #f8fafc;
        color: #0f172a;
        margin: -24px -28px;
        padding: 24px 32px 40px 32px;
        min-height: calc(100vh - 70px);
        font-family: 'Plus Jakarta Sans', -apple-system, sans-serif;
    }

    /* BACK NAV */
    .btn-back-nav {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        color: #4f46e5;
        font-size: 13px;
        font-weight: 700;
        text-decoration: none;
        margin-bottom: 16px;
        cursor: pointer;
        transition: transform 0.15s;
    }
    .btn-back-nav:hover { transform: translateX(-3px); }

    /* TOP HERO CARD */
    .course-hero-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px 26px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        margin-bottom: 20px;
    }
    .hero-left-flex { display: flex; align-items: center; gap: 18px; }
    .hero-code-icon {
        width: 56px; height: 56px;
        border-radius: 14px;
        background: #ede9fe;
        color: #4f46e5;
        font-size: 24px; font-weight: 800;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .hero-title-group h1 { font-size: 21px; font-weight: 800; color: #0f172a; margin-bottom: 4px; }
    .hero-title-group p { font-size: 12.5px; color: #64748b; margin-bottom: 8px; line-height: 1.4; }
    .hero-meta-badges { display: flex; align-items: center; gap: 14px; font-size: 11.5px; color: #475569; font-weight: 600; flex-wrap: wrap; }
    .hero-badge-pill { display: flex; align-items: center; gap: 5px; }

    /* HERO PROGRESS RIGHT */
    .hero-right-progress { width: 260px; display: flex; flex-direction: column; gap: 6px; flex-shrink: 0; }
    .progress-header-row { display: flex; justify-content: space-between; align-items: center; font-size: 12.5px; }
    .progress-header-row strong { color: #0f172a; font-weight: 800; }
    .progress-header-row span { color: #4f46e5; font-weight: 800; }
    .progress-track-bg { width: 100%; height: 7px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
    .progress-fill-bar { height: 100%; background: #4f46e5; border-radius: 4px; transition: width 0.3s; }
    .progress-sub-text { font-size: 10.5px; color: #64748b; }
    .btn-continue-learning {
        width: 100%;
        background: #4f46e5;
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 9px 14px;
        font-size: 12px; font-weight: 700;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center; gap: 6px;
        transition: background 0.15s;
        margin-top: 4px;
        text-decoration: none;
    }
    .btn-continue-learning:hover { background: #4338ca; color: #ffffff; }

    /* PRINCIPAL MANAGEMENT TOOLBAR */
    .principal-action-banner {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 12px;
        padding: 12px 18px;
        margin-bottom: 20px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
    }
    .principal-pill-tag {
        background: #ede9fe;
        color: #4f46e5;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 6px;
        border: 1px solid #c7d2fe;
    }
    .btn-principal-ai {
        background: linear-gradient(135deg, #4f46e5, #7c3aed);
        color: #ffffff;
        border: none;
        border-radius: 8px;
        padding: 7px 16px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
        transition: all 0.15s;
    }
    .btn-principal-ai:hover { background: linear-gradient(135deg, #4338ca, #6d28d9); color: #fff; }
    .btn-principal-secondary {
        background: #ffffff;
        color: #334155;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 6px 14px;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.15s;
        text-decoration: none;
    }
    .btn-principal-secondary:hover { background: #f8fafc; border-color: #94a3b8; color: #0f172a; }

    /* TABS */
    .course-tabs-bar { display: flex; gap: 24px; border-bottom: 1.5px solid #e2e8f0; margin-bottom: 22px; }
    .course-tab-item { font-size: 13px; font-weight: 600; color: #64748b; padding-bottom: 10px; cursor: pointer; border-bottom: 2.5px solid transparent; transition: all 0.15s; }
    .course-tab-item:hover { color: #0f172a; }
    .course-tab-item.active { font-weight: 800; color: #4f46e5; border-bottom-color: #4f46e5; }

    /* 2-COLUMN GRID (ABOUT COURSE + ACCORDION / TABS) */
    .course-content-grid { display: grid; grid-template-columns: 340px 1fr; gap: 24px; margin-bottom: 24px; }
    @media (max-width: 992px) {
        .course-content-grid { grid-template-columns: 1fr; }
        .course-hero-card { flex-direction: column; align-items: stretch; }
        .hero-right-progress { width: 100%; }
    }

    /* ABOUT THIS COURSE CARD */
    .about-course-card {
        background: #ffffff;
        border: 1.5px solid #e2e8f0;
        border-radius: 16px;
        padding: 22px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.02);
        display: flex;
        flex-direction: column;
        height: fit-content;
    }
    .about-course-card h3 { font-size: 14.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
    .about-course-desc { font-size: 12.5px; color: #64748b; line-height: 1.6; margin-bottom: 18px; }

    .course-meta-table { display: flex; flex-direction: column; gap: 12px; border-top: 1px solid #f1f5f9; padding-top: 14px; font-size: 12px; }
    .meta-table-row { display: flex; justify-content: space-between; align-items: center; }
    .meta-table-label { display: flex; align-items: center; gap: 8px; color: #64748b; font-weight: 600; }
    .meta-table-val { color: #0f172a; font-weight: 700; }

    /* TAB PANES STYLING */
    .tab-pane-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.02); }
    .tab-pane-title { font-size: 17px; font-weight: 800; color: #0f172a; margin-bottom: 6px; }
    .tab-pane-sub { font-size: 12.5px; color: #64748b; margin-bottom: 18px; line-height: 1.5; }

    /* MODULE ACCORDION (RIGHT) */
    .module-accordion-container { display: flex; flex-direction: column; gap: 12px; }
    .module-card { background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.02); transition: border-color 0.15s; }
    .module-card:hover { border-color: #cbd5e1; }
    .module-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 18px; background: #ffffff; cursor: pointer; user-select: none; }
    .module-header-left { display: flex; align-items: center; gap: 12px; }
    .module-badge-num { width: 26px; height: 26px; border-radius: 6px; background: #ede9fe; color: #4f46e5; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
    .module-title-text { font-size: 13.5px; font-weight: 800; color: #0f172a; }
    .module-header-right { display: flex; align-items: center; gap: 10px; font-size: 12px; color: #64748b; }
    .module-chevron { font-size: 11px; color: #94a3b8; transition: transform 0.2s; }

    .module-lessons-list { border-top: 1px solid #f1f5f9; background: #fafafa; }
    .module-lessons-list.hidden { display: none; }
    .lesson-row-item { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid #f1f5f9; font-size: 12.5px; background: #ffffff; }
    .lesson-row-item:last-child { border-bottom: none; }
    .lesson-left-col { display: flex; align-items: center; gap: 12px; }
    .lesson-status-icon { font-size: 14px; }
    .lesson-num-code { color: #64748b; font-weight: 700; font-size: 12px; min-width: 26px; }
    .lesson-title-name { color: #0f172a; font-weight: 700; }

    .lesson-right-col { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
    .lesson-duration-tag { font-size: 11.5px; color: #64748b; }
    .btn-start-lesson-pill {
        background: #ffffff;
        border: 1.5px solid #4f46e5;
        color: #4f46e5;
        border-radius: 6px;
        padding: 5px 12px;
        font-size: 11.5px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.15s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .btn-start-lesson-pill:hover { background: #4f46e5; color: #ffffff; }

    /* MODAL STYLES (LIGHT THEME) */
    .modal-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(4px);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 99999;
        padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-card {
        background: #ffffff;
        border-radius: 18px;
        width: 100%;
        max-width: 800px;
        max-height: 90vh;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        border: 1px solid #e2e8f0;
    }
    .modal-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1.5px solid #e2e8f0;
        background: #ffffff;
    }
    .modal-body {
        flex: 1;
        overflow-y: auto;
        padding: 24px;
        background: #f8fafc;
        color: #0f172a;
    }
    .modal-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 16px 24px;
        border-top: 1.5px solid #e2e8f0;
        background: #ffffff;
    }

    .form-input-light {
        width: 100%;
        background: #ffffff;
        border: 1.5px solid #cbd5e1;
        border-radius: 8px;
        padding: 9px 12px;
        font-size: 13px;
        color: #0f172a;
        outline: none;
        transition: border-color 0.15s;
    }
    .form-input-light:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
    }
</style>
@endsection

@section('content')
<div class="course-page-wrapper">
    <a class="btn-back-nav" href="{{ route('courses.index') }}">
        <span>←</span>
        <span>Back to Courses</span>
    </a>

    @php
        $currentUser = \App\Models\User::find(session('auth_user_id'));
        $isPrincipalOrAdmin = in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']);
        $canManageLessons = $currentUser ? ($isPrincipalOrAdmin || $currentUser->canManageLessons($course->course_id)) : false;
        $canManageCourses = $currentUser ? ($isPrincipalOrAdmin || $currentUser->canManageCourses()) : false;
    @endphp

    <!-- PRINCIPAL & AUTHORIZED FACULTY MANAGEMENT BAR -->
    @if($isPrincipalOrAdmin || $canManageLessons || $canManageCourses)
    <div class="principal-action-banner">
        <div style="display: flex; align-items: center; gap: 10px;">
            <span class="principal-pill-tag">{{ $isPrincipalOrAdmin ? 'INSTITUTION CONTROLS' : 'FACULTY AUTHORING' }}</span>
            <span style="font-size: 12.5px; color: #475569; font-weight: 600;">
                {{ $isPrincipalOrAdmin ? 'Full curriculum authoring & faculty delegation rights' : 'Module & lesson authoring permissions assigned by Principal' }}
            </span>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            @if($canManageLessons)
            <button onclick="openAiLessonModal()" class="btn-principal-ai">
                <span>✨ AI Lesson Studio (Gemini)</span>
            </button>
            <button onclick="document.getElementById('addLessonModal').style.display='flex'" class="btn-principal-secondary">
                <span>+ Add Lesson Manually</span>
            </button>
            @endif

            @if($isPrincipalOrAdmin)
            <button onclick="document.getElementById('assignTeacherModal').style.display='flex'" class="btn-principal-secondary">
                <span>+ Assign Faculty</span>
            </button>
            @endif

            @if($canManageCourses)
            <a href="{{ route('courses.edit', $course->course_id) }}" class="btn-principal-secondary">
                <span>⚙️ Edit Course</span>
            </a>
            @endif
        </div>
    </div>
    @endif

    <!-- TOP HERO CARD -->
    <div class="course-hero-card">
        <div class="hero-left-flex">
            <div class="hero-code-icon">
                {{ $course->icon ?? '</>' }}
            </div>
            <div class="hero-title-group">
                <h1>{{ $course->title }}</h1>
                <p>{{ $course->description }}</p>
                <div class="hero-meta-badges">
                    <span class="hero-badge-pill">📖 {{ $course->lessons->count() }} Lessons</span>
                    <span>•</span>
                    <span class="hero-badge-pill">⏱️ {{ $course->duration_text }}</span>
                    <span>•</span>
                    <span class="hero-badge-pill">📊 {{ $course->level }}</span>
                    <span>•</span>
                    <span class="hero-badge-pill">🌐 {{ $course->language ?? 'English' }}</span>
                </div>
            </div>
        </div>

        @php
            $completedLessonsCount = $course->lessons->where('is_completed', 1)->count();
            $totalLessonsCount = max(1, $course->lessons->count());
            $pct = round(($completedLessonsCount / $totalLessonsCount) * 100);
            $firstLesson = $course->lessons->first();
        @endphp

        <div class="hero-right-progress">
            <div class="progress-header-row">
                <strong>Your Progress</strong>
                <span>{{ $pct }}%</span>
            </div>
            <div class="progress-track-bg">
                <div class="progress-fill-bar" style="width: {{ $pct }}%;"></div>
            </div>
            <span class="progress-sub-text">{{ $completedLessonsCount }} of {{ $course->lessons->count() }} Lessons Completed</span>
            @if($firstLesson)
                <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $firstLesson->lesson_num]) }}?tab=study" class="btn-continue-learning">
                    <span>▶</span> Continue Learning
                </a>
            @else
                <button class="btn-continue-learning" disabled style="opacity: 0.5;">
                    <span>▶</span> No Lessons Added
                </button>
            @endif
        </div>
    </div>

    <!-- Faculty Instructors Assigned Banner (If Any) -->
    @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']) && $course->teacherAssignments->count() > 0)
    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 14px; padding: 14px 20px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.02);">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px;">
            <span style="font-size: 13px; font-weight: 800; color: #4f46e5; display: flex; align-items: center; gap: 6px;">
                <span>👥</span>
                <span>Assigned Faculty Instructors ({{ $course->teacherAssignments->count() }})</span>
            </span>
            <button onclick="document.getElementById('assignTeacherModal').style.display='flex'" class="btn-start-lesson-pill" style="font-size: 11px; padding: 4px 10px;">
                + Assign Another Faculty
            </button>
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 10px;">
            @foreach($course->teacherAssignments as $assignment)
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 10px 14px; display: flex; align-items: center; justify-content: space-between;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #0f172a;">{{ $assignment->teacher->full_name ?? 'Faculty Member' }}</div>
                        <div style="font-size: 11px; color: #64748b;">{{ $assignment->teacher->department ?? 'Faculty' }} &bull; Role: <strong style="color: #10b981;">{{ $assignment->role }}</strong></div>
                    </div>
                    <form action="{{ route('courses.unassignTeacher', ['course_id' => $course->course_id, 'teacher_id' => $assignment->teacher_id]) }}" method="POST" onsubmit="return confirm('Unassign this instructor?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #ef4444; font-size: 14px; cursor: pointer; padding: 4px;" title="Unassign">✕</button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- TABS -->
    <div class="course-tabs-bar">
        <span class="course-tab-item active" id="tab-btn-content" onclick="switchCourseTab('content')">Course Content</span>
        <span class="course-tab-item" id="tab-btn-overview" onclick="switchCourseTab('overview')">Overview &amp; Syllabus</span>
        <span class="course-tab-item" id="tab-btn-resources" onclick="switchCourseTab('resources')">Resources &amp; Tools</span>
        <span class="course-tab-item" id="tab-btn-notes" onclick="switchCourseTab('notes')">Quick Notes &amp; PDF</span>
    </div>

    <!-- 2-COLUMN GRID (LEFT: ABOUT COURSE META, RIGHT: ACTIVE TAB CONTENT) -->
    <div class="course-content-grid">
        <!-- LEFT COLUMN: ABOUT THIS COURSE -->
        <div class="about-course-card">
            <h3>About This Course</h3>
            <p class="about-course-desc">
                {{ $course->description }}
            </p>

            <div class="course-meta-table">
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>🎯</span> Level</span>
                    <strong class="meta-table-val">{{ $course->level }}</strong>
                </div>
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>⏱️</span> Duration</span>
                    <strong class="meta-table-val">{{ $course->duration_text }}</strong>
                </div>
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>📖</span> Lessons</span>
                    <strong class="meta-table-val">{{ $course->lessons->count() }} Lessons</strong>
                </div>
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>📜</span> Certificate</span>
                    <strong class="meta-table-val">{{ $course->certificate ?? 'Yes (Verified)' }}</strong>
                </div>
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>📅</span> Last Updated</span>
                    <strong class="meta-table-val">{{ $course->last_updated ?? date('M Y') }}</strong>
                </div>
                <div class="meta-table-row">
                    <span class="meta-table-label"><span>🌐</span> Language</span>
                    <strong class="meta-table-val">{{ $course->language ?? 'English' }}</strong>
                </div>
            </div>

            <!-- QUICK CHEAT SHEET SHORTCUT CARD -->
            <div style="margin-top: 20px; background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 6px;">
                    <span style="font-size: 16px;">📋</span>
                    <strong style="font-size: 13px; color: #1e40af;">{{ $course->title }} Quick Cheat Sheet</strong>
                </div>
                <p style="font-size: 11.5px; color: #3b82f6; margin-bottom: 10px; line-height: 1.4;">Instant key syntax, STL reference &amp; time complexities.</p>
                <button onclick="openCheatSheetModal()" style="width: 100%; background: #2563eb; color: #ffffff; border: none; border-radius: 6px; padding: 7px 12px; font-size: 11.5px; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <span>📖 View Cheat Sheet</span>
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: ACTIVE TAB PANE -->
        <div class="course-tab-content-pane">
            
            <!-- TAB 1: COURSE CONTENT (MODULES ACCORDION) -->
            <div id="pane-tab-content" style="display: block;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 14px;">
                    <h3 style="font-size: 15px; font-weight: 800; color: #0f172a; margin: 0;">Course Modules &amp; Topics ({{ $course->lessons->count() }} Total)</h3>
                    <span id="btn-toggle-all" style="font-size: 12px; color: #4f46e5; font-weight: 700; cursor: pointer;" onclick="toggleAllModules()">Expand All ⌵</span>
                </div>

                <!-- DYNAMIC ACCORDIONS -->
                <div class="module-accordion-container" id="course-modules-accordion">
                    @forelse($lessonsByModule as $moduleTitle => $lessons)
                        @php
                            $modNum = $lessons->first()->module_num ?? $loop->iteration;
                            $isFirst = $loop->first;
                        @endphp
                        <div class="module-card">
                            <div class="module-header" onclick="toggleModule(this)">
                                <div class="module-header-left">
                                    <div class="module-badge-num">{{ $modNum }}</div>
                                    <strong class="module-title-text">{{ $moduleTitle }}</strong>
                                </div>
                                <div class="module-header-right">
                                    <span>{{ $lessons->count() }} Lessons</span>
                                    <span class="module-chevron">{{ $isFirst ? '▲' : '▼' }}</span>
                                </div>
                            </div>
                            <div class="module-lessons-list {{ $isFirst ? '' : 'hidden' }}">
                                @foreach($lessons as $ls)
                                    <div class="lesson-row-item">
                                        <div class="lesson-left-col">
                                            <span class="lesson-status-icon" style="color: {{ $ls->is_completed ? '#10b981' : '#94a3b8' }}; font-weight: bold;">
                                                {{ $ls->is_completed ? '✓' : '○' }}
                                            </span>
                                            <span class="lesson-num-code">{{ $ls->lesson_num }}</span>
                                            <div>
                                                <span class="lesson-title-name">{{ $ls->lesson_title }}</span>
                                                <div style="display: flex; gap: 8px; margin-top: 3px; font-size: 11px; color: #64748b;">
                                                    <span>⏱️ {{ $ls->duration_text }}</span>
                                                    <span>•</span>
                                                    <span>MCQs: <strong style="color: #0f172a;">{{ $ls->mcqs->count() }}</strong></span>
                                                    <span>•</span>
                                                    <span>Coding: <strong style="color: {{ $ls->codingChallenge ? '#0284c7' : '#64748b' }};">{{ $ls->codingChallenge ? 'Active' : 'None' }}</strong></span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="lesson-right-col">
                                            <!-- 1. STUDY LESSON BUTTON -->
                                            <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $ls->lesson_num]) }}?tab=study" 
                                               class="btn-start-lesson-pill" 
                                               style="background: #f5f3ff; border-color: #c7d2fe; color: #4f46e5;" title="Study Conceptual Notes">
                                                <span>📖</span> <span>Study Lesson</span>
                                            </a>

                                            <!-- 2. MCQ PRACTICE BUTTON -->
                                            <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $ls->lesson_num]) }}?tab=mcq" 
                                               class="btn-start-lesson-pill" 
                                               style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;" title="Practice MCQs">
                                                <span>📝</span> <span>MCQ ({{ $ls->mcqs->count() }})</span>
                                            </a>

                                            <!-- 3. CODING PRACTICE BUTTON -->
                                            <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $ls->lesson_num]) }}?tab=code" 
                                               class="btn-start-lesson-pill" 
                                               style="background: #eff6ff; border-color: #bfdbfe; color: #1e40af;" title="Interactive Coding Sandbox">
                                                <span>💻</span> <span>Coding Sandbox</span>
                                            </a>

                                            <!-- PRINCIPAL / ADMIN / AUTHORIZED FACULTY MANAGE BUTTON -->
                                            @if($isPrincipalOrAdmin || $canManageLessons)
                                                <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $ls->lesson_num]) }}?tab=study" 
                                                   class="btn-start-lesson-pill" 
                                                   style="background: #ffffff; border-color: #cbd5e1; color: #334155;" title="Edit Lesson Content">
                                                    <span>⚙️</span> <span>Manage &amp; Notes</span>
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div style="background: #ffffff; border: 1.5px dashed #cbd5e1; border-radius: 14px; text-align: center; padding: 48px; color: #64748b;">
                            <div style="font-size: 38px; margin-bottom: 10px;">📚</div>
                            <h4 style="color: #0f172a; font-size: 16px; margin-bottom: 6px; font-weight: 800;">No Curriculum Modules Yet</h4>
                            <p style="font-size: 12.5px; margin-bottom: 18px;">Click "✨ AI Lesson Studio (Gemini)" to author your first complete lesson module.</p>
                            @if($isPrincipalOrAdmin || $canManageLessons)
                                <button onclick="openAiLessonModal()" class="btn-principal-ai" style="margin: 0 auto;">
                                    <span>✨ Generate Lesson with Gemini AI</span>
                                </button>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- TAB 2: OVERVIEW & SYLLABUS -->
            <div id="pane-tab-overview" class="tab-pane-card" style="display: none;">
                <h2 class="tab-pane-title">Course Overview &amp; Learning Objectives</h2>
                <p class="tab-pane-sub">
                    {{ $course->description }} This curriculum provides an industry-grade learning pathway with theoretical foundations, instant feedback quizzes, and compiler sandbox tests.
                </p>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                        <div style="font-size: 20px; margin-bottom: 6px;">🎯</div>
                        <strong style="font-size: 13.5px; color: #0f172a; display: block; margin-bottom: 6px;">What You Will Master</strong>
                        <ul style="padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                            <li>Core Syntax, Data Structures &amp; Algorithmic Patterns</li>
                            <li>Control Flow, Decision Logic &amp; Recursion Mechanics</li>
                            <li>Memory Management, Pointers &amp; System Performance</li>
                            <li>Object-Oriented Design &amp; Clean Architecture</li>
                            <li>Automated Test Cases &amp; Sandbox Problem Solving</li>
                        </ul>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 18px;">
                        <div style="font-size: 20px; margin-bottom: 6px;">⚡</div>
                        <strong style="font-size: 13.5px; color: #0f172a; display: block; margin-bottom: 6px;">Interactive Learning Flow</strong>
                        <ul style="padding-left: 18px; font-size: 12px; color: #475569; line-height: 1.6;">
                            <li><strong>📖 1. Study Lessons:</strong> Detailed conceptual theory notes &amp; runnable code.</li>
                            <li><strong>📝 2. MCQ Practice:</strong> Instant score feedback &amp; step-by-step explanations.</li>
                            <li><strong>💻 3. Live Sandbox:</strong> Real-time automated test evaluation across languages.</li>
                            <li><strong>🔄 Re-watch &amp; Re-study:</strong> Content remains available 24/7.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- TAB 3: RESOURCES & TOOLS -->
            <div id="pane-tab-resources" class="tab-pane-card" style="display: none;">
                <h2 class="tab-pane-title">Developer Resources &amp; References</h2>
                <p class="tab-pane-sub">Interactive cheat sheets, compiler setup guides, and standard library references.</p>
                
                <div style="display: flex; flex-direction: column; gap: 14px;">
                    <!-- CHEAT SHEET CARD -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 26px;">📋</span>
                            <div>
                                <strong style="font-size: 14px; color: #0f172a; display: block;">Quick Reference Cheat Sheet</strong>
                                <span style="font-size: 12px; color: #64748b;">Key syntax, pointers, STL containers, and Big-O time complexities</span>
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <button class="btn-start-lesson-pill" onclick="openCheatSheetModal()">📖 View Cheat Sheet</button>
                        </div>
                    </div>

                    <!-- RUNTIME ENVIRONMENT CARD -->
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 18px 20px; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px;">
                        <div style="display: flex; align-items: center; gap: 14px;">
                            <span style="font-size: 26px;">⚙️</span>
                            <div>
                                <strong style="font-size: 14px; color: #0f172a; display: block;">Multi-Language Runtime Compiler Sandbox</strong>
                                <span style="font-size: 12px; color: #64748b;">GCC 15+, Python 3.12, OpenJDK 17, and Node.js 24 secure execution engine</span>
                            </div>
                        </div>
                        <span style="background: #dcfce7; color: #15803d; font-size: 11.5px; font-weight: 800; padding: 6px 12px; border-radius: 6px;">Active &amp; Ready ✓</span>
                    </div>
                </div>
            </div>

            <!-- TAB 4: QUICK NOTES & PDF -->
            <div id="pane-tab-notes" class="tab-pane-card" style="display: none;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 18px;">
                    <div>
                        <h2 class="tab-pane-title">Download Lesson Study Notes</h2>
                        <p class="tab-pane-sub" style="margin-bottom: 0;">Download complete curated notes and code walkthroughs for offline study.</p>
                    </div>
                </div>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    @foreach($course->lessons as $ls)
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
                            <div>
                                <strong style="color: #0f172a; font-size: 13px;">{{ $ls->lesson_num }} {{ $ls->lesson_title }}</strong>
                                <span style="display: block; font-size: 11px; color: #64748b;">Module {{ $ls->module_num }}: {{ $ls->module_title }} • {{ $ls->duration_text }}</span>
                            </div>
                            <a href="{{ route('courses.lessons.content', ['course_id' => $course->course_id, 'lesson_num' => $ls->lesson_num]) }}?tab=study" class="btn-start-lesson-pill" style="display: flex; align-items: center; gap: 4px;">
                                <span>📖 Open &amp; Download PDF</span>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 1: INTERACTIVE CHEAT SHEET MODAL (LIGHT THEME)
======================================================== -->
<div class="modal-overlay" id="modal-cheatsheet" onclick="if(event.target===this) closeCheatSheetModal()">
    <div class="modal-card">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">📋</span>
                <div>
                    <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">{{ $course->title }} Quick Reference Cheat Sheet</h2>
                    <span style="font-size: 11.5px; color: #64748b;">Core Syntax, Pointers, Memory &amp; STL Containers</span>
                </div>
            </div>
            <button onclick="closeCheatSheetModal()" style="background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer; padding: 4px;">✕</button>
        </div>
        
        <div class="modal-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">1. Essential Syntax &amp; Types</h3>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11.5px; line-height: 1.5; overflow-x: auto; margin: 0;">// Variable Declarations & Scope
auto x = 42;          // automatic type
int val = 100;
int& ref = val;       // alias reference
int* ptr = &val;      // pointer to address</pre>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">2. Memory &amp; Resource Management</h3>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11.5px; line-height: 1.5; overflow-x: auto; margin: 0;">#include &lt;memory&gt;

// Smart Pointers (RAII)
auto uPtr = std::make_unique&lt;int&gt;(50);
auto sPtr = std::make_shared&lt;std::string&gt;("Hi");
// No delete required!</pre>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">3. Standard Library Containers</h3>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11.5px; line-height: 1.5; overflow-x: auto; margin: 0;">std::vector&lt;int&gt; v = {1, 2, 3};
v.push_back(4);       // O(1) amortized

std::unordered_map&lt;string, int&gt; mp;
mp["key"] = 95;       // O(1) avg lookup</pre>
                </div>

                <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px;">
                    <h3 style="font-size: 13.5px; font-weight: 800; color: #0f172a; margin-bottom: 8px; border-bottom: 1px solid #f1f5f9; padding-bottom: 6px;">4. Classes &amp; OOP</h3>
                    <pre style="background: #0f172a; color: #38bdf8; padding: 12px; border-radius: 8px; font-family: monospace; font-size: 11.5px; line-height: 1.5; overflow-x: auto; margin: 0;">class Shape {
public:
    virtual void draw() const = 0;
    virtual ~Shape() = default;
};</pre>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <span style="font-size: 12px; color: #64748b;">ExamFort Certified Developer Quick Reference</span>
            <button onclick="closeCheatSheetModal()" class="btn-start-lesson-pill" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">Close</button>
        </div>
    </div>
</div>

<!-- ========================================================
     MODAL 2: PRINCIPAL GEMINI AI LESSON STUDIO (LIGHT THEME)
======================================================== -->
@if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
<div class="modal-overlay" id="aiLessonModal" onclick="if(event.target===this) closeAiLessonModal()">
    <div class="modal-card" style="max-width: 850px;">
        <div class="modal-header">
            <div style="display: flex; align-items: center; gap: 10px;">
                <span style="font-size: 24px;">✨</span>
                <div>
                    <h2 style="font-size: 17px; font-weight: 800; color: #0f172a; margin: 0;">Principal AI Lesson Studio (Gemini)</h2>
                    <span style="font-size: 11.5px; color: #64748b;">Generate Complete Curriculum with Notes, Practice MCQs &amp; Coding Sandbox</span>
                </div>
            </div>
            <button onclick="closeAiLessonModal()" style="background: none; border: none; font-size: 22px; color: #94a3b8; cursor: pointer;">✕</button>
        </div>

        <div class="modal-body" style="padding: 24px;">
            <!-- Prompt Input Form -->
            <div id="ai-generator-form-pane">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Module Name</label>
                        <input type="text" id="ai_module_name" class="form-input-light" placeholder="e.g. Module 2: Pointers and Dynamic Memory" value="Module {{ count($lessonsByModule) + 1 }}: Advanced Concepts">
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Lesson Number</label>
                        <input type="text" id="ai_lesson_num" class="form-input-light" placeholder="e.g. 2.1" value="{{ count($lessonsByModule) + 1 }}.1">
                    </div>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Lesson Topic Title</label>
                    <input type="text" id="ai_topic_title" class="form-input-light" placeholder="e.g. Pointers, References, and Dynamic Allocation in C++">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Optional Teacher Prompt / Focus Areas</label>
                    <textarea id="ai_custom_prompt" class="form-input-light" rows="3" placeholder="Specify depth, target audience, specific algorithms or coding challenge requirements..."></textarea>
                </div>

                <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 12px 16px; margin-bottom: 20px; font-size: 12px; color: #1e40af;">
                    <strong>🤖 What Gemini AI will generate:</strong>
                    <ul style="margin: 6px 0 0 16px; line-height: 1.5;">
                        <li>Detailed Conceptual Lecture Notes &amp; Runnable Syntax Walkthrough</li>
                        <li>3-5 Practice MCQs with Answer Keys &amp; Explanations</li>
                        <li>Interactive Coding Challenge with Problem Statement, Starter Codes (C++, Python, Java), and Test Cases</li>
                    </ul>
                </div>

                <button type="button" onclick="triggerAiLessonGeneration()" id="btn-trigger-ai" class="btn-principal-ai" style="width: 100%; justify-content: center; padding: 10px 18px; font-size: 13px;">
                    <span>✨ Generate Complete Lesson via Gemini</span>
                </button>
            </div>

            <!-- Loading Spinner -->
            <div id="ai-loading-spinner" style="display: none; text-align: center; padding: 40px 20px;">
                <div style="font-size: 40px; margin-bottom: 12px; animation: spin 1.5s linear infinite; display: inline-block;">⚙️</div>
                <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Gemini AI is crafting your lesson...</h3>
                <p style="font-size: 12.5px; color: #64748b;">Writing comprehensive theory notes, drafting practice MCQs, and creating coding challenge test cases.</p>
            </div>

            <!-- Generated Content Preview & Save -->
            <div id="ai-preview-pane" style="display: none;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; background: #ecfdf5; border: 1.5px solid #a7f3d0; border-radius: 8px; padding: 10px 14px;">
                    <span style="font-size: 12.5px; font-weight: 800; color: #065f46;">✓ AI Generation Complete! Review &amp; Save Below</span>
                    <button onclick="resetAiForm()" style="background: none; border: none; font-size: 12px; color: #065f46; font-weight: 700; cursor: pointer;">↺ Re-generate</button>
                </div>

                <div style="max-height: 380px; overflow-y: auto; background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 16px;" id="ai-preview-content-box">
                    <!-- Dynamic preview loaded by JS -->
                </div>

                <button type="button" onclick="saveGeneratedAiLesson()" id="btn-save-ai" class="btn-principal-ai" style="width: 100%; justify-content: center; padding: 10px 18px; font-size: 13px; background: #10b981;">
                    <span>💾 Save to Curriculum &amp; Publish Lesson</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 3: ASSIGN FACULTY (LIGHT THEME) -->
<div class="modal-overlay" id="assignTeacherModal" onclick="if(event.target===this) document.getElementById('assignTeacherModal').style.display='none'">
    <div class="modal-card" style="max-width: 500px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Assign Faculty to Course</h3>
            <button onclick="document.getElementById('assignTeacherModal').style.display='none'" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">✕</button>
        </div>
        <form action="{{ route('courses.assignTeacher', $course->course_id) }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 20px;">
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Select Teacher / Instructor</label>
                    <select name="teacher_id" class="form-input-light" required>
                        <option value="">-- Choose Faculty Member --</option>
                        @foreach(($teachers ?? $availableTeachers ?? []) as $teacher)
                            <option value="{{ $teacher->id }}">{{ $teacher->full_name }} ({{ $teacher->department ?? 'Faculty' }})</option>
                        @endforeach
                    </select>
                </div>
                <div style="margin-bottom: 14px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Role</label>
                    <select name="role" class="form-input-light">
                        <option value="PRIMARY">Primary Instructor</option>
                        <option value="ASSISTANT">Teaching Assistant</option>
                        <option value="VIEWER">Course Reviewer</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('assignTeacherModal').style.display='none'" class="btn-start-lesson-pill" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">Cancel</button>
                <button type="submit" class="btn-principal-ai">Assign Faculty</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL 4: MANUAL ADD LESSON (LIGHT THEME) -->
<div class="modal-overlay" id="addLessonModal" onclick="if(event.target===this) document.getElementById('addLessonModal').style.display='none'">
    <div class="modal-card" style="max-width: 550px;">
        <div class="modal-header">
            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a; margin: 0;">Add Lesson Manually</h3>
            <button onclick="document.getElementById('addLessonModal').style.display='none'" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer;">✕</button>
        </div>
        <form action="{{ route('courses.lessons.store', $course->course_id) }}" method="POST">
            @csrf
            <div class="modal-body" style="padding: 20px;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px;">
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Module Number</label>
                        <input type="number" name="module_num" class="form-input-light" value="{{ count($lessonsByModule) + 1 }}" required>
                    </div>
                    <div>
                        <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Lesson Number</label>
                        <input type="text" name="lesson_num" class="form-input-light" value="{{ count($lessonsByModule) + 1 }}.1" required>
                    </div>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Module Title</label>
                    <input type="text" name="module_title" class="form-input-light" placeholder="e.g. Object Oriented Programming" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Lesson Title</label>
                    <input type="text" name="lesson_title" class="form-input-light" placeholder="e.g. Classes and Objects" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label style="display: block; font-size: 12px; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Duration (e.g. 20m)</label>
                    <input type="text" name="duration_text" class="form-input-light" value="20m">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" onclick="document.getElementById('addLessonModal').style.display='none'" class="btn-start-lesson-pill" style="background: #f1f5f9; color: #475569; border-color: #cbd5e1;">Cancel</button>
                <button type="submit" class="btn-principal-ai">Save Lesson</button>
            </div>
        </form>
    </div>
</div>
@endif

@endsection

@section('scripts')
<script>
    window.switchCourseTab = function(tabName) {
        document.querySelectorAll('.course-tab-item').forEach(t => t.classList.remove('active'));
        const activeTab = document.getElementById('tab-btn-' + tabName);
        if (activeTab) activeTab.classList.add('active');

        const pContent = document.getElementById('pane-tab-content');
        const pOverview = document.getElementById('pane-tab-overview');
        const pResources = document.getElementById('pane-tab-resources');
        const pNotes = document.getElementById('pane-tab-notes');

        if (pContent) pContent.style.display = (tabName === 'content') ? 'block' : 'none';
        if (pOverview) pOverview.style.display = (tabName === 'overview') ? 'block' : 'none';
        if (pResources) pResources.style.display = (tabName === 'resources') ? 'block' : 'none';
        if (pNotes) pNotes.style.display = (tabName === 'notes') ? 'block' : 'none';
    };

    window.toggleModule = function(headerEl) {
        const list = headerEl.nextElementSibling;
        if (!list) return;
        list.classList.toggle('hidden');
        const chev = headerEl.querySelector('.module-chevron');
        if (chev) {
            chev.textContent = list.classList.contains('hidden') ? '▼' : '▲';
        }
    };

    window.allExpanded = false;
    window.toggleAllModules = function() {
        window.allExpanded = !window.allExpanded;
        document.querySelectorAll('.module-lessons-list').forEach(list => {
            if (window.allExpanded) list.classList.remove('hidden');
            else list.classList.add('hidden');
        });
        const btn = document.getElementById('btn-toggle-all');
        if (btn) btn.textContent = window.allExpanded ? 'Collapse All ∧' : 'Expand All ⌵';
    };

    window.openCheatSheetModal = function() {
        const modal = document.getElementById('modal-cheatsheet');
        if (modal) modal.classList.add('active');
    };
    window.closeCheatSheetModal = function() {
        const modal = document.getElementById('modal-cheatsheet');
        if (modal) modal.classList.remove('active');
    };

    window.downloadCheatSheetPDF = async function() {
        const container = document.querySelector('.modal-body');
        if (!container) return;

        const opt = {
            margin:       [8, 8, 8, 8],
            filename:     `{{ str_replace(' ', '_', $course->title) }}_Quick_Cheat_Sheet.pdf`,
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true, letterRendering: true, backgroundColor: '#f8fafc' },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };

        try {
            if (typeof html2pdf !== 'undefined') {
                await html2pdf().set(opt).from(container).save();
            } else {
                window.print();
            }
        } catch (err) {
            console.error(err);
            alert('PDF download error: ' + err.message);
        }
    };

    // AI LESSON STUDIO LOGIC
    window.lastGeneratedData = null;

    window.openAiLessonModal = function() {
        const modal = document.getElementById('aiLessonModal');
        if (modal) modal.classList.add('active');
    };

    window.closeAiLessonModal = function() {
        const modal = document.getElementById('aiLessonModal');
        if (modal) modal.classList.remove('active');
    };

    window.resetAiForm = function() {
        const formPane = document.getElementById('ai-generator-form-pane');
        const loading = document.getElementById('ai-loading-spinner');
        const preview = document.getElementById('ai-preview-pane');
        if (formPane) formPane.style.display = 'block';
        if (loading) loading.style.display = 'none';
        if (preview) preview.style.display = 'none';
    };

    window.triggerAiLessonGeneration = async function() {
        const module_name = document.getElementById('ai_module_name')?.value;
        const lesson_num = document.getElementById('ai_lesson_num')?.value;
        const topic_title = document.getElementById('ai_topic_title')?.value;
        const custom_prompt = document.getElementById('ai_custom_prompt')?.value;

        if (!topic_title) {
            alert('Please enter a lesson topic title.');
            return;
        }

        const formPane = document.getElementById('ai-generator-form-pane');
        const loading = document.getElementById('ai-loading-spinner');
        if (formPane) formPane.style.display = 'none';
        if (loading) loading.style.display = 'block';

        try {
            const res = await fetch("{{ route('courses.lessons.aiGenerate', $course->course_id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ module_name, lesson_num, topic_title, custom_prompt })
            });

            const data = await res.json();
            if (loading) loading.style.display = 'none';

            if (data.success && data.lesson) {
                window.lastGeneratedData = data.lesson;
                window.lastGeneratedData.module_name = module_name;
                window.lastGeneratedData.lesson_num = lesson_num;
                window.lastGeneratedData.topic_title = topic_title;

                const preview = document.getElementById('ai-preview-pane');
                if (preview) preview.style.display = 'block';
                const previewBox = document.getElementById('ai-preview-content-box');

                if (previewBox) {
                    previewBox.innerHTML = `
                        <div style="margin-bottom: 12px;">
                            <h4 style="font-size: 15px; font-weight: 800; color: #0f172a; margin-bottom: 4px;">${data.lesson.topic_title || topic_title}</h4>
                            <p style="font-size: 12px; color: #64748b;">${data.lesson.concept_summary || ''}</p>
                        </div>
                        <div style="margin-bottom: 12px; font-size: 12px; background: #f8fafc; padding: 10px; border-radius: 8px;">
                            <strong>📖 Notes Preview:</strong>
                            <div style="font-size: 11.5px; color: #334155; margin-top: 4px; max-height: 100px; overflow-y: auto;">
                                ${data.lesson.detailed_notes || 'Detailed content generated'}
                            </div>
                        </div>
                        <div style="margin-bottom: 12px; font-size: 12px; background: #ecfdf5; padding: 10px; border-radius: 8px; color: #065f46;">
                            <strong>📝 Practice MCQs (${(data.lesson.mcqs || []).length} Generated):</strong>
                            ${(data.lesson.mcqs || []).map((m, i) => `<div>Q${i+1}: ${m.question_text} (Ans: ${m.correct_key})</div>`).join('')}
                        </div>
                        <div style="font-size: 12px; background: #eff6ff; padding: 10px; border-radius: 8px; color: #1e40af;">
                            <strong>💻 Coding Sandbox (${data.lesson.coding_challenge?.title || 'Challenge'}):</strong>
                            <div>${data.lesson.coding_challenge?.statement || ''}</div>
                        </div>
                    `;
                }
            } else {
                alert('Generation Error: ' + (data.message || 'Could not generate lesson.'));
                window.resetAiForm();
            }
        } catch (err) {
            console.error('AI Generation Error:', err);
            alert('AI Generation failed: ' + err.message);
            window.resetAiForm();
        }
    };

    window.saveGeneratedAiLesson = async function() {
        if (!window.lastGeneratedData) return;

        const saveBtn = document.getElementById('btn-save-ai');
        if (saveBtn) {
            saveBtn.disabled = true;
            saveBtn.textContent = 'Saving Lesson...';
        }

        try {
            const res = await fetch("{{ route('courses.lessons.aiSave', $course->course_id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(window.lastGeneratedData)
            });

            const data = await res.json();
            if (data.success) {
                window.location.reload();
            } else {
                alert('Save failed: ' + (data.message || 'Unknown error'));
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.textContent = '💾 Save to Curriculum & Publish Lesson';
                }
            }
        } catch (err) {
            console.error('Save error:', err);
            alert('Save failed: ' + err.message);
            if (saveBtn) {
                saveBtn.disabled = false;
                saveBtn.textContent = '💾 Save to Curriculum & Publish Lesson';
            }
        }
    };
</script>
@endsection
