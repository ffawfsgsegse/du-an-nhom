// Chỉ sửa thông báo toast (hiện / tự biến mất) tại đây.
// Giữ lại hiệu ứng giao diện và thông báo; không chứa giỏ hàng, thanh toán hay quản trị.
const toast = document.querySelector('#toast');
if (toast) setTimeout(() => { toast.style.opacity = '0'; toast.style.transition = 'opacity .4s'; setTimeout(() => toast.remove(), 450); }, 3600);
