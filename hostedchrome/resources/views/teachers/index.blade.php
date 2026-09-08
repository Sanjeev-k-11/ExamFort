@extends('layouts.admin')

@section('title', 'Faculty & Teacher Management')
@section('breadcrumb', 'Teachers & Faculty Directory')

@section('content')
<div style="margin-bottom: 28px;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
        <div>
            <h1 style="font-size: 26px; font-weight: 900; color: #0f172a; letter-spacing: -0.5px;">Faculty & Teacher Accounts</h1>
            <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
                Manage verified professors, college assignments, student capacity quotas, and examination permissions.
            </p>
        </div>
        @if(in_array(session('auth_user_role'), ['ADMIN', 'SUPERADMIN', 'PRINCIPAL', 'DEAN', 'DIRECTOR']))
            <a href="{{ route('teachers.create') }}" class="quick-action-btn" style="background: linear-gradient(135deg, #4f46e5, #3b82f6); box-shadow: 0 4px 16px rgba(79, 70, 229, 0.35);">
                <i data-lucide="user-plus" style="width: 16px; height: 16px;"></i>
                <span>Create Faculty Account</span>
            </a>
        @endif
    </div>
</div>

<!-- Search & Filter Bar -->
<div class="glass-card" style="margin-bottom: 24px; padding: 16px 20px;">
    <form action="{{ route('teachers.index') }}" method="GET" style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
        <div style="flex: 1; min-width: 260px; position: relative;">
            <i data-lucide="search" style="position: absolute; left: 14px; top: 12px; width: 16px; height: 16px; color: var(--text-muted);"></i>
            <input type="text" name="search" class="form-control" style="padding-left: 40px;" placeholder="Search by name, college, department, email..." value="{{ request('search') }}">
        </div>
        <button type="submit" class="quick-action-btn secondary">Filter</button>
        @if(request('search'))
            <a href="{{ route('teachers.index') }}" class="quick-action-btn secondary" style="color: #dc2626;">Clear</a>
        @endif
    </form>
</div>

<!-- Teachers Grid -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 20px;">
    @forelse($teachers as $teacher)
        <div class="glass-card" style="display: flex; flex-direction: column; justify-content: space-between; position: relative; background: rgba(255, 255, 255, 0.9);">
            <div>
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 12px;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 44px; height: 44px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);">
                            {{ substr($teacher->full_name, 0, 1) }}
                        </div>
                        <div>
                            <h3 style="font-size: 16px; font-weight: 800; color: #0f172a;">{{ $teacher->full_name }}</h3>
                            <div class="mono" style="font-size: 11px; color: var(--text-muted);">{{ $teacher->student_id }}</div>
                        </div>
                    </div>
                    <span class="status-pill {{ ($teacher->status ?? 'ACTIVE') === 'ACTIVE' ? 'active' : 'danger' }}">
                        {{ $teacher->status ?? 'ACTIVE' }}
                    </span>
                </div>

                <div style="font-size: 13.5px; color: #1e293b; margin-bottom: 4px; font-weight: 700;">
                    {{ $teacher->college_name }}
                </div>
                <div style="font-size: 12px; color: var(--text-secondary); margin-bottom: 16px;">
                    {{ $teacher->designation ?? 'Faculty Professor' }} &bull; {{ $teacher->department ?? 'General' }}
                </div>

                <!-- Quota Allocation Meter -->
                <div style="background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); padding: 12px; margin-bottom: 14px;">
                    <div style="display: flex; justify-content: space-between; font-size: 12px; margin-bottom: 6px;">
                        <span style="color: var(--text-secondary); font-weight: 600;">Student Capacity Limit</span>
                        <strong style="color: #0f172a;">{{ $teacher->students->count() }} / {{ $teacher->max_students_allowed ?? 100 }}</strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px;">
                        <span style="color: var(--text-secondary); font-weight: 600;">Exam Scheduling Quota</span>
                        <strong style="color: #4f46e5;">{{ $teacher->createdExams->count() }} / {{ $teacher->max_exams_allowed ?? 10 }}</strong>
                    </div>
                </div>

                <!-- Feature Permission Badges -->
                <div style="margin-bottom: 14px;">
                    <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px;">
                        Principal Granted Permissions
                    </div>
                    <div style="display: flex; flex-wrap: wrap; gap: 6px;">
                        <span class="status-pill {{ ($teacher->can_create_exams ?? true) ? 'active' : '' }}" style="font-size: 11px; padding: 3px 8px; {{ !($teacher->can_create_exams ?? true) ? 'background: rgba(241, 245, 249, 0.8); color: #64748b; border: 1px solid #cbd5e1;' : '' }}">
                            📋 Exams: {{ ($teacher->can_create_exams ?? true) ? 'YES' : 'NO' }}
                        </span>
                        <span class="status-pill {{ ($teacher->can_set_questions ?? true) ? 'active' : '' }}" style="font-size: 11px; padding: 3px 8px; {{ !($teacher->can_set_questions ?? true) ? 'background: rgba(241, 245, 249, 0.8); color: #64748b; border: 1px solid #cbd5e1;' : '' }}">
                            📝 Questions: {{ ($teacher->can_set_questions ?? true) ? 'YES' : 'NO' }}
                        </span>
                        <span class="status-pill {{ ($teacher->can_manage_lessons ?? true) ? 'active' : '' }}" style="font-size: 11px; padding: 3px 8px; {{ !($teacher->can_manage_lessons ?? true) ? 'background: rgba(241, 245, 249, 0.8); color: #64748b; border: 1px solid #cbd5e1;' : '' }}">
                            📖 Lessons: {{ ($teacher->can_manage_lessons ?? true) ? 'YES' : 'NO' }}
                        </span>
                        <span class="status-pill {{ ($teacher->can_manage_courses ?? false) ? 'active' : '' }}" style="font-size: 11px; padding: 3px 8px; {{ !($teacher->can_manage_courses ?? false) ? 'background: rgba(241, 245, 249, 0.8); color: #64748b; border: 1px solid #cbd5e1;' : '' }}">
                            🎓 Courses: {{ ($teacher->can_manage_courses ?? false) ? 'YES' : 'NO' }}
                        </span>
                    </div>
                </div>

                <div style="font-size: 12px; color: var(--text-muted); line-height: 1.5;">
                    <div>Email: <span style="color: #334155; font-weight: 600;">{{ $teacher->email }}</span></div>
                    <div>Phone: <span style="color: #334155; font-weight: 600;">{{ $teacher->phone ?? 'N/A' }}</span></div>
                </div>
            </div>

            <div style="display: flex; gap: 8px; margin-top: 18px; padding-top: 14px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('teachers.show', $teacher->id) }}" class="quick-action-btn secondary" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px;">
                    <i data-lucide="eye" style="width: 14px; height: 14px; color: #4f46e5;"></i>
                    <span>Profile & Roster</span>
                </a>
                <button type="button" onclick="document.getElementById('perm-modal-{{ $teacher->id }}').style.display='flex'" class="quick-action-btn" style="flex: 1; justify-content: center; font-size: 12px; padding: 8px; background: linear-gradient(135deg, #4f46e5, #4338ca);">
                    <i data-lucide="shield" style="width: 14px; height: 14px;"></i>
                    <span>Permissions</span>
                </button>
            </div>

            <!-- Fast Permissions Modal -->
            <div id="perm-modal-{{ $teacher->id }}" class="modal-overlay">
                <div class="modal-box" style="max-width: 520px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                        <div>
                            <h3 style="font-size: 18px; font-weight: 800; color: #0f172a;">Faculty Permissions Control</h3>
                            <div style="font-size: 12px; color: #4f46e5; font-weight: 700; margin-top: 2px;">{{ $teacher->full_name }} ({{ $teacher->student_id }})</div>
                        </div>
                        <button type="button" onclick="document.getElementById('perm-modal-{{ $teacher->id }}').style.display='none'" style="background: none; border: none; color: #64748b; font-size: 24px; cursor: pointer;">&times;</button>
                    </div>

                    <form action="{{ route('teachers.updatePermissions', $teacher->id) }}" method="POST">
                        @csrf
                        <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">📋 Create & Schedule Exams</span>
                                <input type="checkbox" name="can_create_exams" value="1" {{ ($teacher->can_create_exams ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>

                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">📝 Set Questions & AI Generator</span>
                                <input type="checkbox" name="can_set_questions" value="1" {{ ($teacher->can_set_questions ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>

                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">📖 Author Lessons, Notes & PDFs</span>
                                <input type="checkbox" name="can_manage_lessons" value="1" {{ ($teacher->can_manage_lessons ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>

                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">🎓 Course Track Authoring</span>
                                <input type="checkbox" name="can_manage_courses" value="1" {{ ($teacher->can_manage_courses ?? false) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>

                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">👥 Enroll Students & Issue PINs</span>
                                <input type="checkbox" name="can_enroll_students" value="1" {{ ($teacher->can_enroll_students ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>

                            <label style="display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; background: rgba(248, 250, 252, 0.95); border: 1px solid var(--border-color); border-radius: var(--radius-sm); cursor: pointer;">
                                <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">📊 Evaluate & Publish Results</span>
                                <input type="checkbox" name="can_view_results" value="1" {{ ($teacher->can_view_results ?? true) ? 'checked' : '' }} style="width: 18px; height: 18px; accent-color: #4f46e5;">
                            </label>
                        </div>

                        <div style="display: flex; gap: 10px; justify-content: flex-end;">
                            <button type="button" onclick="document.getElementById('perm-modal-{{ $teacher->id }}').style.display='none'" class="quick-action-btn secondary" style="font-size: 12px; padding: 8px 16px;">Cancel</button>
                            <button type="submit" class="quick-action-btn" style="font-size: 12px; padding: 8px 16px; background: linear-gradient(135deg, #10b981, #059669);">
                                Save Permissions
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div style="grid-column: 1/-1; text-align: center; padding: 48px; color: var(--text-muted);">
            <i data-lucide="user-x" style="width: 36px; height: 36px; margin-bottom: 8px; color: #94a3b8;"></i>
            <p style="font-size: 15px; font-weight: 700; color: #0f172a;">No faculty accounts registered</p>
            <p style="font-size: 13px; margin-top: 4px;">Click "Create Faculty Account" to add professors and allocate quotas.</p>
        </div>
    @endforelse
</div>

<div style="margin-top: 24px;">
    {{ $teachers->links() }}
</div>
@endsection
