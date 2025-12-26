<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>ASL Recognition - ALP AI</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@mediapipe/hands/hands.js"></script>
    <script src="https://unpkg.com/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://unpkg.com/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-gray-900 text-white min-h-screen flex flex-col items-center justify-center p-8">
    <h1 class="text-5xl font-bold mb-4 bg-gradient-to-r from-purple-400 to-pink-500 bg-clip-text text-transparent">
        ASL Learning Center
    </h1>
    <p class="text-gray-400 mb-12 text-center max-w-xl">Pilih huruf yang ingin kamu pelajari hari ini:</p>

    <div class="flex flex-col gap-4 items-center">
        <div class="flex flex-wrap justify-center gap-3 max-w-3xl">
            @foreach(range('A', 'Z') as $char)
                <a href="{{ route('asl.learn', $char) }}" 
                   class="w-14 h-16 border border-purple-500/50 rounded-xl flex items-center justify-center font-bold text-2xl hover:bg-purple-600 hover:scale-110 transition-all">
                    {{ $char }}
                </a>
            @endforeach
        </div>
    </div>
</body>

</html>
