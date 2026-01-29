from flask import Flask, request, jsonify
import face_recognition
import mysql.connector
import numpy as np
import cv2
import os
import json

app = Flask(__name__)

# Configurações de Banco
DB_HOST = os.environ.get('DB_HOST', 'ponto_db')
DB_USER = os.environ.get('DB_USER', 'user')
DB_PASS = os.environ.get('DB_PASSWORD', 'password')
DB_NAME = os.environ.get('DB_DATABASE', 'db_pontoeletronico')

def get_db_connection():
    return mysql.connector.connect(
        host=DB_HOST, user=DB_USER, password=DB_PASS, database=DB_NAME, ssl_disabled=True
    )

@app.route('/reconhecer', methods=['POST'])
def reconhecer():
    if 'imagem' not in request.files:
        return jsonify({"status": "erro", "mensagem": "Nenhuma imagem enviada"})

    try:
        # Ler a imagem da Webcam
        file = request.files['imagem']
        npimg = np.frombuffer(file.read(), np.uint8)
        img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
        
        # Converte BGR OpenCV para RGB face_recognition usa RGB
        rgb_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Detecta rosto e gerar encoding 128 números
        encodings = face_recognition.face_encodings(rgb_img)
        
        if len(encodings) == 0:
            return jsonify({"status": "erro", "mensagem": "Nenhum rosto encontrado na foto."})
            
        webcam_encoding = encodings[0] # Pega o primeiro rosto achado

        # Busca usuários no banco
        conn = get_db_connection()
        cursor = conn.cursor(dictionary=True)
        cursor.execute("SELECT id, name, face_encoding FROM users WHERE active = 1 AND face_encoding IS NOT NULL")
        usuarios = cursor.fetchall()
        cursor.close()
        conn.close()

        melhor_match = None
        menor_distancia = 1.0
        
        # Limite de tolerância (0.6 é o padrão do dlib, quanto menor, mais rigoroso)
        TOLERANCIA = 0.6 

        print(f"--- Iniciando Comparação ({len(usuarios)} usuários) ---")

        for user in usuarios:
            try:
                # Limpa string e converte JSON para lista
                clean_str = user['face_encoding'].replace("'", '"')
                db_encoding = json.loads(clean_str)
                
                # O face_recognition espera numpy array
                db_numpy = np.array(db_encoding)

                # Calcula a distância
                distancia = face_recognition.face_distance([db_numpy], webcam_encoding)[0]
                
                print(f"User: {user['name']} | Dist: {distancia:.4f}")

                if distancia < TOLERANCIA and distancia < menor_distancia:
                    menor_distancia = distancia
                    melhor_match = {
                        "usuario_id": user['id'],
                        "nome": user['name'],
                        "confianca": f"{((1-distancia)*100):.2f}%"
                    }

            except Exception as e:
                continue

        if melhor_match:
            print(f"MATCH ENCONTRADO: {melhor_match['nome']}")
            return jsonify({"status": "sucesso", **melhor_match})
        else:
            print("Nenhum match dentro da tolerância.")
            return jsonify({"status": "falha", "mensagem": "Rosto não reconhecido"})

    except Exception as e:
        print(f"Erro Fatal: {e}")
        return jsonify({"status": "erro", "mensagem": f"Erro interno: {str(e)}"})

@app.route('/gerar_biometria', methods=['POST'])
def gerar_biometria():
    if 'imagem' not in request.files:
         return jsonify({"status": "erro", "mensagem": "Imagem ausente"})
    
    try:
        file = request.files['imagem']
        npimg = np.frombuffer(file.read(), np.uint8)
        img = cv2.imdecode(npimg, cv2.IMREAD_COLOR)
        rgb_img = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
        
        # Gera encoding
        encodings = face_recognition.face_encodings(rgb_img)
        
        if len(encodings) > 0:
            # Converte para lista Python normal para salvar no JSON
            encoding_list = encodings[0].tolist()
            return jsonify({"status": "sucesso", "face_encoding": json.dumps(encoding_list)})
        else:
            return jsonify({"status": "erro", "mensagem": "Nenhum rosto detectado na imagem enviada."})
            
    except Exception as e:
        return jsonify({"status": "erro", "mensagem": str(e)})

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000)
