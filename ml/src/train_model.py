"""
Train Random Forest classifier for exercise form classification
"""

import pandas as pd
import numpy as np
from sklearn.ensemble import RandomForestClassifier
from sklearn.model_selection import train_test_split, cross_val_score
from sklearn.metrics import classification_report, confusion_matrix
import joblib
import json
import os
from datetime import datetime

def train_form_classifier(data_path, model_output_path, exercise='pushup'):
    """
    Train Random Forest classifier
    
    Args:
        data_path: Path to labeled CSV
        model_output_path: Where to save trained model
        exercise: Exercise type
    """
    print(f"Training {exercise} form classifier...")
    print("=" * 70)
    
    # 1. Load data
    df = pd.read_csv(data_path)
    print(f"Loaded {len(df)} samples")
    print(f"\nLabel distribution:\n{df['label'].value_counts()}\n")
    
    # 2. Prepare features and labels
    feature_cols = [col for col in df.columns 
                   if col not in ['label', 'video_source', 'frame_number']]
    X = df[feature_cols].values
    y = df['label'].values
    
    print(f"Features: {feature_cols}")
    print(f"Number of features: {len(feature_cols)}\n")
    
    # 3. Train/test split
    X_train, X_test, y_train, y_test = train_test_split(
        X, y, test_size=0.2, random_state=42, stratify=y
    )
    
    print(f"Training samples: {len(X_train)}")
    print(f"Testing samples: {len(X_test)}\n")
    
    # 4. Train Random Forest
    print("Training Random Forest...")
    model = RandomForestClassifier(
        n_estimators=100,
        max_depth=10,
        min_samples_split=5,
        random_state=42,
        n_jobs=-1
    )
    model.fit(X_train, y_train)
    print("Training complete!\n")
    
    # 5. Cross-validation
    cv_scores = cross_val_score(model, X_train, y_train, cv=5)
    print(f"Cross-validation scores: {cv_scores}")
    print(f"Mean CV score: {cv_scores.mean():.3f} (+/- {cv_scores.std() * 2:.3f})\n")
    
    # 6. Evaluate on test set
    y_pred = model.predict(X_test)
    accuracy = (y_pred == y_test).mean()
    
    print(f"Test Accuracy: {accuracy:.3f}\n")
    print("Classification Report:")
    print(classification_report(y_test, y_pred))
    
    print("Confusion Matrix:")
    cm = confusion_matrix(y_test, y_pred)
    print(cm)
    print()
    
    # 7. Feature importance
    feature_importance = pd.DataFrame({
        'feature': feature_cols,
        'importance': model.feature_importances_
    }).sort_values('importance', ascending=False)
    
    print("Feature Importance:")
    print(feature_importance)
    print()
    
    # 8. Save model
    os.makedirs(os.path.dirname(model_output_path), exist_ok=True)
    joblib.dump(model, model_output_path)
    print(f"Model saved: {model_output_path}")
    
    # 9. Save metadata
    metadata = {
        'exercise': exercise,
        'training_date': datetime.now().isoformat(),
        'num_samples': len(df),
        'num_features': len(feature_cols),
        'feature_names': feature_cols,
        'test_accuracy': float(accuracy),
        'cv_mean': float(cv_scores.mean()),
        'cv_std': float(cv_scores.std()),
        'classes': list(model.classes_)
    }
    
    metadata_path = model_output_path.replace('.pkl', '_metadata.json')
    with open(metadata_path, 'w') as f:
        json.dump(metadata, f, indent=2)
    print(f"Metadata saved: {metadata_path}")
    
    return model, accuracy

if __name__ == "__main__":
    # Train push-up classifier
    model, acc = train_form_classifier(
        data_path='ml/data/processed/pushup_labeled.csv',
        model_output_path='ml/models/pushup_form_classifier.pkl',
        exercise='pushup'
    )
    
    print("\n" + "=" * 70)
    print(f"Training completed! Final accuracy: {acc:.3f}")
    print("=" * 70)