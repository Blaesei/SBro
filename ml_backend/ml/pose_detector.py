"""
SpotBro Enhanced Pose Detector
Improvements:
- Better confidence thresholds for stable tracking
- Temporal smoothing to reduce jitter
- Visibility filtering for occluded landmarks
- Enhanced drawing with confidence visualization
- Performance optimizations
"""

import mediapipe as mp
import cv2
import numpy as np
from collections import deque

class PoseDetector:
    """Enhanced pose detection with temporal smoothing and quality filters"""
    
    def __init__(self, 
                 min_detection_confidence=0.7,  # Increased for better initial detection
                 min_tracking_confidence=0.6,    # Balanced for smooth tracking
                 model_complexity=1,             # 0=lite, 1=full, 2=heavy
                 smooth_landmarks=True,          # Enable MediaPipe smoothing
                 enable_segmentation=False,      # Disable for performance
                 temporal_smoothing=True,        # Our custom smoothing
                 smoothing_window=5):            # Frames to average
        
        self.mp_pose = mp.solutions.pose
        self.mp_drawing = mp.solutions.drawing_utils
        self.mp_drawing_styles = mp.solutions.drawing_styles
        
        # Initialize pose detector with optimized settings
        self.pose = self.mp_pose.Pose(
            static_image_mode=False,           # Video mode for better tracking
            model_complexity=model_complexity,
            smooth_landmarks=smooth_landmarks,
            enable_segmentation=enable_segmentation,
            smooth_segmentation=False,
            min_detection_confidence=min_detection_confidence,
            min_tracking_confidence=min_tracking_confidence
        )
        
        # Temporal smoothing
        self.temporal_smoothing = temporal_smoothing
        self.smoothing_window = smoothing_window
        self.landmark_history = deque(maxlen=smoothing_window)
        
        # Performance tracking
        self.frame_count = 0
        self.detection_count = 0
        self.total_confidence = 0.0
        
        # Landmark visibility threshold
        self.visibility_threshold = 0.5
        
        # Drawing style
        self.landmark_drawing_spec = self.mp_drawing.DrawingSpec(
            thickness=2, 
            circle_radius=3,
            color=(0, 255, 0)  # Green landmarks
        )
        self.connection_drawing_spec = self.mp_drawing.DrawingSpec(
            thickness=2, 
            circle_radius=2,
            color=(255, 255, 255)  # White connections
        )
    
    def detect(self, image, apply_smoothing=True):
        """
        Enhanced pose detection with quality filtering
        
        Args:
            image: BGR image from cv2
            apply_smoothing: Whether to apply temporal smoothing
            
        Returns:
            landmarks: NormalizedLandmarkList or None
            confidence: Average landmark confidence (0-1)
        """
        self.frame_count += 1
        
        # Convert to RGB (MediaPipe requires RGB)
        image_rgb = cv2.cvtColor(image, cv2.COLOR_BGR2RGB)
        
        # Process with MediaPipe
        results = self.pose.process(image_rgb)
        
        if results.pose_landmarks:
            self.detection_count += 1
            
            # Calculate average confidence
            avg_confidence = self._calculate_confidence(results.pose_landmarks)
            self.total_confidence += avg_confidence
            
            # Filter low-visibility landmarks
            landmarks = self._filter_by_visibility(results.pose_landmarks)
            
            if landmarks is None:
                return None, 0.0
            
            # Apply temporal smoothing if enabled
            if self.temporal_smoothing and apply_smoothing:
                landmarks = self._apply_temporal_smoothing(landmarks)
            
            return landmarks, avg_confidence
        
        return None, 0.0
    
    def _calculate_confidence(self, landmarks):
        """Calculate average visibility/confidence across all landmarks"""
        try:
            visibilities = [lm.visibility for lm in landmarks.landmark if hasattr(lm, 'visibility')]
            if visibilities:
                return sum(visibilities) / len(visibilities)
            return 0.0
        except:
            return 0.0
    
    def _filter_by_visibility(self, landmarks):
        """
        Filter out landmarks with low visibility
        Returns None if too many key landmarks are occluded
        """
        # Key landmarks that must be visible for exercise analysis
        KEY_LANDMARKS = [
            11, 12,  # Shoulders
            13, 14,  # Elbows
            15, 16,  # Wrists
            23, 24,  # Hips
            25, 26,  # Knees
            27, 28   # Ankles
        ]
        
        visible_count = 0
        for idx in KEY_LANDMARKS:
            lm = landmarks.landmark[idx]
            if hasattr(lm, 'visibility') and lm.visibility > self.visibility_threshold:
                visible_count += 1
        
        # Require at least 75% of key landmarks to be visible
        if visible_count < len(KEY_LANDMARKS) * 0.75:
            return None
        
        return landmarks
    
    def _apply_temporal_smoothing(self, landmarks):
        """
        Apply temporal smoothing to reduce jitter
        Uses exponential moving average of landmark positions
        """
        # Add current landmarks to history
        self.landmark_history.append(landmarks)
        
        if len(self.landmark_history) < 2:
            return landmarks
        
        # Create smoothed landmarks
        smoothed = type(landmarks)()
        
        for i in range(len(landmarks.landmark)):
            # Collect positions across history
            x_values = []
            y_values = []
            z_values = []
            
            for hist_landmarks in self.landmark_history:
                lm = hist_landmarks.landmark[i]
                x_values.append(lm.x)
                y_values.append(lm.y)
                z_values.append(lm.z)
            
            # Apply weighted average (more recent = higher weight)
            weights = np.linspace(0.5, 1.0, len(x_values))
            weights = weights / weights.sum()
            
            smoothed_lm = smoothed.landmark.add()
            smoothed_lm.x = np.average(x_values, weights=weights)
            smoothed_lm.y = np.average(y_values, weights=weights)
            smoothed_lm.z = np.average(z_values, weights=weights)
            
            # Copy visibility from current frame
            if hasattr(landmarks.landmark[i], 'visibility'):
                smoothed_lm.visibility = landmarks.landmark[i].visibility
        
        return smoothed
    
    def draw_landmarks(self, image, landmarks, confidence=None, show_confidence=True):
        """
        Enhanced landmark drawing with confidence visualization
        
        Args:
            image: Image to draw on
            landmarks: Pose landmarks
            confidence: Optional confidence score to display
            show_confidence: Whether to color-code by confidence
        """
        if landmarks is None:
            return image
        
        # Draw pose connections with style
        self.mp_drawing.draw_landmarks(
            image,
            landmarks,
            self.mp_pose.POSE_CONNECTIONS,
            landmark_drawing_spec=self.landmark_drawing_spec,
            connection_drawing_spec=self.connection_drawing_spec
        )
        
        # Draw individual landmarks with confidence-based colors
        if show_confidence:
            h, w, _ = image.shape
            for idx, lm in enumerate(landmarks.landmark):
                # Get visibility/confidence
                vis = lm.visibility if hasattr(lm, 'visibility') else 1.0
                
                # Color based on confidence
                if vis > 0.8:
                    color = (0, 255, 0)  # Green - high confidence
                elif vis > 0.5:
                    color = (0, 255, 255)  # Yellow - medium confidence
                else:
                    color = (0, 0, 255)  # Red - low confidence
                
                # Draw landmark
                cx, cy = int(lm.x * w), int(lm.y * h)
                cv2.circle(image, (cx, cy), 4, color, -1)
                cv2.circle(image, (cx, cy), 5, (255, 255, 255), 1)
        
        # Display overall confidence
        if confidence is not None and show_confidence:
            conf_text = f"Confidence: {confidence:.2%}"
            conf_color = (0, 255, 0) if confidence > 0.8 else (0, 255, 255) if confidence > 0.5 else (0, 0, 255)
            cv2.putText(image, conf_text, (10, image.shape[0] - 20),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.6, conf_color, 2)
        
        return image
    
    def draw_landmark_labels(self, image, landmarks, landmark_indices=None):
        """
        Draw landmark index labels for debugging
        
        Args:
            landmark_indices: List of indices to label, or None for all
        """
        if landmarks is None:
            return image
        
        h, w, _ = image.shape
        
        if landmark_indices is None:
            # Label only key landmarks
            landmark_indices = [0, 11, 12, 13, 14, 15, 16, 23, 24, 25, 26, 27, 28]
        
        for idx in landmark_indices:
            lm = landmarks.landmark[idx]
            cx, cy = int(lm.x * w), int(lm.y * h)
            
            # Draw label background
            cv2.rectangle(image, (cx - 15, cy - 20), (cx + 15, cy - 5), (0, 0, 0), -1)
            # Draw label text
            cv2.putText(image, str(idx), (cx - 10, cy - 10),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.4, (255, 255, 255), 1)
        
        return image
    
    def get_statistics(self):
        """Get detection statistics"""
        detection_rate = (self.detection_count / self.frame_count * 100) if self.frame_count > 0 else 0
        avg_confidence = (self.total_confidence / self.detection_count) if self.detection_count > 0 else 0
        
        return {
            'frames_processed': self.frame_count,
            'detections': self.detection_count,
            'detection_rate': detection_rate,
            'average_confidence': avg_confidence
        }
    
    def reset_statistics(self):
        """Reset performance statistics"""
        self.frame_count = 0
        self.detection_count = 0
        self.total_confidence = 0.0
    
    def is_stable(self, min_frames=10, min_detection_rate=0.8):
        """Check if detection is stable enough for analysis"""
        if self.frame_count < min_frames:
            return False
        
        stats = self.get_statistics()
        return stats['detection_rate'] >= min_detection_rate * 100
    
    def close(self):
        """Release resources"""
        self.pose.close()
        self.landmark_history.clear()


# =======================
# ENHANCED TEST PROGRAM
# =======================
if __name__ == "__main__":
    print("=" * 60)
    print("Enhanced Pose Detector Test")
    print("=" * 60)
    print("Controls:")
    print("  'q' - Quit")
    print("  's' - Show statistics")
    print("  'r' - Reset statistics")
    print("  'l' - Toggle landmark labels")
    print("  't' - Toggle temporal smoothing")
    print("=" * 60)
    
    # Initialize detector with optimal settings
    detector = PoseDetector(
        min_detection_confidence=0.7,
        min_tracking_confidence=0.6,
        model_complexity=1,
        temporal_smoothing=True,
        smoothing_window=5
    )
    
    cap = cv2.VideoCapture(0)
    
    # Test settings
    show_labels = False
    use_smoothing = True
    
    while cap.isOpened():
        ret, frame = cap.read()
        if not ret:
            print("Failed to grab frame")
            break
        
        # Flip for mirror effect
        frame = cv2.flip(frame, 1)
        
        # Detect pose
        landmarks, confidence = detector.detect(frame, apply_smoothing=use_smoothing)
        
        # Draw results
        if landmarks:
            detector.draw_landmarks(frame, landmarks, confidence, show_confidence=True)
            
            if show_labels:
                detector.draw_landmark_labels(frame, landmarks)
            
            # Stability indicator
            if detector.is_stable():
                cv2.putText(frame, "STABLE", (10, 30),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 0), 2)
            else:
                cv2.putText(frame, "STABILIZING...", (10, 30),
                           cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 255, 255), 2)
        else:
            cv2.putText(frame, "NO POSE DETECTED", (10, 30),
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (0, 0, 255), 2)
        
        # Smoothing indicator
        smoothing_text = f"Smoothing: {'ON' if use_smoothing else 'OFF'}"
        cv2.putText(frame, smoothing_text, (10, frame.shape[0] - 50),
                   cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 2)
        
        # Display frame
        cv2.imshow('Enhanced Pose Detection', frame)
        
        # Handle key presses
        key = cv2.waitKey(1) & 0xFF
        
        if key == ord('q'):
            break
        elif key == ord('s'):
            stats = detector.get_statistics()
            print("\n" + "=" * 60)
            print("DETECTION STATISTICS")
            print("=" * 60)
            print(f"Frames Processed:  {stats['frames_processed']}")
            print(f"Detections:        {stats['detections']}")
            print(f"Detection Rate:    {stats['detection_rate']:.1f}%")
            print(f"Avg Confidence:    {stats['average_confidence']:.2%}")
            print("=" * 60 + "\n")
        elif key == ord('r'):
            detector.reset_statistics()
            print("Statistics reset")
        elif key == ord('l'):
            show_labels = not show_labels
            print(f"Landmark labels: {'ON' if show_labels else 'OFF'}")
        elif key == ord('t'):
            use_smoothing = not use_smoothing
            print(f"Temporal smoothing: {'ON' if use_smoothing else 'OFF'}")
    
    # Final statistics
    print("\n" + "=" * 60)
    print("FINAL STATISTICS")
    print("=" * 60)
    stats = detector.get_statistics()
    for key, value in stats.items():
        if isinstance(value, float):
            print(f"{key}: {value:.2f}")
        else:
            print(f"{key}: {value}")
    print("=" * 60)
    
    cap.release()
    cv2.destroyAllWindows()
    detector.close()