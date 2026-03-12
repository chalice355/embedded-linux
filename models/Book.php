<?php
require_once __DIR__ . '/../config/db.php';

class Book {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll($search = '') {
        if ($search) {
            $stmt = $this->db->prepare(
                "SELECT * FROM books WHERE title LIKE ? OR author LIKE ? ORDER BY id DESC"
            );
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM books ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO books (title, author, isbn, publisher, quantity, available_qty)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        return $stmt->execute([
            $data['title'], $data['author'], $data['isbn'],
            $data['publisher'], $data['quantity'], $data['quantity']
        ]);
    }

    public function update($id, $data) {
        $book = $this->getById($id);
        $diff = $data['quantity'] - $book['quantity'];
        $new_available = max(0, $book['available_qty'] + $diff);

        $stmt = $this->db->prepare(
            "UPDATE books SET title=?, author=?, isbn=?, publisher=?, quantity=?, available_qty=? WHERE id=?"
        );
        return $stmt->execute([
            $data['title'], $data['author'], $data['isbn'],
            $data['publisher'], $data['quantity'], $new_available, $id
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM books WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function decreaseAvailable($id) {
        $stmt = $this->db->prepare(
            "UPDATE books SET available_qty = available_qty - 1 WHERE id = ? AND available_qty > 0"
        );
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function increaseAvailable($id) {
        $stmt = $this->db->prepare(
            "UPDATE books SET available_qty = available_qty + 1 WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM books")->fetchColumn();
    }

    public function getBorrowedCount() {
        return $this->db->query(
            "SELECT SUM(quantity - available_qty) FROM books"
        )->fetchColumn() ?: 0;
    }
}