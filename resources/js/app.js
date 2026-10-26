//
const menu = document.querySelector('.menu-toggle');
menu?.addEventListener('click', () => {
    const expanded = menu.getAttribute('aria-expanded') !== 'true';
    menu.setAttribute('aria-expanded', String(expanded));
    document.getElementById('sidebar-menu').classList.toggle('is-open', expanded);
});

document.getElementById('copy-referral')?.addEventListener('click', async () => {
    const input = document.getElementById('referral-link');
    const status = document.getElementById('copy-status');
    try {
        await navigator.clipboard.writeText(input.value);
        status.textContent = 'Referral link copied.';
    } catch {
        input.focus();
        input.select();
        status.textContent = 'Select and copy the link using your device’s copy command.';
    }
});
