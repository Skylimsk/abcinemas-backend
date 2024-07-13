<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {

    $app->get('/movies', function (Request $request, Response $response, $args) {
        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT * FROM movies";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode(['movies' => $movies]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    

    // Get all tickets
    $app->get('/tickets', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $sql = "SELECT * FROM tickets";
                $stmt = $conn->prepare($sql);
                $stmt->execute();
                $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $response->getBody()->write(json_encode($result));
                return $response->withHeader('Content-Type', 'application/json');
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Get ticket by ID
    $app->get('/tickets/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $id = $args['id'];
                $sql = "SELECT * FROM tickets WHERE ticket_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($ticket) {
                    $response->getBody()->write(json_encode($ticket));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write(json_encode(["error" => "Ticket not found"]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Database error: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Create a new ticket
    $app->post('/tickets', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $data = $request->getParsedBody();
                if (!isset($data['movie_id']) || !isset($data['user_id']) || !isset($data['showtime']) || !isset($data['seat_row']) || !isset($data['seat_number'])) {
                    $response->getBody()->write(json_encode(["error" => "Movie ID, user ID, showtime, seat row, and seat number are required."]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
                $movie_id = $data['movie_id'];
                $user_id = $data['user_id'];
                $showtime = $data['showtime'];
                $seat_row = $data['seat_row'];
                $seat_number = $data['seat_number'];

                $sql = "INSERT INTO tickets (movie_id, user_id, showtime, seat_row, seat_number) VALUES (:movie_id, :user_id, :showtime, :seat_row, :seat_number)";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':movie_id', $movie_id);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':showtime', $showtime);
                $stmt->bindValue(':seat_row', $seat_row);
                $stmt->bindValue(':seat_number', $seat_number);
                $stmt->execute();

                $ticket_id = $conn->lastInsertId();

                $response->getBody()->write(json_encode(["message" => "Ticket created successfully", 'ticket_id' => $ticket_id]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error creating ticket: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Update a ticket
    $app->put('/tickets/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $id = $args['id'];
                $data = $request->getParsedBody();
                if (!isset($data['movie_id']) || !isset($data['user_id']) || !isset($data['showtime']) || !isset($data['seat_row']) || !isset($data['seat_number'])) {
                    $response->getBody()->write(json_encode(["error" => "Movie ID, user ID, showtime, seat row, and seat number are required."]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
                $movie_id = $data['movie_id'];
                $user_id = $data['user_id'];
                $showtime = $data['showtime'];
                $seat_row = $data['seat_row'];
                $seat_number = $data['seat_number'];

                $sql = "UPDATE tickets SET movie_id = :movie_id, user_id = :user_id, showtime = :showtime, seat_row = :seat_row, seat_number = :seat_number WHERE ticket_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':movie_id', $movie_id);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':showtime', $showtime);
                $stmt->bindValue(':seat_row', $seat_row);
                $stmt->bindValue(':seat_number', $seat_number);
                $stmt->bindValue(':id', $id);
                $stmt->execute();

                $response->getBody()->write(json_encode(["message" => "Ticket updated successfully"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error updating ticket: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Delete a ticket
    $app->delete('/tickets/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $id = $args['id'];
                $sql = "DELETE FROM tickets WHERE ticket_id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $response->getBody()->write(json_encode(["message" => "Ticket deleted successfully"]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error deleting ticket: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Branches Endpoint
    $app->get('/branches', function (Request $request, Response $response, $args) {
        $branches = ['UTM', 'Muar', 'Yong Peng', 'Kota Tinggi', 'Cameron Highlands'];
        $response->getBody()->write(json_encode(['branches' => $branches]));
        return $response->withHeader('Content-Type', 'application/json');
    });
};
