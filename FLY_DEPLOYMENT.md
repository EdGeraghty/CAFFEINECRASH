# Deploying CAFFEINECRASH to Fly.io

This guide will help you deploy your CAFFEINECRASH application to Fly.io.

## Prerequisites

1. **Install flyctl**: Visit [Fly.io Installation Guide](https://fly.io/docs/hands-on/install-flyctl/)
2. **Create a Fly.io account**: Sign up at [fly.io](https://fly.io/)
3. **Login to flyctl**: Run `flyctl auth login`

## Quick Deployment

### Option 1: Using the deployment script (recommended)

**On Windows (PowerShell):**
```powershell
.\deploy.ps1
```

**On Linux/Mac:**
```bash
chmod +x deploy.sh
./deploy.sh
```

### Option 2: Manual deployment

1. **Create the Fly.io app:**
   ```bash
   flyctl apps create caffeinecrash
   ```

2. **Create persistent volume:**
   ```bash
   # Single volume for both database and sessions
   flyctl volumes create caffeinecrash_data --region iad --size 1 --app caffeinecrash
   ```

3. **Deploy the application:**
   ```bash
   flyctl deploy --app caffeinecrash
   ```

## Configuration Details

### fly.toml Configuration

The `fly.toml` file contains the following key configurations:

- **App name**: `caffeinecrash`
- **Region**: `iad` (US East - Virginia)
- **Memory**: 256MB
- **Auto-scaling**: Enabled with minimum 0 machines
- **Persistent volume**: Single volume mounted at `/var/www/html/data` containing:
  - Database: `/var/www/html/data/database/`
  - Sessions: `/var/www/html/data/sessions/`

### Environment Variables

The application uses these environment variables (configured in fly.toml):

- `APP_NAME`: Application name
- `DEBUG`: Set to false for production
- `SESSION_NAME`: Custom session name
- `SESSION_LIFETIME`: Session duration (3600 seconds)
- `HASH_ALGO`: Password hashing algorithm

### Docker Configuration

The Dockerfile:
- Uses PHP 8.4 FPM with Nginx
- Installs SQLite3 support
- Sets up proper file permissions
- Configures Nginx with optimized settings for PHP applications
- Uses Supervisor to manage both Nginx and PHP-FPM processes
- Includes security headers and performance optimizations
- Uses production environment configuration

## Post-Deployment Setup

1. **Visit your application**: https://caffeinecrash.fly.dev
2. **Complete installation wizard**: Follow the setup process
3. **Create admin account**: Set up your administrative user
4. **Configure security**: 
   - Enable TOTP 2FA for admin accounts
   - Review user registration settings
   - Set strong passwords

## Useful Commands

### Monitoring
```bash
# Check application status
flyctl status --app caffeinecrash

# View logs
flyctl logs --app caffeinecrash

# Follow logs in real-time
flyctl logs --app caffeinecrash -f

# SSH into the container
flyctl ssh console --app caffeinecrash
```

### Scaling
```bash
# Scale to specific number of instances
flyctl scale count 2 --app caffeinecrash

# Scale memory
flyctl scale memory 512 --app caffeinecrash
```

### Updates
```bash
# Redeploy after changes
flyctl deploy --app caffeinecrash

# Deploy specific version
flyctl deploy --app caffeinecrash --image caffeinecrash:v1.0.0
```

### Database Management
```bash
# Connect to your app and access SQLite
flyctl ssh console --app caffeinecrash
sqlite3 /var/www/html/public/data/caffeinecrash.db
```

## Troubleshooting

### Common Issues

1. **Volume not mounting**: Check that volumes are created in the same region as your app
2. **Permission errors**: Verify that file permissions are set correctly in Dockerfile
3. **Database not persisting**: Ensure the volume is properly mounted to `/var/www/html/public/data`

### Debug Steps

1. **Check logs**: `flyctl logs --app caffeinecrash`
2. **Verify volumes**: `flyctl volumes list --app caffeinecrash`
3. **Check app status**: `flyctl status --app caffeinecrash`
4. **SSH into container**: `flyctl ssh console --app caffeinecrash`

### Support

- **Fly.io Documentation**: https://fly.io/docs/
- **Fly.io Community**: https://community.fly.io/
- **CAFFEINECRASH Issues**: Create an issue in your repository

## Security Considerations

- **Database**: SQLite database is stored on persistent volume
- **Sessions**: Session data is stored on persistent volume
- **HTTPS**: Automatically enabled by Fly.io
- **Environment**: Production environment variables are used
- **Access**: No sensitive data in environment variables (uses .env file)

## Backup Recommendations

1. **Regular snapshots**: Consider implementing database backup strategy
2. **Volume snapshots**: Fly.io doesn't provide automatic volume backups
3. **Export data**: Use the admin panel to export important data regularly

## Cost Optimization

- **Auto-scaling**: App scales to 0 when not in use
- **Resource allocation**: 256MB RAM should be sufficient for small to medium usage
- **Volume size**: 1GB volumes for data and sessions (increase as needed)

The free tier on Fly.io should be sufficient for testing and light production use.
