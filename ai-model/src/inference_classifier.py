import cv2
import mediapipe as mp
import pickle
import numpy as np

import os
import pickle

# 1. Dapatkan lokasi folder 'src' tempat script ini berada
BASE_DIR = os.path.dirname(os.path.abspath(__file__))

# 2. Arahkan ke file model (naik satu tingkat ke 'ai-model', lalu masuk ke 'model')
# Pastikan nama foldernya 'model' (tanpa 's') sesuai info yang kamu berikan
model_path = os.path.join(BASE_DIR, "..", "model", "asl_knn_model.p")

# 3. Verifikasi apakah file ada sebelum dibuka (Opsional tapi membantu debugging)
if not os.path.exists(model_path):
    print(f"❌ ERROR: File tidak ditemukan di: {os.path.abspath(model_path)}")
else:
    with open(model_path, "rb") as f:
        model_dict = pickle.load(f)
        model = model_dict["model"]
    print("✅ Model berhasil dimuat!")

# 2. Inisialisasi MediaPipe
mp_hands = mp.solutions.hands
hands = mp_hands.Hands(
    static_image_mode=False, max_num_hands=1, min_detection_confidence=0.5
)
mp_drawing = mp.solutions.drawing_utils

cap = cv2.VideoCapture(0)

while True:
    ret, frame = cap.read()
    if not ret:
        break

    H, W, _ = frame.shape
    frame_rgb = cv2.cvtColor(frame, cv2.COLOR_BGR2RGB)
    results = hands.process(frame_rgb)

    if results.multi_hand_landmarks:
        for hand_landmarks in results.multi_hand_landmarks:
            # 1. Ekstrak seluruh 21 titik ke dalam list flat (42 angka)
            raw_coords = []
            for i in range(21):
                raw_coords.append(hand_landmarks.landmark[i].x)
                raw_coords.append(hand_landmarks.landmark[i].y)

                # --- MULAI NORMALISASI (Sama dengan fungsi training kamu) ---

            # Step 1: Ambil Wrist (Indeks 0 dan 1)
            wrist_x, wrist_y = raw_coords[0], raw_coords[1]

            # Step 2: Shifting (Kurangi setiap titik dengan Wrist)
            temp_row = []
            for i in range(0, len(raw_coords), 2):
                temp_row.append(raw_coords[i] - wrist_x)  # x_relatif
                temp_row.append(raw_coords[i + 1] - wrist_y)  # y_relatif

            # Step 3: Scaling (Bagi dengan nilai absolut maksimum)
            max_val = max(map(abs, temp_row))
            if max_val == 0:
                max_val = 1  # Cegah pembagian nol

            final_normalized_data = [val / max_val for val in temp_row]

            # D. Prediksi
            prediction = model.predict([final_normalized_data])
            predicted_character = prediction[0]

            # D. Visualisasi
            mp_drawing.draw_landmarks(frame, hand_landmarks, mp_hands.HAND_CONNECTIONS)
            cv2.putText(
                frame,
                f"Huruf: {predicted_character}",
                (50, 50),
                cv2.FONT_HERSHEY_SIMPLEX,
                1.3,
                (0, 255, 0),
                3,
                cv2.LINE_AA,
            )

    cv2.imshow("ASL Recognition - KNN", frame)
    if cv2.waitKey(1) & 0xFF == ord("q"):
        break

cap.release()
cv2.destroyAllWindows()
