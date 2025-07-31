<!DOCTYPE html>
<html lang="en" class="dark">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Punch In</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            inter: ['Inter', 'sans-serif'],
            manrope: ['Manrope', 'sans-serif'],
          },
        },
      },
    }
  </script>
</head>

<body class="font-inter bg-gray-100 dark:bg-gray-900">
  <!-- Navbar -->
  <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-md px-6 py-4 w-full fixed">
    <div class="max-w-full flex justify-between items-center">
      <div class="flex items-center space-x-3">
        <img src="images/transparent-logo.png" alt="Logo" class="w-8 h-8" />
        <span class="text-xl font-semibold text-gray-800 dark:text-white">Attentify</span>
      </div>
      <div class="flex items-center gap-4">
        <button onclick="toggleTheme()" title="Toggle Dark Mode"
          class="text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-white transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"
            stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
              d="M12 3v1m0 16v1m8.66-8.66h1M3.34 12H2.34m15.36 4.24l.71.71M6.34 6.34l-.71-.71m12.02-.02l-.71.71M6.34 17.66l.71-.71M21 12a9 9 0 11-9-9c.34 0 .68.02 1.01.06a7 7 0 008.93 8.94c.04.33.06.67.06 1z" />
          </svg>
        </button>
      </div>
    </div>
  </nav>
  <div class="flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 shadow-2xl rounded-2xl p-8 transition">
      <div class="flex items-center justify-center mb-8">
        <img src="./images/transparent-logo.png" alt="logo" class="h-12 me-3 object-cover">
        <p class="text-3xl font-bold text-gray-800 dark:text-white">Attendify</p>
      </div>
      <form id="loginForm" onsubmit="return handleLogin(event)">
        <h1 class="text-center text-2xl font-bold text-gray-900 dark:text-white mb-6">Punch In</h1>
        <input type="text" id="emp_id"
          class="w-full h-12 px-4 mb-6 rounded-full text-gray-900 placeholder-gray-400 border border-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-white dark:placeholder-gray-300"
          placeholder="Employee ID" required>
        <button type="submit"
          class="w-full h-12 rounded-full bg-indigo-600 text-white font-semibold hover:bg-indigo-700 transition">
          Check
        </button>
      </form>
    </div>
  </div>

  <!-- Modal -->
  <div id="employeeModal"
    class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center transition duration-300">
    <div
      class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-md shadow-2xl border border-gray-200 dark:border-gray-700">
      <h2 class="text-2xl font-bold mb-4 text-gray-800 dark:text-white text-center">Employee Details</h2>
      <div id="employeeDetails" class="text-gray-700 dark:text-gray-200 space-y-2 text-sm sm:text-base"></div>
      <div class="mt-6 space-y-3">
        <button onclick="changePage()"
          class="w-full bg-sky-600 text-white py-2 rounded-full hover:bg-sky-700 transition">Punch In / Out</button>
        <button onclick="closeModal()"
          class="w-full bg-gray-400 text-white py-2 rounded-full hover:bg-gray-500 transition">Close</button>
      </div>
    </div>
  </div>

  <script>
    function handleLogin(event) {
      const empId = document.getElementById('emp_id').value.trim();
      event.preventDefault();
      if (empId === "admin") {
        window.location.href = "admin-dashboard/dashboard.php";
        return;
      }
      if (!empId) return;

      fetch(`get_employee.php?employee_id=${encodeURIComponent(empId)}`)
        .then(response => response.json())
        .then(data => {
          if (data && data.employee_id) {
            document.getElementById('employeeDetails').innerHTML = `
              <p><strong>ID:</strong> ${data.employee_id}</p>
              <p><strong>Name:</strong> ${data.first_name} ${data.middle_name || ''} ${data.last_name}</p>
              <p><strong>Role:</strong> ${data.role}</p>
              <p><strong>Email:</strong> ${data.email}</p>
              <p><strong>Phone:</strong> ${data.phone_num}</p>
            `;
            document.getElementById('employeeModal').classList.remove('hidden');
          } else {
            alert("Employee not found.");
          }
        })
        .catch(err => {
          console.error(err);
          alert("Error fetching employee data.");
        });
    }

    function closeModal() {
      document.getElementById('employeeModal').classList.add('hidden');
    }

    function changePage() {
      const empId = document.getElementById('emp_id').value.trim();
      window.location.href = "login.php?emp_id=" + empId;
    }
  </script>
  <script>
  function toggleTheme() {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
  }
</script>
<script>
  // On page load, set dark mode based on saved preference
  if (localStorage.getItem('theme') === 'dark' ||
     (!localStorage.getItem('theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
    document.documentElement.classList.add('dark');
  } else {
    document.documentElement.classList.remove('dark');
  }
</script>

</body>

</html>