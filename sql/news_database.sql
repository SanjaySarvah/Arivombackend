CREATE DATABASE IF NOT EXISTS news_db;
USE news_db;

-- News table
CREATE TABLE news (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  category VARCHAR(100),
  subcategory VARCHAR(100),
  excerpt TEXT,
  content TEXT,
  image VARCHAR(255),
  author VARCHAR(100),
  slug VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  likes INT DEFAULT 0
);

INSERT INTO news (title, category, subcategory, excerpt, content, image, author, slug)
VALUES 
('Tech Update 2025', 'Technology', 'AI', 'AI revolution in India', 'Full story about AI growth...', 'ai.jpg', 'John Doe', 'tech-update-2025'),
('Politics Today', 'Politics', 'Election', 'New election news', 'Detailed political report...', 'election.jpg', 'Jane Doe', 'politics-today');

-- Articles table
CREATE TABLE articles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  category VARCHAR(100),
  subcategory VARCHAR(100),
  subsubcategory VARCHAR(100),
  excerpt TEXT,
  content TEXT,
  image VARCHAR(255),
  author VARCHAR(100),
  slug VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  likes INT DEFAULT 0
);

INSERT INTO articles (title, category, subcategory, excerpt, content, image, author, slug)
VALUES 
('Investment Tips', 'Finance', 'Stock Market', 'Top 10 investment strategies', 'Full content on investing...', 'invest.jpg', 'Clament', 'investment-tips'),
('Healthy Living', 'Lifestyle', 'Fitness', 'Morning yoga guide', 'Detailed fitness article...', 'yoga.jpg', 'Sanjay', 'healthy-living');

-- Videos table
CREATE TABLE news_videos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255),
  category VARCHAR(100),
  excerpt TEXT,
  content TEXT,
  videoUrl VARCHAR(255),
  thumbnail VARCHAR(255),
  author VARCHAR(100),
  slug VARCHAR(255),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  likes INT DEFAULT 0
);

INSERT INTO news_videos (title, category, excerpt, content, videoUrl, thumbnail, author, slug)
VALUES
('Budget 2025 Explained', 'Finance', 'Quick summary of budget 2025', 'Video explanation...', 'https://example.com/video.mp4', 'thumb.jpg', 'Ravi', 'budget-2025-explained');
