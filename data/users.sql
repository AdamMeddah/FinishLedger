CREATE TABLE IF NOT EXISTS `users` (
  `Id` int NOT NULL AUTO_INCREMENT,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`Id`),
  UNIQUE KEY `idx_users_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`firstName`, `lastName`, `email`, `password`) VALUES
('Demo', 'Manager', 'demo@example.com', '$2y$12$J1X8d0GybTVbBmyOgciF9eecjHi/FkV6UpNEyrj6oWG/H8LZMBGY2');
