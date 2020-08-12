FROM composer:1.10

# MAINTAINER Evo Stamatov "evo@ionata.com.au"

VOLUME ["/project"]
WORKDIR /project

COPY docker-entrypoint.sh /docker-entrypoint.sh
ENTRYPOINT ["/docker-entrypoint.sh"]

CMD ["composer", "install"]
