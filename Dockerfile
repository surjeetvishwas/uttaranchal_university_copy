# Simple nginx setup for Cloud Run
FROM nginx:alpine

# Copy website files to nginx document root
COPY . /usr/share/nginx/html/

# Create a simple nginx config that works with default setup
RUN echo 'server { \
    listen 8080; \
    server_name _; \
    root /usr/share/nginx/html; \
    index index.html index.htm; \
    \
    # Add CORS headers for all requests \
    add_header Access-Control-Allow-Origin "*" always; \
    add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS" always; \
    add_header Access-Control-Allow-Headers "DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization" always; \
    \
    location / { \
        try_files $uri $uri/ /index.html; \
    } \
    \
    # Handle preflight requests \
    location ~* \.(json)$ { \
        if ($request_method = OPTIONS) { \
            add_header Access-Control-Allow-Origin "*"; \
            add_header Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"; \
            add_header Access-Control-Allow-Headers "DNT,User-Agent,X-Requested-With,If-Modified-Since,Cache-Control,Content-Type,Range,Authorization"; \
            add_header Access-Control-Max-Age 1728000; \
            add_header Content-Type "text/plain; charset=utf-8"; \
            add_header Content-Length 0; \
            return 204; \
        } \
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
