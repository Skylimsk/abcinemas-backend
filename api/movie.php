<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

$db = new db();

return function (App $app) {
    // Create a new movies
    $app->post('/movies', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $data = $request->getParsedBody();
        $movieID = $data['id'];
        $title = $data['title'];
        $description = $data['description'];
        $genre = $data['genre'];
        $duration = $data['duration'];

        $sql = "INSERT INTO movies (id, title, description, genre, duration) VALUES (:id, :title, :description, :genre, :duration)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':id', $movieID);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':description', $description);
        $stmt->bindValue(':genre', $genre);
        $stmt->bindValue(':duration', $duration);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(['message' => 'New movie has been created successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Error creating movie']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
    
     // Update a review
    $app->put('/movies/{id}', function (Request $request, Response $response, array $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            $data = $request->getParsedBody();
            $id = $data['id'];
            $title = $data['title'];
            $description = $data['description'];
            $genre = $data['genre'];
            $duration = $data['duration'];
            $sql = "UPDATE movies SET id = :id, title = :title, description = :description, genre = :genre, duration = :duration WHERE movieID = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->bindValue(':title', $title);
            $stmt->bindValue(':description', $description);
            $stmt->bindValue(':genre', $genre);
            $stmt->bindValue(':duration', $duration);
            $stmt->execute();
            $response->getBody()->write(json_encode(["message" => "Movies updated successfully"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error updating movies: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Delete a movie
    $app->delete('/movies/{id}', function (Request $request, Response $response, $args) use ($db) {
        try {

            $db = new db();

            $id = $args['id'];
            $conn = $db->connect();
            $sql = "DELETE FROM movies WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':id', $id);
            $stmt->execute();
            $response->getBody()->write(json_encode(["message" => "Movies deleted successfully"]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error deleting movies: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
