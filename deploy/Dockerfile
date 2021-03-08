FROM crocusgroup/worker:0.2

COPY deploy/default.nginx.conf /etc/nginx/conf.d
COPY . /var/www/html
RUN dnf -y install sudo
RUN cat /var/www/html/deploy/supervisord.conf >> /etc/supervisord.conf
RUN mkdir -p /var/log/app/marketplace
RUN chmod 777 /var/log/app/marketplace
RUN cp /var/www/html/deploy/sphinx.conf /opt/sphinx-3.1.1/etc
RUN chmod +x /var/www/html/deploy/sphinx-init.sh
