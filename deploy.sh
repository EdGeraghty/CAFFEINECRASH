#!/bin/bash

# CAFFEINECRASH Fly.io Deployment Script

echo "🚀 Deploying CAFFEINECRASH to Fly.io..."

# Check if flyctl is installed
if ! command -v flyctl &> /dev/null; then
    echo "❌ flyctl is not installed. Please install it first:"
    echo "   Visit: https://fly.io/docs/hands-on/install-flyctl/"
    exit 1
fi

# Check if user is logged in
if ! flyctl auth whoami &> /dev/null; then
    echo "🔑 Please log in to Fly.io first:"
    echo "   flyctl auth login"
    exit 1
fi

# Create the app if it doesn't exist
echo "📝 Checking if app exists..."
if ! flyctl status --app caffeinecrash &> /dev/null; then
    echo "🆕 Creating new Fly.io app..."
    flyctl apps create caffeinecrash --generate-name=false
fi

# Create volume if it doesn't exist
echo "💾 Setting up persistent volume..."
flyctl volumes list --app caffeinecrash | grep -q caffeinecrash_data || {
    echo "Creating data volume (contains both database and sessions)..."
    flyctl volumes create caffeinecrash_data --region iad --size 1 --app caffeinecrash
}

# Deploy the application
echo "🚀 Deploying application..."
flyctl deploy --app caffeinecrash

# Show the status
echo "✅ Deployment complete!"
echo "🌐 Your app should be available at: https://caffeinecrash.fly.dev"
echo "📊 Check status with: flyctl status --app caffeinecrash"
echo "📜 View logs with: flyctl logs --app caffeinecrash"

echo ""
echo "🔧 Next steps:"
echo "1. Visit your app and complete the installation wizard"
echo "2. Create your admin account"
echo "3. Configure your application settings"
echo ""
echo "🔒 Security reminder:"
echo "- Change default passwords immediately"
echo "- Enable TOTP 2FA for admin accounts"
echo "- Review user registration settings"
