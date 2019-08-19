#!/bin/bash

apt-get -y install gpg

echo 'deb http://rep.logentries.com/ bionic main' > /etc/apt/sources.list.d/logentries.list
gpg --keyserver pgp.mit.edu --recv-keys A5270289C43C79AD && gpg -a --export A5270289C43C79AD | apt-key add -
apt-get -y update
apt-get -y install python-setproctitle logentries
le reinit --user-key=${LOGGING_LOGENTRIES} --pull-server-side-config=False

cat >> /etc/le/config << EOF
[nginx]                                                                                                                                                                  
path = /var/log/nginx/access.log                                                                                                                                         
destination = ${LOGGING_ID}/nginx                                                                                                                                          
                                                                                                                                                                         
[app]                                                                                                                                                                    
path = /home/app/storage/logs/lumen.log                                                                                                                                  
destination = ${LOGGING_ID}/app       
EOF

apt-get -y install logentries-daemon
le register
service logentries start
