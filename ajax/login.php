<?php
require_once '../core/_config/config.php';

$email = $_POST['email'];
$password = $_POST['password'];

$persona = new personal();
$logueado = $persona->login($email, $password);

$return = array(
	'status' => $logueado === TRUE,
	'mensaje' => $logueado ? 'Se ha logueado con éxito' : 'email o password incorrecto'
);
echo json_encode($return);

