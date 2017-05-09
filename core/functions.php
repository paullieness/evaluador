<?php

function removeMQ(&$arr) {
	if (get_magic_quotes_gpc() == 1) {
		if (is_array($arr)) {
			return array_map("\\core\\removeMQ", $arr);
		} else {
			return stripslashes($arr);
		}
	} else {
		return $arr;
	}
}

function getFileExtension($name) {
	$ns = explode(".", $name);
	return $ns[count($ns) - 1];
}

/**
 * Autocarga las clases de la carpeta \models
 */
spl_autoload_register(function ($clsName) {
//    $className = ltrim($clsName, '\\');
	$className = $clsName;
	$fileName = '';
//    $namespace = '';
//    $lastNsPos = strripos($className, '\\');
//    if ($lastNsPos) {
//        $namespace = substr($className, 0, $lastNsPos);
//        $className = substr($className, $lastNsPos + 1);
//        $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
//    }
	$fileName .=  'models' . DIRECTORY_SEPARATOR . $className . '.php';
	if (file_exists(ROOT_PATH . $fileName)) {
		require ROOT_PATH . $fileName;
		if (DEBUG) {
//			\core\debugger::addClsLoaded($clsName);
		}
		return true;
	} return false;
});