#!/bin/bash
set -e

# Render dynamically assigns $PORT (default to 10000)
PORT="${PORT:-10000}"
echo "Configuring Apache to listen on port ${PORT}..."

# Update Apache port bindings
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf

# Reverse Proxy & Port 10000 fix in Apache configuration
cat << 'EOF' > /etc/apache2/conf-available/reverse-proxy.conf
# Prevent Apache from appending internal port (:10000) on canonical redirects
UseCanonicalName Off
UseCanonicalPhysicalPort Off
ServerName localhost

# Enable AllowOverride for web root so .htaccess runs
<Directory /var/www/html>
    Options -Indexes +FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>
EOF

a2enconf reverse-proxy

# Set proper ownership and permissions
chown -R www-data:www-data /var/www/html
chmod -R 755 /var/www/html

echo "Starting Apache on port ${PORT}..."
exec apache2-foreground
