<?php

/**
 * Description of personal
 *
 * @author paullieness
 */
class personal extends base{

	public $id;
	public $cedula;
	public $primer_nombre;
	public $segundo_nombre;
	public $primer_apellido;
	public $segundo_apellido;
	public $fecha_nacimiento;
	public $email;
	public $password;
	public $tipo;

	public static function isLoggedIn() {
		if (isset($_SESSION[SESSION_LOGIN])) {
			return $_SESSION[SESSION_LOGIN];
		} else {
			return false;
		}
	}

	public function login($email, $password) {
		try {
			$conexion = _pdo::getConn();
			$sql = 'SELECT P.id, P.cedula, P.primer_nombre, P.segundo_nombre, P.primer_apellido, 
				P.segundo_nombre, P.fecha_nacimiento, P.email,
				IF(PROF.id, "1", "0") profesor, IF(TUT.id, "1", "0") tutor
					FROM personal P
					LEFT JOIN profesores PROF ON PROF.id=P.id
					LEFT JOIN tutores TUT ON TUT.id=P.id
					WHERE email=:param_email AND password=:param_password 
					LIMIT 1';
			$consulta = $conexion->prepare($sql);
			$consulta->bindParam(':param_email', $email, _pdo::PARAM_STR);
			$consulta->bindParam(':param_password', sha1($password), _pdo::PARAM_STR);
			$consulta->execute();
			$resultado = $consulta->fetch(_pdo::FETCH_ASSOC);

			if (!empty($resultado)) {
				$this->castRow($resultado);

				$_SESSION[SESSION_LOGIN] = true;
				$_SESSION[SESSION_USUARIO] = serialize($resultado);
				return true;
			}
		} catch (Exception $ex) {
			throw $ex;
		}

		return false;
	}

}

abstract class personalTipo {

	const PROFESOR = '1';
	const TUTOR = '2';
	const PROFESOR_TUTOR = '3';

}
