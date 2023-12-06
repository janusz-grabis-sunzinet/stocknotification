FROM frameworkspl/dockware_nosource:latest
#COPY src/files /var/www/html/files
COPY src/public /var/www/html/public
#COPY src/var /var/www/html/var
#COPY src/vendor /var/www/html/vendor
RUN sudo chown -R www-data: /var/www/html
RUN sudo apt-get update --fix-missing
RUN sudo apt-get install -qq -y wget

#Installing mailhog's replacment of sendmail (based on: https://kinsta.com/blog/mailhog/#configuring-mailhog-with-mhsendmail)
RUN cd /tmp && sudo wget https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
RUN cd /tmp && sudo chmod +x mhsendmail_linux_amd64
RUN sudo mv /tmp/mhsendmail_linux_amd64 /usr/local/bin/mhsendmail