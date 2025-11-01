# QuizCraft - Testing Guide

## How to Test the Registration System

Follow these steps to test the complete registration flow:

### Step 1: Setup Database

**Option A - Automatic (Recommended):**
1. Make sure XAMPP Apache and MySQL are running
2. Open browser and visit: `http://localhost/Quiz management system/install_database.php`
3. You'll see a confirmation page when database is created
4. **Important:** Delete `install_database.php` after installation

**Option B - Manual:**
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Click "SQL" tab
3. Copy content from `database/setup.sql`
4. Paste and click "Go"

### Step 2: Test Registration

1. Visit: `http://localhost/Quiz management system/index.php`
2. Click **"Create Account"** button
3. Fill in the registration form:
   - **Role**: Select Student or Teacher
   - **Full Name**: Enter your name
   - **Email**: Enter a valid email
   - **Work/School**: Enter your school/workplace
   - **Username**: Choose a unique username
   - **Password**: Create a password (min 6 characters)
   - **Confirm Password**: Re-enter the same password
4. Click **"Create Account"** button

### Step 3: Verify Success

**What Should Happen:**
1. Form data is validated
2. Data is saved to the database
3. You're redirected to the home page (index.php)
4. A beautiful modal appears with the message: **"Account Created!"**
5. Message says: "Your account has been created successfully. Welcome to QuizCraft!"
6. Click **"Get Started"** to close the modal

### Step 4: Verify Database Entry

1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Select database: `quizcraft_db`
3. Click on table: `users`
4. You should see your registration data:
   - Role (Student/Teacher)
   - Full name
   - Email
   - Work/School
   - Username
   - Hashed password (encrypted, not plain text)
   - Created timestamp

### Testing Error Scenarios

**Test 1: Missing Role**
- Don't select Student or Teacher
- Submit form
- Should show error: "Please select a role"

**Test 2: Password Mismatch**
- Enter different passwords in Password and Confirm Password
- Should show error: "Passwords do not match"

**Test 3: Duplicate Email**
- Register with an email that's already registered
- Should show error: "Email address is already registered"

**Test 4: Duplicate Username**
- Register with a username that's already taken
- Should show error: "Username is already taken"

**Test 5: Invalid Email**
- Enter invalid email format (e.g., "test@test")
- Should show error: "Please enter a valid email address"

**Test 6: Short Username**
- Enter username less than 4 characters
- Should show error: "Username must be at least 4 characters long"

**Test 7: Short Password**
- Enter password less than 6 characters
- Should show error: "Password must be at least 6 characters long"

### Features to Notice

1. **Form Retention**: When errors occur, the form keeps your entered data (except passwords)
2. **Real-time Validation**: Password matching is checked as you type
3. **Visual Feedback**:
   - Red error messages appear at the top
   - Input fields highlight on focus
   - Buttons have hover and click animations
4. **Security Features**:
   - Passwords are hashed (never stored as plain text)
   - SQL injection protection
   - XSS protection
   - Session-based messaging

### Expected Database Structure

**users table columns:**
- `id` - Auto-increment primary key
- `role` - Student or Teacher
- `full_name` - User's full name
- `email` - Unique email address
- `work_school` - School or workplace
- `username` - Unique username
- `password` - Hashed password (BCRYPT)
- `created_at` - Account creation timestamp
- `updated_at` - Last update timestamp
- `is_active` - Account status (default: 1)
- `last_login` - Last login timestamp

### Success Criteria

✅ Database is created successfully
✅ Form validates all required fields
✅ Data is saved to database with hashed password
✅ Success modal appears on index.php
✅ No duplicate emails or usernames allowed
✅ Error messages display correctly
✅ Form data is retained on errors (except passwords)
✅ All animations work smoothly

### Troubleshooting

**Error: "Connection failed"**
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/database.php`

**Error: "Table doesn't exist"**
- Run the database installer first
- Check if tables were created in phpMyAdmin

**Form submits but no success message**
- Check PHP error logs
- Verify session is working (check if cookies enabled)
- Check browser console for JavaScript errors

**Success modal doesn't appear**
- Clear browser cache and refresh
- Check browser console for errors
- Verify PHP session is working

---

**Next Steps:**
After successful registration testing, you can proceed to:
1. Create the Login system
2. Build the Dashboard
3. Add Quiz creation features
4. Implement quiz taking functionality

---

**QuizCraft** - Craft Your Knowledge, Master Your Skills
