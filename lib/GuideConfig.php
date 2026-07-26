<?php

define('GUIDE_CONFIG_DIR', __DIR__ . '/../templates/guides');

function getAllGuides(): array
{
    static $configs = null;
    if ($configs !== null) {
        return $configs;
    }

    $configs = [];
    foreach (glob(GUIDE_CONFIG_DIR . '/*.json') ?: [] as $file) {
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

function getGuideConfig(string $slug): ?array
{
    if (!isValidSlug($slug)) {
        return null;
    }

    $path = GUIDE_CONFIG_DIR . '/' . basename($slug) . '.json';
    if (!is_file($path)) {
        return null;
    }

    $data = json_decode(file_get_contents($path), true);
    return is_array($data) ? $data : null;
}
