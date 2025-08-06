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
    <div class='flex justify-center mb-6 space-x-4'>
      <button onclick="showTab('login')" id='loginTab' class='text-sm font-medium px-4 py-2 rounded-full bg-indigo-600 text-white'>Login</button>
      <button onclick="showTab('register')" id='registerTab' class='text-sm font-medium px-4 py-2 rounded-full bg-gray-300 dark:bg-gray-700 text-gray-800 dark:text-gray-200'>Register</button>
    </div>

    <form id='loginForm' action='company_login_process.php' method='post' class='space-y-4'>
      <h2 class='text-2xl font-bold text-center text-gray-800 dark:text-white'>Company Login</h2>
      <input type='text' name='email' id='loginEmail' placeholder='Email' required
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500' autofocus>
      <input type='password' name='password' placeholder='Password' required
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
      <button type='submit'
        class='w-full bg-indigo-600 text-white py-2 rounded-full hover:bg-sky-700 transition'>Login</button>
    </form>

    <form id='registerForm' action='process_company_registration.php' method='post' class='space-y-4 hidden'>
      <h2 class='text-2xl font-bold text-center text-gray-800 dark:text-white'>Company Register</h2>
      <input type='text' name='company_name' placeholder='Company Name' required
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
      <input type='email' name='email' placeholder='Company Email' required
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
      <input type='tel' name='phone_num' placeholder='Company Phone Number'
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
      <input type='number' name='company_size' placeholder='Company Size (number of employees)'
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
      <input type='password' name='password' placeholder='Password' required
        class='w-full px-4 py-2 border rounded-full dark:bg-gray-700 dark:border-gray-600 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500'>
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
    window.onload = () => {
      document.getElementById('loginEmail').focus();
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