# Autohat
Script de selenium para automatizar la puesta de 'gorritos' de cada clase.

## Prerequisitos

Ejecuta o sigue los pasos de `install.sh`.

## Utilización

Ejecuta el script con el usuario y contraseña de Itaca:

```
$ autohat.php [-h] -u <usuario> -p <contraseña>
```

o añade el usuario y contraseña en el archivo `config.ini`

La opción -h permite ejecutar el script en modo `headless`

Si quieres utilizar autohat.php en cron utiliza mejor el script `cron-autohat.sh`:

`0 8 * * *  /rutacompleta/cron-autohat.sh -u <user> -p <password>`

y mira los registros en el archivo `autohat.log`

Profit!
