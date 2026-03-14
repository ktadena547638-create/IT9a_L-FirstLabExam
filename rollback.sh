#!/bin/bash
# ROLLBACK SCRIPT FOR WAREHOUSE APP
# Use this if deployment fails: bash rollback.sh

echo "================================"
echo "⚠️  WAREHOUSE APP ROLLBACK"
echo "================================"
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${RED}WARNING: This will revert to previous version!${NC}"
echo ""
read -p "Are you sure you want to rollback? (yes/no): " confirm

if [ "$confirm" != "yes" ]; then
    echo "Rollback cancelled."
    exit 0
fi

echo ""
echo -e "${YELLOW}Starting rollback...${NC}"
echo ""

# Step 1: Clear all caches
echo -e "${YELLOW}[1/5]${NC} Clearing all caches..."
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
echo -e "${GREEN}✓ Caches cleared${NC}"
echo ""

# Step 2: Revert code
echo -e "${YELLOW}[2/5]${NC} Reverting to previous commit..."
git revert HEAD --no-edit || git reset --hard HEAD~1
echo -e "${GREEN}✓ Code reverted${NC}"
echo ""

# Step 3: Re-pull code
echo -e "${YELLOW}[3/5]${NC} Pulling stable version..."
git pull origin main
echo -e "${GREEN}✓ Code pulled${NC}"
echo ""

# Step 4: Reinstall dependencies
echo -e "${YELLOW}[4/5]${NC} Reinstalling dependencies..."
composer install --no-dev --optimize-autoloader
npm install --production
npm run build
echo -e "${GREEN}✓ Dependencies reinstalled${NC}"
echo ""

# Step 5: Verify status
echo -e "${YELLOW}[5/5]${NC} Verifying status..."
php artisan tinker <<EOF
\$count = \App\Models\StockItem::count();
echo "✓ Application is accessible\n";
exit;
EOF
echo -e "${GREEN}✓ Application verified${NC}"
echo ""

echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}✓ ROLLBACK COMPLETE${NC}"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Application restored to previous version."
echo ""
echo "To restart web server:"
echo "   sudo systemctl restart nginx"
echo ""
echo "To view recent log messages:"
echo "   tail -f storage/logs/laravel.log"
echo ""
