<!DOCTYPE html>
<html lang="en" class="dark">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
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
<body class="font-inter bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center px-4">
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

  <!-- Modal -->
  <div id="employeeModal" class="fixed inset-0 z-50 hidden bg-black/60 backdrop-blur-sm flex justify-center items-center transition duration-300">
    <div class="bg-white dark:bg-gray-800 rounded-2xl p-8 w-full max-w-md shadow-2xl border border-gray-200 dark:border-gray-700">
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
</body>
</html>
