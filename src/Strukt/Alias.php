<?php

namespace Strukt;

/**
* Aliasing class
* 
* You can alias a command, event-name or string
*
* @author Moderator <pitsolu@gmail.com>
*/
class Alias{

	protected static $aliases = []; 

	/**
	 * @param string $alias
	 * @param string $long_name
	 * 
	 * @return string
	 */
	public static function set(string $alias, string $long_name):string{

		if(self::exists($alias))
			throw new \Exception(sprintf("Alias %s already exists!", $alias));

		return static::$aliases[$alias] = $long_name;
	}

	/**
	 * @param string $alias
	 * 
	 * @return string
	 */
	public static function get(string $alias):string|null{

		if(self::exists($alias))
			return static::$aliases[$alias];

		return null;
	}

	/**
	 * @param string $alias
	 * 
	 * @return boolean
	 */
	public static function exists(string $alias):bool{

		return array_key_exists($alias, static::$aliases);
	}

	/**
	 * @param string $starts_with
	 * 
	 * @return array
	 */
	public static function ls(?string $starts_with = null):array{

		$names = array_keys(static::$aliases);
		if(is_null($starts_with))
			return $names;

		$ls = [];
		foreach($names as $name)
			if(str_starts_with($name, $starts_with))
				$ls[] = $name;

		return $ls;
	}
}