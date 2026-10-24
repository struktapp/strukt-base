<?php

declare(strict_types=1);

namespace Strukt\Container;

use Psr\Container\ContainerExceptionInterface;

/**
 * Thrown when a dependency cannot be resolved or constructed.
 */
class ContainerException extends \RuntimeException implements ContainerExceptionInterface
{
}
