<?php

namespace Strukt;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
class Raise{

	protected static $errors = [];

	/**
	 * @param string $error
	 * @param integer $code
	 */
	public function __construct(string $error, int $code = 500){

		static::$errors[] = $error;

		throw new \Exception($error, $code);
	}
}