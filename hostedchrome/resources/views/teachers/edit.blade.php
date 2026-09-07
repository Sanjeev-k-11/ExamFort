@extends('layouts.admin')

@section('title', 'Edit Faculty: ' . $teacher->full_name)
@section('breadcrumb', 'Edit Teacher')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('teachers.show', $teacher->id) }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Profile</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Edit Faculty & Quota Permissions</h1>
    </div>

    <div class="glass-card">
        <form action="{{ route('teachers.update', $teacher->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $teacher->full_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Designation / Academic Role *</label>
                    <input type="text" name="designation" class="form-control" value="{{ old('designation', $teacher->designation) }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">College / Institute Name *</label>
                    <input type="text" name="college_name" class="form-control" value="{{ old('college_name', $teacher->college_name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Department *</label>
                    <input type="text" name="department" class="form-control" value="{{ old('department', $teacher->department) }}" required>
                </div>
            </div>

            <div style="font-size: 15px; font-weight: 700; color: #818cf8; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                <span>Quota Allocations</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Max Students Allowed (Quota) *</label>
                    <input type="number" name="max_students_allowed" class="form-control" min="1" max="10000" value="{{ old('max_students_allowed', $teacher->max_students_allowed ?? 100) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Exams Allowed (Quota) *</label>
                    <input type="number" name="max_exams_allowed" class="form-control" min="1" max="500" value="{{ old('max_exams_allowed', $teacher->max_exams_allowed ?? 10) }}" required>
                </div>
            </div>

            <!-- Granular Permissions Matrix by Principal -->
            <div style="font-size: 15px; font-weight: 700; color: #34d399; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                    <span>Faculty Authority & Feature Permissions (Principal Granted)</span>
                </div>
                <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Toggle access per teacher</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-bottom: 24px;">
                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_create_exams" value="1" {{ old('can_create_exams', $teacher->can_create_exams ?? true) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📋 Create & Schedule Exams</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to author new exam papers and test schedules.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_set_questions" value="1" {{ old('can_set_questions', $teacher->can_set_questions ?? true) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📝 Set Questions & AI Generator</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to set MCQ, coding challenges, & AI questions.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_manage_lessons" value="1" {{ old('can_manage_lessons', $teacher->can_manage_lessons ?? true) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📖 Author Lessons & PDF Notes</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to write lesson notes, theory materials & notes.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_manage_courses" value="1" {{ old('can_manage_courses', $teacher->can_manage_courses ?? false) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>🎓 Course Track Authoring</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to create full curriculum tracks & courses.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_enroll_students" value="1" {{ old('can_enroll_students', $teacher->can_enroll_students ?? true) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>👥 Enroll Students & PINs</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to register candidates and issue access codes.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_view_results" value="1" {{ old('can_view_results', $teacher->can_view_results ?? true) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📊 Evaluate & Publish Results</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to grade submissions and view audit scores.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_create_teachers" value="1" {{ old('can_create_teachers', $teacher->can_create_teachers ?? false) ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>🏛️ Register Department Faculty</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize this HOD to register and create new faculty & proctors.</div>
                    </div>
                </label>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $teacher->email) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone *</label>
                    <input type="text" name="phone" class="form-control" value="{{ old('phone', $teacher->phone) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Account Status *</label>
                    <select name="status" class="form-control" required>
                        <option value="ACTIVE" {{ $teacher->status === 'ACTIVE' ? 'selected' : '' }}>ACTIVE</option>
                        <option value="SUSPENDED" {{ $teacher->status === 'SUSPENDED' ? 'selected' : '' }}>SUSPENDED</option>
                    </select>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Update Password (Leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••">
                </div>

                <div class="form-group">
                    <label class="form-label">Access / Proctor PIN Code</label>
                    <input type="text" name="access_code" class="form-control" value="{{ old('access_code', $teacher->access_code) }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bio & Specialization Notes</label>
                <textarea name="bio" class="form-control">{{ old('bio', $teacher->bio) }}</textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('teachers.show', $teacher->id) }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="save" style="width: 16px; height: 16px;"></i>
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
