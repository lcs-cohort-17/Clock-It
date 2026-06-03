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
