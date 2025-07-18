 const password = document.getElementById('password');
  const confirmPassword = document.getElementById('confirmPassword');
  const passwordError = document.getElementById('passwordError');
  const confirmPasswordError = document.getElementById('confirmPasswordError');

  function validatePasswords() {
    let valid = true;

    // تحقق من الطول
    if (password.value.length < 8) {
      passwordError.style.display = 'block';
      valid = false;
    } else {
      passwordError.style.display = 'none';
    }

    // تحقق من التطابق
    if (password.value !== confirmPassword.value) {
      confirmPasswordError.style.display = 'block';
      valid = false;
    } else {
      confirmPasswordError.style.display = 'none';
    }

    return valid;
  }

  // التحقق عند تغيير الحقول
  password.addEventListener('input', validatePasswords);
  confirmPassword.addEventListener('input', validatePasswords);
