<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyFlow — AI-Powered Student Productivity Hub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&family=Fira+Code:wght@400;500&display=swap" rel="stylesheet">
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }
        .faq-content { max-height: 0; overflow: hidden; transition: max-height 0.4s ease-in-out; }
        .gradient-text { background: linear-gradient(135deg, #7c3aed, #a855f7, #ec4899); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .hero-glow { background: radial-gradient(ellipse 80% 50% at 50% -10%, rgba(124,58,237,0.12), transparent); }
        .feature-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .feature-card:hover { transform: translateY(-4px); box-shadow: 0 20px 40px rgba(124,58,237,0.1); }
        .mono { font-family: 'Fira Code', monospace; }
        .badge-sa { background: linear-gradient(135deg, #ef4444, #b91c1c); }
        /* Animated gradient border on hero mockup */
        .mockup-glow { box-shadow: 0 0 0 1px rgba(124,58,237,.2), 0 32px 64px rgba(124,58,237,.18), 0 12px 32px rgba(0,0,0,.15); }
        /* Typing cursor blink */
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0} }
        .cursor { animation: blink 1s step-end infinite; }
        /* Slide-up on scroll (progressive enhancement) */
        .reveal { opacity: 0; transform: translateY(24px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: none; }
        /* Plan card hover */
        .plan-card { transition: transform .2s, box-shadow .2s; }
        .plan-card:hover { transform: translateY(-6px); }
    </style>
</head>
<body class="bg-white text-gray-800 antialiased">

<!-- ══════════════════ NAVBAR ══════════════════ -->
<header class="bg-white/90 backdrop-blur-md fixed top-0 left-0 right-0 z-50 border-b border-gray-100 shadow-sm">
    <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
            </div>
            <span class="text-2xl font-bold text-purple-600">StudyFlow</span>
        </div>
        <nav class="hidden md:flex space-x-8 items-center text-sm font-medium">
            <a href="#features"     class="text-gray-600 hover:text-purple-600 transition-colors">Features</a>
            <a href="#code-editor"  class="text-gray-600 hover:text-purple-600 transition-colors">Code Editor</a>
            <a href="#how-it-works" class="text-gray-600 hover:text-purple-600 transition-colors">How It Works</a>
            <a href="#pricing"      class="text-gray-600 hover:text-purple-600 transition-colors">Pricing</a>
            <a href="#faq"          class="text-gray-600 hover:text-purple-600 transition-colors">FAQ</a>
        </nav>
        <div class="flex items-center gap-3">
            <a href="login.php"    class="text-sm font-medium text-gray-600 hover:text-purple-600 transition-colors hidden sm:block">Sign In</a>
            <a href="register.php" class="bg-purple-600 text-white px-5 py-2 rounded-lg text-sm font-semibold hover:bg-purple-700 transition-colors shadow-sm">Get Started Free</a>
        </div>
    </div>
</header>

<main>

<!-- ══════════════════ HERO ══════════════════ -->
<section class="hero-glow bg-gradient-to-b from-purple-50 via-white to-white pt-32 pb-16 overflow-hidden">
    <div class="container mx-auto px-6">
        <!-- Text -->
        <div class="text-center max-w-3xl mx-auto mb-14 reveal">
            <span class="inline-flex items-center gap-1.5 bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1.5 rounded-full mb-5 tracking-wide uppercase">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                Trusted by 500+ Students &amp; Developers
            </span>
            <h2 class="text-5xl md:text-6xl font-black text-gray-900 leading-tight tracking-tight">
                Study Smarter.<br>
                <span class="gradient-text">Code Faster.</span><br>
                Powered by AI.
            </h2>
            <p class="mt-6 text-lg text-gray-500 max-w-2xl mx-auto leading-relaxed">
                AI Tutor, smart notes, flashcards, study goals, a full code editor with Agent Mode — all in one place. Stop juggling apps. Start achieving more.
            </p>
            <div class="mt-8 flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="inline-block bg-purple-600 text-white px-8 py-4 rounded-xl font-bold text-lg hover:bg-purple-700 transition-all shadow-lg hover:shadow-purple-200 hover:-translate-y-0.5">
                    Start Free — No Card Needed
                </a>
                <a href="login.php" class="inline-block bg-white border border-gray-200 text-gray-700 px-8 py-4 rounded-xl font-semibold text-lg hover:border-purple-300 hover:text-purple-600 transition-all shadow-sm">
                    Sign In →
                </a>
            </div>
            <p class="mt-4 text-xs text-gray-400">Free plan forever · Upgrade anytime · Cancel anytime</p>
        </div>

        <!-- Dashboard Mockup -->
        <div class="relative max-w-5xl mx-auto mockup-glow rounded-2xl reveal">
            <!-- Browser chrome -->
            <div class="bg-gray-800 rounded-t-2xl px-4 py-3 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="ml-4 flex-1 bg-gray-700 rounded-full px-4 py-1 text-xs text-gray-400 max-w-xs font-mono">localhost/studyflow/index.php</div>
                <div class="ml-auto flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full bg-green-400"></div>
                    <span class="text-xs text-gray-400">Live</span>
                </div>
            </div>
            <!-- App UI mockup -->
            <div class="bg-gray-100 rounded-b-2xl overflow-hidden border border-gray-200" style="height:440px;">
                <div class="flex h-full">
                    <!-- Sidebar -->
                    <div class="w-52 bg-gray-800 flex flex-col p-3 shrink-0">
                        <div class="text-white font-bold text-sm mb-5 px-2 flex items-center gap-2">
                            <div class="w-5 h-5 bg-purple-600 rounded flex items-center justify-center"><svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13"/></svg></div>
                            StudyFlow
                        </div>
                        <?php
                        $nav_items = [
                            ['d'=>'M3 7h18M3 12h18M3 17h9',             'label'=>'Dashboard',   'active'=>true,  'color'=>''],
                            ['d'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2', 'label'=>'To-dos','active'=>false,'color'=>''],
                            ['d'=>'M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z', 'label'=>'Study Goals','active'=>false,'color'=>''],
                            ['d'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3',  'label'=>'AI Tutor',    'active'=>false,'color'=>'text-purple-400'],
                            ['d'=>'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4',       'label'=>'Code Editor', 'active'=>false,'color'=>'text-blue-400'],
                            ['d'=>'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'label'=>'Calendar','active'=>false,'color'=>''],
                        ];
                        foreach ($nav_items as $item): ?>
                        <div class="flex items-center gap-2 px-3 py-1.5 rounded-lg mb-0.5 <?php echo $item['active'] ? 'bg-purple-600 text-white' : 'text-gray-400 hover:bg-gray-700'; ?>">
                            <svg class="w-3.5 h-3.5 shrink-0 <?php echo $item['color']; ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $item['d']; ?>"/></svg>
                            <span class="text-xs font-medium"><?php echo $item['label']; ?></span>
                        </div>
                        <?php endforeach; ?>

                        <div class="mt-auto px-2">
                            <div class="bg-purple-900/50 rounded-lg p-2 text-xs">
                                <p class="text-purple-300 font-semibold">Standard Plan</p>
                                <p class="text-purple-400 text-xs mt-0.5">5 GB · 5 users</p>
                            </div>
                        </div>
                    </div>
                    <!-- Main content -->
                    <div class="flex-1 p-5 overflow-hidden bg-gray-50">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="text-sm font-bold text-gray-800">Good morning, Carlos 👋</h3>
                                <p class="text-xs text-gray-400">Friday, Apr 17 · 3 tasks due today</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="bg-white border border-gray-200 rounded-lg px-2 py-1 text-xs text-gray-500 flex items-center gap-1">
                                    <svg class="w-3 h-3 text-green-500" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="3"/></svg>
                                    Study Timer: 42:15
                                </div>
                                <div class="w-7 h-7 rounded-full bg-purple-600 flex items-center justify-center text-white text-xs font-bold">C</div>
                            </div>
                        </div>
                        <!-- Stats -->
                        <div class="grid grid-cols-4 gap-2.5 mb-4">
                            <?php foreach ([['Tasks','8','bg-purple-50','text-purple-600'],['Goals','3','bg-blue-50','text-blue-600'],['Studied','2h 14m','bg-green-50','text-green-600'],['Flashcards','24','bg-orange-50','text-orange-600']] as $s): ?>
                            <div class="<?php echo $s[2]; ?> rounded-lg p-2.5">
                                <p class="text-xs text-gray-500"><?php echo $s[0]; ?></p>
                                <p class="text-base font-bold <?php echo $s[3]; ?>"><?php echo $s[1]; ?></p>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Two columns -->
                        <div class="grid grid-cols-5 gap-2.5">
                            <div class="col-span-3 bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-xs font-semibold text-gray-700 mb-2">Recent To-dos</p>
                                <?php foreach ([['Biology Chapter 4',true],['Math Homework',false],['History Essay',false],['Code Project Review',false]] as [$task,$done]): ?>
                                <div class="flex items-center gap-2 py-1">
                                    <div class="w-3.5 h-3.5 rounded border-2 <?php echo $done ? 'bg-purple-600 border-purple-600' : 'border-gray-300'; ?> shrink-0"></div>
                                    <span class="text-xs <?php echo $done ? 'line-through text-gray-400' : 'text-gray-700'; ?>"><?php echo $task; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <div class="col-span-2 bg-white rounded-lg p-3 shadow-sm">
                                <p class="text-xs font-semibold text-gray-700 mb-2">AI Quick Actions</p>
                                <?php foreach ([['🤖 Ask AI Tutor','bg-purple-50 text-purple-700'],['⚡ Generate Flashcards','bg-blue-50 text-blue-700'],['💻 Open Code Editor','bg-gray-800 text-white']] as [$lbl,$cls]): ?>
                                <div class="<?php echo $cls; ?> rounded text-xs px-2 py-1.5 mb-1.5 font-medium cursor-pointer"><?php echo $lbl; ?></div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════ STATS BAR ══════════════════ -->
<section class="py-12 bg-white border-y border-gray-100">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <?php foreach ([['500+','Active Students'],['10K+','Tasks Completed'],['50K+','Flashcards Created'],['4.9★','Average Rating']] as [$v,$l]): ?>
            <div class="reveal">
                <p class="text-4xl font-extrabold text-purple-600"><?php echo $v; ?></p>
                <p class="text-gray-500 text-sm mt-1"><?php echo $l; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ FEATURES ══════════════════ -->
<section id="features" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Everything you need</span>
            <h3 class="text-4xl font-extrabold text-gray-900">Built for students who want results</h3>
            <p class="text-gray-500 mt-3 max-w-xl mx-auto">One platform that replaces five apps — organised, powerful, and powered by AI.</p>
        </div>

        <!-- Feature 1: AI Tutor -->
        <div class="grid md:grid-cols-2 gap-16 items-center mb-28 reveal">
            <div>
                <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">AI Tutor</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">Your personal tutor, available 24/7</h4>
                <p class="text-gray-500 leading-relaxed mb-6">Ask questions, get explanations, and work through problems with an AI that remembers your conversation. No more waiting for office hours.</p>
                <ul class="space-y-3">
                    <?php foreach (['Multi-turn conversations with full history','Explains concepts step-by-step','Works for any subject or course','Powered by free OpenRouter AI models'] as $item): ?>
                    <li class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo $item; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- AI Tutor chat mockup -->
            <div class="relative">
                <div class="bg-gray-900 rounded-2xl shadow-2xl overflow-hidden" style="height:340px;">
                    <div class="bg-gray-800 px-4 py-3 flex items-center gap-2 border-b border-gray-700">
                        <div class="w-7 h-7 bg-purple-600 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3"/></svg>
                        </div>
                        <span class="text-white text-sm font-semibold">AI Tutor</span>
                        <span class="ml-auto text-xs bg-green-500/20 text-green-400 rounded-full px-2 py-0.5">● Online</span>
                    </div>
                    <div class="p-4 space-y-3 overflow-hidden">
                        <div class="flex gap-2">
                            <div class="w-6 h-6 bg-purple-600 rounded-full shrink-0 flex items-center justify-center text-white text-xs">AI</div>
                            <div class="bg-gray-800 rounded-xl rounded-tl-none px-3 py-2 text-xs text-gray-200 max-w-xs">Hi! Ask me anything about your studies. I can explain concepts, solve problems, or quiz you. 📚</div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <div class="bg-purple-600 rounded-xl rounded-tr-none px-3 py-2 text-xs text-white max-w-xs">Can you explain how photosynthesis works?</div>
                        </div>
                        <div class="flex gap-2">
                            <div class="w-6 h-6 bg-purple-600 rounded-full shrink-0 flex items-center justify-center text-white text-xs">AI</div>
                            <div class="bg-gray-800 rounded-xl rounded-tl-none px-3 py-2 text-xs text-gray-200 max-w-xs">
                                <strong class="text-purple-300">Photosynthesis</strong> is how plants convert light into energy:<br><br>
                                <span class="text-green-400">6CO₂ + 6H₂O + light → C₆H₁₂O₆ + 6O₂</span><br><br>
                                It happens in two stages: the <em>light reactions</em> and the <em>Calvin cycle</em>...
                            </div>
                        </div>
                        <div class="flex gap-2 justify-end">
                            <div class="bg-gray-700 rounded-xl rounded-tr-none px-3 py-2 text-xs text-gray-300 italic flex items-center gap-1">
                                What's the difference between C3 and C4 plants?<span class="cursor text-purple-400 font-bold">|</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-4 -left-4 bg-white rounded-xl shadow-lg px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-gray-800">Instant Answers</p>
                        <p class="text-xs text-gray-400">Free AI models via OpenRouter</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Feature 2: Flashcards -->
        <div class="grid md:grid-cols-2 gap-16 items-center mb-28 reveal">
            <!-- Flashcard mockup -->
            <div class="order-2 md:order-1 relative">
                <div class="bg-gradient-to-br from-blue-600 to-purple-700 rounded-2xl shadow-2xl p-6" style="height:340px;">
                    <div class="flex justify-between items-center mb-5">
                        <span class="text-blue-200 text-xs font-semibold uppercase tracking-wide">Biology · Mitosis</span>
                        <span class="text-white text-xs bg-white/20 rounded-full px-2 py-0.5">Card 3 of 12</span>
                    </div>
                    <!-- Flashcard -->
                    <div class="bg-white/10 backdrop-blur rounded-xl p-5 mb-4 border border-white/20">
                        <p class="text-xs text-blue-200 uppercase font-semibold mb-3">Question</p>
                        <p class="text-white font-semibold text-base">What are the 4 phases of mitosis in order?</p>
                    </div>
                    <div class="bg-white rounded-xl p-4 shadow-inner">
                        <p class="text-xs text-gray-400 uppercase font-semibold mb-2">Answer</p>
                        <p class="text-gray-800 text-sm font-medium">
                            <span class="text-purple-600 font-bold">P</span>rophase →
                            <span class="text-blue-600 font-bold">M</span>etaphase →
                            <span class="text-green-600 font-bold">A</span>naphase →
                            <span class="text-orange-600 font-bold">T</span>elophase
                            <span class="text-gray-400 text-xs ml-1">(PMAT)</span>
                        </p>
                    </div>
                    <div class="flex gap-2 mt-4">
                        <button class="flex-1 bg-red-500/30 text-white text-xs py-2 rounded-lg font-semibold">✗ Again</button>
                        <button class="flex-1 bg-yellow-400/30 text-white text-xs py-2 rounded-lg font-semibold">~ Hard</button>
                        <button class="flex-1 bg-green-500/30 text-white text-xs py-2 rounded-lg font-semibold">✓ Easy</button>
                    </div>
                </div>
                <div class="absolute -top-4 -right-4 bg-white rounded-xl shadow-lg px-4 py-3 text-center">
                    <p class="text-xs text-gray-500">AI Generated</p>
                    <p class="text-2xl font-extrabold text-purple-600">24 ✦</p>
                    <p class="text-xs text-gray-400">cards in 5 sec</p>
                </div>
            </div>
            <div class="order-1 md:order-2">
                <span class="inline-block bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">Flashcards</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">AI-generated flashcards in seconds</h4>
                <p class="text-gray-500 leading-relaxed mb-6">Type a topic and let AI generate a full set of Q&amp;A cards. Study them with our interactive flip mode, or export for offline review.</p>
                <ul class="space-y-3">
                    <?php foreach (['Generate cards from any topic instantly','Interactive flip-card study mode','Organise into collections by subject','Track retention with Easy / Hard / Again'] as $item): ?>
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

        <!-- Feature 3: Goals & Tasks -->
        <div class="grid md:grid-cols-2 gap-16 items-center reveal">
            <div>
                <span class="inline-block bg-green-100 text-green-700 text-xs font-semibold px-3 py-1 rounded-full mb-4">Goals &amp; Tasks</span>
                <h4 class="text-3xl font-bold text-gray-900 mb-4">Set goals. Break them into steps. Achieve them.</h4>
                <p class="text-gray-500 leading-relaxed mb-6">AI suggests measurable study goals for your subject, then breaks each goal into actionable to-do tasks. Track progress with built-in study timers.</p>
                <ul class="space-y-3">
                    <?php foreach (['AI-suggested study goals per subject','Auto-generate task lists from goals','Study timer with session history','Visual progress across all goals'] as $item): ?>
                    <li class="flex items-center gap-3">
                        <div class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                        </div>
                        <span class="text-gray-600 text-sm"><?php echo $item; ?></span>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Goals mockup -->
            <div class="relative">
                <div class="bg-white rounded-2xl shadow-2xl border border-gray-100 overflow-hidden" style="height:340px;">
                    <div class="bg-gray-50 px-4 py-3 border-b border-gray-100 flex justify-between items-center">
                        <span class="text-sm font-bold text-gray-700">Study Goals</span>
                        <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-semibold">3 Active</span>
                    </div>
                    <div class="p-4 space-y-4">
                        <?php
                        $goals_mock = [
                            ['title'=>'Pass Biology Midterm','pct'=>72,'color'=>'bg-purple-600','badge'=>'HIGH','bc'=>'bg-red-100 text-red-700'],
                            ['title'=>'Complete Math Problem Sets','pct'=>45,'color'=>'bg-blue-500','badge'=>'MED','bc'=>'bg-yellow-100 text-yellow-700'],
                            ['title'=>'History Essay Draft','pct'=>20,'color'=>'bg-green-500','badge'=>'LOW','bc'=>'bg-green-100 text-green-700'],
                        ];
                        foreach ($goals_mock as $g): ?>
                        <div>
                            <div class="flex justify-between items-center mb-1.5">
                                <span class="text-sm font-medium text-gray-700"><?php echo $g['title']; ?></span>
                                <span class="text-xs font-bold <?php echo $g['bc']; ?> px-1.5 py-0.5 rounded"><?php echo $g['badge']; ?></span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 rounded-full h-2">
                                    <div class="<?php echo $g['color']; ?> h-2 rounded-full transition-all" style="width:<?php echo $g['pct']; ?>%"></div>
                                </div>
                                <span class="text-xs font-bold text-gray-500 w-8 text-right"><?php echo $g['pct']; ?>%</span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <div class="bg-purple-50 rounded-xl p-3 flex items-start gap-3 mt-2">
                            <div class="w-7 h-7 bg-purple-600 rounded-lg flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            </div>
                            <div>
                                <p class="text-xs font-semibold text-purple-800">AI Suggestion</p>
                                <p class="text-xs text-purple-600 mt-0.5">"Focus on Chapters 3-5. Create 15 flashcards on cell division."</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-4 -right-4 bg-white rounded-xl shadow-lg px-4 py-3">
                    <p class="text-xs text-gray-500">Session Today</p>
                    <div class="flex items-center gap-2 mt-1">
                        <div class="w-20 h-2 bg-gray-100 rounded-full"><div class="w-14 h-2 bg-purple-600 rounded-full"></div></div>
                        <span class="text-xs font-bold text-gray-700">72%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ══════════════════ CODE EDITOR ══════════════════ -->
<section id="code-editor" class="py-24 bg-gray-950 overflow-hidden">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <span class="inline-flex items-center gap-1.5 bg-blue-500/10 text-blue-400 border border-blue-500/20 text-xs font-semibold px-3 py-1.5 rounded-full mb-4 uppercase tracking-wide">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                New — Code Editor + AI Agent
            </span>
            <h3 class="text-4xl font-extrabold text-white">A full IDE — right in your browser</h3>
            <p class="text-gray-400 mt-3 max-w-2xl mx-auto">Monaco Editor (VS Code engine), local folder access, and an AI Agent that reads your entire codebase and writes real files. This is how Claude Code works — now inside StudyFlow.</p>
        </div>

        <!-- Code Editor Mockup -->
        <div class="relative max-w-6xl mx-auto reveal">
            <div class="bg-gray-800 rounded-t-2xl px-4 py-3 flex items-center gap-2">
                <div class="w-3 h-3 rounded-full bg-red-400"></div>
                <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                <div class="w-3 h-3 rounded-full bg-green-400"></div>
                <div class="ml-4 bg-gray-700 rounded-full px-4 py-1 text-xs text-gray-400 mono max-w-xs">localhost/studyflow/pages/code-editor.php</div>
                <div class="ml-auto flex gap-2">
                    <span class="bg-gray-700 text-gray-300 text-xs px-2 py-0.5 rounded">Open Folder</span>
                    <span class="bg-purple-600 text-white text-xs px-2 py-0.5 rounded">AI ✦</span>
                </div>
            </div>
            <div class="bg-gray-900 rounded-b-2xl overflow-hidden border border-gray-700" style="height:480px;">
                <div class="flex h-full">
                    <!-- File tree -->
                    <div class="w-44 bg-gray-900 border-r border-gray-700 flex flex-col">
                        <div class="px-3 py-2 border-b border-gray-700">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-wider">studyflow</p>
                        </div>
                        <div class="p-2 text-xs space-y-0.5">
                            <?php
                            $tree = [
                                ['📁 api', 0, false],['📁 pages', 0, true],['  📁 editor', 1, false],
                                ['  🐘 code-editor.php', 1, true],['  🐘 billing.php', 1, false],
                                ['  🐘 api-settings.php', 1, false],['📁 partials', 0, false],
                                ['🐘 config.php', 0, false],['📋 secrets.php', 0, false],
                            ];
                            foreach ($tree as [$name, $indent, $active]): ?>
                            <div class="px-<?php echo ($indent*3+1); ?> py-0.5 rounded <?php echo $active ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300'; ?> cursor-pointer flex items-center gap-1">
                                <?php echo htmlspecialchars($name); ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Editor area -->
                    <div class="flex-1 flex flex-col min-w-0">
                        <!-- Tabs -->
                        <div class="bg-gray-800 border-b border-gray-700 flex text-xs">
                            <?php foreach ([['code-editor.php','🐘',true],['billing.php','🐘',false]] as [$tab,$ico,$act]): ?>
                            <div class="flex items-center gap-1.5 px-3 py-2 border-r border-gray-700 <?php echo $act ? 'bg-gray-900 border-t-2 border-t-purple-500 text-white' : 'text-gray-500'; ?>">
                                <span><?php echo $ico; ?></span><?php echo $tab; ?>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <!-- Code -->
                        <div class="flex-1 overflow-hidden p-4 mono text-xs leading-relaxed">
                            <div class="flex text-gray-600 mb-1"><span class="w-8 text-right mr-4 text-gray-600">1</span><span class="text-blue-400">&lt;?php</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">2</span><span class="text-green-400 italic">// File: pages/code-editor.php</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">3</span><span class="text-purple-400">session_start</span><span class="text-white">();</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">4</span><span class="text-purple-400">require_once</span><span class="text-white">(</span><span class="text-green-300">'../config.php'</span><span class="text-white">);</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">5</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">6</span><span class="text-blue-300">$workspace</span><span class="text-white"> = </span><span class="text-yellow-300">get_workspace_root</span><span class="text-white">();</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">7</span><span class="text-blue-300">$files</span><span class="text-white"> = </span><span class="text-yellow-300">scan_directory</span><span class="text-white">(</span><span class="text-blue-300">$workspace</span><span class="text-white">);</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">8</span></div>
                            <div class="flex mb-1 bg-purple-900/30 rounded"><span class="w-8 text-right mr-4 text-gray-600">9</span><span class="text-green-400 italic">// Agent mode: AI reads + writes real files</span></div>
                            <div class="flex mb-1"><span class="w-8 text-right mr-4 text-gray-600">10</span><span class="text-purple-400">require_once</span><span class="text-white">(</span><span class="text-green-300">'api/editor/ai-agent.php'</span><span class="text-white">);</span></div>
                        </div>
                    </div>

                    <!-- AI Sidebar -->
                    <div class="w-64 border-l border-gray-700 flex flex-col bg-gray-900">
                        <div class="px-3 py-2 border-b border-gray-700 flex items-center gap-2">
                            <div class="w-5 h-5 bg-purple-600 rounded flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707"/></svg>
                            </div>
                            <span class="text-xs font-bold text-gray-200">AI Assistant</span>
                            <div class="ml-auto flex border border-gray-600 rounded text-xs overflow-hidden">
                                <span class="px-1.5 py-0.5 text-gray-400">Chat</span>
                                <span class="px-1.5 py-0.5 bg-purple-600 text-white font-semibold">Agent</span>
                            </div>
                        </div>
                        <!-- Context bar -->
                        <div class="bg-gray-950 border-b border-gray-700 px-3 py-1.5 flex items-center gap-2">
                            <span class="text-xs text-green-400">✓ 42 files · 128 KB loaded</span>
                        </div>
                        <!-- Messages -->
                        <div class="flex-1 p-3 space-y-2 overflow-hidden text-xs">
                            <div class="bg-gray-700 rounded-lg rounded-tl-none px-2.5 py-2 text-gray-200 text-xs">
                                Hi! I can analyze your full codebase and write real file changes. Try Agent mode!
                            </div>
                            <div class="bg-purple-600 rounded-lg rounded-tr-none px-2.5 py-2 text-white text-xs self-end ml-4">
                                Add dark mode to all pages
                            </div>
                            <!-- Changes card -->
                            <div class="border border-gray-600 rounded-lg overflow-hidden">
                                <div class="bg-gray-800 px-2.5 py-1.5 flex items-center justify-between">
                                    <span class="text-purple-300 text-xs font-semibold">📦 4 changes proposed</span>
                                    <button class="text-xs bg-green-500/20 text-green-400 px-2 py-0.5 rounded font-semibold">Apply All</button>
                                </div>
                                <?php foreach ([['✚','partials/dark-mode.php','#a6e3a1'],['✎','pages/billing.php','#89b4fa'],['✎','partials/header.php','#89b4fa'],['✎','assets/style.css','#89b4fa']] as [$icon,$path,$color]): ?>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 border-t border-gray-700 text-xs">
                                    <span style="color:<?php echo $color; ?>"><?php echo $icon; ?></span>
                                    <span class="text-gray-400 mono truncate flex-1"><?php echo $path; ?></span>
                                    <button class="text-xs text-gray-500 hover:text-white">Apply</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="p-2 border-t border-gray-700">
                            <div class="bg-gray-800 rounded-lg px-2.5 py-1.5 text-xs text-gray-500 flex items-center justify-between">
                                <span>Describe task… (Ctrl+↵)</span>
                                <span class="text-purple-400">↑</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Code Editor Feature Bullets -->
        <div class="grid md:grid-cols-3 gap-6 mt-12 max-w-4xl mx-auto">
            <?php
            $ce_features = [
                ['icon'=>'M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4', 'title'=>'Monaco Editor', 'desc'=>'The same engine as VS Code — syntax highlighting, IntelliSense, multi-tab, bracket pairs, and mini-map.', 'color'=>'bg-blue-500/10 text-blue-400'],
                ['icon'=>'M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z', 'title'=>'Local Folder Access', 'desc'=>'Open any folder on your computer directly. Read and write real files — not just uploads. Works on XAMPP locally.', 'color'=>'bg-green-500/10 text-green-400'],
                ['icon'=>'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1', 'title'=>'AI Agent Mode', 'desc'=>'AI reads your entire codebase, proposes multi-file changes (create / edit / delete), and applies them with one click.', 'color'=>'bg-purple-500/10 text-purple-400'],
            ];
            foreach ($ce_features as $f): ?>
            <div class="bg-gray-800 rounded-xl p-5 border border-gray-700 reveal">
                <div class="w-9 h-9 rounded-lg <?php echo $f['color']; ?> flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $f['icon']; ?>"/></svg>
                </div>
                <h5 class="font-bold text-white mb-1"><?php echo $f['title']; ?></h5>
                <p class="text-gray-400 text-sm leading-relaxed"><?php echo $f['desc']; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ FEATURE CARDS ══════════════════ -->
<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-10 reveal">
            <h3 class="text-2xl font-bold text-gray-800">Everything else included</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 max-w-5xl mx-auto">
            <?php
            $cards = [
                ['M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z', 'Smart Notes',     'Rich-text notes with AI writing and summarise assistance.', 'bg-purple-100 text-purple-600'],
                ['M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'Calendar',  'Weekly calendar view for classes, exams, and study blocks.', 'bg-blue-100 text-blue-600'],
                ['M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'Team Workspace', 'Invite classmates and share notes, goals, and assignments.', 'bg-pink-100 text-pink-600'],
                ['M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z', 'Assignments',    'Track project status, deadlines, and linked study goals.', 'bg-orange-100 text-orange-600'],
                ['M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z', 'Sheets',     'Spreadsheet editor for data, budgets, and schedules.', 'bg-teal-100 text-teal-600'],
                ['M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12', 'File Sharing', 'Upload and share documents, images, and study materials.', 'bg-indigo-100 text-indigo-600'],
                ['M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'Access Logs',  'See who accessed shared content and when.', 'bg-red-100 text-red-600'],
                ['M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'Super Admin',  'Platform-level role for managing users, API keys, and plans.', 'bg-yellow-100 text-yellow-700'],
            ];
            foreach ($cards as [$icon, $title, $desc, $color]): ?>
            <div class="feature-card bg-white rounded-xl p-5 shadow-sm border border-gray-100 hover:shadow-md reveal">
                <div class="w-9 h-9 rounded-lg <?php echo $color; ?> flex items-center justify-center mb-3">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo $icon; ?>"/></svg>
                </div>
                <h5 class="font-bold text-gray-800 mb-1 text-sm"><?php echo $title; ?></h5>
                <p class="text-gray-500 text-xs leading-relaxed"><?php echo $desc; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ HOW IT WORKS ══════════════════ -->
<section id="how-it-works" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Simple to start</span>
            <h3 class="text-4xl font-extrabold text-gray-900">Up and running in 3 steps</h3>
        </div>
        <div class="grid md:grid-cols-3 gap-8 max-w-4xl mx-auto">
            <?php
            $steps = [
                ['01','Create your account',  'Sign up for free — no credit card required. Your private workspace is created instantly with the Free plan.', 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z', 'bg-purple-100 text-purple-600'],
                ['02','Add your subjects',    'Set up study goals, create flashcard decks, and add your upcoming assignments in minutes. AI helps you structure everything.', 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4', 'bg-blue-100 text-blue-600'],
                ['03','Study &amp; Build with AI', 'Chat with the AI Tutor, generate flashcards, and use Code Editor Agent Mode to build real projects with AI writing code for you.', 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z', 'bg-green-100 text-green-600'],
            ];
            foreach ($steps as [$num, $title, $desc, $icon, $color]): ?>
            <div class="text-center reveal">
                <div class="relative mb-6 inline-block">
                    <div class="w-24 h-24 rounded-2xl <?php echo $color; ?> mx-auto flex items-center justify-center shadow-lg">
                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="<?php echo $icon; ?>"/></svg>
                    </div>
                    <div class="absolute -top-3 -right-3 w-8 h-8 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-extrabold shadow-md"><?php echo (int)$num; ?></div>
                </div>
                <h5 class="font-bold text-gray-900 text-lg mb-2"><?php echo $title; ?></h5>
                <p class="text-gray-500 text-sm leading-relaxed"><?php echo $desc; ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ PRICING ══════════════════ -->
<section id="pricing" class="py-24 bg-gray-50">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12 reveal">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Pricing</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Start free. Upgrade when you're ready.</h3>
            <p class="text-gray-500 mt-2">All plans include the core study tools. Paid plans unlock AI features and team collaboration.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 max-w-6xl mx-auto items-end">
            <?php
            $plans = [
                ['slug'=>'free',     'name'=>'Free',     'price'=>0,     'annual'=>null,   'popular'=>false, 'dark'=>false,
                 'sub'=>'Forever free for solo learners.',
                 'features'=>['1 user','500 MB storage','All core study tools','Code Editor (local)','Community support'],
                 'missing'=>['AI Tutor','AI Flashcards','Agent Mode','Team workspace'],
                 'cta'=>'Get Started Free', 'cta_href'=>'register.php'],
                ['slug'=>'basic',    'name'=>'Basic',    'price'=>4.99,  'annual'=>49.99,  'popular'=>false, 'dark'=>false,
                 'sub'=>'Great for solo students.',
                 'features'=>['1 user','1 GB storage','All core tools','Limited AI features','Code Editor + AI Chat','Email support'],
                 'missing'=>['Agent Mode','Team workspace'],
                 'cta'=>'Start Free Trial', 'cta_href'=>'register.php'],
                ['slug'=>'standard', 'name'=>'Standard', 'price'=>9.99,  'annual'=>99.99,  'popular'=>true,  'dark'=>true,
                 'sub'=>'For students who want it all.',
                 'features'=>['Up to 5 users','5 GB storage','Full AI Tutor + Flashcards','AI Goals &amp; Tasks','Code Editor + Agent Mode','Priority support'],
                 'missing'=>[],
                 'cta'=>'Start Free Trial', 'cta_href'=>'register.php'],
                ['slug'=>'premium',  'name'=>'Premium',  'price'=>19.99, 'annual'=>199.99, 'popular'=>false, 'dark'=>false,
                 'sub'=>'For schools, teams, and power users.',
                 'features'=>['Unlimited users','10 GB storage','Everything in Standard','File sharing &amp; resources','Access logs','Custom integrations','Dedicated support'],
                 'missing'=>[],
                 'cta'=>'Start Free Trial', 'cta_href'=>'register.php'],
            ];
            foreach ($plans as $p):
                $card_bg  = $p['dark'] ? 'bg-gray-900 text-white' : 'bg-white text-gray-800';
                $sub_col  = $p['dark'] ? 'text-gray-400' : 'text-gray-500';
                $feat_col = $p['dark'] ? 'text-gray-300' : 'text-gray-600';
                $miss_col = $p['dark'] ? 'text-gray-600' : 'text-gray-300';
                $chk_col  = $p['dark'] ? 'text-green-400' : 'text-green-500';
            ?>
            <div class="plan-card <?php echo $card_bg; ?> rounded-2xl p-7 shadow-md flex flex-col relative reveal
                        <?php echo $p['popular'] ? 'ring-2 ring-purple-500 shadow-2xl' : 'border border-gray-200'; ?>">

                <?php if ($p['popular']): ?>
                <div class="absolute -top-4 left-1/2 -translate-x-1/2">
                    <span class="bg-purple-600 text-white text-xs font-bold uppercase px-4 py-1.5 rounded-full shadow-lg whitespace-nowrap">Most Popular</span>
                </div>
                <?php endif; ?>

                <div class="mb-5">
                    <span class="text-xs font-bold uppercase tracking-widest <?php echo $p['dark'] ? 'text-gray-400' : 'text-gray-400'; ?>"><?php echo $p['name']; ?></span>
                    <div class="mt-2 flex items-end gap-1">
                        <?php if ($p['price'] == 0): ?>
                        <span class="text-5xl font-black <?php echo $p['dark'] ? 'text-white' : 'text-gray-900'; ?>">Free</span>
                        <?php else: ?>
                        <span class="text-5xl font-black <?php echo $p['dark'] ? 'text-white' : 'text-gray-900'; ?>">$<?php echo number_format($p['price'],2); ?></span>
                        <span class="text-base mb-1 <?php echo $sub_col; ?>">/mo</span>
                        <?php endif; ?>
                    </div>
                    <?php if ($p['annual']): ?>
                    <p class="text-xs <?php echo $sub_col; ?> mt-0.5">$<?php echo number_format($p['annual'],2); ?> billed annually</p>
                    <?php else: ?>
                    <p class="text-xs <?php echo $sub_col; ?> mt-0.5">No credit card required</p>
                    <?php endif; ?>
                    <p class="text-sm <?php echo $sub_col; ?> mt-2"><?php echo $p['sub']; ?></p>
                </div>

                <ul class="space-y-2.5 text-sm flex-1 mb-6">
                    <?php foreach ($p['features'] as $f): ?>
                    <li class="flex items-start gap-2 <?php echo $feat_col; ?>">
                        <span class="<?php echo $chk_col; ?> font-bold mt-0.5">✓</span>
                        <?php echo $f; ?>
                    </li>
                    <?php endforeach; ?>
                    <?php foreach ($p['missing'] as $m): ?>
                    <li class="flex items-start gap-2 opacity-40 line-through <?php echo $miss_col; ?>">
                        <span class="mt-0.5">—</span><?php echo $m; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?php echo $p['cta_href']; ?>"
                   class="block text-center py-3 rounded-xl font-semibold text-sm transition-all
                          <?php echo $p['dark']
                            ? 'bg-purple-600 text-white hover:bg-purple-500'
                            : ($p['price']==0 ? 'bg-gray-900 text-white hover:bg-gray-700' : 'border-2 border-purple-600 text-purple-600 hover:bg-purple-50'); ?>">
                    <?php echo $p['cta']; ?>
                </a>
            </div>
            <?php endforeach; ?>
        </div>

        <p class="text-center text-xs text-gray-400 mt-8">All paid plans for demonstration. Stripe integration ready.</p>
    </div>
</section>

<!-- ══════════════════ TESTIMONIALS ══════════════════ -->
<section id="testimonials" class="py-24 bg-white">
    <div class="container mx-auto px-6">
        <div class="text-center mb-12 reveal">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">Testimonials</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Students love StudyFlow</h3>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
            <?php
            $testimonials = [
                ['name'=>'Sarah Malik',   'role'=>'Biology student, UT',    'initials'=>'SM', 'color'=>'bg-purple-600',
                 'quote'=>'The AI Tutor is like having a professor at 2am. I aced my midterm after just 30 minutes reviewing Mitosis with it. The flashcard generator is incredibly fast.', 'stars'=>5],
                ['name'=>'James Larsson', 'role'=>'CS student, MIT',        'initials'=>'JL', 'color'=>'bg-blue-600',
                 'quote'=>'The Code Editor\'s Agent Mode is the real deal. I described what I wanted to build and it wrote the full feature across 5 files, then I hit Apply All. Incredible.', 'stars'=>5],
                ['name'=>'Alice Holmes',  'role'=>'Teacher, Central High',  'initials'=>'AH', 'color'=>'bg-pink-600',
                 'quote'=>'I manage our school\'s workspace as Super Admin. Setting the AI model to a free one saved us a lot. The access logs show exactly who reviewed shared materials.', 'stars'=>5],
            ];
            foreach ($testimonials as $t): ?>
            <div class="bg-gray-50 p-8 rounded-2xl border border-gray-100 hover:shadow-md transition-shadow reveal">
                <div class="flex gap-1 mb-4">
                    <?php for ($i=0;$i<$t['stars'];$i++): ?>
                    <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                    <?php endfor; ?>
                </div>
                <p class="text-gray-600 text-sm leading-relaxed mb-6">"<?php echo $t['quote']; ?>"</p>
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full <?php echo $t['color']; ?> flex items-center justify-center text-white font-bold text-sm shrink-0">
                        <?php echo $t['initials']; ?>
                    </div>
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

<!-- ══════════════════ FAQ ══════════════════ -->
<section id="faq" class="py-24 bg-gray-50">
    <div class="container mx-auto px-6 max-w-3xl">
        <div class="text-center mb-12 reveal">
            <span class="inline-block bg-purple-100 text-purple-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 uppercase tracking-wide">FAQ</span>
            <h3 class="text-4xl font-extrabold text-gray-800">Frequently Asked Questions</h3>
        </div>
        <div class="space-y-4">
            <?php
            $faqs = [
                ['q'=>'Is StudyFlow really free to start?',
                 'a'=>'Yes — the Free plan is free forever. Create an account, set up your workspace, and start using all core study tools immediately. No credit card required. Upgrade to Basic, Standard, or Premium when you need AI features, more storage, or team collaboration.'],
                ['q'=>'What AI model powers the tutor and code assistant?',
                 'a'=>'StudyFlow uses OpenRouter to access free and premium AI models. A Super Admin can manage the API key and switch to any free model (like Llama 3.3, Gemma 3, DeepSeek) directly from the API Settings page — including fetching the latest free models with one click.'],
                ['q'=>'What is the Code Editor Agent Mode?',
                 'a'=>'Agent Mode is a powerful feature inside the built-in Code Editor. It reads your entire project folder, then you describe a task in plain English. The AI proposes specific file changes (create, edit, delete) which you can preview and apply individually or all at once — exactly how Claude Code works.'],
                ['q'=>'What is the Super Admin role?',
                 'a'=>'Super Admin is a platform-level role above Admin. Only Super Admins can access the API Settings page to update the OpenRouter API key and AI model. Super Admins also see all tenants and users across the entire platform, not just their own workspace.'],
                ['q'=>'Can multiple students share one workspace?',
                 'a'=>'Yes. When you register you create a workspace (tenant). You can invite classmates as members under the Standard plan (up to 5 users) or Premium (unlimited). Each workspace\'s data is fully isolated from others.'],
                ['q'=>'How do I upgrade or change my plan?',
                 'a'=>'Go to Settings → Billing from the sidebar. You\'ll see all four plans (Free, Basic, Standard, Premium) with a live comparison table. Click Subscribe on any plan to switch instantly. In production, this connects to Stripe for payment.'],
                ['q'=>'Is my data private?',
                 'a'=>'All data is scoped to your workspace. Other tenants cannot access your notes, goals, or files. We never use your content to train AI models. Your API key is stored in a local secrets.php file, not in the database.'],
            ];
            foreach ($faqs as $faq): ?>
            <div class="bg-white rounded-xl border border-gray-100 overflow-hidden shadow-sm reveal">
                <button class="faq-btn flex justify-between items-center w-full p-6 text-left hover:bg-gray-50 transition-colors">
                    <span class="font-semibold text-gray-800 pr-4"><?php echo $faq['q']; ?></span>
                    <svg class="faq-arrow w-5 h-5 text-purple-500 shrink-0 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>
                <div class="faq-content px-6">
                    <p class="text-gray-500 text-sm pb-5 leading-relaxed"><?php echo $faq['a']; ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ══════════════════ CTA ══════════════════ -->
<section class="py-24 relative overflow-hidden">
    <!-- Pure CSS gradient background — no external images needed -->
    <div class="absolute inset-0 bg-gradient-to-br from-purple-900 via-purple-800 to-indigo-900"></div>
    <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(circle at 20% 50%, #fff 1px, transparent 1px), radial-gradient(circle at 80% 20%, #fff 1px, transparent 1px); background-size: 60px 60px;"></div>
    <!-- Glowing orbs -->
    <div class="absolute top-10 left-1/4 w-72 h-72 bg-purple-500 rounded-full opacity-10 blur-3xl"></div>
    <div class="absolute bottom-10 right-1/4 w-72 h-72 bg-pink-500 rounded-full opacity-10 blur-3xl"></div>

    <div class="relative container mx-auto px-6 text-center">
        <div class="reveal">
            <span class="inline-block bg-white/10 text-purple-200 text-xs font-semibold px-3 py-1 rounded-full mb-5 uppercase tracking-wide border border-white/20">Start today — it's free</span>
            <h3 class="text-4xl md:text-5xl font-black text-white mb-4">Ready to study smarter<br>and build faster?</h3>
            <p class="text-purple-200 text-lg mb-8 max-w-xl mx-auto leading-relaxed">Join hundreds of students already using StudyFlow. AI Tutor, flashcards, goals, and a full code editor — all free to start.</p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center">
                <a href="register.php" class="inline-block bg-white text-purple-700 px-10 py-4 rounded-xl font-black text-lg hover:bg-purple-50 transition-all shadow-2xl hover:-translate-y-0.5">
                    Get Started for Free →
                </a>
                <a href="login.php" class="inline-block bg-white/10 text-white border border-white/30 px-10 py-4 rounded-xl font-semibold text-lg hover:bg-white/20 transition-all">
                    Sign In
                </a>
            </div>
            <p class="text-purple-300 text-sm mt-5">No credit card required · Free plan forever · Cancel paid plans anytime</p>
        </div>
    </div>
</section>

</main>

<!-- ══════════════════ FOOTER ══════════════════ -->
<footer class="bg-gray-900 text-gray-400">
    <div class="container mx-auto px-6 py-14">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-10">
            <!-- Brand -->
            <div class="md:col-span-2">
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-7 h-7 bg-purple-600 rounded-lg flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253"/></svg>
                    </div>
                    <span class="text-white font-bold text-lg">StudyFlow</span>
                </div>
                <p class="text-sm leading-relaxed max-w-xs">The all-in-one productivity platform for students who want to study smarter and build faster — powered by free AI.</p>
                <div class="flex gap-3 mt-4">
                    <?php foreach ([['bg-blue-600','f'],['bg-sky-500','t'],['bg-gray-700','g']] as [$bg,$l]): ?>
                    <a href="#" class="w-8 h-8 <?php echo $bg; ?> rounded-lg flex items-center justify-center text-white text-xs font-bold hover:opacity-80 transition"><?php echo strtoupper($l); ?></a>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- Product -->
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Product</h4>
                <ul class="space-y-2 text-sm">
                    <?php foreach ([['#features','Features'],['#code-editor','Code Editor'],['#how-it-works','How It Works'],['#pricing','Pricing'],['#faq','FAQ']] as [$href,$lbl]): ?>
                    <li><a href="<?php echo $href; ?>" class="hover:text-purple-400 transition-colors"><?php echo $lbl; ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <!-- Legal -->
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Legal</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Privacy Policy</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Terms of Service</a></li>
                    <li><a href="#" class="hover:text-purple-400 transition-colors">Cookie Policy</a></li>
                </ul>
            </div>
            <!-- Account -->
            <div>
                <h4 class="text-white text-sm font-semibold uppercase tracking-widest mb-4">Account</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="login.php"    class="hover:text-purple-400 transition-colors">Sign In</a></li>
                    <li><a href="register.php" class="hover:text-purple-400 transition-colors">Create Account</a></li>
                    <li><a href="register.php" class="hover:text-purple-400 transition-colors">Free Plan</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-10 pt-6 border-t border-gray-800 flex flex-col md:flex-row justify-between items-center text-sm gap-4">
            <p>&copy; <?php echo date("Y"); ?> StudyFlow. All Rights Reserved.</p>
            <div class="flex items-center gap-2">
                <span class="text-xs bg-purple-900/50 text-purple-400 px-2 py-0.5 rounded border border-purple-800/50">Powered by OpenRouter AI</span>
            </div>
        </div>
    </div>
</footer>

<script>
// ── FAQ accordion ───────────────────────────────────
let openFaq = null;
document.querySelectorAll('.faq-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const content = btn.nextElementSibling;
        const arrow   = btn.querySelector('.faq-arrow');
        if (openFaq && openFaq !== content) {
            openFaq.style.maxHeight = null;
            openFaq.previousElementSibling.querySelector('.faq-arrow').style.transform = '';
        }
        if (content.style.maxHeight) {
            content.style.maxHeight = null;
            arrow.style.transform = '';
            openFaq = null;
        } else {
            content.style.maxHeight = content.scrollHeight + 'px';
            arrow.style.transform = 'rotate(180deg)';
            openFaq = content;
        }
    });
});

// ── Scroll reveal ────────────────────────────────────
const revealEls = document.querySelectorAll('.reveal');
const observer  = new IntersectionObserver((entries) => {
    entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); } });
}, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
revealEls.forEach(el => observer.observe(el));
// Make above-fold items instantly visible
revealEls.forEach(el => {
    const rect = el.getBoundingClientRect();
    if (rect.top < window.innerHeight) el.classList.add('visible');
});
</script>
</body>
</html>
