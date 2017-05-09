<?php

require_once '../core/_config/config.php';
require_once '../controllers/TplLogin.php';

$tplLogin = new TplLogin();
$tpl = $tplLogin->getTpl();
$tpl->printToScreen();


