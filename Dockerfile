# Simple nginx setup for Cloud Run
FROM nginx:alpine

# Copy website files to nginx document root
COPY . /usr/share/nginx/html/

# Create a simple nginx config that works with default setup
RUN echo 'server { \
    listen 8080; \
    server_name _; \
    root /usr/share/nginx/html; \
    index result.html contact.html; \
    \
    location / { \
        try_files $uri $uri/ /result.html; \
    } \
    \
    location /health { \
        return 200 "healthy\\n"; \
        add_header Content-Type text/plain; \
    } \
}' > /etc/nginx/conf.d/default.conf

# Remove unnecessary files
RUN rm -f /usr/share/nginx/html/Dockerfile \
    && rm -f /usr/share/nginx/html/docker-compose.yml \
    && rm -f /usr/share/nginx/html/.dockerignore \
    && rm -f /usr/share/nginx/html/nginx.conf \
    && rm -f /usr/share/nginx/html/nginx-dev.conf \
    && rm -f /usr/share/nginx/html/start.sh \
    && rm -f /usr/share/nginx/html/deploy.ps1 \
    && rm -f /usr/share/nginx/html/database.json

# Expose port 8080
EXPOSE 8080

# Start nginx
CMD ["nginx", "-g", "daemon off;"]
