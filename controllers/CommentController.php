<?php
require_once __DIR__ . '/../models/Comment.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CommentController {
    private $comment;

    public function __construct() {
        $this->comment = new Comment();
    }

    public function getAllComments() {
        $stmt = $this->comment->read();
        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = [
                'id' => $row['id'],
                'content' => $row['content'],
                'post_id' => $row['post_id'],
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'created_at' => $row['created_at']
            ];
        }
        echo json_encode($comments);
    }

    public function getCommentsByPost($post_id) {
        $this->comment->post_id = $post_id;
        $stmt = $this->comment->readByPost();
        $comments = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $comments[] = [
                'id' => $row['id'],
                'content' => $row['content'],
                'post_id' => $row['post_id'],
                'user_id' => $row['user_id'],
                'username' => $row['username'],
                'created_at' => $row['created_at']
            ];
        }
        echo json_encode($comments);
    }

    public function getComment($id) {
        $this->comment->id = $id;
        if ($this->comment->readOne()) {
            echo json_encode([
                'id' => $this->comment->id,
                'content' => $this->comment->content,
                'post_id' => $this->comment->post_id,
                'user_id' => $this->comment->user_id,
                'username' => $this->comment->username,
                'created_at' => $this->comment->created_at
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Comment not found']);
        }
    }

    public function createComment($data) {
        $decoded = AuthMiddleware::authenticate();

        if (empty($data['content']) || empty($data['post_id'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Content and post_id are required']);
            return;
        }

        $this->comment->content = htmlspecialchars(strip_tags($data['content']));
        $this->comment->post_id = $data['post_id'];
        $this->comment->user_id = $decoded['user_id'];

        if ($this->comment->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'Comment created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to create comment']);
        }
    }

    public function updateComment($id, $data) {
        $decoded = AuthMiddleware::authenticate();

        $this->comment->id = $id;
        if (!$this->comment->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Comment not found']);
            return;
        }

        if ($this->comment->user_id != $decoded['user_id']) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        $this->comment->content = isset($data['content']) ? htmlspecialchars(strip_tags($data['content'])) : $this->comment->content;

        if ($this->comment->update()) {
            echo json_encode(['message' => 'Comment updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to update comment']);
        }
    }

    public function deleteComment($id) {
        $decoded = AuthMiddleware::authenticate();

        $this->comment->id = $id;
        if (!$this->comment->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Comment not found']);
            return;
        }

        if ($this->comment->user_id != $decoded['user_id']) {
            http_response_code(403);
            echo json_encode(['message' => 'Access denied']);
            return;
        }

        if ($this->comment->delete()) {
            echo json_encode(['message' => 'Comment deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to delete comment']);
        }
    }
}
?>
