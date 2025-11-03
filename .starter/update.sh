#!/bin/bash
# .starter/update.sh

set -e

STARTER_REMOTE="starter"
STARTER_BRANCH="main"

echo "🔄 Laravel Vue Starter - Update Tool"
echo ""

# Check if starter remote exists
if ! git remote get-url $STARTER_REMOTE > /dev/null 2>&1; then
    echo "❌ Starter remote not found!"
    echo ""
    read -p "Add starter remote? (Y/n) " -n 1 -r
    echo

    if [[ ! $REPLY =~ ^[Nn]$ ]]; then
        read -p "Starter repo URL: " STARTER_URL
        git remote add $STARTER_REMOTE "$STARTER_URL"
        echo "✅ Starter remote added"
    else
        exit 1
    fi
fi

# Fetch latest
echo "📥 Fetching updates from starter..."
git fetch $STARTER_REMOTE

# Check if there are updates
LOCAL=$(git rev-parse HEAD)
REMOTE=$(git rev-parse $STARTER_REMOTE/$STARTER_BRANCH)

if [ "$LOCAL" = "$REMOTE" ]; then
    echo "✅ Already up to date!"
    exit 0
fi

# Show changes
echo ""
echo "📋 Updates available in starter:"
echo ""
git log --oneline --graph --decorate HEAD..$STARTER_REMOTE/$STARTER_BRANCH --max-count=20

echo ""
echo "📊 Files that will be affected:"
git diff --name-status HEAD..$STARTER_REMOTE/$STARTER_BRANCH | head -30

echo ""
read -p "Continue with merge? (y/N) " -n 1 -r
echo

if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "⏭️  Update cancelled"
    exit 0
fi

# Create backup branch
BACKUP_BRANCH="backup-before-update-$(date +%Y%m%d-%H%M%S)"
git branch $BACKUP_BRANCH
echo "💾 Created backup branch: $BACKUP_BRANCH"

# Stash any uncommitted changes
if ! git diff-index --quiet HEAD --; then
    echo "💼 Stashing uncommitted changes..."
    git stash push -m "Auto-stash before starter update"
    STASHED=true
else
    STASHED=false
fi

# Merge
echo ""
echo "🔀 Merging starter updates..."
if git merge $STARTER_REMOTE/$STARTER_BRANCH --no-edit; then
    echo ""
    echo "✅ Merge successful!"

    # Restore stashed changes
    if [ "$STASHED" = true ]; then
        echo "📦 Restoring stashed changes..."
        git stash pop
    fi

    echo ""
    echo "📝 Post-update checklist:"
    echo "  [ ] composer install"
    echo "  [ ] npm install"
    echo "  [ ] npm run build"
    echo "  [ ] php artisan migrate"
    echo "  [ ] php artisan optimize:clear"
    echo "  [ ] php artisan test"
    echo "  [ ] npm run test"
    echo ""
    echo "💡 Backup branch available: $BACKUP_BRANCH"
else
    echo ""
    echo "⚠️  Merge conflicts detected!"
    echo ""
    echo "📝 Resolve conflicts:"
    echo "  1. Fix conflicts in files marked with <<<<<<"
    echo "  2. Run: git add ."
    echo "  3. Run: git commit"
    echo ""
    echo "🔧 Conflict resolution strategies:"
    echo "  Keep yours:    git checkout --ours path/to/file"
    echo "  Keep starter:  git checkout --theirs path/to/file"
    echo ""
    echo "❌ To abort merge:"
    echo "   git merge --abort"
    echo "   git checkout $BACKUP_BRANCH"
fi
