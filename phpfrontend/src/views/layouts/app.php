<!doctype html>
<html lang="en" class="h-full" :class="{ 'dark': darkMode }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Clock-It') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="/assets/css/app.css">
    <script defer src="/assets/js/app.js"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {}
            }
        }
    </script>
</head>
<body x-data="{ darkMode: true }" :class="darkMode ? 'dark bg-[#0f172a] text-slate-100' : 'bg-slate-50 text-slate-900'" class="min-h-full h-full antialiased transition-colors duration-200">
    <div class="flex h-screen w-screen overflow-hidden">
        <?= $content ?>
    </div>
</body>
</html>