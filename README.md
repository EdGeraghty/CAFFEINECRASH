# CAFFEINECRASH
A progressive web app for medication and health tracking

## Features

- **User Authentication**: Secure login and registration system
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

## Security Features

- Passwords are hashed using Argon2ID
- SQL injection protection via prepared statements
- XSS protection via output sanitization
- Session-based authentication
- User data isolation

## Database Schema

The application uses SQLite with the following tables:
- `users`: User account information
- `medications`: Medication records
- `health_data`: Health metrics and measurements
- `reminders`: Scheduled reminders
- `shares`: Shared information between users

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

This project is open source and available under the MIT License.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.
