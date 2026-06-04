function userManager() {

    return {

        showAddModal: false,

        showEditModal: false,

        editId: '',

        editName: '',

        editEmail: '',

        editRole: 'Staff',

        openEditModal(user) {

            this.editId = user.id

            this.editName = user.name

            this.editEmail = user.email

            this.editRole = user.role

            this.showEditModal = true

        }

    }

}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-auto-dismiss-alert]').forEach(function (alert) {
        window.setTimeout(function () {
            alert.classList.add('user-alert-hiding');
            window.setTimeout(function () {
                alert.remove();
            }, 250);
        }, 2800);
    });

    var userModals = Array.prototype.slice.call(document.querySelectorAll('[data-user-modal]'));

    function closeUserModals() {
        userModals.forEach(function (modal) {
            modal.style.display = 'none';
            modal.setAttribute('x-cloak', '');
        });
    }

    function fillEditModal(user) {
        var modal = document.querySelector('[data-user-modal="edit"]');
        if (!modal || !user) {
            return;
        }

        var idInput = modal.querySelector('input[name="id"]');
        var nameInput = modal.querySelector('input[name="name"]');
        var emailInput = modal.querySelector('input[name="email"]');
        var roleInput = modal.querySelector('select[name="role"]');

        if (idInput) idInput.value = user.id || '';
        if (nameInput) nameInput.value = user.name || '';
        if (emailInput) emailInput.value = user.email || '';
        if (roleInput) roleInput.value = user.role || 'Staff';
    }

    function openUserModal(name, user) {
        var modal = document.querySelector('[data-user-modal="' + name + '"]');
        if (!modal) {
            return;
        }

        if (name === 'edit') {
            fillEditModal(user);
        }

        modal.removeAttribute('x-cloak');
        modal.style.display = 'flex';
    }

    closeUserModals();

    document.querySelectorAll('[data-open-user-modal]').forEach(function (button) {
        button.addEventListener('click', function () {
            var modalName = button.getAttribute('data-open-user-modal');
            var user = null;

            if (button.dataset.editUser) {
                try {
                    user = JSON.parse(button.dataset.editUser);
                } catch (_) {
                    user = null;
                }
            }

            openUserModal(modalName, user);
        });
    });

    document.querySelectorAll('[data-close-user-modal]').forEach(function (button) {
        button.addEventListener('click', closeUserModals);
    });

    userModals.forEach(function (modal) {
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeUserModals();
            }
        });
    });

    document.querySelectorAll('[data-copy-user]').forEach(function (button) {
        button.addEventListener('click', function () {
            var value = button.getAttribute('data-copy-value') || '';
            var originalTitle = button.getAttribute('title') || 'Copy invite details';

            function markCopied() {
                button.setAttribute('title', 'Copied');
                button.setAttribute('aria-label', 'Copied invite details');
                button.classList.add('user-copy-done');
                window.setTimeout(function () {
                    button.setAttribute('title', originalTitle);
                    button.setAttribute('aria-label', originalTitle);
                    button.classList.remove('user-copy-done');
                }, 1400);
            }

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(value).then(markCopied).catch(markCopied);
                return;
            }

            markCopied();
        });
    });

    var input = document.querySelector('[data-user-search]');

    if (!input) {
        return;
    }

    var rows = Array.prototype.slice.call(document.querySelectorAll('[data-user-row]'));
    var results = document.querySelector('[data-user-results]');

    function updateUrl(value) {
        if (!window.history || !window.history.replaceState) {
            return;
        }

        var url = new URL(window.location.href);
        if (value.trim() !== '') {
            url.searchParams.set('q', value.trim());
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.delete('page');
        window.history.replaceState({}, '', url.toString());
    }

    function filterUsers() {
        var value = (input.value || '').toLowerCase().trim();
        var visibleCount = 0;

        rows.forEach(function (row) {
            var haystack = row.getAttribute('data-user-search-value') || '';
            var visible = !value || haystack.indexOf(value) !== -1;
            row.hidden = !visible;
            if (visible) {
                visibleCount += 1;
            }
        });

        if (results) {
            results.textContent = String(visibleCount);
        }
        updateUrl(value);
    }

    if (input.form) {
        input.form.addEventListener('submit', function (event) {
            event.preventDefault();
            filterUsers();
        });
    }

    input.addEventListener('input', filterUsers);
    input.addEventListener('search', filterUsers);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            filterUsers();
        }
    });

    filterUsers();
});
