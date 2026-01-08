# Download Dataset
Download dataset melalui link berikut:
https://drive.google.com/file/d/1JJDV5La9TDuBQ4oS69LYxJ0v5W04lnE6/view?usp=sharing

Setelah didownload, letakkan dataset ke dalam folder:
ai-model/data/dataset

Buat folder baru dalam ai-model/data > processed

## Ekstrak dataset untuk mendapatkan landmark gambar
Terminal jalankan dalam ai-model/src: python extract_landmark.py

## Train dataset untuk mendapatkan model AI
Jalankan python notebook: knn_train.ipynb

# Menjalankan AI Model (FastAPI)
Masuk ke folder src pada ai-model:
cd ai-model/src

Jalankan server FastAPI:
uvicorn app_api:app

Server AI akan berjalan secara default di:
http://127.0.0.1:8000

# Menjalankan Laravel Application
Masuk ke folder Laravel:
cd laravel-app

Jalankan frontend (Vite):
npm run dev

Jalankan server PHP:
php -S 127.0.0.1:9090 -t public

Buka aplikasi melalui browser:
http://127.0.0.1:9090

# Catatan Penting
- Pastikan AI Model dijalankan terlebih dahulu sebelum Laravel.
- Pastikan tidak ada konflik port (8000 dan 9090).
- Dataset wajib berada di folder yang sesuai.

# Troubleshooting
Jika uvicorn tidak ditemukan:
pip install uvicorn fastapi

Jika npm run dev error:
npm install

Pastikan permission folder Laravel sudah benar.
