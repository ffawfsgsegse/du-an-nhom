<?php defined('APP_BOOTSTRAPPED') || exit; ?>
<section class="auth-wrap">
  <div class="auth-card">
    <div class="eyebrow">WELCOME TO 3DPRINTHUB</div>
    <h1>Chào mừng <em>trở lại.</em></h1>
    <p class="muted">Sáng tạo không giới hạn, bắt đầu từ đây.</p>
    <form method="post" action="<?= h(url('login')) ?>" class="stack">
      <?php csrf_field(); ?>
      <input type="hidden" name="action" value="login">
      <input type="hidden" name="redirect" value="<?= h($_GET['redirect'] ?? $_POST['redirect'] ?? '') ?>">
      <input type="hidden" name="redirect_id" value="<?= h($_GET['id'] ?? $_POST['redirect_id'] ?? '') ?>">
      <label for="email">Email
        <input id="email" required type="email" name="email" maxlength="120" placeholder="you@example.com" autocomplete="email" value="<?= h($_POST['email'] ?? '') ?>">
      </label>
      <label for="password">Mật khẩu
        <input id="password" required type="password" name="password" minlength="1" placeholder="••••••••" autocomplete="current-password">
      </label>
      <button class="btn btn-primary btn-lg" type="submit">Đăng nhập →</button>
    </form>
    <p class="muted auth-links">Chưa có tài khoản? <a href="<?= h(url('register')) ?>">Đăng ký ngay</a></p>
  </div>
</section>