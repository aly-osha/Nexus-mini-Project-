# Educational Platform - Mini Project

A comprehensive web-based educational platform built with PHP, MySQL, HTML, CSS, and JavaScript. This platform provides a complete learning management system for students, teachers, and administrators.

## Features

### 🔐 Authentication System
- Secure login system for three user types: Admin, Teacher, and Student
- Session management and user verification
- Password-based authentication

### 👨‍💼 Admin Features
- **Dashboard**: Overview of system statistics
- **User Management**: View and manage all users
- **Course Management**: Create, edit, and delete courses
- **Course Assignment**: Assign courses to teachers
- **User Verification**: Bulk verify unverified users with "Select All" functionality
- **Settings**: Profile management and system configuration

### 👨‍🏫 Teacher Features
- **Dashboard**: Personal teaching overview
- **Course Management**: Create and manage courses
- **Assignment Management**: Create assignments and grade student submissions
- **Student Management**: View enrolled students
- **Materials Upload**: Share course materials and resources
- **Profile Management**: Update profile picture and personal information

### 👨‍🎓 Student Features
- **Dashboard**: Personal learning overview
- **Course Enrollment**: Browse and enroll in available courses
- **My Learning**: View enrolled courses with progress tracking
- **Assignment Submission**: Submit assignments with file uploads
- **Profile Management**: Update personal information

### 📚 Course Management
- **Course Creation**: Teachers and admins can create courses with descriptions and images
- **Course Enrollment**: Students can enroll in available courses
- **Progress Tracking**: Visual progress indicators for student learning
- **Course Materials**: Teachers can upload various types of course materials

### 📝 Assignment System
- **Assignment Creation**: Teachers can create assignments with due dates and point values
- **Assignment Submission**: Students can submit text and file-based assignments
- **Grading System**: Teachers can grade submissions and provide feedback
- **Submission Tracking**: Track submission status (submitted, graded, late)

### 👥 User Management
- **User Verification**: Admin can verify new users in bulk
- **Profile Pictures**: Support for profile picture uploads
- **User Roles**: Distinct permissions for admin, teacher, and student roles

## Database Schema

The platform uses a comprehensive MySQL database with the following main tables:

- **adminnex**: Admin user information
- **teacher_details** & **teacher_user**: Teacher profiles and authentication
- **student_details** & **student_user**: Student profiles and authentication
- **course**: Course information and metadata
- **enrollments**: Student course enrollment tracking
- **assignments**: Assignment details and requirements
- **submissions**: Student assignment submissions and grades
- **course_materials**: Course resources and materials

## Installation & Setup

### Prerequisites
- WAMP/XAMPP server (Apache, MySQL, PHP)
- PHP 7.4 or higher
- MySQL 5.7 or higher

### Installation Steps

1. **Clone/Download the project files** to your WAMP `www` directory
2. **Import the database schema**:
   ```sql
   -- Run the mini_schema.sql file in your MySQL database
   ```
3. **Configure database connection** in `config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', 'your_password');
   define('DB_NAME', 'mini');
   ```
4. **Populate with sample data**:
   - Navigate to `http://localhost/your-project-folder/populate_sample_data.php`
   - This will create sample users, courses, and assignments for testing

### Sample Accounts

After running the sample data script, you can test with these accounts:

**Admin Accounts:**
- Username: `admin1`, Password: `admin123`
- Username: `admin2`, Password: `admin456`

**Teacher Accounts:**
- Username: `teacher1`, Password: `teacher123` (Dr. Sarah Johnson - Computer Science)
- Username: `teacher2`, Password: `teacher123` (Prof. Michael Chen - Web Development)
- Username: `teacher3`, Password: `teacher123` (Dr. Emily Rodriguez - Database Systems)
- Username: `teacher4`, Password: `teacher123` (Prof. David Kim - Data Science)
- Username: `teacher5`, Password: `teacher123` (Dr. Lisa Thompson - Mobile Development)

**Student Accounts:**
- Username: `student1`, Password: `student123` (Alice Johnson)
- Username: `student2`, Password: `student123` (Bob Smith)
- Username: `student3`, Password: `student123` (Carol Davis)

## File Structure

```
MiniProject/
├── config.php                          # Database configuration
├── populate_sample_data.php            # Sample data population script
├── login.php                           # Login page
├── user_validation.php                 # Authentication logic
├── new_user.php                        # User registration
├── user_name.php                       # Username validation
├── admin.php                           # Admin dashboard
├── teacher.php                         # Teacher dashboard
├── student.php                         # Student dashboard
├── courses.php                         # Admin course management
├── teacher_course_management.php       # Teacher course management
├── teacher_assignment_management.php   # Teacher assignment system
├── student_assignments.php             # Student assignment submissions
├── student_course_enrollment.php       # Course enrollment system
├── admin_course_assignment.php         # Admin course assignment
├── admin_user_verification.php         # User verification system
├── my_learning_new.php                 # Student learning dashboard
├── teacher_settings.php                # Teacher profile settings
├── student_settings.php                # Student profile settings
├── mini_schema.sql                     # Database schema
├── uploads/                            # File upload directory
├── images/                             # Static images
├── *.css                               # Stylesheets
└── *.js                                # JavaScript files
```

## Key Features Implementation

### Course Management
- **Edit Functionality**: Click on any course card to edit course details
- **Image Upload**: Support for course cover images
- **Status Management**: Active/inactive course status

### Assignment System
- **File Uploads**: Students can upload various file types
- **Grading Interface**: Teachers can grade and provide feedback
- **Due Date Tracking**: Automatic late submission detection

### User Verification
- **Bulk Operations**: "Select All" functionality for verifying multiple users
- **Individual Verification**: Single-user verification option
- **Verification Status**: Clear indicators for verified/pending users

### Profile Management
- **Image Upload**: Profile picture upload functionality
- **Information Updates**: Comprehensive profile editing
- **Security**: Password change functionality

## Security Features

- **SQL Injection Prevention**: Prepared statements used throughout
- **File Upload Security**: File type validation and secure storage
- **Session Management**: Secure session handling
- **Input Validation**: Server-side validation for all inputs
- **XSS Protection**: HTML entity encoding for user inputs

## Browser Compatibility

- Chrome 80+
- Firefox 75+
- Safari 13+
- Edge 80+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## Support

For support and questions, please contact the development team or create an issue in the project repository.

## License

This project is licensed under the MIT License - see the LICENSE file for details.

---

**Note**: This is a demonstration project for educational purposes. For production use, additional security measures and optimizations should be implemented.
