#!/bin/bash
function pause(){
 read -s -n 1 -p "Press any key to continue ..."
 echo ""
}

echo "ReVoiceChat-MediaServer installer"
pause

echo "Installing Apache2 and PHP ..."
sudo apt-get install apache2-utils apache2 php libapache2-mod-php php-json php-zip php-curl php-gd -y

echo "Configuring Apache2 ..."
sudo a2enmod headers
sudo a2enmod rewrite
sudo cp rvc_media.exemple.conf /etc/apache2/sites-available/rvc_media.conf
sudo a2ensite rvc_media.conf

echo "Enabling Apache2 ..."
sudo systemctl enable apache2
sudo systemctl restart apache2

echo "Creating log directory ..."
sudo mkdir -p /var/log/rvc
sudo chown www-data /var/log/rvc
sudo chgrp www-data /var/log/rvc

echo "Changing permissions on 'data' directory ..."
sudo chown www-data ./www/data
sudo chgrp www-data ./www/data

echo "Done."