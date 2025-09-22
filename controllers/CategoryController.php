<?php
require_once __DIR__ . '/../models/Category.php';
require_once __DIR__ . '/../middleware/AuthMiddleware.php';

class CategoryController {
    private $category;

    public function __construct() {
        $this->category = new Category();
    }

    public function getAllCategories() {
        $stmt = $this->category->read();
        $categories = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $categories[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'created_at' => $row['created_at']
            ];
        }
        echo json_encode($categories);
    }

    public function getCategory($id) {
        $this->category->id = $id;
        if ($this->category->readOne()) {
            echo json_encode([
                'id' => $this->category->id,
                'name' => $this->category->name,
                'description' => $this->category->description,
                'created_at' => $this->category->created_at
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['message' => 'Category not found']);
        }
    }

    public function createCategory($data) {
        AuthMiddleware::authenticate();

        if (empty($data['name'])) {
            http_response_code(400);
            echo json_encode(['message' => 'Name is required']);
            return;
        }

        $this->category->name = htmlspecialchars(strip_tags($data['name']));
        $this->category->description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : '';

        if ($this->category->create()) {
            http_response_code(201);
            echo json_encode(['message' => 'Category created successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to create category']);
        }
    }

    public function updateCategory($id, $data) {
        AuthMiddleware::authenticate();

        $this->category->id = $id;
        if (!$this->category->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Category not found']);
            return;
        }

        $this->category->name = isset($data['name']) ? htmlspecialchars(strip_tags($data['name'])) : $this->category->name;
        $this->category->description = isset($data['description']) ? htmlspecialchars(strip_tags($data['description'])) : $this->category->description;

        if ($this->category->update()) {
            echo json_encode(['message' => 'Category updated successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to update category']);
        }
    }

    public function deleteCategory($id) {
        AuthMiddleware::authenticate();

        $this->category->id = $id;
        if (!$this->category->readOne()) {
            http_response_code(404);
            echo json_encode(['message' => 'Category not found']);
            return;
        }

        if ($this->category->delete()) {
            echo json_encode(['message' => 'Category deleted successfully']);
        } else {
            http_response_code(500);
            echo json_encode(['message' => 'Unable to delete category']);
        }
    }
}
?>
