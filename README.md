# Native PHP Gym Management API

This is a native PHP REST API for a Gym Management System. It uses simple file-based routing and MySQL for data persistence.

## Project Structure

```
/config
  config.example.php
  database.php
/helpers
  cors.php
  response.php
/public
  .htaccess
  index.php
/routes
  /auth
    login.php
    register.php
  /subscriptions
    get_subscriptions.php
    create_subscription.php
    update_subscription.php
    delete_subscription.php
  /equipment
    get_equipment.php
    create_equipment.php
    update_equipment.php
    delete_equipment.php
  /bookings
    get_bookings.php
    create_booking.php
    cancel_booking.php
    use_booking.php
  /trainers
    get_trainers.php
    create_trainer.php
    update_trainer.php
    delete_trainer.php
  /sessions
    create_session.php
    book_session.php
  /nutrition
    create_diet_plan.php
    get_user_diet_plan.php
    get_meals.php
    get_diet_meals.php
  /nutritionists
    get_nutritionists.php
    create_nutritionist.php
    update_nutritionist.php
    delete_nutritionist.php
  /payments
    deposit.php
    get_history.php
  /training
    get_user_training_plan.php
    get_workout_exercises.php
README.md
```

## Setup Instructions

1. **Document Root**: Configure your web server (Apache/Nginx/XAMPP) document root to point to the `public/` folder, or access it via `http://localhost/gymproject/public/api/...`.
2. **Database Configuration**:
   * Copy `config/config.example.php` to `config/config.php`
   * Edit `config/config.php` with your MySQL database connection credentials.
3. **Database Setup**:
   Import your SQL schema. For the **Trainer System**, ensure you have the `trainer_sessions` table:
   ```sql
   CREATE TABLE trainer_sessions (
       id INT AUTO_INCREMENT PRIMARY KEY,
       trainer_id INT NOT NULL,
       user_id INT DEFAULT NULL,
       start_time DATETIME NOT NULL,
       end_time DATETIME NOT NULL,
       price DECIMAL(10,2) DEFAULT 0.00,
       status VARCHAR(50) DEFAULT 'available',
       CONSTRAINT fk_sessions_trainer FOREIGN KEY (trainer_id) REFERENCES users(id) ON DELETE CASCADE,
       CONSTRAINT fk_sessions_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
   );
   ```

## API Endpoints

Routing is handled in `public/index.php`.

### 🔐 Authentication

#### Register User
- **URL**: `POST /api/auth/register`
- **Body**: `{"name": "...", "email": "...", "password": "...", "address": "...", "age": 25, "gender": "...", "role_name": "user", "phone": "..."}`

#### Login User
- **URL**: `POST /api/auth/login`
- **Body**: `{"email": "...", "password": "..."}`

#### Logout User
- **URL**: `POST /api/auth/logout`
- **Headers**: Authorization header (session-based)

#### Get Profile
- **URL**: `GET /api/auth/profile`
- **Headers**: Authorization header (session-based)

#### Update Profile
- **URL**: `POST /api/auth/update`
- **Body**: `{"name": "...", "phone": "..."}`
- **Description**: Updates user personal info. Admins can provide a `user_id` to update others.

#### Update Specialist Profile
- **URL**: `POST /api/auth/specialist-profile`
- **Body**: `{"user_id": 5, "experience_years": 5, "bio": "...", "achievements": ["..."]}`
- **Description**: Updates or creates the detailed specialist profile. Regular users update their own profile; **Admins** can update any user's profile by providing a `user_id`.

#### Delete User (Admin Only)
- **URL**: `POST /api/auth/delete-user`
- **Body**: `{"user_id": 123}`
- **Description**: Permanently removes a user account.

### 💎 Subscription Plans

#### Get All Plans
- **URL**: `GET /api/subscriptions`
- **Response**: List of all available subscription plans.

#### Create Plan
- **URL**: `POST /api/subscriptions/create`
- **Body**: `{"name": "Premium", "description": "Full access", "plan_type": "Monthly", "price": 99.99}`

#### Update Plan
- **URL**: `POST /api/subscriptions/update`
- **Body**: `{"id": 1, "price": 109.99}` (Send only the fields you want to update along with the ID)

#### Delete Plan
- **URL**: `POST /api/subscriptions/delete`
- **Body**: `{"id": 1}`

#### Purchase Subscription Plan
- **URL**: `POST /api/subscriptions/purchase`
- **Body**: `{"plan_id": 1}`
- **Description**: Deducts the plan price from the user's balance and activates the subscription.

### 🏋️ Equipment

#### Get All Equipment
- **URL**: `GET /api/equipment` or `GET /api/equipments`
- **Response**: List of all gym equipment.

#### Add Equipment
- **URL**: `POST /api/equipment/create`
- **Body**: `{"name": "Treadmill", "description": "Pro Series", "booking_price": 5.00, "status": "available"}`

#### Update Equipment
- **URL**: `POST /api/equipment/update`
- **Body**: `{"id": 1, "status": "maintenance"}`

#### Delete Equipment
- **URL**: `POST /api/equipment/delete`
- **Body**: `{"id": 1}`

### 🏃 Exercises

#### Get All Exercises
- **URL**: `GET /api/exercises`
- **Response**: List of all exercises with their associated equipment.

#### Add Exercise
- **URL**: `POST /api/exercises/create`
- **Body**: `{"name": "Bench Press", "description": "Chest workout", "muscle_name": "Chest", "equipment_id": 1}`

#### Update Exercise
- **URL**: `POST /api/exercises/update`
- **Body**: `{"id": 1, "name": "Incline Bench Press"}` (Send only the fields you want to update along with the ID)

#### Delete Exercise
- **URL**: `POST /api/exercises/delete`
- **Body**: `{"id": 1}`

### 📅 Bookings

#### Get My Bookings
- **URL**: `GET /api/bookings`
- **Description**: Returns all bookings for the logged-in user.

#### Create Booking
- **URL**: `POST /api/bookings/create`
- **Body**: `{"equipment_id": 1, "start_time": "2026-04-10 10:00:00", "end_time": "2026-04-10 11:30:00"}`
- **Note**: This will automatically deduct the equipment's booking price from the user's balance and check for overlaps.

#### Cancel Booking
- **URL**: `POST /api/bookings/cancel`
- **Body**: `{"booking_id": 123}`
- **Note**: Refunds the user balance if the booking has not started yet.

#### Find Active Session
- **URL**: `GET /api/bookings/use`
- **Description**: Returns the user's booking that is currently within its scheduled time slot.

### 👤 Trainers & Nutritionists

#### List All Specialists
- **URL**: `GET /api/specialists`
- **Description**: Returns all trainers and nutritionists in a single combined list.

#### Create Specialist
- **URL**: `POST /api/specialists/create`
- **Body**: `{"name": "John Doe", "email": "john@gym.com", "role_name": "trainer", "experience_years": 5, "bio": "..."}`
- **Description**: Creates a new user account with the specified role and simultaneously creates their professional specialist profile.

#### List All Trainers / Coaches
- **URL**: `GET /api/trainers` or `GET /api/coaches`
- **Description**: Returns all users with the `trainer` role and their detailed profiles.

#### List All Nutritionists
- **URL**: `GET /api/nutritionists`
- **Description**: Returns all users with the `nutritionist` role and their detailed profiles.

#### Add Trainer (Admin Only)
- **URL**: `POST /api/trainers/create`
- **Body**: `{"name": "...", "email": "...", "experience_years": 5, "bio": "..."}`

#### Update Trainer
- **URL**: `POST /api/trainers/update`
- **Body**: `{"user_id": 5, "name": "...", "experience_years": 6}` (Admins can update any; trainers update themselves)

#### Delete Trainer (Admin Only)
- **URL**: `POST /api/trainers/delete`
- **Body**: `{"user_id": 5}`

#### Add Nutritionist (Admin Only)
- **URL**: `POST /api/nutritionists/create`
- **Body**: `{"name": "...", "email": "...", "experience_years": 3, "bio": "..."}`

#### Update Nutritionist
- **URL**: `POST /api/nutritionists/update`
- **Body**: `{"user_id": 10, "name": "...", "experience_years": 4}` (Admins can update any; nutritionists update themselves)

#### Delete Nutritionist (Admin Only)
- **URL**: `POST /api/nutritionists/delete`
- **Body**: `{"user_id": 10}`

#### Create Session (Trainer Only)
- **URL**: `POST /api/sessions/create`
- **Body**: `{"start_time": "2026-04-12 14:00:00", "end_time": "2026-04-12 15:00:00", "price": 25.00}`
- **Note**: Requires the logged-in user to have the `role_name = 'trainer'`.

#### Book Session
- **URL**: `POST /api/sessions/book`
- **Body**: `{"session_id": 1}`
- **Note**: Deducts the session price from the user's balance.

### 🍎 Nutrition Plans

#### Create Nutrition Plan
- **URL**: `POST /api/nutrition/create`
- **Body**: 
  ```json
  {
    "user_id": 1,
    "goal": "Weight Loss",
    "description": "High protein, low carb diet",
    "meals": [
      {"meal_id": 1, "day_number": 1},
      {"meal_id": 2, "day_number": 1}
    ]
  }
  ```
- **Description**: Creates a new diet plan and associates meals with it for a specific user.

#### Get My Nutrition Plan
- **URL**: `GET /api/nutrition/user`
- **Description**: Returns the latest nutrition plan and its associated meals for the authenticated user.

#### Get Diet Plan Meals
- **URL**: `GET /api/nutrition/diet-meals?plan_id=1`
- **Description**: Returns all meals associated with a specific diet plan ID.

#### Get All Meals
- **URL**: `GET /api/meals`
- **Description**: Returns a list of all available meals in the system.

#### Add Meal
- **URL**: `POST /api/meals/create`
- **Body**: `{"name": "Oatmeal", "preparation_steps": "Boil oats in milk", "calories": 300, "serving_size": 200, "meal_type": "Breakfast"}`

### 🏋️‍♂️ Training Plans

#### Get My Training Plan
- **URL**: `GET /api/training/user`
- **Description**: Returns the latest training plan and its associated workout exercises for the authenticated user.

#### Get Workout Exercises
- **URL**: `GET /api/training/workout-exercises?plan_id=1`
- **Description**: Returns all workout exercises associated with a specific training plan ID.

### 💳 Payments & Transactions

#### Deposit Funds (Top-up)
- **URL**: `POST /api/payments/deposit`
- **Body**: `{"amount": 50.00}`
- **Description**: Adds funds to the logged-in user's account balance.

#### Get Transaction History
- **URL**: `GET /api/payments/history`
- **Description**: Returns the user's current balance and a detailed list of all past transactions (deposits, bookings, purchases).

### 📊 Dashboards & Analytics

#### Admin Dashboard
- **URL**: `GET /api/admin/dashboard`
- **Description**: (Admin Only) Returns key performance indicators: Total users, active subscriptions, total revenue (breakdown by category), equipment maintenance stats, and busiest gym hours.

#### Specialist Dashboard
- **URL**: `GET /api/specialists/dashboard`
- **Description**: (Specialist Only) Returns performance stats for Trainers (Earnings, Clients, Upcoming Sessions) and Nutritionists (Plans created, Active clients).

---

### Running Development Server
Run this from the project root:
```bash
php -S localhost:8000
```
Accessible at: `http://localhost:8000/api/...`

### Add to git
```bash
git add .
git commit -m "[your message]"
git push
```