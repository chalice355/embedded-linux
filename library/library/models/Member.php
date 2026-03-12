<?php
require_once __DIR__ . '/../config/db.php';

class Member {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll($search = '') {
        if ($search) {
            $stmt = $this->db->prepare(
                "SELECT * FROM members WHERE name LIKE ? OR email LIKE ? ORDER BY id DESC"
            );
            $stmt->execute(["%$search%", "%$search%"]);
        } else {
            $stmt = $this->db->query("SELECT * FROM members ORDER BY id DESC");
        }
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM members WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($data) {
        $stmt = $this->db->prepare(
            "INSERT INTO members (name, email, phone) VALUES (?, ?, ?)"
        );
        return $stmt->execute([$data['name'], $data['email'], $data['phone']]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare(
            "UPDATE members SET name=?, email=?, phone=? WHERE id=?"
        );
        return $stmt->execute([$data['name'], $data['email'], $data['phone'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM members WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getTotalCount() {
        return $this->db->query("SELECT COUNT(*) FROM members")->fetchColumn();
    }
}