"""
Real-time inference system for SpotBro
PRODUCTION VERSION: No ghost reps, validated counting, optimized performance
"""

import cv2
import joblib
import numpy as np
import time
from collections import deque
from pose_detector import PoseDetector
from feature_extractor import FeatureExtractor

class FormAnalyzer:
    """
    Real-time exercise form analyzer with validated rep counting
    
    Key features:
    - Depth validation (must reach minimum angle)
    - Duration validation (must take minimum time)
    - Debouncing (cooldown between reps)
    - Sustained movement check
    - Enhanced smoothing
    """
    
    def __init__(self, model_path, exercise='pushup', buffer_size=5):
        self.model = joblib.load(model_path)
        self.exercise = exercise
        self.detector = PoseDetector()
        self.extractor = FeatureExtractor()
        
        # Prediction smoothing
        self.prediction_buffer = deque(maxlen=buffer_size)
        
        # ENHANCED: Rep counter with validation
        self.rep_count = 0
        self.in_down_position = False
        self.rep_phase = "UP"
        
        # NEW: Rep validation tracking
        self.min_angle_this_rep = 180  # Deepest angle reached
        self.rep_start_time = 0        # When down phase started
        self.last_rep_time = 0         # Last successful rep time
        
        # NEW: Enhanced smoothing (5 frames = 1 second at 5 FPS)
        self.angle_buffer = deque(maxlen=5)  # Was 3
        
        # Position validation
        self.invalid_position_frames = 0
        self.relaxed_mode = False
        
        # Feedback storage
        self.last_rep_feedback = []
        self.current_rep_issues = []
        
        # Model classes
        self.model_classes = list(self.model.classes_)
        print(f"✓ Model loaded. Classes: {self.model_classes}")
        
        # TUNED: Rep counting thresholds
        self.DOWN_ANGLE = 110      # Trigger down phase
        self.UP_ANGLE = 145        # Trigger up phase
        self.HYSTERESIS = 10       # Buffer zone
        
        # NEW: Validation parameters
        self.MIN_DEPTH_ANGLE = 95       # Must reach ≤95° at bottom
        self.MIN_REP_DURATION = 0.8     # Must take ≥0.8 seconds
        self.MAX_REP_DURATION = 5.0     # Must complete <5 seconds
        self.REP_COOLDOWN = 0.7         # Minimum 0.7s between reps
        
        # Statistics
        self.total_rep_attempts = 0
        self.rejected_reps = 0
        
        # Feedback mapping
        self.feedback_map = {
            'GOOD_FORM': "✓ Perfect form!",
            'ELBOWS_FLARED': "⚠ Elbows too wide",
            'BACK_SAGGING': "⚠ Back sagging",
            'INCOMPLETE_DEPTH': "⚠ Go deeper",
            'NECK_STRAIN': "⚠ Neck alignment",
            'FORM_ERROR': "⚠ Form issue",
            'BAD_FORM': "⚠ Form issue"
        }
    
    def get_smoothed_angle(self, angle):
        """
        Smooth angle with moving average (5-frame window)
        Reduces jitter and false triggers
        """
        self.angle_buffer.append(angle)
        if len(self.angle_buffer) == 0:
            return angle
        return sum(self.angle_buffer) / len(self.angle_buffer)
    
    def is_in_pushup_position(self, features, landmarks):
        """
        Validate push-up position with relaxed thresholds
        
        Returns: (is_valid, reason)
        """
        try:
            lm = landmarks.landmark
            
            # Key body points
            shoulder_y = lm[12].y
            hip_y = lm[24].y
            wrist_y = lm[16].y
            knee_y = lm[26].y
            
            # Feature values
            back_angle = features.get('back_angle', 0)
            hip_ratio = features.get('hip_height_ratio', 0)
            knee_angle = features.get('knee_angle', 180)
            
            # Relaxed mode bypass
            if self.relaxed_mode:
                if wrist_y < shoulder_y - 0.3:
                    return False, "⚠ Lower hands to ground"
                return True, "✓ Relaxed mode - counting reps"
            
            # Position checks (relaxed thresholds)
            if wrist_y < shoulder_y - 0.2:
                return False, "❌ Hands not on ground"
            
            body_vertical_diff = abs(shoulder_y - hip_y)
            if body_vertical_diff > 0.70:
                return False, "❌ Body not horizontal"
            
            if hip_ratio < 0.30:
                return False, "❌ Lift body to plank"
            elif hip_ratio > 2.0:
                return False, "❌ Lower into push-up position"
            
            if knee_angle < 140:
                return False, "❌ Straighten legs"
            
            if back_angle < 140:
                return False, "❌ Straighten back"
            
            if shoulder_y < 0.25:
                return False, "❌ Move closer to ground"
            
            # Valid position
            self.invalid_position_frames = 0
            return True, "✓ In position - ready to count"
            
        except Exception as e:
            return False, f"⚠ Position check failed: {str(e)}"
    
    def get_detailed_feedback(self, features, prediction):
        """Generate specific feedback based on form analysis"""
        issues = []
        suggestions = []
        
        try:
            elbow_angle = features.get('elbow_angle', 180)
            back_angle = features.get('back_angle', 180)
            hip_ratio = features.get('hip_height_ratio', 1.0)
            elbow_spread = features.get('elbow_spread', 0.0)
            knee_angle = features.get('knee_angle', 180)
            
            # Elbow analysis
            if elbow_angle > 110:
                issues.append("❌ Elbows flared out")
                suggestions.append("→ Keep elbows at 45° from body")
            
            # Depth analysis
            if elbow_angle > 110 and self.rep_phase == "DOWN":
                issues.append("❌ Not deep enough")
                suggestions.append("→ Lower chest to floor")
            
            # Back/core analysis
            if back_angle < 155:
                issues.append("❌ Back sagging")
                suggestions.append("→ Engage core")
            
            if hip_ratio < 0.80:
                issues.append("❌ Hips too low")
                suggestions.append("→ Raise hips")
            elif hip_ratio > 1.15:
                issues.append("❌ Hips too high")
                suggestions.append("→ Lower hips")
            
            # Arm spread
            if elbow_spread > 0.15:
                issues.append("❌ Arms too wide")
                suggestions.append("→ Elbows closer to ribs")
            
            # Legs
            if knee_angle < 160:
                issues.append("⚠ Knees bending")
                suggestions.append("→ Keep legs straight")
            
            if not issues:
                return ["✓ Perfect form!"], ["Keep it up!"]
            
        except Exception as e:
            print(f"Feedback error: {e}")
            return ["Analyzing..."], [""]
        
        return issues, suggestions
    
    def validate_rep(self, min_angle, duration):
        """
        Validate if movement qualifies as a proper rep
        
        Returns: (is_valid, rejection_reason)
        """
        # Check 1: Depth validation
        if min_angle > self.MIN_DEPTH_ANGLE:
            return False, f"Insufficient depth ({min_angle:.0f}° > {self.MIN_DEPTH_ANGLE}°)"
        
        # Check 2: Duration validation (too fast)
        if duration < self.MIN_REP_DURATION:
            return False, f"Too fast ({duration:.1f}s < {self.MIN_REP_DURATION}s)"
        
        # Check 3: Duration validation (too slow/paused)
        if duration > self.MAX_REP_DURATION:
            return False, f"Too slow ({duration:.1f}s > {self.MAX_REP_DURATION}s)"
        
        # Check 4: Cooldown (prevent double counting)
        time_since_last = time.time() - self.last_rep_time
        if time_since_last < self.REP_COOLDOWN:
            return False, f"Too soon ({time_since_last:.1f}s < {self.REP_COOLDOWN}s)"
        
        # All checks passed
        return True, "Valid rep"
    
    def analyze_frame(self, frame):
        """
        Analyze frame with validated rep counting
        
        Returns: Dict with analysis results
        """
        try:
            # Detect pose
            landmarks = self.detector.detect(frame)
            
            if landmarks is None:
                return self._no_person_response()
            
            # Extract features
            features = self.extractor.extract_pushup_features(landmarks) if self.exercise == 'pushup' else None
            
            if features is None:
                return self._no_features_response(landmarks)
            
            # Validate position
            position_valid, position_msg = self.is_in_pushup_position(features, landmarks)
            
            # Track invalid frames (enable relaxed mode if needed)
            if not position_valid:
                self.invalid_position_frames += 1
                if self.invalid_position_frames > 20 and not self.relaxed_mode:
                    print("⚠ Entering relaxed mode")
                    self.relaxed_mode = True
            
            # ML Classification
            feature_vector = [features[k] for k in features]
            prediction = self.model.predict([feature_vector])[0]
            confidence = self.model.predict_proba([feature_vector]).max()
            
            # Temporal smoothing
            self.prediction_buffer.append(prediction)
            final_prediction = max(set(self.prediction_buffer), key=self.prediction_buffer.count)
            
            # Get feedback
            issues, suggestions = self.get_detailed_feedback(features, final_prediction)
            
            # VALIDATED REP COUNTING
            current_elbow_angle = features.get('elbow_angle', 180)
            smoothed_angle = self.get_smoothed_angle(current_elbow_angle)
            
            feedback = self.feedback_map.get(final_prediction, f"⚠ {final_prediction}")
            
            # Only count if in valid position (or relaxed mode)
            if position_valid or self.relaxed_mode:
                
                # Track minimum angle during down phase
                if self.in_down_position:
                    self.min_angle_this_rep = min(self.min_angle_this_rep, smoothed_angle)
                
                # DOWN phase detection
                if smoothed_angle < self.DOWN_ANGLE and not self.in_down_position:
                    self.rep_phase = "DOWN"
                    self.in_down_position = True
                    self.min_angle_this_rep = smoothed_angle
                    self.rep_start_time = time.time()
                    self.current_rep_issues = issues.copy()
                    print(f"📉 DOWN phase | Angle: {smoothed_angle:.1f}°")
                
                # UP phase detection with VALIDATION
                elif smoothed_angle > self.UP_ANGLE and self.in_down_position:
                    self.rep_phase = "UP"
                    self.in_down_position = False
                    
                    # Calculate duration
                    rep_duration = time.time() - self.rep_start_time
                    
                    # VALIDATE REP
                    self.total_rep_attempts += 1
                    is_valid, rejection_reason = self.validate_rep(
                        self.min_angle_this_rep, 
                        rep_duration
                    )
                    
                    if is_valid:
                        # VALID REP - COUNT IT
                        self.rep_count += 1
                        self.last_rep_time = time.time()
                        self.last_rep_feedback = self.current_rep_issues.copy()
                        
                        print(f"✓ REP #{self.rep_count} | Depth: {self.min_angle_this_rep:.1f}° | Time: {rep_duration:.1f}s")
                        
                        # Feedback
                        if len(self.last_rep_feedback) == 1 and "Perfect" in self.last_rep_feedback[0]:
                            feedback = f"✓ Rep #{self.rep_count} - PERFECT! 🎉"
                        else:
                            feedback = f"Rep #{self.rep_count} - " + ", ".join(self.last_rep_feedback[:2])
                    else:
                        # INVALID REP - REJECT IT
                        self.rejected_reps += 1
                        print(f"✗ Rep rejected | Reason: {rejection_reason}")
                        feedback = f"✗ Rep rejected: {rejection_reason}"
                    
                    # Reset for next rep
                    self.min_angle_this_rep = 180
            else:
                # Not in valid position
                feedback = position_msg
            
            # Return results
            return {
                'prediction': final_prediction,
                'confidence': confidence,
                'feedback': feedback,
                'detailed_issues': issues,
                'suggestions': suggestions,
                'rep_count': self.rep_count,
                'landmarks': landmarks,
                'rep_phase': self.rep_phase,
                'elbow_angle': smoothed_angle,
                'has_data': True,
                'position_valid': position_valid or self.relaxed_mode,
                'position_msg': position_msg,
                'rejected_reps': self.rejected_reps,
                'total_attempts': self.total_rep_attempts
            }
        
        except Exception as e:
            print(f"❌ Frame analysis error: {e}")
            return self._error_response()
    
    def _no_person_response(self):
        """Return response when no person detected"""
        return {
            'prediction': None,
            'confidence': 0.0,
            'feedback': "⚠ No person detected",
            'detailed_issues': ["Stand in frame"],
            'suggestions': ["Ensure full body visible"],
            'rep_count': self.rep_count,
            'landmarks': None,
            'rep_phase': self.rep_phase,
            'elbow_angle': 0,
            'has_data': False,
            'position_valid': False,
            'position_msg': "No pose detected",
            'rejected_reps': self.rejected_reps,
            'total_attempts': self.total_rep_attempts
        }
    
    def _no_features_response(self, landmarks):
        """Return response when features can't be extracted"""
        return {
            'prediction': None,
            'confidence': 0.0,
            'feedback': "⚠ Pose detection failed",
            'detailed_issues': ["Adjust position"],
            'suggestions': ["Try side view"],
            'rep_count': self.rep_count,
            'landmarks': landmarks,
            'rep_phase': self.rep_phase,
            'elbow_angle': 0,
            'has_data': False,
            'position_valid': False,
            'position_msg': "Feature extraction failed",
            'rejected_reps': self.rejected_reps,
            'total_attempts': self.total_rep_attempts
        }
    
    def _error_response(self):
        """Return response on error"""
        return {
            'prediction': None,
            'confidence': 0.0,
            'feedback': "⚠ Analysis error",
            'detailed_issues': ["System recovering..."],
            'suggestions': [""],
            'rep_count': self.rep_count,
            'landmarks': None,
            'rep_phase': self.rep_phase,
            'elbow_angle': 0,
            'has_data': False,
            'position_valid': False,
            'position_msg': "Error occurred",
            'rejected_reps': self.rejected_reps,
            'total_attempts': self.total_rep_attempts
        }
    
    def visualize(self, frame, result):
        """Draw analysis results on frame"""
        try:
            # Draw skeleton
            if result.get('landmarks') and result.get('has_data'):
                self.detector.draw_landmarks(frame, result['landmarks'])
            
            # Color based on state
            position_valid = result.get('position_valid', False)
            issues = result.get('detailed_issues', [])
            
            if not position_valid:
                color = (0, 0, 255)  # Red
            elif not issues or "Perfect" in str(issues):
                color = (0, 255, 0)  # Green
            else:
                color = (0, 165, 255)  # Orange
            
            # Main feedback (larger font)
            feedback = result.get('feedback', 'Initializing...')
            cv2.putText(frame, feedback, (10, 40),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 3)
            
            # Position status
            position_status = "✓ IN POSITION" if position_valid else "✗ NOT IN POSITION"
            position_color = (0, 255, 0) if position_valid else (0, 0, 255)
            cv2.putText(frame, position_status, (10, 80),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, position_color, 2)
            
            # Rep count (large, prominent)
            rep_count = result.get('rep_count', 0)
            cv2.putText(frame, f"Reps: {rep_count}", (10, 130),
                       cv2.FONT_HERSHEY_SIMPLEX, 1.5, (255, 255, 255), 4)
            
            # Rep phase
            if position_valid:
                rep_phase = result.get('rep_phase', 'UP')
                phase_color = (0, 255, 255) if rep_phase == "DOWN" else (255, 255, 255)
                cv2.putText(frame, f"Phase: {rep_phase}", (10, 175),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, phase_color, 2)
            
            # Statistics (bottom left)
            rejected = result.get('rejected_reps', 0)
            total = result.get('total_attempts', 0)
            if total > 0:
                accuracy = ((total - rejected) / total) * 100
                cv2.putText(frame, f"Accuracy: {accuracy:.0f}% ({total-rejected}/{total})", 
                           (10, frame.shape[0] - 20),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.5, (200, 200, 200), 1)
            
            # Debug info (bottom center)
            if result.get('has_data'):
                elbow_angle = result.get('elbow_angle', 0)
                cv2.putText(frame, f"Elbow: {elbow_angle:.1f}°", 
                           (frame.shape[1]//2 - 100, frame.shape[0] - 20),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.5, (200, 200, 200), 1)
        
        except Exception as e:
            print(f"Visualization error: {e}")
        
        return frame
    
    def run(self):
        """Run real-time analysis with keyboard controls"""
        cap = cv2.VideoCapture(0)
        
        print("=" * 70)
        print("SPOTBRO - PRODUCTION REP COUNTER (No Ghost Reps)")
        print("=" * 70)
        print(f"Validation: Depth ≤{self.MIN_DEPTH_ANGLE}° | Duration {self.MIN_REP_DURATION}-{self.MAX_REP_DURATION}s")
        print(f"Thresholds: DOWN={self.DOWN_ANGLE}° | UP={self.UP_ANGLE}°")
        print("Controls: 'q'=Quit | 'r'=Reset | 's'=Stats")
        print("=" * 70)
        
        while cap.isOpened():
            try:
                ret, frame = cap.read()
                if not ret:
                    continue
                
                frame = cv2.flip(frame, 1)
                result = self.analyze_frame(frame)
                frame = self.visualize(frame, result)
                
                cv2.imshow('SpotBro - Production Counter', frame)
                
                key = cv2.waitKey(1) & 0xFF
                if key == ord('q'):
                    break
                elif key == ord('r'):
                    self.rep_count = 0
                    self.rejected_reps = 0
                    self.total_rep_attempts = 0
                    print("✓ Counter reset")
                elif key == ord('s'):
                    self._print_stats()
                
            except KeyboardInterrupt:
                break
            except Exception as e:
                print(f"Frame error: {e}")
                continue
        
        self._print_stats()
        cap.release()
        cv2.destroyAllWindows()
        self.detector.close()
    
    def _print_stats(self):
        """Print session statistics"""
        print("\n" + "=" * 70)
        print("SESSION STATISTICS")
        print("=" * 70)
        print(f"Valid reps: {self.rep_count}")
        print(f"Rejected reps: {self.rejected_reps}")
        print(f"Total attempts: {self.total_rep_attempts}")
        if self.total_rep_attempts > 0:
            accuracy = (self.rep_count / self.total_rep_attempts) * 100
            print(f"Accuracy: {accuracy:.1f}%")
        print("=" * 70 + "\n")

if __name__ == "__main__":
    print("Initializing SpotBro Production Counter...")
    try:
        analyzer = FormAnalyzer(
            model_path='ml/models/pushup_form_classifier.pkl',
            exercise='pushup'
        )
        analyzer.run()
    except Exception as e:
        print(f"Failed to start: {e}")