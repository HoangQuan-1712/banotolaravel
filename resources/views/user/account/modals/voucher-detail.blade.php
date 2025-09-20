<div class="modal fade" id="voucherDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="voucherDetailModalLabel">Chi tiết Voucher</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p id="voucherDescription"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var voucherDetailModal = document.getElementById('voucherDetailModal');
    voucherDetailModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget;
        var name = button.getAttribute('data-name');
        var description = button.getAttribute('data-description');
        
        var modalTitle = voucherDetailModal.querySelector('.modal-title');
        var modalBody = voucherDetailModal.querySelector('.modal-body p');
        
        modalTitle.textContent = name;
        modalBody.textContent = description;
    });
});
</script>
