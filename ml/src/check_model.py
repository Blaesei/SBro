"""Check what classes the trained model predicts"""
import joblib

# Load model
model = joblib.load('ml/models/pushup_form_classifier.pkl')

# Print classes
print("Model predicts these classes:")
print(model.classes_)