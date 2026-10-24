<?php

use Strukt\Alias;
use Strukt\Env;
use Strukt\Ref;
use Strukt\Raise;

beforeEach(function (): void {
    Alias::reset();
    Env::reset();
    Raise::reset();
});

test('aliases are indexed and wildcard queries are efficient', function (): void {
    expect(alias('user', 'App\\User'))->toBe('App\\User')
        ->and(alias('user'))->toBe('App\\User')
        ->and(alias('u*'))->toBe(['user' => 'App\\User']);
});

test('environment files parse comments, booleans, quotes, and equals signs', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'strukt-env-');
    file_put_contents($path, "# comment\nexport APP_NAME=Payroll\nDEBUG=true\nSECRET=\"a=b\"\n");

    Env::withFile($path);

    expect(Env::get('APP_NAME'))->toBe('Payroll')
        ->and(Env::get('DEBUG'))->toBeTrue()
        ->and(Env::get('SECRET'))->toBe('a=b');

    unlink($path);
});

test('reflection caches classes and invokes methods and properties', function (): void {
    $reference = Ref::create(ReflectionFixture::class)->makeArgs(['Ada']);
    $reference->method('setRole')->invoke('admin');

    expect($reference->method('name')->invoke())->toBe('Ada')
        ->and($reference->prop('role')->get())->toBe('admin');
});

test('raise is a runtime exception with a process-local error log', function (): void {
    expect(fn (): never => raise('failed', 422))->toThrow(Raise::class);
    expect(Raise::errors())->toBe(['failed']);
});

test('base helper registry includes every base helper', function (): void {
    expect(helper('base'))->toContain(
        'helper_add',
        'is_map',
        'alias',
        'env',
        'ref',
        'raise',
        'timezone',
        'negate',
        'notnull',
    );
});

test('dd and dump resolve to symfony/var-dumper when its functions file loads', function (): void {
    expect(function_exists('dd'))->toBeTrue()
        ->and(function_exists('dump'))->toBeTrue()
        ->and(class_exists(\Symfony\Component\VarDumper\VarDumper::class))->toBeTrue()
        ->and(helper('base'))->not->toContain('dd');
});

final class ReflectionFixture
{
    private string $name;
    private string $role = 'guest';

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }
}
