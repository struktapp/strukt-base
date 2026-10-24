<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Strukt\Container\Container;

interface OrderRepository
{
    public function save(array $order): void;

    /** @return list<array<string, mixed>> */
    public function all(): array;
}

final class InMemoryOrderRepository implements OrderRepository
{
    /** @var list<array<string, mixed>> */
    private array $orders = [];

    public function save(array $order): void
    {
        $this->orders[] = $order;
    }

    public function all(): array
    {
        return $this->orders;
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

// The application service depends only on abstractions.
final class OrderService
{
    public function __construct(
        private OrderRepository $orders,
        private PaymentGateway $gateway,
        private Notifier $notifier,
    ) {
    }

    public function checkout(string $sku, int $quantity, float $unitPrice): string
    {
        $total = $quantity * $unitPrice;
        $receipt = $this->gateway->charge($total);

        $this->orders->save([
            'sku' => $sku,
            'quantity' => $quantity,
            'total' => $total,
            'gateway' => $this->gateway->name(),
            'receipt' => $receipt,
        ]);

        $this->notifier->send(sprintf('%s x%d paid via %s', $sku, $quantity, $this->gateway->name()));

        return sprintf('%s x%d = %.2f via %s (%s)', $sku, $quantity, $total, $this->gateway->name(), $receipt);
    }
}

// --- Wiring -----------------------------------------------------------------

$container = new Container();

$container->set(OrderRepository::class, static fn (): OrderRepository => new InMemoryOrderRepository());
$container->set(PaymentGateway::class, static fn (): PaymentGateway => new StripeGateway());
$container->set(Notifier::class, static fn (): Notifier => new EmailNotifier());

// --- Running ----------------------------------------------------------------

$service = $container->get(OrderService::class);

echo $service->checkout('SKU-1', 2, 12.50), "\n";
echo $service->checkout('SKU-2', 1, 99.00), "\n";

// Switching the payment provider is a one-line wiring change; no domain code
// is touched and OrderService is rebuilt with the new gateway.
$container->set(PaymentGateway::class, static fn (): PaymentGateway => new PaypalGateway());
echo $container->get(OrderService::class)->checkout('SKU-3', 3, 5.00), "\n";

// --- Inspecting -------------------------------------------------------------

echo "\norders:\n";
foreach ($container->get(OrderRepository::class)->all() as $order) {
    printf("  %s x%d = %.2f (%s)\n", $order['sku'], $order['quantity'], $order['total'], $order['gateway']);
}

echo "notifications:\n";
foreach ($container->get(Notifier::class)->messages as $message) {
    printf("  %s\n", $message);
}
