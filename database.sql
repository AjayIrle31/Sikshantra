-- Shikshantra - Complete Database Schema
-- Updated to include class-based filtering and chat system
-- This schema includes all features currently used by the application

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100),
  email VARCHAR(100) UNIQUE,
  password VARCHAR(255),
  role ENUM('admin','teacher','student') NOT NULL
);

-- Classes system
CREATE TABLE IF NOT EXISTS classes (
  class_id INT AUTO_INCREMENT PRIMARY KEY,
  class_name VARCHAR(100) NOT NULL,
  class_code VARCHAR(20) UNIQUE,
  description TEXT,
  teacher_id INT,
  max_students INT DEFAULT 30,
  is_active BOOLEAN DEFAULT TRUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS class_members (
  id INT AUTO_INCREMENT PRIMARY KEY,
  class_id INT,
  student_id INT,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_membership (class_id, student_id),
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS assignments (
  assignment_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  description TEXT,
  file_path VARCHAR(255),
  uploaded_by INT,
  class_id INT NULL,
  upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS submissions (
  submission_id INT AUTO_INCREMENT PRIMARY KEY,
  assignment_id INT,
  student_id INT,
  file_path VARCHAR(255),
  submitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_sub (assignment_id, student_id),
  FOREIGN KEY (assignment_id) REFERENCES assignments(assignment_id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS timetable (
  timetable_id INT AUTO_INCREMENT PRIMARY KEY,
  day VARCHAR(20),
  time_slot VARCHAR(50),
  subject VARCHAR(100),
  teacher_id INT,
  class_id INT NULL,
  FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS feedback (
  feedback_id INT AUTO_INCREMENT PRIMARY KEY,
  subject VARCHAR(100),
  student_id INT,
  teacher_id INT,
  feedback_text TEXT,
  submitted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (teacher_id) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS study_material (
  material_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  file_path VARCHAR(255),
  uploaded_by INT,
  class_id INT NULL,
  uploaded_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (uploaded_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS quizzes (
  quiz_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  created_by INT,
  class_id INT NULL,
  created_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS quiz_questions (
  question_id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  question_text TEXT,
  option_a VARCHAR(255),
  option_b VARCHAR(255),
  option_c VARCHAR(255),
  option_d VARCHAR(255),
  correct_option CHAR(1),
  FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_results (
  result_id INT AUTO_INCREMENT PRIMARY KEY,
  quiz_id INT,
  student_id INT,
  score INT,
  attempted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (quiz_id) REFERENCES quizzes(quiz_id) ON DELETE CASCADE,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS attendance (
  attendance_id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT,
  class_id INT NULL,
  date DATE,
  status ENUM('present','absent'),
  marked_by INT,
  FOREIGN KEY (student_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL,
  FOREIGN KEY (marked_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS notices (
  notice_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  message TEXT,
  posted_by INT,
  class_id INT NULL,
  posted_on TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (posted_by) REFERENCES users(user_id) ON DELETE SET NULL,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE SET NULL
);

-- Chat system
CREATE TABLE IF NOT EXISTS chat_rooms (
  room_id INT AUTO_INCREMENT PRIMARY KEY,
  room_type ENUM('private', 'class') DEFAULT 'private',
  class_id INT NULL,
  created_by INT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (class_id) REFERENCES classes(class_id) ON DELETE CASCADE,
  FOREIGN KEY (created_by) REFERENCES users(user_id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS chat_participants (
  id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT,
  user_id INT,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES chat_rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS chat_messages (
  message_id INT AUTO_INCREMENT PRIMARY KEY,
  room_id INT,
  sender_id INT,
  message TEXT,
  is_read BOOLEAN DEFAULT FALSE,
  sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (room_id) REFERENCES chat_rooms(room_id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- Performance indexes
CREATE INDEX idx_assignments_class ON assignments(class_id);
CREATE INDEX idx_quizzes_class ON quizzes(class_id);
CREATE INDEX idx_study_material_class ON study_material(class_id);
CREATE INDEX idx_notices_class ON notices(class_id);
CREATE INDEX idx_attendance_class ON attendance(class_id);
CREATE INDEX idx_timetable_class ON timetable(class_id);
CREATE INDEX idx_class_members_student ON class_members(student_id);
CREATE INDEX idx_class_members_class ON class_members(class_id);

