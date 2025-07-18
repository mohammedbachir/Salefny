
        // فتح وإغلاق القائمة في الأجهزة الصغيرة
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const navLinks = document.getElementById('navLinks');
        
        mobileMenuToggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });

        // قائمة المستخدم المنسدلة
        const userInfo = document.getElementById('userInfo');
        const dropdownMenu = document.getElementById('dropdownMenu');
        
        userInfo.addEventListener('click', (e) => {
            e.stopPropagation();
            dropdownMenu.classList.toggle('show');
        });

        // إغلاق القائمة المنسدلة عند النقر خارجها
        document.addEventListener('click', () => {
            dropdownMenu.classList.remove('show');
        });

        // منع إغلاق القائمة عند النقر داخلها
        dropdownMenu.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // البحث التفاعلي
        const searchInput = document.getElementById('searchInput');
        const searchBtn = document.getElementById('searchBtn');
        
        function performSearch() {
            const query = searchInput.value.trim().toLowerCase();
            if (query) {
                console.log('البحث عن:', query);
                // هنا يمكن إضافة منطق البحث الفعلي
                showLoading();
                setTimeout(() => {
                    hideLoading();
                    // عرض نتائج البحث
                }, 1000);
            }
        }

        searchBtn.addEventListener('click', performSearch);
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        // فلاتر البحث
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        filterBtns.forEach(btn => {
            btn.addEventListener('click', () => {
                // إزالة الفئة النشطة من جميع الأزرار
                filterBtns.forEach(b => b.classList.remove('active'));
                // إضافة الفئة النشطة للزر المحدد
                btn.classList.add('active');
                
                const filter = btn.dataset.filter;
                console.log('تطبيق فلتر:', filter);
                // هنا يمكن إضافة منطق الفلترة
            });
        });

        // أحداث الكتب
        const bookCards = document.querySelectorAll('.book-card');
        
        bookCards.forEach(card => {
            const readBtn = card.querySelector('.book-actions .btn:first-child');
            const saveBtn = card.querySelector('.book-actions .btn:nth-child(2)');
            const downloadBtn = card.querySelector('.book-actions .btn:last-child');
            
            if (readBtn) {
                readBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const bookTitle = card.querySelector('.book-title').textContent;
                    console.log('فتح كتاب:', bookTitle);
                    // هنا يمكن فتح قارئ الكتب
                });
            }
            
            if (saveBtn) {
                saveBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const bookTitle = card.querySelector('.book-title').textContent;
                    
                    if (saveBtn.textContent.trim() === 'حفظ') {
                        saveBtn.innerHTML = '<i class="fas fa-heart"></i> محفوظ';
                        saveBtn.style.backgroundColor = 'var(--accent-color)';
                        console.log('تم حفظ:', bookTitle);
                    } else {
                        saveBtn.innerHTML = 'حفظ';
                        saveBtn.style.backgroundColor = 'var(--dark-color)';
                        console.log('تم إلغاء حفظ:', bookTitle);
                    }
                });
            }
            
            if (downloadBtn) {
                downloadBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const bookTitle = card.querySelector('.book-title').textContent;
                    console.log('تحميل كتاب:', bookTitle);
                    
                    // عرض حالة التحميل
                    const originalText = downloadBtn.innerHTML;
                    downloadBtn.innerHTML = '<div class="loading"></div>';
                    downloadBtn.disabled = true;
                    
                    setTimeout(() => {
                        downloadBtn.innerHTML = '<i class="fas fa-check"></i> تم';
                        downloadBtn.style.backgroundColor = 'var(--primary-color)';
                        setTimeout(() => {
                            downloadBtn.innerHTML = originalText;
                            downloadBtn.style.backgroundColor = 'var(--info-color)';
                            downloadBtn.disabled = false;
                        }, 2000);
                    }, 2000);
                });
            }
        });

        // الكتب المقروءة مؤخراً
        const recentBooks = document.querySelectorAll('.recent-book-item');
        
        recentBooks.forEach(book => {
            book.addEventListener('click', () => {
                const bookTitle = book.querySelector('.recent-book-title').textContent;
                console.log('متابعة قراءة:', bookTitle);
                // هنا يمكن فتح الكتاب في آخر صفحة قرأها المستخدم
            });
        });

        // تأثيرات الرسوم المتحركة
        function showLoading() {
            // يمكن إضافة مؤشر تحميل عام هنا
        }

        function hideLoading() {
            // إخفاء مؤشر التحميل
        }

        // تطبيق التأثيرات المتحركة عند التحميل
        document.addEventListener('DOMContentLoaded', () => {
            const fadeElements = document.querySelectorAll('.fade-in');
            
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            });
            
            fadeElements.forEach(el => {
                el.style.opacity = '0';
                el.style.transform = 'translateY(20px)';
                el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(el);
            });
        });

        // تحديث الإحصائيات (محاكاة)
        function updateStats() {
            // هنا يمكن جلب الإحصائيات الفعلية من الخادم
            console.log('تحديث الإحصائيات...');
        }

        // تحديث الإحصائيات كل 5 دقائق
        setInterval(updateStats, 5 * 60 * 1000);

        // معالج أحداث النقرات العامة
        document.addEventListener('click', (e) => {
            // إغلاق القوائم المنسدلة
            if (!e.target.closest('.user-menu')) {
                dropdownMenu.classList.remove('show');
            }
            
            if (!e.target.closest('.nav-links') && !e.target.closest('.mobile-menu-toggle')) {
                navLinks.classList.remove('active');
            }
        });

        // حفظ حالة الفلاتر في التخزين المحلي (محاكاة)
        function saveUserPreferences() {
            const activeFilter = document.querySelector('.filter-btn.active')?.dataset.filter || 'all';
            console.log('حفظ تفضيلات المستخدم:', { activeFilter });
            // يمكن حفظ التفضيلات في قاعدة البيانات
        }

        // تحميل تفضيلات المستخدم عند بدء التشغيل
        function loadUserPreferences() {
            // يمكن تحميل التفضيلات من قاعدة البيانات
            console.log('تحميل تفضيلات المستخدم...');
        }

        // استدعاء تحميل التفضيلات عند تحميل الصفحة
        loadUserPreferences();
   