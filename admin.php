<?php
session_start();
require 'db.php';

$is_logged_in = isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
$error = "";

$views_count = 0;
$views_query = $conn->query("SELECT views_count FROM page_views WHERE id = 1");
if ($views_query) $views_count = $views_query->fetch_assoc()['views_count'] ?? 0;

if (isset($_GET['logout'])) { session_destroy(); header("Location: admin.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password']) || $password === $user['password']) {
            $_SESSION['admin_logged_in'] = true;
            $is_logged_in = true;
        } else { $error = "كلمة المرور غير صحيحة"; }
    } else { $error = "اسم المستخدم غير صحيح"; }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>إدارة المحتوى والإدارة | ممدوح</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root { --bg-body: #070a12; --panel-bg: #111726; --input-bg: #070a12; --border-color: #1d273e; --primary: #38bdf8; --accent: #34d399; --warning: #f59e0b; --danger: #ef4444; --text-main: #f8fafc; --text-sub: #94a3b8; }
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Tajawal', sans-serif; }
        body { background-color: var(--bg-body); color: var(--text-main); display: flex; justify-content: center; min-height: 100vh; padding: 2rem 1rem; }
        .dashboard { width: 100%; max-width: 750px; }
        .header-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color); }
        .btn-logout { background: #1e293b; color: #f1f5f9; padding: 0.5rem 0.9rem; text-decoration: none; border-radius: 6px; font-size: 0.85rem; }
        .stat-card { background: linear-gradient(135deg, #111726 0%, #1e293b 100%); border: 1px solid var(--border-color); padding: 1.2rem; border-radius: 12px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; }
        .tabs { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; background: var(--panel-bg); padding: 0.3rem; border-radius: 8px; border: 1px solid var(--border-color); }
        .tab-btn { flex: 1; padding: 0.7rem; border: none; background: transparent; color: var(--text-sub); font-weight: 700; cursor: pointer; border-radius: 6px; font-size: 0.85rem; }
        .tab-btn.active { background: var(--primary); color: #070a12; }
        .panel-card { background: var(--panel-bg); border: 1px solid var(--border-color); border-radius: 12px; padding: 1.8rem; margin-bottom: 1.5rem; }
        .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 0.4rem; }
        input, textarea { width: 100%; background: var(--input-bg); border: 1px solid var(--border-color); color: white; padding: 0.75rem; border-radius: 8px; font-size: 0.9rem; outline: none; }
        input:focus, textarea:focus { border-color: var(--primary); }
        .btn-submit { width: 100%; background: var(--accent); color: #070a12; font-weight: 800; padding: 0.8rem; border: none; border-radius: 8px; font-size: 1rem; cursor: pointer; margin-top: 0.5rem; }
        .btn-cancel { background: #334155; color: white; border: none; padding: 0.8rem; border-radius: 8px; cursor: pointer; margin-top: 0.5rem; display: none; }

        /* أزرار الإجراءات في القائمة */
        .items-list { margin-top: 2rem; border-top: 1px solid var(--border-color); padding-top: 1.5rem; }
        .items-list h3 { font-size: 1rem; color: var(--primary); margin-bottom: 1rem; }
        .item-row { display: flex; justify-content: space-between; align-items: center; background: var(--input-bg); border: 1px solid var(--border-color); padding: 0.8rem 1rem; border-radius: 8px; margin-bottom: 0.6rem; }
        .action-btns { display: flex; gap: 0.5rem; }
        .btn-edit { background: var(--warning); color: #000; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold; }
        .btn-delete { background: var(--danger); color: white; border: none; padding: 0.4rem 0.8rem; border-radius: 6px; cursor: pointer; font-size: 0.8rem; font-weight: bold; }

        #toast { visibility: hidden; min-width: 250px; background-color: var(--accent); color: #000; font-weight: bold; text-align: center; border-radius: 8px; padding: 12px; position: fixed; z-index: 99; bottom: 30px; left: 50%; transform: translateX(-50%); }
        #toast.show { visibility: visible; animation: fadein 0.5s, fadeout 0.5s 2.5s; }
        @keyframes fadein { from {bottom: 0; opacity: 0;} to {bottom: 30px; opacity: 1;} }
        @keyframes fadeout { from {bottom: 30px; opacity: 1;} to {bottom: 0; opacity: 0;} }
    </style>
</head>
<body>

<div class="dashboard">
    <?php if (!$is_logged_in): ?>
        <div class="panel-card" style="margin-top: 4rem;">
            <h2 style="margin-bottom: 1.5rem; text-align: center;">دخول لوحة التحكم</h2>
            <?php if($error): ?><p style="color:#f87171; text-align:center; margin-bottom:1rem;"><?= $error ?></p><?php endif; ?>
            <form method="POST">
                <div class="form-group"><label>اسم المستخدم</label><input type="text" name="username" required></div>
                <div class="form-group"><label>كلمة المرور</label><input type="password" name="password" required></div>
                <button type="submit" name="login_submit" class="btn-submit" style="background:var(--primary);">دخول</button>
            </form>
        </div>
    <?php else: ?>
        <div class="header-bar">
            <h1>لوحة تحكم ممدوح</h1>
            <div>
                <a href="index.html" target="_blank" class="btn-logout">معاينة الموقع <i class="fa-solid fa-external-link"></i></a>
                <a href="admin.php?logout=true" class="btn-logout">خروج</a>
            </div>
        </div>

        <div class="stat-card">
            <div>
                <span style="color:var(--text-sub); font-size:0.85rem; display:block;">إجمالي زوار الموقع</span>
                <strong style="font-size:1.1rem;">مشاهدات حقيقية</strong>
            </div>
            <div style="font-size:1.8rem; font-weight:900; color:var(--accent);"><?= number_format($views_count) ?></div>
        </div>

        <div class="tabs">
            <button class="tab-btn active" onclick="switchTab('projects', this)"><i class="fa-solid fa-code"></i> إدارة المشاريع</button>
            <button class="tab-btn" onclick="switchTab('courses', this)"><i class="fa-solid fa-certificate"></i> إدارة الشهادات</button>
            <button class="tab-btn" onclick="switchTab('cv', this)"><i class="fa-solid fa-file-pdf"></i> السيرة الذاتية</button>
        </div>

        <!-- تبويب المشاريع -->
        <div id="tab-projects" class="panel-card">
            <h2 id="p-form-title" style="font-size:1.1rem; margin-bottom:1rem; color:var(--primary);">إضافة مشروع جديد</h2>
            <form id="project-form">
                <input type="hidden" id="p-edit-id" value="">
                <div class="grid-2">
                    <div class="form-group"><label>عنوان المشروع (عربي) *</label><input type="text" id="p-title-ar" required></div>
                    <div class="form-group"><label>Title (English) *</label><input type="text" id="p-title-en" dir="ltr" required></div>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label>الوصف (عربي) *</label><textarea id="p-desc-ar" rows="3" required></textarea></div>
                    <div class="form-group"><label>Description (English) *</label><textarea id="p-desc-en" rows="3" dir="ltr" required></textarea></div>
                </div>
                <div class="form-group"><label>رابط المشروع (اختياري)</label><input type="url" id="p-link" dir="ltr"></div>
                <button type="submit" id="p-submit-btn" class="btn-submit">نشر المشروع</button>
                <button type="button" id="p-cancel-btn" class="btn-cancel" onclick="resetProjectForm()">إلغاء التعديل</button>
            </form>

            <div class="items-list">
                <h3><i class="fa-solid fa-list"></i> المشاريع المضافة حالياً:</h3>
                <div id="admin-projects-list">جاري التحميل...</div>
            </div>
        </div>

        <!-- تبويب الشهادات -->
        <div id="tab-courses" class="panel-card" style="display: none;">
            <h2 id="c-form-title" style="font-size:1.1rem; margin-bottom:1rem; color:var(--primary);">إضافة شهادة جديدة</h2>
            <form id="course-form" enctype="multipart/form-data">
                <input type="hidden" id="c-edit-id" value="">
                <div class="grid-2">
                    <div class="form-group"><label>اسم الشهادة (عربي) *</label><input type="text" id="c-title-ar" required></div>
                    <div class="form-group"><label>Title (English) *</label><input type="text" id="c-title-en" dir="ltr" required></div>
                </div>
                <div class="grid-2">
                    <div class="form-group"><label>الجهة المقدمة (عربي) *</label><input type="text" id="c-provider-ar" required></div>
                    <div class="form-group"><label>Provider (English) *</label><input type="text" id="c-provider-en" dir="ltr" required></div>
                </div>
                <div class="form-group"><label>صورة الشهادة (اختياري)</label><input type="file" id="c-image" accept="image/*,.pdf"></div>
                <button type="submit" id="c-submit-btn" class="btn-submit">حفظ الشهادة</button>
                <button type="button" id="c-cancel-btn" class="btn-cancel" onclick="resetCourseForm()">إلغاء التعديل</button>
            </form>

            <div class="items-list">
                <h3><i class="fa-solid fa-list"></i> الشهادات المضافة حالياً:</h3>
                <div id="admin-courses-list">جاري التحميل...</div>
            </div>
        </div>
<!-- تبويب السيرة الذاتية -->
        <div id="tab-cv" class="panel-card" style="display: none;">
            <div id="cv-status" style="margin-bottom: 1.2rem; background: var(--input-bg); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
                <p style="color: var(--text-sub); font-size: 0.9rem;">جاري جلب حالة السيرة الذاتية...</p>
            </div>

            <form id="cv-form" enctype="multipart/form-data">
                <div class="form-group">
                    <label>رفع ملف سيرة ذاتية جديدة (PDF فقط) *</label>
                    <input type="file" id="cv-file" accept=".pdf" required>
                </div>
                <button type="submit" class="btn-submit">رفع وتحديث السيرة الذاتية</button>
            </form>

            <button type="button" id="delete-cv-btn" class="btn-delete" style="width: 100%; margin-top: 1rem; padding: 0.8rem; font-size: 0.95rem; display: none;" onclick="deleteCV()">
                <i class="fa-solid fa-trash"></i> حذف السيرة الذاتية الحالية نهائياً
            </button>
            
        </div>
        
    <?php endif; ?>
</div>

<div id="toast">تمت العملية بنجاح!</div>

<script>
    let rawProjects = [];
    let rawCourses = [];

    function switchTab(tab, btn) {
        document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
        document.getElementById('tab-projects').style.display = 'none';
        document.getElementById('tab-courses').style.display = 'none';
        document.getElementById('tab-cv').style.display = 'none';

        document.getElementById('tab-' + tab).style.display = 'block';
        btn.classList.add('active');
    }

    function showToast(msg) {
        const toast = document.getElementById("toast");
        toast.innerText = msg;
        toast.className = "show";
        setTimeout(() => { toast.className = toast.className.replace("show", ""); }, 3000);
    }

    // جلب قائمة المشاريع
    function loadAdminProjects() {
        fetch('get_projects.php')
            .then(r => r.json())
            .then(data => {
                rawProjects = data || [];
                const list = document.getElementById('admin-projects-list');
                if(!data || data.length === 0) { list.innerHTML = '<p style="color:var(--text-sub);">لا توجد مشاريع مضافة.</p>'; return; }
                list.innerHTML = '';
                data.forEach(p => {
                    list.innerHTML += `
                        <div class="item-row">
                            <span><strong>${p.title_ar}</strong> (${p.title_en})</span>
                            <div class="action-btns">
                                <button class="btn-edit" onclick="editProject(${p.id})"><i class="fa-solid fa-pen-to-square"></i> تعديل</button>
                                <button class="btn-delete" onclick="deleteProject(${p.id})"><i class="fa-solid fa-trash"></i> حذف</button>
                            </div>
                        </div>
                    `;
                });
            });
    }

    // جلب قائمة الشهادات
    function loadAdminCourses() {
        fetch('get_courses.php')
            .then(r => r.json())
            .then(data => {
                rawCourses = data || [];
                const list = document.getElementById('admin-courses-list');
                if(!data || data.length === 0) { list.innerHTML = '<p style="color:var(--text-sub);">لا توجد شهادات مضافة.</p>'; return; }
                list.innerHTML = '';
                data.forEach(c => {
                    list.innerHTML += `
                        <div class="item-row">
                            <span><strong>${c.title_ar}</strong> (${c.title_en})</span>
                            <div class="action-btns">
                                <button class="btn-edit" onclick="editCourse(${c.id})"><i class="fa-solid fa-pen-to-square"></i> تعديل</button>
                                <button class="btn-delete" onclick="deleteCourse(${c.id})"><i class="fa-solid fa-trash"></i> حذف</button>
                            </div>
                        </div>
                    `;
                });
            });
    }

    // بدء تعديل مشروع
    function editProject(id) {
        const p = rawProjects.find(item => item.id == id);
        if(!p) return;
        document.getElementById('p-edit-id').value = p.id;
        document.getElementById('p-title-ar').value = p.title_ar;
        document.getElementById('p-title-en').value = p.title_en;
        document.getElementById('p-desc-ar').value = p.description_ar;
        document.getElementById('p-desc-en').value = p.description_en;
        document.getElementById('p-link').value = p.link || '';

        document.getElementById('p-form-title').innerText = 'تعديل بيانات المشروع';
        document.getElementById('p-submit-btn').innerText = 'حفظ التعديلات';
        document.getElementById('p-submit-btn').style.background = 'var(--warning)';
        document.getElementById('p-cancel-btn').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetProjectForm() {
        document.getElementById('p-edit-id').value = '';
        document.getElementById('project-form').reset();
        document.getElementById('p-form-title').innerText = 'إضافة مشروع جديد';
        document.getElementById('p-submit-btn').innerText = 'نشر المشروع';
        document.getElementById('p-submit-btn').style.background = 'var(--accent)';
        document.getElementById('p-cancel-btn').style.display = 'none';
    }

    // بدء تعديل شهادة
    function editCourse(id) {
        const c = rawCourses.find(item => item.id == id);
        if(!c) return;
        document.getElementById('c-edit-id').value = c.id;
        document.getElementById('c-title-ar').value = c.title_ar;
        document.getElementById('c-title-en').value = c.title_en;
        document.getElementById('c-provider-ar').value = c.provider_ar;
        document.getElementById('c-provider-en').value = c.provider_en;

        document.getElementById('c-form-title').innerText = 'تعديل بيانات الشهادة';
        document.getElementById('c-submit-btn').innerText = 'حفظ التعديلات';
        document.getElementById('c-submit-btn').style.background = 'var(--warning)';
        document.getElementById('c-cancel-btn').style.display = 'block';
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetCourseForm() {
        document.getElementById('c-edit-id').value = '';
        document.getElementById('course-form').reset();
        document.getElementById('c-form-title').innerText = 'إضافة شهادة جديدة';
        document.getElementById('c-submit-btn').innerText = 'حفظ الشهادة';
        document.getElementById('c-submit-btn').style.background = 'var(--accent)';
        document.getElementById('c-cancel-btn').style.display = 'none';
    }

    // معالجة فورماً المشاريع (إضافة أو تعديل)
    document.getElementById('project-form').addEventListener('submit', e => {
        e.preventDefault();
        const editId = document.getElementById('p-edit-id').value;
        const url = editId ? 'edit_project.php' : 'add_project.php';

        const payload = {
            id: editId,
            title_ar: document.getElementById('p-title-ar').value,
            title_en: document.getElementById('p-title-en').value,
            description_ar: document.getElementById('p-desc-ar').value,
            description_en: document.getElementById('p-desc-en').value,
            link: document.getElementById('p-link').value
        };

        fetch(url, {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        }).then(r => r.json()).then(res => {
            if(res.status === 'success') { 
                showToast(editId ? '✏️ تم تحديث المشروع!' : '✅ تم نشر المشروع!'); 
                resetProjectForm(); 
                loadAdminProjects(); 
            }
        });
    });

    // معالجة فورماً الشهادات (إضافة أو تعديل)
    document.getElementById('course-form').addEventListener('submit', e => {
        e.preventDefault();
        const editId = document.getElementById('c-edit-id').value;
        const url = editId ? 'edit_course.php' : 'add_course.php';

        const formData = new FormData();
        if(editId) formData.append('id', editId);
        formData.append('title_ar', document.getElementById('c-title-ar').value);
        formData.append('title_en', document.getElementById('c-title-en').value);
        formData.append('provider_ar', document.getElementById('c-provider-ar').value);
        formData.append('provider_en', document.getElementById('c-provider-en').value);
        const fileInput = document.getElementById('c-image');
        if(fileInput.files[0]) formData.append('certificate_image', fileInput.files[0]);

        fetch(url, { method: 'POST', body: formData })
        .then(r => r.json()).then(res => {
            if(res.status === 'success') { 
                showToast(editId ? '✏️ تم تحديث الشهادة!' : '📜 تم حفظ الشهادة!'); 
                resetCourseForm(); 
                loadAdminCourses(); 
            }
        });
    });

    function deleteProject(id) {
        if(confirm('هل أنت تأكد من حذف هذا المشروع؟')) {
            fetch('delete_project.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ id }) })
            .then(r => r.json()).then(res => { if(res.status === 'success') { showToast('🗑️ تم الحذف'); loadAdminProjects(); } });
        }
    }

    function deleteCourse(id) {
        if(confirm('هل أنت تأكد من حذف هذه الشهادة؟')) {
            fetch('delete_course.php', { method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({ id }) })
            .then(r => r.json()).then(res => { if(res.status === 'success') { showToast('🗑️ تم الحذف'); loadAdminCourses(); } });
        }
    }

    // رفع CV
// جلب حالة السيرة الذاتية وإظهار زر الحذف إذا كانت موجودة
    function checkCVStatus() {
        fetch('get_settings.php')
            .then(r => r.json())
            .then(data => {
                const cvStatus = document.getElementById('cv-status');
                const deleteBtn = document.getElementById('delete-cv-btn');
                
                if (data && data.cv_file) {
                    cvStatus.innerHTML = `<p style="color: var(--accent);"><i class="fa-solid fa-check-circle"></i> توجد سيرة ذاتية مرفوعة حالياً: <a href="${data.cv_file}" target="_blank" style="color: var(--primary);">معاينة الملف 📄</a></p>`;
                    deleteBtn.style.display = 'block';
                } else {
                    cvStatus.innerHTML = `<p style="color: var(--text-sub);"><i class="fa-solid fa-info-circle"></i> لا توجد سيرة ذاتية مرفوعة حالياً.</p>`;
                    deleteBtn.style.display = 'none';
                }
            });
    }

    // تنفيذ حذف CV
    function deleteCV() {
        if(confirm('هل أنت تأكد من حذف السيرة الذاتية الحالية؟ سيتوقف زر التحميل لدى الزوار.')) {
            fetch('delete_cv.php', { method: 'POST' })
                .then(r => r.json())
                .then(res => {
                    if(res.status === 'success') {
                        showToast('🗑️ تم حذف السيرة الذاتية بنجاح!');
                        checkCVStatus();
                    }
                });
        }
    }

    // تشغيل فحص حالة CV عند التحميل
    checkCVStatus();

    loadAdminProjects();
    loadAdminCourses();
</script>
</body>
</html>