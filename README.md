# Personal Coding Coach API – Day 1

**A backend-only, adaptive coding coach API built with Laravel 10.**  
This project represents **Day 1** of the Personal Coding Coach concept: a tool designed to track a user’s coding journey, manage exercises, and provide a foundation for an adaptive learning experience.

> This API is ideal for learners, educators, and developers who want a clean, backend-first system to manage coding exercises and user progress.

---

## 🚀 Features (Day 1)

- **User registration & login** with email and password
- **Token-based authentication** (Laravel Sanctum)
- Tracks which **programming languages the user is learning**
- Fully **backend-only**; no frontend required (..yet ;-) )
- Clean **RESTful API architecture**
- Easy to extend for exercise tracking and adaptive suggestions
- Tested via **Postman** with ready-to-use requests

---

## 💻 Installation & Setup

**Prerequisites:**

- PHP ≥ 8.2  
- Composer  
- MySQL (or SQLite)  
- Git  
- Postman (or any API client)  

**Step 1: Clone repository**

git clone https://github.com/Evina88/personal-coach-api/
cd personal-coach-api

**Step 2: Install dependencies**
composer install

**Step 3: Configure environment**
In .env file set your database credentials.

**Step 4: Run migrations**
php artisan migrate

**Step 5: Start the server**
php artisan serve

Testing the API

<img width="859" height="661" alt="image" src="https://github.com/user-attachments/assets/ac5f9bc4-cd2c-425e-a5ba-70ba559a8a63" />

<img width="851" height="650" alt="image" src="https://github.com/user-attachments/assets/c23858ef-f70c-4dd6-aa91-6ec8918c93d6" />


