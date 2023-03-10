CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    title TEXT NOT NULL,
    body TEXT,
    created_at INTEGER,
    updated_at INTEGER,
    created_by TEXT
)
----
CREATE TABLE IF NOT EXISTS replies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    body TEXT,
    created_at INTEGER,
    updated_at INTEGER,
    created_by TEXT
)
----
# this is a comment

until next boundary
----
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT,
    email TEXT,
    password TEXT,
    refresh TEXT,
    avatar TEXT,
    created_at INTEGER DEFAULT CURRENT_TIMESTAMP,
    updated_at INTEGER DEFAULT CURRENT_TIMESTAMP
)