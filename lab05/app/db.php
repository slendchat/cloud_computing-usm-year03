<?php
declare(strict_types=1);

/**
 * Basic helpers to manage master/read-replica connections.
 * The defaults follow the lab RDS hosts and credentials, but can be overridden with env vars.
 */
function appConfig(): array
{
    return [
        'master_host'  => getenv('DB_MASTER_HOST') ?: 'project-rds-mysql-prod',
        'replica_host' => getenv('DB_REPLICA_HOST') ?: 'project-rds-mysql-read-replica',
        'db_name'      => getenv('DB_NAME') ?: 'project_db',
        'user'         => getenv('DB_USER') ?: 'admin',
        'password'     => getenv('DB_PASS') ?: '',
        'port'         => getenv('DB_PORT') ?: '3306',
    ];
}

function connectPdo(string $host, array $config): PDO
{
    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        $host,
        $config['port'],
        $config['db_name']
    );

    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    return new PDO($dsn, $config['user'], $config['password'], $options);
}

function masterPdo(): PDO
{
    static $pdo;
    if (!$pdo) {
        $config = appConfig();
        $pdo = connectPdo($config['master_host'], $config);
    }
    return $pdo;
}

function replicaPdo(): PDO
{
    static $pdo;
    if (!$pdo) {
        $config = appConfig();
        $pdo = connectPdo($config['replica_host'], $config);
    }
    return $pdo;
}

function fetchCategories(PDO $pdo): array
{
    $stmt = $pdo->query('SELECT id, name FROM categories ORDER BY id');
    return $stmt->fetchAll();
}

function fetchTodos(PDO $pdo): array
{
    $stmt = $pdo->query(
        'SELECT t.id, t.title, t.status, t.category_id, c.name AS category_name
         FROM todos t
         JOIN categories c ON c.id = t.category_id
         ORDER BY t.id DESC'
    );
    return $stmt->fetchAll();
}

function fetchTodo(PDO $pdo, int $id): ?array
{
    $stmt = $pdo->prepare(
        'SELECT t.id, t.title, t.status, t.category_id, c.name AS category_name
         FROM todos t
         JOIN categories c ON c.id = t.category_id
         WHERE t.id = :id'
    );
    $stmt->execute(['id' => $id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function createTodo(PDO $pdo, string $title, int $categoryId, string $status): int
{
    $stmt = $pdo->prepare('INSERT INTO todos (title, category_id, status) VALUES (:title, :category_id, :status)');
    $stmt->execute([
        'title' => $title,
        'category_id' => $categoryId,
        'status' => $status,
    ]);
    return (int)$pdo->lastInsertId();
}

function updateTodo(PDO $pdo, int $id, string $title, int $categoryId, string $status): bool
{
    $stmt = $pdo->prepare(
        'UPDATE todos SET title = :title, category_id = :category_id, status = :status WHERE id = :id'
    );
    return $stmt->execute([
        'id' => $id,
        'title' => $title,
        'category_id' => $categoryId,
        'status' => $status,
    ]);
}

function deleteTodo(PDO $pdo, int $id): bool
{
    $stmt = $pdo->prepare('DELETE FROM todos WHERE id = :id');
    return $stmt->execute(['id' => $id]);
}
