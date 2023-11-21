FROM frameworkspl/dockware_nosource:latest
RUN sudo apt-get update --fix-missing
RUN sudo apt-get install -qq -y wget

#Installing mailhog's replacment of sendmail (based on: https://kinsta.com/blog/mailhog/#configuring-mailhog-with-mhsendmail)
RUN cd /tmp && sudo wget https://github.com/mailhog/mhsendmail/releases/download/v0.2.0/mhsendmail_linux_amd64
RUN cd /tmp && sudo chmod +x mhsendmail_linux_amd64
RUN sudo mv /tmp/mhsendmail_linux_amd64 /usr/local/bin/mhsendmail