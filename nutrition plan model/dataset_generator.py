import numpy as np
import pandas as pd

np.random.seed(42)

n = 2000
data = []

for _ in range(n):
    weight = np.random.randint(50, 110)
    height = np.random.randint(150, 200)
    body_fat = np.random.randint(10, 35)
    muscle_mass = np.random.randint(20, 40)

    # NEW: body water mass (kg)
    water_perc = round(weight * np.random.uniform(0.45, 0.65), 2)

    bmr = np.random.randint(1300, 2200)
    goal = np.random.choice(['Weight Loss', 'Weight Gain', 'Muscle Gain'])

    if goal == 'Weight Loss':
        calories = bmr - 500
        p, c, f = 0.4, 0.3, 0.3
    elif goal == 'Weight Gain':
        calories = bmr + 500
        p, c, f = 0.25, 0.5, 0.25
    else:
        calories = bmr + 300
        p, c, f = 0.35, 0.45, 0.2

    protein = (calories * p) / 4
    carbs = (calories * c) / 4
    fat = (calories * f) / 9

    data.append([
        weight, height, body_fat, muscle_mass, water_perc,
        bmr, goal,
        round(calories, 2), round(protein, 2),
        round(carbs, 2), round(fat, 2)
    ])

df = pd.DataFrame(data, columns=[
    'weight', 'height', 'body_fat', 'muscle_mass', 'water_perc',
    'bmr', 'goal',
    'calories', 'protein', 'carbs', 'fat'
])

df.to_csv("nutrition_dataset.csv", index=False)
print("✅ Dataset generated with water_perc!")

# Save separate test dataset
df.sample(frac=0.2, random_state=42).to_csv("nutrition_test_dataset.csv", index=False)
print("✅ Test dataset generated!")


# -------------------------
# Workout Dataset Generator
# -------------------------


np.random.seed(42)

n = 2000
data = []

for _ in range(n):
    weight = np.random.randint(50, 110)
    height = np.random.randint(150, 200)
    body_fat = np.random.randint(10, 35)
    muscle_mass = np.random.randint(20, 40)
    water_perc = np.random.uniform(45, 65)
    goal = np.random.choice(['Weight Loss', 'Weight Gain', 'Muscle Gain'])

    # -------------------------
    # Workout Logic (realistic-ish)
    # -------------------------

    if goal == "Weight Loss":
        training_days = np.random.randint(4, 6)
        cardio = np.random.randint(150, 300)
        strength = np.random.randint(2, 4)
        sets = np.random.randint(2, 4)
        reps = np.random.randint(12, 20)

    elif goal == "Muscle Gain":
        training_days = np.random.randint(4, 6)
        cardio = np.random.randint(30, 90)
        strength = np.random.randint(4, 6)
        sets = np.random.randint(3, 5)
        reps = np.random.randint(6, 12)

    else:  # Weight Gain
        training_days = np.random.randint(3, 5)
        cardio = np.random.randint(20, 60)
        strength = np.random.randint(3, 5)
        sets = np.random.randint(3, 5)
        reps = np.random.randint(6, 10)

    # Add noise for realism
    training_days = np.clip(training_days + np.random.choice([0, 0, 1]), 3, 6)
    cardio = max(20, cardio + np.random.randint(-10, 10))

    data.append([
        weight, height, body_fat, muscle_mass, water_perc, goal,
        training_days, cardio, strength, sets, reps
    ])

df = pd.DataFrame(data, columns=[
    'weight', 'height', 'body_fat', 'muscle_mass', 'water_perc', 'goal',
    'training_days', 'cardio_minutes', 'strength_sessions', 'sets', 'reps'
])

df.to_csv("workout_dataset.csv", index=False)
print("✅ Workout dataset generated!")