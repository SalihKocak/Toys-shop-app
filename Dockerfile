# Build context MUST be the app root (folder containing composer.json, public/, src/).
# On Render: set "Root Directory" to this folder if your repo has the app in a subfolder.

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock* ./
RUN composer install --no-dev --prefer-dist --no-interaction --optimize-autoloader --no-scripts \
    --ignore-platform-req=ext-mongodb

FROM php:8.2-cli

RUN apt-get update \
    && apt-get install -y --no-install-recommends libssl-dev pkg-config unzip \
    && pecl install mongodb \
    && docker-php-ext-enable mongodb \
    && apt-get purge -y --auto-remove \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .

# Ensure script has LF line endings and is executable (for Linux)
RUN sed -i 's/\r$//' scripts/render-start.sh 2>/dev/null || true \
    && chmod +x scripts/render-start.sh \
    && mkdir -p storage/logs storage/sessions public/uploads \
    && touch storage/logs/app.log \
    && chmod -R 775 storage public/uploads

ENV APP_ENV=production
ENV PORT=10000

EXPOSE 10000

HEALTHCHECK --interval=30s --timeout=5s --start-period=20s --retries=3 \
  CMD php -r "exit(@file_get_contents('http://127.0.0.1:' . (getenv('PORT') ?: '10000') . '/healthz') ? 0 : 1);"

CMD ["sh", "./scripts/render-start.sh"]
