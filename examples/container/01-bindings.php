<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Strukt\Container\Container;

// A tiny collaborator whose identity we can compare.
final class OrderIdGenerator
{
    public function __construct(private string $prefix = 'ORD')
    {
    }

    public function next(): string
    {
        return $this->prefix.'-'.bin2hex(random_bytes(3));
    }
}

$container = new Container();

// instance(): register an object you have already built.
$container->instance(OrderIdGenerator::class, new OrderIdGenerator('GIVEN'));

// set(): register a factory that is built once and reused (shared).
$container->set('notifier', static fn (): object => new class {
    public function send(string $message): string
    {
        return 'sent: '.$message;
    }
});

// factory(): register a factory that runs on every get() (transient).
$container->factory('receipt', static fn (): object => new class {
});

printf("has(notifier)        : %s\n", $container->has('notifier') ? 'yes' : 'no');
printf("has(payment)         : %s\n", $container->has('payment') ? 'yes' : 'no');

// instance() and set() hand back the same object every time.
printf("instance() is shared : %s\n", $container->get(OrderIdGenerator::class) === $container->get(OrderIdGenerator::class) ? 'same' : 'different');
printf("set() is shared      : %s\n", $container->get('notifier') === $container->get('notifier') ? 'same' : 'different');

// factory() hands back a new object every time.
printf("factory() transient  : %s\n", $container->get('receipt') === $container->get('receipt') ? 'same' : 'different');

printf("order id             : %s\n", $container->get(OrderIdGenerator::class)->next());
printf("notifier->send()     : %s\n", $container->get('notifier')->send('order placed'));
