#!/bin/bash
# DEPLOYMENT SCRIPT FOR WAREHOUSE APP
# Run this after pulling code: bash deploy.sh

echo "================================"
echo "🚀 WAREHOUSE APP DEPLOYMENT"
echo "================================"
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Dependencies
echo -e "${YELLOW}[1/10]${NC} Installing dependencies..."
composer install --no-dev --optimize-autoloader
npm install --production
echo -e "${GREEN}✓ Dependencies installed${NC}"
echo ""

# Step 2: Environment setup
echo -e "${YELLOW}[2/10]${NC} Configuring environment..."
if [ ! -f .env ]; then
    echo -e "${RED}ERROR: .env file not found!${NC}"
    echo "Copy .env from secure location and try again:"
    echo "  cp /secure/location/.env ."
    exit 1
fi
echo -e "${GREEN}✓ .env file exists${NC}"
echo ""

# Step 3: Verify APP_DEBUG is false
if grep -q "APP_DEBUG=true" .env; then
    echo -e "${RED}WARNING: APP_DEBUG=true in .env! This is unsafe in production!${NC}"
    echo "Set APP_DEBUG=false before deployment!"
    exit 1
fi
echo -e "${GREEN}✓ APP_DEBUG is false${NC}"
echo ""

# Step 4: Build assets
echo -e "${YELLOW}[3/10]${NC} Building production assets..."
npm run build
echo -e "${GREEN}✓ Assets built${NC}"
echo ""

# Step 5: Database migration
echo -e "${YELLOW}[4/10]${NC} Running database migrations..."
php artisan migrate --force
echo -e "${GREEN}✓ Database migrated${NC}"
echo ""

# Step 6: Cache config
echo -e "${YELLOW}[5/10]${NC} Caching configuration..."
php artisan config:cache
echo -e "${GREEN}✓ Configuration cached${NC}"
echo ""

# Step 7: Cache routes
echo -e "${YELLOW}[6/10]${NC} Caching routes..."
php artisan route:cache
echo -e "${GREEN}✓ Routes cached${NC}"
echo ""

# Step 8: Cache views
echo -e "${YELLOW}[7/10]${NC} Caching views..."
php artisan view:cache
echo -e "${GREEN}✓ Views cached${NC}"
echo ""

# Step 9: Optimize
echo -e "${YELLOW}[8/10]${NC} Optimizing autoloader..."
php artisan optimize
echo -e "${GREEN}✓ Autoloader optimized${NC}"
echo ""

# Step 10: Verify deployment
echo -e "${YELLOW}[9/10]${NC} Verifying deployment..."
php artisan tinker <<EOF
\$count = \App\Models\StockItem::count();
echo "✓ Database connection successful (Found \$count items)\n";
exit;
EOF
echo -e "${GREEN}✓ Database connection verified${NC}"
echo ""

echo -e "${YELLOW}[10/10]${NC} Deployment complete!"
echo ""
echo -e "${GREEN}================================${NC}"
echo -e "${GREEN}🎉 DEPLOYMENT SUCCESSFUL!"
echo -e "${GREEN}================================${NC}"
echo ""
echo "Next steps:"
echo "1. Restart your web server:"
echo "   sudo systemctl restart nginx"
echo ""
echo "2. Test the application at your domain"
echo ""
echo "3. Monitor logs:"
echo "   tail -f storage/logs/laravel.log"
echo ""
echo "Need to rollback? Run:"
echo "   bash rollback.sh"
echo ""
