-- Demo login examples kept as real database rows, not frontend mock data.
-- Passwords:
--   taaraa@clockit.com  / admin123
--   shaheed@clockit.com / staff123

INSERT INTO users (user_id, first_name, last_name, employee_id, role, is_active, email, password)
VALUES
  ('00000000-0000-4000-8000-000000000004', 'Taaraa', 'Admin', 'EMP004', 'admin', 1, 'taaraa@clockit.com', '$2y$12$JGOH7xMfCcNzZUSr/0aYi.BEpgrpJhy8EOl2ueSQGNYxVu6DVVYLq'),
  ('00000000-0000-4000-8000-000000000001', 'Shaheed', 'Staff', 'EMP001', 'staff', 1, 'shaheed@clockit.com', '$2y$12$had5p/EFXaHAC4WsJ0jX0uxqv6KCiWg6VuYsUMedAtnHzLjymwCN6')
ON DUPLICATE KEY UPDATE
  first_name = VALUES(first_name),
  last_name = VALUES(last_name),
  role = VALUES(role),
  is_active = VALUES(is_active),
  password = VALUES(password);
