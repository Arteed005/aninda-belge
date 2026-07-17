<?php

/**
 * Data shaper for "kind": "resume" documents (currently just the CV). Unlike
 * ClauseRenderer, resume fields aren't interpolated into sentences — they're
 * displayed as labeled data — so a plain array is enough. Used identically by
 * sablon.php's SSR preview and templates/resume-shell.php's PDF output.
 */
function renderResumeData(array $config, array $values, array $groupEntries, ?string $photoDataUri = null): array
{
    $get = function (string $name) use ($values): string {
        return trim((string) ($values[$name] ?? ''));
    };

    $splitList = function (string $csv): array {
        $items = array_map('trim', explode(',', $csv));
        return array_values(array_filter($items, fn($v) => $v !== ''));
    };

    $sections = [];
    foreach ($config['groups'] ?? [] as $group) {
        $sections[] = [
            'title' => $group['title'],
            'fields' => $group['fields'],
            'entries' => $groupEntries[$group['key']] ?? [],
        ];
    }

    return [
        'photo' => $photoDataUri,
        'full_name' => $get('full_name'),
        'title' => $get('title'),
        'email' => $get('email'),
        'phone' => $get('phone'),
        'location' => $get('location'),
        'linkedin' => $get('linkedin'),
        'summary' => $get('summary'),
        'skills' => $splitList($get('skills')),
        'languages' => $splitList($get('languages')),
        'hobbies' => $splitList($get('hobbies')),
        'sections' => $sections,
    ];
}
