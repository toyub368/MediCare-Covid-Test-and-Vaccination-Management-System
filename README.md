# 🩺 MediCare – Covid Test and Vaccination Management System

MediCare is a **PHP-based**, **session-managed** web application designed to manage Covid-19 testing and vaccination appointments. It provides separate portals for **Admin**, **Hospitals**, and **Patients**, enabling secure and efficient healthcare service management.

---

## 📁 Project Features

### 👤 Admin Portal
- Manage hospital registrations and approvals
- Monitor testing and vaccination bookings
- View system-wide data

### 🏥 Hospital Portal
- Schedule and manage Covid test & vaccine appointments
- Update inventory of vaccines
- Input test results and vaccination records

### 🧑‍⚕️ Patient Portal
- Register and log in
- Book Covid test and vaccination slots
- View test results and download vaccine certificates

---

## ⚙️ Technologies Used

- **Backend**: PHP 8.2
- **Database**: MySQL (MariaDB 10.4)
- **Frontend**: HTML, CSS, Bootstrap
- **Development Server**: XAMPP / Apache
- **Session Management**: PHP sessions

---

## 🗃️ Database Details

### 📌 Database Name
```sql
covid_booking_system
```

### 📦 Main Tables

| Table | Description |
|-------|-------------|
| `admins` | Admin login and user management |
| `hospitals` | Hospital registration and access |
| `patients` | Patient registration and profile |
| `test_bookings` | Covid test bookings and results |
| `vaccination_bookings` | Vaccination bookings and certificates |
| `vaccine_inventory` | Track available vaccines per hospital |

✅ **Foreign Keys** are used for maintaining relationships and enabling cascading deletes between:
- `test_bookings` ↔ `patients`, `hospitals`
- `vaccination_bookings` ↔ `patients`, `hospitals`
- `vaccine_inventory` ↔ `hospitals`

---

## 🧪 Sample Data Included

Your SQL dump includes:
- 1 admin user
- 6 registered hospitals (e.g., AKUH, JPMC, SKMH)
- 7 registered patients
- Sample test and vaccine bookings
- Pre-filled vaccine inventory

---

## 🚀 How to Run the Project

1. **Install XAMPP** and start Apache & MySQL
2. Place the project folder in `htdocs/`
3. Import the SQL file into **phpMyAdmin**:
   - Go to `localhost/phpmyadmin`
   - Create a database named `covid_booking_system`
   - Import the `.sql` file provided
4. Update database connection in your project’s config file:
```php
$conn = new mysqli("localhost", "root", "", "covid_booking_system");
```
5. Open the project in browser:
```
http://localhost/your_project_folder/
```

---

## 🔐 Default Login (Sample)

### Admin
- **Email**: `admin@covidbook.com`
- **Password**: `admin123` (hash is stored, adjust if needed)

### Hospital
- Use any hospital email like `akuu@karachi.com`

### Patient
- Use patient email like `ayesha@gmail.com`

---

## 🧠 What I Learned

- PHP session management and role-based access
- SQL database normalization with foreign key constraints
- Web security best practices (password hashing, validation)
- CRUD operations and real-world form handling

---

## ❗ Notes

- Passwords are stored using PHP's `password_hash()` (bcrypt).
- All operations are secured using session-based authentication.
- Be sure to configure SMTP if using email notifications.

---

## 📄 License

This project is for **educational purposes only** and simulates a health service management system. Not for production use.

---

