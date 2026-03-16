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
README.md
```

## Setup Instructions

1. **Document Root**: Configure your web server (Apache/Nginx/XAMPP) document root to point to the `public/` folder, or access it via `http://localhost/gymproject/public/api/...`.
2. **Database Configuration**:
   * Copy `config/config.example.php` to `config/config.php`
   * Edit `config/config.php` with your MySQL database connection credentials.
3. **Database Setup**:
   Import your SQL schema provided in the request or use the `CREATE TABLE` statements for `users`, `subscription_plans`, etc.

## API Endpoints

Routing is handled in `public/index.php`.

### 🔐 Authentication

#### Register User
- **URL**: `POST /api/auth/register`
- **Body**: `{"name": "...", "email": "...", "password": "...", "address": "...", "age": 25, "gender": "...", "role_name": "user", "phone": "..."}`

#### Login User
- **URL**: `POST /api/auth/login`
- **Body**: `{"email": "...", "password": "..."}`

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

### 🏋️ Equipment

#### Get All Equipment
- **URL**: `GET /api/equipment`
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

---

### Running Development Server
Run this from the project root:
```bash
php -S localhost:8000
```
Accessible at: `http://localhost:8000/api/...`
