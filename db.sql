-- schema design with necessary data
CREATE DATABASE atlanta_db;
USE atlanta_db;

CREATE TABLE atl_team_members (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    dob DATE NOT NULL, 
    address TEXT NOT NULL,
    designation INT UNSIGNED NOT NULL,
    role INT UNSIGNED NOT NULL,
    gender TINYINT(1) UNSIGNED NOT NULL,
    marital_status TINYINT(2) UNSIGNED DEFAULT 1,
    account_status TINYINT(2) UNSIGNED DEFAULT 1,
    is_deleted TINYINT(1) UNSIGNED DEFAULT 0,
    version INT UNSIGNED DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);


CREATE TABLE atl_type_designation (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL,
    role_id INT UNSIGNED NOT NULL
);

CREATE TABLE atl_type_role (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE atl_type_gender (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE atl_type_account_status (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);

CREATE TABLE atl_type_marital_status (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) UNIQUE NOT NULL
);


-- seed some dummy data 
INSERT INTO atl_type_designation (name,role_id) value ('Software Developer', 1),('UI/UX Developer', 1),('Backend Developer', 1),('Frontend Developer', 1),('Project Manager', 2),('Business Analyst', 3),('Quality Assurance Tester', 4),('DevOps Engineer', 5);
INSERT INTO atl_type_role (name) value ('Developer'),('Manager'),('Analyst'),('Tester'),('Devops');
INSERT INTO atl_type_gender (name) value ("Male"),("Female"),("Others");
INSERT INTO atl_type_account_status (name) value ("Active"),("InActive");
INSERT INTO atl_type_marital_status (name) value ("Married"),("UnMarried");