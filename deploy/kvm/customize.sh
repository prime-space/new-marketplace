sed -i 's/enforcing/disabled/g' /etc/selinux/config /etc/selinux/config
cat <<EOT > /etc/sysctl.d/70-ipv6.conf
net.ipv6.conf.all.disable_ipv6 = 1
net.ipv6.conf.default.disable_ipv6 = 1
EOT
dnf -y update
dnf -y install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm
dnf -y install https://rpms.remirepo.net/enterprise/remi-release-8.rpm
dnf -y module enable php:remi-7.3
curl --silent --location https://dl.yarnpkg.com/rpm/yarn.repo | tee /etc/yum.repos.d/yarn.repo
curl -sL https://rpm.nodesource.com/setup_14.x | sudo bash -
dnf -y install sudo wget git nginx mysql supervisor cronie nodejs yarn php php-json php-bcmath php-gd php-zip php-mysql

mkdir /run/php-fpm
mkdir /run/supervisor

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php --install-dir=/usr/local/bin --filename=composer
rm -f composer-setup.php

wget http://sphinxsearch.com/files/sphinx-3.1.1-612d99f-linux-amd64-glibc2.12.tar.gz
tar -xf sphinx-3.1.1-612d99f-linux-amd64-glibc2.12.tar.gz -C /opt

mkdir /var/log/app
chmod 777 /var/log/app
