@extends('layouts.admin')

@section('title', 'Faculty Profile: ' . $teacher->full_name)
@section('breadcrumb', 'Teacher Profile')

@section('content')
<div style="margin-bottom: 24px;">
    <a href="{{ route('teachers.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
        <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
        <span>Back to Faculty Directory</span>
    </a>
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Faculty Profile & Quota Audit</h1>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('teachers.edit', $teacher->id) }}" class="quick-action-btn secondary">
                <i data-lucide="edit" style="width: 16px; height: 16px;"></i>
                <span>Edit Profile / Quotas</span>
            </a>
            <form action="{{ route('teachers.destroy', $teacher->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this faculty account?');">
                @csrf
                @method('DELETE')
                <button type="submit" class="quick-action-btn danger">
                    <i data-lucide="trash-2" style="width: 16px; height: 16px;"></i>
                    <span>Delete Account</span>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Teacher Banner Card -->
<div class="glass-card" style="margin-bottom: 28px;">
    <div style="display: flex; align-items: flex-start; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
        <div style="display: flex; gap: 20px; align-items: center;">
            <div style="width: 72px; height: 72px; border-radius: 16px; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-size: 28px; font-weight: 800;">
                {{ substr($teacher->full_name, 0, 1) }}
            </div>
            <div>
                <div style="display: flex; align-items: center; gap: 10px;">
                    <h2 style="font-size: 22px; font-weight: 800; color: #fff;">{{ $teacher->full_name }}</h2>
                    <span class="status-pill {{ strtolower($teacher->status ?? 'active') }}">{{ $teacher->status ?? 'ACTIVE' }}</span>
                </div>
                <div style="font-size: 13px; color: #818cf8; font-weight: 600; margin-top: 4px;">
                    {{ $teacher->designation }} &bull; {{ $teacher->department }}
                </div>
                <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
                    College: <strong style="color: #cbd5e1;">{{ $teacher->college_name }}</strong>
                </div>
            </div>
        </div>

        <div style="display: flex; gap: 24px; background: rgba(0,0,0,0.25); padding: 14px 24px; border-radius: var(--radius-md); border: 1px solid var(--border-color);">
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Email Address</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff;">{{ $teacher->email }}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Phone Number</div>
                <div style="font-size: 13px; font-weight: 600; color: #fff;">{{ $teacher->phone ?? 'N/A' }}</div>
            </div>
            <div>
                <div style="font-size: 11px; color: var(--text-muted);">Proctor PIN</div>
                <div class="mono" style="font-size: 13px; font-weight: 700; color: #34d399;">{{ $teacher->access_code ?? '888888' }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Quota Limits Bar -->
<div class="metrics-grid" style="margin-bottom: 24px;">
    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #6366f1, #3b82f6); --accent-color: #818cf8;">
        <div class="metric-icon-box">
            <i data-lucide="users" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $teacher->students->count() }} / {{ $teacher->max_students_allowed ?? 100 }}</div>
            <div class="metric-label">Enrolled Students Quota</div>
        </div>
    </div>

    <div class="metric-card" style="--accent-gradient: linear-gradient(90deg, #10b981, #06b6d4); --accent-color: #34d399;">
        <div class="metric-icon-box" style="color: #34d399;">
            <i data-lucide="calendar" style="width: 26px; height: 26px;"></i>
        </div>
        <div class="metric-info">
            <div class="metric-value">{{ $teacher->createdExams->count() }} / {{ $teacher->max_exams_allowed ?? 10 }}</div>
            <div class="metric-label">Conducted Exams Quota</div>
        </div>
    </div>
</div>

<!-- Principal Granular Permissions Matrix -->
<div class="glass-card" style="margin-bottom: 28px;">
    <div class="card-header-flex" style="margin-bottom: 16px;">
        <div class="card-title">
            <i data-lucide="shield-check" style="color: #34d399; width: 22px; height: 22px;"></i>
            <span>Faculty Authority & Permissions Matrix</span>
        </div>
        <span style="font-size: 12px; color: var(--text-muted);">
            Principal Control: Manage what Prof. {{ $teacher->full_name }} can author, conduct & delegate.
        </span>
    </div>

    <form action="{{ route('teachers.updatePermissions', $teacher->id) }}" method="POST">
        @csrf
        
        <!-- SECTION 1: GLOBAL FACULTY PRIVILEGES -->
        <div style="font-size: 13px; font-weight: 700; color: #818cf8; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
            <span>1. Core Institutional Privileges</span>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 28px;">
            
            <!-- 1. Exam Create -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_create_exams ?? true) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>📋 Exam Creation</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_create_exams" value="1" {{ ($teacher->can_create_exams ?? true) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_create_exams ?? true) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_create_exams ?? true) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Permission to author, configure timing, and schedule online examinations.
                </div>
            </div>

            <!-- 2. Set Questions -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_set_questions ?? true) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>📝 Set Questions & AI Generator</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_set_questions" value="1" {{ ($teacher->can_set_questions ?? true) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_set_questions ?? true) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_set_questions ?? true) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Permission to author MCQs, coding challenges, rubrics, & run AI question generator.
                </div>
            </div>

            <!-- 3. Lessons & PDFs Global -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_manage_lessons ?? true) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>📖 Module & Lesson Management</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_manage_lessons" value="1" {{ ($teacher->can_manage_lessons ?? true) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_manage_lessons ?? true) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_manage_lessons ?? true) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Overall right to author topics, module content, theory notes, and PDF courseware.
                </div>
            </div>

            <!-- 4. Course Authoring -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_manage_courses ?? false) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>🎓 Full Course Creation</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_manage_courses" value="1" {{ ($teacher->can_manage_courses ?? false) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_manage_courses ?? false) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_manage_courses ?? false) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Permission to add brand new courses, syllabus tracks, and curriculum subjects.
                </div>
            </div>

            <!-- 5. Register Faculty / Teachers -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_create_teachers ?? false) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>🏛️ Register Department Faculty</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_create_teachers" value="1" {{ ($teacher->can_create_teachers ?? false) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_create_teachers ?? false) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_create_teachers ?? false) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Allows this HOD / Senior Professor to register and add new faculty & proctors.
                </div>
            </div>

            <!-- 6. Student Enrollment -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_enroll_students ?? true) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>👥 Student Enrollment & PINs</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_enroll_students" value="1" {{ ($teacher->can_enroll_students ?? true) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_enroll_students ?? true) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_enroll_students ?? true) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Permission to add candidates, register student rosters, and generate exam PINs.
                </div>
            </div>

            <!-- 7. Results & Evaluation -->
            <div style="background: rgba(0,0,0,0.25); border: 1px solid {{ ($teacher->can_view_results ?? true) ? 'rgba(99, 102, 241, 0.4)' : 'var(--border-color)' }}; border-radius: var(--radius-md); padding: 16px;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                    <span style="font-size: 14px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                        <span>📊 Evaluate & Publish Results</span>
                    </span>
                    <label style="position: relative; display: inline-block; width: 44px; height: 24px; cursor: pointer;">
                        <input type="checkbox" name="can_view_results" value="1" {{ ($teacher->can_view_results ?? true) ? 'checked' : '' }} style="opacity: 0; width: 0; height: 0;" onchange="this.nextElementSibling.style.background = this.checked ? '#6366f1' : '#334155'; this.nextElementSibling.querySelector('span').style.transform = this.checked ? 'translateX(20px)' : 'translateX(0)';">
                        <div style="position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background: {{ ($teacher->can_view_results ?? true) ? '#6366f1' : '#334155' }}; border-radius: 24px; transition: 0.3s;">
                            <span style="position: absolute; content: ''; height: 18px; width: 18px; left: 3px; bottom: 3px; background-color: white; border-radius: 50%; transition: 0.3s; transform: {{ ($teacher->can_view_results ?? true) ? 'translateX(20px)' : 'translateX(0)' }};"></span>
                        </div>
                    </label>
                </div>
                <div style="font-size: 12px; color: var(--text-secondary);">
                    Permission to score descriptive submissions and publish student performance ranks.
                </div>
            </div>

        </div>

        <!-- SECTION 2: SPECIFIC COURSE & MODULE AUTHORING DELEGATIONS -->
        <div style="margin-top: 10px; margin-bottom: 20px; border-top: 1px dashed rgba(255,255,255,0.1); padding-top: 20px;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px;">
                <div style="font-size: 13px; font-weight: 700; color: #38bdf8; text-transform: uppercase; letter-spacing: 0.05em; display: flex; align-items: center; gap: 8px;">
                    <span>2. Granular Course & Module Authoring Rights</span>
                </div>
                <span style="font-size: 12px; color: var(--text-muted);">
                    Select which specific courses this professor has permission to add lessons, edit syllabus, & upload PDFs for.
                </span>
            </div>

            <div class="table-responsive" style="background: rgba(0,0,0,0.2); border-radius: var(--radius-md); border: 1px solid var(--border-color);">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th style="width: 28%;">Course Track</th>
                            <th style="text-align: center;">Assigned</th>
                            <th style="text-align: center;">Add Lessons</th>
                            <th style="text-align: center;">Edit Modules</th>
                            <th style="text-align: center;">Upload PDFs</th>
                            <th style="text-align: center;">Manage MCQs</th>
                            <th style="text-align: center;">Manage Coding</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($allCourses as $c)
                            @php
                                $assign = $assignedCourses[$c->course_id] ?? null;
                                $isAssigned = $assign ? true : false;
                            @endphp
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <span style="font-size: 18px;">{{ $c->icon ?? '📘' }}</span>
                                        <div>
                                            <div style="font-weight: 700; color: #fff;">{{ $c->title }}</div>
                                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $c->course_id }} &bull; {{ $c->level ?? 'Intermediate' }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][assigned]" value="1" {{ $isAssigned ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #6366f1; cursor: pointer;">
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][can_add_lessons]" value="1" {{ ($assign && $assign->can_add_lessons) || !$assign ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #10b981; cursor: pointer;">
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][can_edit_modules]" value="1" {{ ($assign && $assign->can_edit_modules) || !$assign ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #10b981; cursor: pointer;">
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][can_upload_pdf]" value="1" {{ ($assign && $assign->can_upload_pdf) || !$assign ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #10b981; cursor: pointer;">
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][can_manage_mcqs]" value="1" {{ ($assign && $assign->can_manage_mcqs) || !$assign ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #3b82f6; cursor: pointer;">
                                </td>
                                <td style="text-align: center;">
                                    <input type="checkbox" name="courses[{{ $c->course_id }}][can_manage_coding]" value="1" {{ ($assign && $assign->can_manage_coding) || !$assign ? 'checked' : '' }} style="width: 16px; height: 16px; accent-color: #f59e0b; cursor: pointer;">
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div style="display: flex; justify-content: flex-end; margin-top: 16px;">
            <button type="submit" class="quick-action-btn" style="background: linear-gradient(135deg, #10b981, #059669); padding: 10px 24px; font-size: 14px;">
                <i data-lucide="check" style="width: 18px; height: 18px;"></i>
                <span>Save Delegations & Permissions for Prof. {{ $teacher->full_name }}</span>
            </button>
        </div>
    </form>
</div>

<!-- Assigned Students List -->
<div class="glass-card" style="margin-bottom: 28px;">
    <div class="card-header-flex">
        <div class="card-title">
            <i data-lucide="users" style="color: #6366f1; width: 20px; height: 20px;"></i>
            <span>Students Enrolled / Department Candidates ({{ $students->count() }})</span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="custom-table">
            <thead>
                <tr>
                    <th>Student Name & ID</th>
                    <th>Course / Stream</th>
                    <th>Email</th>
                    <th>Average Score</th>
                    <th>Completed Exams</th>
                    <th style="text-align: right;">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $cand)
                    <tr>
                        <td>
                            <div style="font-weight: 700; color: #fff;">{{ $cand->full_name }}</div>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $cand->student_id }}</div>
                        </td>
                        <td>{{ $cand->course }} ({{ $cand->stream }})</td>
                        <td>{{ $cand->email }}</td>
                        <td>
                            @php
                                $scoreVal = (float)$cand->average_score;
                                $scoreColor = $scoreVal >= 75 ? '#34d399' : ($scoreVal >= 50 ? '#fbbf24' : '#f87171');
                            @endphp
                            <strong style="color: {{ $scoreColor }};">{{ number_format($scoreVal, 1) }}%</strong>
                        </td>
                        <td>
                            <span class="mono" style="font-weight: 600; color: #cbd5e1;">{{ $cand->exams_completed }}</span>
                        </td>
                        <td style="text-align: right;">
                            <a href="{{ route('candidates.show', $cand->id) }}" class="quick-action-btn secondary" style="font-size: 11px; padding: 4px 8px;">
                                Profile &rarr;
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 24px; color: var(--text-muted);">
                            No students registered under this faculty account yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
