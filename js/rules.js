
    // فتح وإغلاق القائمة في الأجهزة الصغيرة
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');
    
    mobileMenuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    // إضافة تأثيرات تفاعلية للأقسام
    const termsSections = document.querySelectorAll('.terms-section');
    
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.transform = 'translateY(0)';
                entry.target.style.opacity = '1';
            }
        });
    }, observerOptions);

    termsSections.forEach(section => {
        section.style.transform = 'translateY(20px)';
        section.style.opacity = '0';
        section.style.transition = 'all 0.6s ease';
        observer.observe(section);
    });
