<?php
require_once __DIR__ . '/../config/db.php';

class Loan {
    private $db;

    public function __construct() {
        $this->db = getDB();
    }

    public function getAll() {
        $stmt = $this->db->query(
            "SELECT l.*, b.title AS book_title, m.name AS member_name
             FROM loans l
             JOIN books b ON l.book_id = b.id
             JOIN members m ON l.member_id = m.id
             ORDER BY l.id DESC"
        );
        return $stmt->fetchAll();
    }

    public function getActive() {
        $stmt = $this->db->query(
            "SELECT l.*, b.title AS book_title, m.name AS member_name
             FROM loans l
             JOIN books b ON l.book_id = b.id
             JOIN members m ON l.member_id = m.id
             WHERE l.status != 'returned'
             ORDER BY l.due_date ASC"
        );
        return $stmt->fetchAll();
    }

    public function getOverdue() {
        $stmt = $this->db->query(
            "SELECT l.*, b.title AS book_title, m.name AS member_name,
                    DATEDIFF(CURDATE(), l.due_date) AS overdue_days
             FROM loans l
             JOIN books b ON l.book_id = b.id
             JOIN members m ON l.member_id = m.id
             WHERE l.status = 'borrowed' AND l.due_date < CURDATE()
             ORDER BY l.due_date ASC"
        );
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare(
            "SELECT l.*, b.title AS book_title, m.name AS member_name
             FROM loans l
             JOIN books b ON l.book_id = b.id
             JOIN members m ON l.member_id = m.id
             WHERE l.id = ?"
        );
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create($book_id, $member_id, $loan_date, $due_date) {
        $stmt = $this->db->prepare(
            "INSERT INTO loans (book_id, member_id, loan_date, due_date, status)
             VALUES (?, ?, ?, ?, 'borrowed')"
        );
        return $stmt->execute([$book_id, $member_id, $loan_date, $due_date]);
    }

    public function returnLoan($id) {
        $stmt = $this->db->prepare(
            "UPDATE loans SET return_date = CURDATE(), status = 'returned' WHERE id = ?"
        );
        return $stmt->execute([$id]);
    }

    public function updateOverdueStatus() {
        $stmt = $this->db->prepare(
            "UPDATE loans SET status = 'overdue'
             WHERE status = 'borrowed' AND due_date < CURDATE()"
        );
        return $stmt->execute();
    }

    public function getRecentActivity($limit = 5) {
        $stmt = $this->db->prepare(
            "SELECT l.*, b.title AS book_title, m.name AS member_name
             FROM loans l
             JOIN books b ON l.book_id = b.id
             JOIN members m ON l.member_id = m.id
             ORDER BY l.id DESC LIMIT ?"
        );
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }

    public function getActiveCount() {
        return $this->db->query(
            "SELECT COUNT(*) FROM loans WHERE status != 'returned'"
        )->fetchColumn();
    }

    public function getOverdueCount() {
        return $this->db->query(
            "SELECT COUNT(*) FROM loans WHERE status = 'borrowed' AND due_date < CURDATE()"
        )->fetchColumn();
    }
}