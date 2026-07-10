// sidebar.js — accordion nav, mobile toggle
document.addEventListener('DOMContentLoaded', () => {
    // Accordion groups
    document.querySelectorAll('.nav-group-header').forEach(header => {
        header.addEventListener('click', () => {
            const group = header.parentElement;
            const isOpen = group.classList.contains('open');
            // Close all
            document.querySelectorAll('.nav-group').forEach(g => g.classList.remove('open'));
            if (!isOpen) group.classList.add('open');
        });
    });

    // Mobile sidebar toggle
    const menuToggle    = document.getElementById('menuToggle');
    const sidebar       = document.getElementById('sidebar');
    const sidebarClose  = document.getElementById('sidebarClose');
    const sidebarOverlay = document.getElementById('sidebarOverlay');

    function openSidebar() {
        sidebar.classList.add('mobile-open');
        sidebarOverlay.classList.add('show');
    }
    function closeSidebar() {
        sidebar.classList.remove('mobile-open');
        sidebarOverlay.classList.remove('show');
    }

    menuToggle?.addEventListener('click', openSidebar);
    sidebarClose?.addEventListener('click', closeSidebar);
    sidebarOverlay?.addEventListener('click', closeSidebar);

    // Notification dropdown
    const notifBtn      = document.getElementById('notifBtn');
    const notifDropdown = document.getElementById('notifDropdown');
    notifBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        notifDropdown?.classList.toggle('show');
        document.getElementById('userDropdown')?.classList.remove('show');
    });

    // User dropdown
    const userBtn      = document.getElementById('userBtn');
    const userDropdown = document.getElementById('userDropdown');
    userBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        userDropdown?.classList.toggle('show');
        notifDropdown?.classList.remove('show');
    });

    // Close dropdowns on outside click
    document.addEventListener('click', () => {
        notifDropdown?.classList.remove('show');
        userDropdown?.classList.remove('show');
    });
});