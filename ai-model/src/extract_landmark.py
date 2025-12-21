import os  # buat ngelola file dan direktori
import pickle  # buat nyimpen data ke file .pkl

import mediapipe as mp
import cv2
import matplotlib.pyplot as plt
from tqdm import tqdm


mp_hands = mp.solutions.hands  # untuk mendeteksi tangan di mediapipe
mp_drawing = (
    mp.solutions.drawing_utils
)  # untuk menggambar titik2 tangan yang terdeteksi dalam bentuk skeleton
mp_drawing_styles = (
    mp.solutions.drawing_styles
)  # untuk menggambar skeleton tangan dengan style tertentu, style
# Jika mau kustom warna/ketebalan, buat mp_drawing.DrawingSpec(color=..., thickness=...).

hands = mp_hands.Hands(
    static_image_mode=True,  # model True itu untuk gambar diam, False itu untuk video. soalnya no tracking di gambar diam
    max_num_hands=1,  # max jumlah tangan yang dideteksi dalam 1 gambar
    min_detection_confidence=0.5,
)
# ini itu untuk nentuin seberapa yakin model mediapipe ini kalo dia nemu tangan di gambar


BASE_DIR = os.path.dirname(os.path.abspath(__file__))
DATA_DIR = os.path.normpath(os.path.join(BASE_DIR, "..", "data", "dataset"))


data = []  # list kosong buat nampung koordinat tangan semua gambar
labels = []  # list kosong buat nampung label semua gambar
success_count = 0
failed_count = 0
failed_files = []

# build a flat list of (label_dir, filename) so we can show progress with tqdm
image_files = []
stats_per_folder = {}
for dir_ in os.listdir(DATA_DIR):
    dir_full = os.path.join(DATA_DIR, dir_)
    if not os.path.isdir(dir_full):
        continue
    for img_path in os.listdir(dir_full):
        image_files.append((dir_, img_path))

# iterate with a progress bar
for dir_, img_path in tqdm(image_files, desc="Extracting landmarks", unit="img"):
    # Inisialisasi statistik untuk folder ini jika belum ada
    if dir_ not in stats_per_folder:
        stats_per_folder[dir_] = {"success": 0, "total": 0}

    stats_per_folder[dir_]["total"] += 1  # Tambah hitungan total file di folder ini
    data_aux = []
    img_full = os.path.join(DATA_DIR, dir_, img_path)
    img = cv2.imread(img_full)
    
    if img is None:
        failed_count += 1
        failed_files.append(os.path.join(dir_, img_path))
        continue

    try:
        img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    except Exception:
        failed_count += 1
        failed_files.append(os.path.join(dir_, img_path))
        continue

    results = hands.process(img_rgb)
    if results.multi_hand_landmarks:
        for hand_landmarks in results.multi_hand_landmarks:
            for i in range(len(hand_landmarks.landmark)):
                x = hand_landmarks.landmark[i].x
                y = hand_landmarks.landmark[i].y
                data_aux.append(x)
                data_aux.append(y)

        data.append(data_aux)
        labels.append(dir_)
        
        stats_per_folder[dir_]['success'] += 1
        success_count += 1
    else:
        failed_count += 1
        failed_files.append(os.path.join(dir_, img_path))

        #  for (
        #      hand_landmarks
        #  ) in results.multi_hand_landmarks:  # kalo ada tangan yang ke deteksi
        #      mp_drawing.draw_landmarks(  # gambar titik2 tangan yang ke deteksi
        #          img_rgb,  # foto yang mau digambar
        #          hand_landmarks,  # titik2 tangan yang ke deteksi
        #          mp_hands.HAND_CONNECTIONS,  # sambungan antar titik tangan
        #          mp_drawing_styles.get_default_hand_landmarks_style(),  # style titik tangan
        #          mp_drawing_styles.get_default_hand_connections_style(),
        #      )  # style sambungan titik tangan
        # ini itu untuk ngecek apakah gambar di dataset itu udah bener apa belum

        # plt.figure()
        # plt.imshow(img_rgb)

        # plt.show()
# simpen data dan labels ke file .pkl biar bisa dipake di lain waktu tanpa perlu ekstrak ulang


PROCESSED_DIR = os.path.normpath(os.path.join(BASE_DIR, "..", "data", "processed"))
os.makedirs(PROCESSED_DIR, exist_ok=True)
with open(os.path.join(PROCESSED_DIR, "data.pkl"), "wb") as f:
    pickle.dump({"data": data, "labels": labels}, f)

# 4. Print Laporan Akhir yang Rapi
print("\n" + "="*45)
print(f"{'Folder':<12} | {'Success':<10} | {'Total':<10} | {'%':<5}")
print("-" * 45)

for folder in sorted(stats_per_folder.keys()):
    s = stats_per_folder[folder]['success']
    t = stats_per_folder[folder]['total']
    perc = (s / t) * 100 if t > 0 else 0
    print(f"{folder:<12} | {s:<10} | {t:<10} | {perc:.1f}%")

print("="*45)
total_images = success_count + failed_count
print(f"OVERALL SUMMARY:")
print(f"✅ Success      : {success_count}")
print(f"❌ Failed       : {failed_count}")
print(f"📊 Total Images : {total_images}")
#print(f"📁 Data saved to: {PKL_PATH}")
#if failed_files:
#    print(f"⚠️ Failed list  : {FAILED_LOG_PATH}")
#print("="*45)