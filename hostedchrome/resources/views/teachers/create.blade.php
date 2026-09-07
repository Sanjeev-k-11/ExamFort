@extends('layouts.admin')

@section('title', 'Create Faculty Account')
@section('breadcrumb', 'Add New Teacher')

@section('content')
<div style="max-width: 860px; margin: 0 auto;">
    <div style="margin-bottom: 24px;">
        <a href="{{ route('teachers.index') }}" style="color: #818cf8; text-decoration: none; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; margin-bottom: 8px;">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i>
            <span>Back to Faculty Directory</span>
        </a>
        <h1 style="font-size: 26px; font-weight: 800; color: #fff;">Register Verified Teacher / Proctor</h1>
        <p style="color: var(--text-secondary); font-size: 14px; margin-top: 4px;">
            Assign college affiliation, faculty credentials, student capacity quotas, and exam scheduling limits.
        </p>
    </div>

    <div class="glass-card">
        <form action="{{ route('teachers.store') }}" method="POST">
            @csrf

            <div style="font-size: 15px; font-weight: 700; color: #818cf8; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="user-check" style="width: 18px; height: 18px;"></i>
                <span>1. Personal & Faculty Identification</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name *</label>
                    <input type="text" name="full_name" class="form-control" placeholder="Prof. Rajesh Sharma" value="{{ old('full_name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Faculty / Employee ID *</label>
                    <input type="text" name="faculty_id" class="form-control" placeholder="FAC-2026-CS01" value="{{ old('faculty_id') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">System User ID (Unique Key) *</label>
                    <input type="text" name="id" class="form-control" placeholder="TEACH_NITP_01" value="{{ old('id') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Designation / Role *</label>
                    <input type="text" name="designation" class="form-control" placeholder="Associate Professor / HOD" value="{{ old('designation', 'Associate Professor') }}" required>
                </div>
            </div>

            <div style="font-size: 15px; font-weight: 700; color: #818cf8; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="building" style="width: 18px; height: 18px;"></i>
                <span>2. College & Department Details</span>
            </div>

            <div style="display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">College / Institute Name *</label>
                    <input type="text" name="college_name" class="form-control" placeholder="National Institute of Technology (NIT Patna)" value="{{ old('college_name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Department / Stream *</label>
                    <input type="text" name="department" class="form-control" placeholder="Computer Science & Engineering" value="{{ old('department', 'Computer Science & Engineering') }}" required>
                </div>
            </div>

            <div style="font-size: 15px; font-weight: 700; color: #818cf8; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="sliders" style="width: 18px; height: 18px;"></i>
                <span>3. Quota Limits & Capacity</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Max Students Allowed (Student Quota) *</label>
                    <input type="number" name="max_students_allowed" class="form-control" min="1" max="10000" placeholder="150" value="{{ old('max_students_allowed', 150) }}" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Maximum number of candidates this teacher is authorized to register.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Max Exams Allowed (Exam Quota) *</label>
                    <input type="number" name="max_exams_allowed" class="form-control" min="1" max="500" placeholder="10" value="{{ old('max_exams_allowed', 10) }}" required>
                    <small style="color: var(--text-muted); font-size: 11px; margin-top: 4px; display: block;">Maximum number of examinations this teacher can schedule.</small>
                </div>
            </div>

            <!-- Granular Permissions Matrix by Principal -->
            <div style="font-size: 15px; font-weight: 700; color: #34d399; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; justify-content: space-between;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i data-lucide="shield-check" style="width: 18px; height: 18px;"></i>
                    <span>4. Faculty Authority & Feature Permissions (Principal Granted)</span>
                </div>
                <span style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Toggle access per teacher</span>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 14px; margin-bottom: 24px;">
                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_create_exams" value="1" {{ old('can_create_exams', '1') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📋 Create & Schedule Exams</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to author new exam papers and test schedules.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_set_questions" value="1" {{ old('can_set_questions', '1') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📝 Set Questions & AI Generator</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to set MCQ, coding challenges, & AI questions.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_manage_lessons" value="1" {{ old('can_manage_lessons', '1') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📖 Author Lessons & PDF Notes</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to write lesson notes, theory materials & notes.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_manage_courses" value="1" {{ old('can_manage_courses', '0') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>🎓 Course Track Authoring</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to create full curriculum tracks & courses.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_enroll_students" value="1" {{ old('can_enroll_students', '1') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>👥 Enroll Students & PINs</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to register candidates and issue access codes.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_view_results" value="1" {{ old('can_view_results', '1') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>📊 Evaluate & Publish Results</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize to grade submissions and view audit scores.</div>
                    </div>
                </label>

                <label style="display: flex; align-items: flex-start; gap: 12px; padding: 14px; background: rgba(255,255,255,0.03); border: 1px solid var(--border-color); border-radius: var(--radius-md); cursor: pointer; transition: all 0.2s;">
                    <input type="checkbox" name="can_create_teachers" value="1" {{ old('can_create_teachers', '0') ? 'checked' : '' }} style="margin-top: 3px; accent-color: #6366f1; width: 18px; height: 18px;">
                    <div>
                        <div style="font-size: 13px; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 6px;">
                            <span>🏛️ Register Department Faculty</span>
                        </div>
                        <div style="font-size: 11px; color: var(--text-secondary); margin-top: 3px;">Authorize this HOD to register and create new faculty & proctors.</div>
                    </div>
                </label>
            </div>

            <div style="font-size: 15px; font-weight: 700; color: #818cf8; border-bottom: 1px solid var(--border-color); padding-bottom: 8px; margin: 24px 0 20px; display: flex; align-items: center; gap: 8px;">
                <i data-lucide="lock" style="width: 18px; height: 18px;"></i>
                <span>5. Contact & Security Credentials</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Email Address *</label>
                    <input type="email" name="email" class="form-control" placeholder="teacher@nitp.ac.in" value="{{ old('email') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Phone Number *</label>
                    <input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" value="{{ old('phone') }}" required>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Login Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Access / Proctor PIN Code</label>
                    <input type="text" name="access_code" class="form-control" placeholder="888888" value="{{ old('access_code', '888888') }}">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bio / Academic Specialization</label>
                <textarea name="bio" class="form-control" placeholder="Specialization in Algorithms, Machine Learning, and Network Security...">{{ old('bio') }}</textarea>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end; margin-top: 32px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                <a href="{{ route('teachers.index') }}" class="quick-action-btn secondary">Cancel</a>
                <button type="submit" class="quick-action-btn">
                    <i data-lucide="check" style="width: 16px; height: 16px;"></i>
                    <span>Create & Assign Teacher</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
