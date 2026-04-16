<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>도서 대출 관리 시스템</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand fw-bold" href="index.php">
            <i class="bi bi-book-half me-2"></i>도서 대출 관리 시스템
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link <?= ($page??'')==='' ? 'active':'' ?>" href="index.php">
                        <i class="bi bi-speedometer2"></i> 대시보드
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page??'')==='books' ? 'active':'' ?>" href="index.php?page=books">
                        <i class="bi bi-journal-text"></i> 도서 관리
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= ($page??'')==='members' ? 'active':'' ?>" href="index.php?page=members">
                        <i class="bi bi-people"></i> 회원 관리
                    </a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?= ($page??'')==='loans' ? 'active':'' ?>"
                       href="#" data-bs-toggle="dropdown">
                        <i class="bi bi-arrow-left-right"></i> 대출 관리
                    </a>
                    <ul class="dropdown-menu">
                        <li><a class="dropdown-item" href="index.php?page=loans">대출 현황</a></li>
                        <li><a class="dropdown-item" href="index.php?page=loans&action=create">대출 처리</a></li>
                        <li><a class="dropdown-item" href="index.php?page=loans&action=history">대출 이력</a></li>
                        <li><a class="dropdown-item" href="index.php?page=loans&action=overdue">연체 도서</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (!empty($_GET['msg'])): ?>
        <?php $msgs = [
            'created'  => ['success', '정상적으로 등록되었습니다.'],
            'updated'  => ['success', '정상적으로 수정되었습니다.'],
            'deleted'  => ['warning', '삭제되었습니다.'],
            'returned' => ['info',    '반납 처리되었습니다.'],
        ]; ?>
        <?php if (isset($msgs[$_GET['msg']])): [$type, $text] = $msgs[$_GET['msg']]; ?>
        <div class="alert alert-<?= $type ?> alert-dismissible fade show">
            <?= htmlspecialchars($text) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>
    <?php endif; ?>

    <?= $content ?>
</div>

<footer class="mt-5 py-3 bg-light text-center text-muted">
    <small>도서 대출 관리 시스템 &copy; 2025 | LAMP Stack Project</small>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>