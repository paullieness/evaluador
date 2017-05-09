<?php

/**
 * Description of TplContenedor
 *
 * @author paullieness
 */
class TplContenedor {
	public static function getTpl() {
		$tpl = core\Spk::getTpl('container');
		return $tpl;
	}
}
