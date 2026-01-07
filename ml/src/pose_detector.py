"""
SpotBro Prototype: Pose Detection + ML Feedback
Windows 11 + Python 3.11 + MediaPipe 0.10+
"""

import mediapipe as mp
import cv2
import numpy as np
from sklearn.ensemble import RandomForestClassifier

# ========================
# Pose Detector
# ========================
class PoseDetector:
    def __init__(self, min_detection_confidence=0.5, min_tracking_confidence=0.5):
        self.mp_pose = mp.solutions.pose
        self.pose = self.mp_pose.Pose(
            min_detection_confidence=min_detection_confidence,
            min_tracking_confidence=min_tracking_confidence
        )
        self.mp_drawing = mp.solutions.drawing_utils

    def detect(self, image):
        """
        Detect pose landmarks in an image
        Returns:
            landmarks: NormalizedLandmarkList object or None
        """
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        results = self.pose.process(image_rgb)
        if results.pose_landmarks:
            return results.pose_landmarks  # ✅ use as-is for drawing & ML
        return None

    def draw_landmarks(self, image, landmarks):
        """Draw pose skeleton on image"""
        if landmarks:
            self.mp_drawing.draw_landmarks(
                image,
                landmarks,  # pass NormalizedLandmarkList directly
                self.mp_pose.POSE_CONNECTIONS
            )
        return image

    def close(self):
        self.pose.close()

# ========================
# Convert landmarks to feature vector
# ========================
def landmarks_to_vector(landmarks):
    """
    Converts NormalizedLandmarkList to a flat vector
    [x1, y1, z1, x2, y2, z2, ...]
    """
    vector = []
    for lm in landmarks.landmark:
        vector.extend([lm.x, lm.y, lm.z])
    return np.array(vector).reshape(1, -1)

# ========================
# Dummy ML classifier for demonstration
# ========================
clf = RandomForestClassifier()
X_dummy = np.array([
    np.zeros(33*3),  # all zeros → Good form
    np.ones(33*3)    # all ones → Bad form
])
y_dummy = ["Good form", "Bad form"]
clf.fit(X_dummy, y_dummy)

# ========================
# Main script
# ========================
if __name__ == "__main__":
    detector = PoseDetector()
    cap = cv2.VideoCapture(0)

    print("Press 'q' to quit the webcam.")

    while cap.isOpened():
        ret, frame = cap.read()
        if not ret:
            break

        # Detect pose
        landmarks = detector.detect(frame)

        feedback = "No person detected"
        if landmarks:
            # Draw skeleton
            detector.draw_landmarks(frame, landmarks)

            # Convert landmarks to feature vector
            feature = landmarks_to_vector(landmarks)

            # Predict feedback using ML classifier
            feedback = clf.predict(feature)[0]

        # Display feedback on screen
        cv2.putText(frame, f"Feedback: {feedback}", (20, 40),
                    cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)

        # Show webcam frame
        cv2.imshow("SpotBro Real-Time Feedback", frame)

        if cv2.waitKey(1) & 0xFF == ord('q'):
            break

    cap.release()
    cv2.destroyAllWindows()
    detector.close()
