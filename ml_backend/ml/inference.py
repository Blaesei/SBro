"""
Real-time inference system for SpotBro
FULLY FIXED VERSION - Reliable push-up detection with proper state machine
"""

import cv2
import joblib
import numpy as np
from collections import deque
from pose_detector import PoseDetector
from feature_extractor import FeatureExtractor

class FormAnalyzer:
    """Real-time exercise form analyzer with robust rep counting"""
    
    def __init__(self, model_path, exercise='pushup', buffer_size=5):
        self.model = joblib.load(model_path)
        self.exercise = exercise
        self.detector = PoseDetector()
        self.extractor = FeatureExtractor()
        
        # Prediction buffer for smoothing
        self.prediction_buffer = deque(maxlen=buffer_size)
        
        # Rep counter state - FIXED STATE MACHINE
        self.rep_count = 0
        self.current_state = "WAITING"  # States: WAITING, TOP, DESCENDING, BOTTOM, ASCENDING
        self.prev_elbow_angle = 180
        self.frames_in_state = 0
        self.min_frames_per_state = 3  # Require stability before state change
        
        # Position validation
        self.in_pushup_position = False
        self.position_check_frames = 0
        self.frames_needed_for_position = 5  # Need 5 consecutive valid frames
        
        # Store last rep feedback
        self.last_rep_feedback = []
        self.current_rep_issues = []
        
        # Dynamically get classes from model
        self.model_classes = list(self.model.classes_)
        print(f"Model predicts: {self.model_classes}")
        
        # Main feedback categories
        self.feedback_map = {
            'GOOD_FORM': "✓ Perfect form!",
            'ELBOWS_FLARED': "⚠ Elbows too wide",
            'BACK_SAGGING': "⚠ Back sagging",
            'INCOMPLETE_DEPTH': "⚠ Go deeper",
            'NECK_STRAIN': "⚠ Neck alignment",
            'FORM_ERROR': "⚠ Form issue",
            'BAD_FORM': "⚠ Form issue"
        }
    
    def is_in_pushup_position(self, features, landmarks):
        """
        FIXED: Validate if person is in push-up position using ACTUAL extracted features
        Uses only features that are guaranteed to exist from feature_extractor.py
        """
        try:
            lm = landmarks.landmark
            
            # Get key body points
            shoulder_y = (lm[11].y + lm[12].y) / 2
            hip_y = (lm[23].y + lm[24].y) / 2
            wrist_y = (lm[15].y + lm[16].y) / 2
            ankle_y = (lm[27].y + lm[28].y) / 2
            
            # Get ACTUAL features from extractor (not imaginary ones)
            elbow_angle = features.get('elbow_angle_avg', 180)
            knee_angle = features.get('knee_angle_avg', 180)
            spine_angle = features.get('spine_angle', 180)
            hip_sag_ratio = features.get('hip_sag_ratio', 1.0)
            
            # CHECK 1: Body should be roughly horizontal (plank position)
            # In push-up, shoulders and hips should be at similar vertical position
            body_horizontal_diff = abs(shoulder_y - hip_y)
            if body_horizontal_diff > 0.30:  # Too much vertical difference
                return False, "⚠ Get into plank position (body horizontal)"
            
            # CHECK 2: Person should be in lower portion of frame (not standing)
            if shoulder_y < 0.35:  # Too high in frame
                return False, "⚠ Get down into push-up position"
            
            # CHECK 3: Legs should be relatively straight (no knee push-ups)
            if knee_angle < 160:
                return False, "⚠ Straighten legs (keep knees locked)"
            
            # CHECK 4: Body alignment - spine should be relatively straight
            if spine_angle < 140:  # Body too bent
                return False, "⚠ Straighten body (maintain plank)"
            
            # CHECK 5: Hips shouldn't be too high or too low
            if hip_sag_ratio < 0.75:  # Hips way too low
                return False, "⚠ Lift hips to plank position"
            elif hip_sag_ratio > 1.25:  # Hips too high
                return False, "⚠ Lower hips (straight line from head to heels)"
            
            # CHECK 6: Arms should be in working range (not fully extended standing)
            if elbow_angle > 175 and wrist_y < shoulder_y:  # Arms straight but wrists above shoulders
                return False, "⚠ Position hands on ground below shoulders"
            
            # All checks passed!
            return True, "✓ In position - start exercising"
            
        except Exception as e:
            print(f"Position validation error: {e}")
            return False, "⚠ Position check failed"
    
    def get_detailed_feedback(self, features, prediction):
        """
        FIXED: Provide feedback using ONLY features that actually exist
        """
        issues = []
        suggestions = []
        
        try:
            # Get ACTUAL features (these are guaranteed to exist)
            elbow_angle = features.get('elbow_angle_avg', 180)
            knee_angle = features.get('knee_angle_avg', 180)
            spine_angle = features.get('spine_angle', 180)
            hip_sag_ratio = features.get('hip_sag_ratio', 1.0)
            elbow_flare = features.get('elbow_flare', 0.0)
            elbow_flexion = features.get('elbow_flexion', 0)
            
            # 1. ELBOW FLARE ANALYSIS
            if elbow_flare > 0.25:
                issues.append("⚠ Elbows flared out too much")
                suggestions.append("→ Keep elbows at 45° from body")
            elif elbow_flare > 0.18:
                issues.append("⚠ Elbows slightly wide")
                suggestions.append("→ Tuck elbows closer to ribs")
            
            # 2. DEPTH ANALYSIS (using elbow angle)
            if self.current_state == "BOTTOM":
                if elbow_angle > 110:
                    issues.append("⚠ Not deep enough")
                    suggestions.append("→ Lower chest closer to ground (elbows to 90°)")
                elif elbow_angle > 95:
                    issues.append("⚠ Could go slightly deeper")
            
            # 3. BACK/CORE ANALYSIS (using spine angle and hip ratio)
            if spine_angle < 150:
                issues.append("⚠ Back sagging (hips dropping)")
                suggestions.append("→ Engage core, keep body straight")
            elif spine_angle < 165:
                issues.append("⚠ Slight back sag")
                suggestions.append("→ Tighten core muscles")
            
            if hip_sag_ratio < 0.80:
                issues.append("⚠ Hips too low")
                suggestions.append("→ Raise hips to plank position")
            elif hip_sag_ratio > 1.15:
                issues.append("⚠ Hips too high (piking)")
                suggestions.append("→ Lower hips, maintain straight line")
            
            # 4. LEG ANALYSIS
            if knee_angle < 165:
                issues.append("⚠ Knees bending")
                suggestions.append("→ Keep legs straight and locked")
            
            # 5. ARM PLACEMENT
            if elbow_flare > 0.20:
                issues.append("⚠ Arms too far from body")
                suggestions.append("→ Position hands shoulder-width apart")
            
            # If no issues found
            if not issues:
                return ["✓ Perfect form!"], ["Keep it up!"]
            
        except Exception as e:
            print(f"Error in feedback generation: {e}")
            return ["Analysis in progress..."], [""]
        
        return issues, suggestions
    
    def update_rep_state_machine(self, elbow_angle):
        """
        FIXED: Robust state machine for rep counting
        Uses hysteresis and frame counting to prevent false triggers
        """
        # Define angle thresholds with hysteresis
        TOP_THRESHOLD = 165      # Arms nearly straight
        BOTTOM_THRESHOLD = 100   # Arms bent to ~90 degrees
        TRANSITION_ZONE = 130    # Middle zone between top and bottom
        
        state_changed = False
        
        # Increment frames in current state
        self.frames_in_state += 1
        
        # STATE MACHINE
        if self.current_state == "WAITING":
            # Waiting for first valid top position
            if elbow_angle >= TOP_THRESHOLD and self.frames_in_state >= self.min_frames_per_state:
                self.current_state = "TOP"
                self.frames_in_state = 0
                state_changed = True
                print(f"State: WAITING -> TOP (angle: {elbow_angle:.1f}°)")
        
        elif self.current_state == "TOP":
            # At top, waiting to descend
            if elbow_angle < TRANSITION_ZONE:
                self.current_state = "DESCENDING"
                self.frames_in_state = 0
                state_changed = True
                print(f"State: TOP -> DESCENDING (angle: {elbow_angle:.1f}°)")
        
        elif self.current_state == "DESCENDING":
            # Descending, waiting to reach bottom
            if elbow_angle <= BOTTOM_THRESHOLD and self.frames_in_state >= self.min_frames_per_state:
                self.current_state = "BOTTOM"
                self.frames_in_state = 0
                self.current_rep_issues = self.get_detailed_feedback(
                    {'elbow_angle_avg': elbow_angle}, None
                )[0].copy()
                state_changed = True
                print(f"State: DESCENDING -> BOTTOM (angle: {elbow_angle:.1f}°)")
        
        elif self.current_state == "BOTTOM":
            # At bottom, waiting to ascend
            if elbow_angle > TRANSITION_ZONE:
                self.current_state = "ASCENDING"
                self.frames_in_state = 0
                state_changed = True
                print(f"State: BOTTOM -> ASCENDING (angle: {elbow_angle:.1f}°)")
        
        elif self.current_state == "ASCENDING":
            # Ascending, waiting to reach top (COMPLETE REP!)
            if elbow_angle >= TOP_THRESHOLD and self.frames_in_state >= self.min_frames_per_state:
                self.current_state = "TOP"
                self.frames_in_state = 0
                self.rep_count += 1  # COUNT THE REP!
                self.last_rep_feedback = self.current_rep_issues.copy()
                state_changed = True
                print(f"State: ASCENDING -> TOP (angle: {elbow_angle:.1f}°) - REP #{self.rep_count} COUNTED!")
        
        # Store previous angle
        self.prev_elbow_angle = elbow_angle
        
        return state_changed
    
    def get_rep_phase_display(self):
        """Convert state machine state to display string"""
        state_map = {
            "WAITING": "GET READY",
            "TOP": "TOP",
            "DESCENDING": "GOING DOWN",
            "BOTTOM": "BOTTOM",
            "ASCENDING": "PUSHING UP"
        }
        return state_map.get(self.current_state, "READY")
    
    def analyze_frame(self, frame):
        """Analyze a single frame - FULLY FIXED VERSION"""
        try:
            # Detect pose
            landmarks = self.detector.detect(frame)
            
            if landmarks is None:
                return {
                    'prediction': None,
                    'confidence': 0.0,
                    'feedback': "⚠ No person detected - stand in frame",
                    'detailed_issues': ["Position yourself in camera view"],
                    'suggestions': ["Ensure full body is visible"],
                    'rep_count': self.rep_count,
                    'landmarks': None,
                    'rep_phase': self.get_rep_phase_display(),
                    'elbow_angle': 0,
                    'has_data': False,
                    'position_valid': False,
                    'position_msg': "No pose detected"
                }
            
            # Extract features
            if self.exercise == 'pushup':
                features = self.extractor.extract_pushup_features(landmarks)
            else:
                features = None
            
            if features is None:
                return {
                    'prediction': None,
                    'confidence': 0.0,
                    'feedback': "⚠ Pose detection failed",
                    'detailed_issues': ["Can't analyze pose"],
                    'suggestions': ["Try side view angle"],
                    'rep_count': self.rep_count,
                    'landmarks': landmarks,
                    'rep_phase': self.get_rep_phase_display(),
                    'elbow_angle': 0,
                    'has_data': False,
                    'position_valid': False,
                    'position_msg': "Feature extraction failed"
                }
            
            # VALIDATE PUSH-UP POSITION
            position_valid, position_msg = self.is_in_pushup_position(features, landmarks)
            
            # Update position tracking with hysteresis
            if position_valid:
                self.position_check_frames += 1
                if self.position_check_frames >= self.frames_needed_for_position:
                    self.in_pushup_position = True
            else:
                self.position_check_frames = 0
                self.in_pushup_position = False
                # Reset state machine if leaving position
                if self.current_state != "WAITING":
                    self.current_state = "WAITING"
                    self.frames_in_state = 0
            
            # ML Classification
            feature_vector = [features[k] for k in features]
            prediction = self.model.predict([feature_vector])[0]
            confidence = self.model.predict_proba([feature_vector]).max()
            
            # Temporal smoothing
            self.prediction_buffer.append(prediction)
            final_prediction = max(set(self.prediction_buffer), 
                                  key=self.prediction_buffer.count)
            
            # Get detailed feedback
            issues, suggestions = self.get_detailed_feedback(features, final_prediction)
            
            # REP COUNTING - ONLY IF IN VALID POSITION
            current_elbow_angle = features.get('elbow_angle_avg', 180)
            feedback = self.feedback_map.get(final_prediction, f"⚠ {final_prediction}")
            
            if self.in_pushup_position:
                # Update state machine
                state_changed = self.update_rep_state_machine(current_elbow_angle)
                
                # Generate feedback based on state
                if state_changed and self.current_state == "TOP" and self.rep_count > 0:
                    # Rep just completed!
                    if len(self.last_rep_feedback) == 1 and "Perfect" in self.last_rep_feedback[0]:
                        feedback = f"✓ Rep #{self.rep_count} - PERFECT! 🎉"
                    else:
                        feedback = f"Rep #{self.rep_count} - " + ", ".join(self.last_rep_feedback[:2])
                else:
                    # Show current phase
                    phase_feedback = {
                        "TOP": "✓ Ready - descend slowly",
                        "DESCENDING": "↓ Going down - keep form",
                        "BOTTOM": "✓ Good depth - push up!",
                        "ASCENDING": "↑ Pushing up - full extension"
                    }
                    feedback = phase_feedback.get(self.current_state, position_msg)
            else:
                # Not in valid position
                feedback = position_msg
                # Show how many frames until position is validated
                if position_valid and self.position_check_frames < self.frames_needed_for_position:
                    feedback = f"⚠ Hold position... ({self.position_check_frames}/{self.frames_needed_for_position})"
            
            return {
                'prediction': final_prediction,
                'confidence': confidence,
                'feedback': feedback,
                'detailed_issues': issues,
                'suggestions': suggestions,
                'rep_count': self.rep_count,
                'landmarks': landmarks,
                'rep_phase': self.get_rep_phase_display(),
                'elbow_angle': current_elbow_angle,
                'has_data': True,
                'position_valid': self.in_pushup_position,
                'position_msg': position_msg
            }
        
        except Exception as e:
            print(f"Error in analyze_frame: {e}")
            import traceback
            traceback.print_exc()
            return {
                'prediction': None,
                'confidence': 0.0,
                'feedback': "⚠ Analysis error - continuing...",
                'detailed_issues': ["System recovering..."],
                'suggestions': [""],
                'rep_count': self.rep_count,
                'landmarks': None,
                'rep_phase': self.get_rep_phase_display(),
                'elbow_angle': 0,
                'has_data': False,
                'position_valid': False,
                'position_msg': "Error occurred"
            }
    
    def visualize(self, frame, result):
        """Draw results on frame - ENHANCED VERSION"""
        try:
            # Draw skeleton
            if result.get('landmarks') and result.get('has_data', False):
                self.detector.draw_landmarks(frame, result['landmarks'])
            
            # Color based on position and form
            position_valid = result.get('position_valid', False)
            issues = result.get('detailed_issues', [])
            
            if not position_valid:
                color = (0, 0, 255)  # Red - not in position
            elif not issues or "Perfect form" in str(issues):
                color = (0, 255, 0)  # Green - perfect
            elif len(issues) <= 1:
                color = (0, 255, 255)  # Yellow - minor issues
            else:
                color = (0, 165, 255)  # Orange - multiple issues
            
            # Main feedback
            feedback = result.get('feedback', 'Initializing...')
            cv2.putText(frame, feedback, (10, 40),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.9, color, 3)
            
            # Rep count (large and prominent)
            rep_count = result.get('rep_count', 0)
            cv2.putText(frame, f"REPS: {rep_count}", (10, 100),
                       cv2.FONT_HERSHEY_SIMPLEX, 1.5, (255, 255, 255), 4)
            
            # State display (ONLY if in position)
            if position_valid:
                rep_phase = result.get('rep_phase', 'READY')
                phase_color = {
                    "TOP": (0, 255, 0),
                    "GOING DOWN": (0, 255, 255),
                    "BOTTOM": (0, 165, 255),
                    "PUSHING UP": (255, 255, 0),
                    "GET READY": (255, 255, 255)
                }.get(rep_phase, (255, 255, 255))
                
                cv2.putText(frame, f"Phase: {rep_phase}", (10, 150),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.8, phase_color, 2)
                
                # Elbow angle indicator
                elbow_angle = result.get('elbow_angle', 0)
                cv2.putText(frame, f"Elbow: {elbow_angle:.0f}°", (10, 190),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, (200, 200, 200), 2)
            
            # Detailed issues (right side) - ONLY if in position
            if result.get('has_data', False) and position_valid:
                y_offset = 40
                cv2.putText(frame, "Current Form:", (frame.shape[1] - 350, y_offset),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
                y_offset += 40
                
                for issue in issues[:4]:
                    cv2.putText(frame, issue, (frame.shape[1] - 350, y_offset),
                               cv2.FONT_HERSHEY_SIMPLEX, 0.6, (0, 200, 255), 2)
                    y_offset += 30
                
                # Suggestions
                suggestions = result.get('suggestions', [])
                if suggestions and suggestions[0]:
                    y_offset += 10
                    cv2.putText(frame, "To improve:", (frame.shape[1] - 350, y_offset),
                               cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
                    y_offset += 40
                    
                    for suggestion in suggestions[:3]:
                        if suggestion:
                            cv2.putText(frame, suggestion, (frame.shape[1] - 350, y_offset),
                                       cv2.FONT_HERSHEY_SIMPLEX, 0.5, (100, 255, 100), 2)
                            y_offset += 28
        
        except Exception as e:
            print(f"Visualization error: {e}")
            cv2.putText(frame, "Display error", (10, 30),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2)
        
        return frame
    
    def run(self):
        """Run real-time analysis"""
        cap = cv2.VideoCapture(0)
        
        print("=" * 60)
        print("SPOTBRO - FIXED PUSH-UP ANALYZER")
        print("=" * 60)
        print("Controls:")
        print("  'q' - Quit")
        print("  'r' - Reset rep counter")
        print("  's' - Show last rep summary")
        print("-" * 60)
        print("\nRep Counting Logic:")
        print("  State Machine: WAITING → TOP → DESCENDING → BOTTOM → ASCENDING → TOP")
        print("  Rep counted when: Complete cycle from TOP → BOTTOM → TOP")
        print("  Requires: 3+ frames in each key state for stability")
        print("-" * 60)
        print("\nPosition Validation:")
        print("  - Body horizontal, legs straight, proper plank")
        print("  - Must hold valid position for 5 frames")
        print("  - Red = Not ready | Yellow = Stabilizing | Green = Counting")
        print("=" * 60)
        
        while cap.isOpened():
            ret, frame = cap.read()
            if not ret:
                break
            
            frame = cv2.flip(frame, 1)
            result = self.analyze_frame(frame)
            frame = self.visualize(frame, result)
            
            cv2.imshow('SpotBro - Fixed Push-up Analyzer', frame)
            
            key = cv2.waitKey(1) & 0xFF
            if key == ord('q'):
                break
            elif key == ord('r'):
                self.rep_count = 0
                self.current_state = "WAITING"
                self.frames_in_state = 0
                self.last_rep_feedback = []
                print("\n✓ Rep counter reset")
            elif key == ord('s'):
                print("\n" + "=" * 60)
                print(f"LAST REP SUMMARY (Rep #{self.rep_count}):")
                print("=" * 60)
                if self.last_rep_feedback:
                    for issue in self.last_rep_feedback:
                        print(f"  {issue}")
                else:
                    print("  No issues - Perfect form!")
                print("=" * 60 + "\n")
        
        print("\n" + "=" * 60)
        print("WORKOUT SUMMARY")
        print("=" * 60)
        print(f"Total Reps: {self.rep_count}")
        print("=" * 60)
        
        cap.release()
        cv2.destroyAllWindows()
        self.detector.close()

if __name__ == "__main__":
    print("Initializing Fixed SpotBro Push-up Analyzer...")
    try:
        analyzer = FormAnalyzer(
            model_path='ml/models/pushup_form_classifier.pkl',
            exercise='pushup'
        )
        analyzer.run()
    except Exception as e:
        print(f"Failed to start: {e}")
        import traceback
        traceback.print_exc()