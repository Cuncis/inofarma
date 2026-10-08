{{-- Keeps the admin sidebar from feeling reloaded during SPA navigation: the
     clicked item turns active at once, the sidebar keeps its scroll position,
     and a parent that stays open does not replay its opening animation. --}}
<script data-navigate-once>
    (() => {
        let scrollTop = 0;
        let openParents = [];

        const sidebarNav = () => document.querySelector('.fi-sidebar-nav');
        const labelOf = (item) => item.querySelector(':scope > .fi-sidebar-item-btn .fi-sidebar-item-label')?.textContent.trim();
        const openParentLabels = () => [...document.querySelectorAll('.fi-sidebar-item > .fi-sidebar-sub-group-items')]
            .map((list) => labelOf(list.parentElement));

        document.addEventListener('click', (event) => {
            const link = event.target.closest?.('.fi-sidebar-item-btn[href]');

            if (! link || event.defaultPrevented || event.metaKey || event.ctrlKey || event.shiftKey || event.button !== 0 || link.target === '_blank') {
                return;
            }

            document.querySelectorAll('.fi-sidebar-item.fi-active').forEach((item) => item.classList.remove('fi-active'));
            link.closest('.fi-sidebar-item')?.classList.add('fi-active');
        }, true);

        document.addEventListener('livewire:navigate', () => {
            scrollTop = sidebarNav()?.scrollTop ?? 0;
            openParents = openParentLabels();
        });

        document.addEventListener('livewire:navigated', () => {
            const nav = sidebarNav();

            if (nav) {
                nav.scrollTop = scrollTop;
            }

            document.querySelectorAll('.fi-sidebar-item > .fi-sidebar-sub-group-items').forEach((list) => {
                if (openParents.includes(labelOf(list.parentElement))) {
                    list.setAttribute('data-static', '');
                }
            });
        });
    })();
</script>
