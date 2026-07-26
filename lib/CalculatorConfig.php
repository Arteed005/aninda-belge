<?php

define('CALCULATOR_CONFIG_DIR', __DIR__ . '/../templates/calculators');

function getAllCalculators(): array
{
    static $configs = null;
    if ($configs !== null) {
        return $configs;
    }

    $configs = [];
    foreach (glob(CALCULATOR_CONFIG_DIR . '/*.json') ?: [] as $file) {
        $slug = basename($file, '.json');
        if (!isValidSlug($slug)) {
            continue;
        }
        $data = json_decode(file_get_contents($file), true);
        if (is_array($data)) {
            $configs[$slug] = $data;
        }
    }
    return $configs;
}

function getCalculatorConfig(string $slug): ?array
{
    if (!isValidSlug($slug)) {
        return null;
    }

    $path = CALCULATOR_CONFIG_DIR . '/' . basename($slug) . '.json';
    if (!is_file($path)) {
        return null;
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}
