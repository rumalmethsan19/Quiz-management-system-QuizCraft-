# QuizCraft - Setup Instructions

## Prerequisites
- XAMPP installed and running
- Apache and MySQL services started in XAMPP Control Panel

## Database Setup Instructions

### Step 1: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** server
3. Start **MySQL** server

### Step 2: Install Database (Choose ONE method)

#### Method 1: Automatic Installation (Recommended)
1. Open your browser
2. Navigate to: `http://localhost/Quiz management system/install_database.php`
3. The script will automatically create the database and all tables
4. **Important:** Delete or rename `install_database.php` after installation for security

#### Method 2: Manual Installation via phpMyAdmin
1. Open your browser and go to: `http://localhost/phpmyadmin`
2. Click on "SQL" tab
3. Open the file: `database/setup.sql`
4. Copy all the SQL code
5. Paste it into the SQL query box in phpMyAdmin
6. Click "Go" to execute

### Step 3: Verify Installation
1. In phpMyAdmin, you should see a database named `quizcraft_db`
2. Inside it, you should have these tables:
   - users
   - quizzes
   - questions
   - quiz_results
   - student_answers

### Step 4: Test the Application
1. Navigate to: `http://localhost/Quiz management system/index.php`
2. Click "Create Account"
3. Fill in the registration form
4. Submit and check if the data is saved in the database

## Database Structure

### Users Table
Stores all user information (Students and Teachers)
- id
- role (Student/Teacher)
- full_name
- email (unique)
- work_school
- username (unique)
- password (hashed)
- created_at
- updated_at
- is_active
- last_login

### Quizzes Table (Future Use)
Stores quiz information created by teachers

### Questions Table (Future Use)
Stores questions for each quiz

### Quiz Results Table (Future Use)
Stores student quiz attempt results

### Student Answers Table (Future Use)
Stores individual answers for each question

## Database Configuration

The database configuration is located in: `config/database.php`

Default settings:
- Host: localhost
- Username: root
- Password: (empty)
- Database: quizcraft_db

**Note:** If you have different MySQL credentials, update the `config/database.php` file accordingly.

## Security Notes

1. After installation, delete or rename `install_database.php`
2. Passwords are hashed using PHP's `password_hash()` function with BCRYPT
3. SQL injection protection using prepared statements
4. XSS protection using `htmlspecialchars()`
5. Session management for error handling

## Troubleshooting

### "Connection failed" Error
- Make sure MySQL is running in XAMPP
- Check if the credentials in `config/database.php` are correct

### "Database already exists" Error
- This is normal if you run the installer twice
- The installer uses `CREATE IF NOT EXISTS` to prevent errors

### "Access Denied" Error
- Make sure your MySQL username and password are correct
- Default XAMPP MySQL credentials are: username=root, password=(empty)

## Next Steps

After successful installation:
1. Create a new account (Student or Teacher)
2. Test the login system
3. Proceed with building the dashboard and quiz features

---

**QuizCraft** - Craft Your Knowledge, Master Your Skills
