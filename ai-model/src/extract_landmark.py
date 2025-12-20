import os # buat ngelola file dan direktori
import pickle # buat nyimpen data ke file .pkl

import mediapipe as mp
import cv2
import matplotlib.pyplot as plt


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


DATA_DIR = "./data/dataset"


data =[] # list kosong buat nampung koordinat tangan semua gambar
labels = [] # list kosong buat nampung label semua gambar
success_count = 0
failed_count = 0
failed_files = []

for dir_ in os.listdir(DATA_DIR):
    for img_path in os.listdir(os.path.join(DATA_DIR, dir_)):
        
        data_aux = [] # list kosong buat nampung koordinat tangan tiap gambar
        img = cv2.imread(os.path.join(DATA_DIR, dir_, img_path))  
            # bgr itu format default opencv kepanjangannya blue green red
        img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)  
            # kita convert ke rgb biar sesuai sama format warna yang bener,
            # contohnya kalo ga di convert, merahnya bisa jadi biru

        results = hands.process(img_rgb)  # proses gambar buat deteksi tangan
        if results.multi_hand_landmarks:  # kalo ada tangan yang ke deteksi
            for hand_landmarks in results.multi_hand_landmarks: # iterasi tiap tangan yang ke deteksi
                for i in range(len(hand_landmarks.landmark)): # iterasi tiap titik tangan yang ke deteksi
                    x= hand_landmarks.landmark[i].x # print koordinat titik tangan yang ke deteksi x
                    y= hand_landmarks.landmark[i].y # print koordinat titik tangan yang ke deteksi y
                    
                    data_aux.append(x) # masukin koordinat x ke list data_aux
                    data_aux.append(y) # masukin koordinat y ke list data_aux
                    
            data.append(data_aux) # masukin list data_aux ke list data
            labels.append(dir_) # masukin label (nama folder) ke list labels. name of the directory is the label 
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

        plt.figure()
        plt.imshow(img_rgb)

        plt.show()

# simpen data dan labels ke file .pkl biar bisa dipake di lain waktu tanpa perlu ekstrak ulang
os.makedirs("data/processed", exist_ok=True)
with open("data/processed/data.pkl", "wb") as f:
    pickle.dump({'data': data, 'labels': labels}, f)

total_images = success_count + failed_count
print(f"Success: {success_count}")
print(f"Failed: {failed_count}")
print(f"Total images: {total_images}")