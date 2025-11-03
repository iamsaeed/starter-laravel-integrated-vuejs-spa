#!/bin/bash
# .starter/setup.sh

set -e

echo "🚀 Setting up new project from Laravel Vue Starter..."
echo ""

# Get project details
read -p "New project name: " PROJECT_NAME
read -p "GitHub repo URL (e.g., git@github.com:org/project.git): " REPO_URL
read -p "Starter repo URL [git@github.com:your-org/laravel-vue-starter.git]: " STARTER_URL
STARTER_URL=${STARTER_URL:-git@github.com:your-org/laravel-vue-starter.git}

echo ""
echo "📋 Summary:"
echo "  Project: $PROJECT_NAME"
echo "  Repo: $REPO_URL"
echo "  Starter: $STARTER_URL"
echo ""
read -p "Continue? (y/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "❌ Setup cancelled"
    exit 0
fi

# Remove old git history
echo "🗑️  Removing starter git history..."
rm -rf .git

# Initialize new repo
echo "📦 Initializing new repository..."
git init
git add .
git commit -m "Initial commit from Laravel Vue Starter"

# Add remotes
echo "🔗 Adding git remotes..."
git remote add origin "$REPO_URL"
git remote add starter "$STARTER_URL"

# Create main branch
git branch -M main

echo ""
echo "✅ Git setup complete!"
echo ""
echo "📝 Next steps:"
echo "  1. Push to GitHub:"
echo "     git push -u origin main"
echo ""
echo "  2. Install dependencies:"
echo "     composer install"
echo "     npm install"
echo ""
echo "  3. Setup environment:"
echo "     cp .env.example .env"
echo "     php artisan key:generate"
echo ""
echo "  4. Setup database:"
echo "     php artisan migrate --seed"
echo ""
echo "  5. Start developing:"
echo "     composer run dev"
echo ""
echo "💡 To get updates from starter later:"
echo "   ./.starter/update.sh"
