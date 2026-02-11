-- =====================================================
-- NO BED SYNDROME DATABASE SCHEMA
-- =====================================================

CREATE DATABASE IF NOT EXISTS no_bed_syndrome;
USE no_bed_syndrome;

-- =========================
-- HOSPITALS
-- =========================
CREATE TABLE IF NOT EXISTS hospitals (
    hospital_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    region VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    logo_url VARCHAR(255),
    total_beds INT DEFAULT 0,
    available_beds INT DEFAULT 0,
    icu_beds INT DEFAULT 0,
    icu_available INT DEFAULT 0,
    emergency_beds INT DEFAULT 0,
    emergency_available INT DEFAULT 0,
    general_beds INT DEFAULT 0,
    general_available INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================
-- USERS
-- =========================
CREATE TABLE IF NOT EXISTS users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    hospital_id INT,
    username VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    role ENUM('admin','staff','viewer') DEFAULT 'staff',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE SET NULL
);

-- =========================
-- BED REQUESTS
-- =========================
CREATE TABLE IF NOT EXISTS bed_requests (
    request_id INT PRIMARY KEY AUTO_INCREMENT,
    hospital_id INT NOT NULL,
    patient_name VARCHAR(255),
    patient_email VARCHAR(100),
    patient_phone VARCHAR(20),
    bed_type ENUM('general','icu','emergency'),
    urgency_level ENUM('low','medium','high','critical') DEFAULT 'medium',
    reason TEXT,
    status ENUM('pending','approved','rejected','fulfilled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
);

-- =========================
-- IMAGES
-- =========================
CREATE TABLE IF NOT EXISTS images (
    image_id INT PRIMARY KEY AUTO_INCREMENT,
    hospital_id INT,
    type ENUM('hero','exterior','interior','facility','department'),
    image_url VARCHAR(255),
    caption TEXT,
    display_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (hospital_id) REFERENCES hospitals(hospital_id) ON DELETE CASCADE
);

-- =========================
-- SAMPLE HOSPITALS
-- =========================
INSERT INTO hospitals (name,address,region,phone,email,logo_url,total_beds,available_beds,icu_beds,icu_available,emergency_beds,emergency_available,general_beds,general_available) VALUES
('Korle Bu Teaching Hospital','Accra','Greater Accra','+233302665401','info@kbth.gov.gh','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=200&h=200&fit=crop&crop=center',2000,450,100,25,150,45,1750,380),
('Komfo Anokye Teaching Hospital','Kumasi','Ashanti','+233322022701','info@kath.gov.gh','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=200&h=200&fit=crop&crop=center',1500,320,80,18,120,35,1300,267),
('Ridge Hospital','Accra','Greater Accra','+233302773913','info@ridge.gov.gh','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop&crop=center',800,180,40,12,60,20,700,148),
('37 Military Hospital','Accra','Greater Accra','+233302776111','info@37military.org','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=200&h=200&fit=crop&crop=center',600,135,30,8,45,15,525,112),
('Tamale Teaching Hospital','Tamale','Northern','+233372022701','info@tth.gov.gh','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop&crop=center',800,200,35,10,55,18,710,172),
('Cape Coast Teaching Hospital','Cape Coast','Central','+233332022701','info@ccth.gov.gh','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=200&h=200&fit=crop&crop=center',600,150,25,7,40,12,535,131),
('Ho Teaching Hospital','Ho','Volta','+233362022701','info@hth.gov.gh','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=200&h=200&fit=crop&crop=center',500,125,20,6,30,10,450,109),
('Effia Nkwanta Regional Hospital','Takoradi','Western','+233312022701','info@erh.gov.gh','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=200&h=200&fit=crop&crop=center',400,100,15,5,25,8,360,87),
('Sunyani Regional Hospital','Sunyani','Bono','+233352022701','info@srh.gov.gh','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=200&h=200&fit=crop&crop=center',350,88,12,4,20,7,318,77),
('Tema General Hospital','Tema','Greater Accra','+233303022701','info@tgh.gov.gh','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=200&h=200&fit=crop&crop=center',450,113,18,5,28,9,404,99);

-- =========================
-- HERO IMAGES
-- =========================
INSERT INTO images (hospital_id,type,image_url,caption) VALUES
(NULL,'hero','https://images.unsplash.com/photo-1551190822-a9333d879b1f','Medical care'),
(NULL,'hero','https://images.unsplash.com/photo-1559757148-5c350d0d3c56','Healthcare staff'),
(NULL,'hero','https://images.unsplash.com/photo-1582750433449-648ed127bb54','Hospital environment');

-- =========================
-- HOSPITAL IMAGES
-- =========================
INSERT INTO images (hospital_id,type,image_url,caption,display_order) VALUES
(1,'exterior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Korle Bu Teaching Hospital exterior',1),
(1,'interior','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Korle Bu Teaching Hospital interior',2),
(1,'facility','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Korle Bu emergency department',3),
(2,'exterior','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Komfo Anokye Teaching Hospital exterior',1),
(2,'interior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Komfo Anokye Teaching Hospital interior',2),
(2,'facility','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Komfo Anokye ICU department',3),
(3,'exterior','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Ridge Hospital exterior',1),
(3,'interior','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Ridge Hospital interior',2),
(3,'facility','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Ridge Hospital maternity ward',3),
(4,'exterior','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','37 Military Hospital exterior',1),
(4,'interior','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','37 Military Hospital interior',2),
(4,'facility','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','37 Military Hospital surgical department',3),
(5,'exterior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Tamale Teaching Hospital exterior',1),
(5,'interior','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Tamale Teaching Hospital interior',2),
(5,'facility','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Tamale Teaching Hospital outpatient department',3),
(6,'exterior','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Cape Coast Teaching Hospital exterior',1),
(6,'interior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Cape Coast Teaching Hospital interior',2),
(6,'facility','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Cape Coast Teaching Hospital laboratory',3),
(7,'exterior','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Ho Teaching Hospital exterior',1),
(7,'interior','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Ho Teaching Hospital interior',2),
(7,'facility','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Ho Teaching Hospital radiology department',3),
(8,'exterior','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Effia Nkwanta Regional Hospital exterior',1),
(8,'interior','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Effia Nkwanta Regional Hospital interior',2),
(8,'facility','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Effia Nkwanta Regional Hospital pharmacy',3),
(9,'exterior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Sunyani Regional Hospital exterior',1),
(9,'interior','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Sunyani Regional Hospital interior',2),
(9,'facility','https://images.unsplash.com/photo-1582750433449-648ed127bb54?w=800&h=400&fit=crop','Sunyani Regional Hospital pediatric ward',3),
(10,'exterior','https://images.unsplash.com/photo-1576091160399-112ba8d25d1f?w=800&h=400&fit=crop','Tema General Hospital exterior',1),
(10,'interior','https://images.unsplash.com/photo-1551190822-a9333d879b1f?w=800&h=400&fit=crop','Tema General Hospital interior',2),
(10,'facility','https://images.unsplash.com/photo-1559757148-5c350d0d3c56?w=800&h=400&fit=crop','Tema General Hospital emergency room',3);

-- =========================
-- SAFE ADMIN USERS (FIXED)
-- =========================

INSERT INTO users (hospital_id, username, password_hash, email, role, is_active)
SELECT 1,'admin',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'admin@nobedsyndrome.gov.gh','admin',TRUE
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='admin');

INSERT INTO users (hospital_id, username, password_hash, email, role, is_active)
SELECT 1,'korle_staff',
'$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
'staff@kbth.gov.gh','staff',TRUE
WHERE NOT EXISTS (SELECT 1 FROM users WHERE username='korle_staff');

-- =========================
-- END
-- =========================
