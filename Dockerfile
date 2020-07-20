FROM registry.vepay.cf/apache-php

LABEL maintainer="Vadims I <vivolgin@vepay.online>"

COPY . ${APACHE_DOCUMENT_ROOT}/

RUN set -ex \
    && rm -rf ${APACHE_DOCUMENT_ROOT}/vendor \
    && composer --working-dir="${APACHE_DOCUMENT_ROOT}/" --ansi --no-interaction install \
    \
    && chmod +x ${APACHE_DOCUMENT_ROOT}/yii \
    && chmod +x ${APACHE_DOCUMENT_ROOT}/init \
    && ${APACHE_DOCUMENT_ROOT}/init --env=test \
    && ${APACHE_DOCUMENT_ROOT}/yii cache/flush-all --interactive 0 \
    \
    && sed -ri -e 's!host=localhost!host=0.0.0.0!g' config/db.php \
    && sed -ri -e "s!'password' => ''!'password' => 'vepay'!g" config/db.php \
    \
    && mkdir -p ${APACHE_DOCUMENT_ROOT}/key/ \
    && echo -n '1234567890' > ${APACHE_DOCUMENT_ROOT}/key/key.txt \
    \
    && mkdir -p ${APACHE_DOCUMENT_ROOT}/web/assets \
    && mkdir -p ${APACHE_DOCUMENT_ROOT}/web/shopdata \
    && chmod -R g-w ${APACHE_DOCUMENT_ROOT} \
    && chmod -R g+w ${APACHE_DOCUMENT_ROOT}/runtime \
    && chmod -R g+w ${APACHE_DOCUMENT_ROOT}/web/assets \
    && chmod -R g+w ${APACHE_DOCUMENT_ROOT}/web/shopdata \
    && /docker-entrypoint.d/cleanup.bash

USER ${RUN_USER}:${RUN_GROUP}