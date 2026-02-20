</div><!-- END .page -->
</div><!-- END .main-wrap -->

<script>
// ── Shared modal helpers (available on all pages) ──
function openModal(id)  { document.getElementById(id).classList.add('open'); }
function closeModal(id) { document.getElementById(id).classList.remove('open'); }

document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) closeModal(this.id);
    });
});

document.addEventListener('keydown', e => {
    if (e.key === 'Escape')
        document.querySelectorAll('.modal-overlay.open')
                .forEach(m => m.classList.remove('open'));
});
</script>
</body>
</html>