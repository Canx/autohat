# 0. Install php
sudo apt-get update
sudo apt-get -y install php7.1-cli php7.1-curl php7.1-zip php7.1-mbstring

# 1. Install chrome

wget -q -O - https://dl-ssl.google.com/linux/linux_signing_key.pub | sudo apt-key add - 
sudo sh -c 'echo "deb [arch=amd64] http://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google.list'
sudo apt-get update
sudo apt-get -y install google-chrome-stable

# 1. Install chrome driver
sudo apt-get install -y bsdtar

curl http://chromedriver.storage.googleapis.com/2.33/chromedriver_linux64.zip | bsdtar -xvf -
chmod +x chromedriver

# 2. Download selenium

wget http://selenium-release.storage.googleapis.com/3.7/selenium-server-standalone-3.7.1.jar

# 3. Download composer.php

php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php -r "if (hash_file('SHA384', 'composer-setup.php') === '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061') { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;"
php composer-setup.php
php -r "unlink('composer-setup.php');"

# 4. Install dependencies

php composer.phar install
