<?php

class Website extends \G2Design\ClassStructs\Module {
	
	public function init() {
		$this->add_route('test', function(){
			return "Website module loading";
		});
	}
	
	/**
	 * Creates an website instance for use with g2App
	 * @param type $folder
	 * @return \Website\Instance
	 */
	public static function loadFrom($folder) {
		return new Website\Instance($folder);
	}

}		