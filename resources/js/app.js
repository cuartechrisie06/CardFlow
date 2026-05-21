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
    const passwordToggles = Array.from(authCard.querySelectorAll('[data-password-toggle]'));
    const registerPassword = authCard.querySelector('#register-password');
    const registerUsername = authCard.querySelector('#register-username');
    const usernameCheckUrl = authCard.dataset.usernameCheckUrl;

    const setMode = (mode) => {
        triggers.forEach((trigger) => {
            const active = trigger.dataset.authTrigger === mode;
            trigger.classList.toggle('is-active', active);
            trigger.classList.toggle('active', active);
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

    passwordToggles.forEach((button) => {
        button.addEventListener('click', () => {
            const field = document.getElementById(button.dataset.passwordTarget);

            if (!field) {
                return;
            }

            const shouldShow = field.type === 'password';
            field.type = shouldShow ? 'text' : 'password';
            button.textContent = shouldShow ? 'Hide' : 'Show';
        });
    });

    if (registerPassword) {
        registerPassword.addEventListener('input', function () {
            const val = this.value;
            const bar = document.getElementById('password-strength-bar');
            const fill = document.getElementById('strength-fill');
            const label = document.getElementById('strength-label');

            if (!bar || !fill || !label) {
                return;
            }

            if (!val) {
                bar.hidden = true;
                label.hidden = true;
                return;
            }

            bar.hidden = false;
            label.hidden = false;

            let score = 0;
            if (val.length >= 8) score++;
            if (val.length >= 12) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const levels = [
                { pct: '20%', color: '#c0392b', text: 'Too weak' },
                { pct: '40%', color: '#e67e22', text: 'Weak' },
                { pct: '60%', color: '#f39c12', text: 'Fair' },
                { pct: '80%', color: '#27ae60', text: 'Strong' },
                { pct: '100%', color: '#2d6a4f', text: 'Very strong' },
            ];
            const level = levels[Math.min(score, 4)];

            fill.style.width = level.pct;
            fill.style.background = level.color;
            label.textContent = level.text;
            label.style.color = level.color;
        });
    }

    if (registerUsername && usernameCheckUrl) {
        let usernameTimer;

        registerUsername.addEventListener('input', function () {
            const status = document.getElementById('username-status');
            const val = this.value.trim();

            if (!status) {
                return;
            }

            clearTimeout(usernameTimer);

            if (val.length < 3) {
                status.hidden = true;
                return;
            }

            status.hidden = false;
            status.textContent = 'Checking...';
            status.style.color = '#b09070';

            usernameTimer = setTimeout(() => {
                const url = new URL(usernameCheckUrl, window.location.origin);
                url.searchParams.set('username', val);

                fetch(url)
                    .then((response) => response.json())
                    .then((data) => {
                        if (data.available) {
                            status.textContent = `@${val} is available`;
                            status.style.color = '#2d6a4f';
                        } else {
                            status.textContent = `@${val} is already taken`;
                            status.style.color = '#c0392b';
                        }
                    })
                    .catch(() => {
                        status.textContent = 'Could not check username right now.';
                        status.style.color = '#c0392b';
                    });
            }, 500);
        });
    }
}

const openModal = (id) => {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
};

const closeModal = (id) => {
    const modal = document.getElementById(id);

    if (!modal) {
        return;
    }

    modal.classList.add('hidden');
    document.body.style.overflow = '';
};

document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', (event) => {
        event.preventDefault();
        openModal(trigger.dataset.modalOpen);
    });
});

document.querySelectorAll('[data-modal-close]').forEach((trigger) => {
    trigger.addEventListener('click', () => closeModal(trigger.dataset.modalClose));
});

document.querySelectorAll('[data-modal-accept]').forEach((trigger) => {
    trigger.addEventListener('click', () => {
        closeModal(trigger.dataset.modalAccept);

        const terms = document.getElementById('agree-terms');
        if (terms) {
            terms.checked = true;
        }
    });
});

document.querySelectorAll('[data-modal-backdrop]').forEach((modal) => {
    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal(modal.id);
        }
    });
});

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

import('./bootstrap')
    .then(() => import('./messages'))
    .then(({ initRealtimeMessages }) => initRealtimeMessages())
    .catch((error) => {
        console.warn('Realtime features could not be started.', error);
    });

const toast = document.getElementById('toast-success');
if (toast) {
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.5s';
        setTimeout(() => toast.remove(), 500);
    }, 3000);
}

const announcement = document.getElementById('announcement-banner');
if (announcement) {
    if (localStorage.getItem('cf_banner_dismissed')) {
        announcement.style.display = 'none';
    }

    announcement.querySelector('[data-announcement-dismiss]')?.addEventListener('click', () => {
        announcement.style.display = 'none';
        localStorage.setItem('cf_banner_dismissed', '1');
    });
}

window.addEventListener('pageshow', (event) => {
    if (event.persisted) {
        window.location.href = '/login';
    }
});

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
