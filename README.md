# 💇‍♂️ Ahgzly Salon Booking System

### Production-Ready Laravel Backend + Admin Dashboard

---

## 🔎 Project Summary

Ahgzly Salon is a professionally engineered booking management system designed for single-salon businesses with multi-branch capability.

It provides:

* A powerful Admin Dashboard built with Filament v4
* Secure REST API for mobile apps (Flutter-ready)
* Smart booking engine with slot calculation logic
* Role-based access control
* Clean architecture & scalable design

This project is structured as a portfolio-ready MVP demonstrating production-level backend development.

---

## 🏗 System Architecture

* **Backend:** Laravel 12
* **Admin Dashboard:** Filament v4
* **Authentication:** Laravel Sanctum (Token-Based)
* **Authorization:** Spatie Roles & Permissions
* **Database:** MySQL
* **Design Pattern:** Service-Oriented + Resource-based API
* **Architecture Style:** Clean & Scalable (Single-Salon, Multi-Branch Ready)

---

## 🚀 Key Features

### 🏢 Multi-Branch Ready Structure

* Manage multiple branches under one salon
* Branch-specific working hours
* Branch-specific staff and services
* Exceptional closures support

---

### 📅 Smart Appointment Engine

The system includes a custom-built slot calculation engine that:

* Generates available time slots dynamically
* Respects working hours
* Handles overnight shifts
* Prevents double bookings
* Excludes exceptional closures
* Calculates based on service duration

Booking Status Workflow:

* Pending
* Confirmed
* Cancelled
* Completed

---

### 👥 Staff & Services Management

* Assign services to specific employees
* Filter employees by service
* Control service duration
* Manage availability per branch

---

### 🔐 Security & Access Control

* Sanctum API authentication
* Token-based secure access
* Role-based permissions via Spatie
* Policy-driven authorization
* Scoped branch-based data handling

---

## 📡 API Overview

Base URL:

```
/api
```

### Public Endpoints

```
GET  /api/catalog/branches
GET  /api/catalog/services
GET  /api/catalog/staff
```

---

### Protected Endpoints (Sanctum Required)

#### Profile

```
GET   /api/profile
PATCH /api/profile
POST  /api/logout
```

#### Appointments

```
GET  /api/appointments/slots
POST /api/appointments/create
GET  /api/appointments/mine
POST /api/appointments/{id}/status
```

Authentication Header:

```
Authorization: Bearer {token}
Accept: application/json
```

---

## 🖥 Admin Dashboard

Dashboard URL:

```
/admin
```

Includes:

* Branch Management
* Services Management
* Staff Management
* Appointment Management
* Statistics Widgets
* Dynamic Branding per Branch

Built with Filament v4 for high-performance admin interfaces.

---

## 🧪 Technical Highlights (What Makes This Project Strong)

* Clean separation between API & Admin logic
* Appointment overlap protection logic
* Proper REST resource responses
* Token-based stateless API
* Extendable architecture
* Production-ready structure
* Designed for Flutter integration

---

## ⚙️ Installation Guide

```bash
git clone https://github.com/IBRHMHMDY/Ahgzly-Salon-AdminPanel.git
cd Ahgzly-Salon-AdminPanel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Access:

```
http://127.0.0.1:8000/admin
```

---

## 🧠 Intended Use Cases

* Salon Booking Systems
* Clinic Booking Systems
* Barbershop Management
* Service-based Businesses
* MVP for SaaS Salon Platform

---

## 📈 Future Scalability

The system is designed to support:

* Multi-tenant SaaS architecture
* Payment gateways
* Notifications (SMS / Email)
* Mobile push integration
* Reporting system
* CI/CD integration
* Automated testing

---

## 📌 Why This Project Stands Out

✔ Real-world booking logic
✔ Clean architecture
✔ Security-focused design
✔ Mobile-ready API
✔ Expandable to SaaS model
✔ Professional admin experience

---

## 👨‍💻 Author

Developed as a production-grade backend system demonstrating advanced Laravel architecture and API design.

---

## 📜 License

MIT License

