<h4 class="mb-4"><i class="bi bi-plus-circle me-2"></i>도서 등록</h4>
<div class="card" style="max-width:600px">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">제목 <span class="text-danger">*</span></label>
                <input type="text" name="title" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">저자 <span class="text-danger">*</span></label>
                <input type="text" name="author" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">ISBN</label>
                <input type="text" name="isbn" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">출판사</label>
                <input type="text" name="publisher" class="form-control">
            </div>
            <div class="mb-3">
                <label class="form-label">수량 <span class="text-danger">*</span></label>
                <input type="number" name="quantity" class="form-control" value="1" min="1" required>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">등록</button>
                <a href="index.php?page=books" class="btn btn-secondary">취소</a>
            </div>
        </form>
    </div>
</div>