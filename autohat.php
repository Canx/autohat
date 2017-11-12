#!/usr/bin/php
<?php

require_once('vendor/autoload.php');
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;


if ($argc != 3) {
    exit("Debes pasar el usuario y contraseña de Itaca como parámetros!\n");
}

$user = $argv[1];
$password = $argv[2];

$browser_type = 'chrome';
$host = 'http://localhost:4444/wd/hub';

// testing
$capabilities = DesiredCapabilities::chrome(array("browserName" => $browser_type));

$options = new ChromeOptions();

// TODO: add headless when parameters passed
//$options->addArguments(["--headless"]);
$options->addArguments(["--disable-gpu","start-maximized"]);

$capabilities->setCapability(ChromeOptions::CAPABILITY, $options);

//$capabilities = array("browserName" => $browser_type);
try {
  $driver = RemoteWebDriver::create($host, $capabilities);
}
catch(Exception $e) {
   exit("Error: selenium standalone server no está ejecutandose!\n");
}

$driver->get("https://docent.edu.gva.es");

$driver->wait(20, 1000)->until(
	  WebDriverExpectedCondition::urlIs('https://acces.edu.gva.es/sso/login.xhtml?callbackUrl=https://docent.edu.gva.es/md-front/www/')
  );

$username_box = $driver->findElement(WebDriverBy::id('j_username'));
$username_box->click();
$driver->getKeyboard()->sendKeys($user);

$password_box = $driver->findElement(WebDriverBy::id('j_password'));
$password_box->click();
$driver->getKeyboard()->sendKeys($password);

$button = $driver->findElement(WebDriverBy::name('j_id42'));
$button->click();

$driver->wait(20, 1000)->until(
	  WebDriverExpectedCondition::urlIs('https://docent.edu.gva.es/md-front/www/?lang=es#moduldocent/centres')
  );
$driver->wait(20,1000)->until(
   function () use ($driver) {
      $elements = $driver->findElements(WebDriverBy::className('imc-centre-horari'));
      return count($elements) > 0;
   },
   'Error cargando página de horario');

$driver->wait(20,1000)->until(
	WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('imc-centre-horari')));

$enlace_sesiones = $driver->findElement(WebDriverBy::className('imc-centre-horari'));
$enlace_sesiones->click();
sleep(2);

$driver->wait(10, 1000)->until(
	  WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('imc-horari-dia')));

$sesiones = $driver->findElements(WebDriverBy::cssSelector("li.imc-horari-dia:first-of-type > ul > li"));

$num_sesiones = count($sesiones);
print "sesiones: {$num_sesiones}\n";

for($num_sesion = 0; $num_sesion < $num_sesiones; $num_sesion++) {
   sleep(1);
   $sesion = $driver->wait(10, 1000)->until(
       WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector("li.imc-horari-dia:first-of-type > ul > li")));

   $sesiones = $driver->findElements(WebDriverBy::cssSelector("li.imc-horari-dia:first-of-type > ul > li"));
   $sesion = $sesiones[$num_sesion];

   $driver->wait(20,1000)->until(
	   WebDriverExpectedCondition::visibilityOf($sesion));

   $grupo = $sesion->getAttribute("data-grups"); 
   $materia = $sesion->getAttribute("data-materia");
   $grupomateria = "{$materia} ({$grupo})";

   if($grupo != "Guardia") {
       $sesion->click();
       sleep(2);
       $tareas_diarias = $driver->wait(10, 1000)->until(
	  WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('imc-bt-tasques-diaries')));

       $tareas_diarias->click();
       sleep(2);
       // Aqui comprobamos si hay que hacer click.

       try {
           $checkbox_claseimpartida = $driver->findElement(WebDriverBy::cssSelector("li.imc-sessio-impartida-titular.imc-sessio-impartida-titular-ok > label > div > input"));
           $div_claseimpartida = $driver->findElement(WebDriverBy::cssSelector("li.imc-sessio-impartida-titular.imc-sessio-impartida-titular-ok > label > div"));

	   if ($div_claseimpartida->isDisplayed()) {
	       if (!$checkbox_claseimpartida->isSelected()) {
		   try {
                       $div_claseimpartida->click();
                       $mensaje = "Click realizado!";
	           }
	           catch(Expection $e) {
                       print "Excepción haciendo click en gorrito!\n";	
	           }
	       }
	       else {
		  $mensaje = "Gorrito ya está marcado!";
	       }
           }
	   else {
               $mensaje = "no hay gorrito que marcar!";
	   }
       }
       catch (Exception $e) {
           $mensaje = "no se ha encontrado checkbox de clase impartida en el curso";
       }

       print "Grupo {$grupomateria}: {$mensaje}\n";

       // Al acabar volvemos atrás
       $volver = $driver->findElement(WebDriverBy::cssSelector(".imc-torna > a"));
       $volver->click();
       sleep(2);
   }
}

// Desconectamos
$boton_desconectar = $driver->findElement(WebDriverBy::className("imc-marc-bt-desconecta"));
$boton_desconectar->click();
sleep(1);
$boton_aceptar = $driver->findElement(WebDriverBy::className("imc-bt-accepta"));
$boton_aceptar->click();
