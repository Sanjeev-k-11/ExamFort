const db = require('./db');

async function run() {
    console.log('⏳ Waiting for database connection...');
    await new Promise(r => setTimeout(r, 2000));

    if (!db.pool) {
        console.error('❌ Database pool not ready');
        process.exit(1);
    }

    console.log('🐘 Initializing & Seeding Course Tables...');

    // 1. Create courses table
    await db.pool.query(`
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
        )
    `).catch(err => console.log('courses table notice:', err.message));

    // 2. Create course_lessons table
    await db.pool.query(`
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
        )
    `).catch(err => console.log('course_lessons table notice:', err.message));

    // 3. Create course_lesson_content
    await db.pool.query(`
        CREATE TABLE IF NOT EXISTS course_lesson_content (
            id SERIAL PRIMARY KEY,
            course_id VARCHAR(50) NOT NULL,
            lesson_num VARCHAR(50) NOT NULL,
            concept_summary TEXT NOT NULL,
            detailed_notes TEXT NOT NULL,
            code_example TEXT,
            key_takeaways TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `).catch(err => console.log('course_lesson_content notice:', err.message));

    // 4. Create course_topic_mcqs
    await db.pool.query(`
        CREATE TABLE IF NOT EXISTS course_topic_mcqs (
            id SERIAL PRIMARY KEY,
            course_id VARCHAR(50) NOT NULL,
            lesson_num VARCHAR(50) NOT NULL,
            question_number INT NOT NULL,
            question_text TEXT NOT NULL,
            options_json JSONB NOT NULL,
            correct_key VARCHAR(10) NOT NULL,
            explanation TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `).catch(err => console.log('course_topic_mcqs notice:', err.message));

    // 5. Create course_topic_coding
    await db.pool.query(`
        CREATE TABLE IF NOT EXISTS course_topic_coding (
            id SERIAL PRIMARY KEY,
            course_id VARCHAR(50) NOT NULL,
            lesson_num VARCHAR(50) NOT NULL,
            problem_number INT DEFAULT 1,
            title VARCHAR(255) NOT NULL,
            problem_statement TEXT NOT NULL,
            difficulty VARCHAR(50) DEFAULT 'Easy',
            constraints_text TEXT,
            sample_input TEXT,
            sample_output TEXT,
            starter_code_cpp TEXT,
            starter_code_py TEXT,
            starter_code_java TEXT,
            starter_code_js TEXT,
            test_cases_json JSONB NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    `).catch(err => console.log('course_topic_coding notice:', err.message));

    // 6. Create student_activities
    await db.pool.query(`
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
        )
    `).catch(err => console.log('student_activities notice:', err.message));

    // Seed Courses if empty
    const [cRows] = await db.pool.query('SELECT count(*) as cnt FROM courses');
    const courseCount = parseInt(cRows[0]?.cnt || 0, 10);
    console.log(`📊 Courses count in DB: ${courseCount}`);

    if (courseCount === 0) {
        console.log('🌱 Seeding courses catalog...');
        const courses = [
            ['course-cpp', 'Programming in C++', 'Master C++ programming from basics to advanced concepts with hands-on examples.', '</>', '#6366f1', 18, '6h 20m', 'Intermediate', 42, 8, 'English', 'Yes', 'May 2026'],
            ['course-aptitude', 'Aptitude Fundamentals', 'Learn the basics of quantitative aptitude, number system, percentages, and ratios.', '🧠', '#ec4899', 12, '3h 45m', 'Beginner', 65, 8, 'English', 'Yes', 'May 2026'],
            ['course-dsa', 'Data Structures & Algorithms', 'Learn essential data structures and algorithms for problem solving and coding interviews.', '💾', '#f59e0b', 20, '8h 15m', 'Advanced', 25, 5, 'English', 'Yes', 'May 2026'],
            ['course-reasoning', 'Logical Reasoning', 'Improve your logical thinking skills with practice questions and detailed explanations.', '💡', '#8b5cf6', 10, '2h 30m', 'Beginner', 80, 8, 'English', 'Yes', 'May 2026']
        ];
        for (const c of courses) {
            await db.pool.query(
                'INSERT INTO courses (course_id, title, description, icon, color, lessons_count, duration_text, level, progress_percent, completed_lessons, language, certificate, last_updated) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                c
            );
        }
        console.log('✅ 4 Courses successfully seeded!');
    }

    // Seed Course Lessons if empty
    const [lRows] = await db.pool.query('SELECT count(*) as cnt FROM course_lessons');
    const lessonCount = parseInt(lRows[0]?.cnt || 0, 10);
    console.log(`📊 Course Lessons count in DB: ${lessonCount}`);

    if (lessonCount === 0) {
        console.log('🌱 Seeding 18 C++ lessons...');
        const lessons = [
            ['course-cpp', 1, 'Introduction to C++', '1.1', 'Overview of C++ and Ecosystem', '15 min', 1],
            ['course-cpp', 1, 'Introduction to C++', '1.2', 'Setting Up Compiler & Toolchain (GCC/Clang)', '20 min', 1],
            ['course-cpp', 1, 'Introduction to C++', '1.3', 'Your First Modern C++ Program & Boilerplate', '25 min', 1],
            ['course-cpp', 2, 'Basics of C++', '2.1', 'Variables, Primitive Types & auto Keyword', '30 min', 1],
            ['course-cpp', 2, 'Basics of C++', '2.2', 'Constants, constexpr & Literals', '20 min', 1],
            ['course-cpp', 2, 'Basics of C++', '2.3', 'Static Casting & Type Conversion Rules', '15 min', 1],
            ['course-cpp', 2, 'Basics of C++', '2.4', 'Fast I/O Streams (std::cin, std::cout, std::endl)', '25 min', 1],
            ['course-cpp', 3, 'Operators and Expressions', '3.1', 'Arithmetic, Bitwise & Logical Operators', '20 min', 1],
            ['course-cpp', 3, 'Operators and Expressions', '3.2', 'Operator Precedence & Associativity', '15 min', 0],
            ['course-cpp', 4, 'Control Flow & Branching', '4.1', 'Conditional Statements (if, else if, switch)', '20 min', 0],
            ['course-cpp', 4, 'Control Flow & Branching', '4.2', 'Loops (for, range-based for, while, do-while)', '35 min', 0],
            ['course-cpp', 5, 'Functions & Modular Design', '5.1', 'Function Declaration, Definition & Prototypes', '25 min', 0],
            ['course-cpp', 5, 'Functions & Modular Design', '5.2', 'Pass by Value vs Pass by Const Reference', '30 min', 0],
            ['course-cpp', 5, 'Functions & Modular Design', '5.3', 'Function Overloading & Default Arguments', '20 min', 0],
            ['course-cpp', 6, 'Arrays, Strings & Vectors', '6.1', 'Raw Arrays vs std::array and std::vector', '30 min', 0],
            ['course-cpp', 6, 'Arrays, Strings & Vectors', '6.2', 'std::string Manipulation & String Views', '25 min', 0],
            ['course-cpp', 7, 'Pointers & Memory Architecture', '7.1', 'Pointers, References & Address Arithmetic', '40 min', 0],
            ['course-cpp', 7, 'Pointers & Memory Architecture', '7.2', 'Dynamic Heap Memory & Smart Pointers (std::unique_ptr)', '35 min', 0]
        ];
        for (const l of lessons) {
            await db.pool.query(
                'INSERT INTO course_lessons (course_id, module_num, module_title, lesson_num, lesson_title, duration_text, is_completed) VALUES (?, ?, ?, ?, ?, ?, ?)',
                l
            );
        }
        console.log('✅ 18 C++ Lessons seeded!');
    }

    // Seed Lesson Content if empty
    const [cContentRows] = await db.pool.query('SELECT count(*) as cnt FROM course_lesson_content');
    if (parseInt(cContentRows[0]?.cnt || 0, 10) === 0) {
        console.log('🌱 Seeding course lesson contents...');
        const contents = [
            [
                'course-cpp', '1.1',
                'Introduction to modern C++, its origins from C with Classes, and its core zero-overhead abstraction philosophy.',
                '<h3>1. What is C++?</h3><p>C++ is a high-performance, general-purpose programming language created by Bjarne Stroustrup at Bell Labs in 1979 as an extension of the C programming language. It provides fine-grained hardware control alongside powerful object-oriented, generic, and functional programming paradigms.</p><h3>2. Why C++ is Used Today?</h3><ul><li><strong>Zero-Overhead Abstractions:</strong> What you don’t use, you don’t pay for.</li><li><strong>Direct Memory Management:</strong> Direct pointer manipulation, custom allocators, and deterministic destructors (RAII).</li><li><strong>Dominance in Critical Systems:</strong> Game engines, high-frequency trading (HFT), operating systems, and embedded systems.</li></ul>',
                '#include <iostream>\n\nint main() {\n    std::cout << "Welcome to Modern C++ Mastery!" << "\\n";\n    return 0;\n}',
                'C++ is a multi-paradigm language; RAII guarantees automatic resource cleanup; std::cout is part of the <iostream> header.'
            ],
            [
                'course-cpp', '1.2',
                'Setting up C++ toolchains across GCC, Clang and MSVC, and understanding the 4 compilation phases.',
                '<h3>1. The C++ Compilation Pipeline</h3><ol><li><strong>Preprocessing:</strong> Resolves <code>#include</code> and <code>#define</code> macros.</li><li><strong>Compilation:</strong> Converts preprocessed C++ source into assembly code.</li><li><strong>Assembly:</strong> Translates assembly code into machine object code (<code>.o</code> / <code>.obj</code>).</li><li><strong>Linking:</strong> Combines object files with runtime standard libraries into a binary executable.</li></ol>',
                '// Compile with: g++ -std=c++20 -Wall main.cpp -o main\n#include <iostream>\n\nint main() {\n    std::cout << "Compiled with GCC / Clang\\n";\n    return 0;\n}',
                'Always compile with -Wall -Wextra; understanding the 4 compilation phases aids debugging linking errors.'
            ],
            [
                'course-cpp', '1.3',
                'Mastering main(), return codes, namespaces, and standard formatting in modern C++.',
                '<h3>1. Structure of a Modern C++ Program</h3><p>Every C++ executable begins execution at the top-level <code>main()</code> function. In C++, returning 0 from main signifies successful execution without errors.</p><h3>2. The std Namespace</h3><p>Standard library identifiers like <code>cout</code> and <code>cin</code> live inside the <code>std</code> namespace to prevent global name collisions. Prefer prefixing with <code>std::</code> over <code>using namespace std;</code> in production.</p>',
                '#include <iostream>\n\nint main(int argc, char* argv[]) {\n    std::cout << "Modern C++ Execution: Success\\n";\n    return 0;\n}',
                'main() returns integer exit code (0 = success); avoid global namespace pollution in header files.'
            ]
        ];
        for (const item of contents) {
            await db.pool.query(
                'INSERT INTO course_lesson_content (course_id, lesson_num, concept_summary, detailed_notes, code_example, key_takeaways) VALUES (?, ?, ?, ?, ?, ?)',
                item
            );
        }
        console.log('✅ Course lesson content seeded!');
    }

    // Seed MCQs if empty
    const [mcqRows] = await db.pool.query('SELECT count(*) as cnt FROM course_topic_mcqs');
    if (parseInt(mcqRows[0]?.cnt || 0, 10) === 0) {
        console.log('🌱 Seeding Topic MCQs...');
        const mcqs = [
            ['course-cpp', '1.1', 1, 'Which year was the C++ programming language created by Bjarne Stroustrup at Bell Labs?', JSON.stringify([{key:'A',text:'1972'},{key:'B',text:'1979'},{key:'C',text:'1991'},{key:'D',text:'1998'}]), 'B', 'C++ (originally C with Classes) began development in 1979.'],
            ['course-cpp', '1.1', 2, 'What does the core C++ design philosophy "Zero-Overhead Principle" mean?', JSON.stringify([{key:'A',text:'Zero cost for compiler licenses'},{key:'B',text:'What you do not use, you do not pay for in performance or memory'},{key:'C',text:'Programs consume 0 bytes of RAM'},{key:'D',text:'Zero compile time'}]), 'B', 'Features unused at runtime cost nothing in execution time or memory.'],
            ['course-cpp', '1.1', 3, 'Which standard header file provides std::cout and std::cin streams?', JSON.stringify([{key:'A',text:'<stdio.h>'},{key:'B',text:'<stdlib.h>'},{key:'C',text:'<iostream>'},{key:'D',text:'<string>'}]), 'C', '<iostream> defines the standard input and output streams.'],
            ['course-cpp', '1.1', 4, 'What is the correct sequence of steps in the C++ compilation pipeline?', JSON.stringify([{key:'A',text:'Preprocessor -> Compiler -> Assembler -> Linker'},{key:'B',text:'Linker -> Assembler -> Compiler -> Preprocessor'},{key:'C',text:'Compiler -> Preprocessor -> Linker -> Assembler'},{key:'D',text:'Assembler -> Linker -> Compiler -> Preprocessor'}]), 'A', 'Source goes to Preprocessor (# directives) -> Compiler (Assembly) -> Assembler (Machine code) -> Linker (Executable).'],
            ['course-cpp', '1.1', 5, 'Which modern C++ standard introduced Concepts, Ranges, Coroutines, and Modules?', JSON.stringify([{key:'A',text:'C++11'},{key:'B',text:'C++14'},{key:'C',text:'C++17'},{key:'D',text:'C++20'}]), 'D', 'C++20 introduced the major pillars: Concepts, Ranges, Coroutines, and Modules.']
        ];
        for (const m of mcqs) {
            await db.pool.query(
                'INSERT INTO course_topic_mcqs (course_id, lesson_num, question_number, question_text, options_json, correct_key, explanation) VALUES (?, ?, ?, ?, ?, ?, ?)',
                m
            );
        }
        console.log('✅ Topic MCQs seeded!');
    }

    // Seed Coding Problems if empty
    const [codeRows] = await db.pool.query('SELECT count(*) as cnt FROM course_topic_coding');
    if (parseInt(codeRows[0]?.cnt || 0, 10) === 0) {
        console.log('🌱 Seeding Topic Coding Problems...');
        const coding = [
            [
                'course-cpp', '1.1', 1, 'Welcome to C++ Mastery',
                'Print the exact text "Welcome to Modern C++ Mastery!" followed by a newline.',
                'Easy', 'None', '', 'Welcome to Modern C++ Mastery!',
                '#include <iostream>\n\nint main() {\n    std::cout << "Welcome to Modern C++ Mastery!" << std::endl;\n    return 0;\n}',
                'print("Welcome to Modern C++ Mastery!")',
                'public class Main {\n    public static void main(String[] args) {\n        System.out.println("Welcome to Modern C++ Mastery!");\n    }\n}',
                'console.log("Welcome to Modern C++ Mastery!");',
                JSON.stringify([{input:'', expected_output:'Welcome to Modern C++ Mastery!'}])
            ],
            [
                'course-cpp', '1.1', 2, 'Rectangle Area & Perimeter',
                'Given two integers L (Length) and B (Breadth), calculate and print the Area and Perimeter separated by a space.',
                'Easy', '1 <= L, B <= 10000', '5 10', '50 30',
                '#include <iostream>\n\nint main() {\n    long long l, b;\n    if (std::cin >> l >> b) {\n        std::cout << (l * b) << " " << (2 * (l + b)) << std::endl;\n    }\n    return 0;\n}',
                'import sys\nparts = sys.stdin.read().split()\nif len(parts) >= 2:\n    l, b = int(parts[0]), int(parts[1])\n    print(f"{l*b} {2*(l+b)}")',
                'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long l = sc.nextLong();\n            long b = sc.nextLong();\n            System.out.println((l*b) + " " + (2*(l+b)));\n        }\n    }\n}',
                'const fs = require("fs");\nconst parts = fs.readFileSync(0, "utf-8").trim().split(/\\s+/);\nif (parts.length >= 2) {\n    const l = BigInt(parts[0]), b = BigInt(parts[1]);\n    console.log(`${l*b} ${2n*(l+b)}`);\n}',
                JSON.stringify([{input:'5 10', expected_output:'50 30'},{input:'12 8', expected_output:'96 40'},{input:'100 200', expected_output:'20000 600'}])
            ]
        ];
        for (const item of coding) {
            await db.pool.query(
                'INSERT INTO course_topic_coding (course_id, lesson_num, problem_number, title, problem_statement, difficulty, constraints_text, sample_input, sample_output, starter_code_cpp, starter_code_py, starter_code_java, starter_code_js, test_cases_json) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                item
            );
        }
        console.log('✅ Topic Coding Problems seeded!');
    }

    // Seed student_activities if empty
    const [actRows] = await db.pool.query('SELECT count(*) as cnt FROM student_activities');
    if (parseInt(actRows[0]?.cnt || 0, 10) === 0) {
        console.log('🌱 Seeding student activities...');
        const acts = [
            ['123', 'Completed Programming MCQ', 'Completed Programming MCQ', 'Scored 85% in Modern C++ assessment', 'Score: 85%', 85, 'Passed', '🏆 Passed', 'Today, 09:15 AM', '✓', 'EXAM', 'EXAM'],
            ['123', 'Attempted Mock Test - 2', 'Attempted Mock Test - 2', 'Completed practice algorithm trial', 'Score: 68%', 68, 'Passed', '✓ Completed', 'Yesterday, 04:30 PM', '✓', 'EXAM', 'EXAM'],
            ['123', 'Face Verification Completed', 'Face Verification Completed', 'Live biometric face snapshot verified and updated in profile.', 'Verified', 100, 'Verified', '🛡️ Verified', 'Yesterday, 11:20 AM', '🛡️', 'VERIFICATION', 'VERIFICATION']
        ];
        for (const a of acts) {
            await db.pool.query(
                'INSERT INTO student_activities (student_id, title, activity_title, description, score_info, score, status, badge, time_text, icon, type, activity_type) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)',
                a
            );
        }
        console.log('✅ Student activities seeded!');
    }

    const allCourses = await db.getAllCourses();
    console.log('🎉 Verification getAllCourses count:', allCourses.courses?.length);
    process.exit(0);
}

run().catch(err => {
    console.error('FATAL:', err);
    process.exit(1);
});
