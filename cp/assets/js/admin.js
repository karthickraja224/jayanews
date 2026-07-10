// admin.js — shared utilities
// Toast notifications
function showToast(msg, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }
    const icons = { success: 'check-circle', error: 'exclamation-circle' };
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `<i class="fas fa-${icons[type] || 'info-circle'}"></i> ${msg}`;
    container.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateX(50px)'; toast.style.transition = '.3s'; setTimeout(() => toast.remove(), 300); }, 3000);
}

// Confirm delete modal
function confirmDelete(url, name) {
    const modal = document.getElementById('deleteModal');
    if (!modal) {
        const m = document.createElement('div');
        m.className = 'modal-overlay show'; m.id = 'deleteModal';
        m.innerHTML = `<div class="modal">
            <div class="modal-header">
                <span class="modal-title"><i class="fas fa-trash" style="color:var(--danger)"></i> நீக்கம் உறுதிப்படுத்தல்</span>
                <button class="modal-close" onclick="document.getElementById('deleteModal').remove()">×</button>
            </div>
            <div class="modal-body">
                <p style="color:var(--text-secondary)"><strong style="color:var(--text-primary)">"${name}"</strong> நிரந்தரமாக நீக்கப்படும். தொடர வேண்டுமா?</p>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="document.getElementById('deleteModal').remove()">ரத்து செய்</button>
                <a href="${url}" class="btn btn-danger"><i class="fas fa-trash"></i> நீக்கு</a>
            </div>
        </div>`;
        document.body.appendChild(m);
    }
}

// Tab system
document.addEventListener('DOMContentLoaded', () => {
    // Tabs
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.dataset.tab;
            const parent = btn.closest('.tabs-wrapper') || document;
            parent.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            parent.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
            btn.classList.add('active');
            document.getElementById(target)?.classList.add('active');
        });
    });

    // Image preview on file input
    document.querySelectorAll('input[type="file"].img-input').forEach(input => {
        input.addEventListener('change', function () {
            const preview = document.getElementById(this.dataset.preview);
            if (!preview) return;
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => { preview.src = e.target.result; preview.parentElement.style.display = 'inline-block'; };
                reader.readAsDataURL(file);
            }
        });
    });

    // Select2 init
    if (typeof $ !== 'undefined' && $.fn.select2) {
        $('.select2').select2({ theme: 'default', width: '100%' });
    }

    // Checkbox select-all
    const selectAll = document.getElementById('selectAll');
    if (selectAll) {
        selectAll.addEventListener('change', function () {
            document.querySelectorAll('.row-check').forEach(cb => cb.checked = this.checked);
        });
    }

    // Auto-slug from title
    const titleInput = document.getElementById('news_title');
    const slugInput  = document.getElementById('news_slug');
    if (titleInput && slugInput && !slugInput.dataset.manual) {
        titleInput.addEventListener('input', () => {
            slugInput.value = titleInput.value
                .toLowerCase()
                .replace(/\s+/g, '-')
                .replace(/[^\u0B80-\u0BFFa-z0-9\-]/g, '')
                .replace(/-+/g, '-');
        });
        slugInput.addEventListener('input', () => { slugInput.dataset.manual = '1'; });
    }
});

// ── Theme Picker (6 themes) ──────────────────
(function () {
    var STORAGE_KEY = 'jp_admin_theme';
    var THEMES = ['light','dark','ocean','nature','royal','contrast'];

    function applyTheme(theme) {
        if (!THEMES.includes(theme)) theme = 'light';
        document.documentElement.setAttribute('data-theme', theme);
        try { localStorage.setItem(STORAGE_KEY, theme); } catch(e) {}
        // Mark active option
        document.querySelectorAll('.theme-option').forEach(function(btn) {
            btn.classList.toggle('active', btn.dataset.theme === theme);
        });
    }

    // Read saved preference; default = 'light'
    var saved = 'light';
    try { saved = localStorage.getItem(STORAGE_KEY) || 'light'; } catch(e) {}
    applyTheme(saved);

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(saved); // re-run after DOM (options now exist)

        var pickerBtn   = document.getElementById('themePickerBtn');
        var pickerPanel = document.getElementById('themePickerPanel');

        // Open / close panel
        if (pickerBtn && pickerPanel) {
            pickerBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                pickerPanel.classList.toggle('show');
            });
        }

        // Theme option click
        document.querySelectorAll('.theme-option').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                applyTheme(btn.dataset.theme);
                pickerPanel && pickerPanel.classList.remove('show');
            });
        });

        // Close panel on outside click
        document.addEventListener('click', function() {
            pickerPanel && pickerPanel.classList.remove('show');
        });
    });
})();
