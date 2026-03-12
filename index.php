<?php
session_start();

$page   = $_GET['page']   ?? '';
$action = $_GET['action'] ?? 'index';
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

ob_start();

switch ($page) {
    case 'books':
        require_once __DIR__ . '/controllers/BookController.php';
        $ctrl = new BookController();
        switch ($action) {
            case 'create': $ctrl->create(); break;
            case 'edit':   if ($id) $ctrl->edit($id);   break;
            case 'delete': if ($id) $ctrl->delete($id); break;
            default:       $ctrl->index();
        }
        break;

    case 'members':
        require_once __DIR__ . '/controllers/MemberController.php';
        $ctrl = new MemberController();
        switch ($action) {
            case 'create': $ctrl->create(); break;
            case 'edit':   if ($id) $ctrl->edit($id);   break;
            case 'delete': if ($id) $ctrl->delete($id); break;
            default:       $ctrl->index();
        }
        break;

    case 'loans':
        require_once __DIR__ . '/controllers/LoanController.php';
        $ctrl = new LoanController();
        switch ($action) {
            case 'create':  $ctrl->create();           break;
            case 'return':  if ($id) $ctrl->return($id); break;
            case 'history': $ctrl->history();          break;
            case 'overdue': $ctrl->overdue();          break;
            default:        $ctrl->index();
        }
        break;

    default:
        require __DIR__ . '/views/dashboard.php';
        break;
}

$content = ob_get_clean();
require __DIR__ . '/views/layout.php';