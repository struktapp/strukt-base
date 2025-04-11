<?php

namespace Strukt\Contract;

/**
 * @author Moderator <pitsolu@gmail.com>
 */
interface PeriodInterface{

	function create(\DateTime $start, ?\DateTime $end = null):static;
	function reset(?\DateTime $reset = null):static;
}