# CAFFEINECRASH
A "vibecoded" progressive web app for medication and health tracking

## Features

- **User Authentication**: Secure login and registration system
- **Admin Panel**: Comprehensive administration interface with:
  - User management (view, edit, activate/deactivate, delete users)
  - System settings (enable/disable registration, etc.)
  - TOTP-based multi-factor authentication (MFA) for admin accounts
  - System logs with filtering and search
  - Analytics dashboard with usage metrics
  - Extensible architecture for future admin features
- **Medication Management**: Upload, store, and track medications with dosages, frequencies, and prescriber information
- **Health Data Tracking**: Record and monitor various health metrics including:
  - GAD-7 scores (anxiety assessment)
  - Weight
  - BMI (with automatic calculation)
  - Blood pressure
  - Heart rate
  - Temperature
  - Blood sugar
  - Custom metrics
- **Reminders**: Set medication and health-related reminders
- **Sharing**: Share medication schedules and summaries with other users
- **Progressive Web App**: Installable on mobile devices with offline support

## Requirements

- PHP 8.4 or higher
- SQLite3 support
- Web server (Apache, Nginx, or PHP built-in server)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/EdGeraghty/CAFFEINECRASH.git
cd CAFFEINECRASH
```

2. Copy the environment configuration:
```bash
cp .env.example .env
```

3. Create necessary directories:
```bash
mkdir -p data sessions
chmod 755 data sessions
```

4. Start the development server:
```bash
cd public
php -S localhost:8000
```

5. Open your browser and navigate to `http://localhost:8000`

6. **Complete the installation wizard:**
   - You'll be automatically redirected to the installation page
   - Create your admin account with a username, email, and password (minimum 8 characters)
   - Click "Complete Installation"
   - You'll be logged in automatically as the admin user

**Important:** The admin account created during installation is permanent and will remain even when using demo mode.

## Demo Mode

Want to try CAFFEINECRASH without adding your own data first? Use the demo mode!

1. Navigate to `http://localhost:8000/demo.php`
2. Click "Create Demo Data"
3. Use the provided credentials to login:
   - **Username**: `demo_patient` or `demo_caregiver`
   - **Password**: `demo123`

Demo mode creates:
- Sample users with realistic data
- Multiple medications with dosages and prescriber information
- Health data entries spanning 30 days (weight, blood pressure, heart rate, blood sugar, GAD-7 scores)
- Upcoming and completed reminders

You can clear demo data anytime from the same page.

## Usage

### First Time Setup

1. Navigate to the registration page
2. Create a new account with username, email, and password
3. Log in with your credentials

### Managing Medications

1. Go to the "Medications" page
2. Fill in the medication details:
   - Name (required)
   - Dosage
   - Frequency
   - Prescriber
   - What it's prescribed for
   - Additional notes
3. Click "Add Medication" to save

### Tracking Health Data

1. Go to the "Health Data" page
2. Select the type of data you want to record
3. Enter the value and optional unit
4. For BMI, enter weight and height for automatic calculation
5. Add any relevant notes
6. Click "Add Data" to save

### Setting Reminders

1. Go to the "Reminders" page
2. Enter a title for the reminder
3. Optionally link it to a medication
4. Set the date and time for the reminder
5. Add a description if needed
6. Click "Add Reminder" to save

### Sharing Information

1. Go to the "Share" page
2. Enter the username of the person you want to share with
3. Select what type of information to share:
   - Medications List
   - Reminder Schedule
   - Complete Summary
4. Click "Share" to send

### Admin Panel

For admin users, access the admin panel by clicking "Admin" in the navigation menu.

See [ADMIN.md](ADMIN.md) for complete admin panel documentation including:
- User management
- System logs
- Analytics
- Multi-factor authentication (TOTP)

To make a user an admin:
```bash
sqlite3 public/data/caffeinecrash.db "UPDATE users SET is_admin = 1 WHERE username = 'your_username';"
```

## Security Features

- Passwords are hashed using Argon2ID
- SQL injection protection via prepared statements
- XSS protection via output sanitization
- Session-based authentication
- User data isolation
- Multi-factor authentication (TOTP) for admin accounts
- Comprehensive logging for security auditing

## Database Schema

The application uses SQLite with the following tables:
- `users`: User account information (with admin and active status)
- `medications`: Medication records
- `health_data`: Health metrics and measurements
- `reminders`: Scheduled reminders
- `shares`: Shared information between users
- `logs`: System and application logs for admin monitoring

## Progressive Web App

CAFFEINECRASH is a PWA and can be installed on mobile devices:

1. Open the app in a mobile browser (Chrome, Safari, etc.)
2. Look for the "Add to Home Screen" or "Install" prompt
3. Follow the prompts to install the app
4. The app will work offline after installation

## Development

The project structure:
```
CAFFEINECRASH/
├── public/           # Web-accessible files
│   ├── css/         # Stylesheets
│   ├── js/          # JavaScript files
│   ├── includes/    # PHP includes (header, etc.)
│   └── *.php        # Application pages
├── src/             # PHP classes
│   ├── Database.php
│   ├── Auth.php
│   ├── Medication.php
│   ├── HealthData.php
│   ├── Reminder.php
│   └── Share.php
├── data/            # SQLite database (created automatically)
├── sessions/        # Session files (created automatically)
└── vendor/          # Autoloader
```

## License

This project is open source and available under the [YOLO Public License (YPL) v0.12.34-hunter.2](https://github.com/YOLOSecFW/YoloSec-Framework/blob/master/YOLO%20Public%20License).

## Contributing

This is LLM-only!
