<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$db = new db();

// Get all users
$app->get('/users', function (Request $request, Response $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $sql = "SELECT * FROM user";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Get user by ID
$app->get('/users/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "SELECT * FROM user WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user) {
            $response->getBody()->write(json_encode($user));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(["error" => "User not found"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Database error: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Create a new user
$app->post('/users', function (Request $request, Response $response, $args) use ($db) {
    try {
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['full_name']) || !isset($data['email']) || !isset($data['password']) || !isset($data['date_of_birth']) || !isset($data['phone_number'])) {
            $response->getBody()->write(json_encode(["error" => "Full name, email, password, date of birth, and phone number are required."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $full_name = $data['full_name'];
        $email = $data['email'];
        $password = password_hash($data['password'], PASSWORD_DEFAULT);
        $date_of_birth = $data['date_of_birth'];
        $phone_number = $data['phone_number'];

        $sql = "SELECT * FROM user WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $existingUser = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($existingUser) {
            $response->getBody()->write(json_encode(["error" => "Email already exists"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $sql = "INSERT INTO user (full_name, email, password, role, date_of_birth, phone_number) VALUES (:full_name, :email, :password, :role, :date_of_birth, :phone_number)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':full_name', $full_name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':password', $password);
        $stmt->bindValue(':role', 'user');
        $stmt->bindValue(':date_of_birth', $date_of_birth);
        $stmt->bindValue(':phone_number', $phone_number);
        $stmt->execute();

        $user_id = $conn->lastInsertId();

         $response->getBody()->write(json_encode(["message" => "User created successfully", 'user_id' => $user_id, 'email' => $email]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error creating user: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Update a user
$app->put('/users/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $data = $request->getParsedBody();
        if (!isset($data['full_name']) || !isset($data['email']) || !isset($data['date_of_birth']) || !isset($data['phone_number'])) {
            $response->getBody()->write(json_encode(["error" => "Full name, email, date of birth, and phone number are required."]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }
        $full_name = $data['full_name'];
        $email = $data['email'];
        $date_of_birth = $data['date_of_birth'];
        $phone_number = $data['phone_number'];
        $sql = "UPDATE user SET full_name = :full_name, email = :email, date_of_birth = :date_of_birth, phone_number = :phone_number WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':full_name', $full_name);
        $stmt->bindValue(':email', $email);
        $stmt->bindValue(':date_of_birth', $date_of_birth);
        $stmt->bindValue(':phone_number', $phone_number);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $response->getBody()->write(json_encode(["message" => "User updated successfully"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error updating user: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// Delete a user
$app->delete('/users/{id}', function (Request $request, Response $response, $args) use ($db) {
    try {
        $id = $args['id'];
        $conn = $db->connect();
        $sql = "DELETE FROM user WHERE user_id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        $response->getBody()->write(json_encode(["message" => "User deleted successfully"]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(["error" => "Error deleting user: " . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
});

// User login
$app->post('/users/login', function ($request, $response, $args) {
    $data = $request->getParsedBody();
    $email = $data['email'] ?? '';
    $password = $data['password'] ?? '';

    // Validate input
    if (empty($email) || empty($password)) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid input']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Connect to the database
    $db = new db();
    $dbConnection = $db->connect();

    if (!$dbConnection) {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Database connection failed']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // Check if the user exists
    $stmt = $dbConnection->prepare("SELECT * FROM user WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $response->getBody()->write(json_encode(['success' => true, 'message' => 'Login successful', 'user_id' => $user['user_id'], 'email' => $user['email'], 'role' => $user['role']]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
        $response->getBody()->write(json_encode(['success' => false, 'message' => 'Invalid email or password']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
});
