#!/bin/bash

clear
echo -e "========================================================================\n"
echo -e "¡Bienvenidx al instalador de la aplicación web!\n"
echo -e "Asegurese de estar ejecutando este Script desde el directorio en el que se encuentra.\n"
sleep 2
read -p "Pulse ENTER para comenzar con la instalación:" ok
clear
echo -e "========================================================================\n"
echo -e "El proceso puede durar varios minutos. Por favor, no cierre el terminal.\n"
echo -e "========================================================================\n"
sleep 5

sudo apt update 
sudo apt upgrade -y 
sudo apt install curl except -y

sudo apt -y install software-properties-common 
sudo add-apt-repository ppa:ondrej/php -y 
sudo apt-get update 
sudo apt -y install php7.4 php7.4-xml php7.4-mysql
sudo sed -i 's/;extension=mysqli/extension=mysqli/g' /etc/php/7.4/apache2/php.ini 

sudo curl -fsSL https://deb.nodesource.com/setup_14.x | sudo -E bash - 
sudo apt-get install -y nodejs 

cd .. 
cd server_node 

/usr/bin/expect <<!
spawn npm init
expect "package name:"
send "\n" 
expect "version:"
send "\n" 
expect "description:"
send "\n" 
expect "entry point:"
send "server.js\n" 
expect "test command:"
send "\n" 
expect "git repository"
send "\n" 
expect "keywords:"
send "\n" 
expect "author:"
send "\n" 
expect "license:"
send "\n" 
expect "Is this OK?"
send "\n"
expect eof
!

npm cache clean --force
npm install express@4.17.1 --save 
npm install mysql@2.18.1 --save 
npm install node-cron@2.0.3 --save 
npm install serialport@9.0.4 --save 
npm install socket.io@2.3.0 --save 

sudo apt install -y mariadb-server 

SECURE_MYSQL=$(expect -c " 
set timeout 10 
spawn mysql_secure_installation
expect "Enter current password for root (enter for none):"
send "\n"
expect "Set root password?"
send "n\n"
expect "Remove anonymous users?"
send "\n"
expect "Disallow root login remotely?"
send "\n"
expect "Remove test database and access to it?"
send "\n"
expect "Reload privilege tables now?"
send "\n"
expect eof
")

echo "$SECURE_MYSQL"

echo -e "========================================================================\n"
read -p "Introduzca una contraseña para el usuario 'admin' de la base de datos: " mysqlpass
echo -e "\n========================================================================\n"

sudo mysql -e "GRANT ALL ON *.* TO 'admin'@'localhost' IDENTIFIED BY '$mysqlpass' WITH GRANT OPTION;" 
sudo mysql -e "FLUSH PRIVILEGES;" 

cd .. 

read -p "Introduzca la contraseña para el usuario 'admin' de la aplicación web: " adminpass

echo -e "\n========================================================================\n"
read -p "Introduzca el e-mail para el usuario 'admin' de la aplicación web: " adminemail
echo -e "\n========================================================================\n"

sudo sed -i 's/<PASSWORD>/'$adminpass'/g' script.sql 
sudo sed -i 's/<ADMIN_E-MAIL>/'$adminemail'/g' script.sql 

sundo mysql -u admin -p$mysqlpass -e "source script.sql" 

sudo apt install -y apache2 libapache2-mod-php7.4 

sudo a2dismod mpm_event 
sudo a2enmod mpm_prefork 
sudo a2enmod php7.4 

sudo rm -r /var/www/html/index.html 
sudo mv * /var/www/html 
sudo mv .htaccess /var/www/html 

sudo a2enmod rewrite 

declare -i line=$(awk '/DocumentRoot/{ print NR; exit }' /etc/apache2/sites-available/000-default.conf)

line=$((line + 1))

sudo ex /etc/apache2/sites-available/000-default.conf << eof
$line insert
<Directory "/var/www/html">
.
xit
eof

line=$((line + 1))

sudo ex /etc/apache2/sites-available/000-default.conf << eof
$line insert
AllowOverride All
.
xit
eof

line=$((line + 1))

sudo ex /etc/apache2/sites-available/000-default.conf << eof
$line insert
</Directory>
.
xit
eof

sudo a2enmod proxy 
sudo a2enmod proxy_http 
sudo systemctl restart apache2 

sudo sed -i 's/DirectoryIndex index.html index.cgi index.pl index.php index.xhtml index.htm/DirectoryIndex index.php index.cgi index.pl index.html index.xhtml index.htm/g' /etc/apache2/mods-available/dir.conf 

sudo usermod -a -G dialout www-data 
sudo systemctl restart apache2 

echo -e "========================================================================\n"
read -p "Introduzca el host del servidor de base de datos (generalmente localhost): " hostdb
echo -e "\n========================================================================\n"

sudo sed -i 's/<USERNAME>/admin/g' /var/www/html/web_config/configuration_properties.php 
sudo sed -i 's/<USERNAME_PASSWORD>/'$mysqlpass'/g' /var/www/html/web_config/configuration_properties.php 
sudo sed -i 's/<DB_NAME>/db/g' /var/www/html/web_config/configuration_properties.php 
sudo sed -i 's/<HOST>/'$hostdb'/g' /var/www/html/web_config/configuration_properties.php 

echo -e "Introduzca la URL de la web. "
echo -e "Recuerda incluir 'http://' o 'https://' y obviar el '/' del final.\n"
echo -e "Por ejemplo: http://www.prueba.es\n" 
read -p "Introducir URL: " url
echo -e "\n========================================================================\n"

echo -e "Introduzca el e-mail con el que la aplicación enviará correos electrónicos. "
echo -e "Recuerde que el e-mail debe tener habilitado el acceso de aplicaciones poco seguras.\n"
read -p "Introducir e-mail: " email
echo -e "\n========================================================================\n"

read -p "Introduzca la contraseña para el e-mail anterior: " emailpass
echo -e "========================================================================\n"

sudo sed -i 's/<URL>/'$url'/g' /var/www/html/web_config/configuration_properties.php 
sudo sed -i 's/<E-MAIL>/'$email'/g' /var/www/html/web_config/configuration_properties.php 
sudo sed -i 's/<E-MAIL_PASS>/'$emailpass'/g' /var/www/html/web_config/configuration_properties.php 

sudo sed -i 's/<HOST>/'$hostdb'/g' /var/www/html/server_node/config/configuration_properties.js 
sudo sed -i 's/<USERNAME>/admin/g' /var/www/html/server_node/config/configuration_properties.js 
sudo sed -i 's/<PASSWORD>/'$mysqlpass'/g' /var/www/html/server_node/config/configuration_properties.js 
sudo sed -i 's/<DB_NAME>/db/g' /var/www/html/server_node/config/configuration_properties.js 

sudo sed -i 's/<URL>/'$url'/g' /var/www/html/.htaccess 

sudo apt install xterm -y 
sudo xhost +local: 

sudo chmod a+rw /var/www/html/web_config/devices_info.xml 
sudo chmod a+rw /var/www/html/server_node/logs

sudo apt purge curl except -y

clear
echo -e "========================================================================\n"
echo -e "¡INSTALACIÓN EXITOSA!\n"
echo -e "\n========================================================================\n"
sleep 2
read -p "Pulse ENTER para salir del instalador." ok
clear

