# How to install ReVoiceChat-MediaServer

## Install Apache2 and PHP
```sh
sudo apt-get install apache2-utils apache2 php libapache2-mod-php php-json php-zip php-curl php-gd -y
```

```sh
sudo systemctl enable apache2
```

```sh
sudo a2enmod headers
```

```sh
sudo a2enmod rewrite
```

```sh
sudo systemctl restart apache2
```

## Clone this repository

For this guide, we will use ```/srv/rvc``` but you can use any directory (don't forget to change ```/srv/rvc``` to your path)

```sh
git clone https://github.com/revoicechat/ReVoiceChat-MediaServer
```
```sh
cd ReVoiceChat-MediaServer/
```

## Create VirtualHost

### Create new VirtualHost from exemple
```sh
sudo cp rvc_media.exemple.conf /etc/apache2/sites-available/rvc_media.conf
```

### Make sure **/var/log/rvc/** exist and apache2 can write to it
```sh
sudo mkdir -p /var/log/rvc
```
```sh
sudo chown www-data /var/log/rvc
```
```sh
sudo chgrp www-data /var/log/rvc
```

### Make sure apache2 can write inside `www/data`
```sh
sudo chown www-data ./www/data
```
```sh
sudo chgrp www-data ./www/data
```

### Disable *000-default*
```sh
sudo a2dissite 000-default.conf
```

### Enable *VirtualHost*
```sh
sudo a2ensite rvc_media.conf
```
```sh
sudo systemctl reload apache2
```