-- ========================================================
-- EXAMFORT COMPLETE DATABASE SCHEMA & DATA
-- 100% Pure MySQL Storage for XAMPP (Zero Hardcoded / Fallback Data)
-- ========================================================

CREATE DATABASE IF NOT EXISTS `examfort` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `examfort`;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. ORGANIZATIONS TABLE
CREATE TABLE IF NOT EXISTS `organizations` (
    `id` VARCHAR(50) PRIMARY KEY,
    `name` VARCHAR(255) NOT NULL,
    `code` VARCHAR(50) NOT NULL,
    `type` VARCHAR(100) DEFAULT 'University',
    `email` VARCHAR(150) NULL,
    `phone` VARCHAR(50) NULL,
    `website` VARCHAR(255) NULL,
    `address` TEXT NULL,
    `max_teachers_allowed` INT DEFAULT 100,
    `max_students_allowed` INT DEFAULT 8000,
    `max_exams_allowed` INT DEFAULT 200,
    `gemini_api_key` VARCHAR(255) NULL,
    `status` VARCHAR(20) DEFAULT 'ACTIVE',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. USERS / STUDENT PROFILES TABLE
CREATE TABLE IF NOT EXISTS `users` (
    `id` VARCHAR(50) PRIMARY KEY,
    `student_id` VARCHAR(50) NOT NULL UNIQUE,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `dob` VARCHAR(50) DEFAULT '12 Jan 2003',
    `location` VARCHAR(100) DEFAULT 'Bihar, India',
    `college_name` VARCHAR(255) DEFAULT 'National Institute of Technology (NIT Patna)',
    `course` VARCHAR(100) DEFAULT 'B.Tech',
    `stream` VARCHAR(100) DEFAULT 'Computer Science & Engineering',
    `batch_years` VARCHAR(50) DEFAULT '2022 - 2026',
    `bio` TEXT,
    `goal` TEXT,
    `achievements` TEXT,
    `interests` TEXT,
    `profile_completion_pct` INT DEFAULT 85,
    `exams_enrolled` INT DEFAULT 12,
    `exams_completed` INT DEFAULT 5,
    `upcoming_exams_count` INT DEFAULT 3,
    `average_score` INT DEFAULT 72,
    `best_score` INT DEFAULT 95,
    `current_streak_days` INT DEFAULT 7,
    `password` VARCHAR(255) NOT NULL,
    `role` VARCHAR(50) DEFAULT 'CANDIDATE',
    `access_code` VARCHAR(20) DEFAULT '123456',
    `avatar_url` MEDIUMTEXT DEFAULT NULL,
    `status` VARCHAR(20) DEFAULT 'ACTIVE',
    `org_id` VARCHAR(50) NULL,
    `created_by_principal_id` VARCHAR(50) NULL,
    `created_by_teacher_id` VARCHAR(50) NULL,
    `designation` VARCHAR(100) NULL,
    `department` VARCHAR(100) NULL,
    `max_students_allowed` INT DEFAULT 500,
    `max_exams_allowed` INT DEFAULT 50,
    `can_create_exams` TINYINT(1) DEFAULT 1,
    `can_set_questions` TINYINT(1) DEFAULT 1,
    `can_manage_lessons` TINYINT(1) DEFAULT 1,
    `can_manage_courses` TINYINT(1) DEFAULT 0,
    `can_enroll_students` TINYINT(1) DEFAULT 1,
    `can_view_results` TINYINT(1) DEFAULT 1,
    `gemini_api_key` VARCHAR(255) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 3. COURSES TABLE
CREATE TABLE IF NOT EXISTS `courses` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` VARCHAR(50) NOT NULL UNIQUE,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `icon` VARCHAR(50) DEFAULT '💻',
    `color` VARCHAR(50) DEFAULT '#4f46e5',
    `lessons_count` INT DEFAULT 18,
    `duration_text` VARCHAR(50) DEFAULT '6h 20m',
    `level` VARCHAR(50) DEFAULT 'Intermediate',
    `progress_percent` INT DEFAULT 42,
    `completed_lessons` INT DEFAULT 8,
    `language` VARCHAR(50) DEFAULT 'English',
    `certificate` VARCHAR(50) DEFAULT 'Yes',
    `last_updated` VARCHAR(50) DEFAULT 'May 2026',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 4. COURSE LESSONS TABLE
CREATE TABLE IF NOT EXISTS `course_lessons` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` VARCHAR(50) NOT NULL,
    `module_num` INT NOT NULL,
    `module_title` VARCHAR(255) NOT NULL,
    `lesson_num` VARCHAR(20) NOT NULL,
    `lesson_title` VARCHAR(255) NOT NULL,
    `duration_text` VARCHAR(50) NOT NULL,
    `is_completed` TINYINT(1) DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_course` (`course_id`)
) ENGINE=InnoDB;

-- 5. COURSE LESSON CONTENT
CREATE TABLE IF NOT EXISTS `course_lesson_content` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` VARCHAR(50) NOT NULL,
    `lesson_num` VARCHAR(50) NOT NULL,
    `concept_summary` TEXT NOT NULL,
    `detailed_notes` LONGTEXT NOT NULL,
    `code_example` LONGTEXT,
    `key_takeaways` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_course_lesson_content` (`course_id`, `lesson_num`)
) ENGINE=InnoDB;

-- 6. TOPIC MCQS PRACTICE
CREATE TABLE IF NOT EXISTS `course_topic_mcqs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` VARCHAR(50) NOT NULL,
    `lesson_num` VARCHAR(50) NOT NULL,
    `question_number` INT NOT NULL,
    `question_text` TEXT NOT NULL,
    `options_json` JSON NOT NULL,
    `correct_key` VARCHAR(10) NOT NULL,
    `explanation` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_topic_mcq` (`course_id`, `lesson_num`, `question_number`)
) ENGINE=InnoDB;

-- 7. TOPIC CODING PRACTICE CHALLENGES
CREATE TABLE IF NOT EXISTS `course_topic_coding` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `course_id` VARCHAR(50) NOT NULL,
    `lesson_num` VARCHAR(50) NOT NULL,
    `problem_number` INT DEFAULT 1,
    `title` VARCHAR(255) NOT NULL,
    `problem_statement` LONGTEXT NOT NULL,
    `difficulty` VARCHAR(50) DEFAULT 'Easy',
    `constraints_text` TEXT,
    `sample_input` TEXT,
    `sample_output` TEXT,
    `starter_code_cpp` LONGTEXT,
    `starter_code_py` LONGTEXT,
    `starter_code_java` LONGTEXT,
    `starter_code_js` LONGTEXT,
    `test_cases_json` JSON NOT NULL,
    UNIQUE KEY `uniq_coding_topic_prob` (`course_id`, `lesson_num`, `problem_number`)
) ENGINE=InnoDB;

-- 8. COURSE PRACTICE SUBMISSIONS
CREATE TABLE IF NOT EXISTS `course_practice_submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` VARCHAR(50) NOT NULL,
    `course_id` VARCHAR(50) NOT NULL,
    `lesson_num` VARCHAR(50) NOT NULL,
    `practice_type` ENUM('MCQ', 'CODING') NOT NULL,
    `score` INT DEFAULT 0,
    `total_score` INT DEFAULT 0,
    `passed` BOOLEAN DEFAULT 0,
    `details_json` JSON,
    `submitted_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_cand_practice` (`candidate_id`, `course_id`, `lesson_num`)
) ENGINE=InnoDB;

-- 9. COURSE PRACTICE DRAFTS
CREATE TABLE IF NOT EXISTS `course_practice_drafts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` VARCHAR(50) NOT NULL,
    `course_id` VARCHAR(50) NOT NULL,
    `lesson_num` VARCHAR(50) NOT NULL,
    `problem_number` INT DEFAULT 1,
    `language` VARCHAR(20) DEFAULT 'cpp',
    `draft_code` LONGTEXT,
    `mcq_answers_json` JSON,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uniq_cand_draft` (`candidate_id`, `course_id`, `lesson_num`, `problem_number`)
) ENGINE=InnoDB;

-- 10. EXAMS TABLE
CREATE TABLE IF NOT EXISTS `exams` (
    `id` INT AUTO_INCREMENT UNIQUE KEY,
    `exam_code` VARCHAR(50) PRIMARY KEY,
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `category` VARCHAR(100) DEFAULT 'Aptitude & Coding',
    `duration_minutes` INT DEFAULT 120,
    `total_marks` INT DEFAULT 120,
    `total_questions` INT DEFAULT 13,
    `exam_date` VARCHAR(50) DEFAULT '31 Dec 2026',
    `exam_time` VARCHAR(50) DEFAULT '10:00 AM - 12:00 PM',
    `status` VARCHAR(20) DEFAULT 'ACTIVE',
    `is_results_published` TINYINT(1) DEFAULT 0,
    `face_verification_required` TINYINT(1) DEFAULT 1,
    `college_name` VARCHAR(255) DEFAULT 'National Institute of Technology (NIT Patna)',
    `org_id` VARCHAR(50) DEFAULT 'ORG_NITP',
    `created_by_teacher_id` VARCHAR(50) NULL,
    `created_by_principal_id` VARCHAR(50) NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 11. EXAM INSTRUCTIONS TABLE
CREATE TABLE IF NOT EXISTS `exam_instructions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `exam_code` VARCHAR(50) NOT NULL,
    `instruction_text` TEXT NOT NULL,
    `display_order` INT DEFAULT 1,
    INDEX `idx_exam_inst` (`exam_code`)
) ENGINE=InnoDB;

-- 12. PLACEMENT EXAMS TABLE
CREATE TABLE IF NOT EXISTS `placement_exams` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `exam_code` VARCHAR(50) NOT NULL UNIQUE,
    `company_name` VARCHAR(150) NOT NULL,
    `job_role` VARCHAR(150) NOT NULL,
    `package_lpa` VARCHAR(50) DEFAULT '12 LPA',
    `title` VARCHAR(255) NOT NULL,
    `description` TEXT,
    `duration_minutes` INT DEFAULT 90,
    `total_marks` INT DEFAULT 100,
    `exam_date` VARCHAR(50) DEFAULT '31 Dec 2026',
    `start_time` VARCHAR(50) DEFAULT '10:00 AM',
    `end_time` VARCHAR(50) DEFAULT '11:30 AM',
    `face_verification_required` TINYINT(1) DEFAULT 1,
    `status` VARCHAR(20) DEFAULT 'ACTIVE',
    `college_name` VARCHAR(255) DEFAULT 'National Institute of Technology (NIT Patna)',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 13. PLACEMENT EXAM CANDIDATES TABLE
CREATE TABLE IF NOT EXISTS `placement_exam_candidates` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `placement_exam_id` INT NOT NULL,
    `exam_code` VARCHAR(50) NOT NULL,
    `student_id` VARCHAR(50) NOT NULL,
    `full_name` VARCHAR(150) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `phone` VARCHAR(30) DEFAULT NULL,
    `stream` VARCHAR(100) DEFAULT 'CSE',
    `course` VARCHAR(50) DEFAULT 'B.Tech',
    `access_code` VARCHAR(20) NOT NULL,
    `scheduled_date` VARCHAR(50) DEFAULT '31 Dec 2026',
    `scheduled_start_time` VARCHAR(50) DEFAULT '10:00 AM',
    `scheduled_end_time` VARCHAR(50) DEFAULT '11:30 AM',
    `company_name` VARCHAR(150) DEFAULT '',
    `job_role` VARCHAR(150) DEFAULT '',
    `package_lpa` VARCHAR(50) DEFAULT '',
    `attempt_status` VARCHAR(20) DEFAULT 'PENDING',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_p_cand_code` (`access_code`),
    INDEX `idx_p_cand_stu` (`student_id`)
) ENGINE=InnoDB;

-- 14. QUESTIONS TABLE
CREATE TABLE IF NOT EXISTS `questions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `exam_code` VARCHAR(50) NOT NULL,
    `placement_exam_id` INT NULL,
    `question_number` INT NOT NULL,
    `type` ENUM('MCQ', 'CODING', 'PARAGRAPH') NOT NULL,
    `title` VARCHAR(255) NOT NULL,
    `question_text` LONGTEXT NOT NULL,
    `options` JSON DEFAULT NULL,
    `correct_answer` VARCHAR(255) DEFAULT NULL,
    `entry_function` VARCHAR(100) DEFAULT 'solve',
    `coding_starter_code` LONGTEXT DEFAULT NULL,
    `reference_solution` LONGTEXT DEFAULT NULL,
    `random_input_schema` JSON DEFAULT NULL,
    `public_test_cases` JSON DEFAULT NULL,
    `hidden_test_cases` JSON DEFAULT NULL,
    `public_weightage_marks` DECIMAL(5,2) DEFAULT 10.00,
    `hidden_weightage_marks` DECIMAL(5,2) DEFAULT 40.00,
    `max_marks` DECIMAL(5,2) DEFAULT 50.00,
    `rubric_json` JSON DEFAULT NULL,
    INDEX `idx_q_exam` (`exam_code`),
    INDEX `idx_q_placement` (`placement_exam_id`)
) ENGINE=InnoDB;

-- 15. SUBMISSIONS TABLE
CREATE TABLE IF NOT EXISTS `submissions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` VARCHAR(50) NOT NULL,
    `exam_code` VARCHAR(50) NOT NULL,
    `answers_json` LONGTEXT NOT NULL,
    `mcq_score` DECIMAL(5,2) DEFAULT 0.00,
    `coding_public_score` DECIMAL(5,2) DEFAULT 0.00,
    `coding_hidden_score` DECIMAL(5,2) DEFAULT 0.00,
    `essay_score` DECIMAL(5,2) DEFAULT 0.00,
    `total_score` DECIMAL(5,2) DEFAULT 0.00,
    `evaluation_report` JSON DEFAULT NULL,
    `submission_timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_sub_cand_exam` (`candidate_id`, `exam_code`)
) ENGINE=InnoDB;

-- 16. VIOLATIONS TABLE
CREATE TABLE IF NOT EXISTS `violations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` VARCHAR(50) NOT NULL,
    `exam_code` VARCHAR(50) DEFAULT 'NAT-2026-EXAM',
    `violation_type` VARCHAR(100) NOT NULL,
    `details` TEXT NOT NULL,
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 17. CANDIDATE DRAFTS TABLE (Auto-Save)
CREATE TABLE IF NOT EXISTS `candidate_drafts` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `candidate_id` VARCHAR(50) NOT NULL,
    `exam_code` VARCHAR(50) NOT NULL,
    `answers_json` LONGTEXT NOT NULL,
    `last_saved_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `cand_exam_unique` (`candidate_id`, `exam_code`)
) ENGINE=InnoDB;

-- 18. STUDENT ACTIVITIES TABLE
CREATE TABLE IF NOT EXISTS `student_activities` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `student_id` VARCHAR(50) NOT NULL,
    `title` VARCHAR(255) DEFAULT '',
    `activity_title` VARCHAR(255) DEFAULT '',
    `description` TEXT NULL,
    `activity_type` VARCHAR(50) DEFAULT 'EXAM',
    `type` VARCHAR(50) DEFAULT 'EXAM',
    `score_info` VARCHAR(100) DEFAULT '',
    `score` INT DEFAULT 0,
    `status` VARCHAR(50) DEFAULT 'Completed',
    `badge` VARCHAR(50) DEFAULT '✓',
    `time_text` VARCHAR(50) DEFAULT 'Recent',
    `icon` VARCHAR(50) DEFAULT '✓',
    `timestamp` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 19. SUPPORT TICKETS TABLE
CREATE TABLE IF NOT EXISTS `support_tickets` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `ticket_id` VARCHAR(50) NOT NULL UNIQUE,
    `candidate_id` VARCHAR(50) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `subject` VARCHAR(255) NOT NULL,
    `description` TEXT NOT NULL,
    `status` ENUM('OPEN', 'IN_PROGRESS', 'RESOLVED') DEFAULT 'OPEN',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ========================================================
-- SEED DATA DIRECTLY IN MYSQL (100% Complete XAMPP MySQL Dataset)
-- ========================================================

-- Insert Organizations
INSERT IGNORE INTO `organizations` (`id`, `name`, `code`, `type`, `email`, `phone`, `website`, `address`, `max_teachers_allowed`, `max_students_allowed`, `max_exams_allowed`, `status`) VALUES
('ORG_NITP', 'National Institute of Technology (NIT Patna)', 'NITP', 'University / Institute of National Importance', 'admin@nitp.ac.in', '+91 612 237 1715', 'https://www.nitp.ac.in', 'Ashok Rajpath, Patna, Bihar 800005', 100, 8000, 200, 'ACTIVE'),
('ORG_IITD', 'Indian Institute of Technology Delhi (IIT Delhi)', 'IITD', 'Institute of Eminence', 'dean.academics@iitd.ac.in', '+91 11 2659 7135', 'https://home.iitd.ac.in', 'Hauz Khas, New Delhi 110016', 150, 12000, 300, 'ACTIVE');

-- Insert Users (Superadmin, Principal, Teacher, Candidates)
INSERT IGNORE INTO `users` (`id`, `student_id`, `full_name`, `email`, `phone`, `dob`, `location`, `college_name`, `course`, `stream`, `batch_years`, `bio`, `goal`, `achievements`, `interests`, `profile_completion_pct`, `exams_enrolled`, `exams_completed`, `upcoming_exams_count`, `average_score`, `best_score`, `current_streak_days`, `password`, `role`, `access_code`, `status`, `designation`, `department`, `max_students_allowed`, `max_exams_allowed`, `org_id`, `avatar_url`) VALUES
('ADMIN001', 'ADMIN001', 'Institutional Super Administrator', 'admin@examfort.com', '+91 99999 00001', '01 Jan 1980', 'New Delhi, India', 'ExamFort Central Governance', 'Administration', 'System Administration', '2020 - 2030', 'System Chief Administrator managing institutional exams, university portals, and global security policies.', 'Zero-latency global proctoring ecosystem.', 'Architected ExamFort Multi-Tiered Proctoring Suite.', 'Distributed Systems, Cloud Architecture, Cyber Security', 100, 50, 50, 0, 99, 100, 30, 'admin123', 'SUPERADMIN', '999999', 'ACTIVE', 'Chief Technology Administrator', 'Central Administration', 100000, 5000, 'ORG_NITP', NULL),
('PRIN001', 'PRIN1001', 'Dr. P. K. Mishra (Dean & Principal)', 'principal@examfort.com', '+91 99999 00002', '15 Aug 1975', 'Patna, India', 'National Institute of Technology (NIT Patna)', 'Academic Affairs', 'Computer Science', '2015 - 2030', 'Dean & Principal responsible for academic integrity and institutional assessments.', 'Academic excellence & fair automated evaluations.', 'Published 50+ research papers in algorithmic computing.', 'Higher Education, Data Integrity, Institutional Governance', 100, 30, 30, 0, 95, 98, 15, 'principal123', 'PRINCIPAL', '777777', 'ACTIVE', 'Principal & Dean of Academic Affairs', 'Office of the Dean', 5000, 150, 'ORG_NITP', NULL),
('TEACH001', 'FAC101', 'Prof. Rajesh Sharma', 'teacher@examfort.com', '+91 99999 00003', '20 Oct 1985', 'Patna, India', 'National Institute of Technology (NIT Patna)', 'Engineering Faculty', 'Computer Science & Engineering', '2018 - 2028', 'Associate Professor leading the Algorithms and Data Structures curriculum.', 'Empowering students with industry-standard coding proficiency.', 'Best Faculty Award 2024.', 'Algorithms, Competitive Programming, Artificial Intelligence', 95, 20, 20, 0, 90, 95, 10, 'teacher123', 'TEACHER', '123456', 'ACTIVE', 'Associate Professor & Lead Proctor', 'Computer Science & Engineering', 500, 50, 'ORG_NITP', NULL),
('123', 'STU123456', 'Ankit Kumar', 'ankit.kumar@gmail.com', '+91 98765 43210', '12 Jan 2003', 'Bihar, India', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'I am a dedicated student preparing for competitive exams. I love learning new things and constantly improving my skills.', 'To crack top placement exams and build a successful career.', 'Consistent performer and quick learner.', 'Programming, Problem Solving, Reading, Music', 85, 12, 5, 3, 72, 95, 7, '123', 'CANDIDATE', '123456', 'ACTIVE', 'Undergraduate Student', 'Computer Science & Engineering', 1, 1, 'ORG_NITP', 'https://res.cloudinary.com/dpkgmrpcx/image/upload/v1788429254/sanjeev_xagdte.jpg'),
('CAND123456', 'STU789012', 'Rahul Kumar', 'rahul.kumar@gmail.com', '+91 98765 12345', '15 Aug 2002', 'Delhi, India', 'IIT Delhi', 'B.Tech', 'Information Technology', '2022 - 2026', 'Passionate developer and algorithm enthusiast.', 'To become a Principal Software Architect.', 'Winner of Inter-College Hackathon.', 'Algorithms, Cloud Computing, Chess', 90, 15, 8, 2, 78, 98, 12, '123', 'CANDIDATE', '123456', 'ACTIVE', 'Undergraduate Student', 'Information Technology', 1, 1, 'ORG_IITD', 'https://res.cloudinary.com/dpkgmrpcx/image/upload/v1788429254/sanjeev_xagdte.jpg');

-- Insert Courses Catalog
INSERT IGNORE INTO `courses` (`course_id`, `title`, `description`, `icon`, `color`, `lessons_count`, `duration_text`, `level`, `progress_percent`, `completed_lessons`, `language`, `certificate`, `last_updated`) VALUES
('course-cpp', 'Programming in C++', 'Master C++ programming from basics to advanced concepts with hands-on examples.', '</>', '#6366f1', 18, '6h 20m', 'Intermediate', 42, 8, 'English', 'Yes', 'May 2026'),
('course-aptitude', 'Aptitude Fundamentals', 'Learn the basics of quantitative aptitude, number system, percentages, and ratios.', '🧠', '#ec4899', 12, '3h 45m', 'Beginner', 65, 8, 'English', 'Yes', 'May 2026'),
('course-dsa', 'Data Structures & Algorithms', 'Learn essential data structures and algorithms for problem solving and coding interviews.', '💾', '#f59e0b', 20, '8h 15m', 'Advanced', 25, 5, 'English', 'Yes', 'May 2026'),
('course-reasoning', 'Logical Reasoning', 'Improve your logical thinking skills with practice questions and detailed explanations.', '💡', '#8b5cf6', 10, '2h 30m', 'Beginner', 80, 8, 'English', 'Yes', 'May 2026');

-- Insert 18 C++ Course Lessons
INSERT IGNORE INTO `course_lessons` (`course_id`, `module_num`, `module_title`, `lesson_num`, `lesson_title`, `duration_text`, `is_completed`) VALUES
('course-cpp', 1, 'Introduction to C++', '1.1', 'Overview of C++ and Ecosystem', '15 min', 1),
('course-cpp', 1, 'Introduction to C++', '1.2', 'Setting Up Compiler & Toolchain (GCC/Clang)', '20 min', 1),
('course-cpp', 1, 'Introduction to C++', '1.3', 'Your First Modern C++ Program & Boilerplate', '25 min', 1),
('course-cpp', 2, 'Basics of C++', '2.1', 'Variables, Primitive Types & auto Keyword', '30 min', 1),
('course-cpp', 2, 'Basics of C++', '2.2', 'Constants, constexpr & Literals', '20 min', 1),
('course-cpp', 2, 'Basics of C++', '2.3', 'Static Casting & Type Conversion Rules', '15 min', 1),
('course-cpp', 2, 'Basics of C++', '2.4', 'Fast I/O Streams (std::cin, std::cout, std::endl)', '25 min', 1),
('course-cpp', 3, 'Operators and Expressions', '3.1', 'Arithmetic, Bitwise & Logical Operators', '20 min', 1),
('course-cpp', 3, 'Operators and Expressions', '3.2', 'Operator Precedence & Associativity', '15 min', 0),
('course-cpp', 4, 'Control Flow & Branching', '4.1', 'Conditional Statements (if, else if, switch)', '20 min', 0),
('course-cpp', 4, 'Control Flow & Branching', '4.2', 'Loops (for, range-based for, while, do-while)', '35 min', 0),
('course-cpp', 5, 'Functions & Modular Design', '5.1', 'Function Declaration, Definition & Prototypes', '25 min', 0),
('course-cpp', 5, 'Functions & Modular Design', '5.2', 'Pass by Value vs Pass by Const Reference', '30 min', 0),
('course-cpp', 5, 'Functions & Modular Design', '5.3', 'Function Overloading & Default Arguments', '20 min', 0),
('course-cpp', 6, 'Arrays, Strings & Vectors', '6.1', 'Raw Arrays vs std::array and std::vector', '30 min', 0),
('course-cpp', 6, 'Arrays, Strings & Vectors', '6.2', 'std::string Manipulation & String Views', '25 min', 0),
('course-cpp', 7, 'Pointers & Memory Architecture', '7.1', 'Pointers, References & Address Arithmetic', '40 min', 0),
('course-cpp', 7, 'Pointers & Memory Architecture', '7.2', 'Dynamic Heap Memory & Smart Pointers (std::unique_ptr)', '35 min', 0);

-- Insert Exams Catalog
INSERT IGNORE INTO `exams` (`exam_code`, `title`, `description`, `category`, `duration_minutes`, `total_marks`, `total_questions`, `exam_date`, `exam_time`, `status`, `is_results_published`, `face_verification_required`, `college_name`, `org_id`) VALUES
('NAT-2026-EXAM', 'Aptitude & Coding Assessment Test', 'Official national level aptitude, core algorithmic problem-solving and software design assessment.', 'Aptitude & Coding', 120, 120, 13, '31 Dec 2026', '10:00 AM - 12:00 PM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP'),
('DSA-MOCK-2026', 'Data Structures & Algorithms Mock Test', 'Advanced dynamic programming, graphs, trees and greedy algorithmic challenges.', 'Coding Assessment', 90, 100, 5, '08 Sep 2026', '02:00 PM - 03:30 PM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP'),
('CPP-PROG-2026', 'C++ Object-Oriented Programming Test', 'Deep dive into modern C++20, STL templates, RAII memory management and pointers.', 'Programming MCQ', 60, 50, 7, '28 Aug 2026', '11:00 AM - 12:00 PM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP'),
('APT-TEST-2026', 'General Quantitative & Reasoning Assessment', 'Logical deductions, probability, permutation combinations and quantitative mathematics.', 'Aptitude & Logic', 60, 50, 7, '25 Aug 2026', '04:00 PM - 05:00 PM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP'),
('SQL-DB-2026', 'Relational Databases & SQL Optimization', 'Complex JOINs, query indexing, ACID transactions and database normalization principles.', 'Database Engineering', 75, 80, 7, '15 Sep 2026', '10:30 AM - 11:45 AM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP'),
('SYS-DESIGN-2026', 'Distributed System Architecture & Security', 'High-concurrency streaming, Redis caching, microservices and OAuth2 security.', 'System Architecture', 90, 100, 7, '22 Sep 2026', '03:00 PM - 04:30 PM', 'ACTIVE', 0, 1, 'National Institute of Technology (NIT Patna)', 'ORG_NITP');

-- Insert Exam Instructions
INSERT IGNORE INTO `exam_instructions` (`exam_code`, `instruction_text`, `display_order`) VALUES
('NAT-2026-EXAM', 'This is a strictly proctored assessment. Ensure your webcam and microphone remain active throughout.', 1),
('NAT-2026-EXAM', 'Do not switch tabs, minimize the browser window, or open secondary display monitors.', 2),
('NAT-2026-EXAM', 'All coding submissions are evaluated against public and strict hidden test cases with anti-cheat detection.', 3),
('NAT-2026-EXAM', 'Auto-save operates every 5 seconds. In case of network disconnection, answers are preserved in draft.', 4),
('NAT-2026-EXAM', 'Click the Final Submit button once you complete all 3 sections.', 5);

-- Insert Placement Exam Drives
INSERT IGNORE INTO `placement_exams` (`id`, `exam_code`, `company_name`, `job_role`, `package_lpa`, `title`, `description`, `duration_minutes`, `total_marks`, `exam_date`, `start_time`, `end_time`, `face_verification_required`, `status`, `college_name`) VALUES
(1, 'PLC-2026-TCS', 'Tata Consultancy Services (TCS Digital)', 'Digital Software Engineer', '7.5 LPA', 'TCS Digital National Campus Drive', 'Exclusive recruitment drive for final year engineering candidates for digital software roles.', 90, 100, '31 Dec 2026', '10:00 AM', '11:30 AM', 1, 'ACTIVE', 'National Institute of Technology (NIT Patna)'),
(2, 'PLC-2026-INFY', 'Infosys Specialist Programmer', 'Specialist Programmer (Power Programmer)', '9.5 LPA', 'Infosys SP Campus Challenge', 'Algorithmic problem solving and system programming assessment for specialist engineering roles.', 120, 120, '31 Dec 2026', '02:00 PM', '04:00 PM', 1, 'ACTIVE', 'National Institute of Technology (NIT Patna)'),
(3, 'PLC-2026-AMZN', 'Amazon Web Services (AWS)', 'Software Development Engineer - 1 (SDE-1)', '28.0 LPA', 'Amazon SDE-1 Campus Hiring Drive', 'Coding, system structures, and behavioural assessment for SDE-1 engineering positions.', 120, 150, '31 Dec 2026', '10:00 AM', '12:00 PM', 1, 'ACTIVE', 'National Institute of Technology (NIT Patna)');

-- Insert Placement Candidates with Unique 6-Digit Access Codes
INSERT IGNORE INTO `placement_exam_candidates` (`placement_exam_id`, `exam_code`, `student_id`, `full_name`, `email`, `phone`, `stream`, `course`, `access_code`, `scheduled_date`, `scheduled_start_time`, `scheduled_end_time`, `company_name`, `job_role`, `package_lpa`, `attempt_status`) VALUES
(1, 'PLC-2026-TCS', 'STU123456', 'Ankit Kumar', 'ankit.kumar@gmail.com', '+91 98765 43210', 'Computer Science & Engineering', 'B.Tech', '789123', '31 Dec 2026', '10:00 AM', '11:30 AM', 'Tata Consultancy Services (TCS Digital)', 'Digital Software Engineer', '7.5 LPA', 'PENDING'),
(2, 'PLC-2026-INFY', 'STU123456', 'Ankit Kumar', 'ankit.kumar@gmail.com', '+91 98765 43210', 'Computer Science & Engineering', 'B.Tech', '456789', '31 Dec 2026', '02:00 PM', '04:00 PM', 'Infosys Specialist Programmer', 'Specialist Programmer', '9.5 LPA', 'PENDING'),
(3, 'PLC-2026-AMZN', 'STU123456', 'Ankit Kumar', 'ankit.kumar@gmail.com', '+91 98765 43210', 'Computer Science & Engineering', 'B.Tech', '998877', '31 Dec 2026', '10:00 AM', '12:00 PM', 'Amazon Web Services (AWS)', 'Software Development Engineer - 1 (SDE-1)', '28.0 LPA', 'PENDING');

-- ========================================================
-- QUESTIONS CONFIGURATION FOR EACH EXAM (DISTINCT DOMAIN SETS)
-- ========================================================

-- [EXAM 1] NAT-2026-EXAM (10 MCQs, 2 Coding Challenges, 1 Essay)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('NAT-2026-EXAM', 1, 'MCQ', 'BST Search Complexity', 'What is the average time complexity of searching an element in a balanced Binary Search Tree (AVL / Red-Black Tree)?', 
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'O(1)'), JSON_OBJECT('key', 'B', 'text', 'O(n)'), JSON_OBJECT('key', 'C', 'text', 'O(log n)'), JSON_OBJECT('key', 'D', 'text', 'O(n log n)')), 'C', 2.00),
('NAT-2026-EXAM', 2, 'MCQ', 'HTTP Authentication Status Code', 'Which of the following HTTP status codes specifically signifies that authentication credentials are missing or invalid?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', '400 Bad Request'), JSON_OBJECT('key', 'B', 'text', '401 Unauthorized'), JSON_OBJECT('key', 'C', 'text', '403 Forbidden'), JSON_OBJECT('key', 'D', 'text', '404 Not Found')), 'B', 2.00),
('NAT-2026-EXAM', 3, 'MCQ', 'JavaScript Async Architecture', 'In modern JavaScript runtimes (V8/Node.js), what core architectural component orchestrates non-blocking asynchronous I/O execution?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'Kernel Fiber Scheduling'), JSON_OBJECT('key', 'B', 'text', 'Event Loop & Libuv Threadpool'), JSON_OBJECT('key', 'C', 'text', 'Direct Hardware Interrupt Handlers'), JSON_OBJECT('key', 'D', 'text', 'POSIX Signal Dispatcher')), 'B', 2.00),
('NAT-2026-EXAM', 4, 'MCQ', 'Database Normalization', 'Which Normal Form in relational database theory strictly eliminates transitive functional dependencies?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', '1NF'), JSON_OBJECT('key', 'B', 'text', '2NF'), JSON_OBJECT('key', 'C', 'text', '3NF'), JSON_OBJECT('key', 'D', 'text', 'BCNF')), 'C', 2.00),
('NAT-2026-EXAM', 5, 'MCQ', 'Web Security & CSP', 'What is the primary security objective of the Content-Security-Policy (CSP) HTTP response header?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'Encrypting database traffic'), JSON_OBJECT('key', 'B', 'text', 'Preventing Cross-Site Scripting (XSS) and injection attacks'), JSON_OBJECT('key', 'C', 'text', 'Accelerating DNS caching'), JSON_OBJECT('key', 'D', 'text', 'Enforcing CORS pre-flight')), 'B', 2.00),
('NAT-2026-EXAM', 6, 'MCQ', 'Shortest Path Algorithm Optimization', 'Which auxiliary data structure is utilized in Dijkstra algorithm to achieve optimal O((V + E) log V) time complexity?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'Deque'), JSON_OBJECT('key', 'B', 'text', 'Min-Priority Queue / Min-Heap'), JSON_OBJECT('key', 'C', 'text', 'Circular Ring Buffer'), JSON_OBJECT('key', 'D', 'text', 'Monotonic Stack')), 'B', 2.00),
('NAT-2026-EXAM', 7, 'MCQ', 'OS Memory Allocation', 'In operating systems, what term describes the phenomenon where allocated memory partitions contain small, unusable wasted spaces?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'External Fragmentation'), JSON_OBJECT('key', 'B', 'text', 'Internal Fragmentation'), JSON_OBJECT('key', 'C', 'text', 'Page Fault Thrashing'), JSON_OBJECT('key', 'D', 'text', 'Segmentation Fault')), 'B', 2.00),
('NAT-2026-EXAM', 8, 'MCQ', 'SOLID Design Principles', 'Which SOLID principle mandates that classes should be open for extension but closed for modification?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'Single Responsibility Principle'), JSON_OBJECT('key', 'B', 'text', 'Open/Closed Principle (OCP)'), JSON_OBJECT('key', 'C', 'text', 'Liskov Substitution Principle'), JSON_OBJECT('key', 'D', 'text', 'Interface Segregation Principle')), 'B', 2.00),
('NAT-2026-EXAM', 9, 'MCQ', 'Network Transport Protocols', 'Which transport layer protocol provides connection-oriented, reliable, and ordered packet delivery with flow and congestion control?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', 'UDP'), JSON_OBJECT('key', 'B', 'text', 'ICMP'), JSON_OBJECT('key', 'C', 'text', 'TCP'), JSON_OBJECT('key', 'D', 'text', 'IGMP')), 'C', 2.00),
('NAT-2026-EXAM', 10, 'MCQ', 'JavaScript Type System', 'What is the evaluated output of evaluating `typeof NaN` in standard ECMAScript JavaScript?',
 JSON_ARRAY(JSON_OBJECT('key', 'A', 'text', '"undefined"'), JSON_OBJECT('key', 'B', 'text', '"nan"'), JSON_OBJECT('key', 'C', 'text', '"number"'), JSON_OBJECT('key', 'D', 'text', '"object"')), 'C', 2.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `random_input_schema`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('NAT-2026-EXAM', 11, 'CODING', 'Coding Challenge 1: Two Sum Target Indices', 
 'Write a function `twoSum(nums, target)` that takes an array of integers `nums` and an integer `target`, and returns indices of the two numbers such that they add up to `target`.', 
 'twoSum',
 JSON_OBJECT(
   'cpp', '#include <iostream>\n#include <vector>\nusing namespace std;\nvector<int> twoSum(vector<int>& nums, int target) {\n    return {};\n}\nint main() { return 0; }',
   'python', 'def two_sum(nums, target):\n    return []',
   'java', 'public class Solution {\n    public static int[] twoSum(int[] nums, int target) {\n        return new int[]{};\n    }\n}',
   'javascript', 'function twoSum(nums, target) {\n    const map = new Map();\n    for (let i = 0; i < nums.length; i++) {\n        const comp = target - nums[i];\n        if (map.has(comp)) return [map.get(comp), i];\n        map.set(nums[i], i);\n    }\n    return [];\n}'
 ),
 'function twoSum(nums, target) { const map = new Map(); for (let i = 0; i < nums.length; i++) { const comp = target - nums[i]; if (map.has(comp)) return [map.get(comp), i]; map.set(nums[i], i); } return []; }',
 JSON_OBJECT('count', 10, 'params', JSON_ARRAY(JSON_OBJECT('type', 'array_number', 'min', -1000, 'max', 1000, 'minLen', 10, 'maxLen', 50))),
 JSON_ARRAY(JSON_OBJECT('id', 'pub_1', 'nums', JSON_ARRAY(2, 7, 11, 15), 'target', 9, 'expected', JSON_ARRAY(0, 1))),
 JSON_ARRAY(JSON_OBJECT('id', 'hid_1', 'desc', 'Negative Numbers', 'nums', JSON_ARRAY(-3, 4, 3, 90), 'target', 0, 'expected', JSON_ARRAY(0, 2))),
 10.00, 40.00, 50.00),

('NAT-2026-EXAM', 12, 'CODING', 'Coding Challenge 2: Second Largest Element in Array', 
 'Write a function `secondLargest(arr)` that takes an array of integers and returns the second largest distinct element in the array. If it does not exist, return -1.', 
 'secondLargest',
 JSON_OBJECT(
   'cpp', '#include <iostream>\n#include <vector>\nusing namespace std;\nint secondLargest(vector<int>& arr) { return -1; }\nint main() { return 0; }',
   'python', 'def second_largest(arr):\n    return -1',
   'java', 'public class Solution {\n    public static int secondLargest(int[] arr) { return -1; }\n}',
   'javascript', 'function secondLargest(arr) {\n    let max1 = -Infinity, max2 = -Infinity;\n    for (const x of arr) {\n        if (x > max1) { max2 = max1; max1 = x; }\n        else if (x > max2 && x !== max1) { max2 = x; }\n    }\n    return max2 === -Infinity ? -1 : max2;\n}'
 ),
 'function secondLargest(arr) { let max1 = -Infinity, max2 = -Infinity; for (const x of arr) { if (x > max1) { max2 = max1; max1 = x; } else if (x > max2 && x !== max1) { max2 = x; } } return max2 === -Infinity ? -1 : max2; }',
 JSON_OBJECT('count', 10, 'params', JSON_ARRAY(JSON_OBJECT('type', 'array_number', 'min', -1000, 'max', 1000, 'minLen', 10, 'maxLen', 50))),
 JSON_ARRAY(JSON_OBJECT('id', 'pub_1', 'arr', JSON_ARRAY(12, 35, 1, 10, 34, 1), 'expected', 34)),
 JSON_ARRAY(JSON_OBJECT('id', 'hid_1', 'desc', 'All Negatives', 'arr', JSON_ARRAY(-10, -5, -20, -1), 'expected', -5)),
 10.00, 40.00, 50.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `max_marks`, `rubric_json`) VALUES
('NAT-2026-EXAM', 13, 'PARAGRAPH', 'System Architecture & Scalability Strategy', 
 'Explain how you would design a high-throughput online examination proctoring system for 500,000 concurrent students addressing WebSockets, Redis caching, Kafka message queues, and DB sharding.', 
 20.00,
 JSON_OBJECT('max_marks', 20, 'min_words_soft_limit', 40, 'concepts', JSON_ARRAY(
   JSON_OBJECT('id', 'c1', 'name', 'WebSockets / gRPC', 'weight', 5.0, 'keywords', JSON_ARRAY('websocket', 'grpc', 'streaming', 'real-time')),
   JSON_OBJECT('id', 'c2', 'name', 'Redis Caching', 'weight', 5.0, 'keywords', JSON_ARRAY('redis', 'cache', 'in-memory', 'ttl')),
   JSON_OBJECT('id', 'c3', 'name', 'Kafka Queues', 'weight', 5.0, 'keywords', JSON_ARRAY('kafka', 'rabbitmq', 'queue', 'producer', 'consumer')),
   JSON_OBJECT('id', 'c4', 'name', 'DB Sharding', 'weight', 5.0, 'keywords', JSON_ARRAY('sharding', 'replica', 'partitioning', 'indexing'))
 )));

-- [EXAM 2] DSA-MOCK-2026 (Data Structures & Algorithms Mock Test)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('DSA-MOCK-2026', 1, 'MCQ', 'AVL Tree Balancing Factor', 'In an AVL Tree, what is the valid range of balance factor (height(left) - height(right)) for every node?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','-1, 0, or 1'), JSON_OBJECT('key','B','text','-2, 0, or 2'), JSON_OBJECT('key','C','text','0 or 1 only'), JSON_OBJECT('key','D','text','Any integer')), 'A', 10.00),
('DSA-MOCK-2026', 2, 'MCQ', 'Dijkstra Negative Edge Weight', 'What happens when Dijkstra shortest path algorithm is executed on a graph containing negative edge weights?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','It runs in O(V^3) time'), JSON_OBJECT('key','B','text','It may produce incorrect shortest path distances or fail to terminate with negative cycles'), JSON_OBJECT('key','C','text','It automatically converts negative weights to positive'), JSON_OBJECT('key','D','text','It throws an immediate compiler exception')), 'B', 10.00),
('DSA-MOCK-2026', 3, 'MCQ', 'Dynamic Programming Subproblems', 'Which core property must a computational problem satisfy to be optimally solvable via Dynamic Programming?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Optimal Substructure and Overlapping Subproblems'), JSON_OBJECT('key','B','text','Independent Subproblems only'), JSON_OBJECT('key','C','text','Linear Time Divisibility'), JSON_OBJECT('key','D','text','Greedy Choice Property exclusively')), 'A', 10.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('DSA-MOCK-2026', 4, 'CODING', 'Kadane Algorithm: Maximum Subarray Sum',
 'Given an integer array `nums`, find the contiguous subarray with the largest sum and return its sum.\n\nExample: Input: [-2,1,-3,4,-1,2,1,-5,4] -> Output: 6 (Subarray [4,-1,2,1])',
 'maxSubArray',
 JSON_OBJECT(
   'javascript', 'function maxSubArray(nums) {\n    let maxSum = nums[0], curr = nums[0];\n    for (let i = 1; i < nums.length; i++) {\n        curr = Math.max(nums[i], curr + nums[i]);\n        maxSum = Math.max(maxSum, curr);\n    }\n    return maxSum;\n}',
   'cpp', '#include <vector>\n#include <algorithm>\nusing namespace std;\nint maxSubArray(vector<int>& nums) {\n    int maxSum = nums[0], curr = nums[0];\n    for (size_t i = 1; i < nums.size(); i++) {\n        curr = max(nums[i], curr + nums[i]);\n        maxSum = max(maxSum, curr);\n    }\n    return maxSum;\n}'
 ),
 'function maxSubArray(nums) { let max = nums[0], cur = nums[0]; for(let i=1;i<nums.length;i++){ cur = Math.max(nums[i], cur+nums[i]); max = Math.max(max, cur); } return max; }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(-2,1,-3,4,-1,2,1,-5,4), 'expected', 6)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(5,4,-1,7,8), 'expected', 23), JSON_OBJECT('id','h2', 'nums', JSON_ARRAY(-1), 'expected', -1)),
 10.00, 25.00, 35.00),

('DSA-MOCK-2026', 5, 'CODING', 'Array Reversal Algorithm',
 'Given an array of integers `nums`, reverse the array elements in place and return the reversed array.',
 'reverseArray',
 JSON_OBJECT(
   'javascript', 'function reverseArray(nums) {\n    let l = 0, r = nums.length - 1;\n    while (l < r) {\n        let tmp = nums[l]; nums[l] = nums[r]; nums[r] = tmp;\n        l++; r--;\n    }\n    return nums;\n}'
 ),
 'function reverseArray(nums) { return nums.reverse(); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(1,2,3,4,5), 'expected', JSON_ARRAY(5,4,3,2,1))),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(10,20), 'expected', JSON_ARRAY(20,10))),
 10.00, 25.00, 35.00);

-- [EXAM 3] CPP-PROG-2026 (C++ Object-Oriented Programming Test)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('CPP-PROG-2026', 1, 'MCQ', 'C++ Virtual Destructor', 'Why should a base class containing virtual member functions always declare a virtual destructor?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','To prevent memory leaks when deleting derived objects through base pointers'), JSON_OBJECT('key','B','text','To speed up compilation'), JSON_OBJECT('key','C','text','To make the class abstract'), JSON_OBJECT('key','D','text','To allow private inheritance')), 'A', 5.00),
('CPP-PROG-2026', 2, 'MCQ', 'C++ std::unique_ptr Ownership', 'What happens when you attempt to copy-assign an instance of std::unique_ptr to another unique_ptr in C++17?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Compile-time error because copy constructor is deleted'), JSON_OBJECT('key','B','text','Reference count increases by 1'), JSON_OBJECT('key','C','text','Object cloned on heap'), JSON_OBJECT('key','D','text','Runtime segmentation fault')), 'A', 5.00),
('CPP-PROG-2026', 3, 'MCQ', 'C++ RAII Paradigm', 'What is the primary benefit of the Resource Acquisition Is Initialization (RAII) pattern in C++?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Guarantees resource release upon stack unwinding during exceptions'), JSON_OBJECT('key','B','text','Increases CPU clock frequency'), JSON_OBJECT('key','C','text','Enables garbage collector runtime'), JSON_OBJECT('key','D','text','Prevents all template errors')), 'A', 5.00),
('CPP-PROG-2026', 4, 'MCQ', 'Diamond Problem in C++', 'How does C++ resolve the Diamond Problem in multiple inheritance?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Virtual base classes (virtual inheritance)'), JSON_OBJECT('key','B','text','Interface segregation keyword'), JSON_OBJECT('key','C','text','Static dispatch override'), JSON_OBJECT('key','D','text','Namespaces')), 'A', 5.00),
('CPP-PROG-2026', 5, 'MCQ', 'C++ constexpr vs const', 'What distinguishes `constexpr` from `const` in modern C++?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','constexpr guarantees evaluation at compile-time when possible'), JSON_OBJECT('key','B','text','const creates pointers'), JSON_OBJECT('key','C','text','constexpr is only for classes'), JSON_OBJECT('key','D','text','They are 100% identical')), 'A', 5.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('CPP-PROG-2026', 6, 'CODING', 'Valid Anagram String Comparator',
 'Write a function `isAnagram(s, t)` that returns `true` if `t` is an anagram of `s`, and `false` otherwise.',
 'isAnagram',
 JSON_OBJECT(
   'javascript', 'function isAnagram(s, t) {\n    if (s.length !== t.length) return false;\n    return s.split("").sort().join("") === t.split("").sort().join("");\n}'
 ),
 'function isAnagram(s, t) { if (s.length !== t.length) return false; return s.split("").sort().join("") === t.split("").sort().join(""); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 's', 'anagram', 't', 'nagaram', 'expected', true)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 's', 'rat', 't', 'car', 'expected', false), JSON_OBJECT('id','h2', 's', 'listen', 't', 'silent', 'expected', true)),
 5.00, 10.00, 15.00),

('CPP-PROG-2026', 7, 'CODING', 'Matrix Diagonal Sum',
 'Given a square matrix `mat`, return the sum of the matrix diagonals (primary and secondary). An element on both diagonals should only be included once.',
 'diagonalSum',
 JSON_OBJECT(
   'javascript', 'function diagonalSum(mat) {\n    let n = mat.length, sum = 0;\n    for (let i = 0; i < n; i++) {\n        sum += mat[i][i];\n        if (i !== n - 1 - i) sum += mat[i][n - 1 - i];\n    }\n    return sum;\n}'
 ),
 'function diagonalSum(mat) { let n = mat.length, sum = 0; for(let i=0;i<n;i++){ sum += mat[i][i]; if(i !== n-1-i) sum += mat[i][n-1-i]; } return sum; }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'mat', JSON_ARRAY(JSON_ARRAY(1,2,3),JSON_ARRAY(4,5,6),JSON_ARRAY(7,8,9)), 'expected', 25)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'mat', JSON_ARRAY(JSON_ARRAY(1,1,1,1),JSON_ARRAY(1,1,1,1),JSON_ARRAY(1,1,1,1),JSON_ARRAY(1,1,1,1)), 'expected', 8)),
 5.00, 5.00, 10.00);

-- [EXAM 4] APT-TEST-2026 (General Quantitative & Reasoning Assessment)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('APT-TEST-2026', 1, 'MCQ', 'Time and Work Problem', 'A can finish a task in 12 days and B can finish it in 24 days. Working together, in how many days will they finish the task?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','6 days'), JSON_OBJECT('key','B','text','8 days'), JSON_OBJECT('key','C','text','9 days'), JSON_OBJECT('key','D','text','10 days')), 'B', 5.00),
('APT-TEST-2026', 2, 'MCQ', 'Probability Dice Problem', 'What is the probability of rolling a sum of 7 with two standard six-sided dice?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','1/6'), JSON_OBJECT('key','B','text','1/12'), JSON_OBJECT('key','C','text','7/36'), JSON_OBJECT('key','D','text','5/36')), 'A', 5.00),
('APT-TEST-2026', 3, 'MCQ', 'Permutation Letter Arrangement', 'In how many distinct ways can the letters of the word "LEADER" be arranged?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','360'), JSON_OBJECT('key','B','text','720'), JSON_OBJECT('key','C','text','120'), JSON_OBJECT('key','D','text','480')), 'A', 5.00),
('APT-TEST-2026', 4, 'MCQ', 'Compound Interest Calculation', 'What is the compound interest on Rs 10,000 for 2 years at 10% per annum compounded annually?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Rs 2,100'), JSON_OBJECT('key','B','text','Rs 2,000'), JSON_OBJECT('key','C','text','Rs 2,200'), JSON_OBJECT('key','D','text','Rs 1,800')), 'A', 5.00),
('APT-TEST-2026', 5, 'MCQ', 'Clock Angle Problem', 'What is the angle between the hour hand and minute hand of a clock at 3:30?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','75 degrees'), JSON_OBJECT('key','B','text','90 degrees'), JSON_OBJECT('key','C','text','85 degrees'), JSON_OBJECT('key','D','text','60 degrees')), 'A', 5.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('APT-TEST-2026', 6, 'CODING', 'Palindrome Integer Checker',
 'Given an integer `x`, return `true` if `x` is a palindrome, and `false` otherwise without converting negative numbers.',
 'isPalindrome',
 JSON_OBJECT(
   'javascript', 'function isPalindrome(x) {\n    if (x < 0) return false;\n    const s = x.toString();\n    return s === s.split("").reverse().join("");\n}'
 ),
 'function isPalindrome(x) { if (x < 0) return false; const s = x.toString(); return s === s.split("").reverse().join(""); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'x', 121, 'expected', true)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'x', -121, 'expected', false), JSON_OBJECT('id','h2', 'x', 10, 'expected', false)),
 5.00, 10.00, 15.00),

('APT-TEST-2026', 7, 'CODING', 'Hamming Weight: Count 1 Bits',
 'Given a positive integer `n`, return the number of set bits (1s) in its binary representation.',
 'hammingWeight',
 JSON_OBJECT(
   'javascript', 'function hammingWeight(n) {\n    let count = 0;\n    while (n > 0) {\n        count += (n & 1);\n        n = Math.floor(n / 2);\n    }\n    return count;\n}'
 ),
 'function hammingWeight(n) { let count = 0; while (n > 0) { count += (n & 1); n = Math.floor(n / 2); } return count; }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'n', 11, 'expected', 3)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'n', 128, 'expected', 1), JSON_OBJECT('id','h2', 'n', 255, 'expected', 8)),
 5.00, 5.00, 10.00);

-- [EXAM 5] SQL-DB-2026 (Relational Databases & SQL Optimization)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('SQL-DB-2026', 1, 'MCQ', 'BCNF vs 3NF Difference', 'What additional condition does Boyce-Codd Normal Form (BCNF) mandate compared to 3NF?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','For every functional dependency X -> Y, X must be a superkey'), JSON_OBJECT('key','B','text','Eliminates partial dependencies'), JSON_OBJECT('key','C','text','Eliminates atomic columns'), JSON_OBJECT('key','D','text','Requires JSON column indexing')), 'A', 5.00),
('SQL-DB-2026', 2, 'MCQ', 'ACID Isolation Level: Dirty Read', 'Which SQL transaction isolation level prevents dirty reads but allows non-repeatable reads?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Read Committed'), JSON_OBJECT('key','B','text','Read Uncommitted'), JSON_OBJECT('key','C','text','Repeatable Read'), JSON_OBJECT('key','D','text','Serializable')), 'A', 5.00),
('SQL-DB-2026', 3, 'MCQ', 'Clustered Index Physical Layout', 'How many Clustered Indexes can a single relational database table physically possess in InnoDB/MySQL?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Exactly 1, because leaf nodes contain actual table row data'), JSON_OBJECT('key','B','text','Up to 16'), JSON_OBJECT('key','C','text','Unlimited'), JSON_OBJECT('key','D','text','0')), 'A', 5.00),
('SQL-DB-2026', 4, 'MCQ', 'Write-Ahead Logging (WAL)', 'What is the primary role of Write-Ahead Logging (WAL / redo log) in relational databases?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Ensures atomicity and durability by writing changes to log before disk flush'), JSON_OBJECT('key','B','text','Compresses SQL syntax'), JSON_OBJECT('key','C','text','Generates automatic backups'), JSON_OBJECT('key','D','text','Encrypts network traffic')), 'A', 5.00),
('SQL-DB-2026', 5, 'MCQ', 'B+ Tree Index Range Scans', 'Why are B+ Trees favored over B-Trees for database indices?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Leaf nodes are linked in a linked list allowing fast range queries'), JSON_OBJECT('key','B','text','Uses zero RAM'), JSON_OBJECT('key','C','text','Stores data in hashing buckets'), JSON_OBJECT('key','D','text','Requires no rebalancing')), 'A', 5.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('SQL-DB-2026', 6, 'CODING', 'Find Duplicate Elements in Array',
 'Given an integer array `nums` of length `n` where all integers are in the range `[1, n]`, return an array of all the integers that appear twice.',
 'findDuplicates',
 JSON_OBJECT(
   'javascript', 'function findDuplicates(nums) {\n    const seen = new Set(), dups = [];\n    for (const x of nums) {\n        if (seen.has(x)) dups.push(x);\n        else seen.add(x);\n    }\n    return dups.sort((a,b)=>a-b);\n}'
 ),
 'function findDuplicates(nums) { const seen = new Set(), dups = []; for(const x of nums){ if(seen.has(x)) dups.push(x); else seen.add(x); } return dups.sort((a,b)=>a-b); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(4,3,2,7,8,2,3,1), 'expected', JSON_ARRAY(2,3))),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(1,1,2), 'expected', JSON_ARRAY(1))),
 10.00, 20.00, 30.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `max_marks`, `rubric_json`) VALUES
('SQL-DB-2026', 7, 'PARAGRAPH', 'Database Sharding & Replication Architecture',
 'Describe how you would design a multi-region MySQL database cluster utilizing read replicas, connection pooling, and sharding to handle 100,000 queries per second.',
 25.00,
 JSON_OBJECT('max_marks', 25, 'min_words_soft_limit', 30, 'concepts', JSON_ARRAY(
   JSON_OBJECT('id','c1','name','Read Replicas & Binlog','weight',8.0,'keywords',JSON_ARRAY('replica','replication','binlog','read-only','master-slave')),
   JSON_OBJECT('id','c2','name','Connection Pooling (ProxySQL)','weight',8.0,'keywords',JSON_ARRAY('pool','proxysql','connection','throughput','overhead')),
   JSON_OBJECT('id','c3','name','Sharding Key Selection','weight',9.0,'keywords',JSON_ARRAY('sharding','partitioning','hash','range','consistency'))
 )));

-- [EXAM 6] SYS-DESIGN-2026 (Distributed System Architecture & Security)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('SYS-DESIGN-2026', 1, 'MCQ', 'CAP Theorem Trade-Off', 'According to CAP theorem, in the presence of a network partition (P), what trade-off must a distributed system choose?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Consistency (C) or Availability (A)'), JSON_OBJECT('key','B','text','Latency or Throughput'), JSON_OBJECT('key','C','text','Security or Speed'), JSON_OBJECT('key','D','text','ACID or BASE')), 'A', 5.00),
('SYS-DESIGN-2026', 2, 'MCQ', 'Consistent Hashing Virtual Nodes', 'What problem do virtual nodes (vnodes) solve in Consistent Hashing rings?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Non-uniform key distribution (hot spots)'), JSON_OBJECT('key','B','text','Network packet loss'), JSON_OBJECT('key','C','text','Database deadlock'), JSON_OBJECT('key','D','text','Cache corruption')), 'A', 5.00),
('SYS-DESIGN-2026', 3, 'MCQ', 'Rate Limiting Token Bucket Algorithm', 'How does the Token Bucket algorithm handle sudden traffic bursts?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Allows bursts up to bucket capacity while maintaining average refill rate'), JSON_OBJECT('key','B','text','Drops all burst requests immediately'), JSON_OBJECT('key','C','text','Slows down CPU clock'), JSON_OBJECT('key','D','text','Queues infinitely')), 'A', 5.00),
('SYS-DESIGN-2026', 4, 'MCQ', 'OAuth 2.0 PKCE Extension', 'Why is Proof Key for Code Exchange (PKCE) mandatory for single-page applications (SPAs)?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Prevents authorization code interception attack without requiring client secret'), JSON_OBJECT('key','B','text','Encrypts local storage'), JSON_OBJECT('key','C','text','Speeds up JSON parsing'), JSON_OBJECT('key','D','text','Disables CORS headers')), 'A', 5.00),
('SYS-DESIGN-2026', 5, 'MCQ', 'Circuit Breaker States', 'What are the three canonical states of the Circuit Breaker pattern in microservices?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Closed, Open, Half-Open'), JSON_OBJECT('key','B','text','Active, Inactive, Paused'), JSON_OBJECT('key','C','text','Running, Stopped, Failed'), JSON_OBJECT('key','D','text','Ready, Blocked, Terminated')), 'A', 5.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('SYS-DESIGN-2026', 6, 'CODING', 'Longest Palindromic Substring',
 'Given a string `s`, return the longest palindromic substring in `s`.',
 'longestPalindrome',
 JSON_OBJECT(
   'javascript', 'function longestPalindrome(s) {\n    if (!s || s.length <= 1) return s;\n    let start = 0, maxLen = 1;\n    function expand(l, r) {\n        while (l >= 0 && r < s.length && s[l] === s[r]) {\n            if (r - l + 1 > maxLen) { start = l; maxLen = r - l + 1; }\n            l--; r++;\n        }\n    }\n    for (let i = 0; i < s.length; i++) {\n        expand(i, i);\n        expand(i, i + 1);\n    }\n    return s.substring(start, start + maxLen);\n}'
 ),
 'function longestPalindrome(s) { if(!s||s.length<=1)return s; let start=0,max=1; function exp(l,r){ while(l>=0&&r<s.length&&s[l]===s[r]){ if(r-l+1>max){ start=l; max=r-l+1; } l--; r++; } } for(let i=0;i<s.length;i++){ exp(i,i); exp(i,i+1); } return s.substring(start,start+max); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 's', 'babad', 'expected', 'bab')),
 JSON_ARRAY(JSON_OBJECT('id','h1', 's', 'cbbd', 'expected', 'bb'), JSON_OBJECT('id','h2', 's', 'racecar', 'expected', 'racecar')),
 10.00, 25.00, 35.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `max_marks`, `rubric_json`) VALUES
('SYS-DESIGN-2026', 7, 'PARAGRAPH', 'Distributed Notification Dispatcher Design',
 'Design an enterprise notification system that sends SMS, Email, and Push notifications with retry policies, rate limiting, and deduplication.',
 40.00,
 JSON_OBJECT('max_marks', 40, 'min_words_soft_limit', 40, 'concepts', JSON_ARRAY(
   JSON_OBJECT('id','c1','name','Message Queue Decoupling','weight',10.0,'keywords',JSON_ARRAY('kafka','rabbitmq','sqs','async','producer','consumer')),
   JSON_OBJECT('id','c2','name','Idempotency & Deduplication','weight',10.0,'keywords',JSON_ARRAY('idempotent','deduplication','redis','uuid','key')),
   JSON_OBJECT('id','c3','name','Rate Limiting & Throttling','weight',10.0,'keywords',JSON_ARRAY('rate limit','token bucket','throttle','leaky bucket')),
   JSON_OBJECT('id','c4','name','Dead Letter Queue & Retries','weight',10.0,'keywords',JSON_ARRAY('dlq','dead letter','retry','exponential backoff'))
 )));

-- [EXAM 7] PLC-2026-TCS (Tata Consultancy Services Placement Drive)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('PLC-2026-TCS', 1, 'MCQ', 'TCS Number Series Logic', 'Find the next number in series: 2, 6, 12, 20, 30, ?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','40'), JSON_OBJECT('key','B','text','42'), JSON_OBJECT('key','C','text','44'), JSON_OBJECT('key','D','text','36')), 'B', 5.00),
('PLC-2026-TCS', 2, 'MCQ', 'String Anagram Property', 'Two strings are called anagrams of each other if:',
 JSON_ARRAY(JSON_OBJECT('key','A','text','They have the exact same character frequencies irrespective of order'), JSON_OBJECT('key','B','text','They start with the same vowel'), JSON_OBJECT('key','C','text','They have equal byte sizes only'), JSON_OBJECT('key','D','text','They are palindromes')), 'A', 5.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('PLC-2026-TCS', 3, 'CODING', 'Rotate Array by K Steps',
 'Given an integer array `nums`, rotate the array to the right by `k` steps, where `k` is non-negative.',
 'rotateArray',
 JSON_OBJECT(
   'javascript', 'function rotateArray(nums, k) {\n    k = k % nums.length;\n    if (k === 0) return nums;\n    const part = nums.splice(nums.length - k, k);\n    nums.unshift(...part);\n    return nums;\n}'
 ),
 'function rotateArray(nums, k) { k = k % nums.length; if (k === 0) return nums; const part = nums.splice(nums.length - k, k); nums.unshift(...part); return nums; }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(1,2,3,4,5,6,7), 'k', 3, 'expected', JSON_ARRAY(5,6,7,1,2,3,4))),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(-1,-100,3,99), 'k', 2, 'expected', JSON_ARRAY(3,99,-1,-100))),
 10.00, 30.00, 40.00);

-- [EXAM 8] PLC-2026-INFY (Infosys Specialist Programmer Challenge)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('PLC-2026-INFY', 1, 'MCQ', 'Infosys Bitwise XOR Property', 'What is the result of `X ^ 0` and `X ^ X` for any integer `X`?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','X and 0'), JSON_OBJECT('key','B','text','0 and X'), JSON_OBJECT('key','C','text','1 and 0'), JSON_OBJECT('key','D','text','X and 1')), 'A', 10.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('PLC-2026-INFY', 2, 'CODING', 'Maximum Product Subarray',
 'Given an integer array `nums`, find a subarray that has the largest product, and return the product.',
 'maxProduct',
 JSON_OBJECT(
   'javascript', 'function maxProduct(nums) {\n    let max = nums[0], min = nums[0], res = nums[0];\n    for (let i = 1; i < nums.length; i++) {\n        if (nums[i] < 0) { let tmp = max; max = min; min = tmp; }\n        max = Math.max(nums[i], max * nums[i]);\n        min = Math.min(nums[i], min * nums[i]);\n        res = Math.max(res, max);\n    }\n    return res;\n}'
 ),
 'function maxProduct(nums) { let max = nums[0], min = nums[0], res = nums[0]; for(let i=1;i<nums.length;i++){ if(nums[i]<0){ let tmp=max; max=min; min=tmp; } max=Math.max(nums[i], max*nums[i]); min=Math.min(nums[i], min*nums[i]); res=Math.max(res, max); } return res; }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(2,3,-2,4), 'expected', 6)),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(-2,0,-1), 'expected', 0), JSON_OBJECT('id','h2', 'nums', JSON_ARRAY(-2,3,-4), 'expected', 24)),
 10.00, 40.00, 50.00);

-- [EXAM 9] PLC-2026-AMZN (Amazon SDE-1 Campus Hiring Drive)
INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `options`, `correct_answer`, `max_marks`) VALUES
('PLC-2026-AMZN', 1, 'MCQ', 'Amazon SDE LRU Eviction Policy', 'In an LRU (Least Recently Used) Cache with O(1) get and put, which combined data structure is utilized?',
 JSON_ARRAY(JSON_OBJECT('key','A','text','Doubly Linked List + Hash Map'), JSON_OBJECT('key','B','text','Min-Heap + Array'), JSON_OBJECT('key','C','text','Binary Search Tree only'), JSON_OBJECT('key','D','text','Circular Ring Buffer only')), 'A', 10.00);

INSERT IGNORE INTO `questions` (`exam_code`, `question_number`, `type`, `title`, `question_text`, `entry_function`, `coding_starter_code`, `reference_solution`, `public_test_cases`, `hidden_test_cases`, `public_weightage_marks`, `hidden_weightage_marks`, `max_marks`) VALUES
('PLC-2026-AMZN', 2, 'CODING', 'Top K Frequent Elements',
 'Given an integer array `nums` and an integer `k`, return the `k` most frequent elements sorted in descending order of frequency.',
 'topKFrequent',
 JSON_OBJECT(
   'javascript', 'function topKFrequent(nums, k) {\n    const map = new Map();\n    for (const x of nums) map.set(x, (map.get(x) || 0) + 1);\n    const sorted = Array.from(map.entries()).sort((a, b) => b[1] - a[1]);\n    return sorted.slice(0, k).map(e => e[0]);\n}'
 ),
 'function topKFrequent(nums, k) { const map = new Map(); for (const x of nums) map.set(x, (map.get(x) || 0) + 1); const sorted = Array.from(map.entries()).sort((a, b) => b[1] - a[1]); return sorted.slice(0, k).map(e => e[0]); }',
 JSON_ARRAY(JSON_OBJECT('id','p1', 'nums', JSON_ARRAY(1,1,1,2,2,3), 'k', 2, 'expected', JSON_ARRAY(1,2))),
 JSON_ARRAY(JSON_OBJECT('id','h1', 'nums', JSON_ARRAY(1), 'k', 1, 'expected', JSON_ARRAY(1))),
 10.00, 30.00, 40.00);

-- ========================================================
-- TOPIC LEARNING & PRACTICE SEED DATA
-- ========================================================

-- Topic 1.1 Content, MCQs & 3 Coding Problems
INSERT IGNORE INTO `course_lesson_content` (`course_id`, `lesson_num`, `concept_summary`, `detailed_notes`, `code_example`, `key_takeaways`) VALUES
('course-cpp', '1.1',
 'Introduction to modern C++, its origins from C with Classes, and its core zero-overhead abstraction philosophy.',
 '<h3>1. What is C++?</h3><p>C++ is a high-performance, general-purpose programming language created by Bjarne Stroustrup at Bell Labs in 1979 as an extension of the C programming language. It provides fine-grained hardware control alongside powerful object-oriented, generic, and functional programming paradigms.</p><h3>2. Why C++ is Used Today?</h3><ul><li><strong>Zero-Overhead Abstractions:</strong> What you don’t use, you don’t pay for.</li><li><strong>Direct Memory Management:</strong> Direct pointer manipulation, custom allocators, and deterministic destructors (RAII).</li><li><strong>Dominance in Critical Systems:</strong> Game engines, high-frequency trading (HFT), operating systems, and embedded systems.</li></ul>',
 '#include <iostream>\n\nint main() {\n    std::cout << "Welcome to Modern C++ Mastery!" << "\\n";\n    return 0;\n}',
 'C++ is a multi-paradigm language; RAII guarantees automatic resource cleanup; std::cout is part of the <iostream> header.');

INSERT IGNORE INTO `course_topic_mcqs` (`course_id`, `lesson_num`, `question_number`, `question_text`, `options_json`, `correct_key`, `explanation`) VALUES
('course-cpp', '1.1', 1, 'Which year was the C++ programming language created by Bjarne Stroustrup at Bell Labs?', JSON_ARRAY(JSON_OBJECT('key','A','text','1972'),JSON_OBJECT('key','B','text','1979'),JSON_OBJECT('key','C','text','1991'),JSON_OBJECT('key','D','text','1998')), 'B', 'C++ (originally C with Classes) began development in 1979.'),
('course-cpp', '1.1', 2, 'What does the core C++ design philosophy "Zero-Overhead Principle" mean?', JSON_ARRAY(JSON_OBJECT('key','A','text','Zero cost for compiler licenses'),JSON_OBJECT('key','B','text','What you do not use, you do not pay for in performance or memory'),JSON_OBJECT('key','C','text','Programs consume 0 bytes of RAM'),JSON_OBJECT('key','D','text','Zero compile time')), 'B', 'Features unused at runtime cost nothing in execution time or memory.'),
('course-cpp', '1.1', 3, 'Which standard header file provides std::cout and std::cin streams?', JSON_ARRAY(JSON_OBJECT('key','A','text','<stdio.h>'),JSON_OBJECT('key','B','text','<stdlib.h>'),JSON_OBJECT('key','C','text','<iostream>'),JSON_OBJECT('key','D','text','<string>')), 'C', '<iostream> defines the standard input and output streams.'),
('course-cpp', '1.1', 4, 'What is the correct sequence of steps in the C++ compilation pipeline?', JSON_ARRAY(JSON_OBJECT('key','A','text','Preprocessor -> Compiler -> Assembler -> Linker'),JSON_OBJECT('key','B','text','Linker -> Assembler -> Compiler -> Preprocessor'),JSON_OBJECT('key','C','text','Compiler -> Preprocessor -> Linker -> Assembler'),JSON_OBJECT('key','D','text','Assembler -> Linker -> Compiler -> Preprocessor')), 'A', 'Source goes to Preprocessor (# directives) -> Compiler (Assembly) -> Assembler (Machine code) -> Linker (Executable).'),
('course-cpp', '1.1', 5, 'Which modern C++ standard introduced Concepts, Ranges, Coroutines, and Modules?', JSON_ARRAY(JSON_OBJECT('key','A','text','C++11'),JSON_OBJECT('key','B','text','C++14'),JSON_OBJECT('key','C','text','C++17'),JSON_OBJECT('key','D','text','C++20')), 'D', 'C++20 introduced the major pillars: Concepts, Ranges, Coroutines, and Modules.');

INSERT IGNORE INTO `course_topic_coding` (`course_id`, `lesson_num`, `problem_number`, `title`, `problem_statement`, `difficulty`, `constraints_text`, `sample_input`, `sample_output`, `starter_code_cpp`, `starter_code_py`, `starter_code_java`, `starter_code_js`, `test_cases_json`) VALUES
('course-cpp', '1.1', 1, 'Welcome to C++ Mastery', 'Print the exact text "Welcome to Modern C++ Mastery!" followed by a newline.', 'Easy', 'None', '', 'Welcome to Modern C++ Mastery!', '#include <iostream>\n\nint main() {\n    std::cout << "Welcome to Modern C++ Mastery!" << std::endl;\n    return 0;\n}', 'print("Welcome to Modern C++ Mastery!")', 'public class Main {\n    public static void main(String[] args) {\n        System.out.println("Welcome to Modern C++ Mastery!");\n    }\n}', 'console.log("Welcome to Modern C++ Mastery!");', JSON_ARRAY(JSON_OBJECT('input','', 'expected_output','Welcome to Modern C++ Mastery!'))),
('course-cpp', '1.1', 2, 'Rectangle Area & Perimeter', 'Given two integers L (Length) and B (Breadth), calculate and print the Area and Perimeter separated by a space.', 'Easy', '1 <= L, B <= 10000', '5 10', '50 30', '#include <iostream>\n\nint main() {\n    long long l, b;\n    if (std::cin >> l >> b) {\n        std::cout << (l * b) << " " << (2 * (l + b)) << std::endl;\n    }\n    return 0;\n}', 'import sys\nparts = sys.stdin.read().split()\nif len(parts) >= 2:\n    l, b = int(parts[0]), int(parts[1])\n    print(f"{l*b} {2*(l+b)}")', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long l = sc.nextLong();\n            long b = sc.nextLong();\n            System.out.println((l*b) + " " + (2*(l+b)));\n        }\n    }\n}', 'const fs = require("fs");\nconst parts = fs.readFileSync(0, "utf-8").trim().split(/\\s+/);\nif (parts.length >= 2) {\n    const l = BigInt(parts[0]), b = BigInt(parts[1]);\n    console.log(`${l*b} ${2n*(l+b)}`);\n}', JSON_ARRAY(JSON_OBJECT('input','5 10', 'expected_output','50 30'),JSON_OBJECT('input','12 8', 'expected_output','96 40'),JSON_OBJECT('input','100 200', 'expected_output','20000 600'))),
('course-cpp', '1.1', 3, 'Temperature Converter (C to F)', 'Given temperature in Celsius (C), calculate and print Fahrenheit (F) using formula: F = (C * 9/5) + 32.', 'Easy', '-100 <= C <= 500', '0', '32', '#include <iostream>\n\nint main() {\n    long long c;\n    if (std::cin >> c) {\n        long long f = (c * 9 / 5) + 32;\n        std::cout << f << std::endl;\n    }\n    return 0;\n}', 'import sys\nval = sys.stdin.read().strip()\nif val:\n    c = int(val)\n    print((c * 9 // 5) + 32)', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long c = sc.nextLong();\n            System.out.println((c * 9 / 5) + 32);\n        }\n    }\n}', 'const fs = require("fs");\nconst val = fs.readFileSync(0, "utf-8").trim();\nif (val) {\n    const c = BigInt(val);\n    console.log(((c * 9n / 5n) + 32n).toString());\n}', JSON_ARRAY(JSON_OBJECT('input','0', 'expected_output','32'),JSON_OBJECT('input','100', 'expected_output','212'),JSON_OBJECT('input','25', 'expected_output','77')));

-- Topic 1.2 Content, 2 MCQs & 1 Coding Problem
INSERT IGNORE INTO `course_lesson_content` (`course_id`, `lesson_num`, `concept_summary`, `detailed_notes`, `code_example`, `key_takeaways`) VALUES
('course-cpp', '1.2',
 'Setting up C++ toolchains across GCC, Clang and MSVC, and understanding the 4 compilation phases.',
 '<h3>1. The C++ Compilation Pipeline</h3><ol><li><strong>Preprocessing:</strong> Resolves <code>#include</code> and <code>#define</code> macros.</li><li><strong>Compilation:</strong> Converts preprocessed C++ source into assembly code.</li><li><strong>Assembly:</strong> Translates assembly code into machine object code (<code>.o</code> / <code>.obj</code>).</li><li><strong>Linking:</strong> Combines object files with runtime standard libraries into a binary executable.</li></ol>',
 '// Compile with: g++ -std=c++20 -Wall main.cpp -o main\n#include <iostream>\n\nint main() {\n    std::cout << "Compiled with GCC / Clang\\n";\n    return 0;\n}',
 'Always compile with -Wall -Wextra; understanding the 4 compilation phases aids debugging linking errors.');

INSERT IGNORE INTO `course_topic_mcqs` (`course_id`, `lesson_num`, `question_number`, `question_text`, `options_json`, `correct_key`, `explanation`) VALUES
('course-cpp', '1.2', 1, 'Which command compiles a C++ source file using the GNU GCC toolchain?', JSON_ARRAY(JSON_OBJECT('key','A','text','gcc main.cpp'),JSON_OBJECT('key','B','text','g++ -std=c++20 main.cpp -o main'),JSON_OBJECT('key','C','text','cpp run main.cpp'),JSON_OBJECT('key','D','text','build main.cpp')), 'B', 'g++ is the standard GCC C++ frontend compiler command.'),
('course-cpp', '1.2', 2, 'What is the purpose of header guards (#ifndef, #define, #endif) in C++ header files?', JSON_ARRAY(JSON_OBJECT('key','A','text','To encrypt source code'),JSON_OBJECT('key','B','text','To prevent double-inclusion and multiple symbol definitions'),JSON_OBJECT('key','C','text','To speed up disk read speeds'),JSON_OBJECT('key','D','text','To allocate heap memory')), 'B', 'Header guards ensure a header file is only parsed once per translation unit.');

INSERT IGNORE INTO `course_topic_coding` (`course_id`, `lesson_num`, `problem_number`, `title`, `problem_statement`, `difficulty`, `constraints_text`, `sample_input`, `sample_output`, `starter_code_cpp`, `starter_code_py`, `starter_code_java`, `starter_code_js`, `test_cases_json`) VALUES
('course-cpp', '1.2', 1, 'Environment Sanity: Double the Input', 'Read an integer X from standard input and print 2 * X.', 'Easy', '1 <= X <= 10^9', '25', '50', '#include <iostream>\n\nint main() {\n    long long x;\n    if (std::cin >> x) {\n        std::cout << (2 * x) << std::endl;\n    }\n    return 0;\n}', 'import sys\nv = sys.stdin.read().strip()\nif v:\n    print(int(v)*2)', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long x = sc.nextLong();\n            System.out.println(2 * x);\n        }\n    }\n}', 'const fs = require("fs");\nconst v = fs.readFileSync(0, "utf-8").trim();\nif (v) console.log((BigInt(v) * 2n).toString());', JSON_ARRAY(JSON_OBJECT('input','25', 'expected_output','50'),JSON_OBJECT('input','1000', 'expected_output','2000'),JSON_OBJECT('input','7', 'expected_output','14')));

-- Topic 1.3 Content, 0 MCQs & 5 Coding Problems
INSERT IGNORE INTO `course_lesson_content` (`course_id`, `lesson_num`, `concept_summary`, `detailed_notes`, `code_example`, `key_takeaways`) VALUES
('course-cpp', '1.3',
 'Mastering main(), return codes, namespaces, and standard formatting in modern C++.',
 '<h3>1. Structure of a Modern C++ Program</h3><p>Every C++ executable begins execution at the top-level <code>main()</code> function. In C++, returning 0 from main signifies successful execution without errors.</p><h3>2. The std Namespace</h3><p>Standard library identifiers like <code>cout</code> and <code>cin</code> live inside the <code>std</code> namespace to prevent global name collisions. Prefer prefixing with <code>std::</code> over <code>using namespace std;</code> in production.</p>',
 '#include <iostream>\n\nint main(int argc, char* argv[]) {\n    std::cout << "Modern C++ Execution: Success\\n";\n    return 0;\n}',
 'main() returns integer exit code (0 = success); avoid global namespace pollution in header files.');

INSERT IGNORE INTO `course_topic_coding` (`course_id`, `lesson_num`, `problem_number`, `title`, `problem_statement`, `difficulty`, `constraints_text`, `sample_input`, `sample_output`, `starter_code_cpp`, `starter_code_py`, `starter_code_java`, `starter_code_js`, `test_cases_json`) VALUES
('course-cpp', '1.3', 1, 'Print Multiplication Line', 'Given an integer N, print "N x 1 = N" and "N x 2 = 2N" on separate lines.', 'Easy', '1 <= N <= 1000', '5', '5 x 1 = 5\n5 x 2 = 10', '#include <iostream>\n\nint main() {\n    int n;\n    if (std::cin >> n) {\n        std::cout << n << " x 1 = " << (n * 1) << "\\n";\n        std::cout << n << " x 2 = " << (n * 2) << "\\n";\n    }\n    return 0;\n}', 'import sys\nval = sys.stdin.read().strip()\nif val:\n    n = int(val)\n    print(f"{n} x 1 = {n*1}\\n{n} x 2 = {n*2}")', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextInt()) {\n            int n = sc.nextInt();\n            System.out.println(n + " x 1 = " + (n * 1));\n            System.out.println(n + " x 2 = " + (n * 2));\n        }\n    }\n}', 'const fs = require("fs");\nconst input = fs.readFileSync(0, "utf-8").trim();\nif (input) {\n    const n = parseInt(input, 10);\n    console.log(`${n} x 1 = ${n*1}\\n${n} x 2 = ${n*2}`);\n}', JSON_ARRAY(JSON_OBJECT('input','5', 'expected_output','5 x 1 = 5\n5 x 2 = 10'),JSON_OBJECT('input','7', 'expected_output','7 x 1 = 7\n7 x 2 = 14'),JSON_OBJECT('input','12', 'expected_output','12 x 1 = 12\n12 x 2 = 24'))),
('course-cpp', '1.3', 2, 'Two Number Swapper', 'Given two integers A and B, output them swapped separated by a space (i.e. print "B A").', 'Easy', '-10^9 <= A, B <= 10^9', '10 20', '20 10', '#include <iostream>\n\nint main() {\n    long long a, b;\n    if (std::cin >> a >> b) {\n        std::cout << b << " " << a << std::endl;\n    }\n    return 0;\n}', 'import sys\nparts = sys.stdin.read().split()\nif len(parts) >= 2:\n    print(f"{parts[1]} {parts[0]}")', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long a = sc.nextLong();\n            long b = sc.nextLong();\n            System.out.println(b + " " + a);\n        }\n    }\n}', 'const fs = require("fs");\nconst parts = fs.readFileSync(0, "utf-8").trim().split(/\\s+/);\nif (parts.length >= 2) console.log(`${parts[1]} ${parts[0]}`);', JSON_ARRAY(JSON_OBJECT('input','10 20', 'expected_output','20 10'),JSON_OBJECT('input','99 -5', 'expected_output','-5 99'),JSON_OBJECT('input','0 100', 'expected_output','100 0'))),
('course-cpp', '1.3', 3, 'Float Precision Formatter', 'Given a floating point number X, print its integer part and its double value rounded to nearest whole number.', 'Easy', '0 <= X <= 10000', '14.8', '14 15', '#include <iostream>\n#include <cmath>\n\nint main() {\n    double x;\n    if (std::cin >> x) {\n        std::cout << (long long)(x) << " " << (long long)std::round(x) << std::endl;\n    }\n    return 0;\n}', 'import sys\nv = sys.stdin.read().strip()\nif v:\n    x = float(v)\n    print(f"{int(x)} {round(x)}")', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextDouble()) {\n            double x = sc.nextDouble();\n            System.out.println((long)x + " " + Math.round(x));\n        }\n    }\n}', 'const fs = require("fs");\nconst v = fs.readFileSync(0, "utf-8").trim();\nif (v) {\n    const x = parseFloat(v);\n    console.log(`${Math.trunc(x)} ${Math.round(x)}`);\n}', JSON_ARRAY(JSON_OBJECT('input','14.8', 'expected_output','14 15'),JSON_OBJECT('input','7.2', 'expected_output','7 7'),JSON_OBJECT('input','100.5', 'expected_output','100 101'))),
('course-cpp', '1.3', 4, 'Circle Area (Using Integer Pi = 3)', 'Given Radius R, compute area using formula: 3 * R * R.', 'Easy', '1 <= R <= 1000', '10', '300', '#include <iostream>\n\nint main() {\n    long long r;\n    if (std::cin >> r) {\n        std::cout << (3 * r * r) << std::endl;\n    }\n    return 0;\n}', 'import sys\nv = sys.stdin.read().strip()\nif v:\n    r = int(v)\n    print(3 * r * r)', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNextLong()) {\n            long r = sc.nextLong();\n            System.out.println(3 * r * r);\n        }\n    }\n}', 'const fs = require("fs");\nconst v = fs.readFileSync(0, "utf-8").trim();\nif (v) {\n    const r = BigInt(v);\n    console.log((3n * r * r).toString());\n}', JSON_ARRAY(JSON_OBJECT('input','10', 'expected_output','300'),JSON_OBJECT('input','5', 'expected_output','75'),JSON_OBJECT('input','20', 'expected_output','1200'))),
('course-cpp', '1.3', 5, 'ASCII Code Extractor', 'Given a single character C, output its integer ASCII numerical code.', 'Easy', 'Any printable ASCII char', 'A', '65', '#include <iostream>\n\nint main() {\n    char c;\n    if (std::cin >> c) {\n        std::cout << (int)c << std::endl;\n    }\n    return 0;\n}', 'import sys\nv = sys.stdin.read().strip()\nif v:\n    print(ord(v[0]))', 'import java.util.Scanner;\npublic class Main {\n    public static void main(String[] args) {\n        Scanner sc = new Scanner(System.in);\n        if (sc.hasNext()) {\n            char c = sc.next().charAt(0);\n            System.out.println((int)c);\n        }\n    }\n}', 'const fs = require("fs");\nconst v = fs.readFileSync(0, "utf-8").trim();\nif (v) console.log(v.charCodeAt(0).toString());', JSON_ARRAY(JSON_OBJECT('input','A', 'expected_output','65'),JSON_OBJECT('input','Z', 'expected_output','90'),JSON_OBJECT('input','a', 'expected_output','97')));

-- Insert Initial Student Activities
INSERT IGNORE INTO `student_activities` (`student_id`, `title`, `activity_title`, `description`, `score_info`, `score`, `status`, `badge`, `time_text`, `icon`, `type`, `activity_type`) VALUES
('123', 'Completed Programming MCQ', 'Completed Programming MCQ', 'Scored 85% in Modern C++ assessment', 'Score: 85%', 85, 'Passed', '🏆 Passed', 'Today, 09:15 AM', '✓', 'EXAM', 'EXAM'),
('123', 'Attempted Mock Test - 2', 'Attempted Mock Test - 2', 'Completed practice algorithm trial', 'Score: 68%', 68, 'Passed', '✓ Completed', 'Yesterday, 04:30 PM', '✓', 'EXAM', 'EXAM'),
('123', 'Face Verification Completed', 'Face Verification Completed', 'Live biometric face snapshot verified and updated in profile.', 'Verified', 100, 'Verified', '🛡️ Verified', 'Yesterday, 11:20 AM', '🛡️', 'VERIFICATION', 'VERIFICATION');


-- ========================================================
-- DEFAULT SEED DATA (Organizations & Users)
-- ========================================================
INSERT IGNORE INTO `organizations` (`id`, `name`, `code`, `type`, `email`, `phone`, `max_teachers_allowed`, `max_students_allowed`, `max_exams_allowed`, `status`) VALUES
('ORG_NITP', 'National Institute of Technology (NIT Patna)', 'NITP', 'Institute of National Importance', 'admin@nitp.ac.in', '+91 612 237 1715', 100, 8000, 200, 'ACTIVE');

INSERT IGNORE INTO `users` (
    `id`, `student_id`, `full_name`, `email`, `phone`, `dob`, `location`, `college_name`, 
    `course`, `stream`, `batch_years`, `bio`, `goal`, `achievements`, `interests`, 
    `profile_completion_pct`, `exams_enrolled`, `exams_completed`, `upcoming_exams_count`, 
    `average_score`, `best_score`, `current_streak_days`, `password`, `role`, `access_code`, 
    `avatar_url`, `status`, `org_id`, `created_by_principal_id`, `created_by_teacher_id`, 
    `designation`, `department`, `max_students_allowed`, `max_exams_allowed`,
    `can_create_exams`, `can_set_questions`, `can_manage_lessons`, `can_manage_courses`, `can_enroll_students`, `can_view_results`
) VALUES
('NITP_PRIN_01', 'NITP_PRIN_01', 'Prof. Pradeep Kumar', 'principal@nitp.ac.in', '+91 94310 12345', '15 Aug 1968', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'Institutional Administration', 'Computer Science & Engineering', 'Faculty', 'Director & Principal overseeing national examination standards, AI proctoring compliance, and placement credentials.', 'Excellence in National Technical Education', 'Published 40+ IEEE papers', 'AI, Cloud Architecture, Higher Education', 100, 0, 0, 0, 100, 100, 30, 'password123', 'PRINCIPAL', '888888', NULL, 'ACTIVE', 'ORG_NITP', NULL, NULL, 'Director & Head of Institution', 'Administration', 8000, 200, 1, 1, 1, 1, 1, 1),
('TEACH_001', 'FAC-CS-01', 'Prof. Rajesh Sharma', 'teacher@nitp.ac.in', '+91 98765 43210', '20 May 1980', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'Faculty & Assessment Operations', 'Computer Science & Engineering', 'Faculty', 'Senior Professor specializing in Algorithms, Distributed Systems, and Database Systems.', 'Empowering engineering students with core technical excellence', 'Best Faculty Award 2024', 'Algorithms, Cloud, DBMS', 95, 0, 0, 0, 95, 95, 20, 'password123', 'PROCTOR', '888888', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', NULL, 'Associate Professor & HOD', 'Computer Science & Engineering', 200, 20, 1, 1, 1, 1, 1, 1),
('TEACH_002', 'FAC-EC-02', 'Dr. Ananya Verma', 'ananya@nitp.ac.in', '+91 98765 43211', '12 Jan 1985', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'Faculty & Assessment Operations', 'Electronics & Communication', 'Faculty', 'Assistant Professor in Embedded Systems and Microprocessors.', 'Bridging hardware architecture with modern software compilers', 'Gold Medalist in M.Tech', 'IoT, VLSI, Embedded C', 90, 0, 0, 0, 90, 90, 15, 'password123', 'PROCTOR', '888888', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', NULL, 'Assistant Professor', 'Electronics & Communication', 150, 15, 1, 1, 1, 0, 1, 1),
('TEACH_003', 'FAC-IT-03', 'Prof. Vikramaditya', 'vikram@nitp.ac.in', '+91 98765 43212', '05 Nov 1982', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'Faculty & Assessment Operations', 'Information Technology', 'Faculty', 'Associate Professor in Web Security, Cloud Systems, and Full-Stack Engineering.', 'Mentoring top campus placement selections across Tier-1 tech giants', 'Authored Full-Stack Guidebook', 'Full Stack, DevOps, Cyber Security', 92, 0, 0, 0, 92, 92, 18, 'password123', 'PROCTOR', '888888', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', NULL, 'Associate Professor', 'Information Technology', 150, 15, 1, 1, 1, 0, 1, 1),
('CAND123456', '2201CS01', 'Aarav Kumar', 'aarav@nitp.ac.in', '+91 91234 56789', '12 Jan 2003', 'Bihar, India', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'Final year CSE undergraduate passionate about full-stack engineering, distributed systems, and competitive programming.', 'Secure SDE role in Tier-1 Technology Firm', 'Rank 1 in College Hackathon 2025; Solved 450+ LeetCode problems', 'Data Structures, AI, Cloud Architecture', 85, 12, 5, 3, 72, 95, 7, 'password123', 'CANDIDATE', '123456', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0, 0, 0, 0, 0, 0, 0),
('CAND123457', '2201CS02', 'Priya Sharma', 'priya@nitp.ac.in', '+91 91234 56790', '08 Mar 2003', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'CSE Student passionate about AI/ML & Web Development.', 'Software Engineer at top Tech company', 'Finalist at Smart India Hackathon', 'Machine Learning, Python, Web Apps', 90, 10, 4, 2, 85, 98, 12, 'password123', 'CANDIDATE', '123456', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0, 0, 0, 0, 0, 0, 0),
('CAND123458', '2201CS03', 'Rohan Gupta', 'rohan@nitp.ac.in', '+91 91234 56791', '14 Jul 2002', 'Gaya, Bihar', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'Competitive Programmer & Backend Developer.', 'Crack Google/Amazon SDE Interviews', 'Codeforces Candidate Master', 'C++, Algorithms, System Design', 88, 11, 5, 2, 88, 96, 15, 'password123', 'CANDIDATE', '123456', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0, 0, 0, 0, 0, 0, 0),
('CAND123459', '2201CS04', 'Sneha Patel', 'sneha@nitp.ac.in', '+91 91234 56792', '22 Sep 2003', 'Patna, Bihar', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'Frontend developer & UI/UX enthusiast.', 'Lead UI/UX Engineer', 'Design Lead at College Tech Fest', 'React, CSS, Modern UI, TypeScript', 82, 9, 3, 2, 78, 92, 5, 'password123', 'CANDIDATE', '123456', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0, 0, 0, 0, 0, 0, 0),
('CAND123460', '2201CS05', 'Amit Singh', 'amit@nitp.ac.in', '+91 91234 56793', '03 Feb 2003', 'Muzaffarpur, Bihar', 'National Institute of Technology (NIT Patna)', 'B.Tech', 'Computer Science & Engineering', '2022 - 2026', 'Cloud and DevOps Enthusiast.', 'Cloud Architect Certification', 'AWS Certified Cloud Practitioner', 'Docker, Kubernetes, AWS, Go', 80, 8, 3, 2, 75, 90, 6, 'password123', 'CANDIDATE', '123456', NULL, 'ACTIVE', 'ORG_NITP', 'NITP_PRIN_01', 'TEACH_001', 'Student Candidate', 'Computer Science & Engineering', 0, 0, 0, 0, 0, 0, 0, 0);


SET FOREIGN_KEY_CHECKS = 1;
