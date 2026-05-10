# Gym Management REST API

Native PHP REST API · MySQL · Session-based auth · No framework

Base URL (local): `http://localhost/api/#gym/public`

All requests and responses use JSON. Protected endpoints require a valid session (log in first).

---

## Roles

| Role | Description |
|------|-------------|
| `user` | Regular gym member |
| `trainer` | Fitness trainer (specialist) |
| `nutritionist` | Diet specialist |
| `admin` | Full system access |

---

## Seed Accounts

| Role | Email | Password | ID | Balance |
|------|-------|----------|----|---------|
| admin | admin@gym.com | 123456 | 7 | — |
| admin | moadmin@as.com | 123456 | 9 | 50.00 |
| trainer | ahmed@gym.com | 123456 | 4 | — |
| nutritionist | sara@mi.com | 123456 | 10 | — |
| user | jane@example.com | 123456 | 1 | — |

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

  # Admin can also create plans directly without subscription flow
  → POST /api/nutrition/create            (admin shortcut: create diet plan)

  # User management
  → POST /api/auth/delete-user
```

### Trainer

```
POST /api/auth/login
  → GET  /api/specialists/dashboard       (earnings, upcoming sessions)
  → POST /api/auth/specialist-profile     (update bio, achievements, experience_years)

  # View assigned training plans (status: Planning)
  → GET  /api/training/plans

  # Build the plan (sets plan → Active)
  → GET  /api/exercises                   (browse exercise library)
  → POST /api/training/plans/add-exercises

  # One-on-one session slots (optional, separate from subscription plans)
  → POST /api/sessions/create             (create available time slot)
```

### Nutritionist

```
POST /api/auth/login
  → GET  /api/specialists/dashboard       (active clients, recent plans)
  → POST /api/auth/specialist-profile     (update bio, achievements, experience_years)

  # View assigned diet plans (status: Planning)
  → GET  /api/nutrition/plans

  # Build the plan (sets plan → Active)
  → GET  /api/meals                       (browse meal library)
  → POST /api/nutrition/plans/add-meals
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
  → GET  /api/subscriptions               (public)
  → POST /api/subscriptions/purchase      (goal & description recommended for plan creation)
  → GET  /api/subscriptions/user          (my subscriptions + plan IDs & statuses)

  # My plans (check status: Pending Assign → Planning → Active)
  → GET  /api/training/user               (my latest training plan + exercises)
  → GET  /api/nutrition/user              (my latest diet plan + meals)

  # Equipment booking
  → GET  /api/equipment                   (status auto-refreshes from bookings)
  → POST /api/bookings/create             (deducts balance, sets equipment unavailable)
  → GET  /api/bookings                    (my booking history)
  → POST /api/bookings/cancel             (refund if before start time, releases equipment)
  → GET  /api/bookings/use                (check if I have an active booking right now)

  # Browse specialists
  → GET  /api/trainers
  → GET  /api/nutritionists

  # Book one-on-one trainer session (separate from subscription plan)
  → POST /api/sessions/book
```

---

## Complete Endpoint Reference

### Auth

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/auth/register` | — | Register a new user |
| POST | `/api/auth/login` | — | Login, receives session cookie |
| POST | `/api/auth/logout` | any | Destroy session |
| GET | `/api/auth/profile` | any | My profile (role, balance, all fields) |
| POST | `/api/auth/update` | any | Update name, address, phone, age, gender |
| POST | `/api/auth/specialist-profile` | trainer / nutritionist | Update bio, achievements, experience_years |
| POST | `/api/auth/delete-user` | admin | Delete a user account |

### Payments

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/payments/deposit` | any | Add balance (dummy payment gateway) |
| GET | `/api/payments/history` | any | Transaction history + current balance |

### Subscriptions

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/subscriptions` | — | List all subscription plans |
| POST | `/api/subscriptions/purchase` | any | Purchase plan; auto-creates diet/training plan |
| GET | `/api/subscriptions/user` | any | My subscriptions + linked plan IDs & statuses |
| POST | `/api/subscriptions/create` | admin | Create subscription plan |
| POST | `/api/subscriptions/update` | admin | Update subscription plan |
| POST | `/api/subscriptions/delete` | admin | Delete subscription plan |

### Equipment

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/equipment` | — | List equipment; auto-releases expired bookings |
| POST | `/api/equipment/create` | admin | Add equipment |
| POST | `/api/equipment/update` | admin | Update equipment |
| POST | `/api/equipment/delete` | admin | Delete equipment |

### Exercises

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/exercises` | — | List all exercises with equipment name |
| POST | `/api/exercises/create` | admin | Add exercise |
| POST | `/api/exercises/update` | admin | Update exercise |
| POST | `/api/exercises/delete` | admin | Delete exercise |

### Bookings

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/bookings` | any | My booking history |
| POST | `/api/bookings/create` | any | Book equipment (checks available, deducts balance) |
| POST | `/api/bookings/cancel` | any | Cancel booking (refund if not started, releases equipment) |
| GET | `/api/bookings/use` | any | Active booking right now (for check-in) |

### Specialists / Trainers / Nutritionists

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/specialists` | — | All trainers + nutritionists |
| POST | `/api/specialists/create` | admin | Create trainer or nutritionist account |
| GET | `/api/trainers` | — | Trainers with profiles |
| POST | `/api/trainers/update` | admin / trainer (self) | Update trainer info |
| POST | `/api/trainers/delete` | admin | Delete trainer |
| GET | `/api/nutritionists` | — | Nutritionists with profiles |
| POST | `/api/nutritionists/update` | admin / nutritionist (self) | Update nutritionist info |
| POST | `/api/nutritionists/delete` | admin | Delete nutritionist |
| GET | `/api/specialists/dashboard` | trainer / nutritionist | Personal dashboard stats |

### Trainer Sessions (One-on-One)

> Separate from subscription-based training plans.
> A trainer creates available slots; users book them directly.

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| POST | `/api/sessions/create` | trainer | Create available time slot |
| POST | `/api/sessions/book` | any | Book a session slot (deducts balance) |

### Training Plans (Subscription Flow)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/training/plans` | admin / trainer / user | Plans filtered by role |
| POST | `/api/training/plans/assign` | admin | Assign trainer → status: Planning |
| POST | `/api/training/plans/add-exercises` | trainer / admin | Add exercises → status: Active |
| GET | `/api/training/user` | any | My latest training plan + full exercise list |
| GET | `/api/training/workout-exercises` | any | Exercises for a specific plan (`?plan_id=`) |

### Diet / Nutrition Plans (Subscription Flow)

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/nutrition/plans` | admin / nutritionist / user | Plans filtered by role |
| POST | `/api/nutrition/plans/assign` | admin | Assign nutritionist → status: Planning |
| POST | `/api/nutrition/plans/add-meals` | nutritionist / admin | Add meals → status: Active |
| POST | `/api/nutrition/create` | admin | Direct plan creation shortcut |
| GET | `/api/nutrition/user` | any | My latest diet plan + full meal list |
| GET | `/api/nutrition/diet-meals` | any | Meals for a specific plan (`?plan_id=`) |
| GET | `/api/meals` | any | Browse all meals |
| POST | `/api/meals/create` | any (auth) | Add a meal to the library |

### Admin Dashboard

| Method | Endpoint | Auth | Description |
|--------|----------|------|-------------|
| GET | `/api/admin/dashboard` | admin | Revenue, active subscriptions, equipment stats, busiest hours |

---

## Plan Status Lifecycle

```
User purchases subscription (diet / gym / both)
  └─► plan created with status: Pending Assign

Admin assigns specialist
  └─► status: Planning

Specialist adds meals / exercises
  └─► status: Active
```

---

## Equipment Status Lifecycle

```
Equipment created → available
User books → unavailable
  Booking cancelled before start → available (if no other active bookings)
GET /api/equipment called → auto-releases any equipment whose booking end_time has passed
```

---

## Subscription Plan Types

| ID | Name | plan_type | Creates |
|----|------|-----------|---------|
| 1 | Diet Plan | `diet` | diet_plan entry |
| 2 | Gym Plan | `gym` | training_plan entry |
| 3 | Both | `both` | diet_plan + training_plan entries |

---

## curl Test Cases

All curl commands save and send the session cookie via `-c cookie.txt -b cookie.txt`.

```bash
# --- AUTH ---

# Register
curl -s -X POST http://localhost/api/#gym/public/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@test.com","password":"secret","phone":"01234567890","age":28,"gender":"male","address":"Cairo"}'

# Login (admin)
curl -s -c cookie.txt -X POST http://localhost/api/#gym/public/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@gym.com","password":"123456"}'

# Profile
curl -s -b cookie.txt http://localhost/api/#gym/public/api/auth/profile

# Update profile
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/auth/update \
  -H "Content-Type: application/json" \
  -d '{"name":"John Updated","phone":"09876543210"}'

# Update specialist profile (login as trainer first)
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/auth/specialist-profile \
  -H "Content-Type: application/json" \
  -d '{"experience_years":5,"bio":"Certified strength coach","achievements":["NSCA-CPT","5 years experience"]}'

# Logout
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/auth/logout

# Delete user (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/auth/delete-user \
  -H "Content-Type: application/json" \
  -d '{"user_id":2}'


# --- PAYMENTS ---

# Deposit balance
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/payments/deposit \
  -H "Content-Type: application/json" \
  -d '{"amount":100}'

# Transaction history
curl -s -b cookie.txt http://localhost/api/#gym/public/api/payments/history


# --- SUBSCRIPTIONS ---

# List plans (public)
curl -s http://localhost/api/#gym/public/api/subscriptions

# My subscriptions
curl -s -b cookie.txt http://localhost/api/#gym/public/api/subscriptions/user

# Purchase gym plan (id=2)
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/subscriptions/purchase \
  -H "Content-Type: application/json" \
  -d '{"plan_id":2,"goal":"Build muscle mass","description":"Focus on upper body strength"}'

# Purchase diet plan (id=1)
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/subscriptions/purchase \
  -H "Content-Type: application/json" \
  -d '{"plan_id":1,"goal":"Lose 5kg in 2 months","description":"Low carb approach"}'

# Create subscription plan (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/subscriptions/create \
  -H "Content-Type: application/json" \
  -d '{"name":"Premium Both","description":"Gym + Diet combo","plan_type":"both","price":199.99}'

# Update subscription plan (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/subscriptions/update \
  -H "Content-Type: application/json" \
  -d '{"id":1,"price":149.99}'

# Delete subscription plan (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/subscriptions/delete \
  -H "Content-Type: application/json" \
  -d '{"id":4}'


# --- EQUIPMENT ---

# List equipment (public, auto-refreshes status)
curl -s http://localhost/api/#gym/public/api/equipment

# Create equipment (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/equipment/create \
  -H "Content-Type: application/json" \
  -d '{"name":"Kettlebell Set","description":"Cast iron kettlebells 8-32kg","booking_price":3.00,"status":"available"}'

# Update equipment (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/equipment/update \
  -H "Content-Type: application/json" \
  -d '{"id":1,"booking_price":6.00}'

# Delete equipment (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/equipment/delete \
  -H "Content-Type: application/json" \
  -d '{"id":11}'


# --- EXERCISES ---

# List exercises (public)
curl -s http://localhost/api/#gym/public/api/exercises

# Create exercise (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/exercises/create \
  -H "Content-Type: application/json" \
  -d '{"name":"Barbell Row","description":"Bent-over barbell row","muscle_name":"Back","equipment_id":2}'

# Update exercise (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/exercises/update \
  -H "Content-Type: application/json" \
  -d '{"id":1,"muscle_name":"Cardiovascular"}'

# Delete exercise (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/exercises/delete \
  -H "Content-Type: application/json" \
  -d '{"id":36}'


# --- BOOKINGS ---

# Book equipment (user must be logged in, equipment must be available)
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/bookings/create \
  -H "Content-Type: application/json" \
  -d '{"equipment_id":1,"start_time":"2026-05-12 09:00:00","end_time":"2026-05-12 10:00:00"}'

# My bookings
curl -s -b cookie.txt http://localhost/api/#gym/public/api/bookings

# Cancel booking
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/bookings/cancel \
  -H "Content-Type: application/json" \
  -d '{"booking_id":1}'

# Active booking right now (check-in)
curl -s -b cookie.txt http://localhost/api/#gym/public/api/bookings/use


# --- SPECIALISTS ---

# List all specialists (public)
curl -s http://localhost/api/#gym/public/api/specialists

# List trainers (public)
curl -s http://localhost/api/#gym/public/api/trainers

# List nutritionists (public)
curl -s http://localhost/api/#gym/public/api/nutritionists

# Create specialist account (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/specialists/create \
  -H "Content-Type: application/json" \
  -d '{"name":"Ali Hassan","email":"ali@gym.com","password":"123456","role_name":"trainer","experience_years":3,"bio":"Certified personal trainer","achievements":["ACE-CPT"]}'

# Update trainer (admin or trainer self)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/trainers/update \
  -H "Content-Type: application/json" \
  -d '{"user_id":4,"experience_years":6}'

# Delete trainer (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/trainers/delete \
  -H "Content-Type: application/json" \
  -d '{"user_id":4}'

# Update nutritionist (admin or nutritionist self)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/nutritionists/update \
  -H "Content-Type: application/json" \
  -d '{"user_id":10,"experience_years":4}'

# Delete nutritionist (admin)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/nutritionists/delete \
  -H "Content-Type: application/json" \
  -d '{"user_id":10}'

# Specialist dashboard (login as trainer or nutritionist)
curl -s -b cookie.txt http://localhost/api/#gym/public/api/specialists/dashboard


# --- TRAINER SESSIONS (one-on-one) ---

# Create session slot (login as trainer)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/sessions/create \
  -H "Content-Type: application/json" \
  -d '{"start_time":"2026-05-13 10:00:00","end_time":"2026-05-13 11:00:00","price":50.00}'

# Book session slot (login as user)
curl -s -X POST -b cookie.txt -c cookie.txt http://localhost/api/#gym/public/api/sessions/book \
  -H "Content-Type: application/json" \
  -d '{"session_id":1}'


# --- TRAINING PLANS ---

# View training plans (role-filtered)
curl -s -b cookie.txt http://localhost/api/#gym/public/api/training/plans

# Admin assigns trainer to plan
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/training/plans/assign \
  -H "Content-Type: application/json" \
  -d '{"training_plan_id":1,"trainer_id":4}'

# Trainer adds exercises to plan (login as trainer)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/training/plans/add-exercises \
  -H "Content-Type: application/json" \
  -d '{
    "training_plan_id": 1,
    "exercises": [
      {"exercise_id":7,"day_number":1,"sort_order":1,"sets":4,"reps":8,"rest_time":90},
      {"exercise_id":4,"day_number":1,"sort_order":2,"sets":4,"reps":10,"rest_time":60},
      {"exercise_id":22,"day_number":2,"sort_order":1,"sets":3,"reps":12,"rest_time":45}
    ]
  }'

# My latest training plan
curl -s -b cookie.txt http://localhost/api/#gym/public/api/training/user

# Exercises for a specific plan
curl -s -b cookie.txt "http://localhost/api/#gym/public/api/training/workout-exercises?plan_id=1"


# --- DIET / NUTRITION PLANS ---

# View diet plans (role-filtered)
curl -s -b cookie.txt http://localhost/api/#gym/public/api/nutrition/plans

# Admin assigns nutritionist to plan
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/nutrition/plans/assign \
  -H "Content-Type: application/json" \
  -d '{"diet_plan_id":1,"nutritionist_id":10}'

# Nutritionist adds meals to plan (login as nutritionist)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/nutrition/plans/add-meals \
  -H "Content-Type: application/json" \
  -d '{
    "diet_plan_id": 1,
    "meals": [
      {"meal_id":1,"day_number":1},
      {"meal_id":6,"day_number":1},
      {"meal_id":11,"day_number":1},
      {"meal_id":2,"day_number":2},
      {"meal_id":7,"day_number":2},
      {"meal_id":16,"day_number":2}
    ]
  }'

# My latest diet plan
curl -s -b cookie.txt http://localhost/api/#gym/public/api/nutrition/user

# Meals for a specific diet plan
curl -s -b cookie.txt "http://localhost/api/#gym/public/api/nutrition/diet-meals?plan_id=1"

# Browse all meals
curl -s -b cookie.txt http://localhost/api/#gym/public/api/meals

# Admin creates diet plan directly (shortcut, no subscription needed)
curl -s -X POST -b cookie.txt http://localhost/api/#gym/public/api/nutrition/create \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"nutritionist_id":10,"goal":"Muscle gain","description":"High protein focus","meals":[{"meal_id":1,"day_number":1}]}'


# --- ADMIN DASHBOARD ---

curl -s -b cookie.txt http://localhost/api/#gym/public/api/admin/dashboard
```

---

## Files to Note

| File | Status | Note |
|------|--------|------|
| [routes/sessions/create_session.php](routes/sessions/create_session.php) | Active | Trainer creates one-on-one session slots — separate from subscription plans |
| [routes/sessions/book_session.php](routes/sessions/book_session.php) | Active | User books a trainer session slot |
| [routes/training/get_workout_exercises.php](routes/training/get_workout_exercises.php) | Active | Returns exercises for a specific `?plan_id=` — useful for plan detail view |
| [routes/nutrition/get_diet_meals.php](routes/nutrition/get_diet_meals.php) | Active | Returns meals for a specific `?plan_id=` — useful for plan detail view |
| [routes/specialists/create_specialist.php](routes/specialists/create_specialist.php) | Active | Unified create for trainer or nutritionist (admin only) |
| [routes/nutrition/create_diet_plan.php](routes/nutrition/create_diet_plan.php) | Active (admin shortcut) | Bypasses subscription flow; creates plan + meals in one shot |
