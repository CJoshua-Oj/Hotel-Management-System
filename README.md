# Hotel Management System — PHP 8.3

A complete native PHP 8.3 hotel management application

## Main features

- Administrator login/logout
- Dashboard
- Customers/guests
- Room types and rates
- Rooms and availability
- Reservations
- Guest check-in
- Guest check-out
- Payments
- Operational reports
- Hotel profile settings
- CSRF protection
- PDO prepared statements
- Secure password hashing
- Responsive HTML interface
- CSS kept in a separate stylesheet

## Requirements

- PHP 8.3+
- MySQL/MariaDB
- Apache/XAMPP
- PHP extensions: PDO and PDO_MySQL

## Installation with XAMPP

1. Extract the folder into `C:\xampp\htdocs\`.
2. Start Apache and MySQL.
3. Open `config.php` and check the database settings if necessary.
4. Open:

   `http://localhost/Hotel_Management_System_PHP83/install.php`

5. Enter the MySQL credentials and create the administrator account.
6. After installation, open `login.php`.
7. Sign in with the administrator credentials created during installation.

## Default installation values

The installer starts with:

- Database: `hotel_management`
- MySQL user: `root`
- MySQL password: `guards`
- Administrator username: `admin`
- Administrator password: `admin123`

Change these values during installation as appropriate for your computer.

## Important

This version is a standalone native PHP 8.3 application.

The application uses no bundled third-party framework. The stylesheet is included locally.

## Database

`database.sql` contains the complete database structure. The installer imports it automatically.

## PHP built-in server

From the project directory:

```text
php -S localhost:8000
```

Then open:

`http://localhost:8000/`

## Security

- Use a strong administrator password.
- Use a dedicated database user in production.
- Enable HTTPS in production.
- Back up the database regularly.
- Restrict access to `install.php` after installation.
