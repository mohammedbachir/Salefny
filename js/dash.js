  const userName = "أميمة";
  document.getElementById('userGreeting').textContent = `مرحباً، ${userName}`;

  function showSection(id) {
    const sections = document.querySelectorAll('.content-section');
    sections.forEach(section => section.classList.remove('active'));
    document.getElementById(id).classList.add('active');
  }

  function logout() {
    if (confirm("هل تريد تسجيل الخروج؟")) {
      alert("تم تسجيل الخروج");
      window.location.href = "login.html";
    }
  }
