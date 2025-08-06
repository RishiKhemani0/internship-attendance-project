<?php
session_start();
if (isset($_SESSION['company_id'])) {
    if (!isset($_SESSION['device_type'])) {
        header('Location: admin-dashboard/device_setup.php');
    } else {
        header('Location: ' . ($_SESSION['device_type'] === 'admin' ? 'admin-dashboard/dashboard.php' : 'main/index.php'));
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attentify - Smart Attendance Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-hero {
            background: linear-gradient(135deg, #4F46E5 0%, #7C3AED 100%);
        }

        html {
            scroll-behavior: smooth;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate.animate-fadeIn {
            animation: fadeIn 1s ease-out forwards;
        }

        .container {
            width: 80% !important;
        }
    </style>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"
        integrity="sha512-c42qTSw/wPZ3/5LBzD+Bw5f7bSF2oxou6wEb+I/lqeaKV5FDIfMvvRp772y4jcJLKuGUOpbJMdg/BTl50fJYAw=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.0/css/all.min.css"
        integrity="sha512-DxV+EoADOkOygM4IR9yXP8Sb2qwgidEmeqAEmDKIOfPRQZOWbXCzLC6vjbZyy0vPisbH2SyW27+ddLVCN+OMzQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
</head>

<body class="bg-gray-50 text-gray-800 font-sans ">

    <header class="bg-white shadow-sm sticky top-0 z-50 animate-fadeIn wow">
        <nav class="container mx-auto px-4 py-4 flex justify-between items-center">
            <a href="#" class="text-2xl font-bold text-indigo-600">Attentify</a>
            <div class="hidden md:flex space-x-6">
                <a href="#features"
                    class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Features</a>
                <a href="#testimonials"
                    class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Testimonials</a>
                <a href="#pricing" class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Pricing</a>
                <a href="#contact" class="text-gray-600 hover:text-indigo-600 transition-colors duration-300">Contact</a>
            </div>
            <div class="flex items-center space-x-4">
                <a href="./main/company-login.php"
                    class="text-gray-600 hover:text-indigo-600 transition-colors duration-300 hidden md:block">Log
                    In</a>
                <a href="./main/company-login.php"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition-colors duration-300">
                    Get Started
                </a>
            </div>
        </nav>
    </header>

    <section class="bg-hero text-white py-20 md:py-32 relative overflow-hidden">
        <div class="container mx-auto px-4 text-center relative z-10">
            <h1 class="text-4xl md:text-6xl font-extrabold leading-tight mb-4 animate-fadeIn wow">
                Simplify Attendance, <br> Maximize Productivity.
            </h1>
            <p class="text-lg md:text-xl max-w-2xl mx-auto mb-8 opacity-90 animate-fadeIn"
                style="animation-delay: 0.2s;">
                Attentify is the modern, effortless way to manage attendance for your team, school, or event.
            </p>
            <div class="space-y-4 sm:space-y-0 sm:space-x-4 flex flex-col sm:flex-row justify-center animate-fadeIn"
                style="animation-delay: 0.4s;">
                <a href="./main/company-login.php"
                    class="bg-white text-indigo-600 font-semibold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition-all duration-300">
                    Start Your Free Trial
                </a>
                <a href="#features"
                    class="bg-transparent border border-white text-white font-semibold py-3 px-8 rounded-lg hover:bg-white hover:text-indigo-600 transition-all duration-300">
                    Learn More
                </a>
            </div>
        </div>
        <div class="absolute inset-0 z-0 opacity-20">
            <svg class="absolute inset-0 h-full w-full" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"
                preserveAspectRatio="none">
                <pattern id="pattern-hero" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse">
                    <path d="M 0 0 L 10 10 M 10 0 L 0 10" stroke="white" stroke-width="1"></path>
                </pattern>
                <rect x="0" y="0" width="100%" height="100%" fill="url(#pattern-hero)"></rect>
            </svg>
        </div>
    </section>

    <section id="features" class="py-20 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Powerful Features, Simple Interface</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Attentify provides a suite of tools to make attendance tracking painless and efficient.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 animate-fadeIn wow">
                <div
                    class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 text-center">
                    <div class="text-indigo-600 text-4xl mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Real-time Dashboard</h3>
                    <p class="text-gray-500">
                        Monitor attendance instantly with a clean, easy-to-read dashboard. See who's in and who's out at
                        a glance.
                    </p>
                </div>
                <div
                    class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 text-center">
                    <div class="text-indigo-600 text-4xl mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Automated Reporting</h3>
                    <p class="text-gray-500">
                        Generate detailed reports and analytics automatically, saving you hours of manual work every
                        week.
                    </p>
                </div>
                <div
                    class="bg-white p-8 rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 text-center">
                    <div class="text-indigo-600 text-4xl mb-4">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 mx-auto" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h-4a2 2 0 01-2-2v-2H9a2 2 0 01-2-2V8a2 2 0 012-2h4a2 2 0 012 2v2h2a2 2 0 012 2v4a2 2 0 01-2 2zM9 16h6V8H9v8z" />
                        </svg>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold mb-2">Manage Employess</h3>
                    <p class="text-gray-500">
                        Manage Employees Easily using the Dashboard , Manage Roles, Departments and other employee
                        details.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section id="testimonials" class="py-20 bg-white wow animate-fadeIn">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-800 mb-4">Trusted by Teams of All Sizes</h2>
                <p class="text-gray-600 max-w-2xl mx-auto">
                    Don't just take our word for it. See what our happy customers have to say.
                </p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm">
                    <p class="text-gray-600 italic mb-4">"Attentify has completely transformed how we manage our team's
                        attendance. The dashboard is intuitive, and the reporting saves us so much time."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full mr-4 text-center flex items-center"><i
                                class="fa-solid fa-user text-2xl"></i></div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Jane Doe</h4>
                            <p class="text-sm text-gray-500">HR Manager, TechCorp</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm">
                    <p class="text-gray-600 italic mb-4">"The mobile-friendly interface is a game-changer. Our field
                        staff can now easily clock in and out, and we have real-time visibility."</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full mr-4 text-center flex items-center"><i
                                class="fa-solid fa-user text-2xl"></i></div>
                        <div>
                            <h4 class="font-semibold text-gray-800">John Smith</h4>
                            <p class="text-sm text-gray-500">Project Lead, Innovate Inc.</p>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 p-8 rounded-lg shadow-sm">
                    <p class="text-gray-600 italic mb-4">"I love the clean design and the automated reports. It's so
                        much easier to handle payroll now. Highly recommend Attentify!"</p>
                    <div class="flex items-center">
                        <div class="w-12 h-12 rounded-full mr-4 text-center flex items-center"><i
                                class="fa-solid fa-user text-2xl"></i></div>
                        <div>
                            <h4 class="font-semibold text-gray-800">Emily White</h4>
                            <p class="text-sm text-gray-500">Operations Director, Global Solutions</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="bg-indigo-600 text-white py-20 wow animate-fadeIn" id="pricing">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl md:text-4xl font-bold mb-4">Ready to simplify your attendance?</h2>
            <p class="text-lg md:text-xl max-w-2xl mx-auto mb-8 opacity-90">
                Join thousands of organizations who trust Attentify to manage their attendance effortlessly.
            </p>
            <a href="./main/company-login.php"
                class="bg-white text-indigo-600 font-semibold py-3 px-8 rounded-lg shadow-lg hover:shadow-xl hover:bg-gray-100 transition-all duration-300">
                Get Started for Free
            </a>
        </div>
    </section>

    <footer class="bg-gray-800 text-gray-300 py-12 wow animate-fadeIn" id="contact">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div>
                    <h3 class="text-xl font-bold text-white mb-4">Attentify</h3>
                    <p class="text-sm">Effortless attendance management for modern teams.</p>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Quick Links</h4>
                    <ul class="space-y-2">
                        <li><a href="#features" class="hover:text-white transition-colors duration-300">Features</a>
                        </li>
                        <li><a href="#testimonials"
                                class="hover:text-white transition-colors duration-300">Testimonials</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Pricing</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Blog</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Legal</h4>
                    <ul class="space-y-2">
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Privacy Policy</a></li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Terms of Service</a>
                        </li>
                        <li><a href="#" class="hover:text-white transition-colors duration-300">Cookie Policy</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="text-lg font-semibold text-white mb-4">Contact Us</h4>
                    <p class="text-sm">Email: <a href="mailto:info@attentify.com"
                            class="hover:underline">info@attentify.com</a></p>
                    <p class="text-sm">Phone: +1 (123) 456-7890</p>
                </div>
            </div>
            <div class="border-t border-gray-700 mt-8 pt-8 text-center">
                <p>&copy; 2025 Attentify. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"
        integrity="sha512-Eak/29OTpb36LLo2r47IpVzPBLXnAMPAVypbSZiZ4Qkf8p/7S/XRG5xp7OKWPPYfJT6metI+IORkR5G8F900+g=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script>
        wow = new WOW({
            boxClass: 'wow',      // default
            animateClass: 'animate', // default
            offset: 0,          // default
            mobile: true,       // default
            live: true        // default
        });
        wow.init();
    </script>
</body>

</html>