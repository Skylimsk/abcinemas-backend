<?php
class db {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname = 'abcinemas';

    public function connect() {
        try {
            $mysql_connect_str = "mysql:host=$this->host;dbname=$this->dbname";
            $dbConnection = new PDO($mysql_connect_str, $this->user, $this->password);
            $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $dbConnection;
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
            return null;
        }
    }
}

function generateShowtimes($dbConnection) {
    $branches = ['UTM', 'Muar', 'Yong Peng', 'Kota Tinggi', 'Cameron Highlands'];
    $halls = range(1, 6);
    $date_start = new DateTime();
    $date_end = clone $date_start;
    $date_end->modify('+6 days');

    // Fetch all movie durations from the movies table
    $stmt = $dbConnection->prepare("SELECT id, duration FROM movies");
    $stmt->execute();
    $movies_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($movies_data)) {
        echo "No movies found in the movies table.";
        return;
    }

    // Delete existing data
    $stmt = $dbConnection->prepare("DELETE FROM showtimes");
    $stmt->execute();

    foreach ($branches as $branch) {
        $current_date = clone $date_start;
        while ($current_date <= $date_end) {
            $schedule = [];
            foreach ($movies_data as $movie) {
                $movie_id = $movie['id'];
                $duration = $movie['duration'];
                $show_times = generateFixedShowTimes($duration);

                // Ensure each movie has at least 3 shows in different time slots
                $scheduled_count = 0;

                foreach ($show_times as $index => $show_time) {
                    if ($index >= count($halls)) break;

                    $hall = $halls[$index % count($halls)];
                    if (!isTimeSlotAvailable($schedule, $show_time, $duration, $hall)) continue;

                    $schedule[] = [
                        'movie_id' => $movie_id,
                        'branch' => $branch,
                        'hall' => $hall,
                        'show_time' => $show_time,
                        'show_date' => $current_date->format('Y-m-d'),
                        'duration' => $duration
                    ];
                    $scheduled_count++;

                    if ($scheduled_count >= 3 && $scheduled_count <= 6) break;
                }
            }
            insertBatch($dbConnection, $schedule);
            $current_date->modify('+1 day');
        }
    }
}

function generateFixedShowTimes($duration) {
    $show_times = [
        '10:00:00', '10:15:00', '10:30:00', '10:45:00', '11:00:00', 
        '11:15:00', '11:30:00', '11:45:00', '12:00:00', '12:15:00', 
        '12:30:00', '12:45:00', '13:00:00', '13:15:00', '13:30:00', 
        '13:45:00', '14:00:00', '14:15:00', '14:30:00', '14:45:00',
        '15:00:00', '15:15:00', '15:30:00', '15:45:00', '16:00:00', 
        '16:15:00', '16:30:00', '16:45:00', '17:00:00', '17:15:00', 
        '17:30:00', '17:45:00', '18:00:00', '18:15:00', '18:30:00', 
        '18:45:00', '19:00:00', '19:15:00', '19:30:00', '19:45:00',
        '20:00:00', '20:15:00', '20:30:00', '20:45:00', '21:00:00', 
        '21:15:00', '21:30:00', '21:45:00', '22:00:00', '22:15:00', 
        '22:30:00', '22:45:00', '23:00:00', '23:15:00', '23:30:00'
    ];
    shuffle($show_times);  // Randomize show times to ensure variation
    return $show_times;
}

function isTimeSlotAvailable($schedule, $show_time, $duration, $hall) {
    $show_start = new DateTime($show_time);
    $show_end = clone $show_start;
    $show_end->modify("+{$duration} minutes");

    foreach ($schedule as $entry) {
        if ($entry['hall'] !== $hall) continue;

        $existing_start = new DateTime($entry['show_time']);
        $existing_end = clone $existing_start;
        $existing_end->modify("+{$entry['duration']} minutes");

        if ($show_start < $existing_end && $show_end > $existing_start) {
            return false;
        }
    }
    return true;
}

function insertBatch($dbConnection, $schedule) {
    if (empty($schedule)) {
        return;
    }

    $sql = "INSERT INTO showtimes (movie_id, branch, hall, show_time, show_date) VALUES ";
    $values = [];
    foreach ($schedule as $entry) {
        $values[] = "('{$entry['movie_id']}', '{$entry['branch']}', '{$entry['hall']}', '{$entry['show_time']}', '{$entry['show_date']}')";
    }
    $sql .= implode(', ', $values);

    $stmt = $dbConnection->prepare($sql);
    $stmt->execute();
}

$db = new db();
$dbConnection = $db->connect();
if ($dbConnection) {
    generateShowtimes($dbConnection);
    echo "Showtimes data generated successfully.";
} else {
    echo "Failed to connect to the database.";
}
?>
