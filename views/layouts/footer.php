<?php
// views/layouts/footer.php
?>
    </div> <!-- end app-main -->
</div> <!-- end app-wrapper -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('appSidebar');
    const main = document.querySelector('.app-main');
    const toggleBtn = document.getElementById('sidebarToggle');
    const notifBtn = document.getElementById('notifBtn');
    const notifMenu = document.getElementById('notifMenu');

    const backdrop = document.getElementById('sidebarBackdrop');
    const closeBtn = document.getElementById('sidebarCloseBtn');

    // Sidebar collapse state (desktop only)
    if (window.innerWidth <= 992) {
        if (sidebar) sidebar.classList.remove('collapsed');
        if (main) main.classList.remove('expanded');
    } else if (localStorage.getItem('sidebar_collapsed') === 'true' && sidebar && main) {
        sidebar.classList.add('collapsed');
        main.classList.add('expanded');
    }

    window.addEventListener('resize', function() {
        if (window.innerWidth <= 992) {
            if (sidebar) sidebar.classList.remove('collapsed');
            if (main) main.classList.remove('expanded');
        } else {
            closeMobileSidebar();
            if (localStorage.getItem('sidebar_collapsed') === 'true' && sidebar && main) {
                sidebar.classList.add('collapsed');
                main.classList.add('expanded');
            }
        }
    });

    function openMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.add('show-mobile');
        if (backdrop) backdrop.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMobileSidebar() {
        if (!sidebar) return;
        sidebar.classList.remove('show-mobile');
        if (backdrop) backdrop.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (toggleBtn && sidebar) {
        toggleBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            if (window.innerWidth <= 992) {
                if (sidebar.classList.contains('show-mobile')) {
                    closeMobileSidebar();
                } else {
                    openMobileSidebar();
                }
            } else if (main) {
                sidebar.classList.toggle('collapsed');
                main.classList.toggle('expanded');
                localStorage.setItem('sidebar_collapsed', sidebar.classList.contains('collapsed'));
            }
        });
    }

    if (closeBtn) closeBtn.addEventListener('click', closeMobileSidebar);
    if (backdrop) backdrop.addEventListener('click', closeMobileSidebar);

    // Notification dropdown toggle
    if (notifBtn && notifMenu) {
        notifBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            notifMenu.style.display = notifMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', function(e) {
            if (!notifMenu.contains(e.target) && e.target !== notifBtn) {
                notifMenu.style.display = 'none';
            }
        });
    }
});
</script>
<script src="<?= $baseUrl ?>/public/assets/js/pwa.js"></script>
</body>
</html>
