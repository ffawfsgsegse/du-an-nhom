<?php defined('APP_BOOTSTRAPPED') || exit; ?>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="eyebrow">WELCOME TO 3DPRINTHUB</div>
    <h1>Tham gia <em>cộng đồng.</em></h1>
    <p class="muted">Sáng tạo không giới hạn, bắt đầu từ đây.</p>
    <form method="post" action="<?=h(url('register'))?>" class="stack">
      <?php csrf_field(); ?>
      <input type="hidden" name="action" value="register">
      <label for="full_name">Họ và tên
        <input id="full_name" required name="name" maxlength="100" minlength="2" placeholder="Nguyễn Văn A" autocomplete="name" value="<?=h($_POST['name'] ?? '')?>">
      </label>
      <label for="email">Email
        <input id="email" required type="email" name="email" maxlength="120" placeholder="you@example.com" autocomplete="email" value="<?=h($_POST['email'] ?? '')?>">
      </label>
      <label for="password">Mật khẩu
        <input id="password" required type="password" name="password" minlength="8" placeholder="••••••••" autocomplete="new-password">
      </label>
      <label for="confirm_password">Xác nhận mật khẩu
        <input id="confirm_password" required type="password" name="confirm_password" minlength="8" placeholder="Nhập lại mật khẩu" autocomplete="new-password">
      </label>
      <button class="btn btn-primary btn-lg" type="submit">Tạo tài khoản →</button>
    </form>
    <p class="muted auth-links">Đã có tài khoản? <a href="<?=h(url('login'))?>">Đăng nhập</a></p>
  </div>
</section>
