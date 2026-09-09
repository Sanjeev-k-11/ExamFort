const mysql = require('mysql2/promise');
require('dotenv').config();

const DB_CONFIG = {
    host: process.env.DB_HOST || 'localhost',
    port: parseInt(process.env.DB_PORT || '3306', 10),
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'examfort'
};

async function seed() {
    console.log('Connecting to MySQL...');
    const conn = await mysql.createConnection(DB_CONFIG);
    await conn.query('SET FOREIGN_KEY_CHECKS = 0;');

    // 1. Ensure organizations
    await conn.query(`
        INSERT INTO \`organizations\` (\`id\`, \`name\`, \`code\`, \`type\`, \`email\`, \`phone\`, \`max_teachers_allowed\`, \`max_students_allowed\`, \`max_exams_allowed\`, \`status\`)
        VALUES 
        ('ORG_NITP', 'National Institute of Technology (NIT Patna)', 'NITP', 'Institute of National Importance', 'admin@nitp.ac.in', '+91 612 237 1715', 100, 8000, 200, 'ACTIVE'),
        ('ORG_IITD', 'Indian Institute of Technology Delhi (IIT Delhi)', 'IITD', 'Institute of Eminence', 'dean.academics@iitd.ac.in', '+91 11 2659 7135', 150, 12000, 300, 'ACTIVE')
        ON DUPLICATE KEY UPDATE name=VALUES(name);
    `);

    // 2. Insert all Users including ExamFort Portal & Institutional Accounts
    const users = [
        // ---------------- SUPER ADMIN ----------------
        [
            'ADMIN001', 'ADMIN001', 'Institutional Super Administrator', 'admin@examfort.com', '+91 99999 00001',
            '01 Jan 1980', 'New Delhi, India', 'ExamFort Central Governance',
            'Institutional Administration', 'System Administration', '2020 - 2030',
            'System Chief Administrator managing institutional exams, university portals, and global security policies.',
            'Zero-latency global proctoring ecosystem.', 'Architected ExamFort Multi-Tiered Proctoring Suite.', 'Distributed Systems, Cloud Architecture, Cyber Security',
            100, 50, 50, 0, 99, 100, 30, 'admin123', 'SUPERADMIN', '999999', null, 'ACTIVE', 'ORG_NITP',
            null, null, 'Chief Technology Administrator', 'Central Administration', 100000, 5000,
            1, 1, 1, 1, 1, 1
        ],

        // ---------------- EXAMFORT DEMO PRINCIPAL ----------------
        [
            'PRIN001', 'PRIN1001', 'Dr. P. K. Mishra (Dean & Principal)', 'principal@examfort.com', '+91 94310 12345',
            '15 Aug 1968', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Administration & Academics', 'Academic Affairs', 'Faculty',
            'Principal & Dean overseeing institutional examinations, faculty delegations, and question setting standards.',
            'Excellence in National Technical Education', 'Published 40+ IEEE papers', 'AI, Cloud Architecture, Higher Education',
            100, 0, 0, 0, 100, 100, 30, 'principal123', 'PRINCIPAL', '777777', null, 'ACTIVE', 'ORG_NITP',
            null, null, 'Principal & Dean of Academic Affairs', 'Office of the Dean', 5000, 150,
            1, 1, 1, 1, 1, 1
        ],

        // ---------------- EXAMFORT DEMO TEACHER ----------------
        [
            'TEACH001', 'FAC101', 'Prof. Rajesh Sharma (Faculty)', 'teacher@examfort.com', '+91 98765 43210',
            '20 May 1980', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Computer Science & Engineering', 'CSE', 'Faculty',
            'Senior Faculty & Assessment Head with full question authoring and course management privileges.',
            'Empowering engineering students with core technical excellence', 'Best Faculty Award 2024', 'Algorithms, Cloud, DBMS',
            95, 0, 0, 0, 95, 95, 20, 'teacher123', 'PROCTOR', '123456', null, 'ACTIVE', 'ORG_NITP',
            'PRIN001', null, 'Associate Professor & Proctor', 'Computer Science & Engineering', 500, 50,
            1, 1, 1, 1, 1, 1
        ],

        // ---------------- NIT PATNA PRINCIPAL ----------------
        [
            'NITP_PRIN_01', 'NITP_PRIN_01', 'Prof. Pradeep Kumar', 'principal@nitp.ac.in', '+91 94310 12345',
            '15 Aug 1968', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Institutional Administration', 'Computer Science & Engineering', 'Faculty',
            'Director & Principal overseeing national examination standards, AI proctoring compliance, and placement credentials.',
            'Excellence in National Technical Education', 'Published 40+ IEEE papers', 'AI, Cloud Architecture, Higher Education',
            100, 0, 0, 0, 100, 100, 30, 'password123', 'PRINCIPAL', '888888', null, 'ACTIVE', 'ORG_NITP',
            null, null, 'Director & Head of Institution', 'Administration', 8000, 200,
            1, 1, 1, 1, 1, 1
        ],

        // ---------------- NIT PATNA TEACHERS ----------------
        [
            'TEACH_001', 'FAC-CS-01', 'Prof. Rajesh Sharma', 'teacher@nitp.ac.in', '+91 98765 43210',
            '20 May 1980', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Faculty & Assessment Operations', 'Computer Science & Engineering', 'Faculty',
            'Senior Professor specializing in Algorithms, Distributed Systems, and Database Systems.',
            'Empowering engineering students with core technical excellence', 'Best Faculty Award 2024', 'Algorithms, Cloud, DBMS',
            95, 0, 0, 0, 95, 95, 20, 'password123', 'PROCTOR', '888888', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', null, 'Associate Professor & HOD', 'Computer Science & Engineering', 200, 20,
            1, 1, 1, 1, 1, 1
        ],
        [
            'TEACH_002', 'FAC-EC-02', 'Dr. Ananya Verma', 'ananya@nitp.ac.in', '+91 98765 43211',
            '12 Jan 1985', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Faculty & Assessment Operations', 'Electronics & Communication', 'Faculty',
            'Assistant Professor in Embedded Systems and Microprocessors.',
            'Bridging hardware architecture with modern software compilers', 'Gold Medalist in M.Tech', 'IoT, VLSI, Embedded C',
            90, 0, 0, 0, 90, 90, 15, 'password123', 'PROCTOR', '888888', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', null, 'Assistant Professor', 'Electronics & Communication', 150, 15,
            1, 1, 1, 0, 1, 1
        ],
        [
            'TEACH_003', 'FAC-IT-03', 'Prof. Vikramaditya', 'vikram@nitp.ac.in', '+91 98765 43212',
            '05 Nov 1982', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'Faculty & Assessment Operations', 'Information Technology', 'Faculty',
            'Associate Professor in Web Security, Cloud Systems, and Full-Stack Engineering.',
            'Mentoring top campus placement selections across Tier-1 tech giants', 'Authored Full-Stack Guidebook', 'Full Stack, DevOps, Cyber Security',
            92, 0, 0, 0, 92, 92, 18, 'password123', 'PROCTOR', '888888', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', null, 'Associate Professor', 'Information Technology', 150, 15,
            1, 1, 1, 0, 1, 1
        ],

        // ---------------- CANDIDATES ----------------
        [
            'CAND123456', '2201CS01', 'Ankit Kumar', 'ankit.kumar@gmail.com', '+91 98765 43210',
            '12 Jan 2003', 'Bihar, India', 'National Institute of Technology (NIT Patna)',
            'B.Tech', 'Computer Science & Engineering', '2022 - 2026',
            'Final year CSE undergraduate passionate about full-stack engineering, distributed systems, and competitive programming.',
            'Secure SDE role in Tier-1 Technology Firm', 'Rank 1 in College Hackathon 2025; Solved 450+ LeetCode problems', 'Data Structures, AI, Cloud Architecture',
            85, 12, 5, 3, 72, 95, 7, 'password123', 'CANDIDATE', '123456', 'https://res.cloudinary.com/dpkgmrpcx/image/upload/v1788429254/sanjeev_xagdte.jpg', 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0,
            0, 0, 0, 0, 0, 0
        ],
        [
            'CAND123457', '2201CS02', 'Priya Sharma', 'priya@nitp.ac.in', '+91 91234 56790',
            '08 Mar 2003', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'B.Tech', 'Computer Science & Engineering', '2022 - 2026',
            'CSE Student passionate about AI/ML & Web Development.',
            'Software Engineer at top Tech company', 'Finalist at Smart India Hackathon', 'Machine Learning, Python, Web Apps',
            90, 10, 4, 2, 85, 98, 12, 'password123', 'CANDIDATE', '123456', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0,
            0, 0, 0, 0, 0, 0
        ],
        [
            'CAND123458', '2201CS03', 'Rohan Gupta', 'rohan@nitp.ac.in', '+91 91234 56791',
            '14 Jul 2002', 'Gaya, Bihar', 'National Institute of Technology (NIT Patna)',
            'B.Tech', 'Computer Science & Engineering', '2022 - 2026',
            'Competitive Programmer & Backend Developer.',
            'Crack Google/Amazon SDE Interviews', 'Codeforces Candidate Master', 'C++, Algorithms, System Design',
            88, 11, 5, 2, 88, 96, 15, 'password123', 'CANDIDATE', '123456', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0,
            0, 0, 0, 0, 0, 0
        ],
        [
            'CAND123459', '2201CS04', 'Sneha Patel', 'sneha@nitp.ac.in', '+91 91234 56792',
            '22 Sep 2003', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)',
            'B.Tech', 'Computer Science & Engineering', '2022 - 2026',
            'Frontend developer & UI/UX enthusiast.',
            'Lead UI/UX Engineer', 'Design Lead at College Tech Fest', 'React, CSS, Modern UI, TypeScript',
            82, 9, 3, 2, 78, 92, 5, 'password123', 'CANDIDATE', '123456', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0,
            0, 0, 0, 0, 0, 0
        ],
        [
            'CAND123460', '2201CS05', 'Amit Singh', 'amit@nitp.ac.in', '+91 91234 56793',
            '03 Feb 2003', 'Muzaffarpur, Bihar', 'National Institute of Technology (NIT Patna)',
            'B.Tech', 'Computer Science & Engineering', '2022 - 2026',
            'Cloud and DevOps Enthusiast.',
            'Cloud Architect Certification', 'AWS Certified Cloud Practitioner', 'Docker, Kubernetes, AWS, Go',
            80, 8, 3, 2, 75, 90, 6, 'password123', 'CANDIDATE', '123456', null, 'ACTIVE', 'ORG_NITP',
            'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0,
            0, 0, 0, 0, 0, 0
        ]
    ];

    const insertSql = `
        INSERT INTO \`users\` (
            \`id\`, \`student_id\`, \`full_name\`, \`email\`, \`phone\`, \`dob\`, \`location\`, \`college_name\`, 
            \`course\`, \`stream\`, \`batch_years\`, \`bio\`, \`goal\`, \`achievements\`, \`interests\`, 
            \`profile_completion_pct\`, \`exams_enrolled\`, \`exams_completed\`, \`upcoming_exams_count\`, 
            \`average_score\`, \`best_score\`, \`current_streak_days\`, \`password\`, \`role\`, \`access_code\`, 
            \`avatar_url\`, \`status\`, \`org_id\`, \`created_by_principal_id\`, \`created_by_teacher_id\`, 
            \`designation\`, \`department\`, \`max_students_allowed\`, \`max_exams_allowed\`,
            \`can_create_exams\`, \`can_set_questions\`, \`can_manage_lessons\`, \`can_manage_courses\`, \`can_enroll_students\`, \`can_view_results\`
        ) VALUES ?
        ON DUPLICATE KEY UPDATE 
            full_name=VALUES(full_name),
            email=VALUES(email),
            password=VALUES(password),
            role=VALUES(role),
            status=VALUES(status),
            can_create_exams=VALUES(can_create_exams),
            can_set_questions=VALUES(can_set_questions),
            can_manage_lessons=VALUES(can_manage_lessons),
            can_manage_courses=VALUES(can_manage_courses),
            can_enroll_students=VALUES(can_enroll_students),
            can_view_results=VALUES(can_view_results);
    `;

    await conn.query(insertSql, [users]);
    await conn.query('SET FOREIGN_KEY_CHECKS = 1;');

    const [rows] = await conn.query('SELECT id, student_id, full_name, email, password, role FROM users');
    console.log(`✅ Success! Seeded ${rows.length} total users into MySQL users table.`);
    console.table(rows);

    await conn.end();
}

seed().catch(err => {
    console.error('Error seeding users:', err);
});
