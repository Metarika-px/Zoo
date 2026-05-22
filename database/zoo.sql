CREATE DATABASE IF NOT EXISTS zoo_course CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE zoo_course;

DROP VIEW IF EXISTS view_dashboard_counts;
DROP VIEW IF EXISTS view_stats_excursion_popularity;
DROP VIEW IF EXISTS view_stats_ticket_sales;
DROP VIEW IF EXISTS view_stats_animals_by_type;
DROP VIEW IF EXISTS view_users_full;
DROP VIEW IF EXISTS view_excursions_full;
DROP VIEW IF EXISTS view_animal_responsibilities;
DROP VIEW IF EXISTS view_tickets_full;
DROP VIEW IF EXISTS view_employees_full;
DROP VIEW IF EXISTS view_animals_full;

DROP FUNCTION IF EXISTS calculate_ticket_total;
DROP FUNCTION IF EXISTS get_animal_age;
DROP FUNCTION IF EXISTS count_animals_in_enclosure;

DROP PROCEDURE IF EXISTS add_animal;
DROP PROCEDURE IF EXISTS update_animal;
DROP PROCEDURE IF EXISTS delete_animal_with_relations;
DROP PROCEDURE IF EXISTS buy_ticket;
DROP PROCEDURE IF EXISTS assign_employee_to_animal;
DROP PROCEDURE IF EXISTS create_excursion;
DROP PROCEDURE IF EXISTS delete_employee_with_relations;
DROP PROCEDURE IF EXISTS delete_user_with_relations;

DROP TABLE IF EXISTS ticket_order_items, ticket_orders, animal_employee, employee_positions, excursions, animals, species, animal_types, enclosures, employees, positions, users, roles;

CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    role_id INT NOT NULL,
    login VARCHAR(80) NOT NULL UNIQUE,
    password VARCHAR(120) NOT NULL,
    full_name VARCHAR(160) NOT NULL,
    email VARCHAR(160) NOT NULL,
    phone VARCHAR(40),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id)
) ENGINE=InnoDB;

CREATE TABLE animal_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE species (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_type_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT,
    CONSTRAINT fk_species_type FOREIGN KEY (animal_type_id) REFERENCES animal_types(id)
) ENGINE=InnoDB;

CREATE TABLE enclosures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    location VARCHAR(160) NOT NULL,
    area DECIMAL(10,2) NOT NULL DEFAULT 0,
    climate_zone VARCHAR(100) NOT NULL,
    capacity INT NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE animals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    species_id INT NOT NULL,
    enclosure_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    gender ENUM('male','female','unknown') NOT NULL DEFAULT 'unknown',
    birth_date DATE,
    arrival_date DATE NOT NULL,
    description TEXT,
    photo VARCHAR(255),
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_animals_species FOREIGN KEY (species_id) REFERENCES species(id),
    CONSTRAINT fk_animals_enclosure FOREIGN KEY (enclosure_id) REFERENCES enclosures(id)
) ENGINE=InnoDB;

CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL UNIQUE,
    birth_date DATE,
    gender ENUM('male','female','unknown') NOT NULL DEFAULT 'unknown',
    passport_series VARCHAR(10),
    passport_number VARCHAR(20),
    address VARCHAR(255),
    education VARCHAR(160),
    work_experience TEXT,
    medical_book_number VARCHAR(80),
    medical_book_expire_date DATE,
    hire_date DATE NOT NULL,
    dismissal_date DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_employees_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE employee_positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    position_id INT NOT NULL,
    salary_rate DECIMAL(10,2) NOT NULL DEFAULT 0,
    date_from DATE NOT NULL,
    date_to DATE,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_emp_pos_employee FOREIGN KEY (employee_id) REFERENCES employees(id),
    CONSTRAINT fk_emp_pos_position FOREIGN KEY (position_id) REFERENCES positions(id)
) ENGINE=InnoDB;

CREATE TABLE animal_employee (
    id INT AUTO_INCREMENT PRIMARY KEY,
    animal_id INT NOT NULL,
    employee_id INT NOT NULL,
    responsibility VARCHAR(180) NOT NULL,
    assigned_at DATE NOT NULL,
    CONSTRAINT fk_animal_employee_animal FOREIGN KEY (animal_id) REFERENCES animals(id),
    CONSTRAINT fk_animal_employee_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT
) ENGINE=InnoDB;

CREATE TABLE ticket_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    visit_date DATE NOT NULL,
    total_price DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('new','paid','cancelled') NOT NULL DEFAULT 'paid',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_ticket_orders_user FOREIGN KEY (user_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE ticket_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    ticket_type_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_items_order FOREIGN KEY (order_id) REFERENCES ticket_orders(id),
    CONSTRAINT fk_items_type FOREIGN KEY (ticket_type_id) REFERENCES ticket_types(id)
) ENGINE=InnoDB;

CREATE TABLE excursions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    title VARCHAR(160) NOT NULL,
    description TEXT,
    start_time DATETIME NOT NULL,
    duration_minutes INT NOT NULL,
    max_people INT NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    CONSTRAINT fk_excursions_employee FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES ('Гость'), ('Клиент'), ('Сотрудник'), ('Администратор'), ('Директор');
INSERT INTO users (role_id, login, password, full_name, email, phone) VALUES
(2, 'client', '12345', 'Иван Петров', 'client@example.com', '+79990000001'),
(3, 'keeper', '12345', 'Мария Соколова', 'keeper@example.com', '+79990000002'),
(4, 'admin', 'admin', 'Администратор Системы', 'admin@example.com', '+79990000003'),
(5, 'director', 'director', 'Елена Орлова', 'director@example.com', '+79990000004');

INSERT INTO animal_types (name) VALUES ('Млекопитающие'), ('Птицы'), ('Рептилии');
INSERT INTO species (animal_type_id, name, description) VALUES
(1, 'Амурский тигр', 'Крупная кошка Дальнего Востока.'),
(1, 'Азиатский слон', 'Социальное травоядное животное.'),
(2, 'Фламинго', 'Птица с ярким оперением.'),
(3, 'Игуана', 'Тропическая ящерица.');

INSERT INTO enclosures (name, location, area, climate_zone, capacity) VALUES
('Таежный сектор', 'Северная аллея', 750.00, 'Умеренный', 4),
('Слоновник', 'Центральная зона', 1200.00, 'Теплый', 3),
('Птичий пруд', 'Восточная зона', 430.00, 'Влажный', 25),
('Террариум', 'Павильон 2', 80.00, 'Тропический', 12);

INSERT INTO animals (species_id, enclosure_id, name, gender, birth_date, arrival_date, description, photo, is_active) VALUES
(1, 1, 'Байкал', 'male', '2018-04-12', '2020-06-01', 'Спокойный тигр, любит водоем.', NULL, 1),
(2, 2, 'Гая', 'female', '2012-09-03', '2019-05-10', 'Самка азиатского слона.', NULL, 1),
(3, 3, 'Роза', 'female', '2021-03-22', '2022-07-14', 'Фламинго из большой стаи.', NULL, 1),
(4, 4, 'Изумруд', 'unknown', '2020-01-15', '2021-08-20', 'Зеленая игуана.', NULL, 1);

INSERT INTO positions (name, description) VALUES
('Кипер', 'Уход за животными и кормление.'),
('Ветеринар', 'Контроль здоровья животных.'),
('Экскурсовод', 'Проведение экскурсий.');

INSERT INTO employees (user_id, birth_date, gender, passport_series, passport_number, address, education, work_experience, medical_book_number, medical_book_expire_date, hire_date, is_active) VALUES
(2, '1992-05-18', 'female', '4512', '123456', 'Москва', 'Биологическое', '7 лет работы с животными', 'МК-1001', '2027-01-01', '2021-02-10', 1);
INSERT INTO employee_positions (employee_id, position_id, salary_rate, date_from, is_active) VALUES
(1, 1, 65000.00, '2021-02-10', 1),
(1, 3, 18000.00, '2023-04-01', 1);
INSERT INTO animal_employee (animal_id, employee_id, responsibility, assigned_at) VALUES
(1, 1, 'Кормление и наблюдение', '2023-01-10'),
(2, 1, 'Утренний осмотр', '2023-02-15');

INSERT INTO ticket_types (name, price, description) VALUES
('Взрослый', 900.00, 'Посетитель старше 18 лет'),
('Детский', 450.00, 'Дети от 7 до 17 лет'),
('Льготный', 300.00, 'Студенты и пенсионеры');
INSERT INTO ticket_orders (user_id, visit_date, total_price, status) VALUES (1, CURDATE(), 1350.00, 'paid');
INSERT INTO ticket_order_items (order_id, ticket_type_id, quantity, price_at_purchase, subtotal) VALUES
(1, 1, 1, 900.00, 900.00),
(1, 2, 1, 450.00, 450.00);

INSERT INTO excursions (employee_id, title, description, start_time, duration_minutes, max_people, price, is_active) VALUES
(1, 'Хищники зоопарка', 'Маршрут по сектору крупных кошек.', DATE_ADD(NOW(), INTERVAL 2 DAY), 60, 15, 600.00, 1),
(1, 'Тропический павильон', 'Знакомство с рептилиями и птицами.', DATE_ADD(NOW(), INTERVAL 5 DAY), 45, 12, 450.00, 1);

CREATE OR REPLACE VIEW view_animals_full AS
SELECT a.*, s.name AS species_name, at.name AS animal_type_name, e.name AS enclosure_name
FROM animals a
JOIN species s ON s.id = a.species_id
JOIN animal_types at ON at.id = s.animal_type_id
JOIN enclosures e ON e.id = a.enclosure_id;

CREATE OR REPLACE VIEW view_employees_full AS
SELECT emp.*, u.login, u.full_name, u.email, u.phone,
       GROUP_CONCAT(p.name SEPARATOR ', ') AS positions,
       COALESCE(SUM(CASE WHEN ep.is_active = 1 THEN ep.salary_rate ELSE 0 END), 0) AS salary_total
FROM employees emp
JOIN users u ON u.id = emp.user_id
LEFT JOIN employee_positions ep ON ep.employee_id = emp.id
LEFT JOIN positions p ON p.id = ep.position_id
GROUP BY emp.id;

CREATE OR REPLACE VIEW view_tickets_full AS
SELECT o.id AS order_id, u.full_name, o.visit_date, o.total_price, o.status, o.created_at,
       tt.name AS ticket_type, i.quantity, i.price_at_purchase, i.subtotal
FROM ticket_orders o
JOIN users u ON u.id = o.user_id
JOIN ticket_order_items i ON i.order_id = o.id
JOIN ticket_types tt ON tt.id = i.ticket_type_id;

CREATE OR REPLACE VIEW view_animal_responsibilities AS
SELECT ae.id, ae.animal_id, ae.employee_id, a.name AS animal_name, u.full_name AS employee_name, ae.responsibility, ae.assigned_at
FROM animal_employee ae
JOIN animals a ON a.id = ae.animal_id
JOIN employees e ON e.id = ae.employee_id
JOIN users u ON u.id = e.user_id;

CREATE OR REPLACE VIEW view_excursions_full AS
SELECT ex.*, u.full_name AS employee_name
FROM excursions ex
JOIN employees e ON e.id = ex.employee_id
JOIN users u ON u.id = e.user_id;

CREATE OR REPLACE VIEW view_users_full AS
SELECT u.*, r.name AS role_name
FROM users u
JOIN roles r ON r.id = u.role_id;

CREATE OR REPLACE VIEW view_stats_animals_by_type AS
SELECT animal_type_name AS label, COUNT(*) AS total
FROM view_animals_full
WHERE is_active = 1
GROUP BY animal_type_name;

CREATE OR REPLACE VIEW view_stats_ticket_sales AS
SELECT DATE(created_at) AS label, SUM(total_price) AS total
FROM ticket_orders
GROUP BY DATE(created_at)
ORDER BY label;

CREATE OR REPLACE VIEW view_stats_excursion_popularity AS
SELECT title AS label, max_people AS total
FROM excursions
WHERE is_active = 1
ORDER BY max_people DESC;

CREATE OR REPLACE VIEW view_dashboard_counts AS
SELECT 'users' AS metric, COUNT(*) AS total FROM users
UNION ALL SELECT 'animals', COUNT(*) FROM animals
UNION ALL SELECT 'employees', COUNT(*) FROM employees
UNION ALL SELECT 'ticket_sales', COALESCE(SUM(total_price), 0) FROM ticket_orders;

DELIMITER //
CREATE FUNCTION calculate_ticket_total(orderId INT) RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT COALESCE(SUM(subtotal), 0) INTO total FROM ticket_order_items WHERE order_id = orderId;
    RETURN total;
END//

CREATE FUNCTION get_animal_age(birthDate DATE) RETURNS INT
DETERMINISTIC
BEGIN
    RETURN TIMESTAMPDIFF(YEAR, birthDate, CURDATE());
END//

CREATE FUNCTION count_animals_in_enclosure(enclosureId INT) RETURNS INT
DETERMINISTIC
BEGIN
    DECLARE total INT;
    SELECT COUNT(*) INTO total FROM animals WHERE enclosure_id = enclosureId AND is_active = 1;
    RETURN total;
END//

CREATE PROCEDURE add_animal(IN p_species_id INT, IN p_enclosure_id INT, IN p_name VARCHAR(120), IN p_gender VARCHAR(20), IN p_birth_date DATE, IN p_arrival_date DATE, IN p_description TEXT, IN p_photo VARCHAR(255))
BEGIN
    INSERT INTO animals (species_id, enclosure_id, name, gender, birth_date, arrival_date, description, photo, is_active)
    VALUES (p_species_id, p_enclosure_id, p_name, p_gender, p_birth_date, p_arrival_date, p_description, p_photo, 1);
END//

CREATE PROCEDURE update_animal(IN p_id INT, IN p_species_id INT, IN p_enclosure_id INT, IN p_name VARCHAR(120), IN p_gender VARCHAR(20), IN p_birth_date DATE, IN p_arrival_date DATE, IN p_description TEXT, IN p_photo VARCHAR(255), IN p_is_active BOOLEAN)
BEGIN
    UPDATE animals SET species_id = p_species_id, enclosure_id = p_enclosure_id, name = p_name, gender = p_gender,
        birth_date = p_birth_date, arrival_date = p_arrival_date, description = p_description,
        photo = COALESCE(p_photo, photo), is_active = p_is_active
    WHERE id = p_id;
END//

CREATE PROCEDURE delete_animal_with_relations(IN p_id INT)
BEGIN
    DELETE FROM animal_employee WHERE animal_id = p_id;
    DELETE FROM animals WHERE id = p_id;
END//

CREATE PROCEDURE buy_ticket(IN p_user_id INT, IN p_visit_date DATE)
BEGIN
    INSERT INTO ticket_orders (user_id, visit_date, total_price, status) VALUES (p_user_id, p_visit_date, 0, 'paid');
    SELECT LAST_INSERT_ID() AS order_id;
END//

CREATE PROCEDURE assign_employee_to_animal(IN p_animal_id INT, IN p_employee_id INT, IN p_responsibility VARCHAR(180))
BEGIN
    INSERT INTO animal_employee (animal_id, employee_id, responsibility, assigned_at)
    VALUES (p_animal_id, p_employee_id, p_responsibility, CURDATE());
END//

CREATE PROCEDURE create_excursion(IN p_employee_id INT, IN p_title VARCHAR(160), IN p_description TEXT, IN p_start_time DATETIME, IN p_duration INT, IN p_max_people INT, IN p_price DECIMAL(10,2))
BEGIN
    INSERT INTO excursions (employee_id, title, description, start_time, duration_minutes, max_people, price, is_active)
    VALUES (p_employee_id, p_title, p_description, p_start_time, p_duration, p_max_people, p_price, 1);
END//

CREATE PROCEDURE delete_employee_with_relations(IN p_employee_id INT)
BEGIN
    DELETE FROM animal_employee WHERE employee_id = p_employee_id;
    DELETE FROM employee_positions WHERE employee_id = p_employee_id;
    DELETE FROM excursions WHERE employee_id = p_employee_id;
    DELETE FROM employees WHERE id = p_employee_id;
END//

CREATE PROCEDURE delete_user_with_relations(IN p_user_id INT)
BEGIN
    DECLARE v_employee_id INT DEFAULT NULL;

    SELECT id INTO v_employee_id
    FROM employees
    WHERE user_id = p_user_id
    LIMIT 1;

    IF v_employee_id IS NOT NULL THEN
        CALL delete_employee_with_relations(v_employee_id);
    END IF;

    DELETE toi
    FROM ticket_order_items toi
    JOIN ticket_orders tor ON tor.id = toi.order_id
    WHERE tor.user_id = p_user_id;

    DELETE FROM ticket_orders WHERE user_id = p_user_id;
    DELETE FROM users WHERE id = p_user_id;
END//
DELIMITER ;