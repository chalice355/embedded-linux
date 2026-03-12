<?php
require_once __DIR__ . '/../models/Book.php';

class BookController {
    private $model;

    public function __construct() {
        $this->model = new Book();
    }

    public function index() {
        $search = $_GET['search'] ?? '';
        $books = $this->model->getAll($search);
        require __DIR__ . '/../views/books/index.php';
    }

    public function create() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'     => trim($_POST['title']),
                'author'    => trim($_POST['author']),
                'isbn'      => trim($_POST['isbn']),
                'publisher' => trim($_POST['publisher']),
                'quantity'  => (int)$_POST['quantity'],
            ];
            if ($this->model->create($data)) {
                header('Location: index.php?page=books&msg=created');
                exit;
            }
        }
        require __DIR__ . '/../views/books/create.php';
    }

    public function edit($id) {
        $book = $this->model->getById($id);
        if (!$book) {
            header('Location: index.php?page=books');
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'title'     => trim($_POST['title']),
                'author'    => trim($_POST['author']),
                'isbn'      => trim($_POST['isbn']),
                'publisher' => trim($_POST['publisher']),
                'quantity'  => (int)$_POST['quantity'],
            ];
            if ($this->model->update($id, $data)) {
                header('Location: index.php?page=books&msg=updated');
                exit;
            }
        }
        require __DIR__ . '/../views/books/edit.php';
    }

    public function delete($id) {
        $this->model->delete($id);
        header('Location: index.php?page=books&msg=deleted');
        exit;
    }
}