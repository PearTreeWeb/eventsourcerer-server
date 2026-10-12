FROM dunglas/frankenphp:1-builder-php8.5-bookworm AS builder

# Copy xcaddy in the builder image
COPY --from=caddy:builder /usr/bin/xcaddy /usr/bin/xcaddy

# CGO must be enabled to build FrankenPHP
RUN export CGO_ENABLED=1 \
    GOSUMDB=off \
    XCADDY_SETCAP=1 \
    XCADDY_GO_BUILD_FLAGS="-ldflags='-w -s' -trimpath -tags=nobadger,nomysql,nopgx" \
    CGO_CFLAGS=$(php-config --includes) \
    CGO_LDFLAGS="$(php-config --ldflags) $(php-config --libs)" \
    && for i in 1 2 3 4 5; do \
        GOPROXY=https://proxy.golang.org,direct \
        GOFLAGS=-mod=mod \
        xcaddy build \
            --output /usr/local/bin/frankenphp \
            --with github.com/dunglas/frankenphp=./ \
            --with github.com/dunglas/frankenphp/caddy=./caddy/ \
            --with github.com/dunglas/caddy-cbrotli \
            --with github.com/baldinof/caddy-supervisor \
        && break || { echo "xcaddy build failed (attempt $i), retrying with GOPROXY=direct..."; GOPROXY=direct xcaddy build --output /usr/local/bin/frankenphp --with github.com/dunglas/frankenphp=./ --with github.com/dunglas/frankenphp/caddy=./caddy/ --with github.com/dunglas/caddy-cbrotli --with github.com/baldinof/caddy-supervisor && break || sleep 5; }; \
    done

#FROM dunglas/frankenphp:latest AS runner
FROM dunglas/frankenphp:php8.5-bookworm AS runner

COPY --from=builder /usr/local/bin/frankenphp /usr/local/bin/frankenphp

RUN for i in 1 2 3 4 5; do \
        rm -rf /var/lib/apt/lists/* && \
        apt-get -o Acquire::Max-FutureTime=86400 -o Acquire::Check-Valid-Until=false -o Acquire::AllowInsecureRepositories=true -o Acquire::AllowDowngradeToInsecureRepositories=true update --fix-missing && break || { echo "apt-get update failed (attempt $i), retrying..."; sleep 5; }; \
    done \
    && apt-get install -y --no-install-recommends --allow-unauthenticated \
    acl \
    build-essential \
    cron \
    gifsicle \
    git \
    gpg \
    jpegoptim \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libmagickwand-dev \
    libonig-dev \
    libpng-dev \
    libpq-dev \
    librabbitmq-dev \
    libxslt1-dev \
    libzip-dev \
    locales \
    optipng \
    pngquant \
    procps \
    supervisor \
    vim \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /var/cache/apt/archives/*.deb /tmp/* /var/tmp/*

# AMQP
RUN pecl install amqp
RUN docker-php-ext-enable amqp

# Redis
RUN printf "\n" | pecl install redis \
&& docker-php-ext-enable redis

# Install non default extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg

RUN install-php-extensions \
    bcmath \
    exif \
    gd \
    opcache \
    pcntl \
    pdo_pgsql \
    soap \
    zip \
    xsl

## Install Composer
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php -r "if (hash_file('sha384', 'composer-setup.php') === 'c8b085408188070d5f52bcfe4ecfbee5f727afa458b2573b8eaaf77b3419b0bf2768dc67c86944da1544f06fa544fd47') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer

RUN cp $PHP_INI_DIR/php.ini-development $PHP_INI_DIR/php.ini && \
    sed -i 's/memory_limit = .*/memory_limit = 2000M/' $PHP_INI_DIR/php.ini

WORKDIR /app

COPY . .

RUN composer install --no-interaction --optimize-autoloader

COPY docker/php/entrypoint.sh /usr/local/bin/docker-entrypoint
RUN chmod +x /usr/local/bin/docker-entrypoint

ENTRYPOINT ["docker-entrypoint"]

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]
