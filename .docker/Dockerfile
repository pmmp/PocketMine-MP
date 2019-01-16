FROM ubuntu:bionic
MAINTAINER PMMP Team <team@pmmp.io>

EXPOSE 19132/tcp
EXPOSE 19132/udp

RUN apt-get update && apt-get --no-install-recommends -y install \
	sudo \
	ca-certificates \
	jq \
	curl \
	unzip

RUN groupadd pocketmine && useradd -r -d /pocketmine -p "" -m -g pocketmine -g sudo pocketmine

WORKDIR /pocketmine
RUN chown -R pocketmine:pocketmine .

USER pocketmine
RUN echo Sleeping for 5 minutes to wait for Jenkins build && sleep 300

RUN mkdir tmp
RUN curl https://update.pmmp.io/api > tmp/api.json

RUN jq -r '.php_version' < tmp/api.json > tmp/PHP_VERSION
RUN curl -SsL \
		https://jenkins.pmmp.io/job/PHP-`cat tmp/PHP_VERSION`-Aggregate/lastSuccessfulBuild/artifact/PHP-`cat tmp/PHP_VERSION`-Linux-x86_64.tar.gz | \
	tar zx >/dev/null 2>&1

RUN chmod +x bin/php7/bin/php

RUN sed -i'.bak' "s/date.timezone=.*/date.timezone=$(date +%Z)/" bin/php7/bin/php.ini

RUN curl -SsLo PocketMine-MP.phar `jq -r '.download_url' < tmp/api.json`

RUN curl -SsLO https://raw.githubusercontent.com/pmmp/PocketMine-MP/`jq -r '.branch' < tmp/api.json`/start.sh
RUN chmod +x start.sh

RUN rm -r tmp

# RUN sudo mkdir /data /plugins
VOLUME ["/data", "/plugins"]
# CMD ./start.sh --no-wizard --enable-ansi --data=/data --plugins=/plugins
CMD sudo chgrp pocketmine /data /plugins -R && sudo chmod g+rw /data /plugins && ./start.sh --no-wizard --enable-ansi --data=/data --plugins=/plugins
