# Tracademics - Role-Based Academic Management System

A comprehensive academic management system with role-based access control for educational institutions, featuring curriculum management, compliance monitoring, and faculty assignment tools.

## Features

### Role-Based System

#### VPAA (Vice President for Academic Affairs)
- **Dashboard**: Overview of all users with compliance monitoring charts
- **Monitor**: Navigate through departments → programs → faculty compliance
- **Reports**: Generate comprehensive institutional reports
- **Compliance Charts**: Filterable by departments and programs

#### Dean
- **Dashboard**: Overview and compliance charts for programs under their department
- **Monitor Faculty**: View program cards and faculty compliance tables
- **Reports**: Generate PDF reports with timestamped filenames
- **Scope**: Limited to their department only

#### Program Head
- **Dashboard**: Overview and compliance charts for faculty under supervision
- **Monitor Compliances**: Faculty compliance table with filters and pagination
- **Assignments**: Manage faculty subject assignments based on program curriculum
- **Filters**: By Faculty Name, Compliance Status (Complete/Incomplete/Pending)

#### Faculty Member
- **Dashboard**: Personal compliance status summary
- **Subjects**: View assigned subjects and requirements
- **Compliance**: Submit Google Drive links for required documents
- **Requirements Table**: Requirement Name, Description, Due Date, Submission Link, Status

#### MIS (Management Information System)
- **User Management**: Create and manage all user accounts
- **Department & Program Management**: Configure academic structure
- **Curriculum Management**: Create and maintain program curricula
- **Semester Management**: Set up academic terms and semesters
- **Activity Logs**: Monitor system usage and user activities

### Global Features
- **Profile Settings**: Editable user information for all roles
- **Logout Confirmation**: Modal with blurred transparent backdrop
- **Responsive Design**: Enhanced UI with modern styling for tables and buttons
- **Semester Sessions**: Track user activity within academic periods

## Design Guidelines

- **Sidebar**: #72c4d0 (RGB 114,196,208)
- **Header**: #359aca (RGB 53,154,202)
- **Page Background**: #dbffef (RGB 219,255,239)
- **Modal Background**: Blurred transparent backdrop
- **UI Components**: Modern table styling with enhanced visual feedback

## Technical Implementation

### Controllers
- `DashboardController`: Role-specific dashboard data
- `MonitorController`: VPAA and Dean monitoring functionality
- `SubjectsController`: Faculty subject management
- `ProfileController`: User profile settings
- `MISController`: System administration and configuration
- `FacultyAssignmentController`: Program-specific subject assignments
- `SemesterController`: Academic period management
- `ComplianceController`: Document submission and verification

### Views
- Role-specific dashboard views with compliance charts
- Monitoring interfaces for different roles
- Faculty subject requirements and submission forms
- Profile settings with password change functionality
- MIS administration panels for system configuration
- Enhanced UI components with modern styling

### Middleware
- `EnsureRole`: Role-based access control
- `ActivityLogger`: Track user actions throughout the system
- `SemesterSession`: Manage user activity within academic periods

### Database Models
- `User`: Role, department, and program relationships
- `ComplianceDocument`: Document submission tracking
- `FacultyAssignment`: Subject assignments based on curriculum
- `DocumentType`: Required document types with submission options
- `Course`: Academic programs (replaces legacy Program model)
- `Curriculum`: Program-specific subject arrangements
- `CurriculumSubject`: Subjects within program curricula
- `Semester`: Academic periods configuration
- `UserActivityLog`: System usage tracking

## Installation

1. Clone the repository
2. Install dependencies: `composer install` and `npm install`
3. Configure database in `.env`
4. Run migrations: `php artisan migrate`
5. Seed the database: `php artisan db:seed`
6. Compile assets: `npm run dev` or `npm run build`
7. Start the application: `php artisan serve`

## Usage

1. **Login**: Use Google OAuth or email/password with enhanced login UI
2. **Role Assignment**: Users are automatically assigned roles based on their account
3. **Navigation**: Use the sidebar to access role-specific features
4. **Curriculum**: MIS configures programs and associated curricula
5. **Assignments**: Program Heads assign subjects from program-specific curricula
6. **Compliance**: Faculty submit documents via Google Drive links
7. **Monitoring**: Administrators track compliance across the institution
8. **Reporting**: Generate exports of compliance data for institutional review

## Key System Updates

### Curriculum Management System
- Program-specific curricula with subjects, year levels, and units
- Automatic filtering of subjects for faculty assignments
- Integration with compliance monitoring

### Enhanced User Interface
- Modern table styling with improved readability
- Visual feedback for status indicators
- Blurred transparent modal backgrounds
- Responsive components for all screen sizes

### Activity Monitoring
- Comprehensive user action logging
- Session tracking within academic periods
- Performance optimization with database indexes

## Security

- Role-based access control
- Middleware protection for routes
- Activity logging for audit trails
- Input validation and sanitization
- CSRF protection on all forms

## Browser Support

- Modern browsers with ES6+ support
- Responsive design for mobile devices
- Progressive enhancement approach

#Tracademics