document.addEventListener('DOMContentLoaded', () => {
  // 1. Custom 3D Print Real-time Quote Calculator
  const vendorSelect = document.getElementById('quoteVendor');
  const materialRadios = document.querySelectorAll('input[name="material"]');
  const infillRange = document.getElementById('infillRange');
  const infillVal = document.getElementById('infillVal');
  const layerSelect = document.getElementById('layerSelect');
  const qtyInput = document.getElementById('printQty');
  const totalPriceEl = document.getElementById('quoteTotalPrice');

  function calculate3DQuote() {
    if (!totalPriceEl) return;
    const baseVolumeCm3 = 45.8; // Giả lập thể tích đọc từ file .STL
    let materialRate = 3000; // VNĐ / cm3 (PLA mặc định)
    materialRadios.forEach(r => {
      if (r.checked) {
        if (r.value === 'ABS') materialRate = 3600;
        if (r.value === 'RESIN') materialRate = 6500;
      }
    });

    const vendorMultiplier = vendorSelect ? parseFloat(vendorSelect.value) : 1.0;
    const infill = infillRange ? parseInt(infillRange.value) : 20;
    if (infillVal) infillVal.textContent = infill + '%';

    const layerMultiplier = layerSelect ? parseFloat(layerSelect.value) : 1.0;
    const qty = qtyInput ? Math.max(1, parseInt(qtyInput.value) || 1) : 1;

    const infillFactor = 0.75 + (infill / 100) * 0.6;
    const unitPrice = Math.round((baseVolumeCm3 * materialRate * infillFactor * layerMultiplier * vendorMultiplier) / 1000) * 1000;
    const total = unitPrice * qty;
    totalPriceEl.textContent = total.toLocaleString('vi-VN') + ' đ';
  }

  if (vendorSelect) vendorSelect.addEventListener('change', calculate3DQuote);
  materialRadios.forEach(r => r.addEventListener('change', calculate3DQuote));
  if (infillRange) infillRange.addEventListener('input', calculate3DQuote);
  if (layerSelect) layerSelect.addEventListener('change', calculate3DQuote);
  if (qtyInput) qtyInput.addEventListener('input', calculate3DQuote);
  calculate3DQuote();

  // Simulate STL File Upload
  const stlInput = document.getElementById('stlFileInput');
  const fileNameDisplay = document.getElementById('stlFileName');
  if (stlInput && fileNameDisplay) {
    stlInput.addEventListener('change', (e) => {
      if (e.target.files.length > 0) {
        fileNameDisplay.textContent = e.target.files[0].name;
      }
    });
  }

  // 2. Vendor Registration 4-Step Stepper
  const steps = document.querySelectorAll('.vendor-step-pane');
  const stepIndicators = document.querySelectorAll('.stepper-item');
  window.goToStep = function(stepIndex) {
    steps.forEach((pane, idx) => {
      pane.classList.toggle('d-none', idx !== stepIndex);
    });
    stepIndicators.forEach((ind, idx) => {
      ind.classList.remove('active', 'completed');
      if (idx < stepIndex) ind.classList.add('completed');
      if (idx === stepIndex) ind.classList.add('active');
    });
  };
});
