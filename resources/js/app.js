const authCard = document.querySelector('[data-auth-card]');

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
