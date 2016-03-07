/**
 * This file is part of eghojansu/Fatfree-bootstrap
 *
 * @author Eko Kurniawan <ekokurniawanbs@gmail.com>
 */

/** 
 * RBAC and user schema
 */

-- truncate if needed
/*
DELETE FROM `rbac_users`;
ALTER TABLE `rbac_users` AUTO_INCREMENT = 1;

DELETE FROM `rbac_roles`;
ALTER TABLE `rbac_roles` AUTO_INCREMENT = 1;

DELETE FROM `rbac_permissions`;
ALTER TABLE `rbac_permissions` AUTO_INCREMENT = 1;

DELETE FROM `rbac_users_roles`;
ALTER TABLE `rbac_users_roles` AUTO_INCREMENT = 1;

DELETE FROM `rbac_roles_permissions`;
ALTER TABLE `rbac_roles_permissions` AUTO_INCREMENT = 1;

DELETE FROM `profiles`;
ALTER TABLE `profiles` AUTO_INCREMENT = 1;
 */

-- Bcrypted password version, admin
INSERT INTO `rbac_users` (`user_id`, `username`, `password`) VALUES 
(1, 'suadmin', '$2y$10$PxroAPwSAeZpF1Xf3v4/ie65.3Cb/7M7kpQ4WLCkvvl1kaFSOOWoG'),
(2, 'admin', '$2y$10$PxroAPwSAeZpF1Xf3v4/ie65.3Cb/7M7kpQ4WLCkvvl1kaFSOOWoG');

INSERT INTO `rbac_roles` (`role_id`, `role_name`) VALUES 
(1, 'super admin'),
(2, 'admin');

INSERT INTO `rbac_permissions` (`permission_id`, `permission_name`) VALUES 
(1, 'create user'),
(2, 'read user'),
(3, 'update user'),
(4, 'delete user'),
(5, 'create role'),
(6, 'read role'),
(7, 'update role'),
(8, 'delete role'),
(9, 'create permission'),
(10, 'read permission'),
(11, 'update permission'),
(12, 'delete permission'),
(13, 'create role permission'),
(14, 'read role permission'),
(15, 'update role permission'),
(16, 'delete role permission'),
(17, 'create user role'),
(18, 'read user role'),
(19, 'update user role'),
(20, 'delete user role');

INSERT INTO `rbac_users_roles` (`user_id`, `role_id`) VALUES
(1, 1),
(2, 2);

INSERT INTO `rbac_roles_permissions` (`role_id`, `permission_id`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(1, 6),
(1, 7),
(1, 8),
(1, 9),
(1, 10),
(1, 11),
(1, 12),
(1, 13),
(1, 14),
(1, 15),
(1, 16),
(1, 17),
(1, 18),
(1, 19),
(1, 20),
(2, 1),
(2, 2),
(2, 3),
(2, 4),
(2, 17),
(2, 18),
(2, 19),
(2, 20);

INSERT INTO `profiles` (`profile_id`, `fullname`, `user_id`) VALUES 
(1, 'Super Administrator', 1),
(2, 'Administrator', 2);