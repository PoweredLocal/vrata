FROM dusterio/ubuntu-php7.2:latest

# Install memcached & xdebug extensions
# The latter is only necessary for test coverage, it will be disabled later on
RUN apt-get -y update
RUN apt-get -y -o DPkg::Options::="--force-confold" install php-memcached php-xdebug php-sqlite3 php-pgsql

# All files will be chowned later
ENV CHOWN_TO_USER=www-data

# Set correct environment variables.
RUN mkdir -p /home/app
ADD app /home/app/app
ADD artisan /home/app/
ADD bootstrap /home/app/bootstrap
ADD config /home/app/config
ADD database /home/app/database
ADD public /home/app/public
ADD resources /home/app/resources
ADD storage /home/app/storage
ADD vendor /home/app/vendor
ADD tests/ /home/app/tests
ADD phpunit.xml /home/app/
ADD ci/start.sh /
ADD ci/logs-logentries.sh /root/

EXPOSE 80

RUN rm /etc/nginx/sites-enabled/default
ADD ci/site.conf /etc/nginx/sites-enabled/site.conf
ADD ci/log.conf /etc/nginx/conf.d/log.conf

# Use baseimage-docker's init process.
ENTRYPOINT ["/bin/sh", "/start.sh"]
