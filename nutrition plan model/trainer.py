import pandas as pd
from sklearn.model_selection import train_test_split
from sklearn.ensemble import RandomForestRegressor
from sklearn.metrics import r2_score, mean_absolute_error, mean_squared_error
import joblib
import numpy as np

# Load dataset
df = pd.read_csv("nutrition_dataset.csv")

# Encode goal
df['goal'] = df['goal'].map({
    'Weight Loss': 0,
    'Weight Gain': 1,
    'Muscle Gain': 2
})

# Features (MAKE SURE this matches your dataset exactly)
X = df[['weight', 'height', 'body_fat', 'muscle_mass', 'water_perc', 'bmr', 'goal']]
y = df[['calories', 'protein', 'carbs', 'fat']]

# Split
X_train, X_test, y_train, y_test = train_test_split(
    X, y, test_size=0.2, random_state=42
)

# Train model
model = RandomForestRegressor(n_estimators=200, random_state=42)
model.fit(X_train, y_train)

# Save model
joblib.dump(model, "nutrition_model.pkl")

# -------------------------
# Evaluation function
# -------------------------
def evaluate_model(model, X_test, y_test):
    predictions = model.predict(X_test)

    # R2 Score
    r2 = r2_score(y_test, predictions)

    # MAE
    mae = mean_absolute_error(y_test, predictions)

    # RMSE
    rmse = np.sqrt(mean_squared_error(y_test, predictions))

    print("\n=== MODEL EVALUATION ===")
    print(f"R2 Score (Accuracy): {r2:.4f}")
    print(f"Mean Absolute Error (MAE): {mae:.2f}")
    print(f"Root Mean Squared Error (RMSE): {rmse:.2f}")

# Run evaluation
evaluate_model(model, X_test, y_test)

print("\n✅ Model trained, evaluated, and saved!")


# -------------------------
# Workout ML Model Trainer
# -------------------------


df = pd.read_csv("workout_dataset.csv")

# Encode goal
df['goal'] = df['goal'].map({
    'Weight Loss': 0,
    'Weight Gain': 1,
    'Muscle Gain': 2
})

X = df[['weight', 'height', 'body_fat', 'muscle_mass', 'water_perc', 'goal']]
y = df[['training_days', 'cardio_minutes', 'strength_sessions', 'sets', 'reps']]

X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)

model = RandomForestRegressor(n_estimators=200, random_state=42)
model.fit(X_train, y_train)

joblib.dump(model, "workout_model.pkl")

# Evaluation
preds = model.predict(X_test)

r2 = r2_score(y_test, preds)
mae = mean_absolute_error(y_test, preds)
rmse = np.sqrt(mean_squared_error(y_test, preds))

print("\n=== WORKOUT MODEL EVALUATION ===")
print(f"R2 Score: {r2:.4f}")
print(f"MAE: {mae:.2f}")
print(f"RMSE: {rmse:.2f}")

print("✅ Workout model trained!")