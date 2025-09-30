# Copilot Instructions for CAFFEINECRASH

## Project Overview
CAFFEINECRASH is a Progressive Web App (PWA) for medication and health tracking, built with PHP and SQLite. The application allows users to manage medications, track health metrics, set reminders, and share information with others. It features a comprehensive admin panel with TOTP-based MFA.

## Technology Stack
- **Backend**: PHP 8.4+
- **Database**: SQLite3 with PDO
- **Frontend**: Vanilla HTML, CSS, JavaScript
- **Authentication**: Session-based with Argon2ID password hashing
- **PWA**: Service worker with offline support

## Architecture & Project Structure

```
CAFFEINECRASH/
├── public/              # Web root - all publicly accessible files
│   ├── admin/          # Admin panel pages (requires admin auth)
│   ├── includes/       # PHP includes (header, admin-header)
│   ├── css/           # Stylesheets
│   ├── js/            # JavaScript files
│   ├── bootstrap.php  # Application bootstrap and helper functions
│   ├── *.php          # Application pages
│   ├── manifest.json  # PWA manifest
│   └── sw.js          # Service worker
├── src/                # PHP classes (PSR-4 autoloaded as App\)
│   ├── Database.php   # Singleton database with schema init
│   ├── Auth.php       # Authentication & authorization
│   ├── Medication.php # Medication management
│   ├── HealthData.php # Health data tracking
│   ├── Reminder.php   # Reminders system
│   ├── Share.php      # Data sharing between users
│   ├── AdminUser.php  # Admin user management
│   ├── Analytics.php  # Admin analytics
│   ├── Logger.php     # System logging
│   ├── Settings.php   # System settings
│   ├── TOTP.php       # TOTP 2FA implementation
│   └── DemoData.php   # Demo data generator
├── data/               # SQLite database (auto-created)
├── sessions/           # Session files (auto-created)
└── vendor/             # Composer autoloader
```

## Coding Standards & Conventions

### PHP Code Style
- **Namespace**: All classes use `App\` namespace (PSR-4)
- **Type Hints**: Always use strict type hints for parameters and return types
- **PDO**: Use prepared statements for ALL database queries
- **Error Handling**: Catch PDOException for database operations
- **Session**: Access via `$_SESSION` global after bootstrap

### Security Practices
1. **Input Sanitization**: Use `sanitize()` function for ALL output to HTML
   ```php
   <?= sanitize($variable) ?>
   ```

2. **SQL Injection Prevention**: ALWAYS use prepared statements with bound parameters
   ```php
   $stmt = $this->db->prepare("SELECT * FROM table WHERE id = :id");
   $stmt->execute(['id' => $id]);
   ```

3. **Password Hashing**: Use `password_hash()` with `PASSWORD_ARGON2ID`
   ```php
   $hash = password_hash($password, PASSWORD_ARGON2ID);
   ```

4. **Authentication**: Use helper functions from bootstrap.php
   ```php
   require_login();    // For user pages
   require_admin();    // For admin pages
   ```

### Database Patterns
- **Singleton Pattern**: Use `Database::getInstance()` to get PDO instance
- **Schema Auto-Init**: Database schema is created automatically in `Database::initSchema()`
- **Foreign Keys**: Use CASCADE for delete operations
- **Indexes**: Create indexes for commonly queried columns
- **Timestamps**: Use `CURRENT_TIMESTAMP` for created_at/updated_at fields

### Page Structure Pattern
Every page should follow this pattern:
```php
<?php require_once 'bootstrap.php'; 
require_login(); // or require_admin()

$auth = new \App\Auth();
$userId = $auth->getCurrentUserId();

// Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Process form submission
}

// Fetch data for display
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page Title - CAFFEINECRASH</title>
    <link rel="stylesheet" href="/css/style.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#4CAF50">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <div class="container">
        <!-- Page content -->
    </div>
    
    <script src="/js/app.js"></script>
</body>
</html>
```

### Class Patterns

#### Service Classes
All service classes (in `src/`) should:
- Accept database connection via constructor: `$this->db = Database::getInstance()`
- Use type hints for all parameters and return types
- Return `false` or `null` on failure, not throw exceptions
- Use associative arrays for data transfer

Example:
```php
<?php
namespace App;

class Example {
    private \PDO $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function create(int $userId, array $data): int|false {
        $stmt = $this->db->prepare("
            INSERT INTO table (user_id, field) VALUES (:user_id, :field)
        ");
        
        $result = $stmt->execute([
            'user_id' => $userId,
            'field' => $data['field']
        ]);
        
        return $result ? (int)$this->db->lastInsertId() : false;
    }
    
    public function getById(int $id, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT * FROM table WHERE id = :id AND user_id = :user_id
        ");
        $stmt->execute(['id' => $id, 'user_id' => $userId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }
}
```

### Form Handling
- Use POST method for state-changing operations
- Use hidden input for action type: `<input type="hidden" name="action" value="add">`
- Validate and sanitize all input
- Display success/error messages using `$message` and `$error` variables
- Use null coalescing for optional fields: `$_POST['field'] ?? ''`

### User Data Isolation
ALWAYS filter by `user_id` in queries to ensure users can only access their own data:
```php
SELECT * FROM medications WHERE id = :id AND user_id = :user_id
```

### Admin Features
- Admin pages must be in `public/admin/` directory
- Use `require_admin()` to enforce admin access
- Admin pages use `includes/admin-header.php`
- TOTP verification is required for admin actions if enabled
- Use `Logger::log()` for admin actions

## Common Tasks & Patterns

### Adding a New Feature
1. Create service class in `src/` with `App\` namespace
2. Create database table in `Database::initSchema()`
3. Create public page in `public/` following page structure pattern
4. Add navigation link in `includes/header.php` or `includes/admin-header.php`
5. Ensure user data isolation with `user_id` filtering

### Adding Database Columns
- Use ALTER TABLE in `Database::initSchema()` with `IF NOT EXISTS` checks
- Update corresponding service class methods
- Update form inputs and validation

### API/AJAX Endpoints
Use `json_response()` helper:
```php
json_response(['success' => true, 'data' => $data]);
```

## Testing & Development

### Running the Application
```bash
cd public
php -S localhost:8000
```

### Demo Mode
- Available at `/demo.php`
- Creates sample data for testing
- Credentials: `demo_patient` / `demo123` or `demo_caregiver` / `demo123`

### Database Location
- Development: `public/data/caffeinecrash.db`
- Configure via `.env` file (DB_PATH variable)

## Important Notes

### DO
- ✅ Always use prepared statements
- ✅ Always sanitize output with `sanitize()`
- ✅ Filter queries by `user_id` for data isolation
- ✅ Use type hints consistently
- ✅ Follow the established page structure pattern
- ✅ Use helper functions from bootstrap.php
- ✅ Handle both success and error cases
- ✅ Use CASCADE for foreign key deletes
- ✅ Log admin actions with Logger class

### DON'T
- ❌ Never concatenate user input in SQL queries
- ❌ Never output unescaped data to HTML
- ❌ Never use `echo` in service classes (return values instead)
- ❌ Never skip user_id filtering in queries
- ❌ Never use sessions directly in service classes
- ❌ Never hardcode database credentials (use .env)
- ❌ Don't add npm/node dependencies (vanilla JS only)
- ❌ Don't use frameworks (this is a vanilla PHP app)

## PWA Specifics
- Service worker is in `public/sw.js`
- Manifest is in `public/manifest.json`
- Cache static assets for offline use
- Register service worker in pages that need offline support

## Admin Panel
- Comprehensive user management (view, edit, activate/deactivate, delete)
- System logs with filtering and search
- Analytics dashboard with usage metrics
- TOTP-based MFA for enhanced security
- Settings management (registration toggle, etc.)

## License & Contributing
- Licensed under YOLO Public License (YPL) v0.12.34-hunter.2
- This project is "LLM-only" and "vibecoded"
- Focus on pragmatic solutions over perfect architecture
