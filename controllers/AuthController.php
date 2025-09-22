<?php
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../utils/JwtUtil.php';

class AuthController {
    private $user;

    public function __construct() {
        $this->user = new User();
    }

    public function register($data) {
        if (empty($data['username']) || empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Username, email and password are required']);
            return;
        }

        $this->user->username = htmlspecialchars(strip_tags($data['username']));
        $this->user->email = htmlspecialchars(strip_tags($data['email']));

        if ($this->user->emailExists()) {
            http_response_code(400);
            echo json_encode(['message' => 'Email already exists']);
            return;
        }

        $this->user->password_hash = password_hash($data['password'], PASSWORD_BCRYPT);

        if ($this->user->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'User registered successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to register user']);
        }
    }

    public function login($data) {
        if (empty($data['email']) || empty($data['password'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Email and password are required']);
            return;
        }

        $this->user->email = htmlspecialchars(strip_tags($data['email']));

        if (!$this->user->emailExists()) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid email or password']);
            return;
        }

        if (password_verify($data['password'], $this->user->password_hash)) {
            $token = JwtUtil::encode([
                'user_id' => $this->user->id,
                'username' => $this->user->username,
                'email' => $this->user->email
            ]);
            echo json_encode(['token' => $token]);
        } else {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid email or password']);
        }
    }
}
?>
