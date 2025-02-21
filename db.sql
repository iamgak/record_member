CREATE DATABASE atlanta_db;

USE atlanta_db;

CREATE TABLE team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    role VARCHAR(50) NOT NULL,
    designation VARCHAR(50) NOT NULL,
    photo TINYINT(1) DEFAULT 0,
    active TINYINT(1) DEFAULT 1,
    married TINYINT(1) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    version INT DEFAULT 1,
    dob DATE NOT NULL, 
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);
