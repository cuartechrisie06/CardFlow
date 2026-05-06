<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>CardFlow</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/css/app.css')
</head>
<body class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md p-6 bg-white shadow rounded">
        {{ $slot }}
    </div>
</body>
</html>