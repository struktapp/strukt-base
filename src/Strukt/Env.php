<?php

namespace Strukt;

use Strukt\Registry;
use Strukt\Raise;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
class Env{

	private static $env;

	/**
	 * @param string $path
	 * 
	 * @return void
	 */
	public static function withFile(string $path=".env"):void{

		$lines = file(phar($path)->adapt(), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

		foreach($lines as $line){

			if(str_starts_with($line, "#") || str_starts_with($line, "//"))
				continue;

			list($key, $val) = explode("=", $line);

			$val = trim($val);
			$states = ["true"=>true,"false"=>false];
			if(array_key_exists($val, $states))
				$val = $states[$val];

			static::set(trim($key), $val);
		}
	}

	/**
	 * @param string $key
	 * 
	 * @return boolean
	 */
	public static function has(string $key):bool{

		$key = sprintf("env.%s", $key);
		if(class_exists(Registry::class))
			return Registry::getSingleton()->exists($key);

		if(!class_exists(Registry::class))
			array_key_exists($key, static::$env);
	}

	/**
	 * @param string $key
	 * 
	 * @return mixed
	 */
	public static function get(string $key):mixed{

		$key = sprintf("env.%s", $key);

		if(class_exists(Registry::class)){

			$registry = Registry::getSingleton();
			if(!$registry->exists($key))
				new Raise(sprintf("Couldn't get [%s], may not be set by %s!", $key, __CLASS__));

			return $registry->get($key);
		}

		if(!class_exists(Registry::class))
			return static::$env[$key];
	}

	/**
	 * @param string $key
	 * @param string|int|bool $val
	 * 
	 * @return void
	 */
	public static function set(string $key, string|int|bool $val):void{
			
		$key = sprintf("env.%s", $key);
		if(class_exists(Registry::class))
			Registry::getInstance()->set($key, $val);

		if(!class_exists(Registry::class))
			static::$env[$key] = $val;
	}
}