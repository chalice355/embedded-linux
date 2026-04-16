<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-journal-text me-2"></i>도서 관리</h4>
    <a href="index.php?page=books&action=create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> 도서 등록
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="d-flex gap-2">
            <input type="hidden" name="page" value="books">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="제목 또는 저자 검색"
                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <button class="btn btn-outline-secondary btn-sm" type="submit">
                <i class="bi bi-search"></i> 검색
            </button>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th><th>제목</th><th>저자</th><th>ISBN</th>
                    <th>출판사</th><th>수량</th><th>대출가능</th><th>관리</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($books as $book): ?>
                <tr>
                    <td><?= $book['id'] ?></td>
                    <td><?= htmlspecialchars($book['title']) ?></td>
                    <td><?= htmlspecialchars($book['author']) ?></td>
                    <td><?= htmlspecialchars($book['isbn'] ?? '-') ?></td>
                    <td><?= htmlspecialchars($book['publisher'] ?? '-') ?></td>
                    <td><?= $book['quantity'] ?></td>
                    <td>
                        <span class="badge <?= $book['available_qty'] > 0 ? 'bg-success' : 'bg-secondary' ?>">
                            <?= $book['available_qty'] ?>
                        </span>
                    </td>
                    <td>
                        <a href="index.php?page=books&action=edit&id=<?= $book['id'] ?>"
                           class="btn btn-sm btn-outline-primary">수정</a>
                        <a href="index.php?page=books&action=delete&id=<?= $book['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('삭제하시겠습니까?')">삭제</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($books)): ?>
                <tr><td colspan="8" class="text-center text-muted py-3">등록된 도서가 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>