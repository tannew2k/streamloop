FROM nginx
COPY .docker/nginx.conf /etc/nginx/nginx.conf

# Expose port 80
EXPOSE 80

# docker build -t nginx -f .docker/nginx.Dockerfile .