```php
<?php
session_start();

/* -----------------------------
   INITIAL USERS
------------------------------*/
if (!isset($_SESSION['users'])) {

    $_SESSION['users'] = [

        [
            "id" => "1",
            "name" => "Priya Singh",
            "email" => "admin@clockit.app",
            "employeeId" => "A-001",
            "role" => "Admin",
            "status" => "Active",
            "password" => "Temp1234"
        ],

        [
            "id" => "2",
            "name" => "David Okafor",
            "email" => "david@clockit.app",
            "employeeId" => "A-002",
            "role" => "Admin",
            "status" => "Active",
            "password" => "Temp5678"
        ],

        [
            "id" => "3",
            "name" => "David Naidoo",
            "email" => "david.naidoo@clockit.app",
            "employeeId" => "A-003",
            "role" => "Admin",
            "status" => "Active",
            "password" => "Temp9012"
        ],

        [
            "id" => "4",
            "name" => "Sarah Mthembu",
            "email" => "sarah@clockit.app",
            "employeeId" => "S-101",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp3456"
        ],

        [
            "id" => "5",
            "name" => "John Adams",
            "email" => "john@clockit.app",
            "employeeId" => "S-102",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp7890"
        ],

        [
            "id" => "6",
            "name" => "Mary Chen",
            "email" => "mary@clockit.app",
            "employeeId" => "S-103",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp1122"
        ]
        ,[
            "id" => "7",
            "name" => "Mia Patel",
            "email" => "mia@clockit.app",
            "employeeId" => "S-104",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp3344"
        ],
        [
            "id" => "8",
            "name" => "Ethan Brown",
            "email" => "ethan@clockit.app",
            "employeeId" => "S-105",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp5566"
        ],
        [
            "id" => "9",
            "name" => "Aisha Khan",
            "email" => "aisha@clockit.app",
            "employeeId" => "S-106",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp7788"
        ],
        [
            "id" => "10",
            "name" => "Noah Williams",
            "email" => "noah@clockit.app",
            "employeeId" => "A-004",
            "role" => "Admin",
            "status" => "Active",
            "password" => "Temp9900"
        ],
        [
            "id" => "11",
            "name" => "Olivia Rodriguez",
            "email" => "olivia@clockit.app",
            "employeeId" => "S-107",
            "role" => "Staff",
            "status" => "Active",
            "password" => "Temp2233"
        ],
        [
            "id" => "12",
            "name" => "Lukas Müller",
            "email" => "lukas@clockit.app",
            "employeeId" => "A-005",
            "role" => "Admin",
            "status" => "Active",
            "password" => "Temp4455"
        ]
    ];
}

$users = &$_SESSION['users'];

/* -----------------------------
   HELPERS
------------------------------*/

function initials($name)
{
    $parts = explode(" ", $name);

    $initials = "";

    foreach ($parts as $part) {
        $initials .= strtoupper($part[0]);
    }

    return substr($initials, 0, 2);
}

function generateEmployeeId($role, $users)
{
    $prefix = $role === "Admin" ? "A" : "S";

    $existing = array_filter($users, function ($u) use ($role) {
        return $u['role'] === $role;
    });

    $baseNumber = $role === "Admin" ? 1 : 101;

    return $prefix . "-" . str_pad(
        $baseNumber + count($existing),
        3,
        "0",
        STR_PAD_LEFT
    );
}

function generatePassword($length = 10)
{
    $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    $password = "";

    for ($i = 0; $i < $length; $i++) {

        $password .= $chars[rand(0, strlen($chars) - 1)];
    }

    return $password;
}

/* -----------------------------
   ADD USER
------------------------------*/

if (isset($_POST['add_user'])) {

    $password = generatePassword();

    $users[] = [

        "id" => uniqid(),

        "name" => $_POST['name'],

        "email" => $_POST['email'],

        "employeeId" => generateEmployeeId(
            $_POST['role'],
            $users
        ),

        "role" => $_POST['role'],

        "status" => "Active",

        "password" => $password
    ];

    $_SESSION['generated_password'] = $password;

    header("Location: /");
    exit;
}

/* -----------------------------
   EDIT USER
------------------------------*/

if (isset($_POST['edit_user'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $u['name'] = $_POST['name'];

            $u['email'] = $_POST['email'];

            $u['role'] = $_POST['role'];
        }
    }

    header("Location: /");
    exit;
}

/* -----------------------------
   TOGGLE STATUS
------------------------------*/

if (isset($_POST['toggle_status'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $u['status'] =
                $u['status'] === "Active"
                ? "Inactive"
                : "Active";
        }
    }

    header("Location: /");
    exit;
}

/* -----------------------------
   RESET PASSWORD
------------------------------*/

if (isset($_POST['reset_password'])) {

    foreach ($users as &$u) {

        if ($u['id'] === $_POST['id']) {

            $newPassword = generatePassword();

            $u['password'] = $newPassword;

            $_SESSION['generated_password'] = $newPassword;
        }
    }

    header("Location: /");
    exit;
}

/* -----------------------------
   SEARCH
------------------------------*/

$query = strtolower($_GET['q'] ?? '');

$filtered = array_filter($users, function ($u) use ($query) {

    return !$query ||

        str_contains(
            strtolower($u['name']),
            $query
        ) ||

        str_contains(
            strtolower($u['email']),
            $query
        ) ||

        str_contains(
            strtolower($u['employeeId']),
            $query
        );
});

/* -----------------------------
   PAGINATION
------------------------------*/

$page = $_GET['page'] ?? 1;

$pageSize = 5;

$totalPages = max(
    1,
    ceil(count($filtered) / $pageSize)
);

$start = ($page - 1) * $pageSize;

$pageData = array_slice(
    $filtered,
    $start,
    $pageSize
);

?>

<div class="container-fluid py-5 px-4"
     x-data="userManager()">

    <div class="page-card p-4 p-xl-5 shadow-sm">

        <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">

        <div>

            <h1 class="main-title">
                User Management
            </h1>

            <p class="subtitle">
                Add, edit, or disable accounts. Self-registration is disabled.
            </p>

        </div>

        <button class="btn btn-main"
                @click="showAddModal = true">

            + Add User

        </button>

    </div>

    <!-- SEARCH -->

    <form method="GET"
          class="mb-4">

        <div class="search-box position-relative w-100">

            <input type="text"
                   name="q"
                   value="<?= htmlspecialchars($query) ?>"
                   placeholder="Search by name, email, or employee ID"
                   class="form-control search-input ps-5">

        </div>

    </form>

    <!-- TABLE -->

    <div class="table-wrapper">

        <table class="table align-middle mb-0">

            <thead>

            <tr>

                <th>Name</th>

                <th>Email</th>

                <th>Employee ID</th>

                <th>Role</th>

                <th>Status</th>

                <th class="text-end">
                    Actions
                </th>

            </tr>

            </thead>

            <tbody>

            <?php foreach ($pageData as $u): ?>

                <tr>

                    <td>

                        <div class="d-flex align-items-center gap-3">

                            <div class="avatar">

                                <?= initials($u['name']) ?>

                            </div>

                            <div class="fw-semibold">

                                <?= $u['name'] ?>

                            </div>

                        </div>

                    </td>

                    <td><?= $u['email'] ?></td>

                    <td><?= $u['employeeId'] ?></td>

                    <td>

                        <?php if ($u['role'] === 'Admin'): ?>

                            <span class="badge-admin">
                                Admin
                            </span>

                        <?php else: ?>

                            <span class="badge-staff">
                                Staff
                            </span>

                        <?php endif; ?>

                    </td>

                    <td>

                        <span class="status <?= strtolower($u['status']) ?>">

                            <?= $u['status'] ?>

                        </span>

                    </td>


                    <td class="text-end">

                        <!-- EDIT -->

                        <button class="btn btn-sm btn-light"

                                @click="
                                showEditModal = true;
                                editId = '<?= $u['id'] ?>';
                                editName = '<?= $u['name'] ?>';
                                editEmail = '<?= $u['email'] ?>';
                                editRole = '<?= $u['role'] ?>';
                                ">

                            <i class="bi bi-pencil" aria-hidden="true"></i>

                        </button>

                        <!-- RESET PASSWORD -->

                        <form method="POST"
                              class="d-inline">

                            <input type="hidden"
                                   name="id"
                                   value="<?= $u['id'] ?>">

                            <button name="reset_password"
                                    class="btn btn-sm btn-light">

                                <i class="bi bi-key" aria-hidden="true"></i>

                            </button>

                        </form>

                        <!-- TOGGLE -->

                        <form method="POST"
                              class="d-inline">

                            <input type="hidden"
                                   name="id"
                                   value="<?= $u['id'] ?>">

                            <button name="toggle_status"
                                    class="btn btn-sm btn-outline-danger">

                                <i class="bi bi-slash-circle" aria-hidden="true"></i>

                            </button>

                        </form>

                    </td>

                </tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <!-- PAGINATION -->

    <div class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">

        <div class="text-muted">

            Showing <?= count($pageData) ?> of <?= count($filtered) ?> employees

        </div>

        <div class="d-flex align-items-center gap-2">

            <a href="?page=<?= max(1, $page - 1) ?>&q=<?= $query ?>"
               class="btn btn-outline-secondary <?= $page <= 1 ? 'disabled' : '' ?>">
                Previous
            </a>

            <span class="text-muted px-2">
                Page <?= $page ?> of <?= $totalPages ?>
            </span>

            <a href="?page=<?= min($totalPages, $page + 1) ?>&q=<?= $query ?>"
               class="btn btn-outline-secondary <?= $page >= $totalPages ? 'disabled' : '' ?>">
                Next
            </a>

        </div>

    </div>

</div>

    <!-- ADD MODAL -->

    <div class="modal-overlay"
         x-show="showAddModal">

        <div class="modal-box">

            <div class="d-flex justify-content-between mb-4">

                <h3>Add User</h3>

                <button class="btn-close"
                        @click="showAddModal = false">
                </button>

            </div>

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input type="text"
                           name="name"
                           class="form-control"
                           required>

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           class="form-control"
                           required>

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Role
                    </label>

                    <select name="role"
                            class="form-select">

                        <option>Staff</option>

                        <option>Admin</option>

                    </select>

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <button type="button"
                            class="btn btn-outline-secondary"
                            @click="showAddModal = false">

                        Cancel

                    </button>

                    <button name="add_user"
                            class="btn btn-main">

                        Save User

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- EDIT MODAL -->

    <div class="modal-overlay"
         x-show="showEditModal">

        <div class="modal-box">

            <div class="d-flex justify-content-between mb-4">

                <h3>Edit User</h3>

                <button class="btn-close"
                        @click="showEditModal = false">
                </button>

            </div>

            <form method="POST">

                <input type="hidden"
                       name="id"
                       x-model="editId">

                <div class="mb-3">

                    <label class="form-label">
                        Full Name
                    </label>

                    <input type="text"
                           name="name"
                           x-model="editName"
                           class="form-control">

                </div>

                <div class="mb-3">

                    <label class="form-label">
                        Email
                    </label>

                    <input type="email"
                           name="email"
                           x-model="editEmail"
                           class="form-control">

                </div>

                <div class="mb-4">

                    <label class="form-label">
                        Role
                    </label>

                    <select name="role"
                            x-model="editRole"
                            class="form-select">

                        <option>Staff</option>

                        <option>Admin</option>

                    </select>

                </div>

                <div class="d-flex justify-content-end gap-2">

                    <button type="button"
                            class="btn btn-outline-secondary"
                            @click="showEditModal = false">

                        Cancel

                    </button>

                    <button name="edit_user"
                            class="btn btn-main">

                        Save Changes

                    </button>

                </div>

            </form>

        </div>

    </div>

    <!-- PASSWORD MODAL -->

    <?php if (isset($_SESSION['generated_password'])): ?>

        <div class="password-modal">

            <div class="password-box">

                <h3 class="mb-3">
                    Temporary Password
                </h3>

                <p class="text-muted">
                    Share this password securely.
                </p>

                <div class="password-display">

                    <?= $_SESSION['generated_password'] ?>

                </div>

                <div class="text-end mt-4">

                    <a href="clear_password.php"
                       class="btn btn-main">

                        Close

                    </a>

                </div>

            </div>

        </div>

    <?php endif; ?>

    </div>
```
