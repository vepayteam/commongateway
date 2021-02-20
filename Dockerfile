#FROM registry.vepay.cf/apache-php as vendor
#
#ARG COMPOSER_VERSION=1.10.16
#ENV COMPOSER_VERSION=${COMPOSER_VERSION}
#
#COPY composer.json composer.lock ${APACHE_DOCUMENT_ROOT}/
#
#RUN set -ex \
#    && apt-get update \
#    && apt-get install -yq git unzip \
#    && curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php \
#    && php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer --version=${COMPOSER_VERSION} \
#    && /usr/bin/composer global require "fxp/composer-asset-plugin:^1.4.6" \
#    && composer --working-dir="${APACHE_DOCUMENT_ROOT}/" --ansi --no-interaction --no-cache update \
#    && composer --working-dir="${APACHE_DOCUMENT_ROOT}/" --ansi --no-interaction --no-cache install
#
#FROM registry.vepay.cf/apache-php as assets
#
#COPY --from=vendor ${APACHE_DOCUMENT_ROOT}/vendor ${APACHE_DOCUMENT_ROOT}/vendor
#COPY config ${APACHE_DOCUMENT_ROOT}/config
#COPY assets ${APACHE_DOCUMENT_ROOT}/assets
#COPY web/insasset ${APACHE_DOCUMENT_ROOT}/web/insasset
#COPY web/aassets ${APACHE_DOCUMENT_ROOT}/web/aassets
#COPY web/swagger ${APACHE_DOCUMENT_ROOT}/web/swagger
#COPY web/payasset ${APACHE_DOCUMENT_ROOT}/web/payasset
#COPY init yii assets.php ${APACHE_DOCUMENT_ROOT}/
#COPY web/index.php ${APACHE_DOCUMENT_ROOT}/web/
#
#RUN set -ex \
#    && apt-get update \
#    && apt-get install -yq \
#                        git \
#                        nodejs \
#                        npm \
#                        unzip \
#    \
#    && cd ${APACHE_DOCUMENT_ROOT} \
#    && npm install uglify-es clean-css-cli -g \
#    && php init --env=prod \
#    && mkdir -p web/assets \
#    && php yii asset assets.php config/assets-prod.php \
#    && chown -R ${RUN_USER}:${RUN_GROUP} web/
#### @TODO Intermidate containers enable when VF comes out

FROM registry.vepay.cf/apache-php

LABEL maintainer="Vadims I <vivolgin@vepay.online>"

ARG ENVIRONMENT=dev

ENV ENVIRONMENT ${ENVIRONMENT}

COPY --chown=${RUN_USER}:${RUN_GROUP} . ${APACHE_DOCUMENT_ROOT}/

RUN set -ex \
    && cd ${APACHE_DOCUMENT_ROOT} \
    && php init --env=${ENVIRONMENT} \
    && php yii cache/flush-all --interactive 0 \
    \
    && mkdir -p key/ && echo -n '1234567890' > key/key.txt \
    \
    && mkdir -p web/shopdata \
    && mkdir -p runtime/logs/console \
    && chmod -R g-w . \
    && chmod -R g+w runtime \
    && chmod -R g+w web/shopdata

USER ${RUN_USER}:${RUN_GROUP}