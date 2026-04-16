<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-arrow-left-right me-2"></i>대출 현황</h4>
    <a href="index.php?page=loans&action=create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> 대출 처리
    </a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>도서명</th><th>회원명</th>
                    <th>대출일</th><th>반납기한</th><th>상태</th><th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($loans as $loan): ?>
                <tr>
                    <td><?= $loan['id'] ?></td>
                    <td><?= htmlspecialchars($loan['book_title']) ?></td>
                    <td><?= htmlspecialchars($loan['member_name']) ?></td>
                    <td><?= $loan['loan_date'] ?></td>
                    <td class="<?= ($loan['status']==='overdue') ? 'text-danger fw-bold' : '' ?>">
                        <?= $loan['due_date'] ?>
                    </td>
                    <td>
                        <?php if ($loan['status'] === 'overdue'): ?>
                            <span class="badge bg-danger">연체</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark">대출중</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <a href="index.php?page=loans&action=return&id=<?= $loan['id'] ?>"
                           class="btn btn-sm btn-outline-success"
                           onclick="return confirm('반납 처리하시겠습니까?')">반납</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($loans)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">대출 중인 도서가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>