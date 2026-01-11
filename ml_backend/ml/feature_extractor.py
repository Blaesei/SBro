"""
Biomechanical Feature Extraction
Converts pose landmarks to angles, ratios, and distances
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
        Extract features specific to push-up exercise
        
        Landmarks indices (MediaPipe):
        11 = Left Shoulder    12 = Right Shoulder
        13 = Left Elbow       14 = Right Elbow
        15 = Left Wrist       16 = Right Wrist
        23 = Left Hip         24 = Right Hip
        25 = Left Knee        26 = Right Knee
        27 = Left Ankle       28 = Right Ankle
        """
        try:
            # IMPORTANT: Access landmarks using .landmark attribute
            lm = landmarks.landmark
            
            # Use right side (can average with left for robustness)
            shoulder = lm[12]
            elbow = lm[14]
            wrist = lm[16]
            hip = lm[24]
            knee = lm[26]
            ankle = lm[28]
            
            # Feature 1: Elbow angle (should be ~90° at bottom, 180° at top)
            elbow_angle = self.calculate_angle(shoulder, elbow, wrist)
            
            # Feature 2: Back alignment (should be ~180° - straight line)
            back_angle = self.calculate_angle(shoulder, hip, ankle)
            
            # Feature 3: Hip height ratio (detect sagging)
            hip_height_ratio = hip.y / shoulder.y
            
            # Feature 4: Torso length (shoulder to hip distance)
            torso_length = self.calculate_distance(shoulder, hip)
            
            # Feature 5: Body tilt (forward lean)
            # Create a virtual point above shoulder for vertical reference
            class VirtualPoint:
                def __init__(self, x, y):
                    self.x = x
                    self.y = y
            
            vertical_ref = VirtualPoint(shoulder.x, shoulder.y - 0.1)
            body_tilt = self.calculate_angle(vertical_ref, shoulder, hip)
            
            # Feature 6: Elbow spread (how far elbows are from body)
            elbow_spread = abs(elbow.x - shoulder.x)
            
            # Feature 7: Shoulder-hip alignment (lateral)
            lateral_alignment = abs(shoulder.x - hip.x)
            
            # Feature 8: Knee bend (should be minimal in plank)
            knee_angle = self.calculate_angle(hip, knee, ankle)
            
            return {
                'elbow_angle': elbow_angle,
                'back_angle': back_angle,
                'hip_height_ratio': hip_height_ratio,
                'torso_length': torso_length,
                'body_tilt': body_tilt,
                'elbow_spread': elbow_spread,
                'lateral_alignment': lateral_alignment,
                'knee_angle': knee_angle
            }
        
        except Exception as e:
            print(f"Feature extraction error: {e}")
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
    
    print("Feature Extractor Test")
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
                # Display features on frame
                y_offset = 30
                for key, value in features.items():
                    text = f"{key}: {value:.2f}"
                    cv2.putText(frame, text, (10, y_offset), 
                              cv2.FONT_HERSHEY_SIMPLEX, 0.5, (0, 255, 0), 1)
                    y_offset += 20
        
        cv2.imshow('Feature Extraction Test', frame)
        if cv2.waitKey(1) & 0xFF == ord('q'):
            break
    
    cap.release()
    cv2.destroyAllWindows()
    detector.close()