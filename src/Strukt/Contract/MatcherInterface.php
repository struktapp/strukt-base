<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface MatcherInterface{
	
	public function which(string $route):string|null;
	public function params():array;
}