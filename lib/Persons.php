<?php

function savePerson(int $userId, string $fullName, ?string $personType, ?string $tcNo, ?string $phone, ?string $email, ?string $address, ?string $notes): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO persons (user_id, full_name, person_type, tc_no, phone, email, address, notes)
         VALUES (:user_id, :full_name, :person_type, :tc_no, :phone, :email, :address, :notes)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'full_name' => $fullName,
        'person_type' => $personType,
        'tc_no' => $tcNo,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
        'notes' => $notes,
    ]);
    return (int) $pdo->lastInsertId();
}

function updatePersonForUser(int $id, int $userId, string $fullName, ?string $personType, ?string $tcNo, ?string $phone, ?string $email, ?string $address, ?string $notes): bool
{
    $stmt = getPDO()->prepare(
        'UPDATE persons SET full_name = :full_name, person_type = :person_type, tc_no = :tc_no, phone = :phone, email = :email, address = :address, notes = :notes
         WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'full_name' => $fullName,
        'person_type' => $personType,
        'tc_no' => $tcNo,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
        'notes' => $notes,
        'id' => $id,
        'user_id' => $userId,
    ]);
    return $stmt->rowCount() > 0;
}

function deletePersonForUser(int $id, int $userId): bool
{
    $stmt = getPDO()->prepare('DELETE FROM persons WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    return $stmt->rowCount() > 0;
}

function getPersonsForUser(int $userId): array
{
    $stmt = getPDO()->prepare(
        'SELECT id, full_name, person_type, tc_no, phone, email, address, notes
         FROM persons
         WHERE user_id = :user_id
         ORDER BY full_name ASC'
    );
    $stmt->execute(['user_id' => $userId]);
    return array_map('mapPersonRow', $stmt->fetchAll());
}

function getRecentPersonsForUser(int $userId, int $limit = 5): array
{
    $stmt = getPDO()->prepare(
        'SELECT id, full_name, person_type, tc_no, phone, email, address, notes
         FROM persons
         WHERE user_id = :user_id
         ORDER BY created_at DESC
         LIMIT ' . max(1, $limit)
    );
    $stmt->execute(['user_id' => $userId]);
    return array_map('mapPersonRow', $stmt->fetchAll());
}

function getPersonByIdForUser(int $id, int $userId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM persons WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getPersonCountForUser(int $userId): int
{
    $stmt = getPDO()->prepare('SELECT COUNT(*) FROM persons WHERE user_id = :user_id');
    $stmt->execute(['user_id' => $userId]);
    return (int) $stmt->fetchColumn();
}

function savePersonAddresses(int $personId, array $addresses): void
{
    $pdo = getPDO();
    $pdo->prepare('DELETE FROM person_addresses WHERE person_id = :person_id')->execute(['person_id' => $personId]);

    $stmt = $pdo->prepare('INSERT INTO person_addresses (person_id, label, address) VALUES (:person_id, :label, :address)');
    foreach ($addresses as $entry) {
        $address = trim((string) ($entry['address'] ?? ''));
        if ($address === '') {
            continue;
        }
        $label = trim((string) ($entry['label'] ?? ''));
        $stmt->execute([
            'person_id' => $personId,
            'label' => $label !== '' ? $label : null,
            'address' => $address,
        ]);
    }
}

function getPersonAddresses(int $personId): array
{
    $stmt = getPDO()->prepare('SELECT id, label, address FROM person_addresses WHERE person_id = :person_id ORDER BY id ASC');
    $stmt->execute(['person_id' => $personId]);
    return $stmt->fetchAll();
}

function mapPersonRow(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'full_name' => $row['full_name'],
        'person_type' => $row['person_type'],
        'tc_no' => $row['tc_no'],
        'phone' => $row['phone'],
        'email' => $row['email'],
        'address' => $row['address'],
        'notes' => $row['notes'],
    ];
}
