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

(function () {
    var toggle = document.querySelector('[data-admin-sidebar-toggle]');
    var sidebar = document.getElementById('admin-sidebar');
    var backdrop = document.querySelector('[data-admin-sidebar-backdrop]');
    var closeButton = document.querySelector('[data-admin-sidebar-close]');
    if (!toggle || !sidebar || !backdrop) return;

    var desktop = window.matchMedia('(min-width: 901px)');

    function setOpen(open) {
        document.body.classList.toggle('admin-nav-open', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        toggle.setAttribute('aria-label', open ? 'Close navigation' : 'Open navigation');
    }

    toggle.addEventListener('click', function () {
        setOpen(!document.body.classList.contains('admin-nav-open'));
    });

    backdrop.addEventListener('click', function () {
        setOpen(false);
    });

    if (closeButton) {
        closeButton.addEventListener('click', function () {
            setOpen(false);
            toggle.focus();
        });
    }

    sidebar.querySelectorAll('a').forEach(function (link) {
        link.addEventListener('click', function () {
            setOpen(false);
        });
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' || event.key === 'Esc') {
            setOpen(false);
        }
    });

    if (desktop.addEventListener) {
        desktop.addEventListener('change', function (event) {
            if (event.matches) setOpen(false);
        });
    } else if (desktop.addListener) {
        desktop.addListener(function (event) {
            if (event.matches) setOpen(false);
        });
    }
})();

(function () {
    var toolbar = document.querySelector('.admin-catalog-toolbar');
    if (!toolbar) return;

    var content = toolbar.closest('.admin-content');
    if (!content) return;

    var originTop = 0;
    var basePadding = 0;
    var stuck = false;

    function documentTop(element) {
        var top = 0;
        while (element) {
            top += element.offsetTop;
            element = element.offsetParent;
        }
        return top;
    }

    function measure() {
        originTop = documentTop(toolbar);
        basePadding = parseFloat(window.getComputedStyle(content).paddingTop) || 0;
    }

    function pin() {
        var rect = toolbar.getBoundingClientRect();
        var top = parseFloat(window.getComputedStyle(toolbar).top) || 0;

        toolbar.style.width = rect.width + 'px';
        toolbar.style.left = rect.left + 'px';
        toolbar.style.top = top + 'px';
        toolbar.classList.add('is-stuck');
        content.style.paddingTop = (basePadding + rect.height) + 'px';
        stuck = true;
    }

    function unpin() {
        toolbar.classList.remove('is-stuck');
        toolbar.style.width = '';
        toolbar.style.left = '';
        toolbar.style.top = '';
        content.style.paddingTop = '';
        stuck = false;
        basePadding = parseFloat(window.getComputedStyle(content).paddingTop) || 0;
    }

    function update() {
        var scrollY = window.pageYOffset || document.documentElement.scrollTop || 0;
        if (!stuck && scrollY > originTop) {
            pin();
        } else if (stuck && scrollY <= originTop) {
            unpin();
        }
    }

    window.addEventListener('scroll', update, { passive: true });
    window.addEventListener('resize', function () {
        unpin();
        measure();
        update();
    });

    measure();
    update();
})();
</script>

<?php require __DIR__ . '/foot_scripts.php'; ?>
</body>
</html>
