
import re

with open('original_import.sql', 'r', encoding='utf-8') as f:
    sql_content = f.read()

# 1. Base structure (re-use my previous one with 11 teams)
new_sql = """SET FOREIGN_KEY_CHECKS=0;
DROP DATABASE IF EXISTS f1_aura;
CREATE DATABASE IF NOT EXISTS f1_aura CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE f1_aura;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ecuries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    pays VARCHAR(100),
    couleur VARCHAR(50),
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pilotes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    numero INT,
    nationalite VARCHAR(100),
    ecurie_id INT,
    image_url VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ecurie_id) REFERENCES ecuries(id) ON DELETE SET NULL,
    INDEX idx_ecurie (ecurie_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users_pilotes_favoris (
    user_id INT NOT NULL,
    pilote_id INT NOT NULL,
    PRIMARY KEY (user_id, pilote_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users_ecuries_favorites (
    user_id INT NOT NULL,
    ecurie_id INT NOT NULL,
    PRIMARY KEY (user_id, ecurie_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ecurie_id) REFERENCES ecuries(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO ecuries (nom, pays, couleur, image_url) VALUES
('Red Bull Racing', 'Autriche', '#1E41FF', 'PICS/teams/red_bull_racing.webp'),
('Mercedes-AMG Petronas', 'Allemagne', '#00D2BE', 'PICS/teams/mercedesamg_petronas.webp'),
('Scuderia Ferrari', 'Italie', '#DC0000', 'PICS/teams/scuderia_ferrari_hp.webp'),
('McLaren', 'Royaume-Uni', '#FF8700', 'PICS/teams/mclaren_formula_1_team.webp'),
('Aston Martin', 'Royaume-Uni', '#006F62', 'PICS/teams/aston_martin_aramco.webp'),
('Alpine', 'France', '#0090FF', 'PICS/teams/bwt_alpine_f1_team.webp'),
('Williams', 'Royaume-Uni', '#005AFF', 'PICS/teams/williams_racing.webp'),
('Kick Sauber / Audi', 'Suisse', '#52E252', 'PICS/teams/kick_sauber_audi.webp'),
('Haas', 'États-Unis', '#FFFFFF', 'PICS/teams/moneygram_haas_f1_team.webp'),
('Visa Cash App RB', 'Italie', '#2B4562', 'PICS/teams/visa_cash_app_rb.webp'),
('Cadillac F1 Team', 'États-Unis', '#FFD700', 'PICS/teams/cadillac_f1_team.webp');

INSERT INTO pilotes (nom, prenom, numero, nationalite, ecurie_id, image_url) VALUES
('Verstappen', 'Max', 1, 'Pays-Bas', 1, 'PICS/drivers/verstappen.png'),
('Lawson', 'Liam', 30, 'Nouvelle-Zélande', 1, 'PICS/drivers/lawson.png'),

('Russell', 'George', 63, 'Royaume-Uni', 2, 'PICS/drivers/russell.png'),
('Antonelli', 'Andrea Kimi', 12, 'Italie', 2, 'PICS/drivers/antonelli.webp'),

('Leclerc', 'Charles', 16, 'Monaco', 3, 'PICS/drivers/leclerc.png'),
('Hamilton', 'Lewis', 44, 'Royaume-Uni', 3, 'PICS/drivers/hamilton.webp'),

('Norris', 'Lando', 4, 'Royaume-Uni', 4, 'PICS/drivers/norris.png'),
('Piastri', 'Oscar', 81, 'Australie', 4, 'PICS/drivers/piastri.png'),

('Alonso', 'Fernando', 14, 'Espagne', 5, 'PICS/drivers/alonso.png'),
('Stroll', 'Lance', 18, 'Canada', 5, 'PICS/drivers/stroll.png'),

('Gasly', 'Pierre', 10, 'France', 6, 'PICS/drivers/gasly.png'),
('Doohan', 'Jack', 7, 'Australie', 6, 'PICS/drivers/doohan.png'),

('Albon', 'Alexander', 23, 'Thaïlande', 7, 'PICS/drivers/albon.png'),
('Sainz', 'Carlos', 55, 'Espagne', 7, 'PICS/drivers/sainz.webp'),

('Hülkenberg', 'Nico', 27, 'Allemagne', 8, 'PICS/drivers/hulkenberg.webp'),
('Bortoleto', 'Gabriel', 5, 'Brésil', 8, 'PICS/drivers/bortoleto.webp'),

('Ocon', 'Esteban', 31, 'France', 9, 'PICS/drivers/ocon.webp'),
('Bearman', 'Oliver', 87, 'Royaume-Uni', 9, 'PICS/drivers/bearman.png'),

('Tsunoda', 'Yuki', 22, 'Japon', 10, 'PICS/drivers/tsunoda.png'),
('Hadjar', 'Isack', 6, 'France', 10, 'PICS/drivers/hadjar.webp'),

('Bottas', 'Valtteri', 77, 'Finlande', 11, 'PICS/drivers/bottas.webp'),
('Colapinto', 'Franco', 43, 'Argentine', 11, 'PICS/drivers/colapinto.webp');

CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    annee INT NOT NULL,
    round INT NOT NULL,
    nom VARCHAR(100) NOT NULL,
    lieu VARCHAR(100) NOT NULL,
    date_course DATE NOT NULL,
    statut VARCHAR(20) DEFAULT 'À venir', 
    p1_pilote_id INT,
    p1_temps VARCHAR(20),
    p2_pilote_id INT,
    p2_temps VARCHAR(20),
    p3_pilote_id INT,
    p3_temps VARCHAR(20),
    FOREIGN KEY (p1_pilote_id) REFERENCES pilotes(id) ON DELETE SET NULL,
    FOREIGN KEY (p2_pilote_id) REFERENCES pilotes(id) ON DELETE SET NULL,
    FOREIGN KEY (p3_pilote_id) REFERENCES pilotes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

TRUNCATE TABLE courses;
"""

# Extract the courses INSERT block from original file
# We want the values for 2025.
courses_match = re.search(r"INSERT INTO courses \(annee, round, nom, lieu, date_course, statut, p1_pilote_id, p1_temps, p2_pilote_id, p2_temps, p3_pilote_id, p3_temps\) VALUES(.*?);", sql_content, re.DOTALL)
if courses_match:
    courses_block = courses_match.group(0)
    new_sql += "\n" + courses_block + "\n"

# Extract the 2026 courses block
courses_match_2026 = re.search(r"INSERT INTO courses \(annee, round, nom, lieu, date_course, statut\) VALUES(.*?);", sql_content, re.DOTALL)
if courses_match_2026:
    courses_block_2026 = courses_match_2026.group(0)
    new_sql += "\n" + courses_block_2026 + "\n"

new_sql += """
CREATE TABLE IF NOT EXISTS resultats (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    pilote_id INT NOT NULL,
    position INT NOT NULL,
    temps VARCHAR(50),
    points INT DEFAULT 0,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (pilote_id) REFERENCES pilotes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
TRUNCATE TABLE resultats;
"""

# Extract resultats inserts from original file (races 1, 2, 3)
resultats_blocks = re.findall(r"INSERT INTO resultats \(course_id, pilote_id, position, temps, points\) VALUES(.*?);", sql_content, re.DOTALL)
for block in resultats_blocks:
    new_sql += f"\nINSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES{block};\n"

# Now, generate resultats data for Race 4 to 15 (which have podiums in `courses` but no insert into `resultats`)
import random
points_map = {1:25, 2:18, 3:15, 4:12, 5:10, 6:8, 7:6, 8:4, 9:2, 10:1}

completed_courses_match = re.search(r"INSERT INTO courses \(annee, round, nom, lieu, date_course, statut, p1_pilote_id, p1_temps, p2_pilote_id, p2_temps, p3_pilote_id, p3_temps\) VALUES(.*?);", sql_content, re.DOTALL)
if completed_courses_match:
    values_str = completed_courses_match.group(1)
    # Split by ),( to get individual rows approximately
    rows = values_str.split("),\n")
    cleaned_rows = []
    
    for i, row in enumerate(rows):
        # Clean up row string
        r = row.strip().replace("(", "").replace(")", "").replace("'", "")
        parts = [p.strip() for p in r.split(",")]
        
        # Parse essentials: round, p1_id, p2_id, p3_id
        # Format: 2025, round, nom, lieu, date, statut, p1, p1t, p2, p2t, p3, p3t
        # Indices: 0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11
        
        if len(parts) >= 12:
            try:
                race_round = int(parts[1])
                # Skip 1, 2, 3 as they are already in the file inserts
                if race_round <= 3:
                    continue
                    
                p1_id = int(parts[6])
                p2_id = int(parts[8])
                p3_id = int(parts[10])
                
                # Verify these IDs are valid ints (some might be NULL string)
                if not (p1_id and p2_id and p3_id): 
                    continue

                top3 = [p1_id, p2_id, p3_id]
                others = [pid for pid in range(1, 23) if pid not in top3] # 1 to 22 drivers
                random.shuffle(others)
                full_grid = top3 + others # First 3 are fixed podium

                # Generate INSERT
                for pos, driver_id in enumerate(full_grid, 1):
                    pts = points_map.get(pos, 0)
                    time_str = "1:30:00.000" if pos == 1 else "+15.000s" # Dummy time
                    new_sql += f"INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES ({race_round}, {driver_id}, {pos}, '{time_str}', {pts});\n"
                    
            except ValueError:
                pass # Skip malformed lines

with open('full_import.sql', 'w', encoding='utf-8') as f:
    f.write(new_sql)

print("Generated full_import.sql with mixed original and synthetic data.")
