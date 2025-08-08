<?php
session_start();
?>
<!DOCTYPE html>
<html lang='en' class='dark'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1'>
    <title>Employee Login</title>
    <script src='https://cdn.tailwindcss.com'></script>
    <script>
        tailwind.config = {
            darkMode: 'class'
        }
    </script>
</head>
<body class='bg-gray-100 dark:bg-gray-900 font-sans transition-colors duration-300'>

    <nav class='bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4 w-full fixed'>
        <div class='max-w-full flex justify-between items-center'>
            <div class='flex items-center space-x-3'>
                <img src='../images/transparent-logo.png' alt='Logo' class='w-8 h-8' />
                <span class='text-xl font-semibold text-gray-800 dark:text-white'>Attentify</span>
            </div>
        </div>
    </nav>

    <div class='flex items-center justify-center min-h-screen'>
        <div class='bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-8 max-w-md w-full border border-gray-200 dark:border-gray-700'>
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?= htmlspecialchars($_SESSION['login_error']) ?></span>
                </div>
                <?php unset($_SESSION['login_error']); ?>
            <?php endif; ?>
            <form id='loginForm' action='employee_login_process.php' method='post' class='space-y-4'>
                <h2 class='text-2xl font-bold text-center text-gray-800 dark:text-white'>Employee Login</h2>
                <input type='email' name='email' placeholder='Email' required
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' autofocus>
                <input type='password' name='password' placeholder='Password' required
                    class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
                <button type='submit'
                    class='w-full bg-indigo-600 text-white py-2 rounded-full hover:bg-sky-700 transition'>Login</button>
            </form>
        </div>
    </div>
</body>
</html>