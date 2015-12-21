#!/bin/bash
#
export DEBIAN_FRONTEND=noninteractive

echo -e "\n--- Okay, installing now... ---\n"
echo -e "\n--- Updating packages list ---\n"

echo -e "\n--- Updating packages list ---\n"
sudo apt-get -qq update;

echo -e "\n--- Installing Composer for PHP package management ---\n"
curl --silent https://getcomposer.org/installer | php > /dev/null 2>&1
sudo mv composer.phar /usr/local/bin/composer

echo -e "\n--- Installing NodeJS and NPM ---\n"
sudo apt-get -y install nodejs > /dev/null 2>&1
curl --silent https://npmjs.org/install.sh | sudo sh > /dev/null 2>&1
sudo npm update -g npm  > /dev/null 2>&1

echo -e "\n--- Installing javascript components ---\n"
sudo npm install -g grunt-cli bower > /dev/null 2>&1

echo -e "\n--- Restarting Nginx and PHP servers ---\n"
sudo service nginx restart > /dev/null 2>&1;
sudo service php5-fpm restart > /dev/null 2>&1;

##### CLEAN UP #####
echo -e "\n--- Cleaning up ---\n"
sudo dpkg --configure -a  > /dev/null 2>&1; # when upgrade or install doesnt run well (e.g. loss of connection) this may resolve quite a few issues
sudo apt-get autoremove -y  > /dev/null 2>&1; # remove obsolete packages
