# Autohat
Script de selenium para automatizar la puesta de 'gorritos' de cada clase.

## Prerequisitos

- Instala Google Chrome

- Descarga la última versión de [Selenium server standalone](http://www.seleniumhq.org/download/) y ejecútala:

```
$ java -jar selenium-server-standalone-3.7.1.jar
```

- Instala la última versión de [Chrome Driver](https://sites.google.com/a/chromium.org/chromedriver/downloads)

- Instala [composer](https://getcomposer.org/download/)

- Instala las dependencias ejecutando:

```
php composer.phar install
```

Alternativamente ejecuta o sigue los pasos de `install.sh`.

## Utilización

Ejecuta el script con el usuario y contraseña de Itaca:

```
$ autohat.php <usuario> <contraseña>
```

Profit!
