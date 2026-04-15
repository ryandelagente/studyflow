<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow - The Ultimate Student Productivity Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .faq-toggle { transition: transform 0.3s ease-in-out; }
        .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.5s ease-in-out; }
    </style>
</head>
<body class="bg-white text-gray-800">

    <header class="bg-white/90 backdrop-blur-md fixed top-0 left-0 right-0 z-50 border-b border-gray-200">
        <div class="container mx-auto px-6 py-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold text-purple-600">StudyFlow</h1>
            <nav class="hidden md:flex space-x-8 items-center">
                <a href="#features" class="text-gray-600 hover:text-purple-600">Features</a>
                <a href="#pricing" class="text-gray-600 hover:text-purple-600">Pricing</a>
                <a href="#testimonials" class="text-gray-600 hover:text-purple-600">Testimonials</a>
                <a href="#faq" class="text-gray-600 hover:text-purple-600">FAQ</a>
            </nav>
            <a href="index.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-purple-700 transition-colors">
                Go to App
            </a>
        </div>
    </header>

    <main>
        <section class="bg-purple-50 pt-32 pb-20">
            <div class="container mx-auto px-6 text-center">
                <h2 class="text-4xl md:text-6xl font-extrabold text-gray-900 leading-tight">
                    The Ultimate <span class="text-purple-600">Productivity App</span> for Students
                </h2>
                <p class="mt-6 text-lg text-gray-600 max-w-2xl mx-auto">
                    Stop juggling apps. To-dos, notes, flashcards, and an AI tutor—all in one place. Streamline your studies and achieve your academic goals faster.
                </p>
                <a href="register.php" class="mt-10 inline-block bg-purple-600 text-white px-8 py-4 rounded-lg font-semibold text-lg hover:bg-purple-700 transition-transform transform hover:scale-105 shadow-lg">
                    Get Started for Free
                </a>
            </div>
        </section>

        <section id="features" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-16">
                    <h3 class="text-3xl font-bold text-gray-800">Boost productivity with an all-in-one toolkit</h3>
                    <p class="text-gray-500 mt-2 max-w-2xl mx-auto">StudyFlow is designed to make you more efficient and organized, giving you more time to focus on what matters most: learning.</p>
                </div>
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <img src="https://placehold.co/600x400/E9D5FF/3730A3?text=App+Feature" alt="StudyFlow Feature" class="rounded-lg shadow-xl">
                    </div>
                    <div>
                        <ul class="space-y-4">
                            <li class="flex items-start"><span class="text-green-500 mr-3 mt-1">&#10003;</span><span><strong>AI-Powered Assistance:</strong> Get instant answers and guidance with our integrated AI Tutor.</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-3 mt-1">&#10003;</span><span><strong>Seamless Organization:</strong> Manage tasks, notes, and study materials effortlessly in one unified dashboard.</span></li>
                            <li class="flex items-start"><span class="text-green-500 mr-3 mt-1">&#10003;</span><span><strong>Effective Learning Tools:</strong> Create and study with flashcards, track your goals, and manage your schedule.</span></li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <section id="pricing" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h3 class="text-4xl font-extrabold text-gray-800">Pricing</h3>
                    <p class="text-gray-500 mt-2">Choose which suite is right for you.</p>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-6xl mx-auto items-center">
                    <div class="bg-white p-8 rounded-2xl shadow-md border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-500 uppercase">Basic</h3>
                        <p class="text-5xl font-extrabold my-4">$4.99<span class="text-lg font-medium text-gray-500">/month</span></p>
                        <ul class="text-left space-y-3 mt-8 text-gray-600">
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Single User</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> 1GB Storage</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Create & Share Documents</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Create & Share Spreadsheets</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Quick Share</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Image Editor</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Digital Asset Management</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Calendar</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Address Book</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Basic Support</li>
                        </ul>
                        <a href="#" class="mt-8 w-full block text-center bg-white border border-purple-600 text-purple-600 py-3 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                            Free Trial
                        </a>
                    </div>
                    <div class="bg-gray-800 text-white p-8 rounded-2xl shadow-xl relative transform lg:scale-110">
                        <div class="absolute top-0 -translate-y-1/2 left-1/2 -translate-x-1/2">
                            <span class="bg-purple-600 text-white text-xs font-bold uppercase px-4 py-1 rounded-full">Popular</span>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-400 uppercase">Standard</h3>
                        <p class="text-5xl font-extrabold my-4">$9.99<span class="text-lg font-medium text-gray-400">/month</span></p>
                        <ul class="text-left space-y-3 mt-8 text-gray-300">
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> 2 Users</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> 5GB Storage</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Create & Share Documents</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Create & Share Spreadsheets</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Quick Share</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Image Editor</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Digital Asset Management</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Calendar</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Address Book</li>
                            <li class="flex items-center"><span class="text-green-400 mr-3">&#10003;</span> Standard Support</li>
                        </ul>
                        <a href="#" class="mt-8 w-full block text-center bg-white text-gray-800 py-3 rounded-lg font-semibold hover:bg-gray-200 transition-colors">
                            Free Trial
                        </a>
                    </div>
                    <div class="bg-white p-8 rounded-2xl shadow-md border border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-500 uppercase">Premium</h3>
                        <p class="text-5xl font-extrabold my-4">$19.99<span class="text-lg font-medium text-gray-500">/month</span></p>
                        <ul class="text-left space-y-3 mt-8 text-gray-600">
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Unlimited Users</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> 10GB Storage</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Create & Share Documents</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Create & Share Spreadsheets</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Quick Share</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Image Editor</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Digital Asset Management</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Calendar</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Address Book</li>
                            <li class="flex items-center"><span class="text-green-500 mr-3">&#10003;</span> Premium Support</li>
                        </ul>
                        <a href="#" class="mt-8 w-full block text-center bg-white border border-purple-600 text-purple-600 py-3 rounded-lg font-semibold hover:bg-purple-50 transition-colors">
                            Free Trial
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section id="testimonials" class="py-20 bg-white">
            <div class="container mx-auto px-6">
                <div class="text-center mb-12">
                    <h3 class="text-4xl font-extrabold text-gray-800">Testimonials</h3>
                    <p class="text-gray-500 mt-2">What our customers say.</p>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-6xl mx-auto">
                    <div class="bg-gray-50 p-8 rounded-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-lg bg-purple-600 text-white flex items-center justify-center text-xl font-bold">S</div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-900">Sarah Malik</p>
                                <p class="text-gray-500 text-sm">Student, UT</p>
                            </div>
                        </div>
                        <p class="text-gray-600">"It works well and has all the functions I need. I would recommend it to anyone who needs a simple and easy to use document editor."</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-lg bg-indigo-600 text-white flex items-center justify-center text-xl font-bold">J</div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-900">James Larsson</p>
                                <p class="text-gray-500 text-sm">Content Writer, Ray Media</p>
                            </div>
                        </div>
                        <p class="text-gray-600">"I love this product! This is efficient and productive. I can create documents and share them with my colleagues. I can also export them."</p>
                    </div>
                    <div class="bg-gray-50 p-8 rounded-lg">
                        <div class="flex items-center mb-6">
                            <div class="w-12 h-12 rounded-lg bg-blue-600 text-white flex items-center justify-center text-xl font-bold">A</div>
                            <div class="ml-4">
                                <p class="font-semibold text-gray-900">Alice Holmes</p>
                                <p class="text-gray-500 text-sm">Teacher, UT</p>
                            </div>
                        </div>
                        <p class="text-gray-600">"I use this product to share assignments with my students. It is very easy to use and I can see the logs who accessed them."</p>
                    </div>
                </div>
            </div>
        </section>

        <section id="faq" class="py-20 bg-gray-50">
            <div class="container mx-auto px-6 max-w-3xl">
                <div class="text-center mb-12">
                    <p class="text-purple-600 font-semibold">FAQ</p>
                    <h3 class="text-4xl font-extrabold text-gray-800 mt-2">Frequently Asked Questions</h3>
                    <p class="text-gray-500 mt-2">Your questions answered.</p>
                </div>
                <div class="border border-gray-200 rounded-lg">
                    <div class="border-b border-gray-200">
                        <button class="faq-btn flex justify-between items-center w-full p-6 text-left">
                            <span class="font-semibold text-lg">What is the difference between the monthly and yearly plans?</span>
                            <span class="text-purple-600">
                                <svg class="faq-arrow w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                        <div class="faq-content px-6 pb-4">
                            <p class="text-gray-600">The monthly plan is billed monthly and the yearly plan is billed yearly. The yearly plan is 10% off the monthly price, offering a better value for long-term commitment.</p>
                        </div>
                    </div>
                    <div class="border-b border-gray-200">
                        <button class="faq-btn flex justify-between items-center w-full p-6 text-left">
                            <span class="font-semibold text-lg">How do I cancel my subscription?</span>
                            <span class="text-purple-600">
                                <svg class="faq-arrow w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                        <div class="faq-content px-6 pb-4">
                            <p class="text-gray-600">You can cancel your subscription at any time from your account settings page. Once canceled, you will retain access to your plan's features until the end of your current billing cycle.</p>
                        </div>
                    </div>
                    <div class="border-b border-gray-200">
                        <button class="faq-btn flex justify-between items-center w-full p-6 text-left">
                            <span class="font-semibold text-lg">How do I start a trial?</span>
                            <span class="text-purple-600">
                                <svg class="faq-arrow w-6 h-6 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </span>
                        </button>
                        <div class="faq-content px-6 pb-4">
                            <p class="text-gray-600">You can start a free trial by choosing any of our paid plans. No credit card is required to begin. The trial gives you full access to all features in the selected plan for 14 days.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
         <section id="signup" class="py-20 bg-white">
            <div class="container mx-auto max-w-6xl grid grid-cols-1 lg:grid-cols-2 gap-12 p-6 items-center">
                <div class="flex flex-col justify-center">
                    <h2 class="text-3xl font-bold text-gray-900">Increase your productivity with StudyFlow!</h2>
                    <p class="text-gray-600 mt-2">
                        Get Started with StudyFlow for free. No credit card required. No commitment. Cancel anytime.
                    </p>
                    <a href="register.php" class="mt-8 w-full block text-center bg-purple-600 text-white py-3 rounded-lg font-semibold hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500">
                        Sign up for free
                    </a>
                    <p class="text-gray-600 mt-4 text-center">
                        Already have an account? <a href="login.php" class="text-purple-600 font-semibold hover:underline">Sign In</a>
                    </p>
                </div>

                <div class="hidden lg:flex items-center justify-center">
                     <img src="https://placehold.co/600x500/E9D5FF/3730A3?text=StudyFlow\nDashboard" alt="StudyFlow App Screenshot" class="rounded-lg shadow-xl">
                </div>
            </div>
        </section>

    </main>

    <footer class="bg-gray-50 border-t border-gray-200">
        <div class="container mx-auto px-6 py-12">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                <div class="md:col-span-1">
                    <h2 class="text-2xl font-bold text-purple-600">StudyFlow</h2>
                    <p class="text-gray-500 mt-2 text-sm">
                        StudyFlow improves student productivity. It lets students set goals and achieve those efficiently through a powerful set of features and tools.
                    </p>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 tracking-wider uppercase">Company</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="#" class="text-gray-500 hover:text-purple-600">About</a></li>
                        <li><a href="#pricing" class="text-gray-500 hover:text-purple-600">Plans & Pricing</a></li>
                        <li><a href="#testimonials" class="text-gray-500 hover:text-purple-600">Testimonials</a></li>
                        <li><a href="#faq" class="text-gray-500 hover:text-purple-600">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 tracking-wider uppercase">Legal</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="#" class="text-gray-500 hover:text-purple-600">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-purple-600">Terms of Service</a></li>
                    </ul>
                </div>
                <div>
                    <h3 class="font-semibold text-gray-800 tracking-wider uppercase">Account</h3>
                    <ul class="mt-4 space-y-2 text-sm">
                        <li><a href="index.php" class="text-gray-500 hover:text-purple-600">Sign In</a></li>
                        <li><a href="register.php" class="text-gray-500 hover:text-purple-600">Create an Account</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-purple-600">Reset Password</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-12 border-t border-gray-200 pt-6 flex flex-col md:flex-row justify-between items-center text-sm">
                <p class="text-gray-500">&copy; <?php echo date("Y"); ?> StudyFlow. All Rights Reserved.</p>
                <div class="flex space-x-4 mt-4 md:mt-0">
                    <a href="#" class="text-gray-400 hover:text-purple-600">FB</a>
                    <a href="#" class="text-gray-400 hover:text-purple-600">TW</a>
                    <a href="#" class="text-gray-400 hover:text-purple-600">GH</a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        let openFaqContent = null;
        document.querySelectorAll('.faq-btn').forEach(button => {
            button.addEventListener('click', () => {
                const content = button.nextElementSibling;
                const arrow = button.querySelector('.faq-arrow');
                if (openFaqContent && openFaqContent !== content) {
                    openFaqContent.style.maxHeight = null;
                    openFaqContent.previousElementSibling.querySelector('.faq-arrow').style.transform = 'rotate(0deg)';
                }
                if (content.style.maxHeight) {
                    content.style.maxHeight = null;
                    arrow.style.transform = 'rotate(0deg)';
                    openFaqContent = null;
                } else {
                    content.style.maxHeight = content.scrollHeight + "px";
                    arrow.style.transform = 'rotate(180deg)';
                    openFaqContent = content;
                }
            });
        });
    </script>
</body>
</html>