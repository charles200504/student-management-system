CREATE DATABASE IF NOT EXISTS student_management_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE student_management_db;

DROP TABLE IF EXISTS students;
DROP TABLE IF EXISTS courses;

CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_code VARCHAR(20) NOT NULL UNIQUE,
    course_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_no VARCHAR(20) NOT NULL UNIQUE,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    phone VARCHAR(20) NULL,
    date_of_birth DATE NULL,
    gender ENUM('Male', 'Female', 'Other') NOT NULL DEFAULT 'Other',
    address VARCHAR(255) NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    status ENUM('Active', 'Inactive', 'Graduated') NOT NULL DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_students_course
        FOREIGN KEY (course_id)
        REFERENCES courses(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
) ENGINE=InnoDB;

INSERT INTO courses (course_code, course_name) VALUES
('PUSL2021', 'Computing Group Project'),
('COMP101', 'Introduction to Programming'),
('DBMS201', 'Database Management Systems'),
('WEB301', 'Web Application Development');

INSERT INTO students
(student_no, first_name, last_name, email, phone, date_of_birth,
 gender, address, course_id, enrollment_date, status)
VALUES
('STU001', 'Amal', 'Perera', 'amal.perera@example.com', '0712345678',
 '2002-05-14', 'Male', 'Colombo', 1, '2026-01-15', 'Active'),
('STU002', 'Nimali', 'Fernando', 'nimali.fernando@example.com', '0779876543',
 '2003-02-20', 'Female', 'Kandy', 2, '2026-01-15', 'Active'),
('STU003', 'Ravi', 'Silva', 'ravi.silva@example.com', '0754567890',
 '2001-11-03', 'Male', 'Galle', 3, '2025-09-01', 'Graduated');