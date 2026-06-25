<?php
$db_path = __DIR__ . '/database/outreach.db';

try {
    $pdo = new PDO('sqlite:' . $db_path, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->exec('PRAGMA foreign_keys = ON');
    _initDb($pdo);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

function _initDb(PDO $pdo): void
{
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        user_id    INTEGER PRIMARY KEY AUTOINCREMENT,
        full_name  TEXT NOT NULL,
        email      TEXT NOT NULL UNIQUE,
        password   TEXT NOT NULL,
        role       TEXT NOT NULL DEFAULT 'team_member',
        status     TEXT NOT NULL DEFAULT 'active',
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS programs (
        program_id  INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        description TEXT,
        start_date  TEXT,
        end_date    TEXT,
        manager_id  INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
        status      TEXT NOT NULL DEFAULT 'active',
        created_at  TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS activities (
        activity_id INTEGER PRIMARY KEY AUTOINCREMENT,
        title       TEXT NOT NULL,
        description TEXT,
        program_id  INTEGER REFERENCES programs(program_id) ON DELETE SET NULL,
        assigned_to INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
        deadline    TEXT,
        status      TEXT NOT NULL DEFAULT 'pending',
        created_at  TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS milestones (
        milestone_id INTEGER PRIMARY KEY AUTOINCREMENT,
        title        TEXT NOT NULL,
        program_id   INTEGER REFERENCES programs(program_id) ON DELETE SET NULL,
        target_date  TEXT,
        status       TEXT NOT NULL DEFAULT 'not_achieved',
        created_at   TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS evaluations (
        evaluation_id INTEGER PRIMARY KEY AUTOINCREMENT,
        activity_id   INTEGER REFERENCES activities(activity_id) ON DELETE SET NULL,
        supervisor_id INTEGER REFERENCES users(user_id) ON DELETE SET NULL,
        score         INTEGER NOT NULL,
        feedback      TEXT,
        evaluated_at  TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS notifications (
        notification_id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id         INTEGER REFERENCES users(user_id) ON DELETE CASCADE,
        message         TEXT NOT NULL,
        type            TEXT DEFAULT 'general',
        is_read         INTEGER DEFAULT 0,
        created_at      TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    $count = (int)$pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($count === 0) {
        $hash = password_hash('password', PASSWORD_BCRYPT);
        $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute(['Admin User',     'admin@outreach.com',      $hash, 'admin',       'active']);
        $stmt->execute(['Maria Manager',  'manager@outreach.com',    $hash, 'manager',     'active']);
        $stmt->execute(['Sam Supervisor', 'supervisor@outreach.com', $hash, 'supervisor',  'active']);
        $stmt->execute(['Tom Member',     'member@outreach.com',     $hash, 'team_member', 'active']);
    }
}
