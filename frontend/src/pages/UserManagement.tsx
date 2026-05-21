import { useMemo, useState } from "react";
import {
  Pencil,
  KeyRound as Key,
  Ban,
  Plus,
  Search,
  ChevronLeft,
  ChevronRight,
} from "lucide-react";

export default function UserManagement() {
  return <UserManagementPage />;
}

// Palette
// Deep Navy #093C5D
// Mid-Blue #3B7597
// Olive Green #9CB07A
// Light Gray #F5F5F5

type Role = "Admin" | "Staff";
type Status = "Active" | "Inactive";

type User = {
  id: string;
  name: string;
  email: string;
  employeeId: string;
  role: Role;
  status: Status;
  password: string;
};

const INITIAL_USERS: User[] = [
  {
    id: "1",
    name: "Priya Singh",
    email: "admin@clockit.app",
    employeeId: "A-001",
    role: "Admin",
    status: "Active",
    password: "Temp1234",
  },
  {
    id: "2",
    name: "David Okafor",
    email: "david@clockit.app",
    employeeId: "A-002",
    role: "Admin",
    status: "Active",
    password: "Temp5678",
  },
  {
    id: "3",
    name: "David Naidoo",
    email: "david.naidoo@clockit.app",
    employeeId: "A-003",
    role: "Admin",
    status: "Active",
    password: "Temp9012",
  },
  {
    id: "4",
    name: "Sarah Mthembu",
    email: "sarah@clockit.app",
    employeeId: "S-101",
    role: "Staff",
    status: "Active",
    password: "Temp3456",
  },
  {
    id: "5",
    name: "John Adams",
    email: "john@clockit.app",
    employeeId: "S-102",
    role: "Staff",
    status: "Active",
    password: "Temp7890",
  },
  {
    id: "6",
    name: "Mary Chen",
    email: "mary@clockit.app",
    employeeId: "S-103",
    role: "Staff",
    status: "Active",
    password: "Temp1122",
  },
];

const PAGE_SIZE = 5;

function initials(name: string) {
  return name
    .split(" ")
    .map((n) => n[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

function UserManagementPage() {
  const [users, setUsers] = useState<User[]>(INITIAL_USERS);
  const [query, setQuery] = useState("");
  const [page, setPage] = useState(1);

  // Add User Modal
  const [showAddModal, setShowAddModal] = useState(false);

  const [newUser, setNewUser] = useState({
    name: "",
    email: "",
    role: "Staff" as Role,
  });

  // Edit User Modal
  const [showEditModal, setShowEditModal] = useState(false);
  const [editingUserId, setEditingUserId] = useState("");

  const [editUser, setEditUser] = useState({
    name: "",
    email: "",
    role: "Staff" as Role,
  });

  // Password Modal
  const [generatedPassword, setGeneratedPassword] =
    useState("");

  const [showPasswordModal, setShowPasswordModal] =
    useState(false);

  function generateEmployeeId(role: Role) {
    const prefix = role === "Admin" ? "A" : "S";

    const existing = users.filter((u) => u.role === role);

    const nextNumber = existing.length + 1;

    const baseNumber = role === "Admin" ? 1 : 101;

    return `${prefix}-${String(
      baseNumber + existing.length
    ).padStart(3, "0")}`;
  }

  function generatePassword(length = 10) {
    const chars =
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    let password = "";

    for (let i = 0; i < length; i++) {
      password += chars.charAt(
        Math.floor(Math.random() * chars.length)
      );
    }

    return password;
  }

  const filtered = useMemo(() => {
    const q = query.trim().toLowerCase();

    if (!q) return users;

    return users.filter(
      (u) =>
        u.name.toLowerCase().includes(q) ||
        u.email.toLowerCase().includes(q) ||
        u.employeeId.toLowerCase().includes(q)
    );
  }, [query, users]);

  const totalPages = Math.max(
    1,
    Math.ceil(filtered.length / PAGE_SIZE)
  );

  const currentPage = Math.min(page, totalPages);

  const start = (currentPage - 1) * PAGE_SIZE;

  const pageRows = filtered.slice(
    start,
    start + PAGE_SIZE
  );

  return (
    <div className="min-h-screen bg-[#F5F5F5]">
      <main className="w-full px-6 py-8">
        {/* Header */}
        <div className="flex flex-col md:flex-row md:items-start md:justify-between gap-4 mb-6">
          <div>
            <h1 className="text-5xl font-bold text-[#093C5D]">
              User Management
            </h1>

            <p className="text-lg text-[#3B7597] mt-1">
              Add, edit, or disable accounts.
              Self-registration is disabled.
            </p>
          </div>

          {/* Add User Button */}
          <button
            onClick={() => setShowAddModal(true)}
            className="inline-flex items-center gap-2 text-lg bg-[#093C5D] hover:bg-[#072d47] text-white px-5 py-2.5 rounded-lg font-medium transition-colors"
          >
            <Plus size={18} />
            Add User
          </button>
        </div>

        {/* Search */}
        <div className="mb-4 relative max-w-md">
          <Search
            size={16}
            className="absolute left-3 top-1/2 -translate-y-1/2 text-[#3B7597]"
          />

          <input
            type="text"
            value={query}
            onChange={(e) => {
              setQuery(e.target.value);
              setPage(1);
            }}
            placeholder="Search by name, email or employee ID..."
            className="w-full pl-9 pr-3 py-2.5 text-lg rounded-lg border border-gray-200 bg-white text-[#093C5D] placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-[#3B7597]/40 focus:border-[#3B7597]"
          />
        </div>

        {/* Table */}
        <div className="overflow-x-auto bg-white rounded-xl">
          <table className="w-full text-left">
            <thead className="bg-[#F5F5F5] uppercase tracking-wider text-[#3B7597]">
              <tr>
                <th className="px-6 py-4 font-semibold">
                  Name
                </th>
                <th className="px-6 py-4 font-semibold">
                  Email
                </th>
                <th className="px-6 py-4 font-semibold">
                  Employee ID
                </th>
                <th className="px-6 py-4 font-semibold">
                  Role
                </th>
                <th className="px-6 py-4 font-semibold">
                  Status
                </th>
                <th className="px-6 py-4 font-semibold text-right">
                  Actions
                </th>
              </tr>
            </thead>

            <tbody className="divide-y divide-gray-100">
              {pageRows.map((u) => (
                <tr
                  key={u.id}
                  className="hover:bg-[#F5F5F5]/60"
                >
                  {/* Name */}
                  <td className="px-6 py-4">
                    <div className="flex items-center gap-3">
                      <div className="h-9 w-9 rounded-full bg-[#093C5D] text-white text-xs font-semibold flex items-center justify-center">
                        {initials(u.name)}
                      </div>

                      <span className="text-lg font-medium text-[#093C5D]">
                        {u.name}
                      </span>
                    </div>
                  </td>

                  {/* Email */}
                  <td className="px-6 py-4 text-lg text-[#3B7597]">
                    {u.email}
                  </td>

                  {/* Employee ID */}
                  <td className="px-6 py-4 text-lg text-[#093C5D]">
                    {u.employeeId}
                  </td>

                  {/* Role */}
                  <td className="px-6 py-4">
                    {u.role === "Admin" ? (
                      <span className="inline-block px-3 py-1 rounded-full text-sm font-semibold bg-[#093C5D] text-white">
                        Admin
                      </span>
                    ) : (
                      <span className="inline-block px-3 py-1 rounded-full text-sm font-semibold border border-gray-300 text-[#093C5D]">
                        Staff
                      </span>
                    )}
                  </td>

                  {/* Status */}
                  <td className="px-6 py-4">
                    <span
                      className={
                        "inline-block px-3 py-1 rounded-full text-sm font-semibold border " +
                        (u.status === "Active"
                          ? "bg-[#9CB07A]/20 text-[#5e7a3e] border-[#9CB07A]/40"
                          : "bg-red-100 text-red-600 border-red-200")
                      }
                    >
                      {u.status}
                    </span>
                  </td>

                  {/* Actions */}
                  <td className="px-6 py-4">
                    <div className="flex items-center justify-end gap-2">
                      {/* Edit */}
                      <button
                        aria-label="Edit"
                        onClick={() => {
                          setEditingUserId(u.id);

                          setEditUser({
                            name: u.name,
                            email: u.email,
                            role: u.role,
                          });

                          setShowEditModal(true);
                        }}
                        className="p-2 rounded-md text-[#3B7597] hover:bg-[#3B7597]/10"
                      >
                        <Pencil size={16} />
                      </button>

                      {/* Reset Password */}
                      <button
                        aria-label="Reset password"
                        onClick={() => {
                          const newPassword =
                            generatePassword();

                          setUsers((prev) =>
                            prev.map((user) =>
                              user.id === u.id
                                ? {
                                    ...user,
                                    password:
                                      newPassword,
                                  }
                                : user
                            )
                          );

                          setGeneratedPassword(
                            newPassword
                          );

                          setShowPasswordModal(true);
                        }}
                        className="p-2 rounded-md text-[#3B7597] hover:bg-[#3B7597]/10"
                      >
                        <Key size={16} />
                      </button>

                      {/* Disable */}
                      <button
                        aria-label="Disable user"
                        onClick={() => {
                          setUsers((prev) =>
                            prev.map((user) =>
                              user.id === u.id
                                ? {
                                    ...user,
                                    status:
                                      user.status ===
                                      "Active"
                                        ? "Inactive"
                                        : "Active",
                                  }
                                : user
                            )
                          );
                        }}
                        className="p-2 rounded-md text-red-500 hover:bg-red-50"
                      >
                        <Ban size={16} />
                      </button>
                    </div>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>

          {/* Pagination */}
          <div className="flex flex-col sm:flex-row items-center justify-between gap-3 px-6 py-4 border-t border-gray-100 bg-white">
            <p className="text-base text-[#3B7597]">
              Showing{" "}
              <span className="font-semibold text-[#093C5D]">
                {filtered.length === 0 ? 0 : start + 1}
              </span>{" "}
              –{" "}
              <span className="font-semibold text-[#093C5D]">
                {Math.min(
                  start + PAGE_SIZE,
                  filtered.length
                )}
              </span>{" "}
              of{" "}
              <span className="font-semibold text-[#093C5D]">
                {filtered.length}
              </span>{" "}
              users
            </p>

            <div className="flex items-center gap-1">
              <button
                onClick={() =>
                  setPage((p) => Math.max(1, p - 1))
                }
                disabled={currentPage === 1}
                className="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 text-[#093C5D] disabled:opacity-40 hover:bg-[#F5F5F5]"
              >
                <ChevronLeft size={16} /> Prev
              </button>

              {Array.from(
                { length: totalPages },
                (_, i) => i + 1
              ).map((n) => (
                <button
                  key={n}
                  onClick={() => setPage(n)}
                  className={
                    "min-w-9 h-9 px-3 rounded-md text-sm font-medium transition-colors " +
                    (n === currentPage
                      ? "bg-[#093C5D] text-white"
                      : "text-[#093C5D] hover:bg-[#F5F5F5] border border-gray-200")
                  }
                >
                  {n}
                </button>
              ))}

              <button
                onClick={() =>
                  setPage((p) =>
                    Math.min(totalPages, p + 1)
                  )
                }
                disabled={
                  currentPage === totalPages
                }
                className="inline-flex items-center gap-1 px-3 py-1.5 rounded-md border border-gray-200 text-[#093C5D] disabled:opacity-40 hover:bg-[#F5F5F5]"
              >
                Next <ChevronRight size={16} />
              </button>
            </div>
          </div>
        </div>
      </main>

      {/* ADD USER MODAL */}
      {showAddModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-2xl font-bold text-[#093C5D]">
                Add User
              </h2>

              <button
                onClick={() => setShowAddModal(false)}
                className="text-gray-400 hover:text-gray-600 text-xl"
              >
                ✕
              </button>
            </div>

            <div className="space-y-4">
              {/* Full Name */}
              <div>
                <label htmlFor="add-full-name" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Full Name
                </label>

                <input
                  id="add-full-name"
                  type="text"
                  value={newUser.name}
                  onChange={(e) =>
                    setNewUser({
                      ...newUser,
                      name: e.target.value,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                />
              </div>

              {/* Email */}
              <div>
                <label htmlFor="add-email-address" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Email Address
                </label>

                <input
                  id="add-email-address"
                  type="email"
                  value={newUser.email}
                  onChange={(e) =>
                    setNewUser({
                      ...newUser,
                      email: e.target.value,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                />
              </div>

              {/* Role */}
              <div>
                <label htmlFor="add-role" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Role
                </label>

                <select
                  id="add-role"
                  value={newUser.role}
                  onChange={(e) =>
                    setNewUser({
                      ...newUser,
                      role: e.target.value as Role,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                >
                  <option value="Staff">
                    Staff
                  </option>
                  <option value="Admin">
                    Admin
                  </option>
                </select>
              </div>

              {/* Employee ID */}
              <div>
                <label htmlFor="add-employee-id" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Employee ID
                </label>

                <input
                  id="add-employee-id"
                  type="text"
                  disabled
                  value={generateEmployeeId(
                    newUser.role
                  )}
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-200 bg-gray-100 text-gray-500"
                />
              </div>
            </div>

            {/* Buttons */}
            <div className="flex items-center justify-end gap-3 mt-6">
              <button
                onClick={() =>
                  setShowAddModal(false)
                }
                className="px-5 py-2.5 rounded-lg border border-gray-300 text-[#093C5D]"
              >
                Cancel
              </button>

              <button
                onClick={() => {
                  if (
                    !newUser.name ||
                    !newUser.email
                  ) {
                    alert(
                      "Please complete all fields"
                    );
                    return;
                  }

                  const randomPassword =
                    generatePassword();

                  const user: User = {
                    id: crypto.randomUUID(),
                    name: newUser.name,
                    email: newUser.email,
                    role: newUser.role,
                    employeeId:
                      generateEmployeeId(
                        newUser.role
                      ),
                    status: "Active",
                    password: randomPassword,
                  };

                  setUsers((prev) => [
                    ...prev,
                    user,
                  ]);

                  setGeneratedPassword(
                    randomPassword
                  );

                  setShowPasswordModal(true);

                  setNewUser({
                    name: "",
                    email: "",
                    role: "Staff",
                  });

                  setShowAddModal(false);
                }}
                className="px-5 py-2.5 rounded-lg bg-[#093C5D] text-white hover:bg-[#072d47]"
              >
                Save User
              </button>
            </div>
          </div>
        </div>
      )}

      {/* EDIT USER MODAL */}
      {showEditModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
            <div className="flex items-center justify-between mb-6">
              <h2 className="text-2xl font-bold text-[#093C5D]">
                Edit User
              </h2>

              <button
                onClick={() =>
                  setShowEditModal(false)
                }
                className="text-gray-400 hover:text-gray-600 text-xl"
              >
                ✕
              </button>
            </div>

            <div className="space-y-4">
              <div>
                <label htmlFor="edit-full-name" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Full Name
                </label>

                <input
                  id="edit-full-name"
                  type="text"
                  value={editUser.name}
                  onChange={(e) =>
                    setEditUser({
                      ...editUser,
                      name: e.target.value,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                />
              </div>

              <div>
                <label htmlFor="edit-email-address" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Email Address
                </label>

                <input
                  id="edit-email-address"
                  type="email"
                  value={editUser.email}
                  onChange={(e) =>
                    setEditUser({
                      ...editUser,
                      email: e.target.value,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                />
              </div>

              <div>
                <label htmlFor="edit-role" className="block text-sm font-medium text-[#093C5D] mb-1">
                  Role
                </label>

                <select
                  id="edit-role"
                  value={editUser.role}
                  onChange={(e) =>
                    setEditUser({
                      ...editUser,
                      role: e.target.value as Role,
                    })
                  }
                  className="w-full px-4 py-2.5 rounded-lg border border-gray-300"
                >
                  <option value="Staff">
                    Staff
                  </option>
                  <option value="Admin">
                    Admin
                  </option>
                </select>
              </div>
            </div>

            {/* Buttons */}
            <div className="flex items-center justify-end gap-3 mt-6">
              <button
                onClick={() =>
                  setShowEditModal(false)
                }
                className="px-5 py-2.5 rounded-lg border border-gray-300 text-[#093C5D]"
              >
                Cancel
              </button>

              <button
                onClick={() => {
                  setUsers((prev) =>
                    prev.map((user) =>
                      user.id ===
                      editingUserId
                        ? {
                            ...user,
                            name: editUser.name,
                            email:
                              editUser.email,
                            role: editUser.role,
                          }
                        : user
                    )
                  );

                  setShowEditModal(false);
                }}
                className="px-5 py-2.5 rounded-lg bg-[#093C5D] text-white hover:bg-[#072d47]"
              >
                Save Changes
              </button>
            </div>
          </div>
        </div>
      )}

      {/* PASSWORD MODAL */}
      {showPasswordModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 px-4">
          <div className="bg-white w-full max-w-md rounded-2xl shadow-2xl p-6">
            <h2 className="text-2xl font-bold text-[#093C5D] mb-4">
              Temporary Password
            </h2>

            <p className="text-[#3B7597] mb-4">
              Copy and share this password
              securely with the user.
            </p>

            <div className="flex items-center justify-between bg-[#F5F5F5] border border-gray-200 rounded-lg px-4 py-3">
              <span className="font-mono text-lg text-[#093C5D]">
                {generatedPassword}
              </span>

              <button
                onClick={() => {
                  navigator.clipboard.writeText(
                    generatedPassword
                  );

                  alert("Password copied!");
                }}
                className="px-3 py-1.5 rounded-md bg-[#093C5D] text-white text-sm hover:bg-[#072d47]"
              >
                Copy
              </button>
            </div>

            <div className="flex justify-end mt-6">
              <button
                onClick={() =>
                  setShowPasswordModal(false)
                }
                className="px-5 py-2.5 rounded-lg bg-[#093C5D] text-white hover:bg-[#072d47]"
              >
                Close
              </button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}