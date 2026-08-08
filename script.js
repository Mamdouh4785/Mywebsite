let currentLang = localStorage.getItem('lang') || 'ar';

document.addEventListener('DOMContentLoaded', () => {
    fetch('track_visitor.php').catch(err => console.log(err));
    
    // جلب ملف الـ CV المحدث
    fetch('get_settings.php')
        .then(r => r.json())
        .then(data => {
            if(data && data.cv_file) {
                document.getElementById('cv-download-btn').setAttribute('href', data.cv_file);
            }
        });

    applyLanguage(currentLang);

    document.getElementById('lang-toggle-btn').addEventListener('click', () => {
        currentLang = currentLang === 'ar' ? 'en' : 'ar';
        localStorage.setItem('lang', currentLang);
        applyLanguage(currentLang);
    });
});

function applyLanguage(lang) {
    const html = document.documentElement;
    const btn = document.getElementById('lang-toggle-btn');

    if(lang === 'en') {
        html.setAttribute('lang', 'en');
        html.setAttribute('dir', 'ltr');
        btn.innerHTML = '<i class="fa-solid fa-globe"></i> العربية';
    } else {
        html.setAttribute('lang', 'ar');
        html.setAttribute('dir', 'rtl');
        btn.innerHTML = '<i class="fa-solid fa-globe"></i> English';
    }

    // ترجمة النصوص الثابتة
    document.querySelectorAll('[data-ar]').forEach(elem => {
        elem.innerText = elem.getAttribute(`data-${lang}`);
    });

    // جلب المشاريع والشهادات حسب اللغة الحالية
    loadProjects(lang);
    loadCourses(lang);
}

function loadProjects(lang) {
    const container = document.getElementById('projects-container');
    fetch('get_projects.php')
        .then(res => res.json())
        .then(projects => {
            container.innerHTML = '';
            if(projects.length === 0) {
                container.innerHTML = `<p style="color:var(--text-muted);">${lang === 'ar' ? 'لا توجد مشاريع حالياً.' : 'No projects available.'}</p>`;
                return;
            }
            projects.forEach(p => {
                const title = lang === 'ar' ? p.title_ar : p.title_en;
                const desc = lang === 'ar' ? p.description_ar : p.description_en;
                const card = document.createElement('div');
                card.className = 'project-card';
                card.innerHTML = `
                    <div>
                        <h3>${title}</h3>
                        <p>${desc}</p>
                    </div>
                    ${p.link ? `<a href="${p.link}" target="_blank">${lang === 'ar' ? 'معاينة المشروع' : 'View Project'} <i class="fa-solid fa-arrow-up-right-from-square"></i></a>` : ''}
                `;
                container.appendChild(card);
            });
        });
}

function loadCourses(lang) {
    const container = document.getElementById('courses-container');
    fetch('get_courses.php')
        .then(res => res.json())
        .then(courses => {
            container.innerHTML = '';
            if(courses.length === 0) {
                container.innerHTML = `<p style="color:var(--text-muted);">${lang === 'ar' ? 'لا توجد شهادات حالياً.' : 'No certificates available.'}</p>`;
                return;
            }
            courses.forEach(c => {
                const title = lang === 'ar' ? c.title_ar : c.title_en;
                const provider = lang === 'ar' ? c.provider_ar : c.provider_en;
                const card = document.createElement('div');
                card.className = 'course-card';
                card.innerHTML = `
                    <div class="course-info">
                        <i class="fa-solid fa-award"></i>
                        <div>
                            <h3>${title}</h3>
                            <p>${lang === 'ar' ? 'الجهة المقدمة:' : 'Provider:'} ${provider}</p>
                        </div>
                    </div>
                    ${c.certificate_image ? `<a href="${c.certificate_image}" target="_blank" class="btn btn-secondary" style="font-size:0.8rem; padding:0.4rem 0.8rem;">${lang === 'ar' ? 'عرض الشهادة' : 'View Certificate'} <i class="fa-solid fa-image"></i></a>` : ''}
                `;
                container.appendChild(card);
            });
        });
}