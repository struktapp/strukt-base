<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface CsrfInterface{
	
	public function decode():string|false|null;
	public function encode():string;
	public function valid():bool;
}