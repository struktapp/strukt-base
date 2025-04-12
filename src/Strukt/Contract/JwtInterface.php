<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface JwtInterface{
	
	public function valid():bool;
	public function yield():stdClass;
}