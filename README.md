# Blog Management API (Laravel 11 + Sanctum)

A **complete RESTful Blog API** with **Authentication, CRUD, Like Toggle, Search, Filter, Pagination, and Image Upload**.

---

## Features

- **Login** → Sanctum Token
- **CRUD** (Create, Read, Update, Delete)
- **Image Upload** (Public Disk)
- **Like Toggle** (Like → Unlike)
- **Search** (Title + Description)
- **Sort**: `latest` / `most_liked`
- **Pagination** (10 per page)
- **`is_liked`** flag
- **`image_url`** in response
- **Owner-only** edit/delete

---

## Tech Stack

- Laravel 11
- Sanctum (API Auth)
- MySQL
- PHP 8.2+
- XAMPP

---

## Setup

```bash
git clone https://github.com/Ayush-Donga/blog-management-api.git
cd blog-management-api
composer install
cp .env.example .env