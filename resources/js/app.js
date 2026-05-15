import './bootstrap';
import { initRealtimeMessages } from './messages';

const authCard = document.querySelector('[data-auth-card]');
const accountMenus = Array.from(document.querySelectorAll('[data-account-menu]'));
const fileInputs = Array.from(document.querySelectorAll('[data-file-input]'));

window.previewAvatar = (input) => {
    const previewSelector = input.dataset.previewTarget;
    const initialsSelector = input.dataset.previewInitials;
    const preview = previewSelector ? document.querySelector(previewSelector) : null;
    const initials = initialsSelector ? document.querySelector(initialsSelector) : null;
    const file = input.files?.[0];

    if (!file || !preview) {
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        if (preview instanceof HTMLImageElement) {
            preview.src = event.target?.result || '';
            preview.hidden = false;
            preview.style.display = 'block';
        } else {
            preview.style.backgroundImage = `url('${event.target?.result}')`;
        }

        if (initials) {
            initials.hidden = true;
        }
    };
    reader.readAsDataURL(file);
};

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
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            setMode(trigger.dataset.authTrigger);
        });
    });

    links.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            setMode(link.dataset.authLink);
        });
    });
}

fileInputs.forEach((input) => {
    input.addEventListener('change', () => {
        const wrapper = input.closest('.profile-upload-control');
        const fileName = wrapper?.querySelector('[data-file-name]') || document.getElementById('avatar-filename-label');
        const previewSelector = input.dataset.previewTarget;
        const preview = previewSelector ? document.querySelector(previewSelector) : null;
        const initialsSelector = input.dataset.previewInitials;
        const initials = initialsSelector ? document.querySelector(initialsSelector) : null;
        const file = input.files?.[0];

        if (!fileName) {
            return;
        }

        fileName.textContent = file?.name || 'No image selected';
        fileName.style.color = file ? '#3d2b1f' : '';

        if (file && preview) {
            const reader = new FileReader();
            reader.onload = (event) => {
                if (preview instanceof HTMLImageElement) {
                    preview.src = event.target?.result || '';
                    preview.hidden = false;
                    preview.style.display = 'block';
                } else {
                    preview.style.backgroundImage = `url('${event.target?.result}')`;
                }

                if (initials) {
                    initials.style.display = 'none';
                }

                const removeBtn = document.getElementById('remove-avatar-btn');
                if (removeBtn) {
                    removeBtn.style.display = 'none';
                }

                const removeInput = document.getElementById('remove-avatar-input');
                if (removeInput) {
                    removeInput.value = '0';
                }
            };
            reader.readAsDataURL(file);
        }
    });
});

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
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('deleteListingModal');
    const form = document.getElementById('deleteListingForm');
    const openButtons = document.querySelectorAll('.js-open-delete-modal');
    const closeButtons = document.querySelectorAll('.js-close-delete-modal');

    if (!modal || !form) {
        return;
    }

    openButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const deleteUrl = button.dataset.deleteUrl;

            if (!deleteUrl) {
                return;
            }

            form.setAttribute('action', deleteUrl);
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        });
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', () => {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            form.removeAttribute('action');
        });
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && modal.classList.contains('is-open')) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            form.removeAttribute('action');
        }
    });
});
