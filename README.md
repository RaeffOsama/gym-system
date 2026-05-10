# Gym Management REST API

Native PHP REST API · MySQL · Session-based auth · No framework

**Base URL:** `https://test.oralpharm.com`

All requests and responses use **JSON**.  
Protected endpoints require a valid session — log in first and keep the cookie.

---

## Roles

| Role | Description |
|------|-------------|
| `user` | Regular gym member |
| `trainer` | Fitness trainer (specialist) |
| `nutritionist` | Diet specialist |
| `admin` | Full system access |

---

## Test Accounts — 4 per Role

> All passwords are **`123`**

### Users
| ID | Name | Email | Balance |
|----|------|-------|---------|
| 16 | Alex Carter | user1@test.com | 100.00 |
| 17 | Mia Torres | user2@test.com | 200.00 |
| 18 | Omar Nasser | user3@test.com | 50.00 |
| 19 | Layla Hassan | user4@test.com | 0.00 |

### Trainers
| ID | Name | Email |
|----|------|-------|
| 20 | Coach Mike | trainer1@test.com |
| 21 | Coach Sarah | trainer2@test.com |
| 22 | Coach James | trainer3@test.com |
| 23 | Coach Rania | trainer4@test.com |

### Nutritionists
| ID | Name | Email |
|----|------|-------|
| 24 | Dr. Amira Saad | nutri1@test.com |
| 25 | Dr. Youssef Karim | nutri2@test.com |
| 26 | Dr. Nada El-Sayed | nutri3@test.com |
| 27 | Dr. Khaled Omar | nutri4@test.com |

### Admins
| ID | Name | Email |
|----|------|-------|
| 28 | Admin One | admin1@test.com |
| 29 | Admin Two | admin2@test.com |
| 30 | Admin Three | admin3@test.com |
| 31 | Admin Four | admin4@test.com |

---

## Role Flows

### Admin
```
POST /api/auth/login
  → GET  /api/admin/dashboard

  # Manage catalogue (CRUD)
  → POST /api/equipment/create | update | delete
  → POST /api/exercises/create | update | delete
  → POST /api/subscriptions/create | update | delete

  # Manage specialists
  → POST /api/specialists/create          (create trainer or nutritionist account)
  → POST /api/trainers/update | delete
  → POST /api/nutritionists/update | delete
  → GET  /api/specialists                 (pick from this list to assign plans)

  # Assign plans (after user purchases subscription)
  → GET  /api/training/plans              (see all pending plans)
  → POST /api/training/plans/assign       (assign trainer → status: Planning)
  → GET  /api/nutrition/plans             (see all pending diet plans)
  → POST /api/nutrition/plans/assign      (assign nutritionist → status: Planning)

  # Direct plan creation (bypasses subscription flow)
  → POST /api/nutrition/create

  # User management
  → POST /api/auth/delete-user
```

### Trainer
```
POST /api/auth/login
  → GET  /api/specialists/dashboard
  → POST /api/auth/specialist-profile     (update bio, achievements, experience_years)

  # View & build assigned training plans
  → GET  /api/training/plans              (shows plans where status = Planning)
  → GET  /api/exercises                   (browse exercise library)
  → POST /api/training/plans/add-exercises  (sets plan → Active)

  # Optional: one-on-one session slots
  → POST /api/sessions/create
```

### Nutritionist
```
POST /api/auth/login
  → GET  /api/specialists/dashboard
  → POST /api/auth/specialist-profile     (update bio, achievements, experience_years)

  # View & build assigned diet plans
  → GET  /api/nutrition/plans             (shows plans where status = Planning)
  → GET  /api/meals                       (browse meal library)
  → POST /api/nutrition/plans/add-meals   (sets plan → Active)
```

### User
```
POST /api/auth/register
POST /api/auth/login
  → GET  /api/auth/profile
  → POST /api/auth/update

  # Fund wallet
  → POST /api/payments/deposit
  → GET  /api/payments/history

  # Browse & purchase subscription
  → GET  /api/subscriptions
  → POST /api/subscriptions/purchase      (auto-creates plan on purchase)
  → GET  /api/subscriptions/user          (my subscriptions + plan IDs & statuses)

  # Track plan progress: Pending Assign → Planning → Active
  → GET  /api/training/user
  → GET  /api/nutrition/user

  # Equipment booking
  → GET  /api/equipment
  → POST /api/bookings/create
  → GET  /api/bookings
  → POST /api/bookings/cancel
  → GET  /api/bookings/use                (active booking right now / check-in)

  # Browse specialists
  → GET  /api/trainers
  → GET  /api/nutritionists

  # One-on-one trainer session
  → POST /api/sessions/book
```

---

## Plan Status Lifecycle

```
User purchases subscription (diet / gym / both)
  └─► status: Pending Assign   (no specialist assigned yet)

Admin assigns specialist
  └─► status: Planning         (specialist can now build the plan)

Specialist adds meals / exercises
  └─► status: Active           (user can view their full plan)
```

---

## Equipment Status Lifecycle

```
Created                  → available
User books               → unavailable
Booking cancelled early  → available  (restored if no other future bookings remain)
GET /api/equipment       → auto-releases any equipment whose booking end_time has passed
```

---

## Subscription Plan Types

| ID | plan_type | Creates |
|----|-----------|---------|
| 1 | `diet` | diet_plan entry |
| 2 | `gym` | training_plan entry |
| 3 | `both` | diet_plan + training_plan entries |

---

## Endpoint Reference

---

### Auth

#### `POST /api/auth/register`
Auth: none

```json
{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "secret123",
  "phone": "01234567890",
  "age": 28,
  "gender": "male",
  "address": "Cairo, Egypt"
}
```
> `role_name` defaults to `"user"`. Do not expose role assignment to end users.

---

#### `POST /api/auth/login`
Auth: none

```json
{
  "email": "user1@test.com",
  "password": "123"
}
```
> Sets a `PHPSESSID` session cookie. All subsequent requests must send this cookie.

---

#### `POST /api/auth/logout`
Auth: any logged-in user  
Body: none

---

#### `GET /api/auth/profile`
Auth: any  
Body: none — returns `id, name, email, address, age, gender, role_name, phone, balance`

---

#### `POST /api/auth/update`
Auth: any

```json
{
  "name": "Updated Name",
  "phone": "09876543210",
  "address": "New Address",
  "age": 29,
  "gender": "male"
}
```
> All fields optional. Admin can also pass `"user_id"` to update another user's record.

---

#### `POST /api/auth/specialist-profile`
Auth: trainer / nutritionist

```json
{
  "experience_years": 5,
  "bio": "Certified strength and conditioning coach",
  "achievements": ["NSCA-CSCS", "5 years experience"]
}
```
> Admin can pass `"user_id"` to update any specialist's profile.

---

#### `POST /api/auth/delete-user`
Auth: admin

```json
{
  "user_id": 3
}
```

---

### Payments

#### `POST /api/payments/deposit`
Auth: any

```json
{
  "amount": 100.00
}
```
> Adds balance to the logged-in user's wallet and creates a transaction record.

---

#### `GET /api/payments/history`
Auth: any  
Body: none — returns `balance` + `transactions[]`

---

### Subscriptions

#### `GET /api/subscriptions`
Auth: none  
Body: none — returns all subscription plans

---

#### `POST /api/subscriptions/purchase`
Auth: any

```json
{
  "plan_id": 2,
  "goal": "Build muscle mass",
  "description": "Focus on upper body strength"
}
```
> Deducts price from balance, records transaction, creates a `training_plan` (gym), `diet_plan` (diet), or both (both). Returns plan ID(s) with `status: "Pending Assign"`.

---

#### `GET /api/subscriptions/user`
Auth: any  
Body: none — returns user's subscriptions with linked plan IDs and statuses

---

#### `POST /api/subscriptions/create`
Auth: admin

```json
{
  "name": "Premium Both",
  "description": "Gym + Diet combo package",
  "plan_type": "both",
  "price": 199.99
}
```
> `plan_type` must be one of: `diet`, `gym`, `both`

---

#### `POST /api/subscriptions/update`
Auth: admin

```json
{
  "id": 1,
  "name": "Diet Pro",
  "description": "Updated description",
  "plan_type": "diet",
  "price": 149.99
}
```
> Only include fields you want to change.

---

#### `POST /api/subscriptions/delete`
Auth: admin

```json
{
  "id": 4
}
```

---

### Equipment

#### `GET /api/equipment`
Auth: none  
Body: none — auto-releases equipment with past booking end_time before returning

---

#### `POST /api/equipment/create`
Auth: admin

```json
{
  "name": "Kettlebell Set",
  "description": "Cast iron kettlebells 8–32 kg",
  "booking_price": 3.00,
  "status": "available"
}
```

---

#### `POST /api/equipment/update`
Auth: admin

```json
{
  "id": 1,
  "name": "Updated Name",
  "description": "Updated description",
  "booking_price": 6.00,
  "status": "available"
}
```
> Only include fields you want to change.

---

#### `POST /api/equipment/delete`
Auth: admin

```json
{
  "id": 11
}
```

---

### Exercises

#### `GET /api/exercises`
Auth: none  
Body: none — returns exercises joined with equipment name

---

#### `POST /api/exercises/create`
Auth: admin

```json
{
  "name": "Barbell Row",
  "description": "Bent-over barbell row for back development",
  "muscle_name": "Back",
  "equipment_id": 2
}
```
> `equipment_id` is optional.

---

#### `POST /api/exercises/update`
Auth: admin

```json
{
  "id": 1,
  "name": "Updated Name",
  "description": "Updated description",
  "muscle_name": "Cardiovascular",
  "equipment_id": 5
}
```
> Only include fields you want to change.

---

#### `POST /api/exercises/delete`
Auth: admin

```json
{
  "id": 36
}
```

---

### Bookings

#### `GET /api/bookings`
Auth: any  
Body: none — returns the logged-in user's booking history

---

#### `POST /api/bookings/create`
Auth: any

```json
{
  "equipment_id": 1,
  "start_time": "2026-05-20 09:00:00",
  "end_time": "2026-05-20 10:00:00"
}
```
> Equipment must be `available`. Deducts booking price from balance, sets equipment to `unavailable`, records transaction.

---

#### `POST /api/bookings/cancel`
Auth: any

```json
{
  "booking_id": 1
}
```
> If `start_time` is in the future → full refund + transaction record. Equipment is set back to `available` if no other active bookings remain.

---

#### `GET /api/bookings/use`
Auth: any  
Body: none — returns the user's currently active booking (NOW between start_time and end_time), or 404 if none

---

### Specialists / Trainers / Nutritionists

#### `GET /api/specialists`
Auth: none  
Body: none — returns all users with role `trainer` or `nutritionist` including profiles

---

#### `POST /api/specialists/create`
Auth: admin

```json
{
  "name": "Ali Hassan",
  "email": "ali@gym.com",
  "password": "123",
  "role_name": "trainer",
  "experience_years": 3,
  "bio": "Certified personal trainer",
  "achievements": ["ACE-CPT", "3 years experience"]
}
```
> `role_name` must be `trainer` or `nutritionist`. Creates user + specialist_profile in one transaction.

---

#### `GET /api/trainers`
Auth: none  
Body: none

---

#### `POST /api/trainers/update`
Auth: admin (any trainer) or trainer (self only)

```json
{
  "user_id": 4,
  "name": "Ahmed Hassan",
  "phone": "01099999999",
  "experience_years": 6,
  "bio": "Updated bio text",
  "achievements": ["NSCA-CPT", "Olympic Lifting L2"]
}
```
> Trainers omit `user_id` to update themselves. All fields optional.

---

#### `POST /api/trainers/delete`
Auth: admin

```json
{
  "user_id": 4
}
```

---

#### `GET /api/nutritionists`
Auth: none  
Body: none

---

#### `POST /api/nutritionists/update`
Auth: admin (any) or nutritionist (self only)

```json
{
  "user_id": 10,
  "name": "Sara Ali",
  "phone": "01088888888",
  "experience_years": 4,
  "bio": "Specialist in sports nutrition",
  "achievements": ["RD License", "Sports Nutrition Cert"]
}
```

---

#### `POST /api/nutritionists/delete`
Auth: admin

```json
{
  "user_id": 10
}
```

---

#### `GET /api/specialists/dashboard`
Auth: trainer or nutritionist  
Body: none — returns role-specific stats (earnings, active clients, upcoming sessions/plans)

---

### Trainer Sessions (One-on-One)

> These are independent of subscription plans. A trainer opens available time slots; users book them directly.

#### `POST /api/sessions/create`
Auth: trainer

```json
{
  "start_time": "2026-05-20 10:00:00",
  "end_time": "2026-05-20 11:00:00",
  "price": 50.00
}
```

---

#### `POST /api/sessions/book`
Auth: any

```json
{
  "session_id": 1
}
```
> Deducts price from balance, records transaction, marks session as `booked`.

---

### Training Plans

#### `GET /api/training/plans`
Auth: any (role-filtered)  
Body: none

> - **admin** → all plans  
> - **trainer** → only plans assigned to them  
> - **user** → only their own plans

---

#### `POST /api/training/plans/assign`
Auth: admin

```json
{
  "training_plan_id": 1,
  "trainer_id": 20
}
```
> Sets plan `status` to `Planning`.

---

#### `POST /api/training/plans/add-exercises`
Auth: trainer (assigned to plan) or admin

```json
{
  "training_plan_id": 1,
  "exercises": [
    {
      "exercise_id": 7,
      "day_number": 1,
      "sort_order": 1,
      "sets": 4,
      "reps": 8,
      "rest_time": 90
    },
    {
      "exercise_id": 4,
      "day_number": 1,
      "sort_order": 2,
      "sets": 4,
      "reps": 10,
      "rest_time": 60
    },
    {
      "exercise_id": 22,
      "day_number": 2,
      "sort_order": 1,
      "sets": 3,
      "reps": 12,
      "rest_time": 45
    }
  ]
}
```
> Sets plan `status` to `Active`. `sort_order`, `sets`, `reps`, `rest_time` are optional (defaults: 1, 3, 10, 60).

---

#### `GET /api/training/user`
Auth: any  
Body: none — returns user's most recent training plan + full exercise list

---

#### `GET /api/training/workout-exercises?plan_id=1`
Auth: any  
Body: none — returns all exercises for a specific plan ID (query param)

---

### Diet / Nutrition Plans

#### `GET /api/nutrition/plans`
Auth: any (role-filtered)  
Body: none

> - **admin** → all plans  
> - **nutritionist** → only plans assigned to them  
> - **user** → only their own plans

---

#### `POST /api/nutrition/plans/assign`
Auth: admin

```json
{
  "diet_plan_id": 1,
  "nutritionist_id": 24
}
```
> Sets plan `status` to `Planning`.

---

#### `POST /api/nutrition/plans/add-meals`
Auth: nutritionist (assigned to plan) or admin

```json
{
  "diet_plan_id": 1,
  "meals": [
    { "meal_id": 1,  "day_number": 1 },
    { "meal_id": 6,  "day_number": 1 },
    { "meal_id": 11, "day_number": 1 },
    { "meal_id": 2,  "day_number": 2 },
    { "meal_id": 7,  "day_number": 2 },
    { "meal_id": 16, "day_number": 2 }
  ]
}
```
> Sets plan `status` to `Active`.

---

#### `POST /api/nutrition/create`
Auth: admin — creates a complete diet plan directly (bypasses subscription flow)

```json
{
  "user_id": 16,
  "nutritionist_id": 24,
  "goal": "Muscle gain",
  "description": "High protein focus",
  "meals": [
    { "meal_id": 1, "day_number": 1 },
    { "meal_id": 6, "day_number": 1 }
  ]
}
```
> `nutritionist_id` and `meals` are optional. Status is derived from what is provided.

---

#### `GET /api/nutrition/user`
Auth: any  
Body: none — returns user's most recent diet plan + full meal list

---

#### `GET /api/nutrition/diet-meals?plan_id=1`
Auth: any  
Body: none — returns all meals for a specific plan ID (query param)

---

#### `GET /api/meals`
Auth: any (logged in)  
Body: none — returns all meals in the library

---

#### `POST /api/meals/create`
Auth: any (logged in)

```json
{
  "name": "Protein Pancakes",
  "preparation_steps": "Mix oats, egg whites, banana. Cook on medium heat.",
  "calories": 320,
  "serving_size": 200,
  "meal_type": "breakfast"
}
```
> `meal_type` values: `breakfast`, `lunch`, `dinner`, `snack`

---

### Admin Dashboard

#### `GET /api/admin/dashboard`
Auth: admin  
Body: none — returns revenue, active subscriptions, equipment stats, busiest hours, recent transactions

---

## Files to Note

| File | Note |
|------|------|
| [routes/sessions/create_session.php](routes/sessions/create_session.php) | Trainer creates one-on-one session slots — separate from subscription plans |
| [routes/sessions/book_session.php](routes/sessions/book_session.php) | User books a trainer session slot |
| [routes/training/get_workout_exercises.php](routes/training/get_workout_exercises.php) | Returns exercises for a specific `?plan_id=` — useful for plan detail view |
| [routes/nutrition/get_diet_meals.php](routes/nutrition/get_diet_meals.php) | Returns meals for a specific `?plan_id=` — useful for plan detail view |
| [routes/nutrition/create_diet_plan.php](routes/nutrition/create_diet_plan.php) | Admin shortcut: creates plan + meals in one shot without subscription |
