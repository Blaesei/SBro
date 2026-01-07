"""
Extract features from videos and create labeled dataset
"""

import cv2
import pandas as pd
import os
import sys
from pose_detector import PoseDetector
from feature_extractor import FeatureExtractor

def extract_features_from_video(video_path, label, exercise='pushup'):
    """
    Extract features from all frames in a video
    
    Args:
        video_path: Path to video file
        label: Ground truth label (e.g., 'GOOD_FORM', 'ELBOWS_FLARED')
        exercise: Exercise type ('pushup' or 'squat')
    
    Returns:
        DataFrame with features and labels
    """
    detector = PoseDetector()
    extractor = FeatureExtractor()
    
    cap = cv2.VideoCapture(video_path)
    data = []
    frame_count = 0
    processed_count = 0
    
    print(f"Processing: {os.path.basename(video_path)} (Label: {label})")
    
    while cap.isOpened():
        ret, frame = cap.read()
        if not ret:
            break
        
        frame_count += 1
        
        # Skip every 2 frames to reduce dataset size (process 1 of every 3)
        if frame_count % 3 != 0:
            continue
        
        # Detect pose
        landmarks = detector.detect(frame)
        
        if landmarks:
            # Extract features
            if exercise == 'pushup':
                features = extractor.extract_pushup_features(landmarks)
            elif exercise == 'squat':
                features = extractor.extract_squat_features(landmarks)
            else:
                continue
            
            if features:
                features['label'] = label
                features['video_source'] = os.path.basename(video_path)
                features['frame_number'] = frame_count
                data.append(features)
                processed_count += 1
    
    cap.release()
    detector.close()
    
    print(f"  → Extracted {processed_count} frames (from {frame_count} total)")
    
    return pd.DataFrame(data)

def create_dataset(video_dir='ml/data/raw_videos', 
                  output_file='ml/data/processed/pushup_labeled.csv',
                  exercise='pushup'):
    """
    Process all videos in directory and create CSV dataset
    
    Video naming convention:
      pushup_good_01.mp4      -> Label: GOOD_FORM
      pushup_elbows_01.mp4    -> Label: ELBOWS_FLARED
      pushup_back_01.mp4      -> Label: BACK_SAGGING
      pushup_depth_01.mp4     -> Label: INCOMPLETE_DEPTH
      pushup_neck_01.mp4      -> Label: NECK_STRAIN
    """
    
    print("=" * 70)
    print("SPOTBRO DATASET CREATION")
    print("=" * 70)
    print(f"\nExercise: {exercise}")
    print(f"Input directory: {video_dir}")
    print(f"Output file: {output_file}")
    print("-" * 70)
    
    all_data = []
    
    # Define label mapping based on filename keywords
    label_keywords = {
        'good': 'GOOD_FORM',
        'elbows': 'ELBOWS_FLARED',
        'back': 'BACK_SAGGING',
        'depth': 'INCOMPLETE_DEPTH',
        'neck': 'NECK_STRAIN'
    }
    
    # Count videos
    video_files = [f for f in os.listdir(video_dir) 
                   if f.endswith(('.mp4', '.avi', '.mov'))]
    
    if not video_files:
        print(f"\n❌ No videos found in {video_dir}")
        print("Please record videos first using: python scripts/record_dataset.py")
        return None
    
    print(f"\nFound {len(video_files)} videos\n")
    
    # Process each video
    for idx, filename in enumerate(video_files, 1):
        # Determine label from filename
        label = None
        for keyword, label_name in label_keywords.items():
            if keyword in filename.lower():
                label = label_name
                break
        
        if label is None:
            print(f"⚠ Skipping {filename}: Unknown label (no keyword found)")
            continue
        
        video_path = os.path.join(video_dir, filename)
        print(f"\n[{idx}/{len(video_files)}]")
        df = extract_features_from_video(video_path, label, exercise)
        
        if len(df) > 0:
            all_data.append(df)
        else:
            print(f"  ⚠ Warning: No features extracted from {filename}")
    
    if not all_data:
        print("\n❌ No data extracted! Check your videos.")
        return None
    
    # Combine all data
    final_df = pd.concat(all_data, ignore_index=True)
    
    # Save to CSV
    os.makedirs(os.path.dirname(output_file), exist_ok=True)
    final_df.to_csv(output_file, index=False)
    
    print("\n" + "=" * 70)
    print("✓ DATASET CREATED SUCCESSFULLY!")
    print("=" * 70)
    print(f"\nOutput file: {output_file}")
    print(f"Total samples: {len(final_df)}")
    print(f"\nLabel distribution:")
    print(final_df['label'].value_counts())
    print("\nFeature columns:")
    print([col for col in final_df.columns if col not in ['label', 'video_source', 'frame_number']])
    print("\n" + "=" * 70)
    print("\nNext step: python ml/src/train_model.py")
    print("=" * 70)
    
    return final_df

if __name__ == "__main__":
    # Create dataset for push-ups
    df = create_dataset(
        video_dir='ml/data/raw_videos',
        output_file='ml/data/processed/pushup_labeled.csv',
        exercise='pushup'
    )