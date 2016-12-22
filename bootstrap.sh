#!/bin/bash

DB_USER='root'
DB_PORT='3306'
DB_PASSWORD='root'
DB_NAME='phalcon_starter'

echo "Repository update..."
echo vagrant | sudo -S apt-get update > /dev/null 2>&1

echo "Installing mc..."
apt-get install -y mc 2> /dev/null

echo "Installing git..."
echo vagrant | sudo apt-get install git -y > /dev/null 2>&1

echo "Installing nginx..."
echo vagrant | sudo apt-get install -y nginx > /dev/null 2>&1

echo "Setting nginx..."
echo vagrant | sudo rm -rf /usr/share/nginx/html
echo vagrant | sudo ln -s /vagrant/public /usr/share/nginx/html
echo vagrant | sudo -S cp /vagrant/nginx.config /etc/nginx/sites-available/default
echo vagrant | sudo -S service nginx restart

echo "Installing mysql 5.7..."
debconf-set-selections <<< "mysql-server mysql-server/root_password password $DB_PASSWORD"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password $DB_PASSWORD"
echo vagrant | sudo apt-get install software-properties-common
echo vagrant | sudo add-apt-repository -y ppa:ondrej/mysql-5.7 2> /dev/null
echo vagrant | sudo apt-get update
echo vagrant | sudo apt-get install -y mysql-server 2> /dev/null
echo vagrant | sudo -S sed -i "s/^bind-address.*127.0.0.1/bind-address=0.0.0.0/" /etc/mysql/my.cnf

echo "Creating DB..."
mysql -uroot -p$DB_PASSWORD -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8 COLLATE utf8_general_ci;" >> /vagrant/vm_build.log 2>&1

echo "Installing php7"
echo vagrant | sudo -S apt-get purge php.* > /dev/null 2>&1
echo vagrant | sudo -S apt-add-repository ppa:ondrej/php > /dev/null 2>&1
echo vagrant | sudo -S apt-get update > /dev/null 2>&1
echo vagrant | sudo -S apt-get install php7.0 php7.0-fpm php7.0-xml php7.0-xsl php7.0-intl php-pear php7.0-dev php-memcache memcached php7.0-mysql php7.0-pgsql php-curl php7.0-zip php7.0-mongodb -y > /dev/null 2>&1
echo vagrant | sudo -S sed -i 's@user = www-data@user = vagrant@g' /etc/php/7.0/fpm/pool.d/www.conf
echo vagrant | sudo -S sed -i 's@group = www-data@group = vagrant@g' /etc/php/7.0/fpm/pool.d/www.conf
echo vagrant | sudo -S sed -i 's@;date.timezone =@date.timezone = "UTC"@g' /etc/php/7.0/cli/php.ini
echo vagrant | sudo -S pecl install xdebug > /dev/null 2>&1
echo vagrant | sudo -S sed -i '$ a\zend_extension=/usr/lib/php/20151012/xdebug.so' /etc/php/7.0/fpm/php.ini
echo vagrant | sudo -S sed -i '$ a\zend_extension=/usr/lib/php/20151012/xdebug.so' /etc/php/7.0/cli/php.ini
echo vagrant | sudo -S sed -i '$ a\xdebug.remote_enable=1' /etc/php/7.0/fpm/php.ini
echo vagrant | sudo -S sed -i '$ a\xdebug.remote_connect_back=1' /etc/php/7.0/fpm/php.ini

echo "Installing php-mbstring"
echo vagrant | sudo apt-get install php-mbstring

echo "Installing phalcon"
echo vagrant | sudo -S curl -s https://packagecloud.io/install/repositories/phalcon/stable/script.deb.sh | sudo bash > /dev/null 2>&1
echo vagrant | sudo -S apt-get install php7.0-phalcon > /dev/null 2>&1
echo vagrant | sudo -S service php7.0-fpm restart > /dev/null 2>&1

echo "Installing composer..."
curl -sS https://getcomposer.org/installer | php > /dev/null 2>&1
echo vagrant | sudo -S mv composer.phar /usr/local/bin/composer > /dev/null 2>&1

echo "Installing app..."
sudo cp /vagrant/set-github-oauth-token.sh.sample /vagrant/set-github-oauth-token.sh
if [ -f /vagrant/set-github-oauth-token.sh ]
then
    /vagrant/set-github-oauth-token.sh
fi

echo "Installing composer..."
cd /vagrant
sudo composer install > /dev/null 2>&1

pwd
echo "Installing Phalcon Developer Tools..."
git clone git://github.com/phalcon/phalcon-devtools.git
cd phalcon-devtools/
./phalcon.sh
sudo rm -rf /usr/bin/phalcon
sudo ln -s /vagrant/phalcon-devtools/phalcon.php /usr/bin/phalcon
chmod ugo+x /usr/bin/phalcon
cd ..

pwd
echo "Create cache or logs"
sudo mkdir app/cache
sudo mkdir app/logs

pwd
echo "migration run"
echo vagrant | sudo phalcon migration run  > /dev/null 2>&1
phalcon migration list

pwd
echo "Installing codecept"
echo vagrant | php vendor/bin/codecept
echo vagrant | php vendor/bin/codecept bootstrap