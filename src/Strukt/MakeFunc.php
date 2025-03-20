<?php

namespace Strukt;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
class MakeFunc{

	public $all;
	public $pkg_name;
	public static $app = null;

	/**
	 * @param string $pkg_name
	 */
	public function __construct(string $pkg_name){

		$this->pkg_name = $pkg_name = trim($pkg_name);
		$this->all["base"][] = "helper_add";

		if($pkg_name!="base")
			$this->all[$pkg_name] = [];
	}

	/**
	 * @param string $pkg_name
	 * 
	 * @return static
	 */
	public static function create(string $pkg_name):?static{

		if(is_null(static::$app))
			static::$app = new self($pkg_name);

		if(is_null(static::$app->get($pkg_name))){

			static::$app->pkg_name = $pkg_name = trim($pkg_name);

			if(array_key_exists($pkg_name, static::$app->all))
				new Exception(sprintf("Package name [%s] already exists!", $pkg_name));

			if($pkg_name!="base")
				static::$app->all[$pkg_name] = [];
		}

		return static::$app;
	}

	/**
	 * @return static
	 */
	public static function singleton():?static{

		return static::$app;
	}


	/**
	 * @param string $fn_name
	 * 
	 * @return boolean
	 */
	public function register(string $fn_name):bool{

		$fn_name = trim($fn_name);
		if(!function_exists($fn_name)){

			$this->all[$this->pkg_name][] = $fn_name;

			return true;
		}

		return false;
	}

	/**
	 * @param string $pkg_name
	 * 
	 * @return array|null
	 */
	public function get(string $pkg_name):?array{

		$pkg_name = trim($pkg_name);
		if(!array_key_exists($pkg_name, $this->all))
			return null;

		return $this->all[$pkg_name];
	}

	/**
	 * @return array
	 */
	public function listPackages():array{

		return array_keys($this->all);
	}
}