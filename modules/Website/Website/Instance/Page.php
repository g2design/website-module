<?php
namespace Website\Instance;

class Page extends \G2Design\G2App\Controller {
	var $file = null;
	private $template_string;
	private $php;
	private $config;
	private $website;
	private $output = '';
	private $after = [];
	private $before = [];
	private $params = [];

	public function __construct(\Website\Instance $website, $file) {
		if(!is_file($file)){
			throw new \Exception("This file does not exist");
		}
		
		$parts = preg_split('/(==\n)|(==\r)/', file_get_contents($file));
		$this->config = array_shift($parts);
		$this->php = count($parts) > 1 ? array_shift($parts) : null;
		$this->template_string = array_shift($parts);
		$this->website = $website;
		$this->file = $file;
		
	}
	
	function &before($callable = false) {
		if(is_callable($callable)) {
			//Quee this calleable for execution 
			$this->before[] = $callable;
		} else if($callable === false) {
			//Run all callebles
			$ref = &$this;
			foreach($this->before as $call) {
				call_user_func_array($call, [$ref]);
			}
		}
		
		return $this;
	}
	
	function after($callable = false) {
		if(is_callable($callable)) {
			//Quee this calleable for execution 
			$this->after[] = $callable;
		} else if($callable === false) {
			//Run all callebles
			$ref = &$this;
			foreach($this->after as $call) {
				call_user_func_array($call, [$ref]);
			}
		}
		return $this;
	}
	
	function getRoute() {
		//Retrieve route from config
		return $this->config()->route;
	}
	
	/**
	 * Gets the config for this page
	 * 
	 * @return \Zend\Config\Config;
	 */
	function config() {
		$reader = new \Zend\Config\Reader\Ini();
		$ar = $reader->fromString($this->config);
		
		return new \Zend\Config\Config($ar, true);
	}
	
	function args() {
		return $this->params['arguments'];
	}
	
	function render(){
		$params = func_get_args();
		$this->params['arguments'] = $params;
		
		//Create the template in the partials folder
		$parcial_location = $this->website->process_folder.'/pages/'. basename($this->file);
		//Create cache dir if not exist
		if (!is_dir(dirname($parcial_location))) {
			mkdir(dirname($parcial_location), 0777, true);
		}
		
		//Process the php saved in file
			$page = &$this;
		if($this->php) {
			try {
				eval($this->php);
			} catch (\Exception $ex) {
				throw new \Exception('There is something wrong with php code in template '. $this->file );
			}
		}
		
		
		
		file_put_contents($parcial_location, $this->template_string);
		
		$this->before();
		
		$this->output .= $this->website->twig->render('/pages/'. basename($this->file), array_merge(['this' => $page], $this->website->globals, $this->params));
		
		$this->after();
		
		return $this->output;
	}
	
	function &comp($name) {
		if(isset($this->website->globals['comp'][$name])) {
			return $this->website->globals['comp'][$name];
		}
	}
	

}