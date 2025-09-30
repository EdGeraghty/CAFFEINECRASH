# Admin Panel Documentation

## Overview

The CAFFEINECRASH admin panel provides comprehensive system management capabilities including user administration, system logs, analytics, and multi-factor authentication.

## Accessing the Admin Panel

### Making a User an Admin

By default, newly registered users are not admins. To make a user an admin, update the database directly:

```bash
sqlite3 public/data/caffeinecrash.db "UPDATE users SET is_admin = 1 WHERE username = 'your_username';"
```

### Login Process

1. Log in to CAFFEINECRASH with your admin credentials
2. Click the "Admin" link in the main navigation
3. If you have TOTP enabled, you'll be prompted to enter your 6-digit code
4. Access the admin dashboard

## Features

### Dashboard
- View system statistics at a glance
- Monitor total users, active users, and admin count
- Track medications, health records, and reminders
- Check database size
- See recent user signups

### User Management
- **View Users**: Browse all registered users with pagination
- **Toggle Admin Status**: Promote or demote users to/from admin role
- **Activate/Deactivate**: Enable or disable user accounts
- **Reset Passwords**: Reset a user's password to a new value
- **Delete Users**: Permanently remove users from the system
- **View Statistics**: See each user's activity (medications, health data, reminders)

**Note**: You cannot modify your own admin status or deactivate your own account for security.

### System Logs
- **View Logs**: Browse all system logs with timestamps
- **Filter by Level**: Show only specific log levels (debug, info, warning, error, critical)
- **Filter by User**: View logs for a specific user ID
- **IP Tracking**: See the IP address and user agent for each log entry
- **Context Data**: Expand log entries to view additional context information

### Analytics
- **User Metrics**: Total users, active users, new signups
- **Content Metrics**: Medications, health records, reminders counts
- **Growth Charts**: User growth over the last 6 months
- **Top Medications**: Most commonly tracked medications
- **Health Data Distribution**: Breakdown of health data by type
- **Log Statistics**: Distribution of logs by severity level
- **Database Size**: Current database file size

### MFA Settings (TOTP)
- **Enable TOTP**: Set up two-factor authentication for your admin account
- **QR Code**: Scan with any TOTP app (Google Authenticator, Authy, Microsoft Authenticator, etc.)
- **Manual Entry**: Use the secret key to manually configure your authenticator app
- **Disable TOTP**: Remove MFA from your account (requires current TOTP code)

## Security Features

### Access Control
- Only users with `is_admin = 1` can access the admin panel
- Inactive users (`is_active = 0`) cannot log in
- Admin routes use `require_admin()` which checks both admin status and login

### TOTP/MFA
- When enabled, admins must provide a 6-digit TOTP code on each login
- Codes are time-based and rotate every 30 seconds
- Supports a 1-period window for clock drift (±30 seconds)
- QR codes generated using Google Charts API for easy setup

### Logging
- All admin actions are logged with user ID and IP address
- Security-relevant events are logged at appropriate levels:
  - INFO: TOTP enabled/disabled, successful logins
  - WARNING: Password resets, user deletions
  - ERROR: Failed operations

## Best Practices

1. **Enable MFA**: All admin accounts should have TOTP enabled for enhanced security
2. **Regular Monitoring**: Check logs regularly for suspicious activity
3. **User Cleanup**: Deactivate or delete inactive user accounts
4. **Password Policies**: Reset passwords for compromised accounts immediately
5. **Analytics Review**: Monitor analytics to understand system usage patterns

## Technical Details

### Database Schema Additions
```sql
-- Users table additions
ALTER TABLE users ADD COLUMN is_admin BOOLEAN DEFAULT 0;
ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT 1;
ALTER TABLE users ADD COLUMN totp_secret VARCHAR(32);

-- Logs table
CREATE TABLE logs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    level VARCHAR(20) NOT NULL,
    message TEXT NOT NULL,
    context TEXT,
    user_id INTEGER,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
```

### New PHP Classes
- **TOTP**: Pure PHP TOTP implementation (RFC 6238)
- **AdminUser**: User management operations
- **Logger**: System logging with multiple severity levels
- **Analytics**: Usage statistics and metrics calculation

### Admin Routes
- `/admin/` - Main dashboard
- `/admin/users.php` - User management
- `/admin/logs.php` - System logs viewer
- `/admin/analytics.php` - Analytics dashboard
- `/admin/mfa-settings.php` - TOTP configuration
- `/admin/totp-verify.php` - MFA verification page

## Troubleshooting

### Cannot Access Admin Panel
- Verify user has `is_admin = 1` in database
- Ensure user account is active (`is_active = 1`)
- Check that you're logged in
- If TOTP is enabled, ensure you're entering the correct code

### TOTP Code Not Working
- Verify your device's clock is accurate
- Ensure you're using the most recent code (refreshes every 30 seconds)
- Check that you scanned the correct QR code
- Try manually entering the secret key

### Database Path Issues
- The admin panel uses absolute paths to prevent duplicate databases
- All paths are resolved via `PROJECT_ROOT` constant
- Database location: `public/data/caffeinecrash.db`

## Future Enhancements

The admin panel is designed to be extensible. Potential additions include:
- Email notifications for admin actions
- Advanced user search and filtering
- Bulk user operations
- System health monitoring
- Backup and restore functionality
- API rate limiting configuration
- Custom admin roles and permissions
