#!/bin/bash

cp /root/appoptics.so /usr/lib/php/20151012/

cat <<EOT > /etc/php/7.0/fpm/conf.d/25-appoptics.ini
extension=appoptics.so
[appoptics]
; appoptics.service_key: unique service identifier
appoptics.service_key = "$APPOPTICS_SERVICE_KEY"
EOT

