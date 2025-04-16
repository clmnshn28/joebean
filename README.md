# ☕︎ Joebean : A Point-of-Sale System with Inventory
**Joebean** is a simple and efficient POS and inventory system designed for cafés and food businesses, featuring two main roles—**Admin** and **Cashier**—where Admins can manage items, transactions, and staff, while Cashiers can easily take and process customer orders.

## ✨ Features
  ### Joebean supports two user roles:
 #### 👤 Admin
   - Full access to Item Lists
   - Can manage cashier accounts
   - Can view transaction records

#### 👨‍💼 Cashier
  - Can view and select food & beverage items
  - Can place and process customer orders
  - Can view their own profile information

## </> Tech Stack
  - `HTML`
  - `CSS`
  - `JS (JavaScript)`
  - `PHP`
  - `MySQL`
 
## 💡 Get Started
### 1️⃣ Clone the Repository
```bash
https://github.com/clmnshn28/jeobean.git
cd jeobean
```
### 2️⃣ Set up your Database

Import the SQL file provided (if any) or create a new database:

```sql
CREATE DATABASE joebean;
```
Then, create the necessary tables based on the `ERD (Entity Relationship Diagram)` below:
![ERD](assets/images/readme/erd.png)
**💡 The ERD illustrates how tables like `admins`, `cashiers`, `products`, and `transactions` are related.**

After that, update your database credentials in the config file:
 - Open `config/db.php`
 - If there is a custom port like `3307`, remove it to use the default MySQL port.
  
**From:**
```sql
$conn = new mysqli("localhost", "root", "", "joebean", 3307);
```
**To:**
```sql
$conn = new mysqli("localhost", "root", "", "joebean");
```

### 3️⃣ Launch Locally

If you're using XAMPP, MAMP, or any local server:

1. Move the project folder to the htdocs directory (or your server's root folder)
2. Start Apache and MySQL
3. Visit `http://localhost/joebean` in your browser
## 🔎 Overview

### 👤 Admin Screens

#### Admin Login
![Admin Login](assets/images/readme/admin-login.png)

#### Admin Item List
![Admin Item List](assets/images/readme/admin-item-list.png)

#### Admin Item List Modals
![Item Modal 1](assets/images/readme/admin-item-list-modal1.png)
![Item Modal 2](assets/images/readme/admin-item-list-modal2.png)
![Item Modal 3](assets/images/readme/admin-item-list-modal3.png)

#### Admin Cashier List
![Cashier List](assets/images/readme/admin-cashier-list.png)

#### Admin Cashier List Modals
![Cashier Modal 1](assets/images/readme/admin-cashier-list-modal1.png)
![Cashier Modal 2](assets/images/readme/admin-cashier-list-modal2.png)
![Cashier Modal 3](assets/images/readme/admin-cashier-list-modal3.png)
![Cashier Modal 4](assets/images/readme/admin-cashier-list-modal4.png)

#### Admin Transaction Records
![Transaction Record](assets/images/readme/admin-transaction-record.png)
![Transaction Modal 1](assets/images/readme/admin-transaction-record-modal1.png)
![Transaction Modal 2](assets/images/readme/admin-transaction-record-modal2.png)

---

### 👨‍💼 Cashier Screens

#### Cashier Registration
![Cashier Registration](assets/images/readme/cashier-registration.png)

#### Cashier Login
![Cashier Login](assets/images/readme/cashier-login.png)

#### Cashier Dashboard
![Cashier Dashboard](assets/images/readme/cashier-dashboard.png)

#### Cashier Dashboard Modals
![Dashboard Modal 1](assets/images/readme/cashier-dashboard-modal1.png)
![Dashboard Modal 2](assets/images/readme/cashier-dashboard-modal2.png)



## 💡 Let's build something useful together! 🚀

