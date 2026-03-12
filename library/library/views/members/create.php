<h4 class="mb-4"><i class="bi bi-person-plus me-2"></i>회원 등록</h4>
<div class="card" style="max-width:500px">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">이름 <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">이메일 <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">연락처</label>
                <input type="text" name="phone" class="form-control" placeholder="010-0000-0000">
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">등록</button>
                <a href="index.php?page=members" class="btn btn-secondary">취소</a>
            </div>
        </form>
    </div>
</div>