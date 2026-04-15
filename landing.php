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
        .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-in-out; }
        .gradient-text { background: linear-gradient(135deg, #7c3aed, #a855f7); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
        .hero-glow { background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(124,58,237,0.15), transparent); }
        .feature-card:hover { transform: translateY(-4px); }
        .feature-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
    </style>
</head>
<body class="bg-white text-gray-800">

<!-- ===================== NAVBAR ===================== -->
<header class="bg-white/90 backdrop-blur-md fixed top-0 left-0 right-0 z-50 border-b border-gray-100 shadow-sm">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <h1 class="text-2xl font-bold text-purple-600">StudyFlow</h1>
        </div>
        <nav class="hidden md:flex space-x-8 items-center text-sm font-medium">
            <a href="#features"      class="text-gray-600 hover:text-purple-600 transition-colors">Features</a>
            <a href="#how-it-works"  class="text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="#pricing"       class="text-gray-600 hover:text-purple-600 transition-colors">Pricing</a>
            <a href="#testimonials"  class="text-gray-600 hover:text-purple-600 transition-colors">Testimonials</a>
            <a href="#faq"           class="text-gray-600 hover:text-purple-600 transition-colors">FAQ</a>
        </nav>
        <div class="flex items-center gap-3">
            <a href="login.php"    class="text-sm font-medium text-gray-600 hover:text-purple-600 transition-colors">Sign In</a>
            <a href="register.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition-colors shadow-sm">Get Started Free</a>
        </div>
    </div>
</header>

<main>

<!-- ===================== HERO ===================== -->
<section class="hero-glow bg-gradient-to-b from-purple-50 to-white pt-32 pb-12 overflow-hidden">
    <div class="container mx-auto px-6">
        <div class="text-center max-w-3xl mx-auto mb-12">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-4 tracking-wide uppercase">Trusted by 500+ Students</span>
            <h2 class="text-5xl md:text-6xl font-extrabold text-gray-900 leading-tight">
                Study Smarter with <span class="gradient-text">AI-Powered</span> Productivity
            </h2>
            <p class="mt-6 text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
                To-dos, notes, flashcards, study goals, and an AI tutor — all in one place. Stop juggling apps and start achieving more.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="inline-block bg-purple-600 text-white px-8 py-4 rounded-xl font-semibold text-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-purple-200 hover:-translate-y-0.5">
                    Get Started for Free
                </a>
                <a href="login.php" class="inline-block bg-white border border-gray-200 text-gray-700 px-8 py-4 rounded-xl font-semibold text-lg hover:border-purple-300 hover:text-purple-600 transition-all shadow-sm">
                    Sign In →
                </a>
            </div>
        </div>

        <!-- App Screenshot Mockup -->
        <div class="relative max-w-5xl mx-auto">
            <!-- Browser chrome -->
            <div class="bg-gray-800 rounded-t-2xl px-4 py-3 flex items-center gap-2 shadow-2xl">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="ml-4 flex-1 bg-gray-700 rounded-full px-4 py-1 text-xs text-gray-400 max-w-xs">localhost/studyflow/index.php</div>
            </div>
            <!-- Dashboard SVG mockup -->
            <div class="bg-gray-100 rounded-b-2xl shadow-2xl overflow-hidden border border-gray-200" style="height: 420px;">
                <div class="flex h-full">
                    <!-- Sidebar -->
                    <div class="w-52 bg-gray-800 flex flex-col p-4 shrink-0">
                        <div class="text-white font-bold text-lg mb-6">StudyFlow</div>
                        <?php
                        $nav_items = [
                            ['icon' => 'M3 7h18M3 12h18M3 17h18', 'label' => 'Dashboard',   'active' => true],
                            ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label' => 'To-dos', 'active' => false],
                            ['icon' => 'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'label' => 'Study Goals', 'active' => false],
                            ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Calendar', 'active' => false],
                            ['icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'label' => 'AI Tutor', 'active' => false],
                        ];
                        foreach ($nav_items as $item):
                        ?>
                        <div class="flex items-center gap-2 px-3 py-2 rounded-lg mb-1 <?php echo $item['active'] ? 'bg-gray-700 text-white' : 'text-gray-400'; ?>">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $item['icon']; ?>"/></svg>
                            <span class="text-xs font-medium"><?php echo $item['label']; ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <!-- Main content -->
                    <div class="flex-1 p-6 overflow-hidden">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-base font-bold text-gray-800">Dashboard</h3>
                            <div class="w-7 h-7 rounded-full bg-purple-600 flex items-center justify-center text-white text-xs font-bold">A</div>
                        </div>
                        <!-- Stats row -->
                        <div class="grid grid-cols-4 gap-3 mb-4">
                            <?php
                            $stats = [
                                ['label' => 'Tasks Today',   'value' => '8',   'color' => 'bg-purple-50', 'text' => 'text-purple-600'],
                                ['label' => 'Study Goals',   'value' => '3',   'color' => 'bg-blue-50',   'text' => 'text-blue-600'],
                                ['label' => 'Studied Today', 'value' => '2h 14m', 'color' => 'bg-green-50', 'text' => 'text-green-600'],
                                ['label' => 'Flashcards',    'value' => '24',  'color' => 'bg-orange-50', 'text' => 'text-orange-600'],
                            ];
                            foreach ($stats as $s): ?>
                            <div class="<?php echo $s['color']; ?> rounded-lg p-3">
                                <p class="text-xs text-gray-500"><?php echo $s['label']; ?></p>
                                <p class="text-lg font-bold <?php echo $s['text']; ?>"><?php echo $s['value']; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Tasks + Goals side by side -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Recent To-dos</p>
                                <?php foreach (['Biology Chapter 4', 'Math Homework', 'History Essay'] as $i => $task): ?>
                                <div class="flex items-center gap-2 py-1">
                                    <div class="w-3.5 h-3.5 rounded border-2 <?php echo $i === 0 ? 'bg-purple-600 border-purple-600' : 'border-gray-300'; ?> shrink-0"></div>
                                    <span class="text-xs <?php echo $i === 0 ? 'line-through text-gray-400' : 'text-gray-700'; ?>"><?php echo $task; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Study Goals</p>
                                <?php
                                $goals = [['label' => 'Pass Biology Midterm', 'pct' => 65], ['label' => 'Math Problem Sets', 'pct' => 40]];
                                foreach ($goals as $g): ?>
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs text-gray-600 mb-1"><span><?php echo $g['label']; ?></span><span><?php echo $g['pct']; ?>%</span></div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5"><div class="bg-purple-600 h-1.5 rounded-full" style="width:<?php echo $g['pct']; ?>%"></div></div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== STATS BAR ===================== -->
<section class="py-12 bg-white border-y border-gray-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <?php
            $stats = [
                ['value' => '500+',  'label' => 'Active Students'],
                ['value' => '10K+',  'label' => 'Tasks Completed'],
                ['value' => '50K+',  'label' => 'Flashcards Created'],
                ['value' => '4.9★',  'label' => 'Average Rating'],
            ];
            foreach ($stats as $s): ?>
            <div>
                <p class="text-4xl font-extrabold text-purple-600"><?php echo $s['value']; ?></p>
                <p class="text-gray-500 text-sm mt-1"><?php echo $s['label']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== FEATURES ===================== -->
<section id="features" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Everything you need</span>
            <h3 class="text-4xl font-extrabold text-gray-900">Built for students who want results</h3>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">One platform that replaces five apps. Organised, powerful, and powered by AI.</p>
        </div>

        <!-- Feature 1: AI Tutor -->
        <div class="grid md:grid-cols-2 gap-16 items-center mb-24">
            <div>
                <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">AI Tutor</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">Your personal tutor, available 24/7</h4>
                <p class="text-gray-500 leading-relaxed mb-6">Ask questions, get explanations, and work through problems with an AI that remembers your conversation. No more waiting for office hours.</p>
                <ul class="space-y-3">
                    <?php foreach (['Multi-turn conversations with full history', 'Explains concepts step-by-step', 'Works for any subject or course'] as $item): ?>
                    <li class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo $item; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1677442135703-1787eea5ce01?auto=format&fit=crop&w=800&q=80"
                     alt="AI Tutor chat interface"
                     class="rounded-2xl shadow-2xl w-full object-cover"
                     style="height:340px; object-position: center top;"
                     onerror="this.src='https://images.unsplash.com/photo-1516321318423-f06f85e504b3?auto=format&fit=crop&w=800&q=80'">
                <div class="absolute -bottom-4 -left-4 bg-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-800">AI Response</p>
                        <p class="text-xs text-gray-400">Instant answers</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature 2: Flashcards -->
        <div class="grid md:grid-cols-2 gap-16 items-center mb-24">
            <div class="order-2 md:order-1 relative">
                <img src="https://images.unsplash.com/photo-1434030216411-0b6aca9b00bf?auto=format&fit=crop&w=800&q=80"
                     alt="Student studying with flashcards"
                     class="rounded-2xl shadow-2xl w-full object-cover"
                     style="height:340px;">
                <div class="absolute -top-4 -right-4 bg-white rounded-xl shadow-lg px-4 py-3">
                    <p class="text-xs text-gray-500">Cards Generated</p>
                    <p class="text-2xl font-extrabold text-purple-600">24 ✦</p>
                </div>
            </div>
            <div class="order-1 md:order-2">
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">Flashcards</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">AI-generated flashcards in seconds</h4>
                <p class="text-gray-500 leading-relaxed mb-6">Type a topic and let AI generate a full set of Q&A cards. Study them with our interactive flip mode, or export for offline review.</p>
                <ul class="space-y-3">
                    <?php foreach (['Generate cards from any topic instantly', 'Interactive flip-card study mode', 'Organise into collections by subject'] as $item): ?>
                    <li class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo $item; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <!-- Feature 3: Study Goals + Tasks -->
        <div class="grid md:grid-cols-2 gap-16 items-center">
            <div>
                <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">Goals & Tasks</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">Set goals. Break them into steps. Achieve them.</h4>
                <p class="text-gray-500 leading-relaxed mb-6">AI suggests measurable study goals for your subject, then breaks each goal into actionable to-do tasks. Track your progress with visual timers.</p>
                <ul class="space-y-3">
                    <?php foreach (['AI-suggested study goals per subject', 'Auto-generate task lists from goals', 'Study timer with session tracking'] as $item): ?>
                    <li class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo $item; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="relative">
                <img src="https://images.unsplash.com/photo-1484480974693-6ca0a78fb36b?auto=format&fit=crop&w=800&q=80"
                     alt="Student planning study goals"
                     class="rounded-2xl shadow-2xl w-full object-cover"
                     style="height:340px; object-position: center top;">
                <div class="absolute -bottom-4 -right-4 bg-white rounded-xl shadow-lg px-4 py-3">
                    <p class="text-xs text-gray-500">Today's Progress</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-24 h-2 bg-gray-100 rounded-full"><div class="w-16 h-2 bg-purple-600 rounded-full"></div></div>
                        <span class="text-xs font-bold text-gray-700">65%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ===================== FEATURE CARDS GRID ===================== -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
            <?php
            $cards = [
                ['icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'title' => 'Smart Notes', 'desc' => 'Rich-text notes with AI writing and summarise assistance.', 'color' => 'bg-purple-100 text-purple-600'],
                ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'title' => 'Calendar', 'desc' => 'Weekly calendar view to schedule classes, exams, and study sessions.', 'color' => 'bg-blue-100 text-blue-600'],
                ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'title' => 'Contacts', 'desc' => 'Address book to store classmates, professors, and study partners.', 'color' => 'bg-pink-100 text-pink-600'],
                ['icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'title' => 'Assignments', 'desc' => 'Track project status, deadlines, and linked study goals.', 'color' => 'bg-orange-100 text-orange-600'],
                ['icon' => 'M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'title' => 'Sheets', 'desc' => 'Spreadsheet editor for data, budgets, and study schedules.', 'color' => 'bg-teal-100 text-teal-600'],
                ['icon' => 'M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'title' => 'File Sharing', 'desc' => 'Upload and share documents, images, and study materials.', 'color' => 'bg-indigo-100 text-indigo-600'],
            ];
            foreach ($cards as $c): ?>
            <div class="feature-card bg-white rounded-xl p-6 shadow-sm border border-gray-100 hover:shadow-md">
                <div class="w-10 h-10 rounded-lg <?php echo $c['color']; ?> flex items-center justify-center mb-4">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $c['icon']; ?>"/></svg>
                </div>
                <h5 class="font-bold text-gray-800 mb-1"><?php echo $c['title']; ?></h5>
                <p class="text-gray-500 text-sm"><?php echo $c['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== HOW IT WORKS ===================== -->
<section id="how-it-works" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Simple to start</span>
            <h3 class="text-4xl font-extrabold text-gray-900">Up and running in 3 steps</h3>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php
            $steps = [
                ['num' => '01', 'title' => 'Create your account', 'desc' => 'Sign up for free — no credit card required. Your private workspace is created instantly.', 'img' => 'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?auto=format&fit=crop&w=400&q=80'],
                ['num' => '02', 'title' => 'Add your subjects', 'desc' => 'Set up study goals, create flashcard collections, and add your upcoming assignments in minutes.', 'img' => 'https://images.unsplash.com/photo-1501504905252-473c47e087f8?auto=format&fit=crop&w=400&q=80'],
                ['num' => '03', 'title' => 'Study with AI', 'desc' => 'Chat with the AI Tutor, generate flashcards, and get a personalised task breakdown for every goal.', 'img' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?auto=format&fit=crop&w=400&q=80'],
            ];
            foreach ($steps as $step): ?>
            <div class="text-center">
                <div class="relative mb-6 inline-block">
                    <img src="<?php echo $step['img']; ?>" alt="<?php echo $step['title']; ?>"
                         class="w-32 h-32 rounded-2xl object-cover mx-auto shadow-lg"
                         onerror="this.parentElement.innerHTML='<div class=\'w-32 h-32 rounded-2xl bg-purple-100 mx-auto flex items-center justify-center\'><span class=\'text-4xl font-extrabold text-purple-300\'><?php echo $step['num']; ?></span></div>'">
                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-extrabold shadow-md">
                        <?php echo (int)$step['num']; ?>
                    </div>
                </div>
                <h5 class="font-bold text-gray-900 text-lg mb-2"><?php echo $step['title']; ?></h5>
                <p class="text-gray-500 text-sm leading-relaxed"><?php echo $step['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== PRICING ===================== -->
<section id="pricing" class="py-24 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Pricing</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Choose your plan</h3>
            <p class="text-gray-500 mt-2">Start free. Upgrade when you need more.</p>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 max-w-5xl mx-auto items-stretch">
            <!-- Basic -->
            <div class="bg-white p-8 rounded-2xl shadow-md border border-gray-200 flex flex-col">
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Basic</h3>
                    <p class="text-5xl font-extrabold my-4 text-gray-900">$4.99<span class="text-base font-medium text-gray-400">/mo</span></p>
                    <p class="text-gray-500 text-sm mb-6">Great for solo students getting started.</p>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        <?php foreach (['1 User', '1 GB Storage', 'Notes & Sheets', 'Calendar & Contacts', 'Basic Support'] as $f): ?>
                        <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span><?php echo $f; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="register.php" class="mt-auto block text-center border border-purple-600 text-purple-600 py-3 rounded-xl font-semibold hover:bg-purple-50 transition-colors">Start Free Trial</a>
            </div>
            <!-- Standard (popular) -->
            <div class="bg-gray-900 text-white p-8 rounded-2xl shadow-2xl relative flex flex-col transform lg:scale-105">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <span class="bg-purple-600 text-white text-xs font-bold uppercase px-4 py-1.5 rounded-full shadow">Most Popular</span>
                </div>
                <div>
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-widest">Standard</h3>
                    <p class="text-5xl font-extrabold my-4">$9.99<span class="text-base font-medium text-gray-400">/mo</span></p>
                    <p class="text-gray-400 text-sm mb-6">For students who want the full experience.</p>
                    <ul class="space-y-3 text-sm text-gray-300 mb-8">
                        <?php foreach (['2 Users', '5 GB Storage', 'Everything in Basic', 'AI Tutor + Flashcards', 'AI Goals & Tasks', 'Standard Support'] as $f): ?>
                        <li class="flex items-center gap-2"><span class="text-green-400 font-bold">✓</span><?php echo $f; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="register.php" class="mt-auto block text-center bg-purple-600 text-white py-3 rounded-xl font-semibold hover:bg-purple-700 transition-colors shadow-lg">Start Free Trial</a>
            </div>
            <!-- Premium -->
            <div class="bg-white p-8 rounded-2xl shadow-md border border-gray-200 flex flex-col">
                <div>
                    <h3 class="text-sm font-bold text-gray-500 uppercase tracking-widest">Premium</h3>
                    <p class="text-5xl font-extrabold my-4 text-gray-900">$19.99<span class="text-base font-medium text-gray-400">/mo</span></p>
                    <p class="text-gray-500 text-sm mb-6">For schools, teams, and power users.</p>
                    <ul class="space-y-3 text-sm text-gray-600 mb-8">
                        <?php foreach (['Unlimited Users', '10 GB Storage', 'Everything in Standard', 'File Sharing & Resources', 'Access Logs', 'Priority Support'] as $f): ?>
                        <li class="flex items-center gap-2"><span class="text-green-500 font-bold">✓</span><?php echo $f; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <a href="register.php" class="mt-auto block text-center border border-purple-600 text-purple-600 py-3 rounded-xl font-semibold hover:bg-purple-50 transition-colors">Start Free Trial</a>
            </div>
        </div>
    </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section id="testimonials" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Testimonials</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Students love StudyFlow</h3>
            <p class="text-gray-500 mt-2">Real stories from real students.</p>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php
            $testimonials = [
                ['name' => 'Sarah Malik',   'role' => 'Biology student, UT',    'quote' => 'The AI Tutor is like having a professor available at 2am. I aced my midterm after using it to review Mitosis for just 30 minutes.',       'photo' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=100&q=80', 'stars' => 5],
                ['name' => 'James Larsson', 'role' => 'Computer Science, MIT',  'quote' => 'I generate flashcards for every new topic in seconds. My retention has improved so much since I stopped hand-writing everything.',           'photo' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=100&q=80', 'stars' => 5],
                ['name' => 'Alice Holmes',  'role' => 'Teacher, Central High',  'quote' => 'I share assignments with my students directly through StudyFlow. The access logs show me exactly who\'s reviewed the material.',            'photo' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?auto=format&fit=crop&w=100&q=80', 'stars' => 5],
            ];
            foreach ($testimonials as $t): ?>
            <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 hover:shadow-md transition-shadow">
                <!-- Stars -->
                <div class="flex gap-1 mb-4">
                    <?php for ($i = 0; $i < $t['stars']; $i++): ?>
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">"<?php echo $t['quote']; ?>"</p>
                <div class="flex items-center gap-3">
                    <img src="<?php echo $t['photo']; ?>" alt="<?php echo $t['name']; ?>"
                         class="w-10 h-10 rounded-full object-cover"
                         onerror="this.outerHTML='<div class=\'w-10 h-10 rounded-full bg-purple-600 flex items-center justify-center text-white font-bold text-sm\'><?php echo substr($t['name'],0,1); ?></div>'">
                    <div>
                        <p class="font-semibold text-gray-900 text-sm"><?php echo $t['name']; ?></p>
                        <p class="text-gray-400 text-xs"><?php echo $t['role']; ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== FAQ ===================== -->
<section id="faq" class="py-24 bg-gray-50">
    <div class="container mx-auto px-6 max-w-3xl">
        <div class="text-center mb-12">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">FAQ</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Frequently Asked Questions</h3>
        </div>
        <div class="space-y-4">
            <?php
            $faqs = [
                ['q' => 'Is StudyFlow really free to start?',           'a' => 'Yes — create an account and start using all core features immediately. No credit card required. Premium plans unlock more storage, users, and AI credits.'],
                ['q' => 'What AI model powers the tutor?',              'a' => 'StudyFlow uses OpenRouter to access a range of free and premium AI models. Admins can switch models from the API Settings page at any time.'],
                ['q' => 'Can multiple students share one workspace?',   'a' => 'Yes. When you register you create a workspace (tenant). You can invite classmates as members. Each workspace\'s data is fully isolated from others.'],
                ['q' => 'How do I cancel my subscription?',             'a' => 'Cancel at any time from your billing settings. You keep access to paid features until the end of your billing cycle — no sudden cut-off.'],
                ['q' => 'Is my data private?',                          'a' => 'All data is scoped to your workspace. Other tenants cannot access your notes, goals, or files. We never use your content to train AI models.'],
            ];
            foreach ($faqs as $faq): ?>
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm">
                <button class="faq-btn flex justify-between items-center w-full p-6 text-left hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-800"><?php echo $faq['q']; ?></span>
                    <svg class="faq-arrow w-5 h-5 text-purple-500 shrink-0 ml-4 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content px-6">
                    <p class="text-gray-500 text-sm pb-5 leading-relaxed"><?php echo $faq['a']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ===================== CTA ===================== -->
<section class="py-24 relative overflow-hidden">
    <div class="absolute inset-0">
        <img src="https://images.unsplash.com/photo-1522202176988-66273c9f79f4?auto=format&fit=crop&w=1600&q=80"
             alt="Students studying together"
             class="w-full h-full object-cover"
             onerror="this.style.display='none'">
        <div class="absolute inset-0 bg-purple-900/80"></div>
    </div>
    <div class="relative container mx-auto px-6 text-center">
        <h3 class="text-4xl md:text-5xl font-extrabold text-white mb-4">Ready to study smarter?</h3>
        <p class="text-purple-200 text-lg mb-8 max-w-xl mx-auto">Join hundreds of students already using StudyFlow to hit their academic goals.</p>
        <a href="register.php" class="inline-block bg-white text-purple-700 px-10 py-4 rounded-xl font-bold text-lg hover:bg-purple-50 transition-all shadow-xl hover:-translate-y-0.5">
            Get Started for Free →
        </a>
        <p class="text-purple-300 text-sm mt-4">No credit card required · Cancel anytime</p>
    </div>
</section>

</main>

<!-- ===================== FOOTER ===================== -->
<footer class="bg-gray-900 text-gray-400">
    <div class="container mx-auto px-6 py-14">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-10">
            <div class="md:col-span-1">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 bg-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <span class="text-white font-bold text-lg">StudyFlow</span>
                </div>
                <p class="text-sm leading-relaxed">The all-in-one productivity platform for students who want to study smarter, not harder.</p>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Product</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#features"     class="hover:text-purple-400 transition-colors">Features</a></li>
                    <li><a href="#how-it-works" class="hover:text-purple-400 transition-colors">How It Works</a></li>
                    <li><a href="#pricing"      class="hover:text-purple-400 transition-colors">Pricing</a></li>
                    <li><a href="#faq"          class="hover:text-purple-400 transition-colors">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Terms of Service</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Account</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="login.php"    class="hover:text-purple-400 transition-colors">Sign In</a></li>
                    <li><a href="register.php" class="hover:text-purple-400 transition-colors">Create Account</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-10 pt-6 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
            <p>&copy; <?php echo date("Y"); ?> StudyFlow. All Rights Reserved.</p>
            <div class="flex gap-4">
                <a href="#" class="hover:text-purple-400 transition-colors">Facebook</a>
                <a href="#" class="hover:text-purple-400 transition-colors">Twitter</a>
                <a href="#" class="hover:text-purple-400 transition-colors">GitHub</a>
            </div>
        </div>
    </div>
</footer>

<script>
    let openFaq = null;
    document.querySelectorAll('.faq-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            const content = btn.nextElementSibling;
            const arrow   = btn.querySelector('.faq-arrow');
            if (openFaq && openFaq !== content) {
                openFaq.style.maxHeight = null;
                openFaq.previousElementSibling.querySelector('.faq-arrow').style.transform = 'rotate(0deg)';
            }
            if (content.style.maxHeight) {
                content.style.maxHeight = null;
                arrow.style.transform = 'rotate(0deg)';
                openFaq = null;
            } else {
                content.style.maxHeight = content.scrollHeight + 'px';
                arrow.style.transform = 'rotate(180deg)';
                openFaq = content;
            }
        });
    });
</script>
</body>
</html>
