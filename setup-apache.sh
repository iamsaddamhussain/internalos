#!/bin/bash

# InternalOS Apache Configuration Setup Script
# This script configures Apache to serve the Laravel app at the root URL

echo "==================================="
echo "InternalOS Apache Setup"
echo "==================================="
echo ""

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root (use sudo)"
    exit 1
fi

echo "Step 1: Enabling Apache mod_rewrite..."
a2enmod rewrite
echo "✓ mod_rewrite enabled"
echo ""

echo "Step 2: Backing up current Apache configuration..."
if [ -f /etc/apache2/sites-available/000-default.conf ]; then
    cp /etc/apache2/sites-available/000-default.conf /etc/apache2/sites-available/000-default.conf.backup.$(date +%Y%m%d_%H%M%S)
    echo "✓ Backup created"
fi
echo ""

echo "Step 3: Copying new Apache configuration..."
cp /var/www/html/internalos/internalos-apache.conf /etc/apache2/sites-available/000-default.conf
echo "✓ Configuration copied"
echo ""

echo "Step 4: Setting proper permissions..."
chown -R www-data:www-data /var/www/html/internalos/storage
chown -R www-data:www-data /var/www/html/internalos/bootstrap/cache
chmod -R 775 /var/www/html/internalos/storage
chmod -R 775 /var/www/html/internalos/bootstrap/cache
echo "✓ Permissions set"
echo ""

echo "Step 5: Testing Apache configuration..."
if apache2ctl configtest; then
    echo "✓ Configuration is valid"
    echo ""
    
    echo "Step 6: Restarting Apache..."
    systemctl restart apache2
    
    if [ $? -eq 0 ]; then
        echo "✓ Apache restarted successfully"
        echo ""
        echo "==================================="
        echo "Setup Complete!"
        echo "==================================="
        echo ""
        echo "Your application is now accessible at:"
        echo "http://160.250.204.218"
        echo ""
        echo "Make sure to:"
        echo "1. Update your .env APP_URL to: http://160.250.204.218"
        echo "2. Run: cd /var/www/html/internalos && php artisan config:clear"
        echo "3. Run: cd /var/www/html/internalos && php artisan config:cache"
    else
        echo "✗ Failed to restart Apache"
        exit 1
    fi
else
    echo "✗ Configuration test failed"
    exit 1
fi
