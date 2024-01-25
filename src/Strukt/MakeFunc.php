<?php

namespace Strukt;

class MakeFunc{

	public $all;
	public $pkg_name;
	public static $app = null;

	public function __construct(string $pkg_name){

		$this->pkg_name = $pkg_name = trim($pkg_name);
		$this->all["base"][] = "helper_add";

		if($pkg_name!="base")
			$this->all[$pkg_name] = [];
	}

	public static function create(string $pkg_name){

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

	public static function singleton(){

		return static::$app;
	}

	public function register(string $fn_name){

		$fn_name = trim($fn_name);
		if(!function_exists($fn_name)){

			$this->all[$this->pkg_name][] = $fn_name;

			return true;
		}

		return false;
	}

	public function get(string $pkg_name){

		$pkg_name = trim($pkg_name);
		if(!array_key_exists($pkg_name, $this->all))
			return null;

		return $this->all[$pkg_name];
	}

	public function listPackages(){

		return array_keys($this->all);
	}
}