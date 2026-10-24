<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Strukt\Container\Container;

// A binding factory is just code, so the container can encode selection rules:
// "use the first available gateway, otherwise fall back to the default one".
// The selected gateway is what app('gateway') hands back, so calling pay() on
// it routes the payment through the default gateway.

interface PaymentGateway
{
    public function name(): string;

    public function available(): bool;

    public function pay(float $amount): string;
}

final class StripeGateway implements PaymentGateway
{
    public function __construct(private bool $online = true)
    {
    }

    public function name(): string
    {
        return 'stripe';
    }

    public function available(): bool
    {
        return $this->online;
    }

    public function pay(float $amount): string
    {
        return sprintf('stripe charged %.2f', $amount);
    }
}

final class PaypalGateway implements PaymentGateway
{
    public function __construct(private bool $online = true)
    {
    }

    public function name(): string
    {
        return 'paypal';
    }

    public function available(): bool
    {
        return $this->online;
    }

    public function pay(float $amount): string
    {
        return sprintf('paypal captured %.2f', $amount);
    }
}

// The default order gateway, used when no live provider is reachable.
final class OfflineGateway implements PaymentGateway
{
    public function name(): string
    {
        return 'offline';
    }

    public function available(): bool
    {
        return true;
    }

    public function pay(float $amount): string
    {
        return sprintf('queued %.2f for offline processing', $amount);
    }
}

// --- Wiring -----------------------------------------------------------------

// Use the process-wide container so the app() helper reaches these bindings.
$container = app();

// Live providers, in priority order.
$container->set('gateway.stripe', static fn (): PaymentGateway => new StripeGateway(true));
$container->set('gateway.paypal', static fn (): PaymentGateway => new PaypalGateway(true));

// The fallback is registered with instance(): the prebuilt default the selector
// uses when nothing else is available. Good fit for an object that is ready to
// go and carries no per-request state.
$container->instance('gateway.offline', new OfflineGateway());

// factory() re-runs the selector on every resolve, so a change in provider
// availability is reflected immediately without re-wiring by hand.
$container->factory(PaymentGateway::class, static function (Container $c): PaymentGateway {
    foreach (['gateway.stripe', 'gateway.paypal'] as $id) {
        /** @var PaymentGateway $gateway */
        $gateway = $c->get($id);
        if ($gateway->available()) {
            return $gateway;
        }
    }

    return $c->get('gateway.offline');
});

// The friendly key for the abstraction. app('gateway') resolves the default
// gateway, so app('gateway')->pay(...) is a complete payment call.
$container->alias('gateway', PaymentGateway::class);

// --- Scenarios --------------------------------------------------------------

echo "both providers online\n";
echo '  ', app('gateway')->pay(10.00), "\n";

// Stripe goes down: app('gateway') falls through to paypal.
$container->set('gateway.stripe', static fn (): PaymentGateway => new StripeGateway(false));
echo "stripe unavailable\n";
echo '  ', app('gateway')->pay(20.00), "\n";

// Paypal goes down too: app('gateway') is now the default offline gateway.
$container->set('gateway.paypal', static fn (): PaymentGateway => new PaypalGateway(false));
echo "no provider available\n";
echo '  ', app('gateway')->pay(30.00), "\n";

printf("app('gateway') is : %s\n", app('gateway')->name());

// The default is the same prebuilt object every time.
printf("offline default stable : %s\n", $container->get('gateway.offline') === $container->get('gateway.offline') ? 'yes' : 'no');
