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

<body class="bg-gray-900 text-white min-h-screen flex flex-col items-center p-8">

    <h1 class="text-3xl font-bold mb-6 text-blue-400">ASL Real-Time Recognition</h1>

    <div class="relative flex flex-col md:flex-row gap-8 items-start">
        <div class="relative bg-black rounded-xl overflow-hidden shadow-2xl border-4 border-gray-700">
            <video id="webcam" class="w-[640px] h-[480px] -scale-x-100" autoplay></video>
            <canvas id="output_canvas" width="640" height="480"
                class="absolute top-0 left-0 w-[640px] h-[480px] -scale-x-100"></canvas>
        </div>

        <div class="bg-gray-800 p-8 rounded-xl shadow-xl border border-gray-700 min-w-[250px] text-center">
            <h2 class="text-xl font-semibold mb-4 text-gray-400 uppercase tracking-widest">Hasil Prediksi</h2>
            <div id="prediction-text" class="text-9xl font-black text-blue-500 animate-pulse">-</div>

            <div class="mt-6">
                <div class="flex justify-between mb-1">
                    <span class="text-sm font-medium text-blue-400">Accuracy</span>
                    <span id="accuracy-percent" class="text-sm font-medium text-blue-400">0%</span>
                </div>
                <div class="w-full bg-gray-700 rounded-full h-2.5">
                    <div id="accuracy-bar" class="bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>


    <!-- SCRIPT JAVASCRIPT UNTUK DETEKSI TANGAN DAN PREDIKSI -->
    <script>
        // Setting Axios untuk Laravel CSRF
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute(
            'content');

        const videoElement = document.getElementById('webcam');
        const canvasElement = document.getElementById('output_canvas');
        const canvasCtx = canvasElement.getContext('2d');
        const predictionText = document.getElementById('prediction-text');

        const hands = new Hands({
            locateFile: (file) => `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`
        });

        hands.setOptions({
            maxNumHands: 1,
            modelComplexity: 1,
            minDetectionConfidence: 0.5,
            minTrackingConfidence: 0.5
        });

        hands.onResults((results) => {
            canvasCtx.save();
            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);

            if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
                for (const landmarks of results.multiHandLandmarks) {
                    // MENGGAMBAR: Titik dan garis tangan agar terlihat di web
                    drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {
                        color: '#00FF00',
                        lineWidth: 5
                    });
                    drawLandmarks(canvasCtx, landmarks, {
                        color: '#FF0000',
                        lineWidth: 2
                    });

                    // Siapkan data 42 koordinat
                    let coords = [];
                    landmarks.forEach(point => {
                        coords.push(point.x);
                        coords.push(point.y);
                    });
                    throttlePrediction(coords);
                }
            }
            canvasCtx.restore();
        });

        let lastCall = 0;

        function throttlePrediction(landmarks) {
            const now = Date.now();
            if (now - lastCall > 500) {
                lastCall = now;

                // Pastikan route '/predict' sudah ada di web.php
                axios.post('/predict', {
                        landmarks: landmarks
                    })
                    .then(res => {
                        if (res.data.prediction) {
                            // Update Huruf
                            predictionText.innerText = res.data.prediction;

                            // Update Akurasi
                            const confidence = res.data.confidence;
                            document.getElementById('accuracy-percent').innerText = confidence + '%';
                            document.getElementById('accuracy-bar').style.width = confidence + '%';

                            // Opsional: Ubah warna bar kalau akurasi rendah
                            if (confidence < 50) {
                                document.getElementById('accuracy-bar').classList.replace('bg-blue-600', 'bg-red-500');
                            } else {
                                document.getElementById('accuracy-bar').classList.replace('bg-red-500', 'bg-blue-600');
                            }
                        }
                    })
            }
        }

        const camera = new Camera(videoElement, {
            onFrame: async () => {
                await hands.send({
                    image: videoElement
                });
            },
            width: 640,
            height: 480
        });
        camera.start();
    </script>
</body>

</html>
