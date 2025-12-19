#PHP is built in a separate image from PM, so that we don't need to rebuild the entire Dockerfile whenever one src file changes
FROM ubuntu:22.04 AS build-php
ARG COMPILE_SH_ARGS="-f -g -P5"

RUN apt-get update && apt-get install --no-install-recommends -y curl ca-certificates build-essential m4 gzip bzip2 bison git cmake autoconf automake pkg-config libtool libtool-bin re2c

RUN mkdir /opt/pocketmine
COPY --chmod=700 ./build/php/compile.sh /opt/pocketmine
WORKDIR /opt/pocketmine
RUN ./compile.sh -t linux64 -j ${THREADS:-$(grep -E ^processor /proc/cpuinfo | wc -l)} ${COMPILE_SH_ARGS}

FROM ubuntu:22.04 AS build-pocketmine
ARG GIT_HASH=""
RUN test ! -z ${GIT_HASH} || (echo "Missing build-arg GIT_HASH" && false)

RUN apt-get update && apt-get install --no-install-recommends -y curl ca-certificates 7zip
RUN mkdir /opt/pocketmine
COPY --from=build-php /opt/pocketmine/bin /opt/pocketmine/bin
RUN ln -s /opt/pocketmine/bin/php7/bin/php /usr/bin/php
RUN curl -L https://getcomposer.org/installer | php
RUN mv composer.phar /usr/bin/composer

COPY ./build/server-phar.php ./build/server-phar-stub.php /opt/pocketmine/repo/build/
COPY ./resources /opt/pocketmine/repo/resources
COPY ./src /opt/pocketmine/repo/src
COPY ./composer.json ./composer.lock /opt/pocketmine/repo/

WORKDIR /opt/pocketmine/repo
RUN composer install --classmap-authoritative --no-dev --prefer-dist
RUN php vendor/pocketmine/bedrock-data/.minify_json.php
RUN php -dphar.readonly=0 ./build/server-phar.php --git ${GIT_HASH}
# Just to make sure the build script didn't false-positive-exit
RUN test -f /opt/pocketmine/repo/PocketMine-MP.phar

FROM ubuntu:22.04
LABEL maintainer="PMMP Team <team@pmmp.io>"

RUN apt-get update && apt-get install --no-install-recommends -y ca-certificates wget

RUN groupadd -g 1000 pocketmine
RUN useradd -r --no-create-home -u 1000 -g pocketmine pocketmine

WORKDIR /opt/pocketmine
COPY --from=build-php /opt/pocketmine/bin /opt/pocketmine/bin
RUN ln -s /opt/pocketmine/bin/php7/bin/php /usr/bin/php
COPY --from=build-pocketmine /opt/pocketmine/repo/PocketMine-MP.phar PocketMine-MP.phar
ADD ./docker/start.sh /usr/bin/start-pocketmine

RUN mkdir /plugins /data
RUN chown 1000:1000 /plugins /data . -R
RUN chmod o+x /usr/bin/php

USER pocketmine

ENV TERM=xterm

EXPOSE 19132/tcp
EXPOSE 19132/udp
EXPOSE 19133/udp

VOLUME ["/data", "/plugins"]

CMD ["start-pocketmine"]
