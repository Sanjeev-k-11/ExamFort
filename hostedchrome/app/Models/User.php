<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'id',
        'org_id',
        'student_id',
        'full_name',
        'email',
        'phone',
        'dob',
        'location',
        'college_name',
        'course',
        'stream',
        'batch_years',
        'bio',
        'goal',
        'achievements',
        'interests',
        'profile_completion_pct',
        'exams_enrolled',
        'exams_completed',
        'upcoming_exams_count',
        'average_score',
        'best_score',
        'current_streak_days',
        'max_students_allowed',
        'max_exams_allowed',
        'can_create_exams',
        'can_set_questions',
        'can_manage_lessons',
        'can_manage_courses',
        'can_enroll_students',
        'can_view_results',
        'can_create_teachers',
        'created_by_teacher_id',
        'created_by_principal_id',
        'designation',
        'department',
        'status',
        'password',
        'role',
        'access_code',
        'avatar_url',
        'gemini_api_key',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'max_students_allowed' => 'integer',
        'max_exams_allowed' => 'integer',
        'can_create_exams' => 'boolean',
        'can_set_questions' => 'boolean',
        'can_manage_lessons' => 'boolean',
        'can_manage_courses' => 'boolean',
        'can_enroll_students' => 'boolean',
        'can_view_results' => 'boolean',
        'can_create_teachers' => 'boolean',
        'exams_enrolled' => 'integer',
        'exams_completed' => 'integer',
        'average_score' => 'float',
    ];

    public function getAverageScoreAttribute($value): float
    {
        // Calculate real-time accurate average score if candidate has submissions
        if ($this->exists && $this->isCandidate()) {
            try {
                $realAvg = $this->submissions()->avg('total_score');
                if ($realAvg !== null && $this->submissions()->count() > 0) {
                    return round((float)$realAvg, 1);
                }
            } catch (\Throwable $e) {}
        }
        return (float)($value ?? 0);
    }

    public function getExamsCompletedAttribute($value): int
    {
        // Calculate real-time accurate completed exams count if candidate has submissions
        if ($this->exists && $this->isCandidate()) {
            try {
                $realCount = $this->submissions()->count();
                if ($realCount > 0) {
                    return (int)$realCount;
                }
            } catch (\Throwable $e) {}
        }
        return (int)($value ?? 0);
    }

    public function isAdmin(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['ADMIN', 'SUPERADMIN']);
    }

    public function isPrincipal(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['PRINCIPAL', 'DEAN', 'DIRECTOR']);
    }

    public function isTeacher(): bool
    {
        return in_array(strtoupper($this->role ?? ''), ['PROCTOR', 'TEACHER', 'FACULTY']);
    }

    public function isCandidate(): bool
    {
        return strtoupper($this->role ?? '') === 'CANDIDATE';
    }

    // Permission Checkers (Principals and Admins always have full permissions)
    public function canCreateExams(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_create_exams ?? true);
    }

    public function canSetQuestions(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_set_questions ?? true);
    }

    public function canManageLessons($courseId = null): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        if (!(bool)($this->can_manage_lessons ?? true)) return false;
        if ($courseId) {
            $assignment = CourseTeacherAssignment::where('teacher_id', $this->id)->where('course_id', $courseId)->first();
            if ($assignment) {
                return (bool)$assignment->can_add_lessons;
            }
        }
        return true;
    }

    public function canEditModules($courseId = null): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        if (!(bool)($this->can_manage_lessons ?? true)) return false;
        if ($courseId) {
            $assignment = CourseTeacherAssignment::where('teacher_id', $this->id)->where('course_id', $courseId)->first();
            if ($assignment) {
                return (bool)$assignment->can_edit_modules;
            }
        }
        return true;
    }

    public function canUploadPdf($courseId = null): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        if (!(bool)($this->can_manage_lessons ?? true)) return false;
        if ($courseId) {
            $assignment = CourseTeacherAssignment::where('teacher_id', $this->id)->where('course_id', $courseId)->first();
            if ($assignment) {
                return (bool)$assignment->can_upload_pdf;
            }
        }
        return true;
    }

    public function canManageMcqs($courseId = null): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        if (!(bool)($this->can_set_questions ?? true)) return false;
        if ($courseId) {
            $assignment = CourseTeacherAssignment::where('teacher_id', $this->id)->where('course_id', $courseId)->first();
            if ($assignment) {
                return (bool)$assignment->can_manage_mcqs;
            }
        }
        return true;
    }

    public function canManageCoding($courseId = null): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        if (!(bool)($this->can_set_questions ?? true)) return false;
        if ($courseId) {
            $assignment = CourseTeacherAssignment::where('teacher_id', $this->id)->where('course_id', $courseId)->first();
            if ($assignment) {
                return (bool)$assignment->can_manage_coding;
            }
        }
        return true;
    }

    public function canManageCourses(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_manage_courses ?? false);
    }

    public function canEnrollStudents(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_enroll_students ?? true);
    }

    public function canViewResults(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_view_results ?? true);
    }

    public function canCreateTeachers(): bool
    {
        if ($this->isAdmin() || $this->isPrincipal()) return true;
        if (!$this->isTeacher()) return false;
        return (bool)($this->can_create_teachers ?? false);
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class, 'org_id', 'id');
    }

    // For Principal: Teachers created by this Principal
    public function createdTeachers()
    {
        return $this->hasMany(User::class, 'created_by_principal_id', 'id')->whereIn('role', ['PROCTOR', 'TEACHER']);
    }

    // For Teacher: Principal who manages this teacher
    public function principal()
    {
        return $this->belongsTo(User::class, 'created_by_principal_id', 'id');
    }

    // For Teacher: Courses assigned to this teacher by Principal
    public function assignedCourses()
    {
        return $this->belongsToMany(Course::class, 'course_teacher_assignments', 'teacher_id', 'course_id', 'id', 'course_id');
    }

    // For Student: Multi-Subject Teacher Assignments
    public function subjectTeachers()
    {
        return $this->hasMany(StudentSubjectTeacher::class, 'student_id', 'id')->with(['teacher', 'course']);
    }

    // For Teacher: Multi-Subject Students Assigned
    public function subjectStudents()
    {
        return $this->hasMany(StudentSubjectTeacher::class, 'teacher_id', 'id')->with(['student', 'course']);
    }

    // Teacher's assigned students (legacy + direct)
    public function students()
    {
        return $this->hasMany(User::class, 'created_by_teacher_id', 'id')->where('role', 'CANDIDATE');
    }

    // Teacher's conducted exams
    public function createdExams()
    {
        return $this->hasMany(Exam::class, 'created_by_teacher_id', 'id');
    }

    // Student's managing teacher
    public function teacher()
    {
        return $this->belongsTo(User::class, 'created_by_teacher_id', 'id');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'candidate_id', 'id');
    }

    public function violations()
    {
        return $this->hasMany(Violation::class, 'candidate_id', 'id');
    }

    public function drafts()
    {
        return $this->hasMany(CandidateDraft::class, 'candidate_id', 'id');
    }

    public function activities()
    {
        return $this->hasMany(StudentActivity::class, 'student_id', 'id');
    }
}
