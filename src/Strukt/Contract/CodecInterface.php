<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface CodecInterface{
	
	public function encode(string $data):mixed;
	public function decode(string $encrypted):mixed;
}