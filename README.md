# SUBJECTHUB

A web-based application for managing subjects and educational resources. SUBJECTHUB provides a platform for users to access subject-specific materials, while administrators can manage users and the content available.

## Features

- **User Authentication:** Secure user registration and login system.
- **Password Management:** Forgot and reset password functionality.
- **User Profiles:** View and manage user profile information.
- **Dashboard:** A central hub for users to view their subjects and recent activity.
- **Subject Management:** Admins can create, update, and delete subjects.
- **Resource Uploading:** Users can upload and associate educational resources (like PDFs) with subjects.
- **Activity Logging:** Tracks important user and system actions.
- **Admin Panel:** A dedicated dashboard for administrators to manage users and subjects.

## Technology Stack

- **Backend:** PHP
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Web Server:** Apache (configured with `.htaccess`)

## Prerequisites

To run this project locally, you will need:
- [XAMPP](https://www.apachefriends.org/index.html) (or any other Apache, MySQL, PHP stack)

## Installation and Setup

1.  **Place the project:**
    -   Clone or download the project files into your `htdocs` directory within your XAMPP installation folder. The path should look like `C:/xampp/htdocs/SUBJECTHUB`.

2.  **Start Services:**
    -   Open the XAMPP Control Panel and start the **Apache** and **MySQL** modules.

3.  **Database Setup:**
    -   Open your web browser and navigate to `http://localhost/phpmyadmin`.
    -   Create a new database. You can name it `subjecthub_db`.
    -   Select the new database and go to the **Import** tab.
    -   Click "Choose File" and select the `database.sql` file located in the `config/` directory of the project.
    -   Click **Go** to import the database structure.

4.  **Configure Database Connection:**
    -   Open the `config/database.php` file in your code editor.
    -   Update the database credentials (hostname, username, password, and database name) to match your local MySQL setup. For a default XAMPP installation, it might look like this:
        ```php
        <?php
        $host = 'localhost';
        $dbname = 'subjecthub_db';
        $user = 'root';
        $pass = ''; 
        // ... rest of the file
        ```

5.  **Access the Application:**
    -   Open your web browser and navigate to `http://localhost/SUBJECTHUB/`.

## File Structure

```
SUBJECTHUB/
├── .htaccess           # Apache configuration for URL rewriting
├── index.php           # Main application entry point (router)
├── config/
│   ├── database.php    # Database connection settings
│   └── database.sql    # SQL dump for database setup
├── public/             # Web-accessible assets
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   └── assets/         # Images, fonts, etc.
├── src/
│   ├── controllers/    # Handles application logic and user input
│   ├── includes/       # Reusable components (e.g., logger)
│   └── models/         # (Intended for database interaction logic)
├── templates/          # PHP files for rendering HTML views
│   └── partials/       # Reusable header and footer templates
└── uploads/            # Directory for user-uploaded files
```
