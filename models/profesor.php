<?php

/**
 * Description of profesores
 *
 * @author paullieness
 */
class profesor extends base{

	public $id;

	public function __construct($id) {
		$this->id = $id;
	}

	public function getGruposMaterias($anio = '') {
		if (empty($anio)) {
			$anio = date('Y');
		}

		try {
			$conexion = _pdo::getConn();
			$sql = 'SELECT G.anio, G.grado, G.nombre nombre_grupo, G.orientacion, 
					M.nombre nombre_materia, GM.*
					FROM grupos_materias GM
					INNER JOIN grupos G ON G.id = GM.id_grupo
					INNER JOIN materias M ON M.id=GM.id_materia
					WHERE GM.id_profesor=:param_id_profesor AND G.anio=:param_anio';
			
			$consulta = $conexion->prepare($sql);
			$consulta->bindParam(':param_id_profesor', $this->id, _pdo::PARAM_INT);
			$consulta->bindParam(':param_anio', $anio, _pdo::PARAM_INT);
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
