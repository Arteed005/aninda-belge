<?php

define('ADMIN_DOCS_PER_PAGE', 12);
define('ADMIN_CUSTOMERS_PER_PAGE', 10);
define('ADMIN_PAYMENTS_PER_PAGE', 20);

function formatDocNo(int $id): string
{
    return 'BLG-' . str_pad((string) $id, 5, '0', STR_PAD_LEFT);
}

function docIdFromDocNo(string $docNo): ?int
{
    if (preg_match('/^(BLG-)?(\d+)$/i', trim($docNo), $m)) {
        return (int) $m[2];
    }
    return null;
}

function adminInitials(string $name): string
{
    $parts = preg_split('/\s+/', trim($name));
    $parts = array_filter($parts);
    $initials = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1, 'UTF-8'), 'UTF-8'), $parts);
    return implode('', array_slice($initials, 0, 2)) ?: '?';
}

const ADMIN_AVATAR_COLORS = [
    'oklch(55% 0.14 152)', 'oklch(55% 0.1 250)', 'oklch(55% 0.12 180)',
    'oklch(58% 0.13 300)', 'oklch(55% 0.14 60)',
];

function adminAvatarColor(int $index): string
{
    return ADMIN_AVATAR_COLORS[$index % count(ADMIN_AVATAR_COLORS)];
}

function templateTitle(string $slug): string
{
    $cfg = getTemplateConfig($slug);
    return $cfg['title'] ?? $slug;
}

const ADMIN_CATEGORY_LABELS = [
    'sozlesmeler' => 'Sözleşmeler',
    'dilekceler' => 'Dilekçeler',
    'is-belgeleri' => 'İş Belgeleri',
    'kisisel-belgeler' => 'Kişisel Belgeler',
];

function getDashboardStats(): array
{
    $pdo = getPDO();
    return [
        'totalDocuments' => (int) $pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn(),
        'activeCustomers' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL')->fetchColumn(),
        'premiumUsers' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE is_premium = 1')->fetchColumn(),
        'unverifiedUsers' => (int) $pdo->query('SELECT COUNT(*) FROM users WHERE email_verified_at IS NULL')->fetchColumn(),
    ];
}

function getMonthlyDocumentTrend(int $months = 12): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
         FROM documents
         WHERE created_at >= :since
         GROUP BY ym"
    );
    $since = (new DateTime('first day of this month'))->modify('-' . ($months - 1) . ' months')->format('Y-m-d H:i:s');
    $stmt->execute(['since' => $since]);
    $counts = [];
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['ym']] = (int) $row['cnt'];
    }

    $monthLabels = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    $trend = [];
    $cursor = new DateTime('first day of this month');
    $cursor->modify('-' . ($months - 1) . ' months');
    for ($i = 0; $i < $months; $i++) {
        $ym = $cursor->format('Y-m');
        $trend[] = ['label' => $monthLabels[(int) $cursor->format('n') - 1], 'value' => $counts[$ym] ?? 0];
        $cursor->modify('+1 month');
    }

    $max = max(1, max(array_column($trend, 'value')));
    $last = count($trend) - 1;
    return array_map(function ($t, $i) use ($max, $last) {
        return [
            'label' => $t['label'],
            'height' => (int) round(($t['value'] / $max) * 120),
            'opacity' => $i === $last ? 1 : round(0.55 + ($t['value'] / $max) * 0.3, 2),
            'value' => $t['value'],
        ];
    }, $trend, array_keys($trend));
}

function getDocTypeBreakdown(int $limit = 5): array
{
    $pdo = getPDO();
    $rows = $pdo->query('SELECT template_slug, COUNT(*) AS cnt FROM documents GROUP BY template_slug ORDER BY cnt DESC')->fetchAll();
    $total = array_sum(array_column($rows, 'cnt'));
    if ($total === 0) {
        return [];
    }

    $top = array_slice($rows, 0, $limit - 1);
    $rest = array_slice($rows, $limit - 1);
    $result = [];
    foreach ($top as $row) {
        $result[] = [
            'name' => templateTitle($row['template_slug']),
            'pct' => (int) round($row['cnt'] / $total * 100),
        ];
    }
    if ($rest) {
        $restCount = array_sum(array_column($rest, 'cnt'));
        $result[] = ['name' => 'Diğer', 'pct' => (int) round($restCount / $total * 100)];
    }
    return $result;
}

function getTemplateCategoryBreakdown(): array
{
    $pdo = getPDO();
    $rows = $pdo->query('SELECT template_slug, COUNT(*) AS cnt FROM documents GROUP BY template_slug')->fetchAll();
    $total = 0;
    $byCategory = [];
    foreach ($rows as $row) {
        $cfg = getTemplateConfig($row['template_slug']);
        $category = $cfg['category'] ?? 'diger';
        $byCategory[$category] = ($byCategory[$category] ?? 0) + (int) $row['cnt'];
        $total += (int) $row['cnt'];
    }
    if ($total === 0) {
        return [];
    }
    arsort($byCategory);
    $result = [];
    foreach ($byCategory as $category => $cnt) {
        $result[] = [
            'name' => ADMIN_CATEGORY_LABELS[$category] ?? $category,
            'pct' => (int) round($cnt / $total * 100),
        ];
    }
    return $result;
}

function getRecentDocumentsAdmin(int $limit = 8): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT d.id, d.template_slug, d.is_watermarked, d.created_at, d.expires_at,
                u.name AS customer_name, u.email AS customer_email
         FROM documents d
         LEFT JOIN users u ON u.id = d.user_id
         ORDER BY d.created_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return array_map('mapDocumentRow', $stmt->fetchAll());
}

function mapDocumentRow(array $row): array
{
    $isExpired = strtotime($row['expires_at']) < time();
    return [
        'id' => (int) $row['id'],
        'no' => formatDocNo((int) $row['id']),
        'customer' => $row['customer_name'] ?? 'Misafir',
        'customerEmail' => $row['customer_email'] ?? null,
        'type' => templateTitle($row['template_slug']),
        'isWatermarked' => (bool) $row['is_watermarked'],
        'isExpired' => $isExpired,
        'status' => $isExpired ? 'Süresi Dolmuş' : 'Aktif',
        'date' => (new DateTime($row['created_at']))->format('d M Y'),
    ];
}

function getDocumentsPaginated(string $filter, string $search, int $page, int $perPage = ADMIN_DOCS_PER_PAGE): array
{
    [$where, $params] = buildDocumentFilterClause($filter, $search);
    $pdo = getPDO();
    $offset = max(0, ($page - 1) * $perPage);

    $stmt = $pdo->prepare(
        "SELECT d.id, d.template_slug, d.is_watermarked, d.created_at, d.expires_at,
                u.name AS customer_name, u.email AS customer_email
         FROM documents d
         LEFT JOIN users u ON u.id = d.user_id
         $where
         ORDER BY d.created_at DESC
         LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_map('mapDocumentRow', $stmt->fetchAll());
}

function getDocumentCount(string $filter, string $search): int
{
    [$where, $params] = buildDocumentFilterClause($filter, $search);
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM documents d LEFT JOIN users u ON u.id = d.user_id $where"
    );
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->execute();
    return (int) $stmt->fetchColumn();
}

function buildDocumentFilterClause(string $filter, string $search): array
{
    $conditions = [];
    $params = [];

    if ($filter === 'active') {
        $conditions[] = 'd.expires_at >= NOW()';
    } elseif ($filter === 'expired') {
        $conditions[] = 'd.expires_at < NOW()';
    } elseif ($filter === 'watermarked') {
        $conditions[] = 'd.is_watermarked = 1';
    }

    $search = trim($search);
    if ($search !== '') {
        $docId = docIdFromDocNo($search);
        $searchConditions = ['u.name LIKE :search', 'u.email LIKE :search'];
        $params['search'] = '%' . $search . '%';
        if ($docId !== null) {
            $searchConditions[] = 'd.id = :docId';
            $params['docId'] = $docId;
        }
        $conditions[] = '(' . implode(' OR ', $searchConditions) . ')';
    }

    $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
    return [$where, $params];
}

function getRecentCustomers(int $limit = 5): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT u.id, u.name, u.email, u.created_at,
                (SELECT COUNT(*) FROM documents d WHERE d.user_id = u.id) AS doc_count
         FROM users u
         ORDER BY u.created_at DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return array_map(fn($row, $i) => mapCustomerRow($row, $i), $rows, array_keys($rows));
}

function mapCustomerRow(array $row, int $index): array
{
    $expiresAt = $row['premium_expires_at'] ?? null;
    $isPremium = (bool) ($row['is_premium'] ?? false) && ($expiresAt === null || strtotime($expiresAt) > time());
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'initials' => adminInitials($row['name']),
        'avatarBg' => adminAvatarColor($index),
        'docCount' => (int) $row['doc_count'],
        'isPremium' => $isPremium,
        'premiumExpiresAt' => $isPremium && $expiresAt !== null ? (new DateTime($expiresAt))->format('d M Y') : null,
        'isVerified' => !empty($row['email_verified_at']),
        'since' => (new DateTime($row['created_at']))->format('d M Y'),
    ];
}

function getCustomersPaginated(int $page, int $perPage = ADMIN_CUSTOMERS_PER_PAGE): array
{
    $pdo = getPDO();
    $offset = max(0, ($page - 1) * $perPage);
    $stmt = $pdo->prepare(
        "SELECT u.id, u.name, u.email, u.created_at, u.is_premium, u.premium_expires_at, u.email_verified_at,
                (SELECT COUNT(*) FROM documents d WHERE d.user_id = u.id) AS doc_count
         FROM users u
         ORDER BY u.created_at DESC
         LIMIT :limit OFFSET :offset"
    );
    $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $rows = $stmt->fetchAll();
    return array_map(fn($row, $i) => mapCustomerRow($row, $i), $rows, array_keys($rows));
}

function getCustomerCount(): int
{
    return (int) getPDO()->query('SELECT COUNT(*) FROM users')->fetchColumn();
}

function getCustomerKpis(): array
{
    $pdo = getPDO();
    $total = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $newThisMonth = (int) $pdo->query(
        "SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
    )->fetchColumn();
    $totalDocs = (int) $pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    $verified = (int) $pdo->query('SELECT COUNT(*) FROM users WHERE email_verified_at IS NOT NULL')->fetchColumn();

    $avgDocs = $total > 0 ? round($totalDocs / $total, 1) : 0;
    $activeRate = $total > 0 ? round($verified / $total * 100) : 0;

    return [
        ['label' => 'Toplam Müşteri', 'value' => number_format($total, 0, ',', '.')],
        ['label' => 'Bu Ay Yeni', 'value' => number_format($newThisMonth, 0, ',', '.')],
        ['label' => 'Ort. Belge/Müşteri', 'value' => number_format($avgDocs, 1, ',', '.')],
        ['label' => 'Doğrulanmış Oran', 'value' => '%' . $activeRate],
    ];
}

function getReportKpis(): array
{
    $pdo = getPDO();
    $ytd = (int) $pdo->query(
        "SELECT COUNT(*) FROM documents WHERE created_at >= DATE_FORMAT(NOW(), '%Y-01-01')"
    )->fetchColumn();
    $thisMonth = (int) $pdo->query(
        "SELECT COUNT(*) FROM documents WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
    )->fetchColumn();
    $totalDocs = (int) $pdo->query('SELECT COUNT(*) FROM documents')->fetchColumn();
    $watermarked = (int) $pdo->query('SELECT COUNT(*) FROM documents WHERE is_watermarked = 1')->fetchColumn();

    $daysElapsed = max(1, (int) (new DateTime())->format('j'));
    $perDay = round($thisMonth / $daysElapsed, 1);
    $watermarkRate = $totalDocs > 0 ? round($watermarked / $totalDocs * 100) : 0;

    return [
        ['label' => 'Toplam Belge (YTD)', 'value' => number_format($ytd, 0, ',', '.')],
        ['label' => 'Bu Ay Oluşturulan', 'value' => number_format($thisMonth, 0, ',', '.')],
        ['label' => 'Günlük Ortalama', 'value' => number_format($perDay, 1, ',', '.')],
        ['label' => 'Filigranlı Oranı', 'value' => '%' . $watermarkRate],
    ];
}

function getTopCustomersByDocCount(int $limit = 5): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT u.name, COUNT(d.id) AS doc_count
         FROM users u
         JOIN documents d ON d.user_id = u.id
         GROUP BY u.id, u.name
         ORDER BY doc_count DESC
         LIMIT :limit"
    );
    $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(fn($row) => ['name' => $row['name'], 'docCount' => (int) $row['doc_count']], $stmt->fetchAll());
}

function deleteDocumentAdmin(int $id): void
{
    $stmt = getPDO()->prepare('DELETE FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
}

function toggleUserPremium(int $userId): void
{
    $stmt = getPDO()->prepare('UPDATE users SET is_premium = NOT is_premium, premium_expires_at = NULL WHERE id = :id');
    $stmt->execute(['id' => $userId]);
}

function getShopierPaymentCount(): int
{
    return (int) getPDO()->query('SELECT COUNT(*) FROM shopier_processed_orders')->fetchColumn();
}

function getShopierPaymentKpis(): array
{
    $pdo = getPDO();
    $total = (int) $pdo->query('SELECT COUNT(*) FROM shopier_processed_orders')->fetchColumn();
    $thisMonth = (int) $pdo->query(
        "SELECT COUNT(*) FROM shopier_processed_orders WHERE created_at >= DATE_FORMAT(NOW(), '%Y-%m-01')"
    )->fetchColumn();

    return [
        ['label' => 'Toplam Ödeme', 'value' => number_format($total, 0, ',', '.')],
        ['label' => 'Bu Ay', 'value' => number_format($thisMonth, 0, ',', '.')],
        ['label' => 'Toplam Tahmini Gelir', 'value' => '₺' . number_format($total * PREMIUM_PRICE_TRY, 0, ',', '.')],
        ['label' => 'Bu Ay Tahmini Gelir', 'value' => '₺' . number_format($thisMonth * PREMIUM_PRICE_TRY, 0, ',', '.')],
    ];
}

function getMonthlyPaymentTrend(int $months = 6): array
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        "SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
         FROM shopier_processed_orders
         WHERE created_at >= :since
         GROUP BY ym"
    );
    $since = (new DateTime('first day of this month'))->modify('-' . ($months - 1) . ' months')->format('Y-m-d H:i:s');
    $stmt->execute(['since' => $since]);
    $counts = [];
    foreach ($stmt->fetchAll() as $row) {
        $counts[$row['ym']] = (int) $row['cnt'];
    }

    $monthLabels = ['Oca', 'Şub', 'Mar', 'Nis', 'May', 'Haz', 'Tem', 'Ağu', 'Eyl', 'Eki', 'Kas', 'Ara'];
    $trend = [];
    $cursor = new DateTime('first day of this month');
    $cursor->modify('-' . ($months - 1) . ' months');
    for ($i = 0; $i < $months; $i++) {
        $ym = $cursor->format('Y-m');
        $trend[] = ['label' => $monthLabels[(int) $cursor->format('n') - 1], 'value' => $counts[$ym] ?? 0];
        $cursor->modify('+1 month');
    }

    $max = max(1, max(array_column($trend, 'value')));
    $last = count($trend) - 1;
    return array_map(function ($t, $i) use ($max, $last) {
        return [
            'label' => $t['label'],
            'height' => (int) round(($t['value'] / $max) * 120),
            'opacity' => $i === $last ? 1 : round(0.55 + ($t['value'] / $max) * 0.3, 2),
            'value' => $t['value'],
        ];
    }, $trend, array_keys($trend));
}

function getShopierPaymentsPaginated(int $page, int $perPage = ADMIN_PAYMENTS_PER_PAGE): array
{
    $pdo = getPDO();
    $offset = max(0, ($page - 1) * $perPage);
    $stmt = $pdo->prepare(
        'SELECT o.order_id, o.created_at, u.name, u.email
         FROM shopier_processed_orders o
         JOIN users u ON u.id = o.user_id
         ORDER BY o.created_at DESC
         LIMIT :limit OFFSET :offset'
    );
    $stmt->bindValue('limit', $perPage, PDO::PARAM_INT);
    $stmt->bindValue('offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    return array_map(static function (array $row): array {
        return [
            'orderId' => $row['order_id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'date' => (new DateTime($row['created_at']))->format('d M Y H:i'),
        ];
    }, $stmt->fetchAll());
}

function deleteUserAdmin(int $userId): void
{
    $stmt = getPDO()->prepare('DELETE FROM users WHERE id = :id');
    $stmt->execute(['id' => $userId]);
}

const ADMIN_TR_MONTHS = [
    1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
    7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık',
];

const ADMIN_TR_DAYS = [
    'Monday' => 'Pazartesi', 'Tuesday' => 'Salı', 'Wednesday' => 'Çarşamba', 'Thursday' => 'Perşembe',
    'Friday' => 'Cuma', 'Saturday' => 'Cumartesi', 'Sunday' => 'Pazar',
];

function turkishDateLong(DateTime $date): string
{
    return $date->format('j') . ' ' . ADMIN_TR_MONTHS[(int) $date->format('n')] . ' ' . $date->format('Y')
        . ' ' . ADMIN_TR_DAYS[$date->format('l')];
}

function getDocumentById(int $id): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM documents WHERE id = :id');
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}
