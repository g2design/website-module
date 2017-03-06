<?php

namespace Website;

class Instance {

	var $folder = null, $process_folder = null;
	var $globals = [];

	/**
	 *
	 * @var \Twig_Environment 
	 */
	var $twig = null;
	private $pages;
	private $before = [];
	private $after = [];
	private $components;

	/**
	 * 
	 * @param type $theme
	 */
	public function __construct($theme) {
		$this->folder = $theme;
		$this->init_twig($theme);
		$this->load_pages();
	}

	private function init_twig($folder) {
		$defaults = [
			'cache' => G2_PROJECT_ROOT . '/cache/twig',
			'process_folder' => G2_PROJECT_ROOT . '/cache/process'
		];



		//Merge config is declared
			//Convert to array;
			$conf = (array) $conf;
			$conf = array_merge($defaults, $conf);
		} else
			$conf = $defaults;

		//Create cache dir if not exist
		if (!is_dir(dirname($conf['cache']))) {
			mkdir(dirname($conf['cache']), 0777, true);
		}

		if (!is_dir($conf['process_folder'])) {
			mkdir($conf['process_folder'], 0777, true);
		}

		$this->process_folder = $conf['process_folder'];

		$loader = new \Twig_Loader_Filesystem($this->process_folder);
		$loader->addPath($folder);

		$this->twig = new \Twig_Environment($loader, array(
			'cache' => $conf['cache'],
			'auto_reload' => true,
			'autoescape' => false,
//			'debug' => true
		));
	}

	private function load_pages($pages = 'pages') {

		$pages_files = \G2Design\Utils\Functions::directoryToArray($this->folder . '/' . $pages, true);

		foreach ($pages_files as $file) {
			$page = new Instance\Page($this, $file);
			$this->pages[] = $page;
		}
	}

	function before($callable, $page = false) {
		$this->before[] = ['call' => $callable, 'pages' => $page];
	}

	function after($callable, $page = false) {
		$this->after[] = ['call' => $callable, 'pages' => $page];
	}

	function &attachTo(\G2Design\G2App &$app) {
		foreach ($this->pages as $page) { /* @var $page Instance\Page */
			
			//Before execution of page
			foreach ($this->before as $call) {
				if (!is_array($call['pages']))
					$call['pages'] = [$call['pages']];

				if (in_array($page->config()->route, $call['pages']) || empty($call['pages'])) {
					$page->before($call['call']);
				}
			}
			
			//After execution of page
			foreach ($this->after as $call) {
				if (!is_array($call['pages']))
					$call['pages'] = [$call['pages']];

				if (in_array($page->config()->route, $call['pages']) || empty($call['pages'])) {
					$page->after($call['call']);
				}
			}
			/* @todo Implement better security for this part. This currently allows all types of requests */
			$app->router->any($page->getRoute(), [$page, 'render']);
		}
		return $this;
	}
	
	function getPage($route) {
		
		foreach ($this->pages as $page) { /* @var $page Instance\Page */
			if($page->config()->route == $route) {
				return $page;
			}
		}
		return false;
	}

	function _global($name, $value) {
		$this->globals[$name] = $value;
	}

	/**
	 * Name of class that is instance of Component
	 * @param string $component
	 * @return $this
	 */
	function &register_component($component) {
		$reflection = new \ReflectionClass($component);

		if ($reflection->isSubclassOf('\\Website\\Component')) {
			$instance = new $component($this->twig);
			$instance->init();
			$instance->register_functions();

			//Also append this component to accessible properties

			$this->globals['comp'][$instance->globalName()] = $instance;
		} else {
			throw new Exception("Class `$component` is not a subclass of \\Website\\Component");
		}

		return $this;
	}

}
