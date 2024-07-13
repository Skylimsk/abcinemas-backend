<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

$db = new db();

return function (App $app) {
    // Fetch movies
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

    // Fetch movie by ID
    $app->get('/movies/{id}', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $id = $args['id'];
                $sql = "SELECT * FROM movies WHERE id = :id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':id', $id);
                $stmt->execute();
                $movie = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($movie) {
                    $response->getBody()->write(json_encode($movie));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write(json_encode(["error" => "Movie not found"]));
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

    // Fetch branches
    $app->get('/branches', function (Request $request, Response $response, $args) {
        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT * FROM branches";
            $stmt = $conn->prepare($sql);
            $stmt->execute();
            $branches = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode(['branches' => $branches]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Fetch showtimes
    $app->get('/showtimes', function (Request $request, Response $response, $args) {
        $params = $request->getQueryParams();
        $movie = $params['movie'] ?? null;
        $branch = $params['branch'] ?? null;
        $date = $params['date'] ?? null;

        error_log("Fetching showtimes for movie: $movie, branch: $branch, date: $date");

        if (!$movie || !$branch || !$date) {
            $response->getBody()->write(json_encode(['error' => 'Missing parameters']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT hall, show_time FROM showtimes WHERE movie_id = (SELECT id FROM movies WHERE title = :movie) AND branch = :branch AND show_date = :show_date";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':movie', $movie);
            $stmt->bindValue(':branch', $branch);
            $stmt->bindValue(':show_date', $date);

            try {
                $stmt->execute();
                $showtimes = $stmt->fetchAll(PDO::FETCH_ASSOC);
                error_log("Showtimes fetched: " . json_encode($showtimes));
                if ($showtimes) {
                    $response->getBody()->write(json_encode(['showTimes' => $showtimes]));
                    return $response->withHeader('Content-Type', 'application/json');
                } else {
                    $response->getBody()->write(json_encode(['error' => 'No showtimes found']));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
                }
            } catch (PDOException $e) {
                error_log("Error executing query: " . $e->getMessage());
                $response->getBody()->write(json_encode(['error' => 'Database query error']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Create a new booking
    $app->post('/bookings', function (Request $request, Response $response, $args) {
        try {
            $db = new db();
            $conn = $db->connect();
            if ($conn) {
                $data = $request->getParsedBody();
                if (!isset($data['user_id']) || !isset($data['movie_id']) || !isset($data['branch']) || !isset($data['hall']) || !isset($data['show_time']) || !isset($data['show_date']) || !isset($data['total_price']) || !isset($data['payment_method']) || !isset($data['seats'])) {
                    $response->getBody()->write(json_encode(["error" => "All booking details are required."]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
                $user_id = $data['user_id'];
                $movie_id = $data['movie_id'];
                $branch = $data['branch'];
                $hall = $data['hall'];
                $show_time = $data['show_time'];
                $show_date = $data['show_date'];
                $total_price = $data['total_price'];
                $payment_method = $data['payment_method'];
                $seats = $data['seats'];

                // Insert into bookings table
                $sql = "INSERT INTO bookings (user_id, movie_id, branch, hall, show_time, show_date, total_price, payment_method) VALUES (:user_id, :movie_id, :branch, :hall, :show_time, :show_date, :total_price, :payment_method)";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':user_id', $user_id);
                $stmt->bindValue(':movie_id', $movie_id);
                $stmt->bindValue(':branch', $branch);
                $stmt->bindValue(':hall', $hall);
                $stmt->bindValue(':show_time', $show_time);
                $stmt->bindValue(':show_date', $show_date);
                $stmt->bindValue(':total_price', $total_price);
                $stmt->bindValue(':payment_method', $payment_method);
                $stmt->execute();

                $booking_id = $conn->lastInsertId();

                // Insert into booking_details table
                $sql = "INSERT INTO booking_details (booking_id, seat_row, seat_number, ticket_type, price) VALUES (:booking_id, :seat_row, :seat_number, :ticket_type, :price)";
                $stmt = $conn->prepare($sql);
                foreach ($seats as $seat) {
                    $stmt->bindValue(':booking_id', $booking_id);
                    $stmt->bindValue(':seat_row', $seat['row']);
                    $stmt->bindValue(':seat_number', $seat['seat']);
                    $stmt->bindValue(':ticket_type', $seat['ticket_type']);
                    $stmt->bindValue(':price', $seat['price']);
                    $stmt->execute();
                }

                $response->getBody()->write(json_encode(["success" => true, 'booking_id' => $booking_id]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            } else {
                $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } catch (PDOException $e) {
            $response->getBody()->write(json_encode(["error" => "Error creating booking: " . $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Fetch a specific booking by ID
    $app->get('/bookings/{id}', function (Request $request, Response $response, $args) {
        $booking_id = $args['id'];

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT b.booking_id, b.movie_id, m.title AS movie_title, b.branch, b.hall, b.show_time, b.show_date, 
                bd.seat_row, bd.seat_number, bd.price
                FROM bookings b
                JOIN booking_details bd ON b.booking_id = bd.booking_id
                JOIN movies m ON b.movie_id = m.id
                WHERE b.booking_id = :booking_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':booking_id', $booking_id);
            $stmt->execute();
            $bookingDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode(['bookingDetails' => $bookingDetails]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

<<<<<<< HEAD
=======
    $app->get('/users/bookings/{id}', function (Request $request, Response $response, $args) {
        $user_id = $args['id'];

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT b.booking_id, b.movie_id, m.title AS movie_title, b.branch, b.hall, b.show_time, b.show_date, 
                bd.seat_row, bd.seat_number, bd.price
                FROM bookings b
                JOIN booking_details bd ON b.booking_id = bd.booking_id
                JOIN movies m ON b.movie_id = m.id
                WHERE b.user_id = :user_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':user_id', $user_id);
            $stmt->execute();
            $bookingDetails = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode(['bookingDetails' => $bookingDetails]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });


>>>>>>> ef22a3bb0e0c004fd826ec345235be2792ed6808
    // Fetch blocked seats
    $app->get('/blocked-seats', function (Request $request, Response $response, $args) {
        $params = $request->getQueryParams();
        $branch = $params['branch'];
        $hall = $params['hall'];
        $show_time = $params['show_time'];
        $show_date = $params['show_date'];

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT seat_row, seat_number FROM bookings b
                    JOIN booking_details bd ON b.booking_id = bd.booking_id
                    WHERE b.branch = :branch AND b.hall = :hall AND b.show_time = :show_time AND b.show_date = :show_date";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':branch', $branch);
            $stmt->bindValue(':hall', $hall);
            $stmt->bindValue(':show_time', $show_time);
            $stmt->bindValue(':show_date', $show_date);
            $stmt->execute();
            $blockedSeats = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $response->getBody()->write(json_encode(['blockedSeats' => $blockedSeats]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    $app->get('/bookings', function (Request $request, Response $response, $args) {
        $params = $request->getQueryParams();
        $branch = $params['branch'] ?? null;
        $movie = $params['movie'] ?? null;
        $date = $params['date'] ?? null;

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            $sql = "SELECT b.booking_id, b.movie_id, m.title AS movie_title, b.branch, b.hall, b.show_time, b.show_date, 
                    GROUP_CONCAT(CONCAT('R', bd.seat_row, 'S', bd.seat_number) ORDER BY bd.seat_row, bd.seat_number SEPARATOR ', ') AS seats,
                    u.full_name AS customer_name
                    FROM bookings b
                    JOIN booking_details bd ON b.booking_id = bd.booking_id
                    JOIN movies m ON b.movie_id = m.id
                    JOIN user u ON b.user_id = u.user_id
                    WHERE 1=1";

            if ($branch) {
                $sql .= " AND b.branch = :branch";
            }
            if ($movie) {
                $sql .= " AND m.title LIKE :movie";
            }
            if ($date) {
                $sql .= " AND b.show_date = :date";
            }

            $sql .= " GROUP BY b.booking_id";

            $stmt = $conn->prepare($sql);
            if ($branch) {
                $stmt->bindValue(':branch', $branch);
            }
            if ($movie) {
                $stmt->bindValue(':movie', '%' . $movie . '%');
            }
            if ($date) {
                $stmt->bindValue(':date', $date);
            }
            $stmt->execute();
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $response->getBody()->write(json_encode(['bookings' => $bookings]));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });


    // Update a booking
    $app->put('/bookings/{id}', function (Request $request, Response $response, $args) {
        $booking_id = $args['id'];
        $data = $request->getParsedBody();

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            // Update booking details (seats)
            $sql = "DELETE FROM booking_details WHERE booking_id = :booking_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindValue(':booking_id', $booking_id);
            $stmt->execute();

            $sql = "INSERT INTO booking_details (booking_id, seat_row, seat_number, ticket_type, price) VALUES (:booking_id, :seat_row, :seat_number, 'Standard', 10.00)";
            $stmt = $conn->prepare($sql);
            foreach ($data['seats'] as $seat) {
                $stmt->bindValue(':booking_id', $booking_id);
                $stmt->bindValue(':seat_row', $seat['seat_row']);
                $stmt->bindValue(':seat_number', $seat['seat_number']);
                $stmt->execute();
            }

            $response->getBody()->write(json_encode(["success" => true, 'booking_id' => $booking_id]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });

    // Delete a booking
    $app->delete('/bookings/{id}', function (Request $request, Response $response, $args) {
        $booking_id = $args['id'];

        $db = new db();
        $conn = $db->connect();
        if ($conn) {
            try {
                // Delete booking details first
                $sql = "DELETE FROM booking_details WHERE booking_id = :booking_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':booking_id', $booking_id);
                $stmt->execute();

                // Delete booking
                $sql = "DELETE FROM bookings WHERE booking_id = :booking_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':booking_id', $booking_id);
                $stmt->execute();

                $response->getBody()->write(json_encode(["success" => true]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            } catch (PDOException $e) {
                $response->getBody()->write(json_encode(["error" => "Error deleting booking: " . $e->getMessage()]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        } else {
            $response->getBody()->write(json_encode(['error' => 'Database connection failed']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    });
};
