# Multi-stage build for optimized frontend serving
FROM nginx:alpine

# Install curl for health checks
RUN apk add --no-cache curl

# Remove default nginx config
RUN rm /etc/nginx/conf.d/default.conf

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy website files to nginx document root
COPY . /usr/share/nginx/html/

# Remove unnecessary files from production image (including nginx configs)
RUN rm -f /usr/share/nginx/html/Dockerfile \
    && rm -f /usr/share/nginx/html/docker-compose.yml \
    && rm -f /usr/share/nginx/html/.dockerignore \
    && rm -f /usr/share/nginx/html/database.json \
    && rm -f /usr/share/nginx/html/nginx.conf \
    && rm -f /usr/share/nginx/html/nginx-dev.conf \
    && rm -f /usr/share/nginx/html/start.sh \
    && rm -f /usr/share/nginx/html/deploy.ps1 \
    && rm -rf /usr/share/nginx/html/.git* \
    && rm -rf /usr/share/nginx/html/node_modules

# Set proper permissions for nginx
RUN chown -R nginx:nginx /usr/share/nginx/html \
    && chmod -R 755 /usr/share/nginx/html

# Create directory for nginx logs and set permissions
RUN mkdir -p /var/log/nginx \
    && touch /var/log/nginx/access.log \
    && touch /var/log/nginx/error.log \
    && chown -R nginx:nginx /var/log/nginx \
    && chmod 755 /var/log/nginx

# Verify nginx configuration
RUN nginx -t

# Expose port 8080 for Cloud Run compatibility
EXPOSE 8080

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
