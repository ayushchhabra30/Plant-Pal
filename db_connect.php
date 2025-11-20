<?php
/*
 * db_connect.php
 * Connects to the database and automatically sets up tables if they don't exist.
 */

// --- Database Configuration ---
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', 'ayush9122');
define('DB_NAME', 'plant_pal_db');

// --- 1. Connect to MySQL Server (to create DB if needed) ---
$conn = new mysqli(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if it doesn't exist
$sql_create_db = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql_create_db) === TRUE) {
    // Select the database
    $conn->select_db(DB_NAME);
} else {
    die("Error creating database: " . $conn->error);
}

// Set charset
$conn->set_charset("utf8mb4");

// --- 2. Define Table Creation Queries ---
$queries = [];

// A. Ensure 'users' table exists first (Required for Foreign Keys)
$queries['users'] = "CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// B. Plants Table
$queries['plants'] = "CREATE TABLE IF NOT EXISTS `plants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL,
  `scientific_name` VARCHAR(255),
  `care_level` VARCHAR(50),
  `care_level_icon` VARCHAR(50),
  `watering` VARCHAR(100),
  `light` VARCHAR(100),
  `image_url` VARCHAR(255),
  `description` TEXT NULL,
  `submitted_by_user_id` INT NULL,
  `is_approved` BOOLEAN DEFAULT 0,
  FOREIGN KEY (`submitted_by_user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// C. My Plants (User Collection)
$queries['my_plants'] = "CREATE TABLE IF NOT EXISTS `my_plants` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `plant_id` INT NOT NULL,
  `nickname` VARCHAR(100),
  `added_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`plant_id`) REFERENCES `plants`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// D. Care Schedule
$queries['care_schedule'] = "CREATE TABLE IF NOT EXISTS `care_schedule` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `my_plant_id` INT NOT NULL,
  `task_type` ENUM('water', 'fertilize', 'prune', 'repot') NOT NULL,
  `frequency_days` INT NOT NULL,
  `last_completed_date` DATE,
  `next_due_date` DATE NOT NULL,
  FOREIGN KEY (`my_plant_id`) REFERENCES `my_plants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// E. Growth Diary
$queries['growth_diary'] = "CREATE TABLE IF NOT EXISTS `growth_diary` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `my_plant_id` INT NOT NULL,
  `entry_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `notes` TEXT,
  `image_url` VARCHAR(255),
  FOREIGN KEY (`my_plant_id`) REFERENCES `my_plants`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// F. Articles
$queries['articles'] = "CREATE TABLE IF NOT EXISTS `articles` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `author_id` INT,
  `category` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// G. Store Items
$queries['store_items'] = "CREATE TABLE IF NOT EXISTS `store_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `price` DECIMAL(10, 2),
  `image_url` VARCHAR(255),
  `affiliate_link` VARCHAR(1024) NOT NULL,
  `category` ENUM('plants', 'tools', 'accessories')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// H. Cart
$queries['cart'] = "CREATE TABLE IF NOT EXISTS `cart` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `quantity` INT NOT NULL DEFAULT 1,
  `is_wishlist` BOOLEAN DEFAULT 0,
  `added_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`item_id`) REFERENCES `store_items`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

// --- 3. Execute Table Creation ---

foreach ($queries as $table_name => $sql) {
    if ($conn->query($sql) !== TRUE) {
        // In production, log this. For now, we stop to alert the developer.
        die("Error creating table '$table_name': " . $conn->error);
    }
}

// --- 4. Special Handling: Update 'users' table if column is missing ---
// We check if 'location' exists before trying to add it to avoid errors on page reload.
$check_col = $conn->query("SHOW COLUMNS FROM `users` LIKE 'location'");
if ($check_col && $check_col->num_rows == 0) {
    $alter_sql = "ALTER TABLE `users` ADD COLUMN `location` VARCHAR(255) NULL AFTER `password_hash`;";
    if ($conn->query($alter_sql) !== TRUE) {
        die("Error updating users table: " . $conn->error);
    }
}

// --- 5. Seed Data (Insert Default Plants) ---
// Uses INSERT IGNORE so it doesn't duplicate data if run multiple times.
$seed_sql = "INSERT IGNORE INTO `plants` 
(`id`, `name`, `scientific_name`, `care_level`, `care_level_icon`, `watering`, `light`, `image_url`) VALUES
(1, 'Monstera Deliciosa', 'Swiss Cheese Plant', 'Easy', 'fas fa-leaf', 'Weekly', 'Indirect bright light', 'asset/monstera-deliciosa-plant.jpg'),
(2, 'Snake Plant', 'Sansevieria Trifasciata', 'Very Easy', 'fas fa-seedling', 'Every 2-3 weeks', 'Low to bright light', 'asset/monstera-deliciosa-plant.jpg'),
(3, 'Pothos', 'Epipremnum Aureum', 'Easy', 'fas fa-spa', 'Weekly', 'Low to medium light', 'asset/pothos-golden-plant.jpg'),
(4, 'Fiddle Leaf Fig', 'Ficus Lyrata', 'Moderate', 'fas fa-tree', 'Weekly', 'Bright indirect light', 'asset/fiddle-leaf-fig.png'),
(5, 'Peace Lily', 'Spathiphyllum', 'Easy', 'fas fa-leaf', 'Weekly', 'Low to medium light', 'asset/peace-lily-white-flower.jpg'),
(6, 'Rubber Plant', 'Ficus Elastica', 'Easy', 'fas fa-seedling', 'Weekly', 'Bright indirect light', 'asset/rubber-plant-ficus.jpg');";

if ($conn->query($seed_sql) !== TRUE) {
    die("Error seeding data: " . $conn->error);
}

// --- End of Setup ---
// The $conn variable is now ready to be used by other scripts.
?>