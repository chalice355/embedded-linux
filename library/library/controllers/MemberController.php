<?php
require_once __DIR__ . '/../models/Member.php';

class MemberController {
    private $model;

    public function __construct() {
        $this->model = new Member();
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $members = $this->model->getAll($search);
        require __DIR__ . '/../views/members/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'  => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
            ];
            if ($this->model->create($data)) {
                header('Location: index.php?page=members&msg=created');
                exit;
            }
        }
        require __DIR__ . '/../views/members/create.php';
    }

    public function edit($id) {
        $member = $this->model->getById($id);
        if (!$member) {
            header('Location: index.php?page=members');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'  => trim($_POST['name']),
                'email' => trim($_POST['email']),
                'phone' => trim($_POST['phone']),
            ];
            if ($this->model->update($id, $data)) {
                header('Location: index.php?page=members&msg=updated');
                exit;
            }
        }
        require __DIR__ . '/../views/members/edit.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: index.php?page=members&msg=deleted');
        exit;
    }
}