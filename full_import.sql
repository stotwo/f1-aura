SET FOREIGN_KEY_CHECKS=0;
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
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 3, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 7, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 6, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 16, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 12, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 13, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 1, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 10, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 2, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 19, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 15, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 4, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 21, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 5, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 20, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 18, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 22, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 17, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 9, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 11, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (4, 14, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 1, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 5, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 18, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 2, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 11, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 3, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 15, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 17, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 19, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 16, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 22, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 10, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 21, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 7, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 20, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 12, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 13, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 4, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 9, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 14, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (5, 6, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 7, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 3, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 18, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 22, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 21, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 20, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 10, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 1, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 17, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 11, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 5, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 19, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 9, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 2, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 15, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 12, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 14, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 4, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 13, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 16, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (6, 6, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 1, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 7, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 8, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 21, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 20, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 10, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 11, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 3, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 4, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 14, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 12, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 18, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 22, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 15, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 2, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 16, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 5, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 19, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 13, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 17, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 6, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (7, 9, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 7, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 5, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 8, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 17, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 13, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 20, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 11, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 19, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 14, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 9, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 3, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 16, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 10, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 18, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 15, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 6, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 4, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 21, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 2, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 12, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 1, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (8, 22, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 7, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 5, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 12, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 3, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 14, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 9, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 21, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 20, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 15, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 19, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 10, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 6, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 11, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 16, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 13, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 22, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 18, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 17, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 1, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 4, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (9, 2, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 3, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 1, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 4, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 17, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 16, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 12, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 5, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 18, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 6, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 22, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 11, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 19, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 10, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 21, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 7, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 2, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 20, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 8, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 9, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 15, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 13, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (10, 14, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 7, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 8, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 5, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 2, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 6, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 14, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 15, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 3, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 21, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 11, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 16, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 1, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 22, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 12, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 13, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 10, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 17, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 4, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 18, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 9, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 19, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (11, 20, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 7, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 8, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 17, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 16, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 12, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 22, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 15, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 14, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 2, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 20, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 10, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 6, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 13, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 3, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 19, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 21, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 5, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 11, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 9, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 4, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 18, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (12, 1, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 7, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 5, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 15, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 6, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 14, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 1, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 22, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 20, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 16, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 4, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 13, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 17, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 3, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 2, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 21, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 18, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 9, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 19, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 11, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 12, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (13, 10, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 7, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 8, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 3, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 17, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 16, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 21, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 1, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 11, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 14, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 6, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 9, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 4, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 12, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 5, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 10, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 2, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 18, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 15, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 19, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 20, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 22, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (14, 13, 22, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 8, 1, '1:30:00.000', 25);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 1, 2, '+15.000s', 18);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 2, 3, '+15.000s', 15);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 11, 4, '+15.000s', 12);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 20, 5, '+15.000s', 10);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 6, 6, '+15.000s', 8);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 13, 7, '+15.000s', 6);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 10, 8, '+15.000s', 4);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 3, 9, '+15.000s', 2);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 16, 10, '+15.000s', 1);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 21, 11, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 19, 12, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 9, 13, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 17, 14, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 7, 15, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 18, 16, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 4, 17, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 15, 18, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 5, 19, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 22, 20, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 12, 21, '+15.000s', 0);
INSERT INTO resultats (course_id, pilote_id, position, temps, points) VALUES (15, 14, 22, '+15.000s', 0);
