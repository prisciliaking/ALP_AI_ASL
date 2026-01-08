# American Sign Language Alpabeth 🤖

Proyek ini merupakan integrasi antara **AI Model (FastAPI)** untuk pengenalan gesture dan **Laravel Application** sebagai antarmuka pengguna.

## 📋 Daftar Isi

* [Prasyarat](https://www.google.com/search?q=%23prasyarat)
* [Instalasi AI Model (Backend)](https://www.google.com/search?q=%23instalasi-ai-model-backend)
* [Persiapan Dataset & Training](https://www.google.com/search?q=%23persiapan-dataset--training)
* [Menjalankan AI Model](https://www.google.com/search?q=%23menjalankan-ai-model)
* [Instalasi Laravel (Frontend)](https://www.google.com/search?q=%23instalasi-laravel-frontend)
* [Troubleshooting](https://www.google.com/search?q=%23troubleshooting)

---

## 🛠 Prasyarat

Sebelum memulai, pastikan perangkat Anda sudah terinstall:

* **Python 3.8+**
* **PHP 8.x** & **Composer**
* **Node.js** & **NPM**

---

## 🐍 Instalasi AI Model (Backend)

1. **Masuk ke direktori model:**
```bash
cd ai-model

```


2. **Buat & Aktifkan Virtual Environment:**
* **Windows (CMD):**
```bash
python -m venv venv
venv\Scripts\activate

```


* **Windows (PowerShell):**
```bash
python -m venv venv
.\venv\Scripts\Activate.ps1

```


* **macOS / Linux:**
```bash
python3 -m venv venv
source venv/bin/activate

```




3. **Konfigurasi Environment:**
Salin file `.env.example` menjadi `.env`:
* **Windows:** `copy .env.example .env`
* **Mac/Linux:** `cp .env.example .env`


4. **Install Dependencies:**
```bash
pip install -r requirements.txt

```



---

## 📊 Persiapan Dataset & Training

1. **Download Dataset:** [Klik di sini untuk download dataset](https://drive.google.com/file/d/1JJDV5La9TDuBQ4oS69LYxJ0v5W04lnE6/view?usp=sharing).
2. **Penempatan:** Ekstrak dan letakkan dataset ke dalam folder `ai-model/data/dataset`.
3. **Persiapan Folder:** Pastikan folder `ai-model/data/processed` sudah tersedia.
4. **Ekstrak Landmark:**
```bash
cd src
python extract_landmark.py

```


5. **Training Model:** Jalankan file `knn_train.ipynb` menggunakan Jupyter Notebook/VS Code untuk menghasilkan model AI.

---

## 🚀 Menjalankan AI Model

Jalankan perintah ini di dalam folder `ai-model/src` dengan kondisi *venv* aktif:

```bash
uvicorn app_api:app

```

Server AI akan berjalan di: (http://127.0.0.1:8000)

---

## 💻 Instalasi Laravel (Frontend)

Buka **terminal baru** dan ikuti langkah berikut:

1. **Masuk ke direktori Laravel:**
```bash
cd laravel-app

```


2. **Konfigurasi Environment:**
* Salin file: `copy .env.example .env` (Windows) atau `cp .env.example .env` (Linux/Mac)
* Generate Key: `php artisan key:generate`


3. **Install Dependencies:**
```bash
composer install
npm install

```


4. **Jalankan Aplikasi:**
* **Terminal 1 (Vite):** `npm run dev`
* **Terminal 2 (PHP Server):** `php -S 127.0.0.1:9090 -t public`



*Akses aplikasi melalui browser di: **[http://127.0.0.1:9090*](http://127.0.0.1:9090)**

---

## 🛠 Troubleshooting

* **Jika uvicorn tidak ditemukan:**
`pip install uvicorn fastapi`
* **Jika npm run dev error:**
`npm install`
`composer install`
* **Pastikan Folder yang dibutuhkan sudah ada:**
* `ai-model/data`
* `ai-model/data/dataset`
* `ai-model/data/processed`
* `ai-model/model`


* **Pastikan permission folder Laravel sudah benar.**

---

**Catatan Penting:**

* Jalankan **AI Model (FastAPI)** terlebih dahulu sebelum menjalankan Laravel.
* Pastikan tidak ada konflik port pada port **8000** dan **9090**.

---
 