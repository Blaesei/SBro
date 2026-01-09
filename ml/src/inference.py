"""
Real-time inference system for SpotBro
"""

import cv2
import joblib
import numpy as np
from collections import deque
from pose_detector import PoseDetector
from feature_extractor import FeatureExtractor

class FormAnalyzer:
    """Real-time exercise form analyzer"""
    
    def __init__(self, model_path, exercise='pushup', buffer_size=10):
        self.model = joblib.load(model_path)
        self.exercise = exercise
        self.detector = PoseDetector()
        self.extractor = FeatureExtractor()
        
        # Prediction buffer for smoothing
        self.prediction_buffer = deque(maxlen=buffer_size)
        
        # Rep counter
        self.rep_count = 0
        self.prev_elbow_angle = 180
        
        # Feedback mapping
        self.feedback_map = {
            'GOOD_FORM': "✓ Perfect form!",
            'ELBOWS_FLARED': "⚠ Keep elbows closer to body",
            'BACK_SAGGING': "⚠ Engage your core",
            'INCOMPLETE_DEPTH': "⚠ Go lower",
            'NECK_STRAIN': "⚠ Keep neck aligned"
        }
    
    def analyze_frame(self, frame):
        """Analyze a single frame"""
        # Detect pose
        landmarks = self.detector.detect(frame)
        
        if landmarks is None:
            return {
                'prediction': None,
                'confidence': 0.0,
                'feedback': "No person detected",
                'rep_count': self.rep_count,
                'landmarks': None
            }
        
        # Extract features
        if self.exercise == 'pushup':
            features = self.extractor.extract_pushup_features(landmarks)
        else:
            return None
        
        if features is None:
            return {
                'prediction': None,
                'confidence': 0.0,
                'feedback': "Feature extraction failed",
                'rep_count': self.rep_count,
                'landmarks': None
            }
        
        # ML Classification
        feature_vector = [features[k] for k in features]
        prediction = self.model.predict([feature_vector])[0]
        confidence = self.model.predict_proba([feature_vector]).max()
        
        # Temporal smoothing
        self.prediction_buffer.append(prediction)
        final_prediction = max(set(self.prediction_buffer), 
                              key=self.prediction_buffer.count)
        
        # Rep counting
        current_elbow_angle = features['elbow_angle']
        
        if current_elbow_angle < 90 and self.prev_elbow_angle > 90:
            if final_prediction == 'GOOD_FORM':
                self.rep_count += 1
                feedback = "✓ Good rep!"
            else:
                feedback = f"⚠ Rep not counted: {self.feedback_map[final_prediction]}"
        else:
            feedback = self.feedback_map[final_prediction]
        
        self.prev_elbow_angle = current_elbow_angle
        
        return {
            'prediction': final_prediction,
            'confidence': confidence,
            'feedback': feedback,
            'rep_count': self.rep_count,
            'landmarks': landmarks
        }
    
    def visualize(self, frame, result):
        """Draw results on frame"""
        if result['landmarks']:
            self.detector.draw_landmarks(frame, result['landmarks'])
        
        # Color based on form quality
        color = (0, 255, 0) if result['prediction'] == 'GOOD_FORM' else (0, 165, 255)
        
        # Feedback text
        cv2.putText(frame, result['feedback'], (10, 30),
                   cv2.FONT_HERSHEY_SIMPLEX, 0.7, color, 2)
        
        # Rep count
        cv2.putText(frame, f"Reps: {result['rep_count']}", (10, 70),
                   cv2.FONT_HERSHEY_SIMPLEX, 1, (255, 255, 255), 2)
        
        # Confidence
        cv2.putText(frame, f"Confidence: {result['confidence']:.2f}", (10, 110),
                   cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 1)
        
        return frame
    
    def run(self):
        """Run real-time analysis"""
        cap = cv2.VideoCapture(0)
        
        print("=" * 60)
        print("SPOTBRO - REAL-TIME FORM ANALYZER")
        print("=" * 60)
        print("Controls:")
        print("  'q' - Quit")
        print("  'r' - Reset rep counter")
        print("-" * 60)
        
        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break
            
            # Flip for mirror effect
            frame = cv2.flip(frame, 1)
            
            # Analyze
            result = self.analyze_frame(frame)
            
            # Visualize
            frame = self.visualize(frame, result)
            
            # Display
            cv2.imshow('SpotBro - Form Analyzer', frame)
            
            # Handle keys
            key = cv2.waitKey(1) & 0xFF
            if key == ord('q'):
                break
            elif key == ord('r'):
                self.rep_count = 0
                print("Rep count reset")
        
        cap.release()
        cv2.destroyAllWindows()
        self.detector.close()

if __name__ == "__main__":
    analyzer = FormAnalyzer(
        model_path='ml/models/pushup_form_classifier.pkl',
        exercise='pushup'
    )
    
    analyzer.run()