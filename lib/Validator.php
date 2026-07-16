<?php

define('FIELD_MAX_LENGTH', 2000);
define('MAX_CUSTOM_CLAUSES', 10);
define('CUSTOM_CLAUSE_MAX_LENGTH', 1000);
define('MAX_GROUP_ENTRIES', 15);
define('GROUP_TEXT_MAX_LENGTH', 200);
define('GROUP_TEXTAREA_MAX_LENGTH', 1000);

/**
 * Server-side validation — the client-side JS check is UX only, this is
 * the one that's actually trusted. Returns [errors, clean] where errors is
 * a field-name-keyed map of messages (empty = valid) and clean is the
 * sanitized form data to hand to renderClauses()/saveDocument().
 */
function validateFormData(array $config, array $post): array
{
    $errors = [];
    $clean = [];

    foreach ($config['fields'] ?? [] as $field) {
        $name = $field['name'];
        $type = $field['type'] ?? 'text';
        $required = !empty($field['required']);
        $label = $field['label'] ?? $name;

        $raw = $post[$name] ?? '';
        $raw = is_string($raw) ? trim($raw) : '';
        $raw = mb_substr($raw, 0, FIELD_MAX_LENGTH);

        if ($required && $raw === '') {
            $errors[$name] = $label . ' zorunludur.';
            $clean[$name] = '';
            continue;
        }

        if ($raw === '') {
            $clean[$name] = '';
            continue;
        }

        if ($type === 'date') {
            $date = DateTime::createFromFormat('Y-m-d', $raw);
            if (!$date || $date->format('Y-m-d') !== $raw) {
                $errors[$name] = $label . ' geçerli bir tarih olmalıdır.';
                $clean[$name] = '';
                continue;
            }
        }

        if ($type === 'select') {
            $allowed = array_column($field['options'] ?? [], 'value');
            if (!in_array($raw, $allowed, true)) {
                $errors[$name] = $label . ' için geçersiz seçim.';
                $clean[$name] = '';
                continue;
            }
        }

        $clean[$name] = $raw;
    }

    return [$errors, $clean];
}

/**
 * Sanitizes the user's own free-text "Ek Madde" entries — never trust the
 * JS-side count/length limits, this is the one that actually caps them.
 */
function validateCustomClauses(array $post): array
{
    $raw = $post['extra_clauses'] ?? [];
    if (!is_array($raw)) {
        return [];
    }

    $clean = [];
    foreach (array_slice($raw, 0, MAX_CUSTOM_CLAUSES) as $text) {
        $text = is_string($text) ? trim($text) : '';
        $text = mb_substr($text, 0, CUSTOM_CLAUSE_MAX_LENGTH);
        if ($text !== '') {
            $clean[] = $text;
        }
    }

    return $clean;
}

/**
 * Sanitizes repeatable resume groups (experience/education entries). Looser
 * than validateFormData(): sub-field "required" isn't enforced server-side
 * (a CV entry doesn't carry the legal weight a contract field does) — only
 * count/length caps and dropping entirely-empty entries.
 */
function validateRepeatableGroups(array $config, array $post): array
{
    $result = [];

    foreach ($config['groups'] ?? [] as $group) {
        $key = $group['key'];
        $raw = is_array($post[$key] ?? null) ? array_values($post[$key]) : [];

        $entries = [];
        foreach (array_slice($raw, 0, MAX_GROUP_ENTRIES) as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $clean = [];
            $hasContent = false;
            foreach ($group['fields'] as $field) {
                $val = is_string($entry[$field['name']] ?? null) ? trim($entry[$field['name']]) : '';
                $maxLen = ($field['type'] ?? 'text') === 'textarea' ? GROUP_TEXTAREA_MAX_LENGTH : GROUP_TEXT_MAX_LENGTH;
                $val = mb_substr($val, 0, $maxLen);
                $clean[$field['name']] = $val;
                if ($val !== '') {
                    $hasContent = true;
                }
            }

            if ($hasContent) {
                $entries[] = $clean;
            }
        }

        $result[$key] = $entries;
    }

    return $result;
}
