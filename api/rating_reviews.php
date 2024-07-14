<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

$db = new db();

return function (App $app) {
    // Fetch movies
    $app->get('/movies', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $sql = "SELECT * FROM movies";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $response->getBody()->write(json_encode($movies));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Create a new review
    $app->post('/reviews', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $data = $request->getParsedBody();
        $movieID = $data['movieID'];
        $rating = $data['rating'];
        $review = $data['review'];

        $sql = "INSERT INTO rating_reviews (movieID, rating, review) VALUES (:movieID, :rating, :review)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':movieID', $movieID);
        $stmt->bindValue(':rating', $rating);
        $stmt->bindValue(':review', $review);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(['message' => 'New review created successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Error creating review']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Read all reviews for a movie
    $app->get('/reviews', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $params = $request->getQueryParams();
        if (!isset($params['movieID'])) {
            $response->getBody()->write(json_encode(['error' => 'Movie ID is required']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $movieID = $params['movieID'];
        $sql = "SELECT * FROM rating_reviews WHERE movieID = :movieID";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':movieID', $movieID);
        $stmt->execute();
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode($reviews));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // Update a review
    $app->put('/reviews', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $data = $request->getParsedBody();
        if (!isset($data['review_id']) || !isset($data['rating']) || !isset($data['review'])) {
            $response->getBody()->write(json_encode(['error' => 'All fields are required for updating']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $review_id = $data['review_id'];
        $rating = $data['rating'];
        $review = $data['review'];

        $sql = "UPDATE rating_reviews SET rating = :rating, review = :review WHERE review_id = :review_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':rating', $rating);
        $stmt->bindValue(':review', $review);
        $stmt->bindValue(':review_id', $review_id);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(['message' => 'Review updated successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Error updating review']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Delete a review
    $app->delete('/reviews', function (Request $request, Response $response) {
        $db = new db();
        $conn = $db->connect();

        if (!$conn) {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }

        $params = $request->getQueryParams();
        if (!isset($params['review_id'])) {
            $response->getBody()->write(json_encode(['error' => 'Review ID is required for deletion']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $review_id = $params['review_id'];

        $sql = "DELETE FROM rating_reviews WHERE review_id = :review_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':review_id', $review_id);

        if ($stmt->execute()) {
            $response->getBody()->write(json_encode(['message' => 'Review deleted successfully']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Error deleting review']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
