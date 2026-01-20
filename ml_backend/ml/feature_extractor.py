"""
Biomechanical Feature Extraction - FIXED TO MATCH MODEL
Extracts exactly 16 features as expected by the trained model
"""

import numpy as np

class FeatureExtractor:
    """Extract biomechanical features from pose landmarks"""
    
    @staticmethod
    def calculate_angle(point1, point2, point3):
        """
        Calculate angle between three points
        
        Args:
            point1, point2, point3: Landmarks with .x, .y, .z attributes
            
        Returns:
            angle in degrees (0-180)
        """
        # Convert to numpy arrays
        a = np.array([point1.x, point1.y])
        b = np.array([point2.x, point2.y])
        c = np.array([point3.x, point3.y])
        
        # Calculate vectors
        ba = a - b
        bc = c - b
        
        # Calculate angle
        cosine_angle = np.dot(ba, bc) / (np.linalg.norm(ba) * np.linalg.norm(bc) + 1e-6)
        angle = np.arccos(np.clip(cosine_angle, -1.0, 1.0))
        
        return np.degrees(angle)
    
    @staticmethod
    def calculate_distance(point1, point2):
        """Calculate Euclidean distance between two points"""
        return np.sqrt((point1.x - point2.x)**2 + (point1.y - point2.y)**2)
    
    def extract_pushup_features(self, landmarks):
        """
        Extract EXACTLY 16 features for push-up exercise (matching trained model)
        
        Expected features:
        1. torso_length
        2. shoulder_width_norm
        3. elbow_angle_avg
        4. hip_width_norm
        5. knee_angle_avg
        6. spine_angle
        7. com_x_norm
        8. com_y_norm
        9. elbow_flexion
        10. body_alignment
        11. hip_sag_ratio
        12. elbow_flare
        13. hand_placement_width
        14. head_position
        15. is_bottom_position
        16. is_top_position
        """
        try:
            # Access landmarks using .landmark attribute
            lm = landmarks.landmark
            
            # Key body points (using both sides for averaging)
            left_shoulder = lm[11]
            right_shoulder = lm[12]
            left_elbow = lm[13]
            right_elbow = lm[14]
            left_wrist = lm[15]
            right_wrist = lm[16]
            left_hip = lm[23]
            right_hip = lm[24]
            left_knee = lm[25]
            right_knee = lm[26]
            left_ankle = lm[27]
            right_ankle = lm[28]
            nose = lm[0]
            
            # === FEATURE 1: torso_length ===
            # Distance from shoulders to hips (average both sides)
            torso_left = self.calculate_distance(left_shoulder, left_hip)
            torso_right = self.calculate_distance(right_shoulder, right_hip)
            torso_length = (torso_left + torso_right) / 2
            
            # === FEATURE 2: shoulder_width_norm ===
            # Shoulder width normalized by torso length
            shoulder_width = self.calculate_distance(left_shoulder, right_shoulder)
            shoulder_width_norm = shoulder_width / (torso_length + 1e-6)
            
            # === FEATURE 3: elbow_angle_avg ===
            # Average elbow angle (both arms)
            left_elbow_angle = self.calculate_angle(left_shoulder, left_elbow, left_wrist)
            right_elbow_angle = self.calculate_angle(right_shoulder, right_elbow, right_wrist)
            elbow_angle_avg = (left_elbow_angle + right_elbow_angle) / 2
            
            # === FEATURE 4: hip_width_norm ===
            # Hip width normalized by torso length
            hip_width = self.calculate_distance(left_hip, right_hip)
            hip_width_norm = hip_width / (torso_length + 1e-6)
            
            # === FEATURE 5: knee_angle_avg ===
            # Average knee angle (both legs)
            left_knee_angle = self.calculate_angle(left_hip, left_knee, left_ankle)
            right_knee_angle = self.calculate_angle(right_hip, right_knee, right_ankle)
            knee_angle_avg = (left_knee_angle + right_knee_angle) / 2
            
            # === FEATURE 6: spine_angle ===
            # Angle of spine (shoulder to hip alignment)
            # Use average shoulder and hip positions
            avg_shoulder_x = (left_shoulder.x + right_shoulder.x) / 2
            avg_shoulder_y = (left_shoulder.y + right_shoulder.y) / 2
            avg_hip_x = (left_hip.x + right_hip.x) / 2
            avg_hip_y = (left_hip.y + right_hip.y) / 2
            avg_ankle_x = (left_ankle.x + right_ankle.x) / 2
            avg_ankle_y = (left_ankle.y + right_ankle.y) / 2
            
            # Create virtual points for angle calculation
            class VirtualPoint:
                def __init__(self, x, y):
                    self.x = x
                    self.y = y
            
            shoulder_point = VirtualPoint(avg_shoulder_x, avg_shoulder_y)
            hip_point = VirtualPoint(avg_hip_x, avg_hip_y)
            ankle_point = VirtualPoint(avg_ankle_x, avg_ankle_y)
            
            spine_angle = self.calculate_angle(shoulder_point, hip_point, ankle_point)
            
            # === FEATURE 7: com_x_norm ===
            # Center of mass X position (normalized)
            com_x = (avg_shoulder_x + avg_hip_x) / 2
            com_x_norm = com_x
            
            # === FEATURE 8: com_y_norm ===
            # Center of mass Y position (normalized)
            com_y = (avg_shoulder_y + avg_hip_y) / 2
            com_y_norm = com_y
            
            # === FEATURE 9: elbow_flexion ===
            # Degree of elbow bend (180 - elbow_angle)
            elbow_flexion = 180 - elbow_angle_avg
            
            # === FEATURE 10: body_alignment ===
            # How straight the body is (shoulder-hip-ankle alignment)
            body_alignment = spine_angle
            
            # === FEATURE 11: hip_sag_ratio ===
            # Hip position relative to shoulder-ankle line (detect sagging)
            # Higher value = hips sagging lower
            hip_sag_ratio = avg_hip_y / avg_shoulder_y
            
            # === FEATURE 12: elbow_flare ===
            # How far elbows stick out from body (lateral distance)
            left_elbow_flare = abs(left_elbow.x - left_shoulder.x)
            right_elbow_flare = abs(right_elbow.x - right_shoulder.x)
            elbow_flare = (left_elbow_flare + right_elbow_flare) / 2
            
            # === FEATURE 13: hand_placement_width ===
            # Distance between hands (wrists)
            hand_placement_width = self.calculate_distance(left_wrist, right_wrist)
            
            # === FEATURE 14: head_position ===
            # Head position relative to shoulders (forward/backward)
            head_position = nose.y - avg_shoulder_y
            
            # === FEATURE 15: is_bottom_position ===
            # Binary: Is person at bottom of push-up? (elbow < 100 degrees)
            is_bottom_position = 1.0 if elbow_angle_avg < 100 else 0.0
            
            # === FEATURE 16: is_top_position ===
            # Binary: Is person at top of push-up? (elbow > 160 degrees)
            is_top_position = 1.0 if elbow_angle_avg > 160 else 0.0
            
            # Return features in EXACT order expected by model
            return {
                'torso_length': torso_length,
                'shoulder_width_norm': shoulder_width_norm,
                'elbow_angle_avg': elbow_angle_avg,
                'hip_width_norm': hip_width_norm,
                'knee_angle_avg': knee_angle_avg,
                'spine_angle': spine_angle,
                'com_x_norm': com_x_norm,
                'com_y_norm': com_y_norm,
                'elbow_flexion': elbow_flexion,
                'body_alignment': body_alignment,
                'hip_sag_ratio': hip_sag_ratio,
                'elbow_flare': elbow_flare,
                'hand_placement_width': hand_placement_width,
                'head_position': head_position,
                'is_bottom_position': is_bottom_position,
                'is_top_position': is_top_position
            }
        
        except Exception as e:
            print(f"Feature extraction error: {e}")
            import traceback
            traceback.print_exc()
            return None
    
    def extract_squat_features(self, landmarks):
        """Extract features for squat exercise"""
        try:
            # Access landmarks using .landmark attribute
            lm = landmarks.landmark
            
            hip = lm[24]
            knee = lm[26]
            ankle = lm[28]
            shoulder = lm[12]
            
            # Feature 1: Knee angle (should be ~90° at bottom)
            knee_angle = self.calculate_angle(hip, knee, ankle)
            
            # Feature 2: Hip depth (hip should be below knee)
            hip_depth_ratio = hip.y / knee.y
            
            # Feature 3: Back angle (should stay upright)
            back_angle = self.calculate_angle(shoulder, hip, knee)
            
            # Feature 4: Knee alignment (knee shouldn't cave in)
            knee_alignment = abs(knee.x - ankle.x)
            
            # Feature 5: Ankle dorsiflexion
            class VirtualPoint:
                def __init__(self, x, y):
                    self.x = x
                    self.y = y
            
            forward_ref = VirtualPoint(ankle.x + 0.1, ankle.y)
            ankle_angle = self.calculate_angle(knee, ankle, forward_ref)
            
            return {
                'knee_angle': knee_angle,
                'hip_depth_ratio': hip_depth_ratio,
                'back_angle': back_angle,
                'knee_alignment': knee_alignment,
                'ankle_angle': ankle_angle
            }
        
        except Exception as e:
            print(f"Feature extraction error: {e}")
            return None


# Test the feature extractor
if __name__ == "__main__":
    from pose_detector import PoseDetector
    import cv2
    
    detector = PoseDetector()
    extractor = FeatureExtractor()
    
    cap = cv2.VideoCapture(0)
    
    print("Feature Extractor Test - FIXED VERSION")
    print("Should now extract exactly 16 features")
    print("Press 'q' to quit")
    print("-" * 50)
    
    while cap.isOpened():
        ret, frame = cap.read()
        if not ret:
            break
        
        landmarks = detector.detect(frame)
        
        if landmarks:
            # Draw skeleton
            detector.draw_landmarks(frame, landmarks)
            
            # Extract features
            features = extractor.extract_pushup_features(landmarks)
            
            if features:
                # Verify feature count
                feature_count = len(features)
                cv2.putText(frame, f"Features extracted: {feature_count}/16", 
                          (10, 30), cv2.FONT_HERSHEY_SIMPLEX, 0.7, 
                          (0, 255, 0) if feature_count == 16 else (0, 0, 255), 2)
                
                # Display some key features
                y_offset = 60
                key_features = ['elbow_angle_avg', 'spine_angle', 'elbow_flexion', 
                               'is_bottom_position', 'is_top_position']
                for key in key_features:
                    if key in features:
                        value = features[key]
                        text = f"{key}: {value:.2f}"
                        cv2.putText(frame, text, (10, y_offset), 
                                  cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 1)
                        y_offset += 25
        
        cv2.imshow('Feature Extraction Test', frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
    
    cap.release()
    cv2.destroyAllWindows()
    detector.close()