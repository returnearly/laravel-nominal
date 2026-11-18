<?php

declare(strict_types=1);

namespace ReturnEarly\Nominal\Support;

final class Slug
{
    /**
     * Normalize a value to a Nominal heartbeat slug:
     * lowercase letters, numbers, and single hyphens; max 64 chars.
     */
    public static function sanitize(string $value): string
    {
        $slug = strtolower(trim($value));
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? '';
        $slug = trim($slug, '-');
        $slug = preg_replace('/-+/', '-', $slug) ?? '';

        if ($slug === '') {
            $slug = 'monitor';
        }

        return substr($slug, 0, 64);
    }

    public static function isValid(string $slug): bool
    {
        return strlen($slug) <= 64
            && preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug) === 1;
    }
}
