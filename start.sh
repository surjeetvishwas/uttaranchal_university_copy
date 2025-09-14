#!/bin/sh

# Startup script for Cloud Run deployment
echo "Starting Uttaranchal University Application..."

# Print nginx version and configuration info
echo "Nginx version:"
nginx -v

# Test nginx configuration
echo "Testing nginx configuration..."
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid"
else
    echo "❌ Nginx configuration error"
    exit 1
fi

# Print configuration details
echo "Nginx configuration file:"
ls -la /etc/nginx/nginx.conf

# Print document root contents
echo "Document root contents:"
ls -la /usr/share/nginx/html/

# Check if required files exist
if [ -f "/usr/share/nginx/html/index.html" ]; then
    echo "✅ index.html found"
else
    echo "❌ index.html missing"
fi

if [ -f "/usr/share/nginx/html/result.html" ]; then
    echo "✅ result.html found"
else
    echo "❌ result.html missing"
fi

if [ -f "/usr/share/nginx/html/admin.html" ]; then
    echo "✅ admin.html found"
else
    echo "❌ admin.html missing"
fi

# Start nginx
echo "Starting nginx..."
exec nginx -g "daemon off;"