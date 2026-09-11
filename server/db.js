let mysql;
try { mysql = require('mysql2/promise'); } catch (_) {}
let pg;
try { pg = require('pg'); } catch (_) {}

const fs = require('fs');
const path = require('path');
const judgeEngine = require('./judge');
const essayEvaluator = require('./essay-evaluator');
require('dotenv').config();

const DEFAULT_PG_URL = 'postgresql://postgres.leodjnnylkxycarmegzk:Kumar%402004%40h3@aws-0-ap-south-1.pooler.supabase.com:5432/postgres';

const isPostgresConfig = (
    process.env.DB_CONNECTION === 'pgsql' ||
    (process.env.DATABASE_URL && process.env.DATABASE_URL.startsWith('postgres')) ||
    parseInt(process.env.DB_PORT || '0', 10) === 5432 ||
    (process.env.DB_HOST && (process.env.DB_HOST.includes('supabase') || process.env.DB_HOST.includes('pooler'))) ||
    (!process.env.DB_HOST && !process.env.DB_CONNECTION) // Default to Supabase when hosted on Render
);

const DB_CONFIG = {
    host: process.env.DB_HOST || 'aws-0-ap-south-1.pooler.supabase.com',
    port: parseInt(process.env.DB_PORT || '5432', 10),
    user: process.env.DB_USER || 'postgres.leodjnnylkxycarmegzk',
    password: process.env.DB_PASSWORD || 'Kumar@2004@h3',
    database: process.env.DB_NAME || 'postgres',
    connectionString: process.env.DATABASE_URL || DEFAULT_PG_URL
};

function adaptQueryForPg(sql, params = []) {
    let pgSql = sql;
    
    // 1. Convert backticks to double quotes: `column` -> "column"
    pgSql = pgSql.replace(/`([^`]+)`/g, '"$1"');

    // 2. MySQL specific functions -> PostgreSQL functions
    pgSql = pgSql.replace(/\bIFNULL\s*\(/gi, 'COALESCE(');

    // 3. Convert ? to $1, $2, $3...
    let idx = 1;
    pgSql = pgSql.replace(/\?/g, () => `$${idx++}`);

    return { sql: pgSql, params };
}

class MySQLDatabaseService {
    constructor() {
        this.pool = null;
        this.isInitialized = false;
        this.dbType = isPostgresConfig ? 'POSTGRESQL' : 'MYSQL';
        this.init();
    }

    async init() {
        if (isPostgresConfig) {
            await this.initPostgreSQL();
        } else {
            await this.initMySQL();
        }
    }

    async initPostgreSQL() {
        try {
            if (!pg) {
                throw new Error('pg package is not installed. Please run `npm install pg` in server.');
            }

            const pgPoolConfig = DB_CONFIG.connectionString
                ? {
                    connectionString: DB_CONFIG.connectionString,
                    ssl: { rejectUnauthorized: false }
                }
                : {
                    host: DB_CONFIG.host,
                    port: DB_CONFIG.port,
                    user: DB_CONFIG.user,
                    password: DB_CONFIG.password,
                    database: DB_CONFIG.database,
                    ssl: { rejectUnauthorized: false }
                };

            const rawPgPool = new pg.Pool(pgPoolConfig);

            // Test connection
            const testClient = await rawPgPool.connect();
            testClient.release();

            // Create wrapper matching mysql2 [rows, fields] interface
            this.pool = {
                rawPool: rawPgPool,
                query: async (sql, params = []) => {
                    let { sql: finalSql, params: finalParams } = adaptQueryForPg(sql, params);
                    if (/^\s*INSERT\s+INTO/i.test(finalSql) && !/RETURNING/i.test(finalSql)) {
                        finalSql += ' RETURNING id';
                    }
                    const res = await rawPgPool.query(finalSql, finalParams);
                    const rows = res.rows || [];
                    rows.insertId = rows[0]?.id || res.rowCount || 0;
                    rows.affectedRows = res.rowCount || 0;
                    return [rows, res.fields];
                }
            };

            console.log(`🐘 [PostgreSQL] Successfully connected to PostgreSQL / Supabase database "${DB_CONFIG.database}" on ${DB_CONFIG.host}:${DB_CONFIG.port}`);
            this.isInitialized = true;
            this.dbType = 'POSTGRESQL';
            await this.ensureSchemaUpgrades();
        } catch (err) {
            console.error('❌ [PostgreSQL Connection Error]:', err.message);
            console.warn('⚠️ Falling back to check MySQL connection...');
            await this.initMySQL();
        }
    }

    async initMySQL() {
        try {
            if (!mysql) return;
            // Step 1: Connect to MySQL server to ensure DB exists
            const tempConnection = await mysql.createConnection({
                host: DB_CONFIG.host,
                port: DB_CONFIG.port,
                user: DB_CONFIG.user,
                password: DB_CONFIG.password,
                multipleStatements: true
            });

            await tempConnection.query(`CREATE DATABASE IF NOT EXISTS \`${DB_CONFIG.database}\` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`);
            await tempConnection.query(`USE \`${DB_CONFIG.database}\`;`);

            // Check if database tables already exist
            const [existingTables] = await tempConnection.query(`SHOW TABLES LIKE 'users';`);
            if (existingTables.length === 0) {
                const schemaPath = path.join(__dirname, 'schema.sql');
                if (fs.existsSync(schemaPath)) {
                    try {
                        await tempConnection.query('SET FOREIGN_KEY_CHECKS = 0;');
                        const schemaSql = fs.readFileSync(schemaPath, 'utf8');
                        const statements = schemaSql.split(/;\s*[\r\n]+/).map(s => s.trim()).filter(s => s.length > 0 && !s.startsWith('--') && !s.toUpperCase().startsWith('DROP TABLE'));
                        for (const stmt of statements) {
                            try {
                                await tempConnection.query(stmt);
                            } catch (_) {}
                        }
                        await tempConnection.query('SET FOREIGN_KEY_CHECKS = 1;');
                        console.log('✅ [MySQL] Initial schema created successfully.');
                    } catch (schemaErr) {
                        console.log('ℹ️ [MySQL] Schema initialization info:', schemaErr.message);
                    }
                }
            }

            try { await tempConnection.end(); } catch (_) {}

            // Step 2: Create connection pool to the examfort database
            this.pool = mysql.createPool({
                host: DB_CONFIG.host,
                port: DB_CONFIG.port,
                user: DB_CONFIG.user,
                password: DB_CONFIG.password,
                database: DB_CONFIG.database,
                waitForConnections: true,
                connectionLimit: 25,
                queueLimit: 0,
                multipleStatements: true
            });

            console.log(`🐬 [MySQL] Successfully connected to MySQL database "${DB_CONFIG.database}" on ${DB_CONFIG.host}:${DB_CONFIG.port}`);
            this.isInitialized = true;
            this.dbType = 'MYSQL';
            await this.ensureSchemaUpgrades();
        } catch (err) {
            console.error('❌ [Database Connection Error]:', err.message);
        }
    }

    async ensureSchemaUpgrades() {
        if (!this.pool) return;
        try {
            if (this.dbType === 'POSTGRESQL') {
                await this.pool.query('ALTER TABLE submissions ADD COLUMN IF NOT EXISTS attempt_number INT DEFAULT 1').catch(() => {});
                await this.pool.query('ALTER TABLE submissions ADD COLUMN IF NOT EXISTS reattempt_reason TEXT DEFAULT NULL').catch(() => {});
            } else {
                try {
                    await this.pool.query('ALTER TABLE `submissions` ADD COLUMN `attempt_number` INT DEFAULT 1');
                } catch (_) {}
                try {
                    await this.pool.query('ALTER TABLE `submissions` ADD COLUMN `reattempt_reason` TEXT DEFAULT NULL');
                } catch (_) {}
            }

            // Ensure Courses & Lessons Tables Exist
            try {
                if (this.dbType === 'POSTGRESQL') {
                    await this.pool.query(`
                        CREATE TABLE IF NOT EXISTS courses (
                            id SERIAL PRIMARY KEY,
                            course_id VARCHAR(50) NOT NULL UNIQUE,
                            title VARCHAR(255) NOT NULL,
                            description TEXT NOT NULL,
                            icon VARCHAR(50) DEFAULT '💻',
                            color VARCHAR(50) DEFAULT '#4f46e5',
                            lessons_count INT DEFAULT 18,
                            duration_text VARCHAR(50) DEFAULT '6h 20m',
                            level VARCHAR(50) DEFAULT 'Intermediate',
                            progress_percent INT DEFAULT 42,
                            completed_lessons INT DEFAULT 8,
                            language VARCHAR(50) DEFAULT 'English',
                            certificate VARCHAR(50) DEFAULT 'Yes',
                            last_updated VARCHAR(50) DEFAULT 'May 2026',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        );
                        CREATE TABLE IF NOT EXISTS course_lessons (
                            id SERIAL PRIMARY KEY,
                            course_id VARCHAR(50) NOT NULL,
                            module_num INT NOT NULL,
                            module_title VARCHAR(255) NOT NULL,
                            lesson_num VARCHAR(20) NOT NULL,
                            lesson_title VARCHAR(255) NOT NULL,
                            duration_text VARCHAR(50) NOT NULL,
                            is_completed SMALLINT DEFAULT 0,
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        );
                        CREATE TABLE IF NOT EXISTS student_activities (
                            id SERIAL PRIMARY KEY,
                            student_id VARCHAR(50) NOT NULL,
                            title VARCHAR(255) NOT NULL,
                            activity_title VARCHAR(255) NOT NULL,
                            description TEXT NOT NULL,
                            score_info VARCHAR(50) DEFAULT 'Score: 85%',
                            score INT DEFAULT 85,
                            status VARCHAR(50) DEFAULT 'Passed',
                            badge VARCHAR(50) DEFAULT '🏆 Passed',
                            time_text VARCHAR(50) DEFAULT 'Today, 09:15 AM',
                            icon VARCHAR(50) DEFAULT '✓',
                            type VARCHAR(50) DEFAULT 'EXAM',
                            activity_type VARCHAR(50) DEFAULT 'EXAM',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        );
                        CREATE TABLE IF NOT EXISTS exam_reattempt_authorizations (
                            id SERIAL PRIMARY KEY,
                            candidate_id VARCHAR(50) NOT NULL,
                            exam_code VARCHAR(50) NOT NULL,
                            reattempt_reason TEXT NOT NULL,
                            authorized_by VARCHAR(100) DEFAULT 'Faculty Admin',
                            status VARCHAR(20) DEFAULT 'ACTIVE',
                            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            consumed_at TIMESTAMP NULL
                        );
                        CREATE TABLE IF NOT EXISTS candidate_drafts (
                            id SERIAL PRIMARY KEY,
                            candidate_id VARCHAR(100) NOT NULL,
                            exam_code VARCHAR(100) NOT NULL,
                            answers_json TEXT NOT NULL,
                            last_saved_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                        );
                    `).catch(() => {});
                } else {
                    await this.pool.query(`
                        CREATE TABLE IF NOT EXISTS \`exam_reattempt_authorizations\` (
                            \`id\` INT AUTO_INCREMENT PRIMARY KEY,
                            \`candidate_id\` VARCHAR(50) NOT NULL,
                            \`exam_code\` VARCHAR(50) NOT NULL,
                            \`reattempt_reason\` TEXT NOT NULL,
                            \`authorized_by\` VARCHAR(100) DEFAULT 'Faculty Admin',
                            \`status\` VARCHAR(20) DEFAULT 'ACTIVE',
                            \`created_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                            \`consumed_at\` TIMESTAMP NULL
                        ) ENGINE=InnoDB;
                        CREATE TABLE IF NOT EXISTS \`candidate_drafts\` (
                            \`id\` INT AUTO_INCREMENT PRIMARY KEY,
                            \`candidate_id\` VARCHAR(100) NOT NULL,
                            \`exam_code\` VARCHAR(100) NOT NULL,
                            \`answers_json\` LONGTEXT NOT NULL,
                            \`last_saved_at\` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                        ) ENGINE=InnoDB;
                    `).catch(() => {});
                }

                // Check and auto-seed courses if empty
                const [cRows] = await this.pool.query('SELECT count(*) as cnt FROM courses').catch(() => [[]]);
                const cCount = parseInt(cRows[0]?.cnt || 0, 10);
                if (cCount === 0) {
                    const courses = [
                        ['course-cpp', 'Programming in C++', 'Master C++ programming from basics to advanced concepts with hands-on examples.', '</>', '#6366f1', 18, '6h 20m', 'Intermediate', 42, 8, 'English', 'Yes', 'May 2026'],
                        ['course-aptitude', 'Aptitude Fundamentals', 'Learn the basics of quantitative aptitude, number system, percentages, and ratios.', '🧠', '#ec4899', 12, '3h 45m', 'Beginner', 65, 8, 'English', 'Yes', 'May 2026'],
                        ['course-dsa', 'Data Structures & Algorithms', 'Learn essential data structures and algorithms for problem solving and coding interviews.', '💾', '#f59e0b', 20, '8h 15m', 'Advanced', 25, 5, 'English', 'Yes', 'May 2026'],
                        ['course-reasoning', 'Logical Reasoning', 'Improve your logical thinking skills with practice questions and detailed explanations.', '💡', '#8b5cf6', 10, '2h 30m', 'Beginner', 80, 8, 'English', 'Yes', 'May 2026']
                    ];
                    for (const c of courses) {
                        await this.pool.query(
                            'INSERT INTO courses (course_id, title, description, icon, color, lessons_count, duration_text, level, progress_percent, completed_lessons, language, certificate, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                            c
                        ).catch(() => {});
                    }
                }
            } catch (_) {}
        } catch (_) {}
    }

    // ==========================================
    // 1. AUTHENTICATION & ACCESS VERIFICATION
    // ==========================================

    async authenticateUser(userId, password) {
        if (!this.pool) return { success: false, message: 'Database offline. Please check your database connection.' };

        try {
            const cleanId = (userId || '').trim();
            const cleanPass = (password || '').trim();

            const [rows] = await this.pool.query(
                `SELECT id, student_id, full_name, email, phone, role, password, access_code, status, org_id, college_name, designation, department, avatar_url, gemini_api_key 
                 FROM users 
                 WHERE (id = ? OR student_id = ? OR email = ?) 
                 LIMIT 1`,
                [cleanId, cleanId, cleanId]
            );

            if (rows.length > 0) {
                const user = rows[0];
                const dbPass = (user.password || '').trim();
                const isMatch = (dbPass === cleanPass) || 
                                (cleanPass === 'password123' && (dbPass === 'password123' || dbPass === '123456')) ||
                                (cleanPass === '123456' && (dbPass === '123456' || dbPass === 'password123'));

                if (isMatch) {
                    return {
                        success: true,
                        user: {
                            id: user.id,
                            student_id: user.student_id,
                            full_name: user.full_name,
                            email: user.email,
                            phone: user.phone,
                            role: user.role,
                            accessCode: user.access_code,
                            status: user.status || 'ACTIVE',
                            org_id: user.org_id,
                            college_name: user.college_name,
                            designation: user.designation,
                            department: user.department,
                            avatar_url: user.avatar_url,
                            gemini_api_key: user.gemini_api_key
                        }
                    };
                }
            }
            return { success: false, message: 'Invalid User ID/Email or Password.' };
        } catch (err) {
            console.error('❌ [Auth Error]:', err.message);
            return { success: false, message: `Database Query Error: ${err.message}` };
        }
    }

    async validateCredentials(userId, password) {
        return this.authenticateUser(userId, password);
    }

    async validateAccessCode(accessCode, fullName) {
        if (!this.pool) return { success: false, message: 'Database offline.' };

        try {
            const code = (accessCode || '').trim();
            const cleanName = (fullName || '').trim();

            if (!code) {
                return { success: false, message: 'Please enter a valid Exam Access Code.' };
            }

            // 1. Check Placement Exam Candidate Roster in MySQL
            try {
                let pCandQuery = `
                    SELECT c.*, p.title as drive_title, p.company_name, p.job_role, p.package_lpa, p.duration_minutes, p.total_marks, p.face_verification_required 
                    FROM placement_exam_candidates c 
                    JOIN placement_exams p ON c.placement_exam_id = p.id 
                    WHERE c.access_code = ?
                `;
                let pParams = [code];

                if (cleanName) {
                    pCandQuery += ' AND (c.full_name LIKE ? OR c.student_id = ?)';
                    pParams.push(`%${cleanName}%`, cleanName);
                }

                const [pCandRows] = await this.pool.query(pCandQuery, pParams);

                if (pCandRows.length > 0) {
                    const pCand = pCandRows[0];
                    const [exRows] = await this.pool.query('SELECT * FROM exams WHERE exam_code = ? LIMIT 1', [pCand.exam_code]);
                    
                    const exam = exRows.length > 0 ? {
                        ...exRows[0],
                        exam_date: pCand.scheduled_date || exRows[0].exam_date,
                        exam_time: (pCand.scheduled_start_time && pCand.scheduled_end_time) ? `${pCand.scheduled_start_time} - ${pCand.scheduled_end_time}` : exRows[0].exam_time
                    } : {
                        exam_code: pCand.exam_code,
                        title: pCand.drive_title || `${pCand.company_name} Placement Drive`,
                        exam_date: pCand.scheduled_date,
                        exam_time: `${pCand.scheduled_start_time} - ${pCand.scheduled_end_time}`,
                        duration_minutes: pCand.duration_minutes || 90,
                        total_marks: pCand.total_marks || 100,
                        face_verification_required: pCand.face_verification_required !== 0
                    };

                    if (pCand.attempt_status === 'PENDING') {
                        await this.pool.query('UPDATE placement_exam_candidates SET attempt_status = "STARTED" WHERE id = ?', [pCand.id]).catch(() => {});
                    }

                    return {
                        success: true,
                        isPlacement: true,
                        user: {
                            id: pCand.student_id,
                            student_id: pCand.student_id,
                            full_name: pCand.full_name,
                            email: pCand.email,
                            phone: pCand.phone,
                            stream: pCand.stream,
                            course: pCand.course,
                            role: 'CANDIDATE',
                            accessCode: code,
                            examCode: pCand.exam_code,
                            scheduled_date: pCand.scheduled_date,
                            scheduled_start_time: pCand.scheduled_start_time,
                            scheduled_end_time: pCand.scheduled_end_time,
                            companyName: pCand.company_name,
                            jobRole: pCand.job_role,
                            package: pCand.package_lpa,
                            status: 'ACTIVE'
                        },
                        exam: exam
                    };
                }
            } catch (pErr) {
                console.warn('ℹ️ Placement candidate lookup notice:', pErr.message);
            }

            // 2. Direct Match by Exam Code
            const [exactExamRows] = await this.pool.query('SELECT * FROM exams WHERE exam_code = ? LIMIT 1', [code]);
            if (exactExamRows.length > 0) {
                const exam = exactExamRows[0];
                let user = null;
                if (cleanName) {
                    const [uRows] = await this.pool.query('SELECT * FROM users WHERE full_name LIKE ? OR student_id = ? LIMIT 1', [`%${cleanName}%`, cleanName]);
                    if (uRows.length > 0) user = uRows[0];
                }
                if (!user) {
                    const [defUser] = await this.pool.query('SELECT * FROM users WHERE role = "CANDIDATE" LIMIT 1');
                    if (defUser.length > 0) user = defUser[0];
                }

                return {
                    success: true,
                    isPlacement: exam.category === 'Campus Placement',
                    user: user ? { ...user, examCode: exam.exam_code } : { student_id: 'STU123456', full_name: cleanName || 'Candidate', examCode: exam.exam_code },
                    exam: exam
                };
            }

            // 3. Match user access_code in users table
            let [userRows] = await this.pool.query(
                'SELECT * FROM users WHERE access_code = ? AND (full_name LIKE ? OR student_id = ?) LIMIT 1',
                [code, `%${cleanName}%`, cleanName]
            );

            if (userRows.length === 0) {
                [userRows] = await this.pool.query('SELECT * FROM users WHERE access_code = ? LIMIT 1', [code]);
            }

            if (userRows.length > 0) {
                const user = userRows[0];
                const [pRows] = await this.pool.query(
                    'SELECT c.*, p.title as drive_title FROM placement_exam_candidates c JOIN placement_exams p ON c.placement_exam_id = p.id WHERE c.student_id = ? ORDER BY c.id DESC LIMIT 1',
                    [user.student_id || user.id]
                );

                if (pRows.length > 0) {
                    const pCand = pRows[0];
                    const [exRows] = await this.pool.query('SELECT * FROM exams WHERE exam_code = ? LIMIT 1', [pCand.exam_code]);
                    return {
                        success: true,
                        isPlacement: true,
                        user: { ...user, examCode: pCand.exam_code },
                        exam: exRows.length > 0 ? exRows[0] : null
                    };
                }

                // Default active exam for standard candidate access
                const [defExamRows] = await this.pool.query('SELECT * FROM exams WHERE status = "ACTIVE" ORDER BY id ASC LIMIT 1');
                const exam = defExamRows.length > 0 ? defExamRows[0] : null;

                return {
                    success: true,
                    isPlacement: false,
                    user: { ...user, examCode: exam ? exam.exam_code : 'NAT-2026-EXAM' },
                    exam: exam
                };
            }

            return {
                success: false,
                message: 'Invalid Exam Access Code or Candidate Name. Please enter the exact 6-digit access code provided for this exam.'
            };
        } catch (err) {
            console.error('❌ Error in validateAccessCode:', err.message);
            return { success: false, message: `MySQL Error: ${err.message}` };
        }
    }

    // ==========================================
    // 2. EXAMS & QUESTIONS MANAGEMENT
    // ==========================================

    async getExamDetails(examCode) {
        if (!this.pool) return null;
        try {
            const code = (examCode || '').trim();
            if (!code) return null;

            let exam = null;
            const [rows] = await this.pool.query('SELECT * FROM exams WHERE exam_code = ? OR id = ? LIMIT 1', [code, code]);
            if (rows.length > 0) {
                exam = rows[0];
            } else {
                const [pRows] = await this.pool.query('SELECT * FROM placement_exams WHERE exam_code = ? OR id = ? LIMIT 1', [code, code]);
                if (pRows.length > 0) {
                    const pExam = pRows[0];
                    exam = {
                        id: pExam.id,
                        exam_code: pExam.exam_code,
                        title: pExam.title || `${pExam.company_name} Placement Drive`,
                        description: pExam.description || `Campus Placement Drive for ${pExam.company_name} - ${pExam.job_role} (${pExam.package_lpa})`,
                        category: 'Campus Placement',
                        duration_minutes: pExam.duration_minutes || 90,
                        total_marks: pExam.total_marks || 100,
                        exam_date: pExam.exam_date,
                        exam_time: (pExam.start_time && pExam.end_time) ? `${pExam.start_time} - ${pExam.end_time}` : '10:00 AM - 12:00 PM',
                        status: pExam.status || 'ACTIVE',
                        is_results_published: false,
                        face_verification_required: pExam.face_verification_required !== 0,
                        college_name: pExam.college_name || 'ExamFort'
                    };
                }
            }

            if (!exam) return null;

            // Dynamically calculate sections & question counts strictly from questions table for this exam
            const [qRows] = await this.pool.query(
                `SELECT type, COUNT(*) as count, SUM(max_marks) as marks 
                 FROM questions 
                 WHERE exam_code = ? OR placement_exam_id = ?
                 GROUP BY type`,
                [exam.exam_code, exam.id]
            );

            const sections = [];
            const mcqStat = qRows.find(q => q.type === 'MCQ');
            const codingStat = qRows.find(q => q.type === 'CODING');
            const essayStat = qRows.find(q => q.type === 'PARAGRAPH');

            let currentQ = 1;
            if (mcqStat && mcqStat.count > 0) {
                const endQ = currentQ + mcqStat.count - 1;
                sections.push({
                    id: 'mcq',
                    name: 'Section 1: Aptitude & Reasoning',
                    type: 'MCQ',
                    icon: '📝',
                    question_range: mcqStat.count === 1 ? `${currentQ}` : `${currentQ} - ${endQ}`,
                    count: mcqStat.count,
                    marks: parseFloat(mcqStat.marks || 0),
                    description: `Multiple Choice • ${mcqStat.count} Question${mcqStat.count > 1 ? 's' : ''}`
                });
                currentQ = endQ + 1;
            }
            if (codingStat && codingStat.count > 0) {
                const endQ = currentQ + codingStat.count - 1;
                sections.push({
                    id: 'coding',
                    name: 'Section 2: Coding Assessment',
                    type: 'CODING',
                    icon: '💻',
                    question_range: codingStat.count === 1 ? `${currentQ}` : `${currentQ} - ${endQ}`,
                    count: codingStat.count,
                    marks: parseFloat(codingStat.marks || 0),
                    description: `Live Compiler • ${codingStat.count} Challenge${codingStat.count > 1 ? 's' : ''}`
                });
                currentQ = endQ + 1;
            }
            if (essayStat && essayStat.count > 0) {
                const endQ = currentQ + essayStat.count - 1;
                sections.push({
                    id: 'essay',
                    name: 'Section 3: Descriptive & Paragraph',
                    type: 'PARAGRAPH',
                    icon: '✍️',
                    question_range: essayStat.count === 1 ? `${currentQ}` : `${currentQ} - ${endQ}`,
                    count: essayStat.count,
                    marks: parseFloat(essayStat.marks || 0),
                    description: `Essay Response • ${essayStat.count} Question${essayStat.count > 1 ? 's' : ''}`
                });
            }

            exam.sections = sections;
            exam.total_questions = (mcqStat?.count || 0) + (codingStat?.count || 0) + (essayStat?.count || 0);
            if (sections.length > 0) {
                exam.total_marks = (parseFloat(mcqStat?.marks || 0) + parseFloat(codingStat?.marks || 0) + parseFloat(essayStat?.marks || 0));
            }
            exam.face_verification_required = Boolean(exam.face_verification_required);
            return exam;
        } catch (err) {
            console.error('❌ Error fetching exam details from MySQL:', err.message);
            return null;
        }
    }

    async getExamQuestions(examCode) {
        if (!this.pool) return [];
        try {
            const code = (examCode || '').trim();
            if (!code) return [];

            let [rows] = await this.pool.query(
                `SELECT id, exam_code, question_number, type, title, question_text, constraints,
                        sample_input, sample_output, explanation, entry_function, 
                        options, coding_starter_code, reference_solution, random_input_schema, 
                        public_test_cases, hidden_test_cases, public_weightage_marks, 
                        hidden_weightage_marks, max_marks, rubric_json 
                 FROM questions 
                 WHERE exam_code = ? 
                 ORDER BY question_number ASC`,
                [code]
            );

            if (rows.length === 0) {
                const [pRows] = await this.pool.query('SELECT id, exam_code FROM placement_exams WHERE exam_code = ? OR id = ? LIMIT 1', [code, code]);
                if (pRows.length > 0) {
                    const [pqRows] = await this.pool.query(
                        `SELECT id, exam_code, question_number, type, title, question_text, constraints,
                                sample_input, sample_output, explanation, entry_function, 
                                options, coding_starter_code, reference_solution, random_input_schema, 
                                public_test_cases, hidden_test_cases, public_weightage_marks, 
                                hidden_weightage_marks, max_marks, rubric_json 
                         FROM questions 
                         WHERE placement_exam_id = ? OR exam_code = ?
                         ORDER BY question_number ASC`,
                        [pRows[0].id, pRows[0].exam_code]
                    );
                    if (pqRows.length > 0) rows = pqRows;
                }
            }

            // Zero fallback to another exam's questions: strictly return this exam's configured questions
            return rows.map(q => {
                let starterCode = q.coding_starter_code;
                if (typeof starterCode === 'string') {
                    try { starterCode = JSON.parse(starterCode); } catch (_) {}
                }

                const secId = q.type === 'CODING' ? 'coding' : (q.type === 'PARAGRAPH' ? 'essay' : 'mcq');
                const secName = q.type === 'CODING' ? 'Section 2: Coding Assessment' : (q.type === 'PARAGRAPH' ? 'Section 3: Descriptive & Paragraph' : 'Section 1: Aptitude & Reasoning');

                return {
                    ...q,
                    section: secId,
                    section_name: secName,
                    options: typeof q.options === 'string' ? JSON.parse(q.options) : q.options,
                    coding_starter_code: starterCode,
                    public_test_cases: typeof q.public_test_cases === 'string' ? JSON.parse(q.public_test_cases) : q.public_test_cases,
                    hidden_test_cases: typeof q.hidden_test_cases === 'string' ? JSON.parse(q.hidden_test_cases) : q.hidden_test_cases,
                    rubric_json: typeof q.rubric_json === 'string' ? JSON.parse(q.rubric_json) : q.rubric_json
                };
            });
        } catch (err) {
            console.error('❌ Error fetching questions from MySQL:', err.message);
            return [];
        }
    }

    async getAllUserExams(candidateId) {
        if (!this.pool) return { success: false, exams: [] };
        try {
            const [exams] = await this.pool.query(
                `SELECT * FROM exams ORDER BY id ASC`
            );

            const [submissions] = await this.pool.query(
                `SELECT s.*, e.title as exam_title 
                 FROM submissions s
                 LEFT JOIN exams e ON s.exam_code = e.exam_code
                 WHERE s.candidate_id = ? 
                    OR s.candidate_id = (SELECT student_id FROM users WHERE id = ? OR student_id = ? LIMIT 1)
                    OR s.candidate_id = (SELECT id FROM users WHERE id = ? OR student_id = ? LIMIT 1)`,
                [candidateId, candidateId, candidateId, candidateId, candidateId]
            );

            const subMap = new Map();
            submissions.forEach(s => subMap.set(s.exam_code, s));

            const [activities] = await this.pool.query(
                `SELECT * FROM student_activities 
                 WHERE (student_id = ? OR student_id = (SELECT student_id FROM users WHERE id = ? LIMIT 1))
                 ORDER BY id DESC LIMIT 10`,
                [candidateId, candidateId]
            );

            const formatted = exams.map(ex => {
                const sub = subMap.get(ex.exam_code);
                const hasSub = !!sub;

                let icon = '📝';
                const cat = (ex.category || '').toLowerCase();
                if (cat.includes('coding') || cat.includes('programming')) icon = '💻';
                else if (cat.includes('database') || cat.includes('sql')) icon = '🗄️';
                else if (cat.includes('system') || cat.includes('architecture')) icon = '⚙️';
                else if (cat.includes('aptitude') || cat.includes('reasoning')) icon = '🧠';

                return {
                    id: ex.id,
                    exam_code: ex.exam_code,
                    title: ex.title,
                    description: ex.description || 'Comprehensive assessment evaluating key domain skills.',
                    category: ex.category || 'Technical Assessment',
                    icon: icon,
                    duration_minutes: ex.duration_minutes || 120,
                    total_marks: ex.total_marks || 120,
                    total_questions: ex.total_questions || 13,
                    exam_date: ex.exam_date,
                    exam_time: ex.exam_time || '10:00 AM - 12:00 PM',
                    status: ex.status,
                    is_results_published: Boolean(ex.is_results_published),
                    is_submitted: hasSub,
                    submission: hasSub ? {
                        id: sub.id,
                        total_score: sub.total_score,
                        mcq_score: sub.mcq_score,
                        coding_score: (sub.coding_public_score || 0) + (sub.coding_hidden_score || 0),
                        essay_score: sub.essay_score || 0,
                        percentage: Math.round(((sub.total_score || 0) / (ex.total_marks || 120)) * 100),
                        submitted_at: sub.submission_timestamp
                    } : null
                };
            });

            return {
                success: true,
                exams: formatted,
                recentActivities: activities || []
            };
        } catch (err) {
            console.error('❌ Error fetching user exams from MySQL:', err.message);
            return { success: false, exams: [] };
        }
    }

    // ==========================================
    // 3. EXAM EVALUATION & ATTEMPTS RECORDING
    // ==========================================
    async submitExam(candidateId, examCode, answers, reattemptReason) {
        if (!this.pool) return { success: false, message: 'MySQL offline.' };

        try {
            const code = examCode || 'NAT-2026-EXAM';

            // Check existing submissions count for this candidate and exam
            const [existing] = await this.pool.query(
                `SELECT id, attempt_number, total_score, submission_timestamp 
                 FROM submissions 
                 WHERE (candidate_id = ? 
                    OR candidate_id = (SELECT student_id FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                    OR candidate_id = (SELECT CAST(id AS VARCHAR) FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1))
                   AND exam_code = ? 
                 ORDER BY submission_timestamp ASC`,
                [candidateId, candidateId, candidateId, candidateId, candidateId, code]
            );

            const attemptCount = (existing && existing.length) ? existing.length : 0;
            const currentAttemptNumber = attemptCount + 1;
            const finalReattemptReason = currentAttemptNumber > 1 ? (reattemptReason || 'Reattempt initiated by candidate') : null;

            // 1. Evaluate MCQs directly against MySQL/Postgres questions table
            const [mcqs] = await this.pool.query(
                "SELECT question_number, correct_answer, max_marks FROM questions WHERE exam_code = ? AND type = 'MCQ'",
                [code]
            );

            let mcqScore = 0;
            mcqs.forEach(q => {
                const candAns = answers[q.question_number]?.selectedOption;
                if (candAns && candAns.toUpperCase() === (q.correct_answer || '').toUpperCase()) {
                    mcqScore += parseFloat(q.max_marks || 2.0);
                }
            });

            // 2. Evaluate Coding Questions with Dynamic Sandbox Judge
            const [codingQuestions] = await this.pool.query(
                "SELECT * FROM questions WHERE exam_code = ? AND type = 'CODING'",
                [code]
            );

            let totalCodingScore = 0;
            let totalCodingPublicScore = 0;
            let totalCodingHiddenScore = 0;
            const evalReport = { mcqScore, codingDetails: {}, essayDetails: {} };

            for (const cq of codingQuestions) {
                const qNum = cq.question_number;
                const candCode = answers[qNum]?.codeSolution;

                if (candCode && candCode.trim().length > 0) {
                    const evalResult = judgeEngine.evaluateSubmission(cq, candCode, 'javascript');
                    totalCodingScore += evalResult.score;

                    const pubPart = evalResult.sets?.publicSet?.earned || 0;
                    const hidPart = (evalResult.sets?.hiddenSet?.earned || 0) + (evalResult.sets?.randomSet?.earned || 0);
                    totalCodingPublicScore += pubPart;
                    totalCodingHiddenScore += hidPart;

                    evalReport.codingDetails[`Q${qNum}`] = evalResult;
                }
            }

            // 3. Evaluate Paragraph / Essay Questions with Dynamic Rubric Evaluator
            const [essayQuestions] = await this.pool.query(
                "SELECT * FROM questions WHERE exam_code = ? AND type = 'PARAGRAPH'",
                [code]
            );

            let totalEssayScore = 0;
            for (const eq of essayQuestions) {
                const qNum = eq.question_number;
                const candEssay = answers[qNum]?.essayText || answers[qNum]?.answer || '';
                const essayEval = essayEvaluator.evaluate(candEssay, eq);
                totalEssayScore += essayEval.score;
                evalReport.essayDetails[`Q${qNum}`] = essayEval;
            }

            const totalScore = mcqScore + totalCodingScore + totalEssayScore;
            const answersJson = JSON.stringify(answers || {});
            const reportJson = JSON.stringify(evalReport);

            // 4. Save to database submissions table with attempt_number and reattempt_reason
            const [insRes] = await this.pool.query(
                `INSERT INTO submissions 
                 (candidate_id, exam_code, answers_json, mcq_score, coding_public_score, coding_hidden_score, essay_score, total_score, attempt_number, reattempt_reason, evaluation_report) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)`,
                [candidateId, code, answersJson, mcqScore, totalCodingPublicScore, totalCodingHiddenScore, totalEssayScore, totalScore, currentAttemptNumber, finalReattemptReason, reportJson]
            );

            // Clear candidate draft
            await this.pool.query('DELETE FROM candidate_drafts WHERE candidate_id = ? AND exam_code = ?', [candidateId, code]).catch(() => {});

            // Consume active reattempt authorization
            try {
                await this.pool.query(
                    `UPDATE exam_reattempt_authorizations 
                     SET status = 'CONSUMED', consumed_at = CURRENT_TIMESTAMP 
                     WHERE (candidate_id = ? 
                        OR candidate_id = (SELECT student_id FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                        OR candidate_id = (SELECT CAST(id AS VARCHAR) FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                        OR candidate_id = 'ALL')
                       AND exam_code = ? 
                       AND status = 'ACTIVE'`,
                    [candidateId, candidateId, candidateId, candidateId, candidateId, code]
                );
            } catch (_) {}

            // Record student activity in database
            try {
                const actTitle = currentAttemptNumber > 1 
                    ? `Reattempted Exam: ${code} (Attempt #${currentAttemptNumber})` 
                    : `Completed Exam: ${code}`;
                const actDesc = currentAttemptNumber > 1 
                    ? `Scored ${totalScore} pts (Reason: ${finalReattemptReason})` 
                    : `Scored ${totalScore} points`;

                await this.pool.query(
                    `INSERT INTO student_activities (student_id, title, activity_title, description, score_info, score, status, badge, activity_type, type)
                     VALUES (?, ?, ?, ?, ?, ?, 'Completed', '🏆 Submitted', 'EXAM', 'EXAM')`,
                    [candidateId, actTitle, actTitle, actDesc, `Score: ${totalScore}`, totalScore]
                );
            } catch (_) {}

            return {
                success: true,
                submissionId: insRes.insertId,
                attemptNumber: currentAttemptNumber,
                reattemptReason: finalReattemptReason,
                mcqScore,
                codingPublicScore: totalCodingPublicScore,
                codingHiddenScore: totalCodingHiddenScore,
                essayScore: totalEssayScore,
                totalScore,
                evaluationReport: evalReport
            };
        } catch (err) {
            console.error('❌ Error saving submission to MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async checkCandidateAttempt(candidateId, examCode) {
        if (!this.pool) return { success: true, hasSubmitted: false, isReattemptAuthorized: false };
        try {
            const code = examCode || 'NAT-2026-EXAM';

            // Check if Admin/Faculty has authorized a reattempt
            let isReattemptAuthorized = false;
            let reattemptAuthReason = null;
            let authorizedBy = null;

            try {
                const [authRows] = await this.pool.query(
                    `SELECT id, reattempt_reason, authorized_by, created_at 
                     FROM exam_reattempt_authorizations 
                     WHERE (candidate_id = ? 
                        OR candidate_id = (SELECT student_id FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                        OR candidate_id = (SELECT CAST(id AS VARCHAR) FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                        OR candidate_id = 'ALL')
                       AND exam_code = ? 
                       AND status = 'ACTIVE' 
                     ORDER BY id DESC LIMIT 1`,
                    [candidateId, candidateId, candidateId, candidateId, candidateId, code]
                );
                if (authRows && authRows.length > 0) {
                    isReattemptAuthorized = true;
                    reattemptAuthReason = authRows[0].reattempt_reason;
                    authorizedBy = authRows[0].authorized_by;
                }
            } catch (_) {}

            const [rows] = await this.pool.query(
                `SELECT s.id, s.attempt_number, s.reattempt_reason, s.submission_timestamp, s.total_score, s.mcq_score, s.coding_public_score, s.coding_hidden_score, s.essay_score 
                 FROM submissions s 
                 WHERE (s.candidate_id = ? 
                    OR s.candidate_id = (SELECT student_id FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1)
                    OR s.candidate_id = (SELECT CAST(id AS VARCHAR) FROM users WHERE student_id = ? OR CAST(id AS VARCHAR) = ? LIMIT 1))
                   AND s.exam_code = ? 
                 ORDER BY s.submission_timestamp ASC`,
                [candidateId, candidateId, candidateId, candidateId, candidateId, code]
            );

            if (rows && rows.length > 0) {
                const attempts = rows.map((r, idx) => ({
                    id: r.id,
                    attemptNumber: r.attempt_number || (idx + 1),
                    reattemptReason: r.reattempt_reason,
                    totalScore: parseFloat(r.total_score || 0),
                    mcqScore: parseFloat(r.mcq_score || 0),
                    codingPublicScore: parseFloat(r.coding_public_score || 0),
                    codingHiddenScore: parseFloat(r.coding_hidden_score || 0),
                    essayScore: parseFloat(r.essay_score || 0),
                    submittedAt: r.submission_timestamp
                }));

                const firstAttempt = attempts[0];
                const latestAttempt = attempts[attempts.length - 1];
                const scoreDiff = attempts.length > 1 ? (latestAttempt.totalScore - firstAttempt.totalScore) : 0;

                return {
                    success: true,
                    hasSubmitted: true,
                    isReattemptAuthorized,
                    reattemptReason: reattemptAuthReason,
                    authorizedBy,
                    attemptCount: attempts.length,
                    attempts,
                    firstAttempt,
                    latestAttempt,
                    scoreDiff,
                    submissionId: latestAttempt.id,
                    submittedAt: latestAttempt.submittedAt,
                    totalScore: latestAttempt.totalScore
                };
            }
            return { success: true, hasSubmitted: false, isReattemptAuthorized, reattemptReason: reattemptAuthReason, authorizedBy, attemptCount: 0, attempts: [] };
        } catch (err) {
            console.error('❌ Error checking candidate attempt:', err.message);
            return { success: true, hasSubmitted: false, isReattemptAuthorized: false, attemptCount: 0, attempts: [] };
        }
    }

    async getCandidateSubmission(candidateId, examCode, attemptId) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const code = examCode || 'NAT-2026-EXAM';
            let query = `
                SELECT s.*, e.title as exam_title, e.total_marks as max_exam_marks, e.total_questions 
                FROM submissions s 
                LEFT JOIN exams e ON s.exam_code = e.exam_code
                WHERE (s.candidate_id = ? 
                   OR s.candidate_id = (SELECT student_id FROM users WHERE id = ? OR student_id = ? LIMIT 1)
                   OR s.candidate_id = (SELECT id FROM users WHERE id = ? OR student_id = ? LIMIT 1))
                  AND s.exam_code = ?
            `;
            const params = [candidateId, candidateId, candidateId, candidateId, candidateId, code];

            if (attemptId) {
                query += ' AND s.id = ?';
                params.push(attemptId);
            } else {
                query += ' ORDER BY s.submission_timestamp DESC LIMIT 1';
            }

            const [rows] = await this.pool.query(query, params);

            if (rows.length > 0) {
                const sub = rows[0];
                return {
                    success: true,
                    submission: {
                        id: sub.id,
                        candidateId: sub.candidate_id,
                        examCode: sub.exam_code,
                        examTitle: sub.exam_title,
                        attemptNumber: sub.attempt_number || 1,
                        reattemptReason: sub.reattempt_reason,
                        totalScore: sub.total_score,
                        mcqScore: sub.mcq_score,
                        codingPublicScore: sub.coding_public_score,
                        codingHiddenScore: sub.coding_hidden_score,
                        essayScore: sub.essay_score || 0,
                        maxMarks: sub.max_exam_marks || 120,
                        totalQuestions: sub.total_questions || 13,
                        submittedAt: sub.submission_timestamp,
                        evaluationReport: typeof sub.evaluation_report === 'string' ? JSON.parse(sub.evaluation_report) : sub.evaluation_report
                    }
                };
            }
            return { success: false, message: 'No submission found for this candidate.' };
        } catch (err) {
            console.error('❌ Error fetching submission from MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async getAllCandidateAttempts(candidateId, examCode) {
        if (!this.pool) return { success: false, attempts: [] };
        try {
            const code = examCode || 'NAT-2026-EXAM';
            const [rows] = await this.pool.query(
                `SELECT s.*, e.title as exam_title, e.total_marks as max_exam_marks 
                 FROM submissions s 
                 LEFT JOIN exams e ON s.exam_code = e.exam_code 
                 WHERE (s.candidate_id = ? 
                    OR s.candidate_id = (SELECT student_id FROM users WHERE id = ? OR student_id = ? LIMIT 1)
                    OR s.candidate_id = (SELECT id FROM users WHERE id = ? OR student_id = ? LIMIT 1))
                   AND s.exam_code = ? 
                 ORDER BY s.submission_timestamp ASC`,
                [candidateId, candidateId, candidateId, candidateId, candidateId, code]
            );

            return {
                success: true,
                attempts: rows.map((r, idx) => ({
                    id: r.id,
                    attemptNumber: r.attempt_number || (idx + 1),
                    reattemptReason: r.reattempt_reason,
                    totalScore: parseFloat(r.total_score || 0),
                    mcqScore: parseFloat(r.mcq_score || 0),
                    codingPublicScore: parseFloat(r.coding_public_score || 0),
                    codingHiddenScore: parseFloat(r.coding_hidden_score || 0),
                    essayScore: parseFloat(r.essay_score || 0),
                    submittedAt: r.submission_timestamp,
                    examTitle: r.exam_title,
                    maxMarks: r.max_exam_marks
                }))
            };
        } catch (err) {
            console.error('❌ Error fetching all candidate attempts:', err.message);
            return { success: false, attempts: [] };
        }
    }

    async getExamInstructions(examCode) {
        if (!this.pool) return { success: true, instructions: [] };
        try {
            const [rows] = await this.pool.query(
                'SELECT * FROM exam_instructions WHERE exam_code = ? ORDER BY display_order ASC',
                [examCode || 'NAT-2026-EXAM']
            );
            return { success: true, instructions: rows };
        } catch (err) {
            console.error('❌ Error fetching exam instructions:', err.message);
            return { success: true, instructions: [] };
        }
    }

    async rescheduleExam(examCode, { examDate, examTime, durationMinutes, resetSubmissions, candidateId, candidateIds, allowReattempt, reason }) {
        if (!this.pool) return { success: false, message: 'MySQL offline.' };
        try {
            const code = examCode || 'NAT-2026-EXAM';
            const targetCandidateIds = candidateIds && Array.isArray(candidateIds) && candidateIds.length > 0 
                ? candidateIds 
                : (candidateId ? [candidateId] : []);

            // 1. Reschedule Selected Candidates Only
            if (targetCandidateIds.length > 0) {
                for (const cId of targetCandidateIds) {
                    // Update placement_exam_candidates if applicable
                    try {
                        const pUpdates = [];
                        const pParams = [];
                        if (examDate) { pUpdates.push('scheduled_date = ?'); pParams.push(examDate); }
                        if (examTime) { pUpdates.push('scheduled_start_time = ?'); pParams.push(examTime); }
                        pUpdates.push("attempt_status = 'PENDING'");
                        pParams.push(cId, code);

                        await this.pool.query(
                            `UPDATE placement_exam_candidates SET ${pUpdates.join(', ')} WHERE (student_id = ? OR CAST(id AS VARCHAR) = ?) AND exam_code = ?`,
                            pParams
                        );
                    } catch (_) {}

                    // Clear drafts or reset submission if requested
                    await this.pool.query('DELETE FROM candidate_drafts WHERE exam_code = ? AND candidate_id = ?', [code, cId]).catch(() => {});

                    if (resetSubmissions) {
                        await this.pool.query(
                            `DELETE FROM submissions WHERE exam_code = ? AND (candidate_id = ? OR candidate_id = (SELECT student_id FROM users WHERE id = ? OR student_id = ? LIMIT 1))`,
                            [code, cId, cId, cId]
                        ).catch(() => {});
                    }
                }

                return {
                    success: true,
                    isSelective: true,
                    targetCandidatesCount: targetCandidateIds.length,
                    message: `Successfully rescheduled examination for ${targetCandidateIds.length} selected candidate(s) on ${examDate || 'scheduled date'} (${examTime || 'time slot'}).`
                };
            }

            // 2. Reschedule Entire Exam for All Enrolled Students
            const updates = ['status = "ACTIVE"'];
            const params = [];

            if (examDate) {
                updates.push('exam_date = ?');
                params.push(examDate);
            }
            if (examTime) {
                updates.push('exam_time = ?');
                params.push(examTime);
            }
            if (durationMinutes) {
                updates.push('duration_minutes = ?');
                params.push(parseInt(durationMinutes, 10));
            }

            params.push(code);
            await this.pool.query(`UPDATE exams SET ${updates.join(', ')} WHERE exam_code = ?`, params);

            if (resetSubmissions) {
                await this.pool.query('DELETE FROM submissions WHERE exam_code = ?', [code]);
                await this.pool.query('DELETE FROM candidate_drafts WHERE exam_code = ?', [code]);
            }

            const [rows] = await this.pool.query('SELECT * FROM exams WHERE exam_code = ? LIMIT 1', [code]);
            return {
                success: true,
                isSelective: false,
                message: `Exam ${code} has been successfully rescheduled for all candidates to ${examDate || rows[0]?.exam_date} (${examTime || rows[0]?.exam_time}).`,
                exam: rows[0]
            };
        } catch (err) {
            console.error('❌ Error rescheduling exam:', err.message);
            return { success: false, message: err.message };
        }
    }

    // ==========================================
    // 4. AUTO-SAVE DRAFTS & VIOLATIONS
    // ==========================================

    async saveDraft(candidateId, examCode, answers) {
        if (!this.pool) return { success: false, message: 'Database offline' };
        try {
            const eCode = examCode || 'NAT-2026-EXAM';
            const answersJson = JSON.stringify(answers || {});
            const [existing] = await this.pool.query(
                'SELECT id FROM candidate_drafts WHERE candidate_id = ? AND exam_code = ? LIMIT 1',
                [candidateId, eCode]
            );
            if (existing && existing.length > 0) {
                await this.pool.query(
                    'UPDATE candidate_drafts SET answers_json = ?, last_saved_at = CURRENT_TIMESTAMP WHERE candidate_id = ? AND exam_code = ?',
                    [answersJson, candidateId, eCode]
                );
            } else {
                await this.pool.query(
                    'INSERT INTO candidate_drafts (candidate_id, exam_code, answers_json) VALUES (?, ?, ?)',
                    [candidateId, eCode, answersJson]
                );
            }
            return { success: true, savedAt: new Date().toISOString() };
        } catch (err) {
            console.error('saveDraft error:', err);
            return { success: false, message: err.message };
        }
    }

    async getDraft(candidateId, examCode) {
        if (!this.pool) return { success: false, answers: null };
        try {
            const [rows] = await this.pool.query(
                'SELECT answers_json, last_saved_at FROM candidate_drafts WHERE candidate_id = ? AND exam_code = ? LIMIT 1',
                [candidateId, examCode || 'NAT-2026-EXAM']
            );
            if (rows.length > 0) {
                const answers = JSON.parse(rows[0].answers_json);
                return { success: true, answers, lastSavedAt: rows[0].last_saved_at };
            }
            return { success: true, answers: null };
        } catch (err) {
            return { success: false, message: err.message };
        }
    }

    async logViolation(violation) {
        if (!this.pool) return null;
        try {
            const [res] = await this.pool.query(
                'INSERT INTO violations (candidate_id, exam_code, violation_type, details) VALUES (?, ?, ?, ?)',
                [violation.candidateId || 'ANONYMOUS', violation.examCode || 'NAT-2026-EXAM', violation.type, violation.details]
            );
            return { id: res.insertId };
        } catch (err) {
            return null;
        }
    }

    async getAllViolations() {
        if (!this.pool) return [];
        try {
            const [rows] = await this.pool.query('SELECT * FROM violations ORDER BY timestamp DESC LIMIT 100');
            return rows;
        } catch (_) {
            return [];
        }
    }

    // ==========================================
    // 5. USER PROFILES & FACE VERIFICATION
    // ==========================================

    async getUserProfile(userId) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const [rows] = await this.pool.query(
                'SELECT * FROM users WHERE id = ? OR student_id = ? OR email = ? LIMIT 1',
                [userId, userId, userId]
            );
            if (rows.length === 0) {
                return { success: false, message: 'User not found in MySQL.' };
            }

            const p = rows[0];
            delete p.password;
            const stuId = p.student_id || p.id || userId;

            // Live metrics computed from MySQL tables
            const [exCnt] = await this.pool.query('SELECT COUNT(*) as total FROM exams');
            const totalEnrolled = exCnt[0]?.total || 0;

            const [subRows] = await this.pool.query(
                `SELECT s.total_score, s.submission_timestamp, e.total_marks, e.title as exam_title,
                        ROUND(((s.total_score) / (e.total_marks)) * 100) as pct
                 FROM submissions s
                 JOIN exams e ON s.exam_code = e.exam_code
                 WHERE s.candidate_id = ? 
                    OR s.candidate_id = ? 
                    OR s.candidate_id = (SELECT student_id FROM users WHERE id = ? LIMIT 1)
                    OR s.candidate_id = (SELECT id FROM users WHERE student_id = ? LIMIT 1)
                 ORDER BY s.submission_timestamp DESC`,
                [userId, stuId, userId, stuId]
            );

            const completedCount = subRows.length;
            const upcomingCount = Math.max(0, totalEnrolled - completedCount);

            let avgScore = 0;
            let bestScore = 0;
            let bestExamTitle = '';

            if (completedCount > 0) {
                const pcts = subRows.map(r => r.pct || 0);
                avgScore = Math.round(pcts.reduce((a, b) => a + b, 0) / completedCount);
                bestScore = Math.max(...pcts);
                const bestRow = subRows.find(r => r.pct === bestScore);
                if (bestRow && bestRow.exam_title) {
                    bestExamTitle = bestRow.exam_title;
                }
            }

            p.exams_enrolled = totalEnrolled;
            p.exams_completed = completedCount;
            p.upcoming_exams_count = upcomingCount;
            p.average_score = avgScore || p.average_score || 0;
            p.best_score = bestScore || p.best_score || 0;
            p.best_score_title = bestExamTitle || 'Academic Assessment';
            p.current_streak_days = p.current_streak_days || 7;

            return { success: true, profile: p };
        } catch (err) {
            console.error('❌ Error fetching user profile from MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async updateUserProfile(userId, updateData) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const allowed = [
                'full_name', 'email', 'phone', 'dob', 'location', 'college_name',
                'course', 'stream', 'bio', 'goal', 'achievements', 'interests',
                'profile_completion_pct', 'batch_years', 'gemini_api_key'
            ];
            const fields = [];
            const values = [];

            allowed.forEach(f => {
                if (updateData[f] !== undefined) {
                    fields.push(`\`${f}\` = ?`);
                    values.push(updateData[f]);
                }
            });

            if (fields.length === 0) {
                return { success: false, message: 'No valid fields provided for update.' };
            }

            values.push(userId, userId);
            await this.pool.query(
                `UPDATE users SET ${fields.join(', ')} WHERE id = ? OR student_id = ?`,
                values
            );

            return this.getUserProfile(userId);
        } catch (err) {
            console.error('❌ Error updating user profile in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async saveVerifiedFace(candidateId, snapshotUrl) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            // Log verification event into audit trail without overwriting master institutional avatar_url
            try {
                await this.pool.query(
                    `INSERT INTO student_activities (student_id, title, activity_title, description, activity_type, type, badge) 
                     VALUES (?, 'Biometric Identity Verified', 'Biometric Identity Verified', 'Live webcam facial biometrics matched with registered institutional profile.', 'VERIFICATION', 'VERIFICATION', '🛡️ Verified')`,
                    [candidateId]
                );
            } catch (_) {}

            const profileRes = await this.getUserProfile(candidateId);
            return { success: true, profile: profileRes.profile };
        } catch (err) {
            console.error('❌ Error recording verified face:', err.message);
            return { success: false, message: err.message };
        }
    }

    async setExamFaceVerification(examCode, isRequired) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            await this.pool.query(
                'UPDATE exams SET face_verification_required = ? WHERE exam_code = ?',
                [isRequired ? 1 : 0, examCode]
            );
            return {
                success: true,
                message: `Face verification requirement for ${examCode} updated to: ${isRequired ? 'ENABLED' : 'DISABLED'}.`,
                faceVerificationRequired: Boolean(isRequired)
            };
        } catch (err) {
            return { success: false, message: err.message };
        }
    }

    // ==========================================
    // 6. COURSES & LESSONS LEARNING ENGINE
    // ==========================================

    async getAllCourses() {
        const fallbackCourses = [
            { course_id: 'course-cpp', title: 'Programming in C++', description: 'Master C++ programming from basics to advanced concepts with hands-on examples.', icon: '</>', color: '#6366f1', lessons_count: 18, duration_text: '6h 20m', level: 'Intermediate', progress_percent: 42, completed_lessons: 8, language: 'English', certificate: 'Yes', last_updated: 'May 2026' },
            { course_id: 'course-aptitude', title: 'Aptitude Fundamentals', description: 'Learn the basics of quantitative aptitude, number system, percentages, and ratios.', icon: '🧠', color: '#ec4899', lessons_count: 12, duration_text: '3h 45m', level: 'Beginner', progress_percent: 65, completed_lessons: 8, language: 'English', certificate: 'Yes', last_updated: 'May 2026' },
            { course_id: 'course-dsa', title: 'Data Structures & Algorithms', description: 'Learn essential data structures and algorithms for problem solving and coding interviews.', icon: '💾', color: '#f59e0b', lessons_count: 20, duration_text: '8h 15m', level: 'Advanced', progress_percent: 25, completed_lessons: 5, language: 'English', certificate: 'Yes', last_updated: 'May 2026' },
            { course_id: 'course-reasoning', title: 'Logical Reasoning', description: 'Improve your logical thinking skills with practice questions and detailed explanations.', icon: '💡', color: '#8b5cf6', lessons_count: 10, duration_text: '2h 30m', level: 'Beginner', progress_percent: 80, completed_lessons: 8, language: 'English', certificate: 'Yes', last_updated: 'May 2026' }
        ];

        if (!this.pool) return { success: true, courses: fallbackCourses };
        try {
            const [rows] = await this.pool.query('SELECT * FROM courses ORDER BY id ASC');
            if (rows && rows.length > 0) {
                return { success: true, courses: rows };
            }
            return { success: true, courses: fallbackCourses };
        } catch (err) {
            console.error('❌ Error fetching courses from database:', err.message);
            return { success: true, courses: fallbackCourses };
        }
    }

    async getCourseDetails(courseId) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const cid = courseId || 'course-cpp';
            const [courseRows] = await this.pool.query('SELECT * FROM courses WHERE course_id = ? LIMIT 1', [cid]);
            if (courseRows.length === 0) {
                return { success: false, message: 'Course not found in MySQL.' };
            }
            const course = courseRows[0];

            const [lessonRows] = await this.pool.query(
                'SELECT * FROM course_lessons WHERE course_id = ? ORDER BY module_num ASC, id ASC',
                [cid]
            );

            return {
                success: true,
                course,
                lessons: lessonRows
            };
        } catch (err) {
            console.error('❌ Error fetching course details from MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async completeLesson(courseId, lessonNum, isCompleted) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const cid = courseId || 'course-cpp';
            await this.pool.query(
                'UPDATE course_lessons SET is_completed = ? WHERE course_id = ? AND lesson_num = ?',
                [isCompleted ? 1 : 0, cid, lessonNum]
            );

            const [cntRows] = await this.pool.query(
                'SELECT COUNT(*) as total, SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed FROM course_lessons WHERE course_id = ?',
                [cid]
            );

            const total = cntRows[0].total || 18;
            const completed = cntRows[0].completed || 0;
            const pct = Math.round((completed / total) * 100);

            await this.pool.query(
                'UPDATE courses SET completed_lessons = ?, progress_percent = ? WHERE course_id = ?',
                [completed, pct, cid]
            );

            return {
                success: true,
                courseId: cid,
                lessonNum,
                isCompleted,
                completedLessons: completed,
                totalLessons: total,
                progressPercent: pct
            };
        } catch (err) {
            console.error('❌ Error completing lesson in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async updateLessonProgress(courseId, lessonNum, isCompleted) {
        return this.completeLesson(courseId, lessonNum, isCompleted);
    }

    async getTopicLearningData(courseId, lessonNum, candidateId = null) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const cid = courseId || 'course-cpp';
            const lnum = lessonNum || '1.1';
            const candId = candidateId || 'STU123456';

            // 1. Lesson overview
            const [lessonRows] = await this.pool.query(
                'SELECT * FROM course_lessons WHERE course_id = ? AND lesson_num = ? LIMIT 1',
                [cid, lnum]
            );
            const lesson = lessonRows.length > 0 ? lessonRows[0] : null;

            // 2. Rich notes
            const [contentRows] = await this.pool.query(
                'SELECT * FROM course_lesson_content WHERE course_id = ? AND lesson_num = ? LIMIT 1',
                [cid, lnum]
            );
            const content = contentRows.length > 0 ? contentRows[0] : null;

            // 3. Topic MCQs
            const [mcqRows] = await this.pool.query(
                'SELECT * FROM course_topic_mcqs WHERE course_id = ? AND lesson_num = ? ORDER BY question_number ASC',
                [cid, lnum]
            );
            const mcqs = mcqRows.map(m => ({
                id: m.id,
                question_number: m.question_number,
                question_text: m.question_text,
                options: typeof m.options_json === 'string' ? JSON.parse(m.options_json) : m.options_json,
                correct_key: m.correct_key,
                explanation: m.explanation
            }));

            // 4. Topic Coding Problems
            const [codeRows] = await this.pool.query(
                'SELECT * FROM course_topic_coding WHERE course_id = ? AND lesson_num = ? ORDER BY problem_number ASC, id ASC',
                [cid, lnum]
            );
            const codingProblems = codeRows.map((c, idx) => ({
                id: c.id,
                problem_number: c.problem_number || (idx + 1),
                title: c.title,
                problem_statement: c.problem_statement,
                difficulty: c.difficulty,
                constraints: c.constraints_text,
                sample_input: c.sample_input,
                sample_output: c.sample_output,
                starter_codes: {
                    cpp: c.starter_code_cpp,
                    py: c.starter_code_py,
                    java: c.starter_code_java,
                    js: c.starter_code_js
                },
                test_cases: typeof c.test_cases_json === 'string' ? JSON.parse(c.test_cases_json) : c.test_cases_json
            }));
            const codingProblem = codingProblems.length > 0 ? codingProblems[0] : null;

            // 5. Previous practice submissions
            let lastCodingSubmission = null;
            let lastMcqSubmission = null;
            try {
                const [codingSubs] = await this.pool.query(
                    'SELECT * FROM course_practice_submissions WHERE candidate_id = ? AND course_id = ? AND lesson_num = ? AND practice_type = "CODING" ORDER BY id DESC LIMIT 1',
                    [candId, cid, lnum]
                );
                if (codingSubs.length > 0) {
                    const raw = codingSubs[0].details_json;
                    lastCodingSubmission = typeof raw === 'string' ? JSON.parse(raw) : raw;
                }

                const [mcqSubs] = await this.pool.query(
                    'SELECT * FROM course_practice_submissions WHERE candidate_id = ? AND course_id = ? AND lesson_num = ? AND practice_type = "MCQ" ORDER BY id DESC LIMIT 1',
                    [candId, cid, lnum]
                );
                if (mcqSubs.length > 0) {
                    const raw = mcqSubs[0].details_json;
                    lastMcqSubmission = typeof raw === 'string' ? JSON.parse(raw) : raw;
                }
            } catch (_) {}

            // 6. Drafts
            const drafts = {};
            let draftMcqAnswers = null;
            try {
                const [draftRows] = await this.pool.query(
                    'SELECT problem_number, language, draft_code, mcq_answers_json, updated_at FROM course_practice_drafts WHERE candidate_id = ? AND course_id = ? AND lesson_num = ?',
                    [candId, cid, lnum]
                );
                draftRows.forEach(d => {
                    drafts[d.problem_number] = {
                        problemNumber: d.problem_number,
                        language: d.language,
                        code: d.draft_code,
                        updatedAt: d.updated_at
                    };
                    if (d.mcq_answers_json) {
                        draftMcqAnswers = typeof d.mcq_answers_json === 'string' ? JSON.parse(d.mcq_answers_json) : d.mcq_answers_json;
                    }
                });
            } catch (_) {}

            // 7. All Lessons in course for navigation drawer
            const [allLessons] = await this.pool.query(
                'SELECT module_num, module_title, lesson_num, lesson_title, duration_text, is_completed FROM course_lessons WHERE course_id = ? ORDER BY module_num ASC, id ASC',
                [cid]
            );

            return {
                success: true,
                courseId: cid,
                lessonNum: lnum,
                lesson,
                content,
                mcqs,
                codingProblems,
                codingProblem,
                drafts,
                draftMcqAnswers,
                lastCodingSubmission,
                lastMcqSubmission,
                allLessons
            };
        } catch (err) {
            console.error('❌ Error fetching topic learning data from MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async savePracticeDraft(candidateId, courseId, lessonNum, problemNumber, language, draftCode, mcqAnswers) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const candId = candidateId || 'STU123456';
            const cid = courseId || 'course-cpp';
            const lnum = lessonNum || '1.1';
            const pnum = problemNumber || 1;
            const lang = language || 'cpp';
            const answersJson = mcqAnswers ? JSON.stringify(mcqAnswers) : null;

            await this.pool.query(
                `INSERT INTO course_practice_drafts (candidate_id, course_id, lesson_num, problem_number, language, draft_code, mcq_answers_json, updated_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
                 ON DUPLICATE KEY UPDATE 
                     draft_code = COALESCE(VALUES(draft_code), draft_code),
                     language = COALESCE(VALUES(language), language),
                     mcq_answers_json = COALESCE(VALUES(mcq_answers_json), mcq_answers_json),
                     updated_at = NOW()`,
                [candId, cid, lnum, pnum, lang, draftCode, answersJson]
            );
            return { success: true, message: 'Draft auto-saved successfully in MySQL.', savedAt: new Date().toISOString() };
        } catch (err) {
            console.error('❌ Error saving practice draft in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async submitCodingPractice(candidateId, courseId, lessonNum, problemNumber, language, code, results, allPassed) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const candId = candidateId || 'STU123456';
            const cid = courseId || 'course-cpp';
            const lnum = lessonNum || '1.1';
            const pnum = problemNumber || 1;
            const passedCount = (results || []).filter(r => r.passed).length;
            const totalCount = (results || []).length;
            const payload = {
                problemNumber: pnum,
                language: language || 'cpp',
                code,
                totalPassed: passedCount,
                totalCases: totalCount,
                allPassed: Boolean(allPassed),
                results: results || [],
                submittedAt: new Date().toISOString()
            };

            await this.pool.query(
                `INSERT INTO course_practice_submissions (candidate_id, course_id, lesson_num, practice_type, score, total_score, passed, details_json, submitted_at)
                 VALUES (?, ?, ?, 'CODING', ?, ?, ?, ?, NOW())`,
                [candId, cid, lnum, passedCount, totalCount, allPassed ? 1 : 0, JSON.stringify(payload)]
            );

            try {
                await this.pool.query(
                    `INSERT INTO student_activities (student_id, title, activity_title, activity_type, type, score, status, timestamp)
                     VALUES (?, ?, ?, 'PRACTICE_CODING', 'PRACTICE_CODING', ?, ?, NOW())`,
                    [candId, `Completed Practice Problem P${pnum} in Lesson ${lnum}`, `Completed Practice Problem P${pnum} in Lesson ${lnum}`, passedCount, allPassed ? 'Passed' : 'Attempted']
                );
            } catch (_) {}

            return { 
                success: true, 
                message: 'Coding submission recorded in MySQL.', 
                totalPassed: passedCount, 
                totalCases: totalCount, 
                allPassed: Boolean(allPassed) 
            };
        } catch (err) {
            console.error('❌ Error submitting coding practice in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async submitTopicMcqPractice(candidateId, courseId, lessonNum, answers) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const cid = courseId || 'course-cpp';
            const lnum = lessonNum || '1.1';
            const candId = candidateId || 'STU123456';
            const [mcqs] = await this.pool.query(
                'SELECT * FROM course_topic_mcqs WHERE course_id = ? AND lesson_num = ? ORDER BY question_number ASC',
                [cid, lnum]
            );

            let score = 0;
            const total = mcqs.length;
            const reviewList = [];

            mcqs.forEach(m => {
                const userAns = answers ? answers[m.question_number] : null;
                const isCorrect = userAns && userAns.toUpperCase() === m.correct_key.toUpperCase();
                if (isCorrect) score++;
                reviewList.push({
                    question_number: m.question_number,
                    question_text: m.question_text,
                    user_answer: userAns || 'Not Answered',
                    correct_answer: m.correct_key,
                    is_correct: isCorrect,
                    explanation: m.explanation
                });
            });

            const pct = total > 0 ? Math.round((score / total) * 100) : 100;
            const passed = total > 0 ? (score / total) >= 0.6 : true;

            const submissionPayload = {
                answers,
                score,
                totalScore: total,
                percentage: pct,
                passed,
                review: reviewList
            };

            await this.pool.query(
                `INSERT INTO course_practice_submissions (candidate_id, course_id, lesson_num, practice_type, score, total_score, passed, details_json) 
                 VALUES (?, ?, ?, 'MCQ', ?, ?, ?, ?)`,
                [candId, cid, lnum, score, total, passed ? 1 : 0, JSON.stringify(submissionPayload)]
            ).catch(() => {});

            return {
                success: true,
                score,
                totalScore: total,
                percentage: pct,
                passed,
                review: reviewList
            };
        } catch (err) {
            console.error('❌ Error evaluating topic MCQs in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    // ==========================================
    // 7. ADMIN / INSTRUCTOR CONTENT MANAGEMENT
    // ==========================================

    async adminSaveCourse(data) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const { course_id, title, description, icon, color, level, duration_text, language, certificate } = data;
            await this.pool.query(
                `INSERT INTO courses (course_id, title, description, icon, color, level, duration_text, language, certificate)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), description = VALUES(description), icon = VALUES(icon),
                 color = VALUES(color), level = VALUES(level), duration_text = VALUES(duration_text)`,
                [course_id, title, description || '', icon || '💻', color || '#6366f1', level || 'Beginner', duration_text || '2h', language || 'English', certificate || 'Yes']
            );
            return { success: true, message: `Course ${course_id} successfully saved in MySQL.` };
        } catch (e) {
            return { success: false, message: e.message };
        }
    }

    async adminAddLesson(data) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const { course_id, module_num, module_title, lesson_num, lesson_title, duration_text } = data;
            await this.pool.query(
                `INSERT INTO course_lessons (course_id, module_num, module_title, lesson_num, lesson_title, duration_text, is_completed)
                 VALUES (?, ?, ?, ?, ?, ?, 0)`,
                [course_id, module_num || 1, module_title, lesson_num, lesson_title, duration_text || '15 min']
            );
            const [c] = await this.pool.query('SELECT COUNT(*) as total FROM course_lessons WHERE course_id = ?', [course_id]);
            await this.pool.query('UPDATE courses SET lessons_count = ? WHERE course_id = ?', [c[0].total, course_id]);
            return { success: true, message: `Lesson ${lesson_num} added to course ${course_id}.` };
        } catch (e) {
            return { success: false, message: e.message };
        }
    }

    async adminSaveLessonContent(data) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const { course_id, lesson_num, concept_summary, detailed_notes, code_example, key_takeaways } = data;
            await this.pool.query(
                `INSERT INTO course_lesson_content (course_id, lesson_num, concept_summary, detailed_notes, code_example, key_takeaways)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE concept_summary = VALUES(concept_summary), detailed_notes = VALUES(detailed_notes),
                 code_example = VALUES(code_example), key_takeaways = VALUES(key_takeaways)`,
                [course_id, lesson_num, concept_summary, detailed_notes, code_example || '', key_takeaways || '']
            );
            return { success: true, message: `Lesson content for ${lesson_num} saved in MySQL.` };
        } catch (e) {
            return { success: false, message: e.message };
        }
    }

    async adminAddTopicMcq(data) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const { course_id, lesson_num, question_number, question_text, options, correct_key, explanation } = data;
            const optionsJson = typeof options === 'string' ? options : JSON.stringify(options);
            await this.pool.query(
                `INSERT INTO course_topic_mcqs (course_id, lesson_num, question_number, question_text, options_json, correct_key, explanation)
                 VALUES (?, ?, ?, ?, ?, ?, ?)`,
                [course_id, lesson_num, question_number || 1, question_text, optionsJson, correct_key, explanation || '']
            );
            return { success: true, message: `Practice MCQ added for lesson ${lesson_num}.` };
        } catch (e) {
            return { success: false, message: e.message };
        }
    }

    async adminSaveTopicCoding(data) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const { course_id, lesson_num, problem_number, title, problem_statement, difficulty, constraints_text, sample_input, sample_output, starter_code_cpp, starter_code_py, starter_code_java, starter_code_js, test_cases } = data;
            const tcsJson = typeof test_cases === 'string' ? test_cases : JSON.stringify(test_cases || []);
            await this.pool.query(
                `INSERT INTO course_topic_coding (course_id, lesson_num, problem_number, title, problem_statement, difficulty, constraints_text, sample_input, sample_output, starter_code_cpp, starter_code_py, starter_code_java, starter_code_js, test_cases_json)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE title = VALUES(title), problem_statement = VALUES(problem_statement),
                 difficulty = VALUES(difficulty), constraints_text = VALUES(constraints_text), sample_input = VALUES(sample_input),
                 sample_output = VALUES(sample_output), starter_code_cpp = VALUES(starter_code_cpp), starter_code_py = VALUES(starter_code_py),
                 starter_code_java = VALUES(starter_code_java), starter_code_js = VALUES(starter_code_js), test_cases_json = VALUES(test_cases_json)`,
                [course_id, lesson_num, problem_number || 1, title, problem_statement, difficulty || 'Easy', constraints_text || '', sample_input || '', sample_output || '', starter_code_cpp || '', starter_code_py || '', starter_code_java || '', starter_code_js || '', tcsJson]
            );
            return { success: true, message: `Coding challenge for lesson ${lesson_num} saved in MySQL.` };
        } catch (e) {
            return { success: false, message: e.message };
        }
    }

    // ==========================================
    // 8. STUDENT ACTIVITIES & SUPPORT TICKETS
    // ==========================================

    async getStudentActivities(studentId) {
        if (!this.pool) return { success: true, activities: [] };
        try {
            const [rows] = await this.pool.query(
                `SELECT * FROM student_activities 
                 WHERE student_id = ? 
                    OR student_id = (SELECT student_id FROM users WHERE id = ? LIMIT 1)
                    OR student_id = (SELECT id FROM users WHERE student_id = ? LIMIT 1)
                 ORDER BY id DESC LIMIT 10`,
                [studentId, studentId, studentId]
            );
            return { success: true, activities: rows };
        } catch (err) {
            console.error('❌ Error fetching activities from MySQL:', err.message);
            return { success: true, activities: [] };
        }
    }

    async getUserActivities(userId) {
        const res = await this.getStudentActivities(userId);
        return res.activities || [];
    }

    async createSupportTicket(ticketData) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const ticketId = 'TK-' + Math.floor(100000 + Math.random() * 900000);
            await this.pool.query(
                'INSERT INTO support_tickets (ticket_id, candidate_id, category, subject, description) VALUES (?, ?, ?, ?, ?)',
                [ticketId, ticketData.candidateId || 'STU123456', ticketData.category, ticketData.subject, ticketData.description]
            );
            return { success: true, ticketId, message: 'Ticket submitted successfully to MySQL.' };
        } catch (err) {
            console.error('❌ Error creating support ticket in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async getSupportTickets(candidateId) {
        if (!this.pool) return { success: true, tickets: [] };
        try {
            const [rows] = await this.pool.query(
                'SELECT * FROM support_tickets WHERE candidate_id = ? ORDER BY created_at DESC LIMIT 20',
                [candidateId]
            );
            return { success: true, tickets: rows };
        } catch (err) {
            console.error('❌ Error fetching support tickets from MySQL:', err.message);
            return { success: true, tickets: [] };
        }
    }

    async deleteSupportTicket(ticketId, candidateId) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const [result] = await this.pool.query(
                'DELETE FROM support_tickets WHERE ticket_id = ? AND candidate_id = ?',
                [ticketId, candidateId]
            );
            if (result.affectedRows > 0) {
                return { success: true, message: 'Ticket deleted from MySQL.' };
            }
            return { success: false, message: 'Ticket not found or unauthorized.' };
        } catch (err) {
            console.error('❌ Error deleting support ticket from MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }

    async updateSupportTicket(ticketId, updateData) {
        if (!this.pool) return { success: false, message: 'MySQL offline' };
        try {
            const fields = [];
            const values = [];
            if (updateData.status) {
                fields.push('status = ?');
                values.push(updateData.status);
            }
            if (updateData.subject) {
                fields.push('subject = ?');
                values.push(updateData.subject);
            }
            if (updateData.description) {
                fields.push('description = ?');
                values.push(updateData.description);
            }

            if (fields.length === 0) {
                return { success: false, message: 'No valid fields provided for update.' };
            }

            values.push(ticketId);
            await this.pool.query(`UPDATE support_tickets SET ${fields.join(', ')} WHERE ticket_id = ?`, values);
            return { success: true, message: 'Support ticket updated in MySQL.' };
        } catch (err) {
            console.error('❌ Error updating support ticket in MySQL:', err.message);
            return { success: false, message: err.message };
        }
    }
}

module.exports = new MySQLDatabaseService();
