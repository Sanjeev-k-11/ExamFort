const { Client } = require('pg');
require('dotenv').config();

const CONNECTION_STRING = process.env.DATABASE_URL || 'postgresql://postgres.leodjnnylkxycarmegzk:Kumar%402004%40h3@aws-0-ap-south-1.pooler.supabase.com:6543/postgres';

async function seedSupabase() {
    console.log('🐘 Connecting to Supabase PostgreSQL...');
    const client = new Client({
        connectionString: CONNECTION_STRING,
        ssl: { rejectUnauthorized: false }
    });

    await client.connect();
    console.log('✅ Connected to Supabase!');

    try {
        // 1. Ensure organizations
        await client.query(`
            INSERT INTO organizations (id, name, code, type, email, phone, max_teachers_allowed, max_students_allowed, max_exams_allowed, status)
            VALUES 
            ('ORG_NITP', 'National Institute of Technology (NIT Patna)', 'NITP', 'Institute of National Importance', 'admin@nitp.ac.in', '+91 612 237 1715', 100, 8000, 200, 'ACTIVE'),
            ('ORG_IITD', 'Indian Institute of Technology Delhi (IIT Delhi)', 'IITD', 'Institute of Eminence', 'dean.academics@iitd.ac.in', '+91 11 2659 7135', 150, 12000, 300, 'ACTIVE')
            ON CONFLICT (id) DO UPDATE SET name=EXCLUDED.name;
        `);
        console.log('✅ Organizations Seeded');

        // 2. Users Data
        const users = [
            // SUPER ADMIN
            {
                id: 'ADMIN001', student_id: 'ADMIN001', full_name: 'Institutional Super Administrator', email: 'admin@examfort.com', phone: '+91 99999 00001',
                dob: '01 Jan 1980', location: 'New Delhi, India', college_name: 'ExamFort Central Governance',
                course: 'Institutional Administration', stream: 'System Administration', batch_years: '2020 - 2030',
                bio: 'System Chief Administrator managing institutional exams, university portals, and global security policies.',
                goal: 'Zero-latency global proctoring ecosystem.', achievements: 'Architected ExamFort Multi-Tiered Proctoring Suite.', interests: 'Distributed Systems, Cloud Architecture, Cyber Security',
                profile_completion_pct: 100, exams_enrolled: 50, exams_completed: 50, upcoming_exams_count: 0,
                average_score: 99, best_score: 100, current_streak_days: 30, password: 'admin123', role: 'SUPERADMIN', access_code: '999999', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: null, created_by_teacher_id: null, designation: 'Chief Technology Administrator', department: 'Central Administration', max_students_allowed: 100000, max_exams_allowed: 5000,
                can_create_exams: 1, can_set_questions: 1, can_manage_lessons: 1, can_manage_courses: 1, can_enroll_students: 1, can_view_results: 1
            },
            // DEMO PRINCIPAL
            {
                id: 'PRIN001', student_id: 'PRIN1001', full_name: 'Dr. P. K. Mishra (Dean & Principal)', email: 'principal@examfort.com', phone: '+91 94310 12345',
                dob: '15 Aug 1968', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'Administration & Academics', stream: 'Academic Affairs', batch_years: 'Faculty',
                bio: 'Principal & Dean overseeing institutional examinations, faculty delegations, and question setting standards.',
                goal: 'Excellence in National Technical Education', achievements: 'Published 40+ IEEE papers', interests: 'AI, Cloud Architecture, Higher Education',
                profile_completion_pct: 100, exams_enrolled: 0, exams_completed: 0, upcoming_exams_count: 0,
                average_score: 100, best_score: 100, current_streak_days: 30, password: 'principal123', role: 'PRINCIPAL', access_code: '777777', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: null, created_by_teacher_id: null, designation: 'Principal & Dean of Academic Affairs', department: 'Office of the Dean', max_students_allowed: 5000, max_exams_allowed: 150,
                can_create_exams: 1, can_set_questions: 1, can_manage_lessons: 1, can_manage_courses: 1, can_enroll_students: 1, can_view_results: 1
            },
            // DEMO TEACHER / PROCTOR
            {
                id: 'TEACH001', student_id: 'FAC101', full_name: 'Prof. Rajesh Sharma (Faculty)', email: 'teacher@examfort.com', phone: '+91 98765 43210',
                dob: '20 May 1980', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'Computer Science & Engineering', stream: 'CSE', batch_years: 'Faculty',
                bio: 'Senior Faculty & Assessment Head with full question authoring and course management privileges.',
                goal: 'Empowering engineering students with core technical excellence', achievements: 'Best Faculty Award 2024', interests: 'Algorithms, Cloud, DBMS',
                profile_completion_pct: 95, exams_enrolled: 0, exams_completed: 0, upcoming_exams_count: 0,
                average_score: 95, best_score: 95, current_streak_days: 20, password: 'teacher123', role: 'PROCTOR', access_code: '123456', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'PRIN001', created_by_teacher_id: null, designation: 'Associate Professor & Proctor', department: 'Computer Science & Engineering', max_students_allowed: 500, max_exams_allowed: 50,
                can_create_exams: 1, can_set_questions: 1, can_manage_lessons: 1, can_manage_courses: 1, can_enroll_students: 1, can_view_results: 1
            },
            // NITP PRINCIPAL
            {
                id: 'NITP_PRIN_01', student_id: 'NITP_PRIN_01', full_name: 'Prof. Pradeep Kumar', email: 'principal@nitp.ac.in', phone: '+91 94310 12345',
                dob: '15 Aug 1968', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'Institutional Administration', stream: 'Computer Science & Engineering', batch_years: 'Faculty',
                bio: 'Director & Principal overseeing national examination standards, AI proctoring compliance, and placement credentials.',
                goal: 'Excellence in National Technical Education', achievements: 'Published 40+ IEEE papers', interests: 'AI, Cloud Architecture, Higher Education',
                profile_completion_pct: 100, exams_enrolled: 0, exams_completed: 0, upcoming_exams_count: 0,
                average_score: 100, best_score: 100, current_streak_days: 30, password: 'password123', role: 'PRINCIPAL', access_code: '888888', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: null, created_by_teacher_id: null, designation: 'Director & Head of Institution', department: 'Administration', max_students_allowed: 8000, max_exams_allowed: 200,
                can_create_exams: 1, can_set_questions: 1, can_manage_lessons: 1, can_manage_courses: 1, can_enroll_students: 1, can_view_results: 1
            },
            // NITP TEACHERS
            {
                id: 'TEACH_001', student_id: 'FAC-CS-01', full_name: 'Prof. Rajesh Sharma', email: 'teacher@nitp.ac.in', phone: '+91 98765 43210',
                dob: '20 May 1980', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'Faculty & Assessment Operations', stream: 'Computer Science & Engineering', batch_years: 'Faculty',
                bio: 'Senior Professor specializing in Algorithms, Distributed Systems, and Database Systems.',
                goal: 'Empowering engineering students with core technical excellence', achievements: 'Best Faculty Award 2024', interests: 'Algorithms, Cloud, DBMS',
                profile_completion_pct: 95, exams_enrolled: 0, exams_completed: 0, upcoming_exams_count: 0,
                average_score: 95, best_score: 95, current_streak_days: 20, password: 'password123', role: 'PROCTOR', access_code: '888888', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: null, designation: 'Associate Professor & HOD', department: 'Computer Science & Engineering', max_students_allowed: 200, max_exams_allowed: 20,
                can_create_exams: 1, can_set_questions: 1, can_manage_lessons: 1, can_manage_courses: 1, can_enroll_students: 1, can_view_results: 1
            },
            // CANDIDATE 1 (Ankit)
            {
                id: 'CAND123456', student_id: '2201CS01', full_name: 'Ankit Kumar', email: 'ankit.kumar@gmail.com', phone: '+91 98765 43210',
                dob: '12 Jan 2003', location: 'Bihar, India', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'B.Tech', stream: 'Computer Science & Engineering', batch_years: '2022 - 2026',
                bio: 'Final year CSE undergraduate passionate about full-stack engineering, distributed systems, and competitive programming.',
                goal: 'Secure SDE role in Tier-1 Technology Firm', achievements: 'Rank 1 in College Hackathon 2025; Solved 450+ LeetCode problems', interests: 'Data Structures, AI, Cloud Architecture',
                profile_completion_pct: 85, exams_enrolled: 12, exams_completed: 5, upcoming_exams_count: 3,
                average_score: 72, best_score: 95, current_streak_days: 7, password: 'password123', role: 'CANDIDATE', access_code: '123456', avatar_url: 'https://res.cloudinary.com/dpkgmrpcx/image/upload/v1788429254/sanjeev_xagdte.jpg', status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: 'TEACH_001', designation: 'Student Candidate', department: 'Computer Science & Engineering', max_students_allowed: 0, max_exams_allowed: 0,
                can_create_exams: 0, can_set_questions: 0, can_manage_lessons: 0, can_manage_courses: 0, can_enroll_students: 0, can_view_results: 0
            },
            // CANDIDATE 2 (Priya)
            {
                id: 'CAND123457', student_id: '2201CS02', full_name: 'Priya Sharma', email: 'priya@nitp.ac.in', phone: '+91 91234 56790',
                dob: '08 Mar 2003', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'B.Tech', stream: 'Computer Science & Engineering', batch_years: '2022 - 2026',
                bio: 'CSE Student passionate about AI/ML & Web Development.',
                goal: 'Software Engineer at top Tech company', achievements: 'Finalist at Smart India Hackathon', interests: 'Machine Learning, Python, Web Apps',
                profile_completion_pct: 90, exams_enrolled: 10, exams_completed: 4, upcoming_exams_count: 2,
                average_score: 85, best_score: 98, current_streak_days: 12, password: 'password123', role: 'CANDIDATE', access_code: '123456', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: 'TEACH_001', designation: 'Student Candidate', department: 'Computer Science & Engineering', max_students_allowed: 0, max_exams_allowed: 0,
                can_create_exams: 0, can_set_questions: 0, can_manage_lessons: 0, can_manage_courses: 0, can_enroll_students: 0, can_view_results: 0
            },
            // CANDIDATE 3 (Rohan)
            {
                id: 'CAND123458', student_id: '2201CS03', full_name: 'Rohan Gupta', email: 'rohan@nitp.ac.in', phone: '+91 91234 56791',
                dob: '14 Jul 2002', location: 'Gaya, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'B.Tech', stream: 'Computer Science & Engineering', batch_years: '2022 - 2026',
                bio: 'Competitive Programmer & Backend Developer.',
                goal: 'Crack Google/Amazon SDE Interviews', achievements: 'Codeforces Candidate Master', interests: 'C++, Algorithms, System Design',
                profile_completion_pct: 88, exams_enrolled: 11, exams_completed: 5, upcoming_exams_count: 2,
                average_score: 88, best_score: 96, current_streak_days: 15, password: 'password123', role: 'CANDIDATE', access_code: '123456', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: 'TEACH_001', designation: 'Student Candidate', department: 'Computer Science & Engineering', max_students_allowed: 0, max_exams_allowed: 0,
                can_create_exams: 0, can_set_questions: 0, can_manage_lessons: 0, can_manage_courses: 0, can_enroll_students: 0, can_view_results: 0
            },
            // CANDIDATE 4 (Sneha)
            {
                id: 'CAND123459', student_id: '2201CS04', full_name: 'Sneha Patel', email: 'sneha@nitp.ac.in', phone: '+91 91234 56792',
                dob: '22 Sep 2003', location: 'Patna, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'B.Tech', stream: 'Computer Science & Engineering', batch_years: '2022 - 2026',
                bio: 'Frontend developer & UI/UX enthusiast.',
                goal: 'Lead UI/UX Engineer', achievements: 'Design Lead at College Tech Fest', interests: 'React, CSS, Modern UI, TypeScript',
                profile_completion_pct: 82, exams_enrolled: 9, exams_completed: 3, upcoming_exams_count: 2,
                average_score: 78, best_score: 92, current_streak_days: 5, password: 'password123', role: 'CANDIDATE', access_code: '123456', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: 'TEACH_001', designation: 'Student Candidate', department: 'Computer Science & Engineering', max_students_allowed: 0, max_exams_allowed: 0,
                can_create_exams: 0, can_set_questions: 0, can_manage_lessons: 0, can_manage_courses: 0, can_enroll_students: 0, can_view_results: 0
            },
            // CANDIDATE 5 (Amit)
            {
                id: 'CAND123460', student_id: '2201CS05', full_name: 'Amit Singh', email: 'amit@nitp.ac.in', phone: '+91 91234 56793',
                dob: '03 Feb 2003', location: 'Muzaffarpur, Bihar', college_name: 'National Institute of Technology (NIT Patna)',
                course: 'B.Tech', stream: 'Computer Science & Engineering', batch_years: '2022 - 2026',
                bio: 'Cloud and DevOps Enthusiast.',
                goal: 'Cloud Architect Certification', achievements: 'AWS Certified Cloud Practitioner', interests: 'Docker, Kubernetes, AWS, Go',
                profile_completion_pct: 80, exams_enrolled: 8, exams_completed: 3, upcoming_exams_count: 2,
                average_score: 75, best_score: 90, current_streak_days: 6, password: 'password123', role: 'CANDIDATE', access_code: '123456', avatar_url: null, status: 'ACTIVE', org_id: 'ORG_NITP',
                created_by_principal_id: 'NITP_PRIN_01', created_by_teacher_id: 'TEACH_001', designation: 'Student Candidate', department: 'Computer Science & Engineering', max_students_allowed: 0, max_exams_allowed: 0,
                can_create_exams: 0, can_set_questions: 0, can_manage_lessons: 0, can_manage_courses: 0, can_enroll_students: 0, can_view_results: 0
            }
        ];

        for (const u of users) {
            await client.query(`
                INSERT INTO users (
                    id, student_id, full_name, email, phone, dob, location, college_name, 
                    course, stream, batch_years, bio, goal, achievements, interests, 
                    profile_completion_pct, exams_enrolled, exams_completed, upcoming_exams_count, 
                    average_score, best_score, current_streak_days, password, role, access_code, 
                    avatar_url, status, org_id, created_by_principal_id, created_by_teacher_id, 
                    designation, department, max_students_allowed, max_exams_allowed,
                    can_create_exams, can_set_questions, can_manage_lessons, can_manage_courses, can_enroll_students, can_view_results
                ) VALUES (
                    $1, $2, $3, $4, $5, $6, $7, $8,
                    $9, $10, $11, $12, $13, $14, $15,
                    $16, $17, $18, $19,
                    $20, $21, $22, $23, $24, $25,
                    $26, $27, $28, $29, $30,
                    $31, $32, $33, $34,
                    $35, $36, $37, $38, $39, $40
                )
                ON CONFLICT (id) DO UPDATE SET
                    full_name=EXCLUDED.full_name,
                    email=EXCLUDED.email,
                    password=EXCLUDED.password,
                    role=EXCLUDED.role,
                    status=EXCLUDED.status,
                    access_code=EXCLUDED.access_code;
            `, [
                u.id, u.student_id, u.full_name, u.email, u.phone, u.dob, u.location, u.college_name,
                u.course, u.stream, u.batch_years, u.bio, u.goal, u.achievements, u.interests,
                u.profile_completion_pct, u.exams_enrolled, u.exams_completed, u.upcoming_exams_count,
                u.average_score, u.best_score, u.current_streak_days, u.password, u.role, u.access_code,
                u.avatar_url, u.status, u.org_id, u.created_by_principal_id, u.created_by_teacher_id,
                u.designation, u.department, u.max_students_allowed, u.max_exams_allowed,
                u.can_create_exams, u.can_set_questions, u.can_manage_lessons, u.can_manage_courses, u.can_enroll_students, u.can_view_results
            ]);
            console.log(`   + Seeded User: ${u.full_name} (${u.email}) [${u.role}]`);
        }

        console.log('\n🎉 ALL USERS AND STUDENTS SEEDED SUCCESSFULLY INTO SUPABASE POSTGRESQL!\n');
    } catch (err) {
        console.error('❌ Seeding failed:', err);
    } finally {
        await client.end();
    }
}

seedSupabase();
