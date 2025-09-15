<?php
require_once __DIR__ . '/../controllers/AuthController.php';
require_once __DIR__ . '/../controllers/PostController.php';
require_once __DIR__ . '/../controllers/CategoryController.php';
require_once __DIR__ . '/../controllers/CommentController.php';

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$method = $_SERVER['REQUEST_METHOD'];

// Determine the request path for routing
if (isset($_SERVER['PATH_INFO'])) {
    $request = explode('/', trim($_SERVER['PATH_INFO'], '/'));
} else {
    // Fallback: parse REQUEST_URI relative to script name
    $script_name = dirname($_SERVER['SCRIPT_NAME']);
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = substr($request_uri, strlen($script_name));
    $path = strtok($path, '?'); // Remove query string
    $request = explode('/', trim($path, '/'));
}

// Handle preflight OPTIONS request
if ($method === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$authController = new AuthController();
// $postController = new PostController();
// $categoryController = new CategoryController();
// $commentController = new CommentController();

if ($request[0] === 'register' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $authController->register($data);
} elseif ($request[0] === 'login' && $method === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);
    $authController->login($data);
} 
// elseif ($request[0] === 'posts') {
//     if ($method === 'GET') {
//         if (isset($request[1])) {
//             $postController->getPost($request[1]);
//         } else {
//             $postController->getAllPosts();
//         }
//     } elseif ($method === 'POST') {
//         $postController->createPost($_POST);
//     } elseif ($method === 'PUT' && isset($request[1])) {
//         parse_str(file_get_contents("php://input"), $put_vars);
//         $postController->updatePost($request[1], $put_vars);
//     } elseif ($method === 'DELETE' && isset($request[1])) {
//         $postController->deletePost($request[1]);
//     } else {
//         http_response_code(405);
//         echo json_encode(['message' => 'Method not allowed']);
//     }
// } elseif ($request[0] === 'categories') {
//     if ($method === 'GET') {
//         if (isset($request[1])) {
//             $categoryController->getCategory($request[1]);
//         } else {
//             $categoryController->getAllCategories();
//         }
//     } elseif ($method === 'POST') {
//         $data = json_decode(file_get_contents("php://input"), true);
//         $categoryController->createCategory($data);
//     } elseif ($method === 'PUT' && isset($request[1])) {
//         $data = json_decode(file_get_contents("php://input"), true);
//         $categoryController->updateCategory($request[1], $data);
//     } elseif ($method === 'DELETE' && isset($request[1])) {
//         $categoryController->deleteCategory($request[1]);
//     } else {
//         http_response_code(405);
//         echo json_encode(['message' => 'Method not allowed']);
//     }
// } elseif ($request[0] === 'comments') {
//     if ($method === 'GET') {
//         if (isset($request[1])) {
//             $commentController->getComment($request[1]);
//         } elseif (isset($_GET['post_id'])) {
//             $commentController->getCommentsByPost($_GET['post_id']);
//         } else {
//             $commentController->getAllComments();
//         }
//     } elseif ($method === 'POST') {
//         $data = json_decode(file_get_contents("php://input"), true);
//         $commentController->createComment($data);
//     } elseif ($method === 'PUT' && isset($request[1])) {
//         $data = json_decode(file_get_contents("php://input"), true);
//         $commentController->updateComment($request[1], $data);
//     } elseif ($method === 'DELETE' && isset($request[1])) {
//         $commentController->deleteComment($request[1]);
//     } else {
//         http_response_code(405);
//         echo json_encode(['message' => 'Method not allowed']);
//     }
// } 
else {
    http_response_code(404);
    echo json_encode(['message' => 'Endpoint not found']);
}
?>
