<?php

const PROPERTY_FIELDS = [
    'title', 'province', 'district', 'neighborhood', 'address', 'unit_no', 'floor',
    'room_count', 'gross_sqm', 'block_no', 'parcel_no', 'independent_section_no',
    'title_deed_info', 'rent_amount', 'deposit_amount', 'description',
];

function saveProperty(int $userId, array $data): int
{
    $pdo = getPDO();
    $columns = implode(', ', PROPERTY_FIELDS);
    $placeholders = implode(', ', array_map(static fn (string $f): string => ':' . $f, PROPERTY_FIELDS));
    $stmt = $pdo->prepare("INSERT INTO properties (user_id, $columns) VALUES (:user_id, $placeholders)");
    $params = ['user_id' => $userId];
    foreach (PROPERTY_FIELDS as $field) {
        $params[$field] = $data[$field] ?? null;
    }
    $stmt->execute($params);
    return (int) $pdo->lastInsertId();
}

function updatePropertyForUser(int $id, int $userId, array $data): bool
{
    $assignments = implode(', ', array_map(static fn (string $f): string => "$f = :$f", PROPERTY_FIELDS));
    $stmt = getPDO()->prepare("UPDATE properties SET $assignments WHERE id = :id AND user_id = :user_id");
    $params = ['id' => $id, 'user_id' => $userId];
    foreach (PROPERTY_FIELDS as $field) {
        $params[$field] = $data[$field] ?? null;
    }
    $stmt->execute($params);
    return $stmt->rowCount() > 0;
}

function deletePropertyForUser(int $id, int $userId): bool
{
    $stmt = getPDO()->prepare('DELETE FROM properties WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    return $stmt->rowCount() > 0;
}

function getPropertiesForUser(int $userId): array
{
    $stmt = getPDO()->prepare('SELECT * FROM properties WHERE user_id = :user_id ORDER BY title ASC');
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function getRecentPropertiesForUser(int $userId, int $limit = 5): array
{
    $stmt = getPDO()->prepare('SELECT * FROM properties WHERE user_id = :user_id ORDER BY created_at DESC LIMIT ' . max(1, $limit));
    $stmt->execute(['user_id' => $userId]);
    return $stmt->fetchAll();
}

function getPropertyByIdForUser(int $id, int $userId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM properties WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getPropertyCountForUser(int $userId): int
{
    $stmt = getPDO()->prepare('SELECT COUNT(*) FROM properties WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Replaces the owner set for a property. Every $personId must belong to $userId
 * (verified by the caller via getPersonByIdForUser) — this function trusts its input.
 */
function setPropertyOwners(int $propertyId, array $personIds): void
{
    $pdo = getPDO();
    $pdo->prepare('DELETE FROM property_owners WHERE property_id = :property_id')->execute(['property_id' => $propertyId]);

    $stmt = $pdo->prepare('INSERT INTO property_owners (property_id, person_id) VALUES (:property_id, :person_id)');
    $seen = [];
    foreach ($personIds as $personId) {
        $personId = (int) $personId;
        if ($personId <= 0 || isset($seen[$personId])) {
            continue;
        }
        $seen[$personId] = true;
        $stmt->execute(['property_id' => $propertyId, 'person_id' => $personId]);
    }
}

function getPropertyOwners(int $propertyId): array
{
    $stmt = getPDO()->prepare(
        'SELECT p.id, p.full_name, p.tc_no, p.phone, p.email, p.address
         FROM property_owners po
         JOIN persons p ON p.id = po.person_id
         WHERE po.property_id = :property_id
         ORDER BY po.id ASC'
    );
    $stmt->execute(['property_id' => $propertyId]);
    return $stmt->fetchAll();
}
