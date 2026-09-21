<?php
/**
 * Layout Footer — Cierre de HTML, scripts JS globales
 */
?>
        </main><!-- /.app-content -->
    </div><!-- /.app-main -->
</div><!-- /.app-layout -->

<!-- Scripts -->
<script src="<?= SCRIPTS_URL ?>/main.js"></script>
<?php if ($user && $user['rol'] === 'admin'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const button = document.getElementById('stockNotificationButton');
    const panel = document.getElementById('stockNotificationPanel');
    const wrapper = document.getElementById('stockNotification');
    if (!button || !panel || !wrapper) return;

    button.addEventListener('click', function (event) {
        event.stopPropagation();
        const isOpen = !panel.hasAttribute('hidden');
        if (isOpen) panel.setAttribute('hidden', '');
        else panel.removeAttribute('hidden');
        button.setAttribute('aria-expanded', String(!isOpen));
    });

    document.addEventListener('click', function (event) {
        if (!wrapper.contains(event.target)) {
            panel.setAttribute('hidden', '');
            button.setAttribute('aria-expanded', 'false');
        }
    });
});
</script>
<?php endif; ?>
<?php if (isset($extraJs)): ?>
    <?php foreach ((array)$extraJs as $js): ?>
        <script src="<?= SCRIPTS_URL ?>/<?= $js ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Initialize Lucide Icons -->
<script>lucide.createIcons();</script>
</body>
</html>
