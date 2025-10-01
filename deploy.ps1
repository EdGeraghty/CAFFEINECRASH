# CAFFEINECRASH Fly.io Deployment Script for Windows

Write-Host "🚀 Deploying CAFFEINECRASH to Fly.io..." -ForegroundColor Green

# Check if flyctl is installed
if (-not (Get-Command flyctl -ErrorAction SilentlyContinue)) {
    Write-Host "❌ flyctl is not installed. Please install it first:" -ForegroundColor Red
    Write-Host "   Visit: https://fly.io/docs/hands-on/install-flyctl/" -ForegroundColor Yellow
    exit 1
}

# Check if user is logged in
try {
    flyctl auth whoami | Out-Null
} catch {
    Write-Host "🔑 Please log in to Fly.io first:" -ForegroundColor Yellow
    Write-Host "   flyctl auth login" -ForegroundColor Cyan
    exit 1
}

# Create the app if it doesn't exist
Write-Host "📝 Checking if app exists..." -ForegroundColor Cyan
try {
    flyctl status --app caffeinecrash | Out-Null
} catch {
    Write-Host "🆕 Creating new Fly.io app..." -ForegroundColor Green
    flyctl apps create caffeinecrash --generate-name=$false
}

# Create volume if it doesn't exist
Write-Host "💾 Setting up persistent volume..." -ForegroundColor Cyan

$volumes = flyctl volumes list --app caffeinecrash | Out-String

if (-not ($volumes -match "caffeinecrash_data")) {
    Write-Host "Creating data volume (contains both database and sessions)..." -ForegroundColor Yellow
    flyctl volumes create caffeinecrash_data --region iad --size 1 --app caffeinecrash
}

# Deploy the application
Write-Host "🚀 Deploying application..." -ForegroundColor Green
flyctl deploy --app caffeinecrash

# Show the status
Write-Host "✅ Deployment complete!" -ForegroundColor Green
Write-Host "🌐 Your app should be available at: https://caffeinecrash.fly.dev" -ForegroundColor Cyan
Write-Host "📊 Check status with: flyctl status --app caffeinecrash" -ForegroundColor Yellow
Write-Host "📜 View logs with: flyctl logs --app caffeinecrash" -ForegroundColor Yellow

Write-Host ""
Write-Host "🔧 Next steps:" -ForegroundColor Magenta
Write-Host "1. Visit your app and complete the installation wizard" -ForegroundColor White
Write-Host "2. Create your admin account" -ForegroundColor White
Write-Host "3. Configure your application settings" -ForegroundColor White
Write-Host ""
Write-Host "🔒 Security reminder:" -ForegroundColor Red
Write-Host "- Change default passwords immediately" -ForegroundColor White
Write-Host "- Enable TOTP 2FA for admin accounts" -ForegroundColor White
Write-Host "- Review user registration settings" -ForegroundColor White
