
# import pandas as pd
# import joblib
# import numpy as np
# from scipy.optimize import linprog

# # --- API imports ---
# from fastapi import FastAPI
# from pydantic import BaseModel
# import uvicorn

# # Load model & data
# model = joblib.load("nutrition_model.pkl")
# food_data = pd.read_csv("normalized_food_data.csv")

# # --------------------------
# # Utility Functions
# # --------------------------

# def encode_goal(goal):
#     return {
#         'Weight Loss': 0,
#         'Weight Gain': 1,
#         'Muscle Gain': 2
#     }[goal]

# def calculate_bmr(weight, height, age, gender):
#     if gender.lower() == 'male':
#         return 10 * weight + 6.25 * height - 5 * age + 5
#     else:
#         return 10 * weight + 6.25 * height - 5 * age - 161

# def predict_macros(weight, height, body_fat, muscle_mass, bmr, goal):
#     user_df = pd.DataFrame([{
#         'weight': weight,
#         'height': height,
#         'body_fat': body_fat,
#         'muscle_mass': muscle_mass,
#         'bmr': bmr,
#         'goal': encode_goal(goal)
#     }])

#     return model.predict(user_df)[0]

# def generate_plan(calories_target, protein_target, carbs_target, fat_target):
#     foods = food_data
#     n = len(foods)

#     c = np.ones(n)

#     calories = foods['Caloric Value'].values
#     protein = foods['Protein'].values
#     carbs = foods['Carbohydrates'].values
#     fat = foods['Fat'].values

#     A = [calories, -calories, protein, -protein, carbs, -carbs, fat, -fat]
#     b = [
#         calories_target * 1.05, -calories_target * 0.95,
#         protein_target * 1.05, -protein_target * 0.95,
#         carbs_target * 1.05, -carbs_target * 0.95,
#         fat_target * 1.05, -fat_target * 0.95
#     ]

#     bounds = [(0, 3) for _ in range(n)]

#     result = linprog(c, A_ub=A, b_ub=b, bounds=bounds, method='highs')

#     plan = []

#     if result.success:
#         for i, qty in enumerate(result.x):
#             if qty > 0.1:
#                 food = foods.iloc[i]
#                 plan.append({
#                     'food': food['food'],
#                     'servings': round(qty, 2)
#                 })

#     return plan

# # --------------------------
# # CLI FUNCTION
# # --------------------------

# def run_cli():
#     print("=== AI Nutrition Planner (CLI) ===")

#     goal = input("Goal (Weight Loss / Weight Gain / Muscle Gain): ")
#     weight = float(input("Weight: "))
#     height = float(input("Height: "))
#     age = int(input("Age: "))
#     gender = input("Gender: ")
#     body_fat = float(input("Body Fat %: "))
#     muscle_mass = float(input("Muscle Mass: "))

#     bmr = calculate_bmr(weight, height, age, gender)

#     calories, protein, carbs, fat = predict_macros(
#         weight, height, body_fat, muscle_mass, bmr, goal
#     )

#     plan = generate_plan(calories, protein, carbs, fat)

#     print("\n=== RESULT ===")
#     print(f"Calories: {int(calories)}")
#     print(f"Protein: {int(protein)}g | Carbs: {int(carbs)}g | Fat: {int(fat)}g")

#     print("\nMeal Plan:")
#     for item in plan:
#         print(f"- {item['food']} x{item['servings']}")

# # --------------------------
# # API SETUP
# # --------------------------

# app = FastAPI(title="AI Nutrition Planner API")

# class UserInput(BaseModel):
#     goal: str
#     weight: float
#     height: float
#     age: int
#     gender: str
#     body_fat: float
#     muscle_mass: float

# @app.get("/")
# def home():
#     return {"message": "API is running"}

# @app.post("/predict")
# def predict(user: UserInput):
#     bmr = calculate_bmr(user.weight, user.height, user.age, user.gender)

#     calories, protein, carbs, fat = predict_macros(
#         user.weight,
#         user.height,
#         user.body_fat,
#         user.muscle_mass,
#         bmr,
#         user.goal
#     )

#     plan = generate_plan(calories, protein, carbs, fat)

#     return {
#         "calories": int(calories),
#         "protein": int(protein),
#         "carbs": int(carbs),
#         "fat": int(fat),
#         "plan": plan
#     }

# # --------------------------
# # MAIN ENTRY
# # --------------------------

# if __name__ == "__main__":
#     mode = input("Choose mode (cli / api): ").lower()

#     if mode == "cli":
#         run_cli()
#     elif mode == "api":
#         print("Starting API at http://127.0.0.1:8000")
#         uvicorn.run(app, host="127.0.0.1", port=8000)
#     else:
#         print("Invalid mode")
import pandas as pd
import joblib
import numpy as np
from fastapi import FastAPI
from pydantic import BaseModel
import uvicorn

# Load model & food data
model = joblib.load("nutrition_model.pkl")
workout_model = joblib.load("workout_model.pkl")
food_data = pd.read_csv("normalized_food_data.csv")  # must contain: food, Caloric Value, Protein, Carbohydrates, Fat

# -------------------------
# Utility functions
# -------------------------
def encode_goal(goal):
    mapping = {'Weight Loss':0, 'Weight Gain':1, 'Muscle Gain':2}
    if goal not in mapping:
        raise ValueError("Invalid goal")
    return mapping[goal]

def calculate_bmr(weight ,height, age, gender):
    return 10*weight + 6.25*height - 5*age + (5 if gender.lower()=='male' else -161)

def predict_macros(weight, height, body_fat, muscle_mass, water_perc, bmr, goal):
    df_input = pd.DataFrame([{
        'weight': weight,
        'height': height,
        'body_fat': body_fat,
        'muscle_mass': muscle_mass,
        'water_perc': water_perc,
        'bmr': bmr,
        'goal': encode_goal(goal)
    }])
    return model.predict(df_input)[0]

def predict_workout(weight, height, body_fat, muscle_mass, water_perc, goal):
    df_input = pd.DataFrame([{
        'weight': weight,
        'height': height,
        'body_fat': body_fat,
        'muscle_mass': muscle_mass,
        'water_perc': water_perc,
        'goal': encode_goal(goal)
    }])

    return workout_model.predict(df_input)[0]

# -------------------------
# Randomized 10-meal plan
# -------------------------
def generate_plan(calories_target, protein_target, carbs_target, fat_target, num_meals=10):
    foods = food_data.copy()
    foods = foods.sample(frac=1).reset_index(drop=True)  # shuffle for randomness

    plan = []
    total_calories = total_protein = total_carbs = total_fat = 0

    for i in range(len(foods)):
        if len(plan) >= num_meals:
            break
        food = foods.iloc[i]
        serving = round(np.random.uniform(0.5, 2.0), 2)
        food_cals = food['Caloric Value'] * serving
        food_protein = food['Protein'] * serving
        food_carbs = food['Carbohydrates'] * serving
        food_fat = food['Fat'] * serving

        # add if not grossly exceeding calories
        if total_calories + food_cals <= calories_target * 1.1:
            plan.append({
                'food': food['food'],
                'servings': serving,
                'calories': round(food_cals,2),
                'protein': round(food_protein,2),
                'carbs': round(food_carbs,2),
                'fat': round(food_fat,2)
            })
            total_calories += food_cals
            total_protein += food_protein
            total_carbs += food_carbs
            total_fat += food_fat

    # fill remaining meals randomly if plan < num_meals
    while len(plan) < num_meals:
        food = foods.sample(1).iloc[0]
        serving = round(np.random.uniform(0.5, 2.0), 2)
        plan.append({
            'food': food['food'],
            'servings': serving,
            'calories': round(food['Caloric Value']*serving,2),
            'protein': round(food['Protein']*serving,2),
            'carbs': round(food['Carbohydrates']*serving,2),
            'fat': round(food['Fat']*serving,2)
        })

    return plan

def build_workout_plan(pred):
    days, cardio, strength, sets, reps = pred

    return {
        "training_days_per_week": int(days),
        "cardio_minutes_per_week": int(cardio),
        "strength_sessions": int(strength),
        "recommended_sets": int(sets),
        "recommended_reps": int(reps)
    }

# -------------------------
# CLI function
# -------------------------
def run_cli():
    print("=== AI Nutrition Planner (CLI) ===")
    goal = input("Goal (Weight Loss / Weight Gain / Muscle Gain): ")
    weight = float(input("Weight (kg): "))
    height = float(input("Height (cm): "))
    age = int(input("Age (Years): "))
    gender = input("Gender (Male/Female): ")
    body_fat = float(input("Body Fat %: "))
    muscle_mass = float(input("Muscle Mass (kg): "))
    water_perc = float(input("Body Water Mass (kg): "))

    bmr = calculate_bmr(weight, height, age, gender)

    calories, protein, carbs, fat = predict_macros(weight, height, body_fat, muscle_mass, water_perc, bmr, goal)
    workout_pred = predict_workout(weight, height, body_fat, muscle_mass, water_perc, goal)
    # calories, protein, carbs, fat = predict_macros(weight, height, body_fat, muscle_mass, bmr, goal)
    plan = generate_plan(calories, protein, carbs, fat, num_meals=10)
    workout_plan = build_workout_plan(workout_pred)

    print("\n=== RESULT ===")
    print(f"Calories: {int(calories)}")
    print(f"Protein: {int(protein)}g | Carbs: {int(carbs)}g | Fat: {int(fat)}g")
    print("\nMeal Plan:")
    for item in plan:
        print(f"- {item['food']}, {item['servings']} Serving(s) (Calories: {item['calories']} KCal, Protein: {item['protein']} Grams, Carbs: {item['carbs']} Grams, Fats: {item['fat']} Grams)")
    
    print("\n=== WORKOUT PLAN ===")
    print(f"Training Days/Week: {workout_plan['training_days_per_week']}")
    print(f"Cardio Minutes/Week: {workout_plan['cardio_minutes_per_week']}")
    print(f"Strength Sessions: {workout_plan['strength_sessions']}")
    print(f"Sets: {workout_plan['recommended_sets']}")
    print(f"Reps: {workout_plan['recommended_reps']}")

# -------------------------
# API
# -------------------------
app = FastAPI(title="AI Nutrition Planner API")

class UserInput(BaseModel):
    goal: str
    weight: float
    height: float
    age: int
    gender: str
    body_fat: float
    muscle_mass: float
    water_perc: float

@app.get("/")
def home():
    return {"message":"API is running"}

@app.post("/predict")
def predict(user: UserInput):
    bmr = calculate_bmr(user.weight,user.height,user.age,user.gender)
    calories, protein, carbs, fat = predict_macros(
        user.weight,
        user.height,
        user.body_fat,
        user.muscle_mass,
        user.water_perc,
        bmr,
        user.goal
    )

    workout_pred = predict_workout(
        user.weight,
        user.height,
        user.body_fat,
        user.muscle_mass,
        user.water_perc,
        user.goal
    )

    workout_plan = build_workout_plan(workout_pred)
    # calories, protein, carbs, fat = predict_macros(user.weight,user.height,user.body_fat,user.muscle_mass,bmr,user.goal)
    plan = generate_plan(calories, protein, carbs, fat, num_meals=10)
    return {
        "calories": int(calories),
        "protein": int(protein),
        "carbs": int(carbs),
        "fat": int(fat),
        "plan": plan,
        "workout_plan": workout_plan
    }



# -------------------------
# MAIN
# -------------------------
if __name__=="__main__":
    mode = input("Choose mode (cli / api): ").lower()
    if mode=="cli":
        run_cli()
    elif mode=="api":
        print("Starting API at http://127.0.0.1:8000")
        uvicorn.run(app, host="127.0.0.1", port=8000)
    else:
        print("Invalid mode")