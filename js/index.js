

    // فتح وإغلاق القائمة في الأجهزة الصغيرة
    const mobileMenuToggle = document.getElementById('mobileMenuToggle');
    const navLinks = document.getElementById('navLinks');
    mobileMenuToggle.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });

    // سلايدر الإعلانات البسيط
    const adsSlider = document.getElementById('adsSlider');
    const dots = document.querySelectorAll('.slider-dot');
    let currentIndex = 0;
    const slides = adsSlider.children;
    const totalSlides = slides.length;

    function showSlide(index) {
        for (let i = 0; i < totalSlides; i++) {
            slides[i].style.left = `${(i - index) * 100}%`;
            dots[i].classList.toggle('active', i === index);
        }
        currentIndex = index;
    }

    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            showSlide(parseInt(dot.dataset.index));
        });
    });

    function autoSlide() {
        let nextIndex = (currentIndex + 1) % totalSlides;
        showSlide(nextIndex);
    }

    setInterval(autoSlide, 5000);

    // البحث التفاعلي (تحتاج إلى ربط بالبيانات)
    const searchInput = document.getElementById('searchInput');
    searchInput.addEventListener('input', (e) => {
        const query = e.target.value.trim().toLowerCase();
        // هنا يمكن إضافة منطق البحث وتصفية الكتب حسب الكلمة المدخلة
        console.log('بحث عن:', query);
    });

