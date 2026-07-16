<?php

define('TEMPLATE_CONFIG_DIR', __DIR__ . '/../templates/configs');

function isValidSlug(string $slug): bool
{
    return (bool) preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug);
}

function getAllTemplateConfigs(): array
{
    static $configs = null;
    if ($configs !== null) {
        return $configs;
    }

    $configs = [];
    foreach (glob(TEMPLATE_CONFIG_DIR . '/*.json') ?: [] as $file) {
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

function getTemplateConfig(string $slug): ?array
{
    if (!isValidSlug($slug)) {
        return null;
    }

    $path = TEMPLATE_CONFIG_DIR . '/' . basename($slug) . '.json';
    if (!is_file($path)) {
        return null;
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}
