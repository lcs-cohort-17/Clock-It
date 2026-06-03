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

    if (!input || !input.form) {
        return;
    }

    var lastValue = input.value || '';

    function submitSearch() {
        var value = input.value || '';

        if (value === lastValue) {
            return;
        }

        lastValue = value;

        var url = new URL(input.form.action || window.location.href, window.location.origin);
        if (value.trim() !== '') {
            url.searchParams.set('q', value.trim());
        } else {
            url.searchParams.delete('q');
        }
        url.searchParams.delete('page');

        window.location.assign(url.toString());
    }

    input.addEventListener('blur', submitSearch);

    input.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') {
            event.preventDefault();
            submitSearch();
            input.blur();
        }
    });
});
