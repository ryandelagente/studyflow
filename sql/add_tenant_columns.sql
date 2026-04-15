-- Migration: Add tenant_id to flashcards and goal_categories
-- Run in phpMyAdmin on the productivity_hub database

-- 1. Add tenant_id to flashcards (derived from parent collection)
ALTER TABLE `flashcards`
    ADD COLUMN `tenant_id` INT NOT NULL DEFAULT 0 AFTER `collection_id`;

-- Backfill from parent collection
UPDATE `flashcards` f
JOIN `flashcard_collections` fc ON f.collection_id = fc.id
SET f.tenant_id = fc.tenant_id;

-- 2. Add tenant_id to goal_categories
ALTER TABLE `goal_categories`
    ADD COLUMN `tenant_id` INT NOT NULL DEFAULT 0 AFTER `user_id`;

-- 3. Add directory link to sidebar (no SQL needed — PHP only)
-- Done in sidebar.php
