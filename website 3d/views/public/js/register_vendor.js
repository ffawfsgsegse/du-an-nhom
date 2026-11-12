document.addEventListener("DOMContentLoaded", function () {
  let currentStep = 1;
  const totalSteps = 4;

  // 1. Đếm ký tự Tên shop
  const shopNameInput = document.getElementById("shopNameInput");
  const charCount = document.getElementById("charCount");

  if (shopNameInput && charCount) {
    shopNameInput.addEventListener("input", function () {
      charCount.textContent = `${this.value.length}/30`;
    });
  }

  // 2. Gán sự kiện cho các nút chuyển bước
  const btnNext = document.getElementById("btnNext");
  const btnPrev = document.getElementById("btnPrev");

  if (btnNext) {
    btnNext.addEventListener("click", function () {
      changeStep(1);
    });
  }

  if (btnPrev) {
    btnPrev.addEventListener("click", function () {
      changeStep(-1);
    });
  }

  // 3. Hàm chuyển bước
  function changeStep(stepChange) {
    // Validation trước khi sang bước tiếp theo
    if (stepChange === 1) {
      const currentStepDiv = document.getElementById(`step-${currentStep}`);
      const requiredInputs = currentStepDiv.querySelectorAll("[required]");
      let valid = true;

      requiredInputs.forEach((input) => {
        if (!input.checkValidity()) {
          input.reportValidity();
          valid = false;
          return;
        }
      });

      if (!valid) return;
    }

    currentStep += stepChange;

    if (currentStep < 1) currentStep = 1;
    if (currentStep > totalSteps) currentStep = totalSteps;

    // An/Hien Form theo bước
    document.querySelectorAll(".form-step").forEach((el, index) => {
      el.classList.toggle("active", index + 1 === currentStep);
    });

    // Cap nhat Stepper Dot
    for (let i = 1; i <= totalSteps; i++) {
      const dot = document.getElementById(`step-dot-${i}`);
      if (dot) {
        dot.classList.remove("active", "completed");
        if (i < currentStep) {
          dot.classList.add("completed");
        } else if (i === currentStep) {
          dot.classList.add("active");
        }
      }
    }

    // Cap nhat nut dieu huong
    const btnSubmit = document.getElementById("btnSubmit");

    if (currentStep === 1) {
      btnPrev.classList.add("d-none");
    } else {
      btnPrev.classList.remove("d-none");
    }

    if (currentStep === totalSteps) {
      btnNext.classList.add("d-none");
      if (btnSubmit) btnSubmit.classList.remove("d-none");
    } else {
      btnNext.classList.remove("d-none");
      if (btnSubmit) btnSubmit.classList.add("d-none");
    }
  }
});
