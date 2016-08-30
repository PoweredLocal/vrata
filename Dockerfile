FROM dusterio/ubuntu-php7:latest

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

EXPOSE 80

RUN rm /etc/nginx/sites-enabled/default
ADD ci/site.conf /etc/nginx/sites-enabled/site.conf

# Use baseimage-docker's init process.
ENTRYPOINT ["/bin/sh", "/start.sh"]
