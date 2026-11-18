        </div>
    </div>
</div>

<script>
(function () {
    var nav = document.querySelector('[data-admin-accordion]');
    if (!nav) return;

    nav.querySelectorAll('.admin-nav-parent').forEach(function (button) {
        button.addEventListener('click', function () {
            var group = button.closest('.admin-nav-group');
            if (!group) return;

            var willOpen = !group.classList.contains('is-open');
            group.classList.toggle('is-open', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
})();
</script>

</body>
</html>
