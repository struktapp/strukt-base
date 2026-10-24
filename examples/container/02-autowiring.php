<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Strukt\Container\Container;

interface OrderRepository
{
    public function save(string $order): void;
}

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var list<string> */
    public array $saved = [];

    public function save(string $order): void
    {
        $this->saved[] = $order;
    }
}

interface Notifier
{
    public function send(string $message): string;
}

final class EmailNotifier implements Notifier
{
    public function send(string $message): string
    {
        return 'email: '.$message;
    }
}

// OrderService is never bound; the container builds it from its constructor.
final class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private Notifier $notifier,
    ) {
    }

    public function place(string $sku): string
    {
        $this->orders->save($sku);

        return $this->notifier->send($sku.' ordered');
    }
}

$container = new Container();

// Bind the interfaces; their concrete types are chosen here, not by the caller.
$container->set(OrderRepository::class, static fn (): OrderRepository => new InMemoryOrderRepository());
$container->set(Notifier::class, static fn (): Notifier => new EmailNotifier());

// get() autowires OrderService by reflecting its constructor and resolving
// each typed parameter from the bindings above.
$service = $container->get(OrderService::class);

printf("resolved type   : %s\n", $service::class);
printf("place(SKU-1)    : %s\n", $service->place('SKU-1'));
printf("place(SKU-2)    : %s\n", $service->place('SKU-2'));

$repository = $container->get(OrderRepository::class);
printf("saved orders    : %s\n", implode(', ', $repository->saved));
