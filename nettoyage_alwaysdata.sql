-- Script de nettoyage pour Alwaysdata
-- À exécuter dans l'onglet SQL de votre PHPMyAdmin

-- 1. Supprimer les écuries dont le nom est uniquement des chiffres
DELETE FROM ecuries WHERE nom REGEXP '^[0-9]+$';

-- 2. Supprimer les écuries avec des noms trop courts (erreurs de scraping)
DELETE FROM ecuries WHERE LENGTH(nom) <= 2;

-- Après ceci, seules les vraies écuries resteront.
-- Si les points sont toujours faux, c'est qu'il faut réimporter la table 'resultats' depuis votre version locale.
