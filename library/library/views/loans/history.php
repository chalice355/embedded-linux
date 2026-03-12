<h4 class="mb-4"><i class="bi bi-clock-history me-2"></i>대출 이력</h4>
<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>도서명</th><th>회원명</th>
                    <th>대출일</th><th>반납기한</th><th>반납일</th><th>상태</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($loans as $loan): ?>
                <tr>
                    <td><?= $loan['id'] ?></td>
                    <td><?= htmlspecialchars($loan['book_title']) ?></td>
                    <td><?= htmlspecialchars($loan['member_name']) ?></td>
                    <td><?= $loan['loan_date'] ?></td>
                    <td><?= $loan['due_date'] ?></td>
                    <td><?= $loan['return_date'] ?? '-' ?></td>
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
            <?php if (empty($loans)): ?>
                <tr><td colspan="7" class="text-center text-muted py-3">대출 이력이 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>