<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

// This example wires an application entirely through the app() helper, the way
// a bootstrap file would. Contrast it with 06, which uses an explicit
// `new Container()`. Both share the same behaviour; the helper just reaches the
// process-wide container returned by Container::getInstance().

interface OrderRepository
{
    public function save(string $order): void;

    public function count(): int;
}

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var list<string> */
    private array $orders = [];

    public function save(string $order): void
    {
        $this->orders[] = $order;
    }

    public function count(): int
    {
        return count($this->orders);
    }
}

interface PaymentGateway
{
    public function name(): string;

    public function charge(float $amount): string;
}

final class StripeGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'stripe';
    }

    public function charge(float $amount): string
    {
        return sprintf('charged %.2f', $amount);
    }
}

final class PaypalGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'paypal';
    }

    public function charge(float $amount): string
    {
        return sprintf('captured %.2f', $amount);
    }
}

interface Notifier
{
    public function send(string $message): string;
}

final class EmailNotifier implements Notifier
{
    /** @var list<string> */
    public array $messages = [];

    public function send(string $message): string
    {
        $this->messages[] = $message;

        return 'email: '.$message;
    }
}

final class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $gateway,
        private Notifier $notifier,
    ) {
    }

    public function place(string $sku, float $amount): string
    {
        $this->orders->save($sku);
        $this->notifier->send(sprintf('%s paid %.2f via %s', $sku, $amount, $this->gateway->name()));

        return sprintf('%s -> %s (%s)', $sku, $this->gateway->charge($amount), $this->gateway->name());
    }
}

// --- Bootstrap --------------------------------------------------------------

// app() with no argument is the shared container. It is created on first use,
// so a bootstrap file can register bindings before anything resolves them.
printf("shared container    : %s\n", app()::class);

// register() interfaces on the shared container via the helper.
app()->set(OrderRepository::class, static fn (): OrderRepository => new InMemoryOrderRepository());
app()->set(PaymentGateway::class, static fn (): PaymentGateway => new StripeGateway());
app()->set(Notifier::class, static fn (): Notifier => new EmailNotifier());

// --- Resolving -------------------------------------------------------------

// app($id) resolves a binding or autowires a class; no container variable is
// ever needed in the application code below.
$service = app(OrderService::class);

echo $service->place('SKU-1', 19.99), "\n";
echo app(OrderService::class)->place('SKU-2', 4.50), "\n";

// app($id, $params) builds a class with named constructor overrides: the
// gateway is supplied inline while the repository is still autowired.
$paypal = app(OrderService::class, ['gateway' => new PaypalGateway()]);
echo $paypal->place('SKU-3', 7.25), "\n";

printf("orders stored       : %d\n", app(OrderRepository::class)->count());

// The bound gateway is unchanged; only the override used a different one.
printf("gateway in use      : %s\n", app(PaymentGateway::class)->name());

// The notifier is shared, so every order this run notifies through the same one.
echo "notifications:\n";
foreach (app(Notifier::class)->messages as $message) {
    printf("  %s\n", $message);
}

// A quick check that repeated calls hit the same shared container instance.
printf("same as app()       : %s\n", app() === app() ? 'yes' : 'no');
