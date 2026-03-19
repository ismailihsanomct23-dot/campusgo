CREATE DATABASE IF NOT EXISTS campusgo CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE campusgo;

CREATE TABLE IF NOT EXISTS users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  email VARCHAR(180) NOT NULL UNIQUE,
  student_id VARCHAR(60) NOT NULL UNIQUE,
  phone VARCHAR(20) NOT NULL,
  role ENUM('student','faculty','staff','admin') NOT NULL DEFAULT 'student',
  dept VARCHAR(20) NOT NULL,
  year_value VARCHAR(10) NOT NULL DEFAULT 'na',
  password_hash VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS routes (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  route_code VARCHAR(30) NOT NULL UNIQUE,
  name VARCHAR(180) NOT NULL,
  bus_no VARCHAR(30) NOT NULL,
  color VARCHAR(20) NOT NULL DEFAULT '#2a5298',
  base_fare DECIMAL(10,2) NOT NULL DEFAULT 10,
  bus_capacity INT NOT NULL DEFAULT 40,
  status ENUM('active','inactive') NOT NULL DEFAULT 'active',
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NULL ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS route_stops (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  route_id BIGINT UNSIGNED NOT NULL,
  stop_order INT NOT NULL,
  stop_name VARCHAR(120) NOT NULL,
  CONSTRAINT fk_route_stops_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE,
  UNIQUE KEY uq_route_stop_order (route_id, stop_order)
);

CREATE TABLE IF NOT EXISTS route_times (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  route_id BIGINT UNSIGNED NOT NULL,
  schedule_type ENUM('morning','evening') NOT NULL,
  time_label VARCHAR(40) NOT NULL,
  CONSTRAINT fk_route_times_route FOREIGN KEY (route_id) REFERENCES routes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tickets (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  ticket_code VARCHAR(40) NOT NULL UNIQUE,
  user_id BIGINT UNSIGNED NOT NULL,
  route_id BIGINT UNSIGNED NOT NULL,
  from_stop VARCHAR(120) NOT NULL,
  to_stop VARCHAR(120) NOT NULL,
  travel_date DATE NOT NULL,
  time_slot VARCHAR(40) NOT NULL,
  schedule_type ENUM('morning','evening') NOT NULL,
  seat_no VARCHAR(20) NOT NULL,
  fare DECIMAL(10,2) NOT NULL,
  status ENUM('confirmed','reserved','cancelled') NOT NULL DEFAULT 'confirmed',
  paid_via VARCHAR(30) NULL,
  booked_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cancelled_at DATETIME NULL,
  CONSTRAINT fk_tickets_user FOREIGN KEY (user_id) REFERENCES users(id),
  CONSTRAINT fk_tickets_route FOREIGN KEY (route_id) REFERENCES routes(id),
  UNIQUE KEY uq_ticket_seat_trip (route_id, travel_date, time_slot, seat_no)
);

INSERT INTO routes (route_code, name, bus_no, color, base_fare, bus_capacity, status)
VALUES
  ('R1', 'Chelakkara → Thrissur', 'CB-101', '#2a5298', 10, 40, 'active'),
  ('R2', 'Thrissur → Chelakkara', 'CB-102', '#e67e22', 10, 40, 'active')
ON DUPLICATE KEY UPDATE name = VALUES(name), bus_no = VALUES(bus_no), color = VALUES(color);

SET @r1 = (SELECT id FROM routes WHERE route_code = 'R1' LIMIT 1);
SET @r2 = (SELECT id FROM routes WHERE route_code = 'R2' LIMIT 1);

DELETE FROM route_stops WHERE route_id IN (@r1, @r2);
INSERT INTO route_stops (route_id, stop_order, stop_name) VALUES
(@r1,1,'Chelakkara'),(@r1,2,'Manaladi'),(@r1,3,'Vazhakkad'),(@r1,4,'Ottupara'),(@r1,5,'Wadakkanchery'),(@r1,6,'Athani'),(@r1,7,'Thrissur'),
(@r2,1,'Thrissur'),(@r2,2,'Athani'),(@r2,3,'Wadakkanchery'),(@r2,4,'Ottupara'),(@r2,5,'Vazhakkad'),(@r2,6,'Manaladi'),(@r2,7,'Chelakkara');

DELETE FROM route_times WHERE route_id IN (@r1, @r2);
INSERT INTO route_times (route_id, schedule_type, time_label) VALUES
(@r1,'morning','7:00 AM'),(@r1,'morning','7:30 AM'),(@r1,'morning','8:00 AM'),(@r1,'morning','8:30 AM'),
(@r1,'evening','3:30 PM'),(@r1,'evening','4:30 PM'),(@r1,'evening','5:30 PM'),(@r1,'evening','6:30 PM'),
(@r2,'morning','7:00 AM'),(@r2,'morning','7:30 AM'),(@r2,'morning','8:00 AM'),(@r2,'morning','8:30 AM'),
(@r2,'evening','3:30 PM'),(@r2,'evening','4:30 PM'),(@r2,'evening','5:30 PM'),(@r2,'evening','6:30 PM');
