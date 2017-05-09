<?php

require_once '../core/_config/config.php';

if(!personal::isLoggedIn()){
    header("Location: " . WEB_PATH . 'login/');
    die();
}

require_once '../controllers/TplEvaluador.php';
require_once '../controllers/TplContenedor.php';

$tplEvaluador = new TplEvaluador();
$tplE = $tplEvaluador->getTpl();

$tplContenedor = new TplContenedor();
$tpl = $tplContenedor->getTpl();
$tpl->assign('content', $tplE->getOutputContent());

$tpl->printToScreen();

