-- Update Takeaway Types Menu Icon
-- Run this SQL in your database (phpMyAdmin, MySQL Workbench, etc.)

UPDATE menus 
SET icon = 'lab lab-bag-line', 
    updated_at = NOW() 
WHERE language = 'takeaway_types' 
  AND url = 'takeaway-types';

-- Also update any takeaway_types menu with empty/null icon
UPDATE menus 
SET icon = 'lab lab-bag-line', 
    updated_at = NOW() 
WHERE language = 'takeaway_types' 
  AND (icon IS NULL OR icon = '' OR icon = ' ');



