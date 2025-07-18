
  const mobileMenuToggle = document.getElementById('mobileMenuToggle');
  const navLinks = document.getElementById('navLinks');

  mobileMenuToggle.addEventListener('click', () => {
    navLinks.classList.toggle('active');
  });
