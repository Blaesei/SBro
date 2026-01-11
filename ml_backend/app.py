"""
SpotBro ML Backend - Flask API
Serves real-time ML predictions to frontend
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import joblib
import sys
import os

# Add ml modules to path
sys.path.append(os.path.join(os.path.dirname(__file__), 'ml'))

from ml.pose_detector import PoseDetector
from ml.feature_extractor import FeatureExtractor
from ml.inference import FormAnalyzer

app = Flask(__name__)
CORS(app)  # Enable CORS for frontend access

# Global analyzer instances (loaded once at startup)
pushup_analyzer = None
squat_analyzer = None

def initialize_analyzers():
    """Load ML models at startup"""
    global pushup_analyzer, squat_analyzer
    
    try:
        # Load push-up model
        pushup_model_path = 'models/pushup_form_classifier.pkl'
        if os.path.exists(pushup_model_path):
            pushup_analyzer = FormAnalyzer(
                model_path=pushup_model_path,
                exercise='pushup'
            )
            print(f"✓ Push-up model loaded: {pushup_model_path}")
        else:
            print(f"⚠ Push-up model not found: {pushup_model_path}")
        
        # Load squat model (if available)
        squat_model_path = 'models/squat_form_classifier.pkl'
        if os.path.exists(squat_model_path):
            squat_analyzer = FormAnalyzer(
                model_path=squat_model_path,
                exercise='squat'
            )
            print(f"✓ Squat model loaded: {squat_model_path}")
        
    except Exception as e:
        print(f"Error loading models: {e}")

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'ok',
        'pushup_model_loaded': pushup_analyzer is not None,
        'squat_model_loaded': squat_analyzer is not None
    })

@app.route('/api/analyze', methods=['POST'])
def analyze_frame():
    """
    Analyze a video frame from frontend
    
    Request JSON:
    {
        "image": "base64_string",
        "exercise": "pushup" or "squat"
    }
    
    Response JSON:
    {
        "success": true,
        "rep_count": 5,
        "feedback": "Rep #5 - Perfect form!",
        "issues": ["✓ Perfect form!"],
        "suggestions": ["Keep it up!"],
        "confidence": 0.92,
        "rep_phase": "UP",
        "form_score": 95.5,
        "position_valid": true
    }
    """
    try:
        # Parse request
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image provided'
            }), 400
        
        exercise = data.get('exercise', 'pushup').lower()
        
        # Select appropriate analyzer
        analyzer = pushup_analyzer if exercise == 'pushup' else squat_analyzer
        
        if analyzer is None:
            return jsonify({
                'success': False,
                'error': f'No model loaded for {exercise}'
            }), 503
        
        # Decode base64 image
        try:
            # Remove data URL prefix if present
            image_data = data['image']
            if ',' in image_data:
                image_data = image_data.split(',')[1]
            
            image_bytes = base64.b64decode(image_data)
            nparr = np.frombuffer(image_bytes, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            if frame is None:
                raise ValueError("Failed to decode image")
                
        except Exception as e:
            return jsonify({
                'success': False,
                'error': f'Image decode error: {str(e)}'
            }), 400
        
        # Analyze frame
        result = analyzer.analyze_frame(frame)
        
        # Calculate form score (0-100)
        if result['confidence'] > 0:
            # Score based on prediction and issues
            issues = result.get('detailed_issues', [])
            perfect_form = len(issues) == 1 and "Perfect form" in str(issues)
            
            if perfect_form:
                form_score = min(95 + result['confidence'] * 5, 100)
            elif len(issues) <= 1:
                form_score = 85 + result['confidence'] * 10
            elif len(issues) <= 2:
                form_score = 75 + result['confidence'] * 10
            else:
                form_score = 60 + result['confidence'] * 10
        else:
            form_score = 0
        
        # Return response
        return jsonify({
            'success': True,
            'rep_count': result.get('rep_count', 0),
            'feedback': result.get('feedback', 'Analyzing...'),
            'issues': result.get('detailed_issues', []),
            'suggestions': result.get('suggestions', []),
            'confidence': float(result.get('confidence', 0)),
            'rep_phase': result.get('rep_phase', 'UP'),
            'form_score': round(form_score, 1),
            'elbow_angle': round(result.get('elbow_angle', 0), 1),
            'position_valid': result.get('position_valid', False),
            'position_msg': result.get('position_msg', '')
        })
    
    except Exception as e:
        print(f"Error in analyze_frame: {e}")
        return jsonify({
            'success': False,
            'error': f'Analysis error: {str(e)}'
        }), 500

@app.route('/api/reset', methods=['POST'])
def reset_counter():
    """Reset rep counter for an exercise"""
    try:
        data = request.json
        exercise = data.get('exercise', 'pushup').lower()
        
        analyzer = pushup_analyzer if exercise == 'pushup' else squat_analyzer
        
        if analyzer:
            analyzer.rep_count = 0
            analyzer.last_rep_feedback = []
            analyzer.in_down_position = False
            analyzer.rep_phase = "UP"
            
            return jsonify({
                'success': True,
                'message': f'{exercise.capitalize()} counter reset',
                'rep_count': 0
            })
        else:
            return jsonify({
                'success': False,
                'error': 'Analyzer not initialized'
            }), 503
    
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/get_stats', methods=['GET'])
def get_current_stats():
    """Get current workout stats without analyzing a frame"""
    try:
        exercise = request.args.get('exercise', 'pushup').lower()
        analyzer = pushup_analyzer if exercise == 'pushup' else squat_analyzer
        
        if not analyzer:
            return jsonify({
                'success': False,
                'error': 'Analyzer not initialized'
            }), 503
        
        return jsonify({
            'success': True,
            'rep_count': analyzer.rep_count,
            'rep_phase': analyzer.rep_phase,
            'last_feedback': analyzer.last_rep_feedback
        })
    
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

if __name__ == '__main__':
    print("=" * 60)
    print("SpotBro ML Backend - Starting")
    print("=" * 60)
    
    # Initialize models
    initialize_analyzers()
    
    print("\nAPI Endpoints:")
    print("  GET  /api/health          - Health check")
    print("  POST /api/analyze         - Analyze frame")
    print("  POST /api/reset           - Reset counter")
    print("  GET  /api/get_stats       - Get current stats")
    print("=" * 60)
    print("\nStarting Flask server on http://localhost:5000")
    print("Press Ctrl+C to stop")
    print("=" * 60 + "\n")
    
    app.run(host='0.0.0.0', port=5000, debug=True)