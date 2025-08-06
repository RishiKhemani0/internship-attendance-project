<?php
session_start();
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "attendance-db";

if (!isset($_SESSION['company_id'])) {
    header('Location: ../main/company-login.php');
    exit;
}

$company_id = $_SESSION['company_id'];
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch company information
$company = null;
$sql_company = "SELECT * FROM companies WHERE id = ?";
$stmt_company = $conn->prepare($sql_company);
$stmt_company->bind_param("i", $company_id);
$stmt_company->execute();
$result_company = $stmt_company->get_result();
if ($result_company->num_rows > 0) {
    $company = $result_company->fetch_assoc();
}
$stmt_company->close();

// Fetch existing departments
$departments = [];
$dept_sql = "SELECT department_id, dept_name FROM departments WHERE company_id = ?";
$stmt_dept = $conn->prepare($dept_sql);
$stmt_dept->bind_param("i", $company_id);
$stmt_dept->execute();
$result_dept = $stmt_dept->get_result();
while ($row = $result_dept->fetch_assoc()) {
    $departments[] = $row;
}
$stmt_dept->close();

// Fetch existing roles
$roles = [];
$role_sql = "SELECT id, name, expected_intime FROM roles WHERE company_id = ?";
$stmt_role = $conn->prepare($role_sql);
$stmt_role->bind_param("i", $company_id);
$stmt_role->execute();
$result_role = $stmt_role->get_result();
while ($row = $result_role->fetch_assoc()) {
    $roles[] = $row;
}
$stmt_role->close();

$conn->close();

function getBadgeColor($text)
{
    $colors = ['bg-blue-200 text-blue-800', 'bg-green-200 text-green-800', 'bg-yellow-200 text-yellow-800', 'bg-red-200 text-red-800'];
    $hash = crc32($text);
    $index = abs($hash) % count($colors);
    return $colors[$index];
}
?>
<!DOCTYPE html>
<html lang="en" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Info</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 text-gray-800 dark:text-gray-100 font-sans transition-all duration-300">

    <nav class="bg-white dark:bg-gray-800 shadow-md border-b border-gray-200 dark:border-gray-700 px-6 py-4">
        <div class="max-w-full flex justify-between items-center">
            <a class="flex items-center space-x-3" href="dashboard.php">
                <img src="../images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
                <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify Dashboard</span>
            </a>

            <div class="flex items-center gap-4">
                <button onclick="toggleTheme()" title="Toggle Dark Mode"
                    class="text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
                    </svg>
                </button>

                <div
                    class="w-9 h-9 bg-gray-200 dark:bg-gray-600 rounded-full flex items-center justify-center text-gray-700 dark:text-white">
                    <i class="fa-solid fa-user text-sm"></i>
                </div>
            </div>
        </div>
    </nav>

    <div class="flex">
        <aside
            class="w-64 bg-white dark:bg-gray-800 p-6 shadow-md md:block min-h-screen border-r border-gray-200 dark:border-gray-700">
            <ul class="space-y-3">
                <li>
                    <a href="./reports.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <i class="fa-solid fa-chart-simple text-lg"></i>
                        <span>Attendance Report</span>
                    </a>
                </li>
                <li>
                    <a href="./dashboard.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <i class="fa-solid fa-tablet text-lg"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li>
                    <a href="./company_info.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl bg-indigo-100 dark:bg-indigo-900 font-semibold text-indigo-800 dark:text-indigo-200">
                        <i class="fa-solid fa-user-gear text-lg"></i>
                        <span>Company Info</span>
                    </a>
                </li>
                <li>
                    <a href="./register_employee/register.php"
                        class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-700 transition">
                        <i class="fa-solid fa-user-plus text-lg"></i>
                        <span>Add Employee</span>
                    </a>
                </li>
            </ul>
        </aside>

        <main class="flex-1 p-6 lg:p-10">
            <h1 class="text-3xl font-bold text-center mb-10">Company Information</h1>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Company Details</h2>
                <form action="update_company_info.php" method="post" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Company Name</label>
                        <input type="text" name="company_name" value="<?= htmlspecialchars($company['name'] ?? '') ?>"
                            required
                            class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Email</label>
                        <input type="email" name="email" value="<?= htmlspecialchars($company['email'] ?? '') ?>"
                            required
                            class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium mb-1">Phone Number</label>
                        <input type="tel" name="phone_num" value="<?= htmlspecialchars($company['phone_num'] ?? '') ?>"
                            class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div class="flex gap-4">
                        <button type="submit" name="update"
                            class="bg-indigo-600 text-white px-6 py-2 rounded hover:bg-indigo-700 transition">Update</button>
                        <a href="logout.php"
                            class="bg-gray-200 text-gray-800 dark:bg-gray-700 dark:text-white px-6 py-2 rounded hover:bg-gray-300 transition">Logout</a>
                        <button type="button" onclick="openDeleteModal()"
                            class="bg-red-600 text-white px-6 py-2 rounded hover:bg-red-700 transition">Delete
                            Company</button>
                        <a href="device_setup.php"
                            class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">Change Device
                            Type</a>
                    </div>
                </form>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Manage Departments</h2>
                <form action="process_setup.php" method="post" class="flex gap-4 items-center mb-4">
                    <input type="text" name="dept_name" placeholder="New Department Name" required
                        class="flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" name="add_department"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add</button>
                </form>
                <div class="mt-4 flex flex-wrap gap-2">
                    <?php foreach ($departments as $dept): ?>
                        <div
                            class="flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium <?= getBadgeColor($dept['dept_name']) ?>">
                            <span><?= htmlspecialchars($dept['dept_name']) ?></span>
                            <button
                                onclick="openEditModal('dept', <?= $dept['department_id'] ?>, '<?= htmlspecialchars($dept['dept_name']) ?>')"
                                class="text-xs p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa-solid fa-pen"></i>
                            </button>
                            <a href="delete_department_role.php?delete_dept_id=<?= $dept['department_id'] ?>"
                                onclick="return confirm('Are you sure you want to delete this department?');"
                                class="text-xs p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6 mb-8">
                <h2 class="text-2xl font-semibold mb-4">Manage Roles</h2>
                <form action="process_setup.php" method="post" class="flex gap-4 items-center mb-4">
                    <input type="text" name="role_name" placeholder="New Role Name" required
                        class="flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <input type="time" name="expected_intime" placeholder="Expected In Time (HH:MM:SS)" required
                        class="flex-1 px-4 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="submit" name="add_role"
                        class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Add</button>
                </form>
                <div class="mt-4 space-y-2">
                    <?php foreach ($roles as $role): ?>
                        <div
                            class="flex items-center justify-between px-3 py-2 rounded-full text-sm font-medium <?= getBadgeColor($role['name']) ?>">
                            <span><?= htmlspecialchars($role['name']) ?> (Expected In:
                                <?= htmlspecialchars($role['expected_intime']) ?>)</span>
                            <div class="flex gap-2 items-center">
                                <button
                                    onclick="openEditModal('role', <?= $role['id'] ?>, '<?= htmlspecialchars($role['name']) ?>', '<?= htmlspecialchars($role['expected_intime']) ?>')"
                                    class="text-xs p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                                <a href="delete_department_role.php?delete_role_id=<?= $role['id'] ?>"
                                    onclick="return confirm('Are you sure you want to delete this role?');"
                                    class="text-xs p-1 rounded-full hover:bg-gray-100 dark:hover:bg-gray-700">
                                    <i class="fa-solid fa-xmark"></i>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div id="editModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden flex justify-center items-center">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-md">
                    <h3 id="editModalTitle" class="text-xl font-bold mb-4">Edit Item</h3>
                    <form id="editForm" action="update_department_role.php" method="post" class="space-y-4">
                        <input type="hidden" name="item_id" id="editItemId">
                        <input type="hidden" name="update_type" id="editItemType">
                        <div id="editFields">
                        </div>
                        <div class="flex justify-end gap-4 mt-4">
                            <button type="button" onclick="closeEditModal()"
                                class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>

            <div id="deleteModal"
                class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden flex justify-center items-center">
                <div class="bg-white dark:bg-gray-800 p-6 rounded-lg shadow-lg w-full max-w-sm text-center">
                    <h3 class="text-xl font-bold mb-4">Delete Company</h3>
                    <p class="mb-6">Are you sure you want to delete your company? This action is permanent and cannot be
                        undone.</p>
                    <div class="flex justify-center gap-4">
                        <button type="button" onclick="closeDeleteModal()"
                            class="px-4 py-2 bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600">Cancel</button>
                        <a href="delete_company.php"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Confirm Delete</a>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function openEditModal(type, id, name, expected_intime = null) {
            const modal = document.getElementById('editModal');
            const title = document.getElementById('editModalTitle');
            const form = document.getElementById('editForm');
            const fields = document.getElementById('editFields');

            document.getElementById('editItemId').value = id;

            if (type === 'dept') {
                title.textContent = 'Edit Department';
                document.getElementById('editItemType').name = 'update_department';
                fields.innerHTML = `
                <div>
                    <label class="block text-sm font-medium mb-1">Department Name</label>
                    <input type="text" name="new_dept_name" value="${name}" required class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            `;
            } else if (type === 'role') {
                title.textContent = 'Edit Role';
                document.getElementById('editItemType').name = 'update_role';
                fields.innerHTML = `
                <div>
                    <label class="block text-sm font-medium mb-1">Role Name</label>
                    <input type="text" name="new_role_name" value="${name}" required class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Expected In-Time</label>
                    <input type="time" name="new_expected_intime" value="${expected_intime}" required class="w-full px-3 py-2 border rounded dark:bg-gray-700 dark:border-gray-600 focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            `;
            }

            modal.classList.remove('hidden');
        }

        function closeEditModal() {
            const modal = document.getElementById('editModal');
            modal.classList.add('hidden');
        }

        function openDeleteModal() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        function toggleTheme() {
            const isDark = document.documentElement.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        }

        if (localStorage.getItem('theme') === 'dark' ||
            (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>

</html>