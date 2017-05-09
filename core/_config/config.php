<?php

session_start();

date_default_timezone_set('America/Montevideo');
/* raiz del sitio */
$fileName = array_reverse(explode(DIRECTORY_SEPARATOR, dirname(__FILE__)));
defined('ROOT_PATH') ? null : define('ROOT_PATH', substr(dirname(__FILE__), 0, -(strlen($fileName[0]))) . '../');

/* config del sitio */
require(ROOT_PATH . 'core/_config/db.php');
require(ROOT_PATH . 'core/_config/constants.php');
if (DEBUG) {
	ini_set('display_errors', 1);
	error_reporting(E_ALL ^ E_NOTICE);
}

/* url del sitio */
//define('WEB_PATH', (substr($_SERVER["SERVER_NAME"],0,4) == 'www.')? 'http://www.' . $_SERVER["SERVER_NAME"].'/'.FOLDER.'/': 'http://' . $_SERVER["SERVER_NAME"].'/'.FOLDER.'/');
$folder = (FOLDER == "") ? "" : FOLDER . "/";
define('WEB_PATH', 'http://' . filter_input(INPUT_SERVER, "SERVER_NAME") . '/' . $folder);


/* clases y librerías base */
require(ROOT_PATH . 'core/functions.php'); // Funciones generales 
require(ROOT_PATH . 'core/spk.php'); // SPK
require(ROOT_PATH . 'core/vparse.php'); // Parser de html
require(ROOT_PATH . 'core/pdo.php'); // Conexión a la base de datos


$_GET = removeMQ($_GET);
$_POST = removeMQ($_POST);
