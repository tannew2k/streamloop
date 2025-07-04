FROM php:8.2-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    curl \
    ffmpeg \
    proxychains4 \
    supervisor \
    cron

# Install extensions
RUN curl -sSLf -o /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions gd zip pdo_mysql mysqli opcache sockets redis

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy existing application directory contents
COPY . /var/www

# Set working directory
WORKDIR /var/www

# Setup supervisor
RUN mkdir -p /var/log/supervisor
RUN mkdir -p /var/run/supervisor

# Copy supervisor configuration
COPY .docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
RUN touch /var/www/storage/logs/worker.log

RUN ls -la /usr/sbin/

# Copy entrypoint script
COPY .docker/web.entrypoint.sh /usr/local/bin/web.entrypoint.sh
RUN chmod +x /usr/local/bin/web.entrypoint.sh

# Remove unnecessary files
RUN rm -rf .docker Dockerfile docker-compose.yml .git .gitignore \
    _ide_helper.php _ide_helper_models.php \
    .lando.yml \
    .lando \
    laradumps.yaml

# Set permissions
RUN chown -R www-data:www-data /var/www

# Set up cron job
RUN (crontab -l ; echo "* * * * * cd /var/www && php artisan schedule:run >> /dev/null 2>&1") | crontab -

# Start cron service
# RUN service cron start

# Run composer install
RUN composer install --no-interaction

# Expose port 9000 and start php-fpm server
EXPOSE 9000

ENTRYPOINT ["/usr/local/bin/web.entrypoint.sh"]

# docker build -t laravel-app -f .docker/web.Dockerfile .
# docker run -d -p 9000:9000 laravel-app