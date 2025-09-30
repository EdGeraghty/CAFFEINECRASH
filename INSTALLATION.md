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

### 4. **Installation State Management**
Installation completion is tracked via the `settings` table:
```sql
SELECT setting_value FROM settings WHERE setting_key = 'installation_complete'
```
This ensures the installation wizard only runs once.

### 5. **Demo Mode Compatibility**
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
2. Copy `.env.example` to `.env`
3. Create `data` and `sessions` directories
4. Start the PHP server
5. Navigate to the URL
6. Complete the installation wizard

### For Users
1. Access the application URL
2. Fill in the installation form
3. Click "Complete Installation"
4. Start using CAFFEINECRASH!

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
