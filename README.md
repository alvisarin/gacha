# 🎰 Gacha Event System

A production-ready Gacha Event System built with **Laravel 10** (Backend) and **Vue 3 + Tailwind CSS** (Frontend SPA). Features atomic transactions for race condition prevention, weighted random sampling algorithm, and role-based access control.

![Laravel](https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat-square&logo=laravel)
![Vue.js](https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat-square&logo=vue.js)
![Tailwind CSS](https://img.shields.io/badge/Tailwind-3.x-38B2AC?style=flat-square&logo=tailwind-css)

## 📋 Table of Contents

- [Features](#-features)
- [Tech Stack](#-tech-stack)
- [Installation Guide](#-installation-guide)
- [Database Schema](#-database-schema)
- [API Documentation](#-api-documentation)
- [Usage](#-usage)
- [Security Features](#-security-features)

---

## ✨ Features

### User Features
- **Authentication**: Register (with 500 free coins), Login, Logout
- **Gacha Rolling**: Roll for rewards with weighted random selection
- **History**: View personal gacha history with pagination
- **Statistics**: Track total rolls, coins spent, and reward breakdown

### Admin Features
- **Event Management**: CRUD operations for gacha events
- **Reward Management**: CRUD with drop rate validation (must total 100%)
- **Global Logs**: View all user gacha activities in real-time

### Technical Features
- **Atomic Transactions**: Database transactions with row-level locking (`lockForUpdate()`)
- **Race Condition Prevention**: Prevents duplicate coin deduction on rapid clicks
- **Weighted Random Sampling**: Cumulative distribution algorithm for fair rewards
- **SPA Architecture**: Vue 3 with Vue Router and Pinia state management

---

## 🛠 Tech Stack

| Layer | Technology |
|-------|------------|
| Backend | Laravel 10, PHP 8.1+ |
| Authentication | Laravel Sanctum (Token-based) |
| Frontend | Vue 3 (Composition API) |
| Styling | Tailwind CSS 3 |
| State Management | Pinia |
| Build Tool | Vite |
| Database | MySQL 8.0+ |

---

## 🚀 Installation Guide

### Prerequisites
- PHP 8.1 or higher
- Composer 2.x
- Node.js 18+ and npm
- MySQL 8.0+

### Standard Installation

```bash
# 1. Clone or navigate to project directory
cd c:\xampp\htdocs\gacha

# 2. Install PHP dependencies
composer install

# 3. Copy environment file
copy .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Configure your database in .env
# DB_DATABASE=gacha_system
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Create the database
mysql -u root -e "CREATE DATABASE gacha_system"

# 7. Run migrations and seeders
php artisan migrate --seed

# 8. Install Node dependencies
npm install

# 9. Build frontend assets
npm run build

# 10. Start the development server
php artisan serve
```

### Docker / Laravel Sail Installation

```bash
# 1. Install dependencies (without Docker)
composer install

# 2. Copy environment file
cp .env.example .env

# 3. Start Laravel Sail
./vendor/bin/sail up -d

# 4. Generate key
./vendor/bin/sail artisan key:generate

# 5. Run migrations and seeders
./vendor/bin/sail artisan migrate --seed

# 6. Install and build frontend
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

### Demo Accounts

After seeding, you can use these accounts:

| Role | Email | Password | Coins |
|------|-------|----------|-------|
| Admin | admin@gacha.com | password | 10,000 |
| User | user@gacha.com | password | 500 |

---

## 📊 Database Schema

### Users Table
```sql
CREATE TABLE users (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    coins INT DEFAULT 500,
    is_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Events Table
```sql
CREATE TABLE events (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### Rewards Table
```sql
CREATE TABLE rewards (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    event_id BIGINT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    name VARCHAR(255) NOT NULL,
    drop_rate DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
-- Note: Total drop_rate per event MUST equal 100%
```

### Gacha Logs Table
```sql
CREATE TABLE gacha_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    event_id BIGINT NOT NULL REFERENCES events(id) ON DELETE CASCADE,
    reward_id BIGINT NOT NULL REFERENCES rewards(id) ON DELETE CASCADE,
    coins_spent INT DEFAULT 10,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Entity Relationship Diagram

```
┌─────────────┐       ┌─────────────┐       ┌─────────────┐
│   Users     │       │   Events    │       │   Rewards   │
├─────────────┤       ├─────────────┤       ├─────────────┤
│ id          │       │ id          │◄──────│ event_id    │
│ name        │       │ title       │       │ id          │
│ email       │       │ is_active   │       │ name        │
│ coins       │       │ created_at  │       │ drop_rate   │
│ is_admin    │       └─────────────┘       └─────────────┘
└──────┬──────┘               │                    │
       │                      │                    │
       │       ┌──────────────┴────────────────────┘
       │       │
       ▼       ▼
┌─────────────────────┐
│    Gacha Logs       │
├─────────────────────┤
│ id                  │
│ user_id (FK)        │
│ event_id (FK)       │
│ reward_id (FK)      │
│ coins_spent         │
│ created_at          │
└─────────────────────┘
```

---

## 📡 API Documentation

### Base URL
```
http://localhost:8000/api
```

### Authentication Headers
All protected routes require:
```
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

---

### Authentication Endpoints

#### Register
```http
POST /api/register
```

**Request Body:**
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

**Response (201 Created):**
```json
{
    "message": "Registration successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "coins": 500,
        "is_admin": false
    },
    "token": "1|abc123..."
}
```

#### Login
```http
POST /api/login
```

**Request Body:**
```json
{
    "email": "john@example.com",
    "password": "password123"
}
```

**Response (200 OK):**
```json
{
    "message": "Login successful",
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "coins": 500,
        "is_admin": false
    },
    "token": "2|xyz789..."
}
```

#### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "message": "Logged out successfully"
}
```

#### Get Current User
```http
GET /api/user
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "user": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "coins": 490,
        "is_admin": false
    }
}
```

---

### Gacha Endpoints (User)

#### Get Active Events
```http
GET /api/gacha/events
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "events": [
        {
            "id": 1,
            "title": "Summer Festival Gacha",
            "is_active": true,
            "rewards": [
                { "id": 1, "name": "Legendary Dragon", "drop_rate": "1.00" },
                { "id": 2, "name": "Rare Phoenix", "drop_rate": "19.00" },
                { "id": 3, "name": "Common Slime", "drop_rate": "80.00" }
            ]
        }
    ]
}
```

#### Roll Gacha
```http
POST /api/gacha/roll
Authorization: Bearer {token}
```

**Request Body:**
```json
{
    "event_id": 1
}
```

**Response (200 OK):**
```json
{
    "message": "Congratulations!",
    "reward": {
        "id": 3,
        "name": "Common Slime",
        "drop_rate": "80.00"
    },
    "remaining_coins": 490,
    "roll_id": 1
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
    "message": "Insufficient coins. You need 10 coins to roll.",
    "errors": {
        "coins": ["Insufficient coins. You need 10 coins to roll."]
    }
}
```

#### Get Personal History
```http
GET /api/gacha/history?page=1&per_page=15
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "data": [
        {
            "id": 1,
            "user_id": 1,
            "event_id": 1,
            "reward_id": 3,
            "coins_spent": 10,
            "created_at": "2024-01-15T10:30:00.000000Z",
            "event": { "id": 1, "title": "Summer Festival Gacha" },
            "reward": { "id": 3, "name": "Common Slime", "drop_rate": "80.00" }
        }
    ],
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 1
}
```

#### Get Personal Stats
```http
GET /api/gacha/stats
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
    "total_rolls": 50,
    "total_coins_spent": 500,
    "current_coins": 0,
    "reward_breakdown": [
        {
            "reward_id": 3,
            "count": 40,
            "reward": { "id": 3, "name": "Common Slime", "drop_rate": "80.00" }
        },
        {
            "reward_id": 2,
            "count": 9,
            "reward": { "id": 2, "name": "Rare Phoenix", "drop_rate": "19.00" }
        },
        {
            "reward_id": 1,
            "count": 1,
            "reward": { "id": 1, "name": "Legendary Dragon", "drop_rate": "1.00" }
        }
    ]
}
```

---

### Admin Endpoints

> All admin endpoints require `is_admin: true` on the authenticated user.

#### Events CRUD

**List Events:**
```http
GET /api/admin/events
```

**Create Event:**
```http
POST /api/admin/events
Content-Type: application/json

{
    "title": "Winter Wonderland Gacha",
    "is_active": true
}
```

**Update Event:**
```http
PUT /api/admin/events/{id}
Content-Type: application/json

{
    "title": "Updated Title",
    "is_active": false
}
```

**Delete Event:**
```http
DELETE /api/admin/events/{id}
```

#### Rewards CRUD

**List Rewards for Event:**
```http
GET /api/admin/events/{event_id}/rewards
```

**Create Reward:**
```http
POST /api/admin/events/{event_id}/rewards
Content-Type: application/json

{
    "name": "Ultra Rare Item",
    "drop_rate": 5.00
}
```

**Update Reward:**
```http
PUT /api/admin/events/{event_id}/rewards/{reward_id}
Content-Type: application/json

{
    "name": "Super Rare Item",
    "drop_rate": 10.00
}
```

**Delete Reward:**
```http
DELETE /api/admin/events/{event_id}/rewards/{reward_id}
```

**Validate Drop Rate:**
```http
GET /api/admin/events/{event_id}/validate-drop-rate
```

**Response:**
```json
{
    "total_drop_rate": 100.00,
    "is_valid": true,
    "message": "Drop rates are valid (total = 100%)"
}
```

#### Global Gacha Logs

**List All Logs:**
```http
GET /api/admin/gacha-logs?page=1&per_page=20&user_id=1&event_id=1
```

**Get Statistics:**
```http
GET /api/admin/gacha-logs/stats
```

**Real-time Logs (Polling):**
```http
GET /api/admin/gacha-logs/realtime?last_id=0
```

---

## 🎮 Usage

### User Flow

1. **Register** → Get 500 free coins
2. **Login** → Redirect to Dashboard
3. **Select Event** → Choose from active events
4. **Roll Gacha** → Spend 10 coins per roll
5. **View Reward** → Animated reward reveal
6. **Check History** → Track all past rolls

### Admin Flow

1. **Login as Admin** → Redirect to Admin Dashboard
2. **Manage Events** → Create/Edit/Delete events
3. **Manage Rewards** → Add rewards with drop rates (must total 100%)
4. **Monitor Logs** → View all user activities

---

## 🔒 Security Features

### Atomic Transactions
```php
DB::transaction(function () {
    $user = User::where('id', $userId)->lockForUpdate()->first();
    // Prevents race conditions during rapid clicking
});
```

### Input Validation
- All inputs validated with Laravel's validation rules
- Drop rate validation ensures total = 100%
- Coin balance verified before each roll

### Authentication
- Token-based auth with Laravel Sanctum
- Automatic token refresh on login
- Secure logout with token deletion

---

## 📁 Project Structure

```
gacha/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/AuthController.php
│   │   │   ├── Admin/
│   │   │   │   ├── EventController.php
│   │   │   │   ├── RewardController.php
│   │   │   │   └── GachaLogController.php
│   │   │   └── GachaController.php
│   │   └── Middleware/AdminMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Event.php
│       ├── Reward.php
│       └── GachaLog.php
├── database/
│   ├── migrations/
│   └── seeders/DatabaseSeeder.php
├── resources/js/
│   ├── views/
│   │   ├── Login.vue
│   │   ├── Register.vue
│   │   ├── Dashboard.vue
│   │   └── AdminDashboard.vue
│   ├── router/index.js
│   ├── stores/auth.js
│   └── App.vue
├── routes/api.php
└── README.md
```

---

## 🤝 Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request
