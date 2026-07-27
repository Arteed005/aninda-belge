<?php

function savePerson(int $userId, string $fullName, ?string $tcNo, ?string $phone, ?string $email, ?string $address): int
{
    $pdo = getPDO();
    $stmt = $pdo->prepare(
        'INSERT INTO persons (user_id, full_name, tc_no, phone, email, address)
         VALUES (:user_id, :full_name, :tc_no, :phone, :email, :address)'
    );
    $stmt->execute([
        'user_id' => $userId,
        'full_name' => $fullName,
        'tc_no' => $tcNo,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
    ]);
    return (int) $pdo->lastInsertId();
}

function updatePersonForUser(int $id, int $userId, string $fullName, ?string $tcNo, ?string $phone, ?string $email, ?string $address): bool
{
    $stmt = getPDO()->prepare(
        'UPDATE persons SET full_name = :full_name, tc_no = :tc_no, phone = :phone, email = :email, address = :address
         WHERE id = :id AND user_id = :user_id'
    );
    $stmt->execute([
        'full_name' => $fullName,
        'tc_no' => $tcNo,
        'phone' => $phone,
        'email' => $email,
        'address' => $address,
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
        'SELECT id, full_name, tc_no, phone, email, address
         FROM persons
         WHERE user_id = :user_id
         ORDER BY full_name ASC'
    );
    $stmt->execute(['user_id' => $userId]);
    return array_map(static function (array $row): array {
        return [
            'id' => (int) $row['id'],
            'full_name' => $row['full_name'],
            'tc_no' => $row['tc_no'],
            'phone' => $row['phone'],
            'email' => $row['email'],
            'address' => $row['address'],
        ];
    }, $stmt->fetchAll());
}

function getPersonByIdForUser(int $id, int $userId): ?array
{
    $stmt = getPDO()->prepare('SELECT * FROM persons WHERE id = :id AND user_id = :user_id');
    $stmt->execute(['id' => $id, 'user_id' => $userId]);
    $row = $stmt->fetch();
    return $row ?: null;
}
