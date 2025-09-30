# Installation Process Documentation

## Overview

CAFFEINECRASH now includes an automated installation wizard that sets up the database and creates the first admin user. This process ensures a smooth onboarding experience and maintains security best practices.

## Installation Flow

```
┌─────────────────────────────────────────────────────────────┐
│ User accesses http://localhost:8000                         │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│ Check: Is installation complete?                            │
│ (Database setting: installation_complete = 1)               │
└──────────────────────┬──────────────────────────────────────┘
                       │
           ┌───────────┴──────────┐
           │                      │
          NO                     YES
           │                      │
           ▼                      ▼
┌──────────────────────┐  ┌─────────────────────┐
│ Redirect to          │  │ Check if logged in  │
│ /install.php         │  └─────────┬───────────┘
└──────────┬───────────┘            │
           │              ┌─────────┴──────────┐
           │             YES                   NO
           │              │                     │
           ▼              ▼                     ▼
┌──────────────────────┐  ┌──────────────┐  ┌─────────────┐
│ Installation Wizard  │  │ /dashboard   │  │ /login.php  │
│                      │  └──────────────┘  └─────────────┘
│ 1. Enter username    │
│ 2. Enter email       │
│ 3. Enter password    │
│ 4. Confirm password  │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────────────────────────────────────────────┐
│ Validation                                                    │
│ - Username: alphanumeric, underscores, hyphens               │
│ - Email: valid format                                        │
│ - Password: minimum 8 characters                             │
│ - Passwords match                                            │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ Create Admin User                                            │
│ - Hash password with Argon2ID                                │
│ - Set is_admin = 1                                           │
│ - Set is_active = 1                                          │
│ - Insert into users table                                    │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ Mark Installation Complete                                   │
│ - Set setting: installation_complete = 1                     │
│ - Log installation event                                     │
└──────────────────────┬───────────────────────────────────────┘
                       │
                       ▼
┌──────────────────────────────────────────────────────────────┐
│ Auto-login Admin User                                        │
│ - Create session                                             │
│ - Redirect to dashboard                                      │
└──────────────────────────────────────────────────────────────┘
```

## Key Features

### 1. **Automatic Redirection**
All entry points (index.php, login.php, register.php, demo.php) check if installation is complete. If not, users are automatically redirected to the installation wizard.

### 2. **Admin User Persistence**
The admin user created during installation:
- Has `is_admin = 1` flag set
- Is NOT prefixed with "demo_"
- Persists when demo data is created
- Persists when demo data is cleared
- Cannot be accidentally deleted through the UI

### 3. **Security Measures**
- Password minimum length: 8 characters (enforced both client and server-side)
- Argon2ID password hashing (industry best practice)
- Email format validation
- Username pattern validation (alphanumeric + underscores/hyphens)
- Session-based authentication
- Automatic login after installation reduces friction

### 4. **Automated Configuration**
The installation wizard now creates the `.env` configuration file automatically with customizable settings:
- **Application Name**: Customizable app name (default: CAFFEINECRASH)
- **Application URL**: Auto-detected from request or custom URL
- **Database Path**: Location of SQLite database (default: data/caffeinecrash.db)
- **Session Configuration**: Custom session name and lifetime
- **Debug Mode**: Toggle for development vs production

If a `.env` file already exists, it will be preserved and not overwritten.

### 5. **Installation State Management**
Installation completion is tracked via the `settings` table:
```sql
SELECT setting_value FROM settings WHERE setting_key = 'installation_complete'
```
This ensures the installation wizard only runs once.

### 6. **Demo Mode Compatibility**
Demo users are created with the "demo_" prefix:
- `demo_patient`
- `demo_caregiver`

When clearing demo data:
```sql
DELETE FROM users WHERE username LIKE 'demo_%'
```
This SQL query ensures only demo users are deleted, preserving the admin account.

## File Structure

### New Files
- `src/Installation.php` - Installation logic class
- `public/install.php` - Installation wizard UI

### Modified Files
- `public/index.php` - Added installation check
- `public/login.php` - Added installation check
- `public/register.php` - Added installation check
- `public/demo.php` - Added installation check
- `README.md` - Updated installation instructions
- `ADMIN.md` - Updated admin documentation
- `QUICKSTART.md` - Updated quick start guide
- `.gitignore` - Added data directories

## Testing

The installation process has been thoroughly tested:

✅ Fresh installation redirects to wizard
✅ Admin user creation with validation
✅ Auto-login after installation
✅ Install page redirects when already installed
✅ Demo data creation preserves admin user
✅ Demo data clearing preserves admin user
✅ All entry points check installation status

## Database Schema

### Users Table
```sql
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(100) UNIQUE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    is_admin BOOLEAN DEFAULT 0,      -- Admin user has is_admin = 1
    is_active BOOLEAN DEFAULT 1,
    totp_secret VARCHAR(32),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### Settings Table
```sql
CREATE TABLE IF NOT EXISTS settings (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    setting_key VARCHAR(100) UNIQUE NOT NULL,
    setting_value TEXT NOT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Installation complete flag
INSERT INTO settings (setting_key, setting_value)
VALUES ('installation_complete', '1');
```

## Usage

### For Developers
When deploying CAFFEINECRASH:
1. Clone the repository
2. Create `data` and `sessions` directories (optional - created automatically)
3. Start the PHP server
4. Navigate to the URL
5. Complete the installation wizard with admin credentials and configuration
6. The wizard will create `.env` file automatically with your settings

### For Users
1. Access the application URL
2. Fill in the admin account details
3. Configure application settings (or use the defaults)
4. Click "Complete Installation"
5. Start using CAFFEINECRASH!

**Note:** The installation wizard now handles creating the `.env` configuration file automatically. You no longer need to manually copy `.env.example` to `.env`.

## Troubleshooting

### Installation page keeps showing
- Check that the `data` directory has write permissions
- Ensure the SQLite database can be created
- Verify the settings table exists

### Cannot create admin user
- Ensure username and email are unique
- Check password meets minimum requirements (8+ characters)
- Verify database is writable

### Database permission errors
```bash
chmod 755 data sessions
chmod 644 data/caffeinecrash.db  # After creation
```

## Future Enhancements

Potential improvements to the installation process:
- Multi-step wizard with progress indicator
- Database connection testing
- System requirements check
- Email configuration during installation
- Optional sample data creation
- Installation health check page
- ~~Environment configuration during installation~~ ✅ **Implemented!**
