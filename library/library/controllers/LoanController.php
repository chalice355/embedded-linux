<?php
require_once __DIR__ . '/../models/Loan.php';
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Member.php';

class LoanController {
    private $loanModel;
    private $bookModel;
    private $memberModel;

    public function __construct() {
        $this->loanModel   = new Loan();
        $this->bookModel   = new Book();
        $this->memberModel = new Member();
    }

    public function index() {
        $this->loanModel->updateOverdueStatus();
        $loans = $this->loanModel->getActive();
        require __DIR__ . '/../views/loans/index.php';
    }

    public function create() {
        $books   = $this->bookModel->getAll();
        $members = $this->memberModel->getAll();
        $error   = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $book_id   = (int)$_POST['book_id'];
            $member_id = (int)$_POST['member_id'];
            $loan_date = $_POST['loan_date'];
            $due_date  = $_POST['due_date'];

            $book = $this->bookModel->getById($book_id);
            if ($book['available_qty'] <= 0) {
                $error = '해당 도서의 재고가 없습니다.';
            } else {
                $this->bookModel->decreaseAvailable($book_id);
                $this->loanModel->create($book_id, $member_id, $loan_date, $due_date);
                header('Location: index.php?page=loans&msg=created');
                exit;
            }
        }
        require __DIR__ . '/../views/loans/create.php';
    }

    public function return($id) {
        $loan = $this->loanModel->getById($id);
        if ($loan && $loan['status'] !== 'returned') {
            $this->loanModel->returnLoan($id);
            $this->bookModel->increaseAvailable($loan['book_id']);
        }
        header('Location: index.php?page=loans&msg=returned');
        exit;
    }

    public function history() {
        $loans = $this->loanModel->getAll();
        require __DIR__ . '/../views/loans/history.php';
    }

    public function overdue() {
        $this->loanModel->updateOverdueStatus();
        $loans = $this->loanModel->getOverdue();
        require __DIR__ . '/../views/loans/overdue.php';
    }
}