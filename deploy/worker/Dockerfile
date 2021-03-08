FROM centos:8
RUN dnf -y update

RUN dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
RUN dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
RUN dnf -y module enable php:remi-7.3

RUN dnf -y install wget nginx mysql supervisor cronie php php-json php-bcmath php-gd php-zip php-mysql

RUN mkdir /run/php-fpm
RUN mkdir /run/supervisor

RUN wget http://sphinxsearch.com/files/sphinx-3.1.1-612d99f-linux-amd64-glibc2.12.tar.gz
RUN tar -xf sphinx-3.1.1-612d99f-linux-amd64-glibc2.12.tar.gz -C /opt

RUN mkdir /var/log/app; chmod 777 /var/log/app
