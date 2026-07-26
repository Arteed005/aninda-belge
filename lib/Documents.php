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

function getDocumentsForUser(int $userId, int $page, int $perPage = ADMIN_DOCS_PER_PAGE): array
{
    $pdo = getPDO();
    $offset = max(0, ($page - 1) * $perPage);
    $stmt = $pdo->prepare(
        'SELECT id, template_slug, is_watermarked, created_at, expires_at
         FROM documents
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue('user_id', $userId, PDO::PARAM_INT);
    $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(static function (array $row): array {
        $isExpired = strtotime($row['expires_at']) < time();
        $config = getTemplateConfig($row['template_slug']);
        return [
            'id' => (int) $row['id'],
            'no' => formatDocNo((int) $row['id']),
            'type' => $config['title'] ?? $row['template_slug'],
            'category' => $config['category'] ?? null,
            'isResume' => ($config['kind'] ?? 'contract') === 'resume',
            'isWatermarked' => (bool) $row['is_watermarked'],
            'isExpired' => $isExpired,
            'status' => $isExpired ? 'Süresi Dolmuş' : 'Aktif',
            'date' => (new DateTime($row['created_at']))->format('d M Y'),
        ];
    }, $stmt->fetchAll());
}

function getDocumentCountForUser(int $userId): int
{
    $stmt = getPDO()->prepare('SELECT COUNT(*) FROM documents WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function getDocumentByIdForUser(int $id, int $userId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM documents WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
