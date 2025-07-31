<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Punch In</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    * {
      transition: 0.3s all;
    }
  </style>
</head>
<body class="font-inter overflow-hidden">
  <section class="flex justify-center relative">
    <img src="https://pagedone.io/asset/uploads/1702362010.png" alt="background"
      class="w-full h-full object-cover fixed">
    <div class="mx-auto max-w-lg px-6 lg:px-8 absolute py-20">
      <div class="flex justify-center items-center mb-8">
        <img src="./images/transparent-logo.png" alt="logo" class="me-5 object-cover">
        <p class="text-3xl font-bold text-slate-800">Attendify</p>
      </div>
      <div class="rounded-2xl bg-white shadow-xl w-xl ">
        <form id="loginForm" class="lg:p-11 p-7 mx-auto" onsubmit="return handleLogin(event)">
          <div class="mb-11">
            <h1 class="text-gray-900 text-center font-manrope text-3xl font-bold mb-2">Punch In</h1>
          </div>
          <input type="text" id="emp_id"
            class="w-full h-12 text-gray-900 placeholder:text-gray-400 text-lg rounded-full border-gray-300 border shadow-sm px-4 mb-6"
            placeholder="Employee ID" required>
          <button type="submit"
            class="w-full h-12 text-white text-base font-semibold rounded-full bg-indigo-600 hover:bg-indigo-800 transition mb-11">
            Check
          </button>
          </form>
      </div>
    </div>
  </section>

  <!-- Modal -->
  <div id="employeeModal" class="fixed inset-0 z-50 hidden bg-black bg-opacity-50 flex justify-center items-center">
    <div class="bg-white rounded-xl p-8 w-full max-w-md">
      <h2 class="text-2xl font-bold mb-4">Employee Details</h2>
      <div id="employeeDetails" class="text-gray-800 space-y-2"></div>
      <button onclick="changePage()" class="mt-6 w-full bg-sky-500 text-white py-2 rounded-lg hover:bg-gray-600">Punch-in / out</button>
      <button onclick="closeModal()" class="mt-6 w-full bg-gray-400 text-white py-2 rounded-lg hover:bg-gray-600">Close</button>
    </div>
  </div>

  <script>
    function handleLogin(event) {
      const empId = document.getElementById('emp_id').value.trim();
      event.preventDefault();
      if(empId == "admin") {
        window.location.href = "admin-dashboard/dashboard.php";
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
      window.location.href = "login.php?emp_id="+empId;
    }
  </script>
</body>
</html>
