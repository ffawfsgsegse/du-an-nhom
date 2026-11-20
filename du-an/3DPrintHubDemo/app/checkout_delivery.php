<?php
declare(strict_types=1);
defined('APP_BOOTSTRAPPED') || exit;

function checkout_delivery_fields(array $source): array
{
    $fields = [];
    foreach (['shipping_street', 'shipping_ward', 'shipping_district', 'shipping_province'] as $key) {
        $value = $source[$key] ?? '';
        $fields[$key] = is_string($value) ? trim(preg_replace('/\s+/u', ' ', $value) ?? '') : '';
    }
    return $fields;
}

function checkout_delivery_address(array $fields): string
{
    return implode(', ', array_filter($fields, static fn (string $value): bool => $value !== ''));
}
