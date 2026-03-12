<div class="d-flex justify-content-between align-items-center mb-3">
    <h4><i class="bi bi-people me-2"></i>회원 관리</h4>
    <a href="index.php?page=members&action=create" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> 회원 등록
    </a>
</div>

<div class="card mb-3">
    <div class="card-body py-2">
        <form method="get" class="d-flex gap-2">
            <input type="hidden" name="page" value="members">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="이름 또는 이메일 검색"
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
                <tr><th>#</th><th>이름</th><th>이메일</th><th>연락처</th><th>등록일</th><th>관리</th></tr>
            </thead>
            <tbody>
            <?php foreach ($members as $member): ?>
                <tr>
                    <td><?= $member['id'] ?></td>
                    <td><?= htmlspecialchars($member['name']) ?></td>
                    <td><?= htmlspecialchars($member['email']) ?></td>
                    <td><?= htmlspecialchars($member['phone'] ?? '-') ?></td>
                    <td><?= date('Y-m-d', strtotime($member['created_at'])) ?></td>
                    <td>
                        <a href="index.php?page=members&action=edit&id=<?= $member['id'] ?>"
                           class="btn btn-sm btn-outline-primary">수정</a>
                        <a href="index.php?page=members&action=delete&id=<?= $member['id'] ?>"
                           class="btn btn-sm btn-outline-danger"
                           onclick="return confirm('삭제하시겠습니까?')">삭제</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($members)): ?>
                <tr><td colspan="6" class="text-center text-muted py-3">등록된 회원이 없습니다.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>