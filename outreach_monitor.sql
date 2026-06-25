-- Outreach Monitor — PostgreSQL schema + seed data

CREATE TABLE IF NOT EXISTS users (
    user_id SERIAL PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'team_member'
        CHECK (role IN ('admin','manager','supervisor','team_member')),
    status VARCHAR(10) NOT NULL DEFAULT 'active'
        CHECK (status IN ('active','inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS programs (
    program_id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    start_date DATE,
    end_date DATE,
    manager_id INT REFERENCES users(user_id) ON DELETE SET NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active'
        CHECK (status IN ('active','completed','inactive')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS activities (
    activity_id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    program_id INT REFERENCES programs(program_id) ON DELETE SET NULL,
    assigned_to INT REFERENCES users(user_id) ON DELETE SET NULL,
    deadline DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending','in_progress','completed')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS milestones (
    milestone_id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    program_id INT REFERENCES programs(program_id) ON DELETE SET NULL,
    target_date DATE,
    status VARCHAR(20) NOT NULL DEFAULT 'not_achieved'
        CHECK (status IN ('not_achieved','achieved')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS evaluations (
    evaluation_id SERIAL PRIMARY KEY,
    activity_id INT REFERENCES activities(activity_id) ON DELETE SET NULL,
    supervisor_id INT REFERENCES users(user_id) ON DELETE SET NULL,
    score INT NOT NULL,
    feedback TEXT,
    evaluated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS notifications (
    notification_id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(user_id) ON DELETE CASCADE,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'general',
    is_read SMALLINT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed users (password for all: "password")
INSERT INTO users (full_name, email, password, role, status) VALUES
('Admin User',    'admin@outreach.com',      '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin',       'active'),
('Maria Manager', 'manager@outreach.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager',     'active'),
('Sam Supervisor','supervisor@outreach.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'supervisor',  'active'),
('Tom Member',    'member@outreach.com',     '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'team_member', 'active');
