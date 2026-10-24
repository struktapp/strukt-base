<?php

declare(strict_types=1);

namespace Strukt\Container;

use Psr\Container\NotFoundExceptionInterface;

/**
 * Thrown when a requested identifier is not registered and not a class.
 */
final class NotFoundException extends ContainerException implements NotFoundExceptionInterface
{
}
