<?php

declare(strict_types=1);

require __DIR__.'/../../vendor/autoload.php';

use Strukt\Container\Container;

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
        return sprintf('stripe charged %.2f', $amount);
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
        return sprintf('paypal captured %.2f', $amount);
    }
}

// Checkout depends only on the abstraction, never on a concrete gateway.
final class Checkout
{
    public function __construct(private PaymentGateway $gateway)
    {
    }

    public function pay(float $amount): string
    {
        return $this->gateway->name().' -> '.$this->gateway->charge($amount);
    }
}

$container = new Container();

$container->set(PaymentGateway::class, static fn (): PaymentGateway => new StripeGateway());
printf("stripe : %s\n", $container->get(Checkout::class)->pay(42.0));

// Swapping the implementation changes nothing for Checkout. set() clears any
// cached instance, so the next resolve picks up the new gateway.
$container->set(PaymentGateway::class, static fn (): PaymentGateway => new PaypalGateway());
printf("paypal : %s\n", $container->get(Checkout::class)->pay(42.0));
