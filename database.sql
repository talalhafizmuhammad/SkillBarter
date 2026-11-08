CREATE DATABASE IF NOT EXISTS skillbarter
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE skillbarter;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS swaps;
DROP TABLE IF EXISTS skill_wants;
DROP TABLE IF EXISTS skills;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE users (
  id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  full_name     VARCHAR(100) NOT NULL,
  email         VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role          ENUM('user','admin') NOT NULL DEFAULT 'user',
  bio           VARCHAR(500) NOT NULL DEFAULT '',
  department    VARCHAR(100) NOT NULL DEFAULT '',
  semester      TINYINT UNSIGNED NULL,
  created_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id   INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- A skill a user is offering to teach
CREATE TABLE skills (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id     INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  title       VARCHAR(100) NOT NULL,
  description VARCHAR(500) NOT NULL DEFAULT '',
  level       ENUM('beginner','intermediate','expert') NOT NULL DEFAULT 'beginner',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE,
  INDEX (title)
) ENGINE=InnoDB;

-- A skill a user wants to learn (used for matching)
CREATE TABLE skill_wants (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id    INT UNSIGNED NOT NULL,
  title      VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- A barter: requester gets owner's skill and gives one of their own in return
CREATE TABLE swaps (
  id                 INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  requester_id       INT UNSIGNED NOT NULL,
  owner_id           INT UNSIGNED NOT NULL,
  requested_skill_id INT UNSIGNED NOT NULL,
  offered_skill_id   INT UNSIGNED NOT NULL,
  message            VARCHAR(500) NOT NULL DEFAULT '',
  status             ENUM('pending','accepted','rejected','cancelled','completed') NOT NULL DEFAULT 'pending',
  requester_done     TINYINT(1) NOT NULL DEFAULT 0,
  owner_done         TINYINT(1) NOT NULL DEFAULT 0,
  created_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (requester_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (requested_skill_id) REFERENCES skills(id) ON DELETE CASCADE,
  FOREIGN KEY (offered_skill_id) REFERENCES skills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE messages (
  id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  swap_id    INT UNSIGNED NOT NULL,
  sender_id  INT UNSIGNED NOT NULL,
  body       VARCHAR(1000) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (swap_id) REFERENCES swaps(id) ON DELETE CASCADE,
  FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE reviews (
  id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  swap_id     INT UNSIGNED NOT NULL,
  reviewer_id INT UNSIGNED NOT NULL,
  reviewee_id INT UNSIGNED NOT NULL,
  rating      TINYINT UNSIGNED NOT NULL,
  comment     VARCHAR(500) NOT NULL DEFAULT '',
  created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_review (swap_id, reviewer_id),
  FOREIGN KEY (swap_id) REFERENCES swaps(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (reviewee_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO categories (name) VALUES
 ('Programming'),('Design'),('Languages'),('Music'),('Mathematics'),
 ('Writing'),('Business'),('Photography'),('Video Editing'),('Test Prep'),('Other');
