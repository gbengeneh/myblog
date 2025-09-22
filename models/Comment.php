<?php
require_once __DIR__ . '/../config/database.php';

class Comment {
    private $conn;
    private $table_name = "comments";

    public $id;
    public $content;
    public $post_id;
    public $user_id;
    public $created_at;
    public $username;

    public function __construct() {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function create() {
        $query = "INSERT INTO " . $this->table_name . " SET content=:content, post_id=:post_id, user_id=:user_id, created_at=NOW()";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":post_id", $this->post_id);
        $stmt->bindParam(":user_id", $this->user_id);

        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function read() {
        $query = "SELECT c.id, c.content, c.post_id, c.user_id, c.created_at, u.username FROM " . $this->table_name . " c LEFT JOIN users u ON c.user_id = u.id ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function readByPost() {
        $query = "SELECT c.id, c.content, c.post_id, c.user_id, c.created_at, u.username FROM " . $this->table_name . " c LEFT JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.created_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->post_id);
        $stmt->execute();
        return $stmt;
    }

    public function readOne() {
        $query = "SELECT c.id, c.content, c.post_id, c.user_id, c.created_at, u.username FROM " . $this->table_name . " c LEFT JOIN users u ON c.user_id = u.id WHERE c.id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $this->content = $row['content'];
            $this->post_id = $row['post_id'];
            $this->user_id = $row['user_id'];
            $this->created_at = $row['created_at'];
            $this->username = $row['username'];
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table_name . " SET content=:content WHERE id=:id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":content", $this->content);
        $stmt->bindParam(":id", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
?>
