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
