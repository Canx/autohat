#!/usr/bin/env php
<?php

/* Autohat - herramienta para ayudar a poner "gorritos" en Itaca
 *
 * @autor   Ruben Cancho <canchete@gmail.com>
 */

use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverExpectedCondition;
use Facebook\WebDriver\WebDriverBy;

use GetOpt\GetOpt;
use GetOpt\Option;
use GetOpt\Command;
use GetOpt\ArgumentException;
use GetOpt\ArgumentException\Missing;

require_once __DIR__ . '/vendor/autoload.php';


function myprint($message) {
   print date('Y/m/d H:m:s') . " > " . $message . PHP_EOL;
}

function autohat() {
    $getOpt = new GetOpt();
    
    $getOpt->addOptions([
    	['h','headless', GetOpt::NO_ARGUMENT, "Ejecutar sin chrome visible"],
    	['u','user', GetOpt::REQUIRED_ARGUMENT, "Usuario de Itaca"],
    	['p','password', GetOpt::REQUIRED_ARGUMENT, "Contraseña de Itaca"]
        ]);
    
    try {
        $getOpt->process();
    } catch (Exception $exception) {
        print $getOpt->getHelpText() . PHP_EOL;
        exit(1);
    }

    $user = $getOpt['user'];
    $password = $getOpt['password'];
    $headless = $getOpt['headless'];
    
    if ($user == NULL or $password == NULL) {
       $config = parse_ini_file('config.ini');
       $user = $config['user'];
       $password = $config['password'];
    }
    if ($user == NULL or $password == NULL) {
        print "Error: Debes pasar el usuario y contraseña de Itaca como parámetros!" . PHP_EOL;
        print $getOpt->getHelpText();
        exit;
    }
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "http://localhost:4444/wd/hub/status");
    $result = curl_exec($ch);
    curl_close($ch);
    if ( $result != false ) {
         // kill any selenium instance running.
        myprint("killing selenium...");
        shell_exec("lsof -t -i :4444 | xargs kill");
    }

    myprint("starting selenium...");
    $pid = exec('java -jar ' . __DIR__ . '/selenium-server-standalone-3.7.1.jar > /dev/null 2>&1 & echo $! &');
    sleep(2);
    
    $browser_type = 'chrome';
    $host = 'http://localhost:4444/wd/hub';
    
    // testing
    $capabilities = DesiredCapabilities::chrome(array("browserName" => $browser_type));
    
    $options = new ChromeOptions();
    
    if ($headless) { 
        $options->addArguments(["--headless"]);
    }
    $options->addArguments(["--disable-gpu","start-maximized"]);
    
    $capabilities->setCapability(ChromeOptions::CAPABILITY, $options);
    
    //$capabilities = array("browserName" => $browser_type);
    try {
      $driver = RemoteWebDriver::create($host, $capabilities);
    }
    catch(Exception $e) {
       //myprint $e->getMessage();
       myprint("Error: selenium standalone server no está ejecutandose.");
       exit;
    }
    
    myprint("haciendo login...");
    try {
        $driver->get("https://docent.edu.gva.es");
        $driver->wait(20, 1000)->until(
    	  WebDriverExpectedCondition::urlIs('https://acces.edu.gva.es/sso/login.xhtml?callbackUrl=https://docent.edu.gva.es/md-front/www/')
        );
    } catch(Exception $e) {
       throw new Exception("No se pudo cargar la web de Itaca");
    }
    
    $username_box = $driver->findElement(WebDriverBy::id('j_username'));
    $username_box->click();
    $driver->getKeyboard()->sendKeys($user);
    
    $password_box = $driver->findElement(WebDriverBy::id('j_password'));
    $password_box->click();
    $driver->getKeyboard()->sendKeys($password);
    
    $button = $driver->findElement(WebDriverBy::name('j_id42'));
    $button->click();
    
    // TODO: add exception about user/password incorrect
    try {
        $driver->wait(20, 1000)->until(
    	  WebDriverExpectedCondition::urlIs('https://docent.edu.gva.es/md-front/www/?lang=es#moduldocent/centres')
          );
    }
    catch(Exception $e) {
	throw new Exception("Usuario y/o contraseña incorrectos.");
    }
    
    $driver->wait(20,1000)->until(
       function () use ($driver) {
          $elements = $driver->findElements(WebDriverBy::className('imc-centre-horari'));
          return count($elements) > 0;
       },
       'Error cargando página de horario');
    
    $driver->wait(20,1000)->until(
    	WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('imc-centre-horari')));
    
    
    myprint("accediendo a sesiones...");
    $enlace_sesiones = $driver->findElement(WebDriverBy::className('imc-centre-horari'));
    $enlace_sesiones->click();
    sleep(2);
    
    $driver->wait(10, 1000)->until(
    	  WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::className('imc-horari-dia')));
    
    $sesiones = $driver->findElements(WebDriverBy::cssSelector("li.imc-horari-dia:first-of-type > ul > li"));
    
    $num_sesiones = count($sesiones);
    myprint("sesiones del día: {$num_sesiones}");
    
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
    		       sleep(2);
    	           }
    	           catch(Exception $e) {
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
    
           myprint("Grupo {$grupomateria}: {$mensaje}");
    
           // Al acabar volvemos atrás
           $volver = $driver->findElement(WebDriverBy::cssSelector(".imc-torna > a"));
           $volver->click();
           sleep(2);
       }
    }
    
    // Desconectamos
    myprint("desconectando..."); 
    $boton_desconectar = $driver->findElement(WebDriverBy::className("imc-marc-bt-desconecta"));
    $boton_desconectar->click();
    sleep(1);
    $boton_aceptar = $driver->findElement(WebDriverBy::className("imc-bt-accepta"));
    $boton_aceptar->click();
    
    myprint("parando selenium...");
    //shell_exec("lsof -t -i :4444 | xargs kill > /dev/null 2>&1");
    shell_exec("kill -9 {$pid} &");
}

///// MAIN
$retry = false;
$time = 60;
do
    try {
        autohat();
        $retry = false;
    } 
    catch(Exception $e) {
        myprint("Error:" . $e->getMessage());
        myprint("Reintentando en {$time} segundos...");
        sleep($time);
        $time = $time*2;   
        $time = min($time, 3600);
        $retry = true;
    }
while($retry);
