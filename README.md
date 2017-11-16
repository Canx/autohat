# Autohat
Script de selenium para automatizar la puesta de 'gorritos' de cada clase.

## Prerequisitos

Se recomienda utilizar Ubuntu 14.04 o superior.

Ejecuta o sigue los pasos de `install.sh`.

## Utilización

Ejecuta el script con el usuario y contraseña de Itaca:

```
$ autohat.php [-h] -u <usuario> -p <contraseña>
```

La opción -h permite ejecutar el script en modo `headless`

También puedes añadir el usuario y contraseña en el archivo `config.ini`

### Soporte cron

Si quieres utilizar autohat.php en cron utiliza mejor el script `cron-autohat.sh`, por ejemplo así:

`0 8 * * *  /rutacompleta/cron-autohat.sh`

y añade el usuario y contraseña en el archivo `config.ini`.

Mira los registros de funcionamiento en el archivo `autohat.log` que se creará en el directorio del script.

Profit!
