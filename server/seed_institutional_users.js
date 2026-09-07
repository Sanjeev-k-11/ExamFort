const mysql = require('mysql2/promise');

async function main() {
    try {
        const conn = await mysql.createConnection({
            host: 'localhost',
            user: 'root',
            password: '',
            database: 'examfort'
        });

        console.log('Connecting and altering users table...');
        
        await conn.query(`ALTER TABLE users MODIFY COLUMN role VARCHAR(50) DEFAULT 'CANDIDATE'`);

        const addCol = async (sql) => {
            try { await conn.query(sql); } catch(e) { /* ignore */ }
        };

        await addCol(`ALTER TABLE users ADD COLUMN status VARCHAR(20) DEFAULT 'ACTIVE'`);
        await addCol(`ALTER TABLE users ADD COLUMN org_id VARCHAR(50) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN created_by_principal_id VARCHAR(50) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN created_by_teacher_id VARCHAR(50) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN designation VARCHAR(100) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN department VARCHAR(100) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN max_students_allowed INT DEFAULT 500`);
        await addCol(`ALTER TABLE users ADD COLUMN max_exams_allowed INT DEFAULT 50`);
        await addCol(`ALTER TABLE users ADD COLUMN gemini_api_key VARCHAR(255) NULL`);
        await addCol(`ALTER TABLE users ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`);

        // Alter exams table to ensure all foreign keys and columns exist
        await addCol(`ALTER TABLE exams ADD COLUMN created_by_teacher_id VARCHAR(50) NULL`);
        await addCol(`ALTER TABLE exams ADD COLUMN created_by_principal_id VARCHAR(50) NULL`);
        await addCol(`ALTER TABLE exams ADD COLUMN college_name VARCHAR(255) NULL DEFAULT 'National Institute of Technology (NIT Patna)'`);
        await addCol(`ALTER TABLE exams ADD COLUMN org_id VARCHAR(50) NULL DEFAULT 'ORG_NITP'`);
        await addCol(`ALTER TABLE exams ADD COLUMN updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP`);

        await conn.query(`UPDATE exams SET org_id = 'ORG_NITP', college_name = 'National Institute of Technology (NIT Patna)' WHERE org_id IS NULL OR college_name IS NULL`);

        await conn.query(`
            CREATE TABLE IF NOT EXISTS organizations (
                id VARCHAR(50) PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                code VARCHAR(50) NOT NULL,
                type VARCHAR(100) DEFAULT 'University',
                email VARCHAR(150) NULL,
                phone VARCHAR(50) NULL,
                website VARCHAR(255) NULL,
                address TEXT NULL,
                max_teachers_allowed INT DEFAULT 100,
                max_students_allowed INT DEFAULT 8000,
                max_exams_allowed INT DEFAULT 200,
                gemini_api_key VARCHAR(255) NULL,
                status VARCHAR(20) DEFAULT 'ACTIVE',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            )
        `);

        await addCol(`ALTER TABLE organizations ADD COLUMN gemini_api_key VARCHAR(255) NULL`);

        await conn.query(`
            INSERT INTO organizations (id, name, code, type, email, phone, website, address, max_teachers_allowed, max_students_allowed, max_exams_allowed, status)
            VALUES 
            ('ORG_NITP', 'National Institute of Technology (NIT Patna)', 'NITP', 'University / Institute of National Importance', 'admin@nitp.ac.in', '+91 612 237 1715', 'https://www.nitp.ac.in', 'Ashok Rajpath, Patna, Bihar 800005', 100, 8000, 200, 'ACTIVE'),
            ('ORG_IITD', 'Indian Institute of Technology Delhi (IIT Delhi)', 'IITD', 'Institute of Eminence', 'dean.academics@iitd.ac.in', '+91 11 2659 7135', 'https://home.iitd.ac.in', 'Hauz Khas, New Delhi 110016', 150, 12000, 300, 'ACTIVE')
            ON DUPLICATE KEY UPDATE name=VALUES(name), status='ACTIVE'
        `);

        const users = [
            {
                id: 'ADMIN001',
                student_id: 'ADMIN001',
                full_name: 'Institutional Super Administrator',
                email: 'admin@examfort.com',
                role: 'SUPERADMIN',
                password: 'admin123',
                status: 'ACTIVE',
                college_name: 'ExamFort Central Governance',
                designation: 'System Chief Administrator',
                department: 'Central Administration',
                max_students_allowed: 100000,
                max_exams_allowed: 5000
            },
            {
                id: 'PRIN001',
                org_id: 'ORG_NITP',
                student_id: 'PRIN1001',
                full_name: 'Dr. P. K. Mishra (Dean & Principal)',
                email: 'principal@examfort.com',
                role: 'PRINCIPAL',
                password: 'principal123',
                status: 'ACTIVE',
                college_name: 'National Institute of Technology (NIT Patna)',
                designation: 'Principal & Dean of Academic Affairs',
                department: 'Office of the Dean',
                access_code: '777777',
                max_students_allowed: 5000,
                max_exams_allowed: 150
            },
            {
                id: 'TEACH001',
                org_id: 'ORG_NITP',
                student_id: 'FAC101',
                full_name: 'Prof. Rajesh Sharma',
                email: 'teacher@examfort.com',
                role: 'TEACHER',
                password: 'teacher123',
                status: 'ACTIVE',
                college_name: 'National Institute of Technology (NIT Patna)',
                designation: 'Associate Professor & Proctor',
                department: 'Computer Science & Engineering',
                access_code: '123456',
                created_by_principal_id: 'PRIN001',
                max_students_allowed: 500,
                max_exams_allowed: 50
            }
        ];

        for (const u of users) {
            await conn.query(`
                INSERT INTO users (id, student_id, full_name, email, role, password, status, college_name, designation, department, max_students_allowed, max_exams_allowed, org_id, access_code, created_by_principal_id)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE 
                    role=VALUES(role),
                    password=VALUES(password),
                    status='ACTIVE',
                    full_name=VALUES(full_name),
                    college_name=VALUES(college_name),
                    org_id=VALUES(org_id),
                    created_by_principal_id=VALUES(created_by_principal_id)
            `, [
                u.id, u.student_id, u.full_name, u.email, u.role, u.password, u.status, u.college_name, u.designation || null, u.department || null, u.max_students_allowed, u.max_exams_allowed, u.org_id || null, u.access_code || '123456', u.created_by_principal_id || null
            ]);
        }

        console.log('✅ Institutional Users Successfully Inserted!');
        const [rows] = await conn.query('SELECT id, email, full_name, role, password, status FROM users');
        console.table(rows);
        await conn.end();
    } catch (e) {
        console.error('Migration error:', e);
    }
}

main();
