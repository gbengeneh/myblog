<?php
require_once __DIR__ . '/../models/Post.php';
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../utils/JwtUtil.php';

class PostController {
    private $post;
    private $category;

    public function __construct() {
        $this->post = new Post();
        $this->category = new Category();
    }

    public function getAllPosts() {
        $stmt = $this->post->read();
        $posts = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $posts[] = [
                'id' => $row['id'],
                'title' => $row['title'],
                'content' => $row['content'],
                'image' => $row['image'] ? BASE_URL . $row['image'] : null,
                'category_id' => $row['category_id'],
                'category_name' => $row['category_name'],
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'created_at' => $row['created_at'],
                'updated_at' => $row['updated_at']
            ];
        }
        echo json_encode($posts);
    }
    

    public function getPost($id) {
        $this->post->id = $id;
        if ($this->post->readOne()) {
            echo json_encode([
                'id' => $this->post->id,
                'title' => $this->post->title,
                'content' => $this->post->content,
                'image' => $this->post->image ? BASE_URL . $this->post->image : null,
                'category_id' => $this->post->category_id,
                'category_name' => $this->post->category_name,
                'user_id' => $this->post->user_id,
                'username' => $this->post->username,
                'created_at' => $this->post->created_at,
                'updated_at' => $this->post->updated_at
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Post not found']);
        }
    }

    public function createPost($data) {
        // Debugging
        error_log('$_POST: ' . print_r($_POST, true));
        error_log('$_FILES: ' . print_r($_FILES, true));
        error_log('$data: ' . print_r($data, true));

        $token = JwtUtil::getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        $decoded = JwtUtil::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token']);
            return;
        }

        if (!isset($data['title']) || $data['title'] === '' || !isset($data['content']) || $data['content'] === '' || !isset($data['category_id']) || $data['category_id'] === '') {
            http_response_code(400);
            echo json_encode(['message' => 'Title, content and category_id are required. Received: ' . json_encode($data)]);
            return;
        }

        $this->post->title = htmlspecialchars(strip_tags($data['title']));
        $this->post->content = htmlspecialchars(strip_tags($data['content']));
        $this->post->category_id = $data['category_id'];
        $this->post->user_id = $decoded['user_id'];

        // Handle image upload
        if (isset($_FILES['image'])) {
            $image_name = $this->post->uploadImage($_FILES['image']);
            if ($image_name) {
                $this->post->image = $image_name;
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid image']);
                return;
            }
        }

        if ($this->post->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'Post created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to create post']);
        }
    }

    public function updatePost($id, $data) {
        $token = JwtUtil::getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        $decoded = JwtUtil::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token']);
            return;
        }

        $this->post->id = $id;
        if (!$this->post->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Post not found']);
            return;
        }

        if ($this->post->user_id != $decoded['user_id']) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        $this->post->title = isset($data['title']) ? htmlspecialchars(strip_tags($data['title'])) : $this->post->title;
        $this->post->content = isset($data['content']) ? htmlspecialchars(strip_tags($data['content'])) : $this->post->content;
        $this->post->category_id = isset($data['category_id']) ? $data['category_id'] : $this->post->category_id;

        // Handle image upload
        if (isset($_FILES['image'])) {
            $image_name = $this->post->uploadImage($_FILES['image']);
            if ($image_name) {
                $this->post->image = $image_name;
            } else {
                http_response_code(400);
                echo json_encode(['message' => 'Invalid image']);
                return;
            }
        }

        if ($this->post->update()) {
            echo json_encode(['message' => 'Post updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to update post']);
        }
    }

    public function deletePost($id) {
        $token = JwtUtil::getBearerToken();
        if (!$token) {
            http_response_code(401);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        $decoded = JwtUtil::decode($token);
        if (!$decoded) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid token']);
            return;
        }

        $this->post->id = $id;
        if (!$this->post->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Post not found']);
            return;
        }

        if ($this->post->user_id != $decoded['user_id']) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        if ($this->post->delete()) {
            echo json_encode(['message' => 'Post deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to delete post']);
        }
    }
}
?>
