<?php
/**
 * Description of alumno
 *
 * @author paullieness
 */
class alumno extends base{
	public $id;
	public $cedula;
	public $primer_nombre;
	public $segundo_nombre;
	public $primer_apellido;
	public $segundo_apellido;
	public $fecha_nacimiento;
	public $email;
	public $password;
	
	public function getAlumnosGrupo($idGrupo) {
		try {
			$conexion = _pdo::getConn();
			$sql = 'SELECT A.id, A.cedula, A.primer_nombre, A.segundo_nombre, A.primer_apellido, 
				A.segundo_nombre, A.fecha_nacimiento, A.email, AG.numero_lista
					FROM alumnos A
					INNER JOIN alumnos_grupos AG ON AG.id_alumno = A.id
					WHERE AG.id_grupo=:param_id_grupo
					ORDER BY numero_lista';
			
			$consulta = $conexion->prepare($sql);
			$consulta->bindParam(':param_id_grupo', $idGrupo, _pdo::PARAM_INT);
			$consulta->execute();
			$resultado = $consulta->fetchAll(_pdo::FETCH_ASSOC);
				
			if (!empty($resultado)) {
				return $resultado;
			}
		} catch (Exception $ex) {
			throw $ex;
		}
	}
}
