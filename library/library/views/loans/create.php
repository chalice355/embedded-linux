<h4 class="mb-4"><i class="bi bi-arrow-right-circle me-2"></i>대출 처리</h4>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card" style="max-width:550px">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">도서 선택 <span class="text-danger">*</span></label>
                <select name="book_id" class="form-select" required>
                    <option value="">-- 도서 선택 --</option>
                    <?php foreach ($books as $book): ?>
                        <option value="<?= $book['id'] ?>"
                            <?= $book['available_qty'] <= 0 ? 'disabled' : '' ?>>
                            <?= htmlspecialchars($book['title']) ?>
                            (재고: <?= $book['available_qty'] ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">회원 선택 <span class="text-danger">*</span></label>
                <select name="member_id" class="form-select" required>
                    <option value="">-- 회원 선택 --</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?= $member['id'] ?>">
                            <?= htmlspecialchars($member['name']) ?> (<?= htmlspecialchars($member['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">대출일 <span class="text-danger">*</span></label>
                <input type="date" name="loan_date" class="form-control"
                       value="<?= date('Y-m-d') ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">반납기한 <span class="text-danger">*</span></label>
                <input type="date" name="due_date" class="form-control"
                       value="<?= date('Y-m-d', strtotime('+14 days')) ?>" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">대출 처리</button>
                <a href="index.php?page=loans" class="btn btn-secondary">취소</a>
            </div>
        </form>
    </div>
</div>