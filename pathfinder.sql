-- =======================================================
-- PathFinder – Sri Lankan Graduate Career Community Database
-- Database: pathfinder_db
-- Compatibility: MySQL 5.7+ / MariaDB 10.4+ / PHP 8+
-- =======================================================

CREATE DATABASE IF NOT EXISTS `pathfinder_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `pathfinder_db`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `story_views`;
DROP TABLE IF EXISTS `bookmarks`;
DROP TABLE IF EXISTS `mentorship_requests`;
DROP TABLE IF EXISTS `story_tags`;
DROP TABLE IF EXISTS `career_timeline`;
DROP TABLE IF EXISTS `career_stories`;
DROP TABLE IF EXISTS `graduate_skills`;
DROP TABLE IF EXISTS `skills`;
DROP TABLE IF EXISTS `graduate_profiles`;
DROP TABLE IF EXISTS `student_profiles`;
DROP TABLE IF EXISTS `degrees`;
DROP TABLE IF EXISTS `industries`;
DROP TABLE IF EXISTS `universities`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- --------------------------------------------------------
-- Table: users
-- --------------------------------------------------------
CREATE TABLE `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `full_name` VARCHAR(120) NOT NULL,
  `email` VARCHAR(150) NOT NULL UNIQUE,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` ENUM('student', 'graduate', 'admin') NOT NULL DEFAULT 'student',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `avatar_url` VARCHAR(255) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_users_email (`email`),
  INDEX idx_users_role (`role`),
  INDEX idx_users_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: universities
-- --------------------------------------------------------
CREATE TABLE `universities` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `short_name` VARCHAR(50) NOT NULL,
  `location` VARCHAR(100) DEFAULT NULL,
  `website` VARCHAR(200) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: degrees
-- --------------------------------------------------------
CREATE TABLE `degrees` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `university_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `field` VARCHAR(100) NOT NULL,
  `degree_type` VARCHAR(50) DEFAULT 'BSc (Hons)',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: industries
-- --------------------------------------------------------
CREATE TABLE `industries` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `slug` VARCHAR(100) NOT NULL UNIQUE,
  `description` TEXT DEFAULT NULL,
  `icon_class` VARCHAR(80) DEFAULT 'fa-solid fa-briefcase',
  `display_order` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: student_profiles
-- --------------------------------------------------------
CREATE TABLE `student_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `university_id` INT DEFAULT NULL,
  `degree_id` INT DEFAULT NULL,
  `current_year` VARCHAR(50) DEFAULT '3rd Year',
  `bio` TEXT DEFAULT NULL,
  `target_industry` VARCHAR(100) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`degree_id`) REFERENCES `degrees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: graduate_profiles
-- --------------------------------------------------------
CREATE TABLE `graduate_profiles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL UNIQUE,
  `university_id` INT DEFAULT NULL,
  `degree_id` INT DEFAULT NULL,
  `graduation_year` INT DEFAULT 2022,
  `current_job_title` VARCHAR(150) NOT NULL,
  `company` VARCHAR(150) NOT NULL,
  `industry_id` INT DEFAULT NULL,
  `linkedin_url` VARCHAR(255) DEFAULT NULL,
  `is_mentor_available` TINYINT(1) NOT NULL DEFAULT 1,
  `mentor_badge` VARCHAR(50) DEFAULT 'Open to Chat',
  `mentor_headline` VARCHAR(255) DEFAULT NULL,
  `bio` TEXT DEFAULT NULL,
  `years_experience` INT DEFAULT 2,
  `rating` DECIMAL(3,1) DEFAULT 4.9,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`university_id`) REFERENCES `universities`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`degree_id`) REFERENCES `degrees`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`industry_id`) REFERENCES `industries`(`id`) ON DELETE SET NULL,
  INDEX idx_grad_mentor (`is_mentor_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: skills
-- --------------------------------------------------------
CREATE TABLE `skills` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(80) NOT NULL UNIQUE,
  `slug` VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: graduate_skills
-- --------------------------------------------------------
CREATE TABLE `graduate_skills` (
  `graduate_id` INT NOT NULL,
  `skill_id` INT NOT NULL,
  PRIMARY KEY (`graduate_id`, `skill_id`),
  FOREIGN KEY (`graduate_id`) REFERENCES `graduate_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`skill_id`) REFERENCES `skills`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: career_stories
-- --------------------------------------------------------
CREATE TABLE `career_stories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `graduate_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `excerpt` TEXT NOT NULL,
  `summary` TEXT NOT NULL,
  `first_job` VARCHAR(150) DEFAULT NULL,
  `current_job` VARCHAR(150) NOT NULL,
  `company` VARCHAR(150) NOT NULL,
  `industry_id` INT DEFAULT NULL,
  `challenges` TEXT DEFAULT NULL,
  `interview_experience` TEXT DEFAULT NULL,
  `important_skills` TEXT DEFAULT NULL,
  `student_advice` TEXT DEFAULT NULL,
  `status` ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'approved',
  `is_featured` TINYINT(1) NOT NULL DEFAULT 0,
  `views_count` INT NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`graduate_id`) REFERENCES `graduate_profiles`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`industry_id`) REFERENCES `industries`(`id`) ON DELETE SET NULL,
  INDEX idx_story_status (`status`),
  INDEX idx_story_featured (`is_featured`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: career_timeline
-- --------------------------------------------------------
CREATE TABLE `career_timeline` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `story_id` INT NOT NULL,
  `year` VARCHAR(50) NOT NULL,
  `title` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `display_order` INT DEFAULT 0,
  FOREIGN KEY (`story_id`) REFERENCES `career_stories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: story_tags
-- --------------------------------------------------------
CREATE TABLE `story_tags` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `story_id` INT NOT NULL,
  `tag_name` VARCHAR(60) NOT NULL,
  FOREIGN KEY (`story_id`) REFERENCES `career_stories`(`id`) ON DELETE CASCADE,
  INDEX idx_tag_name (`tag_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: bookmarks
-- --------------------------------------------------------
CREATE TABLE `bookmarks` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `story_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_user_story` (`user_id`, `story_id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`story_id`) REFERENCES `career_stories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: mentorship_requests
-- --------------------------------------------------------
CREATE TABLE `mentorship_requests` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `student_id` INT NOT NULL,
  `mentor_id` INT NOT NULL,
  `topic` VARCHAR(150) NOT NULL,
  `message` TEXT NOT NULL,
  `preferred_communication` ENUM('Google Meet', 'WhatsApp', 'Email', 'Zoom') NOT NULL DEFAULT 'Google Meet',
  `status` ENUM('pending', 'accepted', 'rejected', 'completed') NOT NULL DEFAULT 'pending',
  `mentor_notes` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`student_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`mentor_id`) REFERENCES `graduate_profiles`(`id`) ON DELETE CASCADE,
  INDEX idx_req_status (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Table: story_views
-- --------------------------------------------------------
CREATE TABLE `story_views` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `story_id` INT NOT NULL,
  `ip_address` VARCHAR(45) NOT NULL,
  `viewed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`story_id`) REFERENCES `career_stories`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- SEED DATA
-- =======================================================

-- 1. Insert Universities
INSERT INTO `universities` (`id`, `name`, `short_name`, `location`, `website`) VALUES
(1, 'University of Moratuwa', 'UoM (Moratuwa)', 'Katubedda, Moratuwa', 'https://uom.lk'),
(2, 'University of Colombo', 'Colombo UOC', 'Colombo 03', 'https://cmb.ac.lk'),
(3, 'University of Peradeniya', 'Peradeniya', 'Peradeniya, Kandy', 'https://pdn.ac.lk'),
(4, 'Sri Lanka Institute of Information Technology', 'SLIIT', 'Malabe', 'https://sliit.lk'),
(5, 'University of Sri Jayewardenepura', 'USJ', 'Nugegoda', 'https://sjp.ac.lk'),
(6, 'University of Kelaniya', 'Kelaniya', 'Kelaniya', 'https://kln.ac.lk'),
(7, 'CINEC Campus', 'CINEC Campus', 'Malabe', 'https://cinec.edu');

-- 2. Insert Degrees
INSERT INTO `degrees` (`id`, `university_id`, `name`, `field`, `degree_type`) VALUES
(1, 1, 'BSc (Hons) in Information Technology', 'Information Technology', 'BSc (Hons)'),
(2, 1, 'BSc (Hons) in Computer Science & Engineering', 'Engineering', 'BSc Eng (Hons)'),
(3, 1, 'BSc (Hons) in Electronic & Telecommunication Eng', 'Engineering', 'BSc Eng (Hons)'),
(4, 2, 'BSc in Computer Science (UCSC)', 'Computing', 'BSc (Hons)'),
(5, 2, 'BBA in Finance & Business Economics', 'Management', 'BBA (Hons)'),
(6, 3, 'BSc in Electrical & Electronic Engineering', 'Engineering', 'BSc Eng (Hons)'),
(7, 3, 'BSc in Computer Engineering', 'Computing', 'BSc Eng (Hons)'),
(8, 4, 'BSc (Hons) in Software Engineering', 'Software Engineering', 'BSc (Hons)'),
(9, 4, 'BSc (Hons) in Cyber Security', 'Information Technology', 'BSc (Hons)'),
(10, 5, 'BSc (Hons) in Business Information Systems', 'Management & IT', 'BSc (Hons)'),
(11, 5, 'BSc (Hons) in Marketing Management', 'Marketing', 'BSc (Hons)'),
(12, 6, 'BSc (Hons) in Software Engineering', 'Computing', 'BSc (Hons)'),
(13, 7, 'BSc in Logistics & Supply Chain Management', 'Supply Chain', 'BSc (Hons)');

-- 3. Insert Industries
INSERT INTO `industries` (`id`, `name`, `slug`, `description`, `icon_class`, `display_order`) VALUES
(1, 'Software Engineering', 'software-engineering', 'Full stack, backend, frontend, systems and mobile engineering career paths.', 'fa-solid fa-code', 1),
(2, 'Data Science & AI', 'data-science-ai', 'Machine learning, predictive analytics, NLP, and AI research in enterprise.', 'fa-solid fa-chart-line', 2),
(3, 'Digital Marketing & Brand', 'digital-marketing-brand', 'Performance marketing, social media growth, SEO, and brand storytelling.', 'fa-solid fa-bullhorn', 3),
(4, 'Apparel & Supply Chain', 'apparel-supply-chain', 'Merchandising, global logistics, production planning in Sri Lanka’s top apparel giants.', 'fa-solid fa-truck-fast', 4),
(5, 'FinTech & Banking', 'fintech-banking', 'Algorithmic trading, payments infrastructure, corporate finance, and risk models.', 'fa-solid fa-building-columns', 5),
(6, 'Biotech & Healthcare', 'biotech-healthcare', 'Clinical research, biomedical technology, diagnostics, and life sciences.', 'fa-solid fa-flask', 6),
(7, 'Cyber Security', 'cyber-security', 'SOC operations, penetration testing, DevSecOps, and cloud security governance.', 'fa-solid fa-shield-halved', 7),
(8, 'UI/UX Design', 'ui-ux-design', 'Design systems, user research, wireframing, and product design leadership.', 'fa-solid fa-pen-nib', 8),
(9, 'Business & Management', 'business-management', 'Product management, management consulting, and startup operations.', 'fa-solid fa-briefcase', 9),
(10, 'Engineering', 'engineering', 'Electrical, civil, mechanical, and industrial robotics engineering.', 'fa-solid fa-gears', 10);

-- 4. Insert Skills
INSERT INTO `skills` (`id`, `name`, `slug`) VALUES
(1, 'React', 'react'),
(2, 'Spring Boot', 'spring-boot'),
(3, 'Data Structures & Algorithms', 'dsa'),
(4, 'AWS', 'aws'),
(5, 'Cloud Architecture', 'cloud-architecture'),
(6, 'Figma & UI Design', 'figma'),
(7, 'Product Management', 'product-management'),
(8, 'Agile & Scrum', 'agile'),
(9, 'Supply Chain Logistics', 'supply-chain'),
(10, 'Financial Modeling', 'financial-modeling'),
(11, 'Python & ML', 'python-ml'),
(12, 'Docker & Kubernetes', 'docker-k8s'),
(13, 'System Design', 'system-design'),
(14, 'SEO & Performance Ads', 'seo-ads');

-- 5. Insert Users (Password is Password123! for all demo accounts)
-- Hash: $2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6
INSERT INTO `users` (`id`, `full_name`, `email`, `password_hash`, `role`, `status`, `avatar_url`) VALUES
(1, 'PathFinder Administrator', 'admin@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'admin', 'active', 'assets/images/admin-avatar.png'),
(2, 'Kavindi Perera', 'kavindi@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/kavindi.jpg'),
(3, 'Dinuk Wijesinghe', 'dinuk@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/dinuk.jpg'),
(4, 'Thilini Silva', 'thilini@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/thilini.jpg'),
(5, 'Janith Rathnayake', 'janith@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/janith.jpg'),
(6, 'Nilukshi Fernando', 'nilukshi@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/nilukshi.jpg'),
(7, 'Akeel Mansoor', 'akeel@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/akeel.jpg'),
(8, 'Generic Graduate Mentor', 'graduate@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'graduate', 'active', 'assets/images/default-avatar.png'),
(9, 'Malith Perera', 'student@pathfinder.lk', '$2y$10$T3SvSS9wlCFKxG2JE.JKluMAzbaJITwr96LdpnH0LUltPVzf1c2a6', 'student', 'active', 'assets/images/student-avatar.png');

-- 6. Insert Student Profiles
INSERT INTO `student_profiles` (`id`, `user_id`, `university_id`, `degree_id`, `current_year`, `bio`, `target_industry`) VALUES
(1, 9, 4, 8, '3rd Year Undergraduate', 'Passionate software engineering undergraduate seeking guidance for upcoming 6-month industrial internship placement in Colombo.', 'Software Engineering');

-- 7. Insert Graduate Profiles
INSERT INTO `graduate_profiles` (`id`, `user_id`, `university_id`, `degree_id`, `graduation_year`, `current_job_title`, `company`, `industry_id`, `linkedin_url`, `is_mentor_available`, `mentor_badge`, `mentor_headline`, `bio`, `years_experience`, `rating`) VALUES
(1, 2, 1, 1, 2021, 'Senior UX Designer', 'Sysco LABS', 8, 'https://linkedin.com/in/kavindi-perera-demo', 1, 'Open to Chat', 'From campus hackathons to leading design sprints across global enterprise platforms.', 'From campus hackathons to leading design sprints across global enterprise platforms. Happy to share how to build an industry-ready portfolio and break into product design.', 3, 5.0),
(2, 3, 2, 5, 2022, 'Strategy Associate', 'MAS Holdings', 4, 'https://linkedin.com/in/dinuk-wijesinghe-demo', 1, 'STRATEGY', 'Transitioning from finance major to high-velocity apparel supply chain operations.', 'Transitioning from finance major to high-velocity apparel supply chain operations took 6 months of targeted unlearning. Here to guide undergraduates interested in strategic operations.', 2, 4.8),
(3, 4, 4, 8, 2020, 'Cloud Architect', 'Global FinTech', 1, 'https://linkedin.com/in/thilini-silva-demo', 1, 'Open to Chat', 'Self-taught AWS during 3rd year internship after failing my first two code screenings.', 'Self-taught AWS during 3rd year internship after failing my first two code screenings. Here is what finally clicked. Specializing in high-availability cloud systems.', 4, 4.9),
(4, 5, 3, 6, 2022, 'Associate Software Engineer', 'IFS Sri Lanka', 1, 'https://linkedin.com/in/janith-rathnayake-demo', 1, 'Open to Chat', 'Cracked 14 technical interviews with consistent DSA preparation.', 'Peradeniya graduate who navigated Colombo IT recruitment with an average GPA and zero connections. Passionate about helping students master algorithm problem-solving and Java/Spring ecosystem.', 2, 4.9),
(5, 6, 1, 2, 2021, 'Solutions Architect', 'WSO2', 1, 'https://linkedin.com/in/nilukshi-fernando-demo', 1, 'CV Teardown', 'From rural school background to Lead Solutions Architect at global open-source leader.', 'English was not my first language, and client calls used to terrify me. I break down how joining open-source docs communities gave me confidence and built my career foundation.', 3, 5.0),
(6, 7, 4, 8, 2023, 'Product Manager', 'PickMe', 9, 'https://linkedin.com/in/akeel-mansoor-demo', 1, 'Open to Chat', 'Transitioned from technical engineering into fast-paced ride-hailing product growth.', 'Everyone told me product management is only for senior developers or elite MBA graduates. Here is how building one side app landed me a role at Sri Lanka’s leading super-app.', 1, 4.9),
(7, 8, 1, 1, 2020, 'Lead Systems Engineer', 'London Stock Exchange Group (LSEG)', 5, 'https://linkedin.com/in/mentor-demo', 1, 'Open to Chat', 'FinTech infrastructure and financial exchange software specialist.', 'Mentoring Sri Lankan undergraduates on software engineering and financial engineering careers since 2021.', 4, 4.9);

-- 8. Insert Graduate Skills
INSERT INTO `graduate_skills` (`graduate_id`, `skill_id`) VALUES
(1, 6), (1, 7), (1, 8),
(2, 9), (2, 10), (2, 8),
(3, 4), (3, 5), (3, 12),
(4, 1), (4, 2), (4, 3),
(5, 4), (5, 5), (5, 13),
(6, 7), (6, 8), (6, 1),
(7, 5), (7, 10), (7, 13);

-- 9. Insert Career Stories
INSERT INTO `career_stories` (`id`, `graduate_id`, `title`, `excerpt`, `summary`, `first_job`, `current_job`, `company`, `industry_id`, `challenges`, `interview_experience`, `important_skills`, `student_advice`, `status`, `is_featured`, `views_count`) VALUES
(1, 4, 'How I tackled 14 coding interviews before landing an Associate SE role', 
'I had an average GPA and zero family connections in the Colombo tech circuit. Here is the exact 4-month LeetCode and Spring Boot regimen that broke the rejection cycle.',
'Coming out of University of Peradeniya, my academic focus was primarily embedded systems and electronics, but my career aspiration was high-scale enterprise software development in Colombo. With an average GPA of 3.02, my resume was screened out by three initial top-tier tech companies. Instead of getting demoralized, I reverse-engineered the standard Sri Lankan tech hiring rubric into three manageable pillars: Data Structures, System Fundamentals, and behavioral communication.',
'Software Engineering Intern at 99x',
'Associate Software Engineer',
'IFS Sri Lanka',
1,
'Facing 14 consecutive rejections across technical phone screens and take-home assignments was brutal. The biggest challenge was bridging the gap between theoretical classroom knowledge and production-grade software development. I had to learn Docker, Spring Security, and clean architecture completely on my own late at night.',
'IFS conducted 3 rigorous rounds: 1) Online Hackerrank screening with 2 algorithmic challenges and 10 SQL questions. 2) 90-minute live coding session where I was asked to optimize an in-memory caching system and discuss concurrency. 3) Culture fit and architectural mindset interview with an Engineering Director.',
'Java, Spring Boot, React, SQL Optimization, Redis, Git workflows, RESTful API architecture, and algorithmic complexity analysis (Big-O notation).',
'Do not let your GPA define your ceiling. Build at least two complete full-stack projects that solve a real local problem and deploy them to live domains with CI/CD. When interviewers see that you can ship code to production, GPA questions disappear completely.',
'approved', 1, 1420),

(2, 5, 'From rural school background to Lead Solutions Architect: Finding my voice in Tech',
'English was not my first language, and client calls used to terrify me. I break down how joining open-source docs communities gave me confidence and built my career foundation.',
'Growing up in a rural village outside Kurunegala and attending a local Sinhala-medium school, entering the University of Moratuwa was a massive cultural shock. While I could grasp algorithms and math easily, speaking fluently in English during presentations and technical group discussions felt paralyzing. During my 3rd year, I realized technical brilliance without communication would trap me in junior roles. I started by writing documentation for Apache and WSO2 open source projects.',
'Trainee Software Engineer at WSO2',
'Solutions Architect',
'WSO2',
1,
'Language anxiety and imposter syndrome were my biggest hurdles. In my first month as an intern, I could barely bring myself to speak up in daily standup calls with US clients. I spent months practicing spoken technical communication in front of a mirror and transcribing architecture talks.',
'WSO2’s interview involved an extensive code review of my GitHub open-source contributions, followed by a whiteboard scenario where I had to design a distributed API gateway with OAuth2 rate-limiting.',
'Cloud Architecture, Microservices, Kubernetes, OpenID Connect / OAuth2, Technical Writing, Enterprise Integration Patterns, and Public Speaking.',
'Contribute to open-source software! Even fixing spelling mistakes in developer documentation or writing quick start tutorials will get your name noticed by senior engineers and hiring managers. It proves both your curiosity and your communication ability.',
'approved', 1, 2310),

(3, 6, 'Transitioned from mechanical interest to Product Manager in 8 months',
'Everyone told me product management is only for senior developers or elite MBA graduates. Here is how building one side app landed me a role at PickMe.',
'During my time at SLIIT, I was captivated by how software products actually made business decisions. While my friends were optimizing algorithms, I found myself obsessing over user drop-off rates, UI friction, and how ride-hailing drivers interacted with phones on Colombo streets. After being rejected for traditional product roles due to lack of experience, I teamed up with two batchmates to build a hyper-local bus route and fare tracking app.',
'Associate Product Specialist at PickMe',
'Product Manager',
'PickMe',
9,
'The prevailing myth in Sri Lanka is that Product Management is strictly for 30+ year-old professionals with MBA degrees. Convincing hiring teams that an energetic undergraduate understands customer discovery and product roadmapping was an uphill battle.',
'PickMe’s process was deeply practical: I was given a 48-hour challenge to diagnose why driver onboarding drop-offs increased by 14% on Android devices in Kandy, and present my wireframes and sprint plan to the VP of Product.',
'Product Analytics (Mixpanel/PostHog), User Journey Mapping, Wireframing (Figma), Agile Sprint Planning, SQL for Product Metrics, and Empathetic Stakeholder Communication.',
'If nobody will hire you as a Product Manager, become the product manager of your own student projects. Write product requirement documents (PRDs), run user interviews with 20 real students, calculate churn, and bring that portfolio to your interview.',
'approved', 1, 1850),

(4, 1, 'Designing for Global Scale: My Journey from Katubedda to Senior UX at Sysco LABS',
'How hackathons, rapid prototyping, and user psychology helped me transition from a general IT degree to lead product designer.',
'At University of Moratuwa (BSc IT), I discovered that while I understood code, my true passion lay at the intersection of human psychology and digital interfaces. Participating in national hackathons taught me how to conceptualize and pitch user experiences in high-pressure environments.',
'Associate UI/UX Engineer at Virtusa',
'Senior UX Designer',
'Sysco LABS',
8,
'Lack of dedicated UX design degrees in local universities meant I had to curate my own design curriculum through Nielsen Norman Group research, case study teardowns, and design critique groups.',
'Sysco LABS required an in-depth portfolio review where I walked the design panel through my end-to-end design thinking process for a restaurant supply chain inventory system.',
'Figma, Design Systems, User Research, Usability Testing, Heuristic Evaluation, Design Sprints.',
'Never present just pretty UI mockups in your portfolio. Senior design managers care 10x more about your problem discovery, edge cases, user friction analysis, and business impact.',
'approved', 1, 980),

(5, 2, 'Navigating High-Velocity Apparel Supply Chain Strategy at MAS Holdings',
'Transitioning from a pure finance major to strategic apparel supply chain operations took 6 months of targeted unlearning.',
'Studying BBA Finance at University of Colombo gave me financial acumen, but entering Sri Lanka’s multi-billion dollar apparel export industry required understanding lean manufacturing, agile supply webs, and international logistics.',
'Management Trainee at MAS Holdings',
'Strategy Associate',
'MAS Holdings',
4,
'Adapting to 24/7 global supply chain volatility and learning the intricate terminology of fabric manufacturing and vendor compliance.',
'A 4-stage management trainee assessment center including group case presentations, numerical reasoning, and executive director interviews.',
'Lean Manufacturing, Supply Chain Analytics, Strategic Sourcing, Power BI, Advanced Excel, Negotiation.',
'Sri Lankan undergraduates should explore beyond tech; our export apparel giants offer world-class career trajectories that rival global multinationals.',
'approved', 1, 750),

(6, 3, 'Demystifying Cloud Architecture: From Failed Screenings to Cloud Architect at FinTech',
'Failing two coding screenings in 3rd year was the wake-up call I needed to master cloud automation and resilient DevOps.',
'At SLIIT Software Engineering, I struggled with competitive programming rounds. When I discovered cloud infrastructure and infrastructure-as-code, everything shifted. I focused on AWS certified architect pathways and built serverless architectures.',
'Cloud Operations Intern at MillenniumIT ESP',
'Cloud Architect',
'Global FinTech',
1,
'Self-funding cloud lab environments on a student budget and understanding distributed systems reliability without access to enterprise infrastructure.',
'Live architecture defense session where I was asked to redesign a high-frequency payment gateway to withstand multi-region outages with zero data loss.',
'AWS Solutions Architecture, Terraform, Kubernetes, FinOps, CI/CD pipelines, Zero Trust Security.',
'Get certified early, but more importantly, build real architectures. Use the AWS free tier to deploy multi-tier containerized services.',
'approved', 1, 1120);

-- 10. Insert Career Timelines
INSERT INTO `career_timeline` (`story_id`, `year`, `title`, `description`, `display_order`) VALUES
(1, '2018', 'Enrolled in University of Peradeniya', 'Started undergraduate engineering degree with enthusiasm for software systems.', 1),
(1, '2020', 'First Wave of Rejections', 'Faced repeated rejection from Colombo tech internships due to lack of practical coding experience.', 2),
(1, '2021', 'The 4-Month LeetCode Sprint', 'Solved 250+ algorithm problems and built 2 production-ready Spring Boot microservices.', 3),
(1, '2021', 'Software Engineering Intern @ 99x', 'Completed high-performing 6-month internship working on Nordic cloud products.', 4),
(1, '2022', 'Graduated with BSc Eng (Hons)', 'Successfully completed degree and joined IFS as Associate Software Engineer.', 5),
(1, '2024', 'Leading Core Feature Releases', 'Mentoring junior recruits and driving backend modernization initiatives.', 6),

(2, '2017', 'Entered University of Moratuwa', 'Studied Computer Science & Engineering, initially struggled with spoken English.', 1),
(2, '2019', 'First Open-Source PR Merged', 'Wrote documentation for Apache Synapse and fixed core module unit tests.', 2),
(2, '2020', 'Trainee Software Engineer @ WSO2', 'Joined WSO2 identity and integration team for university industrial placement.', 3),
(2, '2021', 'Promoted to Software Engineer', 'Delivered mission-critical cloud integrations for Fortune 500 customers.', 4),
(2, '2023', 'Stepped into Solutions Architecture', 'Designing enterprise integration architectures across North America and Europe.', 5),

(3, '2019', 'BSc Software Engineering @ SLIIT', 'Developed passion for consumer product metrics and UX behavioral psychology.', 1),
(3, '2021', 'Built Local Transit Prototype', 'Created transit tracking app tested with 1,200 active university students.', 2),
(3, '2022', 'Joined PickMe as Associate PM', 'Worked on driver onboarding optimization and fare calculation algorithms.', 3),
(3, '2024', 'Product Manager @ PickMe', 'Owning key features in Sri Lanka’s premier ride-hailing and food delivery ecosystem.', 4);

-- 11. Insert Story Tags
INSERT INTO `story_tags` (`story_id`, `tag_name`) VALUES
(1, 'React'), (1, 'Spring'), (1, 'DSA'), (1, 'IFS'),
(2, 'Cloud'), (2, 'Architecture'), (2, 'Internship'), (2, 'WSO2'),
(3, 'ProductManagement'), (3, 'Agile'), (3, 'Startups'), (3, 'PickMe'),
(4, 'UIUX'), (4, 'Figma'), (4, 'SyscoLABS'),
(5, 'SupplyChain'), (5, 'MASHoldings'), (5, 'Strategy'),
(6, 'DevOps'), (6, 'AWS'), (6, 'FinTech');

-- 12. Insert Bookmarks
INSERT INTO `bookmarks` (`user_id`, `story_id`) VALUES
(9, 1),
(9, 2);

-- 13. Insert Mentorship Requests
INSERT INTO `mentorship_requests` (`student_id`, `mentor_id`, `topic`, `message`, `preferred_communication`, `status`, `mentor_notes`) VALUES
(9, 4, 'DSA & Spring Boot Technical Interview Guidance', 'Hi Janith! I am a 3rd year student at SLIIT preparing for my internship interviews next month. I read your story about breaking the rejection cycle and would love 20 minutes of your time to review my GitHub profile and discuss what IFS looks for in associate engineers.', 'Google Meet', 'accepted', 'Glad to help! Let us connect on Google Meet this Saturday at 4:00 PM. Have your GitHub link and resume ready.'),
(9, 1, 'UX Portfolio Review for Tech Internships', 'Hello Kavindi, I am building my design case study for local tech internships. Could you give me high-level feedback on my wireframes?', 'Email', 'pending', NULL);
