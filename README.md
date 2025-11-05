Sikshantra: Academic Management System
======================================

# Overview
Sikshantra is a fully web-based Academic Management System (PHP + MySQL) designed for educational institutions and coaching centers. 
It centralizes key academic tasks like tracking attendance, submitting assignments, managing feedback, handling quizzes, 
organizing timetables, and generating reports.

# Features
1. Admin Panel: Manage users, departments, courses, and reports.
2. Teacher Dashboard: Upload assignments, mark attendance, create quizzes, and view student performance.
3. Student Dashboard: Submit assignments, take quizzes, view materials, and provide feedback.
4. Backup/Restore: Manage local database for non-cloud use.
5. Feedback & Timetable Modules: Support better communication and organization.

# Folder / File Structure
Path               | Description
------------------ | -----------------------------------------------
index.php          | Main entry point (login page)
/admin/            | Admin dashboard and management modules
/teacher/          | Teacher dashboard and attendance, assignment modules
/student/          | Student dashboard and assignment submission
/includes/ or /config/ | Contains db.php or config.php for DB connection
/uploads/          | Stores assignment and study material files
sikshantra.sql     | Database dump file
README_Sikshantra.txt | Project readme (this file)

# Prerequisites
* OS: Windows / Linux
* Software: XAMPP / WAMP / LAMP

# Requirements
* PHP ≥ 7.0
* MySQL or MariaDB
* Web browser (Chrome, Firefox)

# Installation Steps
1) Install XAMPP from https://www.apachefriends.org.
2) Copy the project folder into:
   C:\xampp\htdocs\sikshantra
3) Start Apache and MySQL from XAMPP Control Panel.
4) Open phpMyAdmin and create a new database named `sikshantra`.
   Click **Import**, select `sikshantra.sql` from the project folder, and click **Go**.
5) Edit your database config file (e.g., config.php or db.php):

   ```php
   $servername = "localhost";
   $username = "root";
   $password = "";
   $database = "sikshantra";
In your browser, visit:
http://localhost/sikshantra/index.php

Default Login Credentials

Role	Email	Password

1.Admin	admin@test.com	password123

2.Teacher	teacher@test.com	password123

3.Student	student@test.com	password123

###If registering a new Admin, the Admin Key is:IAMADMIN###


#Troubleshooting

Blank page or PHP error: Enable display_errors in php.ini or check Apache error log.

Database connection failed: Recheck credentials in the config file.

Uploads not working: Make sure uploads/ folder is writable.

Invalid login: Verify the user exists in the users table in phpMyAdmin.

#Security Notes

Change all default passwords before going live.

Use password_hash() to store passwords securely.

Disable display_errors on live servers.

Validate file uploads for type and size.

Regularly back up the database and uploads/ folder.

#Maintenance

Export the database regularly from phpMyAdmin or using mysqldump.

Keep backups offline or on external storage.

Update PHP and dependencies periodically.

#Project Credits
Project: Sikshantra – Academic Management System
Developed by: Ajay Gautam Irle (TCS2526020)
Some Screenshots from app below:
<img width="1919" height="955" alt="image" src="https://github.com/user-attachments/assets/1f8f84b2-1d2c-4ec0-9244-e5e678bacb2c" />
<img width="1911" height="961" alt="image" src="https://github.com/user-attachments/assets/ba174121-7e0b-40c8-aa95-d2c7a7f11c67" />


#Special Administration registration:
<img width="1916" height="608" alt="image" src="https://github.com/user-attachments/assets/ea305410-ed3e-4cf9-965f-2cdb0ec87754" />
<img width="1915" height="890" alt="image" src="https://github.com/user-attachments/assets/6e02e652-49dc-4da8-abd3-b39243593812" />


#Student Dashboard:
<img width="1918" height="889" alt="image" src="https://github.com/user-attachments/assets/02168f80-79e3-4d49-9332-7f7e37139569" />
<img width="1919" height="887" alt="image" src="https://github.com/user-attachments/assets/47de7181-7669-4781-be2a-4391dbf86153" />
<img width="1908" height="878" alt="image" src="https://github.com/user-attachments/assets/62e4f8a0-4814-429e-a56a-d119f6b002a3" />
<img width="1912" height="876" alt="image" src="https://github.com/user-attachments/assets/6e5fcc3d-e0cf-49cb-a7ad-49e533230fa7" />


#Teacher Dashboard:
<img width="1907" height="894" alt="image" src="https://github.com/user-attachments/assets/3f962e43-c12c-46a9-bdf6-1b74024a1fa8" />
<img width="1876" height="890" alt="image" src="https://github.com/user-attachments/assets/d1b8cdef-ada9-40ab-9df3-0667d888b053" />
<img width="1918" height="886" alt="image" src="https://github.com/user-attachments/assets/01303341-3076-4ae0-a6b3-1e67f2311dc4" />
<img width="1918" height="893" alt="image" src="https://github.com/user-attachments/assets/72c6e7e2-710d-4d31-ba9d-e8465648bfec" />


#Admin:
<img width="1916" height="893" alt="image" src="https://github.com/user-attachments/assets/9e3ac8f5-f0e6-4d7a-90fa-0c3f194ad844" />
<img width="1916" height="876" alt="image" src="https://github.com/user-attachments/assets/b4748b59-d343-4ebb-ad15-80cfc85220c2" />
<img width="1903" height="884" alt="image" src="https://github.com/user-attachments/assets/b7a8ec70-ead7-41f2-88e0-3ba11f8c73d8" />
<img width="1914" height="894" alt="image" src="https://github.com/user-attachments/assets/c61b7bb1-bd0a-4175-9e32-6f0cef09e9a0" />
<img width="1906" height="882" alt="image" src="https://github.com/user-attachments/assets/d9d652ba-0449-41fd-b967-bc7bf610c195" />
<img width="1890" height="666" alt="image" src="https://github.com/user-attachments/assets/ad6f9a57-379e-4124-8ed5-f8e2e05efe12" />






