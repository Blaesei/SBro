"""
SpotBro Backend API
Serves ML predictions to frontend
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import sys
sys.path.append('../ml/src')

from ml_backend.ml.inference import FormAnalyzer

app = Flask(__name__)
CORS(app)  # Allow frontend to call API

# Initialize analyzer (load model once at startup)
analyzer = FormAnalyzer(
    model_path='../ml/models/pushup_form_classifier.pkl',
    exercise='pushup'
)

@app.route('/api/analyze', methods=['POST'])
def analyze_frame():
    """
    Analyze a video frame from frontend
    
    Request: { "image": "base64_string" }
    Response: { 
        "rep_count": 5,
        "feedback": "Rep #5 - Perfect form!",
        "issues": ["✓ Perfect form!"],
        "suggestions": ["Keep it up!"],
        "confidence": 0.92
    }
    """
    try:
        data = request.json
        
        # Decode base64 image
        image_data = base64.b64decode(data['image'].split(',')[1])
        nparr = np.frombuffer(image_data, np.uint8)
        frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
        
        # Analyze frame
        result = analyzer.analyze_frame(frame)
        
        return jsonify({
            'rep_count': result['rep_count'],
            'feedback': result['feedback'],
            'issues': result['detailed_issues'],
            'suggestions': result['suggestions'],
            'confidence': float(result['confidence']),
            'rep_phase': result['rep_phase']
        })
    
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/api/reset', methods=['POST'])
def reset_counter():
    """Reset rep counter"""
    analyzer.rep_count = 0
    analyzer.last_rep_feedback = []
    return jsonify({'message': 'Counter reset', 'rep_count': 0})

@app.route('/api/health', methods=['GET'])
def health():
    """Health check"""
    return jsonify({'status': 'ok', 'model_loaded': True})

if __name__ == '__main__':
    print("Starting SpotBro Backend API...")
    print("ML Model loaded successfully!")
    print("API running on http://localhost:5000")
    app.run(host='0.0.0.0', port=5000, debug=True)