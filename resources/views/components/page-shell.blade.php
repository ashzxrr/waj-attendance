<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $title ?? 'WAJ Attendance' }}</title>
    <script src="{{ asset('vendor/tailwind/tailwind-play.js') }}"></script>
    @include('partials.base-url-meta')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

        body {
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', sans-serif;
            background:
                radial-gradient(circle at top right, rgba(251,191,36,0.20), transparent 38%),
                radial-gradient(circle at bottom left, rgba(253,230,138,0.20), transparent 32%),
                radial-gradient(circle at 50% 0%, rgba(254,243,199,0.35), transparent 45%),
                #FFFDF7;
            min-height: 100vh;
            color: #111827;
        }

        .soft-card {
            background: rgba(255,255,255,0.90);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(251,191,36,0.15);
            box-shadow: 0 20px 60px rgba(180,140,30,0.10);
        }

        .soft-chip {
            background: rgba(255,247,214,0.85);
            border: 1px solid rgba(251,191,36,0.2);
            color: #92400e;
        }
    </style>
</head>
<body class="min-h-screen overflow-x-hidden">
    {{ $slot }}
</body>
</html>
