<?php
require_once __DIR__ . '/../utils/JwtUtil.php';

class AuthMiddleware {
    public static function authenticate() {
        $token = JwtUtil::getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied. No token provided.']);
            exit;
        }

        $decoded = JwtUtil::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied. Invalid token.']);
            exit;
        }

        return $decoded;
    }
}
?>
