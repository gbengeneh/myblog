<?php
require_once __DIR__ . '/../config/config.php';


class JwtUtil {
    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $header_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        $payload['iss'] = JWT_ISSUER;
        $payload['aud'] = JWT_AUDIENCE;
        $payload['iat'] = time();
        $payload['exp'] = time() + JWT_EXPIRATION_TIME;
        $payload_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode(json_encode($payload)));

        $signature = hash_hmac('sha256', $header_encoded . "." . $payload_encoded, JWT_SECRET_KEY, true);
        $signature_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        return $header_encoded . "." . $payload_encoded . "." . $signature_encoded;
    }

    public static function decode($jwt) {
        $parts = explode('.', $jwt);
        if (count($parts) !== 3) {
            return false;
        }

        $header = $parts[0];
        $payload = $parts[1];
        $signature = $parts[2];

        $expected_signature = hash_hmac('sha256', $header . "." . $payload, JWT_SECRET_KEY, true);
        $expected_signature_encoded = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($expected_signature));

        if ($signature !== $expected_signature_encoded) {
            return false;
        }

        $payload_decoded = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
        if ($payload_decoded['exp'] < time()) {
            return false;
        }

        return $payload_decoded;
    }

    public static function getAuthorizationHeader() {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }

    public static function getBearerToken() {
        $headers = self::getAuthorizationHeader();
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }
}
?>
