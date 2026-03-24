-- CampusGo SQLite Database Schema

CREATE TABLE IF NOT EXISTS users (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  name TEXT NOT NULL,
  email TEXT NOT NULL UNIQUE,
  student_id TEXT NOT NULL UNIQUE,
  phone TEXT NOT NULL,
  role TEXT NOT NULL DEFAULT 'student' CHECK(role IN ('student','faculty','staff','admin')),
  dept TEXT NOT NULL,
  year_value TEXT NOT NULL DEFAULT 'na',
  password_hash TEXT NOT NULL,
  is_admin INTEGER NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS routes (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  route_code TEXT NOT NULL UNIQUE,
  name TEXT NOT NULL,
  bus_no TEXT NOT NULL,
  color TEXT NOT NULL DEFAULT '#2a5298',
  base_fare REAL NOT NULL DEFAULT 10,
  bus_capacity INTEGER NOT NULL DEFAULT 40,
  status TEXT NOT NULL DEFAULT 'active' CHECK(status IN ('active','inactive')),
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME
);

CREATE TABLE IF NOT EXISTS route_stops (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  route_id INTEGER NOT NULL,
  stop_order INTEGER NOT NULL,
  stop_name TEXT NOT NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
  UNIQUE(route_id, stop_order)
);

CREATE TABLE IF NOT EXISTS route_times (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  route_id INTEGER NOT NULL,
  schedule_type TEXT NOT NULL CHECK(schedule_type IN ('morning','evening')),
  time_label TEXT NOT NULL,
  FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tickets (
  id INTEGER PRIMARY KEY AUTOINCREMENT,
  ticket_code TEXT NOT NULL UNIQUE,
  user_id INTEGER NOT NULL,
  route_id INTEGER NOT NULL,
  from_stop TEXT NOT NULL,
  to_stop TEXT NOT NULL,
  travel_date DATE NOT NULL,
  time_slot TEXT NOT NULL,
  schedule_type TEXT NOT NULL CHECK(schedule_type IN ('morning','evening')),
  seat_no TEXT NOT NULL,
  fare REAL NOT NULL,
  status TEXT NOT NULL DEFAULT 'confirmed' CHECK(status IN ('confirmed','reserved','cancelled')),
  paid_via TEXT,
  booked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cancelled_at DATETIME,
  FOREIGN KEY (user_id) REFERENCES users(id),
  FOREIGN KEY (route_id) REFERENCES routes(id),
  UNIQUE(route_id, travel_date, time_slot, seat_no)
);

-- Insert sample routes
INSERT OR IGNORE INTO routes (route_code, name, bus_no, color, base_fare, bus_capacity, status)
VALUES
  ('R1', 'Chelakkara → Thrissur', 'CB-101', '#2a5298', 10, 40, 'active'),
  ('R2', 'Thrissur → Chelakkara', 'CB-102', '#e67e22', 10, 40, 'active');

-- Get route IDs
-- Insert route stops for R1
INSERT OR IGNORE INTO route_stops (route_id, stop_order, stop_name)
SELECT id, 1, 'Chelakkara' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 2, 'Manaladi' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 3, 'Vazhakkad' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 4, 'Ottupara' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 5, 'Wadakkanchery' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 6, 'Athani' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 7, 'Thrissur' FROM routes WHERE route_code='R1';

-- Insert route stops for R2
INSERT OR IGNORE INTO route_stops (route_id, stop_order, stop_name)
SELECT id, 1, 'Thrissur' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 2, 'Athani' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 3, 'Wadakkanchery' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 4, 'Ottupara' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 5, 'Vazhakkad' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 6, 'Manaladi' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 7, 'Chelakkara' FROM routes WHERE route_code='R2';

-- Insert route times for R1
INSERT OR IGNORE INTO route_times (route_id, schedule_type, time_label)
SELECT id, 'morning', '7:00 AM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'morning', '7:30 AM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'morning', '8:00 AM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'morning', '8:30 AM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'evening', '3:30 PM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'evening', '4:30 PM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'evening', '5:30 PM' FROM routes WHERE route_code='R1'
UNION ALL
SELECT id, 'evening', '6:30 PM' FROM routes WHERE route_code='R1';

-- Insert route times for R2
INSERT OR IGNORE INTO route_times (route_id, schedule_type, time_label)
SELECT id, 'morning', '7:00 AM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'morning', '7:30 AM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'morning', '8:00 AM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'morning', '8:30 AM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'evening', '3:30 PM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'evening', '4:30 PM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'evening', '5:30 PM' FROM routes WHERE route_code='R2'
UNION ALL
SELECT id, 'evening', '6:30 PM' FROM routes WHERE route_code='R2';
