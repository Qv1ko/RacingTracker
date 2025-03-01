-- Database
DROP DATABASE IF EXISTS racingtracker;
CREATE DATABASE racingtracker;

USE racingtracker;

SET NAMES latin1;
SET CHARACTER SET latin1_swedish_ci;

-- Tables
CREATE TABLE drivers (
  `id` BIGINT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(50) NOT NULL,
  `surname` VARCHAR(100) NOT NULL,
  `nationality` VARCHAR(100),
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARACTER SET latin1 COLLATE latin1_swedish_ci;

CREATE TABLE teams (
  `id` BIGINT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `nationality` VARCHAR(100),
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARACTER SET latin1 COLLATE latin1_swedish_ci;

CREATE TABLE races (
  `id` BIGINT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(100) NOT NULL,
  `date` DATE NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARACTER SET latin1 COLLATE latin1_swedish_ci;

CREATE TABLE participations (
  `id` BIGINT AUTO_INCREMENT NOT NULL,
  `driverId` BIGINT NOT NULL,
  `teamId` BIGINT DEFAULT NULL,
  `raceId` BIGINT NOT NULL,
  `position` VARCHAR(4) NOT NULL,
  `driverPoints` DECIMAL(8,3) NOT NULL DEFAULT 0,
  `uncertainty` DECIMAL(8,3) NOT NULL DEFAULT 0,
  `teamPoints` DECIMAL(8,3) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=INNODB CHARACTER SET latin1 COLLATE latin1_swedish_ci;

-- Unique constraints
ALTER TABLE drivers ADD UNIQUE (`name`, `surname`);
ALTER TABLE teams ADD UNIQUE (`name`);
ALTER TABLE races ADD UNIQUE (`name`, `date`);
ALTER TABLE participations ADD UNIQUE (`driverId`, `raceId`);

-- Foreign keys
ALTER TABLE participations 
  ADD CONSTRAINT `fk_participations_driver` FOREIGN KEY (`driverId`) REFERENCES `drivers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_participations_team` FOREIGN KEY (`teamId`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_participations_race` FOREIGN KEY (`raceId`) REFERENCES `races` (`id`) ON DELETE CASCADE;

-- Indexes
CREATE INDEX idx_drivers_nationality ON drivers (`nationality`);
CREATE INDEX idx_teams_nationality ON teams (`nationality`);
CREATE INDEX idx_races_date ON races (`date`);
CREATE INDEX idx_participations_driver ON participations (`driverId`);
CREATE INDEX idx_participations_race ON participations (`raceId`);
CREATE INDEX idx_participations_team ON participations (`teamId`);
CREATE INDEX idx_participations_position ON participations (`position`);

-- Inserts
INSERT INTO drivers (name, surname, nationality) VALUES
('Jean', 'Alesi', 'French'),
('Rubens', 'Barrichello', 'Brazilian'),
('Luciano', 'Burti', 'Brazilian'),
('Jenson', 'Button', 'British'),
('David', 'Coulthard', 'British'),
('Pedro', 'de la Rosa', 'Spanish'),
('Giancarlo', 'Fisichella', 'Italian'),
('Heinz-Harald', 'Frentzen', 'German'),
('Marc', 'Gené', 'Spanish'),
('Mika', 'Häkkinen', 'Finnish'),
('Nick', 'Heidfeld', 'German'),
('Johnny', 'Herbert', 'British'),
('Eddie', 'Irvine', 'British'),
('Gastón', 'Mazzacane', 'Argentine'),
('Mika', 'Salo', 'Finnish'),
('Michael', 'Schumacher', 'German'),
('Ralf', 'Schumacher', 'German'),
('Jarno', 'Trulli', 'Italian'),
('Jos', 'Verstappen', 'Dutch'),
('Jacques', 'Villeneuve', 'Canadian'),
('Alexander', 'Wurz', 'Austrian'),
('Ricardo', 'Zonta', 'Brazilian');

INSERT INTO teams (name, nationality) VALUES
('Arrows', 'British'),
('BAR', 'British'),
('Benetton', 'Italian'),
('Ferrari', 'Italian'),
('Jaguar', 'British'),
('Jordan', 'British'),
('McLaren', 'British'),
('Minardi', 'Italian'),
('Prost', 'French'),
('Sauber', 'Swiss'),
('Williams', 'British');
