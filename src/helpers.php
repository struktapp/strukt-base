<?php

use Strukt\Helper;
use Strukt\Alias;
use Strukt\Env;
use Strukt\Raise;
use Strukt\Ref;

if(!function_exists("helper")){

	/**
	 * @param string $pkg_name
	 * 
	 * @return Helper|array|null
	 */
	function helper(?string $pkg_name = null):Helper|array|null{		

		$mkfn = Helper::singleton();

		if(!is_null($mkfn)){

			$ls = null;
			if(!is_null($pkg_name)){

				$ls = $mkfn->get($pkg_name);
				if(!is_null($ls))
					return $ls;
			}

			if(is_null($ls))
				if(in_array($pkg_name, ["packages", "pkg", "pkgs"]) || is_null($pkg_name))
					return $mkfn->listPackages();
		}

		if(is_null($mkfn) && is_null($pkg_name))
			$pkg_name = "base";

		if(!in_array($pkg_name, ["packages", "pkg", "pkgs"]))
			return Helper::create($pkg_name);

		return null;
	}
}

if(!function_exists("helper_add")){

	/**
	 * @param string $fn_name
	 * 
	 * @return boolean
	 */
	function helper_add(string $fn_name):bool{

		return Helper::singleton()->register($fn_name);
	}
}

helper("base");

if(helper_add("is_map")){

	function is_map(array $arr):bool{

		return !empty($arr) && 
				array_keys($arr) !== range(0, count($arr) - 1) && 
				empty(array_filter(array_keys($arr), "is_numeric"));
	}
}

if(helper_add("alias")){

	/**
	 * @param string $alias
	 * @param string $long_name
	 * 
	 * @return array|string|null
	 */
	function alias(?string $alias = null, ?string $long_name = null):array|string|null{

		if(!is_null($alias))
			if(!str_ends_with($alias, "*")){

				if(!empty($alias) && is_null($long_name))
					return Alias::get($alias);

				if(!is_null($long_name))
					return Alias::set($alias, $long_name);
			}

		if(!is_null($alias))
			return Alias::ls(trim($alias,"*"));

		return Alias::ls();
	}
}

if(helper_add("env")){

	/**
	 * @param string $key
	 * @param mixed $val - can only be string|int|bool
	 * 
	 * @return string
	 */
	function env(string $key, int|string|bool|null $val = null):string{

		if(!is_null($val))
			Env::set($key, $val);

		return Env::get($key);
	}
}

if(helper_add("ref")){

	/**
	 * @param string|object|callable $class
	 * 
	 * @return mixed
	 */
	function ref(string|object|callable $class):mixed{

		if(is_string($class))
			if(class_exists($class))
				return Ref::create($class);

		if(is_object($class))
			if(class_exists(@array_shift(array_filter([get_class($class)], fn($name)=>$name!=Closure::class))??""))
				return Ref::createFrom($class);

		if(is_callable($class))
			return Ref::func($class);
	}
}

if(helper_add("raise")){

	/**
	 * Raise exception
	 * 
	 * @param string $error
	 * @param integer $code - default: 500 (server error)
	 * 
	 * @return \Strukt\Raise
	 */
	function raise(string $error, int $code = 500):Raise{

		return new Raise($error, $code);
	}
}

if(helper_add("timezone")){

	/**
	 * Timezone
	 * 
	 * @param ?string $local
	 * 
	 * @return string|false
	 */
	function timezone(?string $locale = null):string|false{

		$timezone = ini_get("date.timezone");
		if($timezone == "UTC" && notnull($locale))
			ini_set("date.timezone", $locale);

		return $locale ?? $timezone;
	}
}

if(helper_add("env")){

	/**
	 * @param string $key
	 * @param mixed $val - can only be string|int|bool
	 * 
	 * @return string
	 */
	function env(string $key, int|string|bool|null $val = null):string{

		if(!is_null($val))
			Env::set($key, $val);

		return Env::get($key);
	}
}

if(helper_add("negate")){

	/**
	 * @param boolean $any
	 * 
	 * @return boolean
	 */
	function negate(bool $any):bool{

		return !$any;
	}
}

if(helper_add("notnull")){

	/**
	 * @param mixed $var
	 * 
	 * @return boolean
	 */
	function notnull(mixed $var):bool{

		return negate(is_null($var));
	}
}

use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;
use Symfony\Component\VarDumper\Dumper\HtmlDumper;
use Symfony\Component\VarDumper\VarDumper;

if(helper_add("dd")){

	VarDumper::setHandler(function (mixed $var): void {
	    $cloner = new VarCloner();
	    $dumper = 'cli' === PHP_SAPI ? new CliDumper() : new HtmlDumper();

	    $dumper->dump($cloner->cloneVar($var));
	});
}