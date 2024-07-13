-- Create the database
CREATE DATABASE abcinemas;

-- Switch to the newly created database
USE abcinemas;

-- Create the user table
CREATE TABLE user (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    full_name VARCHAR(100)  NOT NULL,
    role VARCHAR(10)  NOT NULL,
    date_of_birth DATE  NOT NULL,
    phone_number VARCHAR(20)  NOT NULL,
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);