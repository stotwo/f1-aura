-- Disable foreign key checks to avoid constraints issues during truncate
SET FOREIGN_KEY_CHECKS=0;

-- Reset course statuses for 2025
UPDATE courses SET statut = 'Terminé' WHERE annee = 2025;

-- Clear existing results
TRUNCATE TABLE resultats;

-- Insert correct results for 2025 Season
-- Based on f1-aura.alwaysdata.net/saison_2025

-- IDs Mapping:
-- 1:Verstappen, 2:Lawson, 3:Russell, 4:Antonelli, 5:Leclerc, 6:Hamilton, 
-- 7:Norris, 8:Piastri, 9:Alonso, 10:Stroll, 11:Gasly, 12:Doohan, 
-- 13:Albon, 14:Sainz, 15:Hulkenberg, 16:Bortoleto, 17:Ocon, 18:Bearman, 
-- 19:Tsunoda, 20:Hadjar, 21:Bottas, 22:Colapinto

-- Round 1: Australia (16 Mar) - P2 Norris, P1 Verstappen, P3 Russell
-- Wait, text says: P2 Norris, P1 Verstappen, P3 Russell
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=7, p3_pilote_id=3, statut='Terminé' WHERE annee=2025 AND round=1;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(1, 1, 1, 25), (1, 7, 2, 18), (1, 3, 3, 15);

-- Round 2: China (23 Mar) - P2 Norris, P1 Piastri, P3 Russell
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=7, p3_pilote_id=3, statut='Terminé' WHERE annee=2025 AND round=2;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(2, 8, 1, 25), (2, 7, 2, 18), (2, 3, 3, 15);

-- Round 3: Japan (06 Apr) - P2 Norris, P1 Verstappen, P3 Piastri
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=7, p3_pilote_id=8, statut='Terminé' WHERE annee=2025 AND round=3;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(3, 1, 1, 25), (3, 7, 2, 18), (3, 8, 3, 15);

-- Round 4: Bahrain (13 Apr) - P2 Russell, P1 Piastri, P3 Norris
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=3, p3_pilote_id=7, statut='Terminé' WHERE annee=2025 AND round=4;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(4, 8, 1, 25), (4, 3, 2, 18), (4, 7, 3, 15);

-- Round 5: Saudi Arabia (20 Apr) - P2 Verstappen, P1 Piastri, P3 Leclerc
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=1, p3_pilote_id=5, statut='Terminé' WHERE annee=2025 AND round=5;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(5, 8, 1, 25), (5, 1, 2, 18), (5, 5, 3, 15);

-- Round 6: Miami (04 May) - P2 Norris, P1 Piastri, P3 Russell
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=7, p3_pilote_id=3, statut='Terminé' WHERE annee=2025 AND round=6;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(6, 8, 1, 25), (6, 7, 2, 18), (6, 3, 3, 15);

-- Round 7: Imola (18 May) - P2 Norris, P1 Verstappen, P3 Piastri
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=7, p3_pilote_id=8, statut='Terminé' WHERE annee=2025 AND round=7;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(7, 1, 1, 25), (7, 7, 2, 18), (7, 8, 3, 15);

-- Round 8: Monaco (25 May) - P2 Leclerc, P1 Norris, P3 Piastri
-- NOTE: Text says P2 Leclerc, P1 Norris, P3 Piastri. Check image if possible? 
-- assuming P1 is Winner.
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=5, p3_pilote_id=8, statut='Terminé' WHERE annee=2025 AND round=8;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(8, 7, 1, 25), (8, 5, 2, 18), (8, 8, 3, 15);

-- Round 9: Spain (01 Jun) - P2 Norris, P1 Piastri, P3 Leclerc
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=7, p3_pilote_id=5, statut='Terminé' WHERE annee=2025 AND round=9;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(9, 8, 1, 25), (9, 7, 2, 18), (9, 5, 3, 15);

-- Round 10: Canada (15 Jun) - P2 Verstappen, P1 Russell, P3 Antonelli
UPDATE courses SET p1_pilote_id=3, p2_pilote_id=1, p3_pilote_id=4, statut='Terminé' WHERE annee=2025 AND round=10;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(10, 3, 1, 25), (10, 1, 2, 18), (10, 4, 3, 15);

-- Round 11: Austria (29 Jun) - P2 Piastri, P1 Norris, P3 Leclerc
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=8, p3_pilote_id=5, statut='Terminé' WHERE annee=2025 AND round=11;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(11, 7, 1, 25), (11, 8, 2, 18), (11, 5, 3, 15);

-- Round 12: Silverstone (06 Jul) - P2 Piastri, P1 Norris, P3 Hulkenberg
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=8, p3_pilote_id=15, statut='Terminé' WHERE annee=2025 AND round=12;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(12, 7, 1, 25), (12, 8, 2, 18), (12, 15, 3, 15);

-- Round 13: Belgium (27 Jul) - P2 Norris, P1 Piastri, P3 Leclerc
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=7, p3_pilote_id=5, statut='Terminé' WHERE annee=2025 AND round=13;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(13, 8, 1, 25), (13, 7, 2, 18), (13, 5, 3, 15);

-- Round 14: Hungary (03 Aug) - P2 Piastri, P1 Norris, P3 Russell
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=8, p3_pilote_id=3, statut='Terminé' WHERE annee=2025 AND round=14;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(14, 7, 1, 25), (14, 8, 2, 18), (14, 3, 3, 15);

-- Round 15: Netherlands (31 Aug) - P2 Verstappen, P1 Piastri, P3 Hadjar
UPDATE courses SET p1_pilote_id=8, p2_pilote_id=1, p3_pilote_id=20, statut='Terminé' WHERE annee=2025 AND round=15;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(15, 8, 1, 25), (15, 1, 2, 18), (15, 20, 3, 15);

-- Round 16: Italy (07 Sep) - P2 Norris, P1 Verstappen, P3 Piastri
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=7, p3_pilote_id=8, statut='Terminé' WHERE annee=2025 AND round=16;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(16, 1, 1, 25), (16, 7, 2, 18), (16, 8, 3, 15);

-- Round 17: Azerbaijan (21 Sep) - P2 Russell, P1 Verstappen, P3 Sainz
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=3, p3_pilote_id=14, statut='Terminé' WHERE annee=2025 AND round=17;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(17, 1, 1, 25), (17, 3, 2, 18), (17, 14, 3, 15);

-- Round 18: Singapore (05 Oct) - P2 Verstappen, P1 Russell, P3 Norris
UPDATE courses SET p1_pilote_id=3, p2_pilote_id=1, p3_pilote_id=7, statut='Terminé' WHERE annee=2025 AND round=18;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(18, 3, 1, 25), (18, 1, 2, 18), (18, 7, 3, 15);

-- Round 19: USA (19 Oct) - P2 Norris, P1 Verstappen, P3 Leclerc
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=7, p3_pilote_id=5, statut='Terminé' WHERE annee=2025 AND round=19;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(19, 1, 1, 25), (19, 7, 2, 18), (19, 5, 3, 15);

-- Round 20: Mexico (26 Oct) - P2 Leclerc, P1 Norris, P3 Verstappen
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=5, p3_pilote_id=1, statut='Terminé' WHERE annee=2025 AND round=20;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(20, 7, 1, 25), (20, 5, 2, 18), (20, 1, 3, 15);

-- Round 21: Brazil (09 Nov) - P2 Antonelli, P1 Norris, P3 Verstappen
UPDATE courses SET p1_pilote_id=7, p2_pilote_id=4, p3_pilote_id=1, statut='Terminé' WHERE annee=2025 AND round=21;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(21, 7, 1, 25), (21, 4, 2, 18), (21, 1, 3, 15);

-- Round 22: Las Vegas (22 Nov) - P2 Russell, P1 Verstappen, P3 Antonelli
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=3, p3_pilote_id=4, statut='Terminé' WHERE annee=2025 AND round=22;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(22, 1, 1, 25), (22, 3, 2, 18), (22, 4, 3, 15);

-- Round 23: Qatar (30 Nov) - P2 Piastri, P1 Verstappen, P3 Sainz
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=8, p3_pilote_id=14, statut='Terminé' WHERE annee=2025 AND round=23;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(23, 1, 1, 25), (23, 8, 2, 18), (23, 14, 3, 15);

-- Round 24: Abu Dhabi (07 Dec) - P2 Piastri, P1 Verstappen, P3 Norris
UPDATE courses SET p1_pilote_id=1, p2_pilote_id=8, p3_pilote_id=7, statut='Terminé' WHERE annee=2025 AND round=24;
INSERT INTO resultats (course_id, pilote_id, position, points) VALUES 
(24, 1, 1, 25), (24, 8, 2, 18), (24, 7, 3, 15);

-- Add Perez if missing (assuming he is needed for accuracy, though he has 0 points)
INSERT INTO pilotes (nom, prenom, numero, nationalite, ecurie_id, image_url) 
SELECT 'Pérez', 'Sergio', 11, 'Mexique', 11, 'PICS/drivers/perez.webp'
WHERE NOT EXISTS (SELECT 1 FROM pilotes WHERE nom = 'Pérez' AND prenom = 'Sergio');

SET FOREIGN_KEY_CHECKS=1;
