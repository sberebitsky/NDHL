-- create_team_roster.sql
CREATE TABLE IF NOT EXISTS team_roster (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  team VARCHAR(100) NOT NULL,
  drafted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_player (player_id),
  FOREIGN KEY (player_id) REFERENCES signups(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

