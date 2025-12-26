<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Belajar Huruf {{ $letter }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@mediapipe/hands/hands.js"></script>
    <script src="https://unpkg.com/@mediapipe/camera_utils/camera_utils.js"></script>
    <script src="https://unpkg.com/@mediapipe/drawing_utils/drawing_utils.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
</head>

<body class="bg-gray-900 text-white p-6">

    <div class="max-w-6xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <a href="/asl" class="text-purple-400 hover:underline">← Kembali ke Menu</a>
            <h1 class="text-3xl font-bold text-center">Belajar Huruf: <span
                    class="text-purple-500">{{ $letter }}</span></h1>
            <div class="w-24"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-gray-800 p-6 rounded-3xl border border-gray-700">
                <p class="text-center text-gray-400 mb-4 uppercase text-xs">Ikuti Gerakan Ini</p>
                <div class="bg-white rounded-2xl h-80 flex items-center justify-center p-4">
                    <img src="{{ asset('img/asl/' . $letter . '.jpg') }}" alt="Contoh Huruf {{ $letter }}"
                        class="h-full object-contain">
                </div>
            </div>

            <div class="relative bg-black rounded-3xl overflow-hidden border-4 border-gray-800">
                <video id="webcam" class="w-full h-full -scale-x-100" autoplay playsinline></video>
                <canvas id="output_canvas" width="640" height="480"
                    class="absolute top-0 left-0 w-full h-full -scale-x-100"></canvas>
                <div id="feedback-box"
                    class="absolute bottom-4 left-4 right-4 p-4 rounded-xl text-center font-bold text-xl hidden"></div>
            </div>
        </div>

        <div class="mt-6 bg-gray-800 p-4 rounded-2xl flex justify-around items-center">
            <div>
                <p class="text-xs text-gray-500 uppercase">Deteksi Sekarang</p>
                <p id="current-prediction" class="text-4xl font-black">-</p>
            </div>
            <div class="w-1/2">
                <p id="acc-text" class="text-right text-sm mb-1">0%</p>
                <div class="w-full bg-gray-700 rounded-full h-3">
                    <div id="accuracy-bar" class="bg-blue-600 h-3 rounded-full transition-all" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>

    <div id="success-modal"
        class="fixed inset-0 z-50 flex items-center justify-center hidden bg-black/80 backdrop-blur-sm">
        <div
            class="bg-gray-800 border-2 border-green-500 p-10 rounded-3xl text-center shadow-2xl transform scale-95 transition-all duration-300 max-w-sm w-full">
            <div class="w-24 h-24 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-6">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <h2 class="text-3xl font-bold mb-2 text-white">Luar Biasa!</h2>
            <p class="text-gray-400 mb-8">Kamu berhasil memperagakan huruf <span
                    class="text-green-500 font-bold">{{ $letter }}</span> dengan sempurna.</p>

            <a href="/asl"
                class="block w-full py-4 bg-green-600 hover:bg-green-500 text-white font-bold rounded-xl transition-colors shadow-lg shadow-green-900/20">
                Kembali ke Menu Utama
            </a>
        </div>
    </div>

    <script>
        // 1. Variabel Global (Ditaruh di luar agar tidak ter-reset)
        const activeTarget = "{{ $letter }}";
        let matchStartTime = null;
        let isSuccess = false;
        let lastCall = 0;

        // Setting Axios
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]').getAttribute(
            'content');

        const videoElement = document.getElementById('webcam');
        const canvasElement = document.getElementById('output_canvas');
        const canvasCtx = canvasElement.getContext('2d');
        const predictionDisplay = document.getElementById('current-prediction');
        const feedbackBox = document.getElementById('feedback-box');
        const accuracyBar = document.getElementById('accuracy-bar');
        const accuracyText = document.getElementById('acc-text');

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
                    drawConnectors(canvasCtx, landmarks, HAND_CONNECTIONS, {
                        color: '#00FF00',
                        lineWidth: 5
                    });
                    drawLandmarks(canvasCtx, landmarks, {
                        color: '#FF0000',
                        lineWidth: 2
                    });

                    let coords = [];
                    landmarks.forEach(point => {
                        coords.push(point.x);
                        coords.push(point.y);
                    });

                    // Panggil fungsi prediksi
                    throttlePrediction(coords);
                }
            } else {
                if (!isSuccess) feedbackBox.classList.add('hidden');
                matchStartTime = null; // Reset timer jika tangan hilang
            }
            canvasCtx.restore();
        });

        function throttlePrediction(landmarks) {
            if (isSuccess) return;

            const now = Date.now();
            if (now - lastCall > 500) {
                lastCall = now;

                axios.post('/predict', {
                        landmarks: landmarks
                    })
                    .then(res => {
                        // Cek log di F12 untuk memastikan data confidence masuk
                        console.log("Respon API:", res.data);

                        if (res.data.prediction) {
                            const pred = res.data.prediction;
                            const conf = res.data.confidence;

                            // UPDATE UI PERSENTASE
                            predictionDisplay.innerText = pred;
                            accuracyText.innerText = conf + '%';
                            accuracyBar.style.width = conf + '%';

                            // LOGIKA VALIDASI
                            if (pred === activeTarget && conf > 75) {
                                if (matchStartTime === null) {
                                    matchStartTime = Date.now();
                                }

                                let duration = Date.now() - matchStartTime;

                                if (duration >= 1000) {
                                    showSuccessPopup();
                                } else {
                                    feedbackBox.innerText = "Tahan Posisi... ⏳";
                                    feedbackBox.className =
                                        "absolute bottom-4 left-4 right-4 p-4 rounded-xl text-center font-bold text-xl bg-blue-500 text-white block";
                                    accuracyBar.className = "bg-blue-400 h-3 rounded-full transition-all";
                                }
                            } else {
                                matchStartTime = null; // Reset jika gerakan goyang/salah

                                if (pred === activeTarget && conf > 40) {
                                    feedbackBox.innerText = "Hampir Benar, Perjelas! ✋";
                                    feedbackBox.className =
                                        "absolute bottom-4 left-4 right-4 p-4 rounded-xl text-center font-bold text-xl bg-yellow-500 text-white block";
                                    accuracyBar.className = "bg-yellow-500 h-3 rounded-full transition-all";
                                } else if (pred !== activeTarget && conf > 60) {
                                    feedbackBox.innerText = `Itu huruf ${pred}, bukan ${activeTarget} ❌`;
                                    feedbackBox.className =
                                        "absolute bottom-4 left-4 right-4 p-4 rounded-xl text-center font-bold text-xl bg-red-600 text-white block";
                                    accuracyBar.className = "bg-red-600 h-3 rounded-full transition-all";
                                } else {
                                    feedbackBox.classList.add('hidden');
                                    accuracyBar.className = "bg-blue-600 h-3 rounded-full transition-all";
                                }
                            }
                        }
                    })
                    .catch(err => console.error("Error API:", err));
            }
        }

        function showSuccessPopup() {
            if (isSuccess) return;
            isSuccess = true;

            const modal = document.getElementById('success-modal');
            modal.classList.remove('hidden');

            setTimeout(() => {
                modal.firstElementChild.classList.remove('scale-95');
                modal.firstElementChild.classList.add('scale-100');
            }, 10);

            // Berhenti menjalankan kamera
            if (typeof camera !== 'undefined') {
                camera.stop();
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
