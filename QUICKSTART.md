# CAFFEINECRASH Quick Start Guide

## Getting Started in 3 Steps

### 1. Start the Server
```bash
cd public
php -S localhost:8000
```

### 2. Open Your Browser
Navigate to: `http://localhost:8000`

### 3. Complete Installation & Setup
- You'll be automatically redirected to the installation wizard
- Create your admin account with:
  - Username (letters, numbers, underscores, hyphens only)
  - Email address
  - Password (minimum 8 characters)
- Click "Complete Installation"
- You'll be logged in automatically as the admin

**Important:** Your admin account is permanent and will remain even when using demo mode.

**OR** want to explore first? After installation, navigate to `/demo.php` to create sample data and try the app without adding your own information.

## Demo Mode 🎭

Want to see what CAFFEINECRASH can do before adding your own data?

1. Navigate to `http://localhost:8000/demo.php`
2. Click "Create Demo Data"
3. Login with demo credentials:
   - Username: `demo_patient` or `demo_caregiver`
   - Password: `demo123`

You'll get instant access to sample medications, health data, and reminders!

## Main Features

### 📋 Medications
- Click "Medications" in the navigation
- Add your medications with dosage, frequency, prescriber information
- Edit or delete medications as needed
- Track what each medication is prescribed for

### 📊 Health Data
- Click "Health Data" in the navigation
- Record various health metrics:
  - GAD-7 scores (anxiety assessment)
  - Weight
  - BMI (automatically calculated from weight/height)
  - Blood pressure, heart rate, temperature
  - Blood sugar
  - Custom metrics
- View your health history and trends

### ⏰ Reminders
- Click "Reminders" in the navigation
- Set reminders for medications or health tasks
- Link reminders to specific medications
- Mark reminders as completed
- View pending and completed reminders

### 🤝 Share
- Click "Share" in the navigation
- Share your medication list with other users
- Share your reminder schedule
- Share complete health summaries
- View what others have shared with you
- Simply enter the username of the person you want to share with

## Progressive Web App (PWA)

### Installing on Mobile
1. Open the app in your mobile browser (Chrome, Safari, etc.)
2. Look for the "Add to Home Screen" or "Install" prompt
3. Follow the prompts to install
4. The app will work offline after installation

## Security Features
- ✅ Secure password hashing (Argon2ID)
- ✅ Session-based authentication
- ✅ SQL injection protection
- ✅ XSS protection
- ✅ User data isolation

## Data Storage
- All data is stored locally in a SQLite database
- Database file: `data/caffeinecrash.db`
- Session files: `sessions/`
- Both directories are created automatically

## Tips
- Use meaningful medication names
- Keep dosages and frequencies up to date
- Set reminders for important medications
- Share your schedule with caregivers or family members
- Regularly track your health metrics for better insights

## Troubleshooting

### Can't login?
- Check that you're using the correct username (not email) to login
- Passwords are case-sensitive

### Database errors?
- Ensure the `data/` directory has write permissions
- The database will be created automatically on first run

### Session issues?
- Ensure the `sessions/` directory has write permissions
- Clear your browser cookies if needed

## Support
For issues or questions, please create an issue on the GitHub repository.
