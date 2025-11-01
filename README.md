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
