        </main><!-- /.emp-content -->
    </div><!-- /.emp-main -->
</div><!-- /.emp-layout -->

<!-- Toast container -->
<div class="emp-toast-container" id="empToasts"></div>

<!-- Scripts -->
<script src="<?= SCRIPTS_URL ?>/main.js"></script>
<script src="<?= SCRIPTS_URL ?>/empleado.js"></script>
<?php if (!empty($extraJs)): ?>
    <?php foreach ((array)$extraJs as $js): ?>
        <script src="<?= SCRIPTS_URL ?>/<?= e($js) ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>
<script>lucide.createIcons();</script>
<script>
// Sidebar toggle
document.getElementById('empToggle')?.addEventListener('click', function () {
    const sidebar = document.getElementById('empSidebar');
    const overlay = document.getElementById('empOverlay');
    sidebar.classList.toggle('open');
    overlay.classList.toggle('active');
});
document.getElementById('empOverlay')?.addEventListener('click', function () {
    document.getElementById('empSidebar')?.classList.remove('open');
    this.classList.remove('active');
});
// Auto-dismiss alerts
setTimeout(function () {
    document.querySelectorAll('.emp-alert').forEach(function (a) {
        a.style.opacity = '0';
        a.style.transition = 'opacity .3s';
        setTimeout(function () { a.remove(); }, 300);
    });
}, 5000);
</script>
</body>
</html>
