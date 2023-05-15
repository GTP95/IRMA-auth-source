FROM silintl/ssp-base
COPY . /data/vendor/simplesamlphp/simplesamlphp/modules/IRMAidp/
WORKDIR /data/vendor/simplesamlphp/simplesamlphp/modules/IRMAidp
# COMPOSER_ALLOW_SUPERUSER is already inside local.env file, but somehow doesn't work
RUN export COMPOSER_ALLOW_SUPERUSER=1; composer install --no-dev
WORKDIR /data
EXPOSE 80/tcp
ENTRYPOINT ["/usr/local/bin/s3-expand"]
CMD ["/data/run.sh"]
