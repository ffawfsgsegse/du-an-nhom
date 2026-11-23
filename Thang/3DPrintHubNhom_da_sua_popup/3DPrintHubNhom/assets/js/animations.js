// Chỉ sửa hiệu ứng reveal của thẻ sản phẩm / danh mục tại đây.
if ('IntersectionObserver' in window) {
  const observer = new IntersectionObserver(entries => entries.forEach(entry => {
    if (entry.isIntersecting) { entry.target.classList.add('is-visible'); observer.unobserve(entry.target); }
  }), { threshold: .08 });
  document.querySelectorAll('.product-card,.category-tile').forEach((el, i) => {
    el.style.opacity = '0'; el.style.transform = 'translateY(24px)';
    el.style.transition = `opacity .55s ${Math.min(i % 8 * 65, 350)}ms, transform .55s ${Math.min(i % 8 * 65, 350)}ms`;
    observer.observe(el);
  });
  const style = document.createElement('style'); style.textContent = '.is-visible{opacity:1!important;transform:translateY(0)!important}'; document.head.appendChild(style);
}
