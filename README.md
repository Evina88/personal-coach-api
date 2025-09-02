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

---


Testing the API

<img width="859" height="661" alt="image" src="https://github.com/user-attachments/assets/ac5f9bc4-cd2c-425e-a5ba-70ba559a8a63" />

<img width="851" height="650" alt="image" src="https://github.com/user-attachments/assets/c23858ef-f70c-4dd6-aa91-6ec8918c93d6" />


Login

<img width="1375" height="895" alt="image" src="https://github.com/user-attachments/assets/5a8b6055-6a38-4509-a493-7a27067b64db" />

---

## 🚀 Features (Day 2)

✅ CRUD operations for exercises (create, read, update, delete)

🛠️ Patch password API

🧪 Fully tested with Postman requests

---

Patch for updating password

<img width="1402" height="807" alt="image" src="https://github.com/user-attachments/assets/cb991a51-ddd9-4554-951a-a950af6994c6" />


Create exercice

<img width="1382" height="837" alt="image" src="https://github.com/user-attachments/assets/857e9226-402c-47e2-b52b-f56959ec03b2" />


Get exercises

<img width="1377" height="895" alt="image" src="https://github.com/user-attachments/assets/b2529358-d752-4fec-a7fc-84633bf5cff7" />

Patch Exercise 

<img width="1367" height="850" alt="image" src="https://github.com/user-attachments/assets/59dc3d5c-c10f-4332-b841-c82ffed22b65" />

---





