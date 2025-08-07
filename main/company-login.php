<?php
session_start();
if (isset($_SESSION['company_id'])) {
    if (isset($_SESSION['device_type']) && $_SESSION['device_type'] === 'admin') {
        header('Location: ../admin-dashboard/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;
}

$old_form_data = $_SESSION['old_form_data'] ?? null;
$error_message = $_SESSION['error_message'] ?? null;
$show_register_tab = isset($_GET['tab']) && $_GET['tab'] === 'register';

// Clear session data after retrieving
unset($_SESSION['old_form_data']);
unset($_SESSION['error_message']);

?>
<!DOCTYPE html>
<html lang='en' class='dark'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Company Login / Register</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
    <style>
        /* Hide number input arrows for all browsers */
        input[type=number]::-webkit-inner-spin-button, 
        input[type=number]::-webkit-outer-spin-button { 
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</head>
<body class='bg-gray-100 dark:bg-gray-900 font-sans transition-colors duration-300'>

    <nav class='bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4 w-full fixed'>
        <div class='max-w-full flex justify-between items-center'>
            <div class='flex items-center space-x-3'>
                <img src='../images/transparent-logo.png' alt='Logo' class='w-8 h-8' />
                <span class='text-xl font-semibold text-gray-800 dark:text-white'>Attentify</span>
            </div>
            <div class='flex items-center gap-4'>
                <button onclick='toggleTheme()' title='Toggle Dark Mode'
                    class='text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition'>
                    <svg xmlns='http://www.w3.org/2000/svg' class='h-6 w-6' fill='none' viewBox='0 0 24 24' stroke='currentColor'
                        stroke-width='2'>
                        <path stroke-linecap='round' stroke-linejoin='round'
                            d='M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z' />
                    </svg>
                </button>
            </div>
        </div>
    </nav>

    <div class='flex items-center justify-center min-h-screen'>
        <div class='bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-8 max-w-md w-full border border-gray-200 dark:border-gray-700'>
            <?php if ($error_message): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($error_message) ?></span>
                </div>
            <?php endif; ?>

            <div class='flex justify-center mb-6 space-x-4'>
                <button onclick="showTab('login')" id='loginTab' class='text-sm font-medium px-4 py-2 rounded-full'>Login</button>
                <button onclick="showTab('register')" id='registerTab' class='text-sm font-medium px-4 py-2 rounded-full'>Register</button>
            </div>

            <form id='loginForm' action='company_login_process.php' method='post' class='space-y-4'>
                <h2 class='text-2xl font-bold text-center text-gray-800 dark:text-white'>Company Login</h2>
                <input type='text' name='email' id='loginEmail' placeholder='Email' required
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' autofocus>
                <div class="relative">
                    <input type='password' name='password' id='loginPassword' placeholder='Password' required
                        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <input type="checkbox" id="showLoginPassword" class="hidden" onchange="togglePasswordVisibility('loginPassword', 'showLoginPassword')">
                        <label for="showLoginPassword" class="text-gray-400 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.575 3.01 9.963 7.822.043.149.043.298 0 .447-1.388 4.812-5.325 7.82-9.963 7.82-4.638 0-8.575-3.01-9.963-7.82z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </label>
                    </div>
                </div>
                <button type='submit'
                    class='w-full bg-indigo-600 text-white py-2 rounded-full hover:bg-sky-700 transition'>Login</button>
            </form>

            <form id='registerForm' action='process_company_registration.php' method='post' class='space-y-4 hidden' onsubmit="return validateRegistrationForm()">
                <h2 class='text-2xl font-bold text-center text-gray-800 dark:text-white'>Company Register</h2>
                <input type='text' name='company_name' placeholder='Company Name' required
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' value="<?= htmlspecialchars($old_form_data['company_name'] ?? '') ?>">
                <input type='email' name='email' placeholder='Company Email' required
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                <input type='tel' name='phone_num' placeholder='Company Phone Number'
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' value="<?= htmlspecialchars($old_form_data['phone_num'] ?? '') ?>">
                <input type='number' name='company_size' id='companySize' placeholder='Company Size (number of employees)' min="1"
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' value="<?= htmlspecialchars($old_form_data['company_size'] ?? '') ?>">
                <div class="relative">
                    <input type='password' name='password' id='registerPassword' placeholder='Password' required
                        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center">
                        <input type="checkbox" id="showRegisterPassword" class="hidden" onchange="togglePasswordVisibility('registerPassword', 'showRegisterPassword')">
                        <label for="showRegisterPassword" class="text-gray-400 cursor-pointer">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.575 3.01 9.963 7.822.043.149.043.298 0 .447-1.388 4.812-5.325 7.82-9.963 7.82-4.638 0-8.575-3.01-9.963-7.82z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </label>
                    </div>
                </div>
                <button type='submit'
                    class='w-full bg-indigo-600 text-white py-2 rounded-full hover:bg-sky-700 transition'>Register</button>
            </form>
        </div>
    </div>

    <script>
        function showTab(tab) {
            const loginForm = document.getElementById('loginForm');
            const registerForm = document.getElementById('registerForm');
            const loginTab = document.getElementById('loginTab');
            const registerTab = document.getElementById('registerTab');

            if (tab === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
                loginTab.classList.add('bg-indigo-600', 'text-white');
                loginTab.classList.remove('bg-gray-300', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
                registerTab.classList.remove('bg-indigo-600', 'text-white');
                registerTab.classList.add('bg-gray-300', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
                setTimeout(() => document.getElementById('loginEmail').focus(), 100);
            } else {
                loginForm.classList.add('hidden');
                registerForm.classList.remove('hidden');
                registerTab.classList.add('bg-indigo-600', 'text-white');
                registerTab.classList.remove('bg-gray-300', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
                loginTab.classList.remove('bg-indigo-600', 'text-white');
                loginTab.classList.add('bg-gray-300', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-gray-200');
            }
        }
        
        function togglePasswordVisibility(passwordId, checkboxId) {
            const passwordInput = document.getElementById(passwordId);
            const checkbox = document.getElementById(checkboxId);
            if (checkbox.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        }

        function validateRegistrationForm() {
            const companySizeInput = document.getElementById('companySize');
            const companySize = parseInt(companySizeInput.value, 10);
            
            if (companySize <= 0) {
                alert("Company size must be a positive number.");
                return false;
            }
            
            if (companySize > 1000) {
                alert("Please enter a valid company size. If your company is larger than 1000 employees, please contact support.");
                return false;
            }

            return true;
        }

        window.onload = () => {
            const showRegisterTab = <?= json_encode($show_register_tab); ?>;
            if (showRegisterTab) {
                showTab('register');
            } else {
                showTab('login');
                document.getElementById('loginEmail').focus();
            }
        };

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