CREATE TABLE IF NOT EXISTS languages (
    id TINYINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(30) NOT NULL UNIQUE
);
INSERT IGNORE INTO languages (name) VALUES 
('Pascal'),('C'),('C++'),('JavaScript'),('PHP'),('Python'),('Java'),('Haskell'),('Clojure'),('Prolog'),('Scala'),('Go');

CREATE TABLE IF NOT EXISTS applications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('male','female') NOT NULL,
    biography TEXT,
    contract_accepted TINYINT(1) NOT NULL DEFAULT 0,
    login VARCHAR(50) UNIQUE,
    password_hash VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS application_languages (
    application_id INT UNSIGNED NOT NULL,
    language_id TINYINT UNSIGNED NOT NULL,
    PRIMARY KEY (application_id, language_id),
    FOREIGN KEY (application_id) REFERENCES applications(id) ON DELETE CASCADE,
    FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
);