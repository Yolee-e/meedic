-- --------------------------------------------------------
--
-- Table structure for table `prescription_files`
--
-- This table stores uploaded files for prescriptions (lab results, diagnostic images, etc.)
-- NOTE: This table was missing from main database schema and only created dynamically
-- Fixed to ensure proper database export/import functionality
--

CREATE TABLE `prescription_files` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `prescription_id` int(11) NOT NULL COMMENT 'Reference to prescriptions table',
  `patient_id` int(11) NOT NULL COMMENT 'Reference to patients table',
  `stored_name` varchar(255) NOT NULL COMMENT 'Filename as stored on server',
  `original_name` varchar(255) NOT NULL COMMENT 'Original filename uploaded by user',
  `mime_type` varchar(100) DEFAULT NULL COMMENT 'MIME type for proper file serving',
  `file_size` int(11) DEFAULT NULL COMMENT 'File size in bytes',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'When file was uploaded',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'When file record was last updated',
  PRIMARY KEY (`id`),
  KEY `idx_prescription_files_prescription` (`prescription_id`),
  KEY `idx_prescription_files_patient` (`patient_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Constraints for table `prescription_files`
--
ALTER TABLE `prescription_files`
  ADD CONSTRAINT `fk_prescription_files_prescription_id` FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_prescription_files_patient_id` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Add foreign key constraints to existing tables if they don't reference prescription_files
-- (This ensures proper referential integrity)
--

-- Optional: Add file_size column if not exists (for better file management)
-- ALTER TABLE `prescription_files` ADD COLUMN `file_size` INT(11) DEFAULT NULL COMMENT 'File size in bytes' AFTER `mime_type`;
