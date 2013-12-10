--
-- miscellaneous mods
--

-- Leif has dropped the user timezone field.
ALTER TABLE user_settings 
      DROP COLUMN user_tz;


