<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface BcryptInterface{
	
	public function encode():string;
	public function verify(string $hash):bool;
}