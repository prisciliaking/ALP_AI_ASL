import os
import pickle
import mediapipe as mp
import cv2
import numpy as np
from tqdm import tqdm

# --- 1. INISIALISASI MEDIAPIPE ---
mp_hands = mp.solutions.hands
hands = mp_hands.Hands(
    static_image_mode=True,
    max_num_hands=1,
    min_detection_confidence=0.2, 
)

# --- 2. FUNGSI RESTORASI ---
def preprocess_image(img):
    h, w = img.shape[:2]
    if w < 300 or h < 300:
        img = cv2.resize(img, None, fy=2, fx=2, interpolation=cv2.INTER_CUBIC)
    return img
 

# --- 3. PENGELOLAAN DIREKTORI ---
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.normpath(os.path.join(BASE_DIR, "..", "data", "dataset", "B"))

data = []
labels = []
success_count = 0
failed_count = 0
failed_files = []

# Ambil daftar file (Hanya sekali, jangan didobel)
image_files = [f for f in os.listdir(DATA_DIR) if f.lower().endswith(('.jpg', '.jpeg', '.png'))]
stats_per_folder = {"B": {"success": 0, "total": 0}}

# --- 4. PROSES EKSTRAKSI ---
# Perbaikan: Hanya pakai 1 variabel (img_name) karena image_files isinya cuma string nama file
for img_name in tqdm(image_files, desc="Extracting landmarks", unit="img"):
    stats_per_folder["B"]["total"] += 1
    img_full = os.path.join(DATA_DIR, img_name)
    img = cv2.imread(img_full)

    if img is None:
        failed_count += 1
        failed_files.append(img_name)
        continue

    try:

        # PERBAIKAN: Gunakan img_restored, BUKAN img asli
        img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    except Exception:
        failed_count += 1
        failed_files.append(img_name)
        continue

    results = hands.process(img_rgb)
    
    if results.multi_hand_landmarks:
        # Ambil hanya tangan pertama agar jumlah fitur konsisten (42 titik)
        hand_landmarks = results.multi_hand_landmarks[0]

        data_aux = []
        for landmark in hand_landmarks.landmark:
            data_aux.append(landmark.x)
            data_aux.append(landmark.y)

        data.append(data_aux)
        labels.append("B")
        stats_per_folder["B"]["success"] += 1
        success_count += 1
    else:
        failed_count += 1
        failed_files.append(img_name)

# --- 5. PENYIMPANAN ---
PROCESSED_DIR = os.path.normpath(os.path.join(BASE_DIR, "..", "data", "processed"))
os.makedirs(PROCESSED_DIR, exist_ok=True)

with open(os.path.join(PROCESSED_DIR, "b_data_restored.pkl"), "wb") as f:
    pickle.dump({"data": data, "labels": labels, "stats": stats_per_folder}, f)

# --- 6. LAPORAN ---
print("\n" + "=" * 45)
print(f"{'Folder':<12} | {'Success':<10} | {'Total':<10} | {'%':<5}")
print("-" * 45)

for folder in sorted(stats_per_folder.keys()):
    s = stats_per_folder[folder]["success"]
    t = stats_per_folder[folder]["total"]
    perc = (s / t) * 100 if t > 0 else 0
    print(f"{folder:<12} | {s:<10} | {t:<10} | {perc:.1f}%")

print("=" * 45)
print(f"OVERALL SUMMARY:")
print(f"✅ Success      : {success_count}")
print(f"❌ Failed       : {failed_count}")
print(f"📊 Total Images : {success_count + failed_count}")  