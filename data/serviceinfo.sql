CREATE TABLE IF NOT EXISTS `serviceinfo` (
  `InvoiceID` int NOT NULL AUTO_INCREMENT,
  `Service_Type` varchar(80) NOT NULL,
  `Price` decimal(10,2) NOT NULL,
  `Cost` decimal(10,2) NOT NULL,
  `Client_Name` varchar(80) NOT NULL,
  `Date` date NOT NULL,
  PRIMARY KEY (`InvoiceID`),
  INDEX `idx_serviceinfo_date` (`Date`),
  INDEX `idx_serviceinfo_service_type` (`Service_Type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `serviceinfo` (`Service_Type`, `Price`, `Cost`, `Client_Name`, `Date`) VALUES
('Cabinet Refinishing', 2450.00, 860.00, 'Gage Park Kitchen Co.', '2025-01-17'),
('Interior Painting', 1850.00, 620.00, 'Aberdeen Residence', '2025-01-28'),
('Deck Restoration', 3200.00, 1125.00, 'Westdale Property Group', '2025-02-09'),
('Trim Installation', 1425.00, 510.00, 'Locke Street Homes', '2025-02-22'),
('Cabinet Refinishing', 2725.00, 940.00, 'Dundurn Renovations', '2025-03-06'),
('Exterior Painting', 4100.00, 1575.00, 'Stoney Creek Retail', '2025-03-18'),
('Drywall Repair', 980.00, 315.00, 'King Street Office', '2025-04-03'),
('Deck Restoration', 3650.00, 1280.00, 'Ancaster Backyard Studio', '2025-04-19'),
('Interior Painting', 2100.00, 700.00, 'Corktown Duplex', '2025-05-02');
