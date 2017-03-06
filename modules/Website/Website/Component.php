<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Website;

/**
 * Description of Component
 *
 * @author User
 */
abstract class Component extends \G2Design\G2App\Controller{
	
	var $twig = null;
	var $functions = [];
	
	public function __construct(\Twig_Environment &$twig) {
		$this->twig = $twig;
	}
	
	abstract function init();
	abstract function globalName();
	
	protected function add_function($name, callable $function) {
		$this->functions[$name] = $function;
	}
	
	public function register_functions() {
		
		foreach($this->functions as $name => $func) {
			$tfunc = new \Twig_SimpleFunction($name, $func);
			$this->twig->addFunction($name,$tfunc);
		}
		
	}
}
