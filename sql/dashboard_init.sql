-- sql/dashboard_init.sql

-- 1) Secuencias
CREATE TABLE IF NOT EXISTS Secuencias (
  nombre VARCHAR(50) PRIMARY KEY,
  ultimo INT NOT NULL DEFAULT 0
);

INSERT INTO Secuencias (nombre, ultimo) VALUES ('prestamo', 0) ON DUPLICATE KEY UPDATE nombre = nombre;
INSERT INTO Secuencias (nombre, ultimo) VALUES ('herramienta', 0) ON DUPLICATE KEY UPDATE nombre = nombre;
INSERT INTO Secuencias (nombre, ultimo) VALUES ('usuario', 0) ON DUPLICATE KEY UPDATE nombre = nombre;

-- 2) TRIGGERS (pruebalos en staging primero)

DELIMITER $$
CREATE TRIGGER trg_prestamo_before_insert
BEFORE INSERT ON Prestamo
FOR EACH ROW
BEGIN
  DECLARE estado VARCHAR(50);
  SELECT Estado INTO estado FROM Herramienta WHERE ID_Herramienta = NEW.ID_Herramienta FOR UPDATE;
  IF estado IS NULL THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Herramienta no existe';
  END IF;
  IF estado <> 'Disponible' THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = CONCAT('Herramienta no disponible: ', estado);
  END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_prestamo_after_insert
AFTER INSERT ON Prestamo
FOR EACH ROW
BEGIN
  UPDATE Herramienta SET Estado = 'Prestado' WHERE ID_Herramienta = NEW.ID_Herramienta;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_prestamo_after_update
AFTER UPDATE ON Prestamo
FOR EACH ROW
BEGIN
  IF OLD.Fecha_Devolucion IS NULL AND NEW.Fecha_Devolucion IS NOT NULL THEN
    UPDATE Herramienta SET Estado = 'Disponible' WHERE ID_Herramienta = NEW.ID_Herramienta;
  END IF;

  IF NEW.Estado = 'Dañado' THEN
    UPDATE Herramienta SET Estado = 'Dañado' WHERE ID_Herramienta = NEW.ID_Herramienta;
  END IF;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER trg_mantenimiento_after_update
AFTER UPDATE ON Mantenimiento
FOR EACH ROW
BEGIN
  IF NEW.Estado = 'Completado' THEN
    UPDATE Herramienta SET Estado = 'Disponible' WHERE ID_Herramienta = NEW.ID_Herramienta;
  END IF;
END$$
DELIMITER ;
