<?php
require_once __DIR__ . '/../models/Book.php';
require_once __DIR__ . '/../models/Member.php';
require_once __DIR__ . '/../models/Loan.php';

$bookModel   = new Book();
$memberModel = new Member();
$loanModel   = new Loan();
$loanModel->updateOverdueStatus();

$totalBooks    = $bookModel->getTotalCount();
$borrowedBooks = $bookModel->getBorrowedCount();
$totalMembers  = $memberModel->getTotalCount();
$activeLoans   = $loanModel->getActiveCount();
$overdueLoans  = $loanModel->getOverdueCount();
$recentLoans   = $loanModel->getRecentActivity(5);
?>
<h4 class="mb-4"><i class="bi bi-speedometer2 me-2"></i>대시보드</h4>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card stat-card text-white bg-primary">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-5 fw-bold"><?= $totalBooks ?></div>
                    <div class="small">전체 도서</div>
                </div>
                <i class="bi bi-journal-text stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white bg-warning">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-5 fw-bold"><?= $borrowedBooks ?></div>
                    <div class="small">대출 중 도서</div>
                </div>
                <i class="bi bi-arrow-right-circle stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white bg-success">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-5 fw-bold"><?= $totalMembers ?></div>
                    <div class="small">전체 회원</div>
                </div>
                <i class="bi bi-people stat-icon"></i>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card text-white bg-danger">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <div class="fs-5 fw-bold"><?= $overdueLoans ?></div>
                    <div class="small">연체 도서</div>
                </div>
                <i class="bi bi-exclamation-triangle stat-icon"></i>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><i class="bi bi-clock-history me-1"></i>최근 대출/반납 내역</div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>도서명</th><th>회원명</th><th>대출일</th><th>반납기한</th><th>상태</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($recentLoans as $loan): ?>
                <tr>
                    <td><?= htmlspecialchars($loan['book_title']) ?></td>
                    <td><?= htmlspecialchars($loan['member_name']) ?></td>
                    <td><?= $loan['loan_date'] ?></td>
                    <td><?= $loan['due_date'] ?></td>
                    <td>
                        <?php if ($loan['status'] === 'returned'): ?>
                            <span class="badge bg-success">반납완료</span>
                        <?php elseif ($loan['status'] === 'overdue'): ?>
                            <span class="badge bg-danger">연체</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">대출중</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($recentLoans)): ?>
                <tr><td colspan="5" class="text-center text-muted py-3">대출 내역이 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>