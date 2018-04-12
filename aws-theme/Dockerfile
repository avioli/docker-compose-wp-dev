FROM composer:1.6

MAINTAINER Evo Stamatov "evo@ionata.com.au"

# COPY config.json "$COMPOSER_HOME/"
# RUN composer global require ionata/composer-aws -vv

VOLUME ["/project"]
WORKDIR /project

COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["composer", "install"]
