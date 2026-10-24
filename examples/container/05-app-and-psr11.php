<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Psr\Container\ContainerInterface;
use Strukt\Container\Container;

// app() returns the process-wide container, creating it on first use.
$container = app();
printf("app() is shared    : %s\n", $container === app() ? 'same' : 'different');

// The helper is a thin wrapper: get() a binding, or make() with overrides.
$container->set('currency', static fn (): string => 'USD');
printf("app('currency')    : %s\n", app('currency'));

// It satisfies the PSR-11 interface.
printf("PSR-11 container   : %s\n", $container instanceof ContainerInterface ? 'yes' : 'no');

// alias() points a short name at a canonical id.
$container->alias('currency.code', 'currency');
printf("aliased lookup     : %s\n", $container->get('currency.code'));
printf("has(currency.code) : %s\n", $container->has('currency.code') ? 'yes' : 'no');
printf("has(nope)          : %s\n", $container->has('nope') ? 'yes' : 'no');

// An unknown identifier is a not-found exception, not a fatal error.
try {
    $container->get('nope');
} catch (\Psr\Container\NotFoundExceptionInterface $exception) {
    printf("not found          : %s\n", $exception->getMessage());
}

// setInstance() swaps the process-wide container; tests use this to isolate
// state. Passing null installs a fresh container on the next app() call.
Container::setInstance(null);
printf("after reset        : %s\n", app() === $container ? 'same' : 'new');
