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

-- Inseet user data to database, password = 1234
INSERT INTO user (password, email, full_name, role, date_of_birth, phone_number, registration_date) VALUES
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'admin@gmail.com', 'Azman Ahmad', 'admin', '1990-01-01', '018-312 4878', '2024-04-01 12:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'azman.ahmad@example.com', 'Azman Ahmad', 'user', '1990-01-01', '018-312 4878', '2024-04-01 12:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'nurul.safina@example.com', 'Nurul Safina', 'user', '1991-02-02', '018-312 4879', '2024-04-05 14:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'mohd.hafiz@example.com', 'Mohd Hafiz', 'user', '1992-03-03', '018-312 4880', '2024-04-10 10:30:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'siti.zarina@example.com', 'Siti Zarina', 'user', '1993-04-04', '018-312 4881', '2024-04-15 09:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'ahmad.firdaus@example.com', 'Ahmad Firdaus', 'user', '1994-05-05', '018-312 4882', '2024-04-20 11:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'nur.aini@example.com', 'Nur Aini', 'user', '1995-06-06', '018-312 4883', '2024-04-25 15:45:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'faizal.rashid@example.com', 'Faizal Rashid', 'user', '1996-07-07', '018-312 4884', '2024-04-30 17:30:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'syazana.aziz@example.com', 'Syazana Aziz', 'user', '1997-08-08', '018-312 4885', '2024-05-05 08:20:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'hafizah.baharudin@example.com', 'Hafizah Baharudin', 'user', '1998-09-09', '018-312 4886', '2024-05-10 14:50:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'shahril.ismail@example.com', 'Shahril Ismail', 'user', '1999-10-10', '018-312 4887', '2024-05-15 12:15:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'fatin.azhar@example.com', 'Fatin Azhar', 'user', '2000-11-11', '018-312 4888', '2024-05-20 13:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'farid.rahman@example.com', 'Farid Rahman', 'user', '2001-12-12', '018-312 4889', '2024-05-25 16:30:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'aisyah.hasan@example.com', 'Aisyah Hasan', 'user', '2002-01-13', '018-312 4890', '2024-06-01 10:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'zulhilmi.azman@example.com', 'Zulhilmi Azman', 'user', '2003-02-14', '018-312 4891', '2024-06-05 11:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'nadia.rahim@example.com', 'Nadia Rahim', 'user', '2004-03-15', '018-312 4892', '2024-06-10 12:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'rashid.salleh@example.com', 'Rashid Salleh', 'user', '2005-04-16', '018-312 4893', '2024-06-15 13:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'julia.hassan@example.com', 'Julia Hassan', 'user', '2006-05-17', '018-312 4894', '2024-06-20 14:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'ali.hussin@example.com', 'Ali Hussin', 'user', '2007-06-18', '018-312 4895', '2024-06-25 15:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'zahira.kassim@example.com', 'Zahira Kassim', 'user', '2008-07-19', '018-312 4896', '2024-07-01 16:00:00'),
    ('$2y$10$GUA4etug5ZAa4rjHT4aGhelgJbzzsMAu.MG7w..J8ZmxSHmuo1jti', 'arif.zain@example.com', 'Arif Zain', 'user', '2009-08-20', '018-312 4897', '2024-07-05 17:00:00');
