<?php
defined('APP_BOOTSTRAPPED') || exit;
$value = payout_text('bank_name');
if ($value === '') {
    $value = $vendor['bank_name'] ?? '';
}
?>
<label>Ngân hàng
    <input id="withdrawal-bank" name="bank_name" list="withdrawal-bank-list"
           autocomplete="off" maxlength="100" placeholder="Ví dụ: VCB, MB, TCB"
           value="<?= h(withdrawal_bank_name((string)$value)) ?>" required>
</label>
<datalist id="withdrawal-bank-list">
    <?php foreach (withdrawal_banks() as $name => $aliases): ?>
        <option value="<?= h($name) ?>"
                label="<?= h(implode(' · ', array_slice($aliases, 0, 2))) ?>"
                data-aliases="<?= h(implode('|', $aliases)) ?>"></option>
    <?php endforeach; ?>
</datalist>
<script>
(() => {
    const field = document.getElementById('withdrawal-bank');
    const list = document.getElementById('withdrawal-bank-list');
    const key = value => value.normalize('NFD').toLowerCase()
        .replace(/\p{M}/gu, '').replace(/đ/g, 'd').replace(/[^a-z0-9]/g, '');

    field.addEventListener('change', () => {
        const entered = key(field.value);
        if (!entered) return;

        for (const option of list.options) {
            const names = [option.value, ...option.dataset.aliases.split('|')];
            if (names.some(name => key(name) === entered)) {
                field.value = option.value;
                break;
            }
        }
    });
})();
</script>
