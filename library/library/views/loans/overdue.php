<h4 class="mb-4"><i class="bi bi-exclamation-triangle me-2 text-danger"></i>연체 도서</h4>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>도서명</th><th>회원명</th>
                    <th>대출일</th><th>반납기한</th><th>연체일수</th><th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($loans as $loan): ?>
                <tr class="table-danger">
                    <td><?= $loan['id'] ?></td>
                    <td><?= htmlspecialchars($loan['book_title']) ?></td>
                    <td><?= htmlspecialchars($loan['member_name']) ?></td>
                    <td><?= $loan['loan_date'] ?></td>
                    <td class="fw-bold text-danger"><?= $loan['due_date'] ?></td>
                    <td><span class="badge bg-danger"><?= $loan['overdue_days'] ?>일</span></td>
                    <td>
                        <a href="index.php?page=loans&action=return&id=<?= $loan['id'] ?>"
                           class="btn btn-sm btn-outline-success"
                           onclick="return confirm('반납 처리하시겠습니까?')">반납</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($loans)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">연체 도서가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>