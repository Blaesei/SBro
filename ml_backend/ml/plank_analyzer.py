"""
Plank hold time analyzer
Different from reps - tracks hold duration and form stability
"""
import time
from collections import deque
from pose_detector import PoseDetector
from feature_extractor import FeatureExtractor

class PlankAnalyzer:
    """
    Analyzes plank holds
    - Tracks hold duration
    - Monitors form degradation over time
    - Detects common mistakes (hips sagging, pike, etc.)
    """
    
    def __init__(self):
        self.detector = PoseDetector()
        self.extractor = FeatureExtractor()
        
        self.is_holding = False
        self.hold_start_time = 0
        self.total_hold_time = 0
        self.current_hold_time = 0
        
        # Track form over time
        self.form_scores_over_time = []
        self.position_buffer = deque(maxlen=10)
        
        # Thresholds
        self.MIN_BACK_ANGLE = 160  # Degrees
        self.MAX_BACK_ANGLE = 180
        self.HIP_RANGE = (0.85, 1.05)  # Relative to shoulders
        
    def analyze_frame(self, frame):
        """Analyze plank position and hold time"""
        landmarks = self.detector.detect(frame)
        
        if not landmarks:
            return self._no_person_response()
        
        features = self.extractor.extract_pushup_features(landmarks)
        if not features:
            return self._no_features_response()
        
        # Check if in valid plank position
        in_position, position_msg = self._validate_plank_position(features)
        
        current_time = time.time()
        
        if in_position:
            if not self.is_holding:
                # Start hold
                self.is_holding = True
                self.hold_start_time = current_time
                print("✓ Plank hold started")
            
            # Update current hold time
            self.current_hold_time = current_time - self.hold_start_time
            self.total_hold_time = self.current_hold_time
            
            # Track form score
            form_score = self._calculate_form_score(features)
            self.form_scores_over_time.append({
                'time': self.current_hold_time,
                'score': form_score
            })
        else:
            if self.is_holding:
                # End hold
                print(f"✓ Plank hold ended: {self.current_hold_time:.1f}s")
            self.is_holding = False
        
        # Generate feedback
        issues, suggestions = self._get_plank_feedback(features, in_position)
        
        return {
            'is_holding': self.is_holding,
            'current_hold_time': self.current_hold_time,
            'total_hold_time': self.total_hold_time,
            'position_valid': in_position,
            'position_msg': position_msg,
            'issues': issues,
            'suggestions': suggestions,
            'landmarks': landmarks,
            'form_score': self._calculate_form_score(features) if in_position else 0
        }
    
    def _validate_plank_position(self, features):
        """Check if in proper plank position"""
        back_angle = features.get('back_angle', 0)
        hip_ratio = features.get('hip_height_ratio', 0)
        knee_angle = features.get('knee_angle', 180)
        
        # Check back is straight
        if back_angle < self.MIN_BACK_ANGLE:
            return False, "❌ Hips sagging - raise hips"
        
        if back_angle > self.MAX_BACK_ANGLE:
            return False, "❌ Hips too high - lower hips"
        
        # Check hip alignment
        if hip_ratio < self.HIP_RANGE[0]:
            return False, "❌ Hips too low"
        if hip_ratio > self.HIP_RANGE[1]:
            return False, "❌ Hips too high (piking)"
        
        # Check legs are straight
        if knee_angle < 165:
            return False, "❌ Keep legs straight"
        
        return True, "✓ Perfect plank position"
    
    def _calculate_form_score(self, features):
        """Calculate 0-100 form score for plank"""
        score = 100
        
        back_angle = features.get('back_angle', 170)
        hip_ratio = features.get('hip_height_ratio', 1.0)
        knee_angle = features.get('knee_angle', 180)
        
        # Deduct for back not straight
        ideal_back = 170
        back_deviation = abs(back_angle - ideal_back)
        score -= min(back_deviation * 2, 30)
        
        # Deduct for hip misalignment
        ideal_hip = 0.95
        hip_deviation = abs(hip_ratio - ideal_hip)
        score -= min(hip_deviation * 100, 30)
        
        # Deduct for bent knees
        if knee_angle < 170:
            score -= (170 - knee_angle) * 2
        
        return max(0, min(100, score))
    
    def _get_plank_feedback(self, features, in_position):
        """Generate feedback for plank form"""
        issues = []
        suggestions = []
        
        if not in_position:
            return issues, suggestions
        
        back_angle = features.get('back_angle', 170)
        hip_ratio = features.get('hip_height_ratio', 1.0)
        
        if back_angle < 165:
            issues.append("⚠ Hips sagging")
            suggestions.append("→ Engage your core and glutes")
        elif back_angle > 175:
            issues.append("⚠ Hips too high (piking)")
            suggestions.append("→ Lower hips to create straight line")
        
        if hip_ratio < 0.90:
            issues.append("⚠ Body alignment off")
            suggestions.append("→ Adjust hip height")
        
        if not issues:
            issues.append("✓ Perfect form!")
            suggestions.append("Keep holding!")
        
        return issues, suggestions
    
    def _no_person_response(self):
        return {
            'is_holding': False,
            'current_hold_time': 0,
            'total_hold_time': self.total_hold_time,
            'position_valid': False,
            'position_msg': "No person detected",
            'issues': [],
            'suggestions': [],
            'landmarks': None,
            'form_score': 0
        }
    
    def _no_features_response(self):
        return {
            'is_holding': False,
            'current_hold_time': 0,
            'total_hold_time': self.total_hold_time,
            'position_valid': False,
            'position_msg': "Cannot extract features",
            'issues': [],
            'suggestions': [],
            'landmarks': None,
            'form_score': 0
        }