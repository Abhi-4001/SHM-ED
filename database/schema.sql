CREATE DATABASE IF NOT EXISTS smart_health_monitoring
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE smart_health_monitoring;

CREATE TABLE IF NOT EXISTS users (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(254) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS assessments (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    age TINYINT UNSIGNED NOT NULL,
    gender VARCHAR(24) NOT NULL,
    height_cm DECIMAL(5,1) NOT NULL,
    weight_kg DECIMAL(5,1) NOT NULL,
    bmi DECIMAL(5,1) NOT NULL,
    glucose_mg_dl SMALLINT UNSIGNED NOT NULL,
    glucose_type ENUM('fasting', 'random') NOT NULL,
    systolic_bp SMALLINT UNSIGNED NOT NULL,
    diastolic_bp SMALLINT UNSIGNED NOT NULL,
    family_history ENUM('yes', 'no') NOT NULL,
    activity_level ENUM('low', 'moderate', 'high') NOT NULL,
    smoking ENUM('yes', 'no') NOT NULL,
    alcohol_use ENUM('yes', 'no') NOT NULL,
    previous_history ENUM('none', 'prediabetes', 'diabetes', 'gestational_diabetes', 'other') NOT NULL,
    score SMALLINT UNSIGNED NOT NULL,
    risk_level ENUM('low', 'moderate', 'high') NOT NULL,
    factors_json JSON NOT NULL,
    recommendations_json JSON NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_assessments_user_created (user_id, created_at),
    CONSTRAINT fk_assessments_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE=InnoDB;
