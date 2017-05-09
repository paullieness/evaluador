<?php

/**
 * Description of TplEvaluador
 *
 * @author paullieness
 */
class TplEvaluador {

	public static function getTpl() {
		$tpl = core\Spk::getTpl('evaluador');

		$sUsuario = $_SESSION[SESSION_USUARIO];
		if (!empty($sUsuario)) {
			$aUsuario = unserialize($sUsuario);
			$tpl->assign('nombre', "{$aUsuario['primer_nombre']} {$aUsuario['primer_apellido']}");

			$profesor = new profesor($aUsuario['id']);
			$grupos_materias = $profesor->getGruposMaterias();

			$idGrupo;
			if (!empty($grupos_materias)) {
				foreach ($grupos_materias as $grupo_materia) {
					if (empty($idGrupo)) {
						$idGrupo = $grupo_materia['id_grupo'];
					}
					$tpl->newBlock('grupo_materia');
					$tpl->assign('key', $grupo_materia['id_grupo'] . '|' . $grupo_materia['id_materia']);
					$tpl->assign('value', $grupo_materia['nombre_grupo'] . ' - ' . $grupo_materia['nombre_materia']);
				}
				$tpl->gotoBlock('_ROOT');
			}
			$tpl->assign('alumnos', self::getTplAlumnos($idGrupo)->getOutputContent());
		}
		return $tpl;
	}

	public static function getTplAlumnos($idGrupo) {
		$tpl = core\Spk::getTpl('evaluador_alumnos');

		$alumno = new alumno();
		$aAlumnos = $alumno->getAlumnosGrupo($idGrupo);
		if (!empty($aAlumnos)) {
			foreach ($aAlumnos as $aAlumno) {
				$tpl->newBlock('alumno', $aAlumno);
			}
		}

		return $tpl;
	}

}
