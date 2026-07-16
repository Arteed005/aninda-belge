<?php

/**
 * Renders a template's clauses against submitted form data.
 * Used identically for the initial (empty) SSR preview, the client-side
 * JS live preview (which consumes the same config JSON), and the PDF output —
 * this is the single source of truth for "how a clause renders".
 *
 * Each clause becomes ['title' => string, 'lines' => array<array<segment>>].
 * A segment is either ['type' => 'text', 'value' => string] (literal, trusted
 * template text) or ['type' => 'field', 'name' => string, 'text' => string,
 * 'empty' => bool] (interpolated form value, already formatted, dash-if-empty).
 * Callers must htmlspecialchars() every segment's text before output.
 */

function renderClauses(array $config, array $formData): array
{
    $fieldFormats = [];
    foreach ($config['fields'] ?? [] as $field) {
        $fieldFormats[$field['name']] = $field['format'] ?? null;
    }

    $context = clauseAutoContext();

    $rendered = [];
    foreach ($config['clauses'] ?? [] as $clause) {
        $lines = [];
        foreach (explode("\n", $clause['template'] ?? '') as $lineTemplate) {
            $lines[] = renderClauseLine($lineTemplate, $formData, $fieldFormats, $context);
        }
        $rendered[] = [
            'title' => $clause['title'] ?? '',
            'lines' => $lines,
        ];
    }
    return $rendered;
}

function renderClauseLine(string $lineTemplate, array $formData, array $fieldFormats, array $context): array
{
    $parts = preg_split('/\{([a-zA-Z0-9_]+)\}/', $lineTemplate, -1, PREG_SPLIT_DELIM_CAPTURE);
    $segments = [];

    foreach ($parts as $i => $part) {
        if ($i % 2 === 0) {
            if ($part !== '') {
                $segments[] = ['type' => 'text', 'value' => $part];
            }
            continue;
        }

        $name = $part;
        if (array_key_exists($name, $context) && !array_key_exists($name, $formData)) {
            $segments[] = ['type' => 'text', 'value' => $context[$name]];
            continue;
        }

        $raw = $formData[$name] ?? '';
        $formatted = formatFieldValue($raw, $fieldFormats[$name] ?? null);
        $segments[] = ['type' => 'field', 'name' => $name] + $formatted;
    }

    return $segments;
}

function formatFieldValue($value, ?string $format): array
{
    $value = is_string($value) ? trim($value) : $value;
    if ($value === null || $value === '') {
        return ['text' => '—', 'empty' => true];
    }

    switch ($format) {
        case 'currency-try':
            return ['text' => formatCurrencyTry((string) $value), 'empty' => false];
        case 'date-tr':
            return ['text' => formatDateTr((string) $value), 'empty' => false];
        default:
            return ['text' => (string) $value, 'empty' => false];
    }
}

function formatCurrencyTry(string $value): string
{
    $digits = preg_replace('/[^0-9]/', '', $value);
    if ($digits !== '' && (int) $digits > 0) {
        return number_format((int) $digits, 0, ',', '.') . ' TL';
    }
    return $value . ' TL';
}

function formatDateTr(string $value): string
{
    $date = DateTime::createFromFormat('Y-m-d', $value);
    if ($date && $date->format('Y-m-d') === $value) {
        return $date->format('d.m.Y');
    }
    return $value;
}

function clauseAutoContext(): array
{
    return [
        'bugun_tarihi' => (new DateTime())->format('d.m.Y'),
    ];
}

/**
 * Turns the user's own free-text clauses (from the "Ek Maddeler" wizard step)
 * into the same {title, lines} shape renderClauses() produces, so callers can
 * array_merge() the two without pdf-shell.php/sablon.php knowing the difference.
 */
function renderCustomClauses(array $extraClauses): array
{
    $extraClauses = array_values($extraClauses);
    $rendered = [];
    foreach ($extraClauses as $i => $text) {
        $lines = [];
        foreach (explode("\n", (string) $text) as $lineText) {
            $lines[] = $lineText !== '' ? [['type' => 'text', 'value' => $lineText]] : [];
        }
        $rendered[] = [
            'title' => 'EK MADDE ' . ($i + 1),
            'lines' => $lines,
        ];
    }
    return $rendered;
}

/**
 * Renders one clause line's segments to an HTML string. Built by string
 * concatenation (not a foreach/if template) so no incidental whitespace
 * from PHP tag formatting ends up between adjacent segments — e.g. a
 * field immediately followed by punctuation like "{tc})" must not gain
 * a stray space before the closing paren.
 */
function renderLineHtml(array $line): string
{
    $html = '';
    foreach ($line as $segment) {
        if ($segment['type'] === 'text') {
            $html .= htmlspecialchars($segment['value']);
        } else {
            $class = 'fv ' . ($segment['empty'] ? 'fv-empty' : 'fv-filled');
            $html .= '<span class="' . $class . '" data-field="' . htmlspecialchars($segment['name']) . '">'
                . htmlspecialchars($segment['text']) . '</span>';
        }
    }
    return $html;
}

/**
 * mb_strtoupper() doesn't know Turkish dotted/dotless I rules (i -> İ, not I).
 * Map the lowercase Turkish letters first, then uppercase the rest normally.
 */
function trUpper(string $value): string
{
    $value = strtr($value, ['i' => 'İ', 'ı' => 'I']);
    return mb_strtoupper($value, 'UTF-8');
}
