    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Bootstrap JSON, Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        // Dark Mode Toggle Logic
        document.addEventListener('DOMContentLoaded', () => {
            const getStoredTheme = () => localStorage.getItem('theme') || 'light';
            const setTheme = theme => {
                document.documentElement.setAttribute('data-bs-theme', theme);
                document.body.setAttribute('data-bs-theme', theme);
                
                // Update icons if exists
                const themeIcon = document.getElementById('theme-icon');
                if(themeIcon) {
                    if(theme === 'dark') {
                        themeIcon.classList.remove('fa-moon');
                        themeIcon.classList.add('fa-sun');
                    } else {
                        themeIcon.classList.remove('fa-sun');
                        themeIcon.classList.add('fa-moon');
                    }
                }
            }

            setTheme(getStoredTheme());

            const btnThemeToggle = document.getElementById('theme-toggle');
            if(btnThemeToggle) {
                btnThemeToggle.addEventListener('click', () => {
                    const currentTheme = document.documentElement.getAttribute('data-bs-theme');
                    const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                    localStorage.setItem('theme', newTheme);
                    setTheme(newTheme);
                });
            }

            // DataTables Initialization
            if ($('.datatable').length) {
                $('.datatable').DataTable({
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/id.json',
                    }
                });
            }

            // Sidebar Toggle
            const sidebarBtn = document.getElementById('sidebarCollapse');
            const sidebarBtnClose = document.getElementById('sidebarCollapseBtn');
            const sidebarOverlay = document.getElementById('sidebarOverlay');
            const sidebar = document.getElementById('sidebar');
            
            if(sidebarBtn) {
                sidebarBtn.addEventListener('click', () => {
                    sidebar.classList.toggle('active');
                });
            }
            if(sidebarBtnClose) {
                sidebarBtnClose.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                });
            }
            if(sidebarOverlay) {
                sidebarOverlay.addEventListener('click', () => {
                    sidebar.classList.remove('active');
                });
            }
        });
    </script>
</body>
</html>
