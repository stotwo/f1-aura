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
    PRIMARY KEY (user_id, pilotes_id),
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
('Red Bull Racing', 'Autriche', '#1E41FF', 'https://upload.wikimedia.org/wikipedia/en/thumb/c/c4/Oracle_Red_Bull_Racing_logo.svg/1200px-Oracle_Red_Bull_Racing_logo.svg.png'),
('Mercedes-AMG Petronas', 'Allemagne', '#00D2BE', 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/fb/Mercedes_AMG_Petronas_F1_Logo.svg/1200px-Mercedes_AMG_Petronas_F1_Logo.svg.png'),
('Scuderia Ferrari', 'Italie', '#DC0000', 'https://upload.wikimedia.org/wikipedia/en/thumb/d/d1/Ferrari-Logo.svg/1200px-Ferrari-Logo.svg.png'),
('McLaren', 'Royaume-Uni', '#FF8700', 'https://upload.wikimedia.org/wikipedia/en/thumb/6/66/McLaren_Racing_logo.svg/1200px-McLaren_Racing_logo.svg.png'),
('Aston Martin', 'Royaume-Uni', '#006F62', 'https://upload.wikimedia.org/wikipedia/fr/thumb/7/72/Aston_Martin_Aramco_Cognizant_F1.svg/1200px-Aston_Martin_Aramco_Cognizant_F1.svg.png'),
('Alpine', 'France', '#0090FF', 'https://upload.wikimedia.org/wikipedia/fr/thumb/b/b7/Alpine_F1_Team_2021_Logo.svg/1200px-Alpine_F1_Team_2021_Logo.svg.png'),
('Williams', 'Royaume-Uni', '#005AFF', 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f2/Williams_Racing_2020_Logo.svg/1200px-Williams_Racing_2020_Logo.svg.png'),
('Alfa Romeo', 'Suisse', '#900000', 'https://upload.wikimedia.org/wikipedia/commons/thumb/f/f9/Sauber_F1_Team_logo_2024.png/800px-Sauber_F1_Team_logo_2024.png'),
('Haas', 'États-Unis', '#FFFFFF', 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d4/Logo_Haas_F1.png/1200px-Logo_Haas_F1.png'),
('AlphaTauri', 'Italie', '#2B4562', 'https://upload.wikimedia.org/wikipedia/en/thumb/5/52/RB_F1_Team_Logo.svg/1200px-RB_F1_Team_Logo.svg.png');

INSERT INTO pilotes (nom, prenom, numero, nationalite, ecurie_id, image_url) VALUES
('Verstappen', 'Max', 1, 'Pays-Bas', 1, 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7d/Max_Verstappen_2024_China.jpg/640px-Max_Verstappen_2024_China.jpg'),
('Pérez', 'Sergio', 11, 'Mexique', 1, 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/bd/Sergio_Perez_2024_China.jpg/640px-Sergio_Perez_2024_China.jpg'),
('Hamilton', 'Lewis', 44, 'Royaume-Uni', 2, 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/18/Lewis_Hamilton_2024_China.jpg/640px-Lewis_Hamilton_2024_China.jpg'),
('Russell', 'George', 63, 'Royaume-Uni', 2, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4a/George_Russell_2024_China.jpg/640px-George_Russell_2024_China.jpg'),
('Leclerc', 'Charles', 16, 'Monaco', 3, 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a3/Charles_Leclerc_2024_China.jpg/640px-Charles_Leclerc_2024_China.jpg'),
('Sainz', 'Carlos', 55, 'Espagne', 3, 'https://upload.wikimedia.org/wikipedia/commons/thumb/2/2b/Carlos_Sainz_Jr_2024_China.jpg/640px-Carlos_Sainz_Jr_2024_China.jpg'),
('Norris', 'Lando', 4, 'Royaume-Uni', 4, 'https://upload.wikimedia.org/wikipedia/commons/thumb/0/08/Lando_Norris_2024_China.jpg/640px-Lando_Norris_2024_China.jpg'),
('Piastri', 'Oscar', 81, 'Australie', 4, 'https://upload.wikimedia.org/wikipedia/commons/thumb/1/15/Oscar_Piastri_2024_China.jpg/640px-Oscar_Piastri_2024_China.jpg'),
('Alonso', 'Fernando', 14, 'Espagne', 5, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/61/Fernando_Alonso_2024_China.jpg/640px-Fernando_Alonso_2024_China.jpg'),
('Stroll', 'Lance', 18, 'Canada', 5, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d6/Lance_Stroll_2024_China.jpg/640px-Lance_Stroll_2024_China.jpg'),
('Gasly', 'Pierre', 10, 'France', 6, 'https://upload.wikimedia.org/wikipedia/commons/thumb/3/36/Pierre_Gasly_2024_China.jpg/640px-Pierre_Gasly_2024_China.jpg'),
('Ocon', 'Esteban', 31, 'France', 6, 'https://upload.wikimedia.org/wikipedia/commons/thumb/8/87/Esteban_Ocon_2024_China.jpg/640px-Esteban_Ocon_2024_China.jpg'),
('Albon', 'Alexander', 23, 'Thaïlande', 7, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/46/Alex_Albon_2024_China.jpg/640px-Alex_Albon_2024_China.jpg'),
('Sargeant', 'Logan', 2, 'États-Unis', 7, 'https://upload.wikimedia.org/wikipedia/commons/thumb/4/4f/Logan_Sargeant_2024_China.jpg/640px-Logan_Sargeant_2024_China.jpg'),
('Bottas', 'Valtteri', 77, 'Finlande', 8, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/62/Valtteri_Bottas_2024_China.jpg/640px-Valtteri_Bottas_2024_China.jpg'),
('Zhou', 'Guanyu', 24, 'Chine', 8, 'https://upload.wikimedia.org/wikipedia/commons/thumb/d/d2/Zhou_Guanyu_2024_China.jpg/640px-Zhou_Guanyu_2024_China.jpg'),
('Magnussen', 'Kevin', 20, 'Danemark', 9, 'https://upload.wikimedia.org/wikipedia/commons/thumb/a/a0/Kevin_Magnussen_2024_China.jpg/640px-Kevin_Magnussen_2024_China.jpg'),
('Hülkenberg', 'Nico', 27, 'Allemagne', 9, 'https://upload.wikimedia.org/wikipedia/commons/thumb/6/68/Nico_Hulkenberg_2024_China.jpg/640px-Nico_Hulkenberg_2024_China.jpg'),
('Tsunoda', 'Yuki', 22, 'Japon', 10, 'https://upload.wikimedia.org/wikipedia/commons/thumb/7/7e/Yuki_Tsunoda_2024_China.jpg/640px-Yuki_Tsunoda_2024_China.jpg'),
('Ricciardo', 'Daniel', 3, 'Australie', 10, 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b3/Daniel_Ricciardo_2024_China.jpg/640px-Daniel_Ricciardo_2024_China.jpg');

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

INSERT INTO courses (annee, round, nom, lieu, date_course, statut, p1_pilote_id, p1_temps, p2_pilote_id, p2_temps, p3_pilote_id, p3_temps) VALUES
(2025, 1, 'Grand Prix d\'Australie', 'Albert Park', '2025-03-16', 'Terminé', 7, '1:42:06.304', 1, '+0.895s', 3, '+8.481s'),
(2025, 2, 'Grand Prix de Chine', 'Shanghai', '2025-03-23', 'Terminé', 8, '1:30:55.026', 7, '+9.748s', 3, '+11.097s'),
(2025, 3, 'Grand Prix du Japon', 'Suzuka', '2025-04-06', 'Terminé', 1, '1:22:06.983', 7, '+1.423s', 8, '+2.129s'),
(2025, 4, 'Grand Prix de Bahreïn', 'Sakhir', '2025-04-13', 'Terminé', 8, '1:35:39.435', 3, '+15.499s', 7, '+16.273s'),
(2025, 5, 'Grand Prix d\'Arabie Saoudite', 'Djeddah', '2025-04-20', 'Terminé', 8, '1:21:06.758', 1, '+2.843s', 5, '+8.104s'),
(2025, 6, 'Grand Prix de Miami', 'Miami', '2025-05-04', 'Terminé', 8, '1:28:51.587', 7, '+4.630s', 3, '+37.644s'),
(2025, 7, 'Grand Prix d\'Émilie-Romagne', 'Imola', '2025-05-18', 'Terminé', 1, '1:31:33.199', 7, '+6.109s', 8, '+12.956s'),
(2025, 8, 'Grand Prix de Monaco', 'Monaco', '2025-05-25', 'Terminé', 7, '1:40:33.843', 5, '+3.131s', 8, '+3.658s'),
(2025, 9, 'Grand Prix d\'Espagne', 'Barcelone', '2025-06-01', 'Terminé', 8, '1:32:57.375', 7, '+2.471s', 5, '+10.455s'),
(2025, 10, 'Grand Prix du Canada', 'Montréal', '2025-06-15', 'Terminé', 3, '1:31:52.688', 1, '+0.228s', 4, '+1.014s'),
(2025, 11, 'Grand Prix d\'Autriche', 'Red Bull Ring', '2025-06-29', 'Terminé', 7, '1:23:47.693', 8, '+2.695s', 5, '+19.820s'),
(2025, 12, 'Grand Prix de Grande-Bretagne', 'Silverstone', '2025-07-06', 'Terminé', 7, '1:37:15.735', 8, '+6.812s', 17, '+34.742s'),
(2025, 13, 'Grand Prix de Belgique', 'Spa-Francorchamps', '2025-07-27', 'Terminé', 8, '1:25:22.601', 7, '+3.415s', 5, '+20.185s'),
(2025, 14, 'Grand Prix de Hongrie', 'Hungaroring', '2025-08-03', 'Terminé', 7, '1:35:21.231', 8, '+0.698s', 3, '+21.916s'),
(2025, 15, 'Grand Prix des Pays-Bas', 'Zandvoort', '2025-08-31', 'Terminé', 8, '1:38:29.849', 1, '+1.271s', 2, '+3.233s'),
(2025, 16, 'Grand Prix d\'Italie', 'Monza', '2025-09-07', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 17, 'Grand Prix d\'Azerbaïdjan', 'Bakou', '2025-09-21', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 18, 'Grand Prix de Singapour', 'Marina Bay', '2025-10-05', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 19, 'Grand Prix des États-Unis', 'Austin', '2025-10-19', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 20, 'Grand Prix du Mexique', 'Mexico', '2025-10-26', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 21, 'Grand Prix du Brésil', 'Interlagos', '2025-11-09', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 22, 'Grand Prix de Las Vegas', 'Las Vegas', '2025-11-22', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 23, 'Grand Prix du Qatar', 'Lusail', '2025-11-30', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL),
(2025, 24, 'Grand Prix d\'Abou Dabi', 'Yas Marina', '2025-12-07', 'À venir', NULL, NULL, NULL, NULL, NULL, NULL);

INSERT INTO courses (annee, round, nom, lieu, date_course, statut) VALUES
(2026, 1, 'Grand Prix d\'Australie', 'Albert Park', '2026-03-08', 'À venir'),
(2026, 2, 'Grand Prix de Chine', 'Shanghai', '2026-03-15', 'À venir'),
(2026, 3, 'Grand Prix du Japon', 'Suzuka', '2026-03-29', 'À venir'),
(2026, 4, 'Grand Prix de Bahreïn', 'Sakhir', '2026-04-12', 'À venir'),
(2026, 5, 'Grand Prix d\'Arabie Saoudite', 'Djeddah', '2026-04-19', 'À venir'),
(2026, 6, 'Grand Prix de Miami', 'Miami', '2026-05-03', 'À venir'),
(2026, 7, 'Grand Prix du Canada', 'Montréal', '2026-05-24', 'À venir'),
(2026, 8, 'Grand Prix de Monaco', 'Monaco', '2026-06-07', 'À venir'),
(2026, 9, 'Grand Prix d\'Espagne', 'Barcelone', '2026-06-14', 'À venir'),
(2026, 10, 'Grand Prix d\'Autriche', 'Red Bull Ring', '2026-06-28', 'À venir'),
(2026, 11, 'Grand Prix de Grande-Bretagne', 'Silverstone', '2026-07-05', 'À venir'),
(2026, 12, 'Grand Prix de Belgique', 'Spa-Francorchamps', '2026-07-19', 'À venir'),
(2026, 13, 'Grand Prix de Hongrie', 'Hungaroring', '2026-07-26', 'À venir'),
(2026, 14, 'Grand Prix des Pays-Bas', 'Zandvoort', '2026-08-23', 'À venir'),
(2026, 15, 'Grand Prix d\'Italie', 'Monza', '2026-09-06', 'À venir'),
(2026, 16, 'Grand Prix d\'Espagne (Madrid?)', 'Madrid', '2026-09-13', 'À venir'),
(2026, 17, 'Grand Prix d\'Azerbaïdjan', 'Bakou', '2026-09-26', 'À venir'),
(2026, 18, 'Grand Prix de Singapour', 'Marina Bay', '2026-10-11', 'À venir'),
(2026, 19, 'Grand Prix des États-Unis', 'Austin', '2026-10-25', 'À venir'),
(2026, 20, 'Grand Prix du Mexique', 'Mexico', '2026-11-01', 'À venir'),
(2026, 21, 'Grand Prix du Brésil', 'Interlagos', '2026-11-08', 'À venir'),
(2026, 22, 'Grand Prix de Las Vegas', 'Las Vegas', '2026-11-21', 'À venir'),
(2026, 23, 'Grand Prix du Qatar', 'Lusail', '2026-11-29', 'À venir'),
(2026, 24, 'Grand Prix d\'Abou Dabi', 'Yas Marina', '2026-12-06', 'À venir');

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
INSERT IGNORE INTO pilotes (nom, prenom, ecurie_id, image_url)
SELECT 'Tsunoda', 'Yuki', id, 'PICS/drivers/tsunoda.png'
FROM ecuries WHERE nom LIKE '%Visa%' OR nom LIKE '%RB%' LIMIT 1;
INSERT IGNORE INTO pilotes (nom, prenom, ecurie_id, image_url)
SELECT 'Doohan', 'Jack', id, 'PICS/drivers/doohan.png'
FROM ecuries WHERE nom LIKE '%Alpine%' LIMIT 1;
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), 1, '1:42:06.304', 25),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Verstappen%' LIMIT 1), 2, '+0.895s', 18),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Russell%' LIMIT 1), 3, '+8.481s', 15),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Antonelli%' LIMIT 1), 4, '+11.234s', 12),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Albon%' LIMIT 1), 5, '+15.678s', 10),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Stroll%' LIMIT 1), 6, '+18.901s', 8),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'H%lkenberg%' LIMIT 1), 7, '+22.345s', 6),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Leclerc%' LIMIT 1), 8, '+25.678s', 4),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Piastri%' LIMIT 1), 9, '+28.901s', 2),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Hamilton%' LIMIT 1), 10, '+32.123s', 1),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Gasly%' LIMIT 1), 11, '+35.456s', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Tsunoda%' LIMIT 1), 12, '+40.789s', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Ocon%' LIMIT 1), 13, '+45.012s', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Bearman%' LIMIT 1), 14, '+50.234s', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Lawson%' LIMIT 1), 15, 'DNF', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Bortoleto%' LIMIT 1), 16, 'DNF', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Alonso%' LIMIT 1), 17, 'DNF', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Sainz%' LIMIT 1), 18, 'DNF', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Doohan%' LIMIT 1), 19, 'DNF', 0),
(1, (SELECT id FROM pilotes WHERE nom LIKE 'Hadjar%' LIMIT 1), 20, 'DNF', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Piastri%' LIMIT 1), 1, '1:30:55.026', 25),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), 2, '+9.748s', 18),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Russell%' LIMIT 1), 3, '+11.097s', 15),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Verstappen%' LIMIT 1), 4, '+16.656s', 12),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Ocon%' LIMIT 1), 5, '+49.969s', 10),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Antonelli%' LIMIT 1), 6, '+53.748s', 8),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Albon%' LIMIT 1), 7, '+56.321s', 6),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Bearman%' LIMIT 1), 8, '+61.303s', 4),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Stroll%' LIMIT 1), 9, '+70.204s', 2),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Sainz%' LIMIT 1), 10, '+76.387s', 1),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Hadjar%' LIMIT 1), 11, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Lawson%' LIMIT 1), 12, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Doohan%' LIMIT 1), 13, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Bortoleto%' LIMIT 1), 14, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'H%lkenberg%' LIMIT 1), 15, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Tsunoda%' LIMIT 1), 16, '+1 tour', 0),
(2, (SELECT id FROM pilotes WHERE nom LIKE 'Alonso%' LIMIT 1), 17, 'DNF', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Verstappen%' LIMIT 1), 1, '1:22:06.983', 25),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), 2, '+1.423s', 18),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Piastri%' LIMIT 1), 3, '+2.129s', 15),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Leclerc%' LIMIT 1), 4, '+16.097s', 12),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Russell%' LIMIT 1), 5, '+17.362s', 10),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Antonelli%' LIMIT 1), 6, '+18.600s', 8),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Hamilton%' LIMIT 1), 7, '+29.100s', 6),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Hadjar%' LIMIT 1), 8, '+37.100s', 4),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Albon%' LIMIT 1), 9, '+40.300s', 2),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Bearman%' LIMIT 1), 10, '+54.500s', 1),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Alonso%' LIMIT 1), 11, '+57.300s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Tsunoda%' LIMIT 1), 12, '+58.400s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Gasly%' LIMIT 1), 13, '+62.100s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Sainz%' LIMIT 1), 14, '+74.100s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Doohan%' LIMIT 1), 15, '+81.300s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'H%lkenberg%' LIMIT 1), 16, '+81.900s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Lawson%' LIMIT 1), 17, '+82.700s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Ocon%' LIMIT 1), 18, '+83.400s', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'Bortoleto%' LIMIT 1), 19, 'DNF', 0),
(3, (SELECT id FROM pilotes WHERE nom LIKE 'P%rez%' LIMIT 1), 20, 'DNF', 0);
UPDATE courses SET
    p1_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), p1_temps = '1:42:06.304',
    p2_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Verstappen%' LIMIT 1), p2_temps = '+0.895s',
    p3_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Russell%' LIMIT 1), p3_temps = '+8.481s'
WHERE id = 1;
UPDATE courses SET
    p1_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Piastri%' LIMIT 1), p1_temps = '1:30:55.026',
    p2_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), p2_temps = '+9.748s',
    p3_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Russell%' LIMIT 1), p3_temps = '+11.097s'
WHERE id = 2;
UPDATE courses SET
    p1_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Verstappen%' LIMIT 1), p1_temps = '1:22:06.983',
    p2_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Norris%' LIMIT 1), p2_temps = '+1.423s',
    p3_pilote_id = (SELECT id FROM pilotes WHERE nom LIKE 'Piastri%' LIMIT 1), p3_temps = '+2.129s'
WHERE id = 3;