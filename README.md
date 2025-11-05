# LinkedIn Clone - PHP & MySQL Project

A simple social networking web application inspired by LinkedIn, built using **PHP** and **MySQL**.

---

## Features

- User registration and login
- Profile creation and editing
- Create, edit, and delete posts
- Like and comment on posts
- Upload profile pictures and post images
- Responsive UI with basic styling

---

## Technologies Used

- PHP 8.x
- MySQL / MariaDB
- HTML, CSS (inline and external styles)
- JavaScript (basic functionality)
- Git & GitHub for version control
- Railway for backend hosting
- External MySQL hosting (e.g., db4free.net)

---

## Installation & Setup

1. **Clone the repository:**

   ```bash
   git clone https://github.com/yourusername/linkedin_clone.git
   cd linkedin_clone
   
2.Create a MySQL database:

Use db4free.net
 or any free MySQL hosting provider.

Import the provided database.sql dump file (located in the repo) to create tables and seed initial data.

3. Configure database connection:
   Edit config.php with your database credentials or set environment variables if deploying on Railway.

$servername = getenv('DB_HOST');
$username = getenv('DB_USER');
$password = getenv('DB_PASS');
$dbname = getenv('DB_NAME');

4.Deployment:

Push your code to GitHub.

Deploy PHP backend on Railway.

Use external MySQL database (db4free.net or similar).

Set environment variables in Railway for DB connection.

5.Live Demo

Railway Live URL
