"""
Record videos for dataset creation
Press SPACE to start/stop recording
Press 'q' to quit
"""

import cv2
import os
from datetime import datetime

def record_videos(output_dir='ml/data/raw_videos'):
    """
    Record training videos for dataset creation
    
    Args:
        output_dir: Directory to save recorded videos
    """
    os.makedirs(output_dir, exist_ok=True)
    
    cap = cv2.VideoCapture(0)
    
    # Check if camera opened successfully
    if not cap.isOpened():
        print("Error: Could not open camera")
        return
    
    recording = False
    out = None
    video_count = 0
    
    print("=" * 60)
    print("VIDEO RECORDING TOOL FOR SPOTBRO DATASET")
    print("=" * 60)
    print("\nInstructions:")
    print("  SPACE - Start/Stop recording")
    print("  'q'   - Quit application")
    print("\nRecording Guide:")
    print("  1. Record 5-10 videos per form type")
    print("  2. Perform 10-15 reps per video")
    print("  3. Rename files after recording:")
    print("     - pushup_good_01.mp4")
    print("     - pushup_elbows_01.mp4")
    print("     - pushup_back_01.mp4")
    print("     - pushup_depth_01.mp4")
    print("-" * 60)
    
    while True:
        ret, frame = cap.read()
        if not ret:
            print("Error: Failed to capture frame")
            break
        
        # Flip frame horizontally (mirror effect)
        frame = cv2.flip(frame, 1)
        
        # Display recording status
        if recording:
            cv2.circle(frame, (30, 30), 15, (0, 0, 255), -1)  # Red dot
            cv2.putText(frame, "RECORDING", (60, 40), 
                       cv2.FONT_HERSHEY_SIMPLEX, 1, (0, 0, 255), 2)
            out.write(frame)
        else:
            cv2.circle(frame, (30, 30), 15, (128, 128, 128), -1)  # Gray dot
            cv2.putText(frame, "READY (Press SPACE)", (60, 40), 
                       cv2.FONT_HERSHEY_SIMPLEX, 0.7, (255, 255, 255), 2)
        
        # Display video count
        cv2.putText(frame, f"Videos recorded: {video_count}", (10, frame.shape[0] - 20), 
                   cv2.FONT_HERSHEY_SIMPLEX, 0.6, (255, 255, 255), 1)
        
        cv2.imshow('Dataset Recording', frame)
        
        key = cv2.waitKey(1) & 0xFF
        
        if key == ord(' '):  # Space bar
            if not recording:
                # Start recording
                timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                filename = f"{output_dir}/video_{timestamp}.mp4"
                fourcc = cv2.VideoWriter_fourcc(*'mp4v')
                fps = 20.0
                frame_size = (frame.shape[1], frame.shape[0])
                out = cv2.VideoWriter(filename, fourcc, fps, frame_size)
                recording = True
                print(f"\n▶ Recording started: {filename}")
            else:
                # Stop recording
                recording = False
                out.release()
                video_count += 1
                print(f"■ Recording stopped (Total videos: {video_count})")
        
        elif key == ord('q'):
            print("\nQuitting...")
            break
    
    # Cleanup
    if recording:
        out.release()
    cap.release()
    cv2.destroyAllWindows()
    
    print("\n" + "=" * 60)
    print(f"Recording session complete!")
    print(f"Total videos recorded: {video_count}")
    print(f"Videos saved in: {output_dir}")
    print("\nNext steps:")
    print("  1. Rename videos according to form type")
    print("  2. Run: python ml/src/data_labeling.py")
    print("=" * 60)

if __name__ == "__main__":
    record_videos() 