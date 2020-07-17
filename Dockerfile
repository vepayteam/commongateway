FROM php:7.2.32-apache-buster

LABEL maintainer="Vadims I <vivolgin@vepay.online>"

ARG TIMEZONE=Europe/Moscow

ENV DEBIAN_FRONTEND=noninteractive
ENV APACHE_RUN_USER=#1000
ENV APACHE_RUN_GROUP=#1000
ENV APACHE_HTTP_PORT=8000
ENV APACHE_HTTPS_PORT=8443
ENV APACHE_DOCUMENT_ROOT=/www/

COPY . /www/

RUN set -ex \
    && curl -sS -o /etc/ssl/cacert.pem https://curl.haxx.se/ca/cacert.pem \
    && cp /usr/local/etc/php/php.ini-production /usr/local/etc/php/php.ini \
    && sed -ri -e "s,^short_open_tag = Off,short_open_tag = On," /usr/local/etc/php/php.ini \
    && sed -ri -e "s,;date.timezone =,date.timezone = ${TIMEZONE}," /usr/local/etc/php/php.ini \
    && sed -ri -e "s,;curl.cainfo =,curl.cainfo = "/etc/ssl/cacert.pem"," /usr/local/etc/php/php.ini \
    && sed -ri -e "s,;openssl.cafile =,openssl.cafile = "/etc/ssl/cacert.pem"," /usr/local/etc/php/php.ini \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && sed -ri -e "s!:80!${APACHE_HTTP_PORT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!:443!${APACHE_HTTPS_PORT}!g" /etc/apache2/sites-available/*.conf \
    && a2dismod mpm_event mpm_worker ssl status \
    && a2enmod rewrite charset_lite headers \
    && a2enconf docker-php \
    && apache2ctl configtest \
    && /usr/local/bin/docker-php-ext-enable opcache \
    \
    && apt-get update \
    && apt-get install -y npm nodejs git mariadb-client \
    \
    && npm install uglify-es clean-css-cli -g \
    && curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
    && php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer \
    && /usr/bin/composer global require "fxp/composer-asset-plugin:^1.4.1" \
    \
    && chmod +x /www/yii \
    && chmod +x /www/init \
    && /www/init --env=test \
    && /www/yii cache/flush-all --interactive 0 \
    \
    && apt-get remove -y npm nodejs git \
    && apt-get autoremove --purge -y \
    && rm -rf /var/lib/apt/lists/* \
              /etc/apt/sources.list.d/*.list \
              /www/node_modules/ \
              /root/.composer \
    \
    && mkdir -p /www/key/ \
    && echo -n '1234567890' > /www/key/key.txt \
    \
    && mkdir -p /www/web/assets \
    && mkdir -p /www/web/shopdata \
    && chmod -R g-w /www \
    && chmod -R g+w /www/runtime \
    && chmod -R g+w /www/web/assets \
    && chmod -R g+w /www/web/shopdata \
    && chown -R 1000:1000 /www/
#     && echo $'\n\
# <?php\n\
# \n\
# return [\n\
# 'class' => 'yii\db\Connection',\n\
# 'dsn' => 'mysql:host='+getenv("MYSQL_HOST", "mysql")+';port='+getenv("MYSQL_PORT", 3306)+';dbname='+getenv("MYSQL_DB", "vepay"),\n\
# 'username' => getenv("MYSQL_USER", "vepay"),\n\
# 'password' => getenv("MYSQL_PASSWORD", "vepay"),\n\
# 'charset' => 'utf8',\n\
# \n\
# // Schema cache options (for production environment)\n\
# //'enableSchemaCache' => true,\n\
# //'schemaCacheDuration' => 60,\n\
# //'schemaCache' => 'cache',	\n\
# ];\n' > /www/config/db.php

EXPOSE ${APACHE_HTTP_PORT}
USER 1000:1000
WORKDIR /www/