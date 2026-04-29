import './bootstrap';
import { initRealtimeMessages } from './messages';

const authCard = document.querySelector('[data-auth-card]');
const accountMenus = Array.from(document.querySelectorAll('[data-account-menu]'));

if (authCard) {
    const triggers = Array.from(authCard.querySelectorAll('[data-auth-trigger]'));
    const links = Array.from(authCard.querySelectorAll('[data-auth-link]'));
    const panes = Array.from(authCard.querySelectorAll('[data-auth-pane]'));

    const setMode = (mode) => {
        triggers.forEach((trigger) => {
            const active = trigger.dataset.authTrigger === mode;
            trigger.classList.toggle('is-active', active);
            trigger.setAttribute('aria-selected', active ? 'true' : 'false');
        });

        panes.forEach((pane) => {
            const active = pane.dataset.authPane === mode;
            pane.classList.toggle('is-active', active);
            pane.hidden = !active;
        });
    };

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', () => setMode(trigger.dataset.authTrigger));
    });

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            setMode(link.dataset.authLink);
        });
    });
}

if (accountMenus.length > 0) {
    const closeMenu = (menu) => {
        const toggle = menu.querySelector('[data-account-menu-toggle]');
        const popover = menu.querySelector('[data-account-menu-popover]');

        if (!toggle || !popover) {
            return;
        }

        toggle.setAttribute('aria-expanded', 'false');
        menu.classList.remove('is-open');
        popover.hidden = true;
    };

    const openMenu = (menu) => {
        const toggle = menu.querySelector('[data-account-menu-toggle]');
        const popover = menu.querySelector('[data-account-menu-popover]');

        if (!toggle || !popover) {
            return;
        }

        accountMenus.forEach((otherMenu) => {
            if (otherMenu !== menu) {
                closeMenu(otherMenu);
            }
        });

        toggle.setAttribute('aria-expanded', 'true');
        menu.classList.add('is-open');
        popover.hidden = false;
    };

    accountMenus.forEach((menu) => {
        const toggle = menu.querySelector('[data-account-menu-toggle]');

        toggle?.addEventListener('click', () => {
            if (menu.classList.contains('is-open')) {
                closeMenu(menu);
                return;
            }

            openMenu(menu);
        });
    });

    document.addEventListener('click', (event) => {
        accountMenus.forEach((menu) => {
            if (!menu.contains(event.target)) {
                closeMenu(menu);
            }
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            accountMenus.forEach((menu) => closeMenu(menu));
        }
    });
}

initRealtimeMessages();
