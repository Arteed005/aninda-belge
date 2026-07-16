<?php

function saveDocument(?int $userId, string $templateSlug, array $formData, bool $isWatermarked): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO documents (user_id, template_slug, form_data, is_watermarked, expires_at)
         VALUES (:user_id, :template_slug, :form_data, :is_watermarked, :expires_at)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'template_slug' => $templateSlug,
        'form_data' => json_encode($formData, JSON_UNESCAPED_UNICODE),
        'is_watermarked' => $isWatermarked ? 1 : 0,
        'expires_at' => (new DateTime('+' . RETENTION_DAYS_DEFAULT . ' days'))->format('Y-m-d H:i:s'),
    ]);
    return (int) $pdo->lastInsertId();
}
