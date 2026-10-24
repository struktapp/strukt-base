<?php

use Strukt\Env;

beforeEach(function (): void {
    Env::reset();
});

test('a real environment variable wins over the loaded file', function (): void {
    $path = tempnam(sys_get_temp_dir(), 'strukt-env-');
    file_put_contents($path, "SHARED=from-file\nONLY_FILE=file-value\n");

    putenv('SHARED=from-shell');
    try {
        Env::withFile($path);

        expect(Env::get('SHARED'))->toBe('from-shell')
            ->and(Env::get('ONLY_FILE'))->toBe('file-value')
            ->and(Env::has('SHARED'))->toBeTrue();
    } finally {
        putenv('SHARED');
    }

    unlink($path);
});

test('withFile tolerates a missing file when not required', function (): void {
    Env::withFile('/no/such/env', required: false);

    expect(Env::has('WHATEVER'))->toBeFalse();
});

test('withFile still throws for a missing file by default', function (): void {
    expect(fn () => Env::withFile('/no/such/env'))->toThrow(InvalidArgumentException::class);
});