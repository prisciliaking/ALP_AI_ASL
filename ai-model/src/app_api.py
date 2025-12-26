from fastapi import FastAPI
from pydantic import BaseModel
import pickle
import os
import numpy as np

app = FastAPI()

##use the ai model, to parsing the input and give prediction##
## fastapi to laravel at controller##

# Load model dari folder model
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
MODEL_PATH = os.path.join(BASE_DIR, '..', 'model', 'asl_knn_model.p')

with open(MODEL_PATH, 'rb') as f:
    model_dict = pickle.load(f)
    model = model_dict['model']
    
class LandmarkInput(BaseModel):
    landmarks: list # Menerima 42 titik (x, y)
    
# --- LOGIKA NORMALISASI (Sama dengan Notebook kamu) ---
def normalize_input(row):
    # 1. Ambil Wrist (Indeks 0 dan 1)
    wrist_x, wrist_y = row[0], row[1]
    
    # 2. Kurangi setiap titik dengan Wrist (Shifting)
    temp_row = []
    for i in range(0, len(row), 2):
        temp_row.append(row[i] - wrist_x)     # x_relatif
        temp_row.append(row[i+1] - wrist_y)   # y_relatif
        
    # 3. Scaling (Invariansi Jarak)
    max_val = max(map(abs, temp_row))
    if max_val == 0: 
        max_val = 1 
        
    return [val / max_val for val in temp_row]

@app.post("/predict")
async def predict(input_data: LandmarkInput):
    try:
        # Ambil data dari request
        raw_coords = input_data.landmarks
        
        # Jalankan normalisasi
        final_features = normalize_input(raw_coords)
        
        # Prediksi menggunakan model KNN
        # Kita bungkus dalam [ ] karena model KNN expect 2D array
        prediction = model.predict([final_features])
        
        probabilities = model.predict_proba([final_features])
        confidence = np.max(probabilities) * 100  # Ambil nilai tertinggi dan jadikan %
        
        return {
            "status": "success",
            "prediction": str(prediction[0]),
            "confidence": round(confidence, 2) # Contoh: 95.45
        }
    except Exception as e:
        return {
            "status": "error",
            "message": str(e)
        }