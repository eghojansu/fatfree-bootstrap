--
-- @desc Insert default user
--

SET FOREIGN_KEY_CHECKS=0;

INSERT INTO user (nama, username, email, roles, password, created_at) VALUES("Eko Kurniawan", "faldev", "setiawanegho@gmail.com", "ROLE_DEVELOPER", "$2y$10$rV9b5TtOxFFXa9jRpfvV4uzA.I31LPL0Q8uDCPBVUbFkzFqoy5R/K", NOW());
INSERT INTO user (nama, username, email, roles, password, created_at) VALUES("Administrator", "admin", "admin@example.com", "ROLE_SUPER_ADMIN", "$2y$10$b7rdQ7VvmW3ceSn8RWfLZ.n1jAXuJrRPAN0Cp2DPJ34rkQBwFTUEC", NOW());

SET FOREIGN_KEY_CHECKS=1;
