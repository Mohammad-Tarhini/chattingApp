<?php
// require_once __DIR__ . '../controllers/AdminController.php';
// require_once __DIR__ . '../controllers/AuthoController.php';
// require_once __DIR__ . '../controllers/TraineeController.php';
class ResponseService {
    public static function success($payload, int $status_code = 200) {
        return json_encode([
            "success" => true,
            "status" => $status_code,
            "data" => $payload
        ]);
    }

    public static function error(string $message, int $status_code = 400) {
        return json_encode([
            "success" => false,
            "status" => $status_code,
            "message" => $message
        ]);
    }
}


?>