rm -rf /sphinxdata;
mkdir /var/lib/sphinx;
mkdir /var/run/sphinx;
/opt/sphinx-3.1.1/bin/searchd --config /var/www/html/deploy/sphinx.conf;
php /var/www/html/bin/console sphinx:init;
tail -f /dev/null;
