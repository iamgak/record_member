-- schema design with necessary data
CREATE DATABASE atlanta_db;
USE atlanta_db;

CREATE TABLE atl_team_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    mobile VARCHAR(15) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    dob DATE NOT NULL, 
    address TEXT NOT NULL,
    designation INT NOT NULL,
    role INT NOT NULL,
    gender TINYINT(1) NOT NULL,
    marital_status TINYINT(2) DEFAULT 1,
    account_status TINYINT(2) DEFAULT 1,
    is_deleted TINYINT(1) DEFAULT 0,
    version INT DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL
);


CREATE TABLE atl_type_designation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);


CREATE TABLE atl_type_role (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);
CREATE TABLE atl_type_gender (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE atl_type_account_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE atl_type_marital_status (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);


-- seed some dummy data 
INSERT INTO atl_type_designation (name) value ("Project Manager"),("UI/UX Developer"),("DevOps"),("Backend Developer");
INSERT INTO atl_type_gender (name) value ("Male"),("Female"),("Others");
INSERT INTO atl_type_role (name) value ("Developer"),("Manager"),("Analyst"),("Tester");
INSERT INTO atl_type_account_status (name) value ("Active"),("InActive");
INSERT INTO atl_type_marital_status (name) value ("Married"),("UnMarried");