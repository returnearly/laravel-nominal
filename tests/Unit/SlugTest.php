<?php

declare(strict_types=1);

use ReturnEarly\Nominal\Support\Slug;

it('sanitizes queue names into valid heartbeat slugs', function (): void {
    expect(Slug::sanitize('checks.local'))->toBe('checks-local')
        ->and(Slug::sanitize('Mail Queue'))->toBe('mail-queue')
        ->and(Slug::sanitize('--Foo--Bar--'))->toBe('foo-bar')
        ->and(Slug::sanitize('@@@'))->toBe('monitor');
});

it('validates nominal slug shape', function (): void {
    expect(Slug::isValid('default'))->toBeTrue()
        ->and(Slug::isValid('checks-local'))->toBeTrue()
        ->and(Slug::isValid('Bad_Slug'))->toBeFalse()
        ->and(Slug::isValid('-leading'))->toBeFalse();
});
