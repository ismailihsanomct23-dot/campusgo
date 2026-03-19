# CampusGo PHP + MySQL Backend

This folder adds a real database backend for CampusGo using PHP (PDO) and MySQL.

## 1) Create database

Run:

```bash
mysql -u root -p < backend/database.sql
```

## 2) Configure DB credentials

Edit:

- `backend/config.php`

Use your MySQL host, user, password, and database.

## 3) Start PHP server

```bash
php -S localhost:8000 -t backend
```

## 4) API endpoints

- `GET /api/health.php`
- `POST /api/auth.php?action=register`
- `POST /api/auth.php?action=login`
- `GET /api/routes.php`
- `GET /api/tickets.php?email=user@college.edu`
- `POST /api/tickets.php`
- `PATCH /api/tickets.php`

## 5) Example JSON payloads

### Register

`POST /api/auth.php?action=register`

```json
{
  "name": "John Doe",
  "email": "john@college.edu",
  "studentId": "STU2024001",
  "phone": "9876543210",
  "role": "student",
  "dept": "CSE",
  "year": "2",
  "password": "secret123"
}
```

### Login

`POST /api/auth.php?action=login`

```json
{
  "idOrEmail": "john@college.edu",
  "password": "secret123"
}
```

### Create ticket

`POST /api/tickets.php`

```json
{
  "email": "john@college.edu",
  "routeCode": "R1",
  "from": "Chelakkara",
  "to": "Thrissur",
  "date": "2026-03-20",
  "time": "7:30 AM",
  "schedule": "morning",
  "seat": "A1",
  "fare": 30,
  "status": "confirmed",
  "paidVia": "upi"
}
```

---

Your current `campusgo.html` still uses `localStorage`. Next step is wiring frontend calls to these API endpoints.
