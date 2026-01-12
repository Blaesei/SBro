"""
SpotBro ML Backend - Multi-Exercise Support
COMPLETE VERSION with Push-up, Squat, and Plank
"""
from flask import Flask, request, jsonify
from flask_cors import CORS
import cv2
import numpy as np
import base64
import sys
import os

sys.path.append(os.path.join(os.path.dirname(__file__), 'ml'))

from ml.inference import FormAnalyzer
from ml.plank_analyzer import PlankAnalyzer

app = Flask(__name__)
CORS(app)

# Global analyzers dictionary
analyzers = {}

def initialize_analyzers():
    """Load all exercise analyzers at startup"""
    global analyzers
    
    print("=" * 70)
    print("INITIALIZING SPOTBRO ML BACKEND")
    print("=" * 70)
    
    # Push-up analyzer
    try:
        pushup_model = 'models/pushup_form_classifier.pkl'
        if os.path.exists(pushup_model):
            analyzers['pushup'] = FormAnalyzer(
                model_path=pushup_model,
                exercise='pushup'
            )
            print("✓ Push-up analyzer loaded")
        else:
            print(f"✗ Push-up model not found: {pushup_model}")
    except Exception as e:
        print(f"✗ Push-up analyzer failed: {e}")
    
    # Squat analyzer
    try:
        squat_model = 'models/squat_form_classifier.pkl'
        if os.path.exists(squat_model):
            analyzers['squat'] = FormAnalyzer(
                model_path=squat_model,
                exercise='squat'
            )
            print("✓ Squat analyzer loaded")
        else:
            print(f"⚠ Squat model not found: {squat_model}")
            print("  → Train squat model: python ml/src/train_model_squat.py")
    except Exception as e:
        print(f"✗ Squat analyzer failed: {e}")
    
    # Plank analyzer (no model needed - rule-based)
    try:
        analyzers['plank'] = PlankAnalyzer()
        print("✓ Plank analyzer loaded")
    except Exception as e:
        print(f"✗ Plank analyzer failed: {e}")
    
    print("=" * 70)
    print(f"Total exercises loaded: {len(analyzers)}")
    print(f"Available: {', '.join(analyzers.keys())}")
    print("=" * 70)

@app.route('/api/health', methods=['GET'])
def health_check():
    """Health check endpoint - shows all available exercises"""
    return jsonify({
        'status': 'ok',
        'exercises_available': list(analyzers.keys()),
        'pushup_loaded': 'pushup' in analyzers,
        'squat_loaded': 'squat' in analyzers,
        'plank_loaded': 'plank' in analyzers,
        'total_exercises': len(analyzers)
    })

@app.route('/api/analyze', methods=['POST'])
def analyze_frame():
    """
    Analyze frame for any exercise
    
    Request JSON:
    {
        "image": "base64_string",
        "exercise": "pushup" | "squat" | "plank"
    }
    """
    try:
        data = request.json
        
        if not data or 'image' not in data:
            return jsonify({
                'success': False,
                'error': 'No image provided'
            }), 400
        
        exercise = data.get('exercise', 'pushup').lower()
        
        # Check if exercise analyzer exists
        if exercise not in analyzers:
            return jsonify({
                'success': False,
                'error': f'Exercise "{exercise}" not available',
                'available_exercises': list(analyzers.keys())
            }), 404
        
        # Decode base64 image
        try:
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
        
        # Get appropriate analyzer
        analyzer = analyzers[exercise]
        
        # Analyze frame
        result = analyzer.analyze_frame(frame)
        
        # Format response based on exercise type
        if exercise == 'plank':
            # Plank returns hold time instead of rep count
            return jsonify({
                'success': True,
                'exercise': exercise,
                'is_holding': result.get('is_holding', False),
                'current_hold_time': result.get('current_hold_time', 0),
                'total_hold_time': result.get('total_hold_time', 0),
                'feedback': result.get('position_msg', 'Analyzing...'),
                'issues': result.get('issues', []),
                'suggestions': result.get('suggestions', []),
                'form_score': result.get('form_score', 0),
                'position_valid': result.get('position_valid', False),
                'position_msg': result.get('position_msg', '')
            })
        else:
            # Push-up and Squat return rep count
            return jsonify({
                'success': True,
                'exercise': exercise,
                'rep_count': result.get('rep_count', 0),
                'feedback': result.get('feedback', 'Analyzing...'),
                'issues': result.get('detailed_issues', []),
                'suggestions': result.get('suggestions', []),
                'confidence': float(result.get('confidence', 0)),
                'rep_phase': result.get('rep_phase', 'UP'),
                'form_score': result.get('form_score', 0) if 'form_score' in result else calculate_form_score(result),
                'elbow_angle': result.get('elbow_angle', 0) if exercise == 'pushup' else result.get('knee_angle', 0),
                'position_valid': result.get('position_valid', False),
                'position_msg': result.get('position_msg', ''),
                'rejected_reps': result.get('rejected_reps', 0),
                'total_attempts': result.get('total_attempts', 0)
            })
    
    except Exception as e:
        print(f"❌ Error in analyze_frame: {e}")
        return jsonify({
            'success': False,
            'error': f'Analysis error: {str(e)}'
        }), 500

def calculate_form_score(result):
    """Calculate form score from result data"""
    confidence = result.get('confidence', 0)
    issues = result.get('detailed_issues', [])
    
    # Base score on confidence
    form_score = confidence * 100
    
    # Adjust based on issues
    if issues:
        perfect_form = len(issues) == 1 and "Perfect form" in str(issues)
        if perfect_form:
            form_score = min(95 + confidence * 5, 100)
        elif len(issues) <= 1:
            form_score = 85 + confidence * 10
        elif len(issues) <= 2:
            form_score = 75 + confidence * 10
        else:
            form_score = 60 + confidence * 10
    
    return round(form_score, 1)

@app.route('/api/reset', methods=['POST'])
def reset_counter():
    """
    Reset exercise counter/timer
    
    Request JSON:
    {
        "exercise": "pushup" | "squat" | "plank"
    }
    """
    try:
        data = request.json
        exercise = data.get('exercise', 'pushup').lower()
        
        if exercise not in analyzers:
            return jsonify({
                'success': False,
                'error': f'Exercise "{exercise}" not available'
            }), 404
        
        analyzer = analyzers[exercise]
        
        # Reset based on exercise type
        if exercise == 'plank':
            analyzer.is_holding = False
            analyzer.hold_start_time = 0
            analyzer.total_hold_time = 0
            analyzer.current_hold_time = 0
            analyzer.form_scores_over_time = []
            
            return jsonify({
                'success': True,
                'message': f'{exercise.capitalize()} timer reset',
                'hold_time': 0
            })
        else:
            analyzer.rep_count = 0
            analyzer.last_rep_feedback = []
            analyzer.in_down_position = False
            analyzer.rep_phase = "UP"
            analyzer.rejected_reps = 0
            analyzer.total_rep_attempts = 0
            
            return jsonify({
                'success': True,
                'message': f'{exercise.capitalize()} counter reset',
                'rep_count': 0
            })
    
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/get_stats', methods=['GET'])
def get_current_stats():
    """
    Get current workout stats without analyzing frame
    
    Query params:
    ?exercise=pushup|squat|plank
    """
    try:
        exercise = request.args.get('exercise', 'pushup').lower()
        
        if exercise not in analyzers:
            return jsonify({
                'success': False,
                'error': f'Exercise "{exercise}" not available'
            }), 404
        
        analyzer = analyzers[exercise]
        
        if exercise == 'plank':
            return jsonify({
                'success': True,
                'exercise': exercise,
                'is_holding': analyzer.is_holding,
                'current_hold_time': analyzer.current_hold_time,
                'total_hold_time': analyzer.total_hold_time
            })
        else:
            return jsonify({
                'success': True,
                'exercise': exercise,
                'rep_count': analyzer.rep_count,
                'rep_phase': analyzer.rep_phase,
                'rejected_reps': analyzer.rejected_reps,
                'total_attempts': analyzer.total_rep_attempts
            })
    
    except Exception as e:
        return jsonify({
            'success': False,
            'error': str(e)
        }), 500

@app.route('/api/exercises', methods=['GET'])
def list_exercises():
    """List all available exercises with their status"""
    exercises_info = []
    
    for exercise_name in ['pushup', 'squat', 'plank']:
        exercises_info.append({
            'name': exercise_name,
            'display_name': exercise_name.capitalize(),
            'available': exercise_name in analyzers,
            'type': 'hold' if exercise_name == 'plank' else 'reps',
            'icon': '💪' if exercise_name == 'pushup' else '🦵' if exercise_name == 'squat' else '🧘'
        })
    
    return jsonify({
        'success': True,
        'exercises': exercises_info,
        'total_available': len(analyzers)
    })

if __name__ == '__main__':
    print("\n" + "=" * 70)
    print("SPOTBRO ML BACKEND - MULTI-EXERCISE SUPPORT")
    print("=" * 70 + "\n")
    
    # Initialize all analyzers
    initialize_analyzers()
    
    print("\n" + "=" * 70)
    print("API ENDPOINTS:")
    print("=" * 70)
    print("  GET  /api/health          - Health check")
    print("  POST /api/analyze         - Analyze frame")
    print("  POST /api/reset           - Reset counter/timer")
    print("  GET  /api/get_stats       - Get current stats")
    print("  GET  /api/exercises       - List all exercises")
    print("=" * 70)
    print("\nStarting Flask server on http://localhost:5000")
    print("Press Ctrl+C to stop")
    print("=" * 70 + "\n")
    
    app.run(host='0.0.0.0', port=5000, debug=True)