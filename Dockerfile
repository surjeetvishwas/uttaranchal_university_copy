# Multi-stage build for optimized frontend serving
FROM nginx:alpine

# Copy custom nginx configuration
COPY nginx.conf /etc/nginx/nginx.conf

# Copy website files to nginx document root
COPY . /usr/share/nginx/html/

# Remove unnecessary files from production image
RUN rm -f /usr/share/nginx/html/Dockerfile \
    && rm -f /usr/share/nginx/html/docker-compose.yml \
    && rm -f /usr/share/nginx/html/.dockerignore \
    && rm -f /usr/share/nginx/html/database.json \
    && rm -rf /usr/share/nginx/html/.git* \
    && rm -rf /usr/share/nginx/html/node_modules

# Set proper permissions for nginx
RUN chown -R nginx:nginx /usr/share/nginx/html \
    && chmod -R 755 /usr/share/nginx/html

# Create directory for nginx logs
RUN mkdir -p /var/log/nginx \
    && touch /var/log/nginx/access.log \
    && touch /var/log/nginx/error.log \
    && chown -R nginx:nginx /var/log/nginx

# Expose port 8080 for Cloud Run compatibility
EXPOSE 8080

# Health check for container monitoring
HEALTHCHECK --interval=30s --timeout=10s --start-period=5s --retries=3 \
    CMD curl -f http://localhost:8080/ || exit 1

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
