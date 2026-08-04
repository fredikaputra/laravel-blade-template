FROM oven/bun:alpine AS assets

WORKDIR /build

COPY package.json bun.lock ./
RUN bun install --frozen-lockfile

COPY . .

RUN bun run build

FROM dunglas/frankenphp:php8.5-trixie AS deps

COPY composer.json composer.lock ./
RUN apt-get update \
    && apt-get install -y --no-install-recommends unzip \
    && curl -fsSL https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer install --no-dev --prefer-dist --no-scripts --no-autoloader --no-progress --no-interaction

COPY . .
RUN composer install --no-dev --prefer-dist --no-interaction \
    && composer dump-autoload --optimize --classmap-authoritative

FROM dunglas/frankenphp:php8.5-trixie

ARG APP_KEY
ARG APP_URL
ARG ASSET_URL
ARG DB_DATABASE
ARG DB_USERNAME
ARG DB_PASSWORD

ENV APP_KEY=$APP_KEY \
    APP_URL=$APP_URL \
    ASSET_URL=$ASSET_URL \
    DB_CONNECTION=pgsql \
    DB_DATABASE=$DB_DATABASE \
    DB_HOST=127.0.0.1 \
    DB_NAME=$DB_DATABASE \
    DB_PASSWORD=$DB_PASSWORD \
    DB_PORT=5432 \
    DB_USER=$DB_USERNAME \
    DB_USERNAME=$DB_USERNAME \
    PGDATA=/var/lib/postgresql/data

RUN install-php-extensions \
    pdo_pgsql \
    pcntl

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        postgresql \
        supervisor \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

COPY --from=deps /app /app
COPY --from=assets /build/public/build /app/public/build

RUN test -n "$APP_KEY" && test -n "$APP_URL" && test -n "$ASSET_URL" && test -n "$DB_DATABASE" && test -n "$DB_USERNAME" && test -n "$DB_PASSWORD" || { echo 'ERROR: all build args are required: APP_KEY, APP_URL, ASSET_URL, DB_DATABASE, DB_USERNAME, DB_PASSWORD'; exit 1; }

RUN php artisan optimize

RUN rm -rf \
    .devcontainer \
    .dockerignore \
    .editorconfig \
    .env.example \
    .gitattributes \
    .github \
    .gitignore \
    Dockerfile \
    bun.lock \
    compose.yaml \
    composer.json \
    composer.lock \
    config \
    package.json \
    phpstan.neon \
    phpunit.xml \
    pint.json \
    rector.php \
    routes \
    tests \
    vite.config.ts

RUN <<'EOF'
mkdir -p /etc/supervisor/conf.d /var/lib/postgresql/data /var/log/supervisor

cat > /etc/supervisor/conf.d/supervisord.conf <<'SUPER'
[supervisord]
nodaemon=true

[program:postgresql]
command=/usr/lib/postgresql/17/bin/postgres -D /var/lib/postgresql/data
user=postgres
autostart=true
autorestart=true
environment=PGDATA=/var/lib/postgresql/data

[program:laravel]
command=php /app/artisan octane:frankenphp --host=0.0.0.0 --port=80
directory=/app
autostart=true
autorestart=true
SUPER

cat > /usr/local/bin/docker-entrypoint.sh <<'ENTRY'
#!/usr/bin/env bash

if [ ! -s "$PGDATA/PG_VERSION" ]; then
    su postgres -c "/usr/lib/postgresql/17/bin/initdb -D $PGDATA"
fi

/usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf &
SUP_PID=$!

sleep 5
su postgres -c "psql -c \"CREATE USER $DB_USER WITH PASSWORD '$DB_PASSWORD';\" 2>&1 || true"
su postgres -c "psql -c \"CREATE DATABASE $DB_NAME OWNER $DB_USER;\" 2>&1 || true"

cd /app || exit 1
php artisan migrate --force
php artisan octane:reload

wait "$SUP_PID"
ENTRY

chown -R postgres:postgres /var/lib/postgresql
chmod +x /usr/local/bin/docker-entrypoint.sh
EOF

ENTRYPOINT ["docker-entrypoint.sh"]
