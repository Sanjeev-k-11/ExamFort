<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Organization;
use App\Models\User;

class HierarchySeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super Admin Account
        User::updateOrCreate(
            ['email' => 'admin@examfort.com'],
            [
                'id' => 'ADMIN001',
                'student_id' => 'ADMIN001',
                'full_name' => 'Institutional Super Administrator',
                'role' => 'SUPERADMIN',
                'password' => 'admin123',
                'status' => 'ACTIVE',
                'college_name' => 'ExamFort Central Governance',
                'designation' => 'System Chief Administrator',
                'department' => 'Central Administration',
                'max_students_allowed' => 100000,
                'max_exams_allowed' => 5000,
            ]
        );

        // 2. NIT Patna Organization
        $orgNitp = Organization::updateOrCreate(
            ['id' => 'ORG_NITP'],
            [
                'name' => 'National Institute of Technology (NIT Patna)',
                'code' => 'NITP',
                'type' => 'University / Institute of National Importance',
                'email' => 'admin@nitp.ac.in',
                'phone' => '+91 612 237 1715',
                'website' => 'https://www.nitp.ac.in',
                'address' => 'Ashok Rajpath, Patna, Bihar 800005',
                'max_teachers_allowed' => 100,
                'max_students_allowed' => 8000,
                'max_exams_allowed' => 200,
                'status' => 'ACTIVE'
            ]
        );

        // 3. IIT Delhi Organization
        $orgIitd = Organization::updateOrCreate(
            ['id' => 'ORG_IITD'],
            [
                'name' => 'Indian Institute of Technology Delhi (IIT Delhi)',
                'code' => 'IITD',
                'type' => 'Institute of Eminence',
                'email' => 'dean.academics@iitd.ac.in',
                'phone' => '+91 11 2659 7135',
                'website' => 'https://home.iitd.ac.in',
                'address' => 'Hauz Khas, New Delhi 110016',
                'max_teachers_allowed' => 150,
                'max_students_allowed' => 12000,
                'max_exams_allowed' => 300,
                'status' => 'ACTIVE'
            ]
        );

        // 4. Principal for NIT Patna
        $principal = User::updateOrCreate(
            ['email' => 'principal@examfort.com'],
            [
                'id' => 'PRIN001',
                'org_id' => 'ORG_NITP',
                'student_id' => 'PRIN1001',
                'full_name' => 'Dr. P. K. Mishra (Dean & Principal)',
                'phone' => '+91 94310 12345',
                'college_name' => 'National Institute of Technology (NIT Patna)',
                'course' => 'Administration & Academics',
                'stream' => 'Academic Affairs',
                'designation' => 'Principal & Dean of Academic Affairs',
                'department' => 'Office of the Dean',
                'password' => 'principal123',
                'role' => 'PRINCIPAL',
                'access_code' => '777777',
                'status' => 'ACTIVE',
                'max_students_allowed' => 5000,
                'max_exams_allowed' => 150,
            ]
        );

        // 5. Teacher / Proctor for NIT Patna
        User::updateOrCreate(
            ['email' => 'teacher@examfort.com'],
            [
                'id' => 'TEACH001',
                'org_id' => 'ORG_NITP',
                'student_id' => 'FAC101',
                'full_name' => 'Prof. Rajesh Sharma',
                'phone' => '+91 98765 43210',
                'college_name' => 'National Institute of Technology (NIT Patna)',
                'course' => 'Computer Science & Engineering',
                'stream' => 'CSE',
                'designation' => 'Associate Professor & Proctor',
                'department' => 'Computer Science & Engineering',
                'password' => 'teacher123',
                'role' => 'TEACHER',
                'access_code' => '123456',
                'status' => 'ACTIVE',
                'created_by_principal_id' => 'PRIN001',
                'max_students_allowed' => 500,
                'max_exams_allowed' => 50,
            ]
        );

        // 6. Student Candidate: Ankit Kumar (CAND123456)
        User::updateOrCreate(
            ['id' => 'CAND123456'],
            [
                'student_id' => '2201CS01',
                'full_name' => 'Ankit Kumar',
                'email' => 'ankit.kumar@gmail.com',
                'phone' => '+91 98765 43210',
                'org_id' => 'ORG_NITP',
                'dob' => '12 Jan 2003',
                'location' => 'Bihar, India',
                'college_name' => 'National Institute of Technology (NIT Patna)',
                'course' => 'B.Tech',
                'stream' => 'Computer Science & Engineering',
                'batch_years' => '2022 - 2026',
                'bio' => 'Final year CSE undergraduate passionate about full-stack engineering, distributed systems, and competitive programming.',
                'goal' => 'Secure SDE role in Tier-1 Technology Firm',
                'achievements' => 'Rank 1 in College Hackathon 2025; Solved 450+ LeetCode problems',
                'interests' => 'Data Structures, AI, Cloud Architecture',
                'profile_completion_pct' => 85,
                'exams_enrolled' => 12,
                'exams_completed' => 5,
                'upcoming_exams_count' => 3,
                'average_score' => 72,
                'best_score' => 95,
                'current_streak_days' => 7,
                'password' => '123456',
                'role' => 'CANDIDATE',
                'access_code' => '123456',
                'avatar_url' => 'https://res.cloudinary.com/dpkgmrpcx/image/upload/v1788429254/sanjeev_xagdte.jpg',
                'status' => 'ACTIVE',
                'created_by_principal_id' => 'PRIN001',
                'created_by_teacher_id' => 'TEACH001',
                'designation' => 'Student Candidate',
                'department' => 'Computer Science & Engineering',
            ]
        );
    }
}
