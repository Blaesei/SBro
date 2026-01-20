"""
SpotBro ML Backend - Flask API
FIXED VERSION - Robust error handling & proper detection
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
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
            print(f"✅ Push-up model loaded: {pushup_model_path}")
        else:
            print(f"⚠️ Push-up model not found: {pushup_model_path}")
        
        # Load squat model
        squat_model_path = 'models/squat_form_classifier.pkl'
        if os.path.exists(squat_model_path):
            squat_analyzer = FormAnalyzer(
                model_path=squat_model_path,
                exercise='squat'
            )
            print(f"✅ Squat model loaded: {squat_model_path}")
        else:
            print(f"⚠️ Squat model not found: {squat_model_path}")
        
    except Exception as e:
        print(f"❌ Error loading models: {e}")
        import traceback
        traceback.print_exc()

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint"""
    return jsonify({
        'status': 'ok',
        'pushup_model_loaded': pushup_analyzer is not None,
        'squat_model_loaded': squat_analyzer is not None,
        'pushup_classes': list(pushup_analyzer.model_classes) if pushup_analyzer else [],
        'squat_classes': list(squat_analyzer.model_classes) if squat_analyzer else []
    })

@app.route('/api/analyze', methods=['POST'])
def analyze_frame():
    """
    Analyze a video frame from frontend - FIXED VERSION
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
        if exercise == 'pushup':
            analyzer = pushup_analyzer
        elif exercise == 'squat':
            analyzer = squat_analyzer
        else:
            return jsonify({
                'success': False,
                'error': f'Unknown exercise: {exercise}'
            }), 400
        
        if analyzer is None:
            return jsonify({
                'success': False,
                'error': f'No model loaded for {exercise}. Check backend logs.',
                'rep_count': 0,
                'feedback': f'⚠️ Model not loaded for {exercise}',
                'issues': [f'Model file missing for {exercise}'],
                'suggestions': ['Check if model file exists in models/ folder'],
                'confidence': 0,
                'rep_phase': 'UP',
                'form_score': 0,
                'position_valid': False,
                'position_msg': 'Model not available'
            }), 503
        
        # Decode base64 image - IMPROVED ERROR HANDLING
        try:
            # Remove data URL prefix if present
            image_data = data['image']
            if ',' in image_data:
                image_data = image_data.split(',')[1]
            
            # Decode base64
            image_bytes = base64.b64decode(image_data)
            nparr = np.frombuffer(image_bytes, np.uint8)
            frame = cv2.imdecode(nparr, cv2.IMREAD_COLOR)
            
            if frame is None:
                raise ValueError("Failed to decode image")
            
            # Validate frame dimensions
            if frame.shape[0] < 100 or frame.shape[1] < 100:
                raise ValueError("Image too small")
                
        except Exception as e:
            print(f"Image decode error: {e}")
            return jsonify({
                'success': False,
                'error': f'Image decode error: {str(e)}',
                'rep_count': 0,
                'feedback': '⚠️ Invalid image format',
                'issues': ['Could not process image'],
                'suggestions': ['Check camera permissions'],
                'confidence': 0,
                'rep_phase': 'UP',
                'form_score': 0,
                'position_valid': False,
                'position_msg': 'Image processing failed'
            }), 400
        
        # Analyze frame - WITH DETAILED ERROR HANDLING
        try:
            result = analyzer.analyze_frame(frame)
            
            # Ensure all required fields exist
            if not result:
                result = create_default_result()
            
            # Validate result structure
            required_fields = ['rep_count', 'feedback', 'confidence', 'rep_phase', 'position_valid', 'position_msg']
            for field in required_fields:
                if field not in result:
                    result[field] = get_default_value(field)
            
            # Calculate form score (0-100)
            if result.get('confidence', 0) > 0:
                issues = result.get('detailed_issues', [])
                perfect_form = len(issues) == 1 and "Perfect" in str(issues)
                
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
            
            # Return response with ALL required fields
            response = {
                'success': True,
                'rep_count': int(result.get('rep_count', 0)),
                'feedback': str(result.get('feedback', 'Analyzing...')),
                'issues': list(result.get('detailed_issues', [])),
                'suggestions': list(result.get('suggestions', [])),
                'confidence': float(result.get('confidence', 0)),
                'rep_phase': str(result.get('rep_phase', 'UP')),
                'form_score': round(form_score, 1),
                'elbow_angle': round(result.get('elbow_angle', 0), 1),
                'position_valid': bool(result.get('position_valid', False)),
                'position_msg': str(result.get('position_msg', ''))
            }
            
            return jsonify(response)
            
        except Exception as e:
            print(f"Analysis error: {e}")
            import traceback
            traceback.print_exc()
            
            return jsonify({
                'success': False,
                'error': f'Analysis failed: {str(e)}',
                'rep_count': 0,
                'feedback': '⚠️ Analysis error',
                'issues': ['Processing failed'],
                'suggestions': ['Ensure full body is visible'],
                'confidence': 0,
                'rep_phase': 'UP',
                'form_score': 0,
                'position_valid': False,
                'position_msg': 'Analysis error'
            }), 500
    
    except Exception as e:
        print(f"Error in analyze_frame: {e}")
        import traceback
        traceback.print_exc()
        return jsonify({
            'success': False,
            'error': f'Server error: {str(e)}',
            'rep_count': 0,
            'feedback': '⚠️ Server error',
            'issues': ['Unexpected error'],
            'suggestions': ['Try again'],
            'confidence': 0,
            'rep_phase': 'UP',
            'form_score': 0,
            'position_valid': False,
            'position_msg': 'Server error'
        }), 500

def create_default_result():
    """Create a safe default result"""
    return {
        'rep_count': 0,
        'feedback': 'Initializing...',
        'detailed_issues': ['Starting analysis...'],
        'suggestions': ['Position yourself in frame'],
        'confidence': 0,
        'rep_phase': 'UP',
        'position_valid': False,
        'position_msg': 'Initializing',
        'elbow_angle': 0
    }

def get_default_value(field):
    """Get default value for a field"""
    defaults = {
        'rep_count': 0,
        'feedback': 'Analyzing...',
        'confidence': 0,
        'rep_phase': 'UP',
        'position_valid': False,
        'position_msg': 'Checking position...',
        'form_score': 0,
        'elbow_angle': 0
    }
    return defaults.get(field, None)

@app.route('/api/reset', methods=['POST'])
def reset_counter():
    """Reset rep counter for an exercise"""
    try:
        data = request.json
        exercise = data.get('exercise', 'pushup').lower()
        
        if exercise == 'pushup':
            analyzer = pushup_analyzer
        elif exercise == 'squat':
            analyzer = squat_analyzer
        else:
            return jsonify({
                'success': False,
                'error': f'Unknown exercise: {exercise}'
            }), 400
        
        if analyzer:
            analyzer.rep_count = 0
            analyzer.last_rep_feedback = []
            analyzer.in_down_position = False
            analyzer.rep_phase = "UP"
            analyzer.prev_elbow_angle = 180
            
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
        print(f"Error in reset: {e}")
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/get_stats', methods=['GET'])
def get_current_stats():
    """Get current workout stats without analyzing a frame"""
    try:
        exercise = request.args.get('exercise', 'pushup').lower()
        
        if exercise == 'pushup':
            analyzer = pushup_analyzer
        elif exercise == 'squat':
            analyzer = squat_analyzer
        else:
            return jsonify({
                'success': False,
                'error': f'Unknown exercise: {exercise}'
            }), 400
        
        if not analyzer:
            return jsonify({
                'success': False,
                'error': 'Analyzer not initialized'
            }), 503
        
        return jsonify({
            'success': True,
            'exercise': exercise,
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