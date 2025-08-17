# ReVoiceChat-MediaServer

Proof of concept for ReVoiceChat media server

### Install Apache2 and PHP
```sh
sudo apt-get install apache2-utils apache2 -y
sudo apt-get install php libapache2-mod-php php-cli php-fpm php-json php-zip php-curl -y
sudo systemctl enable apache2
sudo a2enmod headers
sudo a2enmod rewrite
```

### Create VirtualHost

Create new **VirtualHost**
```sh
sudo nano /etc/apache2/sites-available/rvcm.conf
```

VirtualHost exemple
```apache
<VirtualHost *:80>
    Header set Access-Control-Allow-Origin "*"

    DocumentRoot /var/www/html/ReVoiceChat-MediaServer/www/
    DirectoryIndex index.php

    <Directory /var/www/html/ReVoiceChat-MediaServer/www/>
        AllowOverride all
        Require all granted
    </Directory>

    <Directory /var/www/html/ReVoiceChat-MediaServer/www/data/>
        AllowOverride None
        Require all denied
    </Directory>

    ErrorLog /var/www/html/logs/rvcm_http_error.log
    LogLevel info
</VirtualHost>
```
**Cache-Control** can be set to **no-cache, must-revalidate**

Enable **VirtualHost**
```sh
sudo a2ensite rvcm.conf
sudo systemctl reload apache2
```