<?php

/**
 * Description of base
 *
 * @author paullieness
 */
class base {

	protected function castRow($row) {
		foreach ($row as $key => $value) {
			if (property_exists($this, $key)) {
				$this->{$key} = $value;
			}
		}
	}

}
