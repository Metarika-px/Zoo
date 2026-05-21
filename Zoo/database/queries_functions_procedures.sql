USE zoo_course;

/*
    Основные SQL-запросы, представления, функции и хранимые процедуры
    для курсового проекта "Зоопарк".

    Файл можно приложить к архиву отдельно от полной структуры БД zoo.sql.
*/

/* =========================
   ПРИМЕРЫ ОСНОВНЫХ ЗАПРОСОВ
   ========================= */

-- Список активных животных с видом, типом и вольером
SELECT *
FROM view_animals_full
WHERE is_active = 1
ORDER BY name;

-- Поиск животных по имени или виду
SELECT *
FROM view_animals_full
WHERE is_active = 1
  AND (name LIKE CONCAT('%', 'тигр', '%') OR species_name LIKE CONCAT('%', 'тигр', '%'))
ORDER BY name;

-- Список сотрудников с должностями и итоговой активной ставкой
SELECT *
FROM view_employees_full
ORDER BY full_name;

-- Состав заказов билетов
SELECT *
FROM view_tickets_full
ORDER BY created_at DESC, order_id DESC;

-- Закрепленные животные и обязанности сотрудников
SELECT *
FROM view_animal_responsibilities
ORDER BY employee_name, animal_name;

-- Активные экскурсии с ФИО сотрудника
SELECT *
FROM view_excursions_full
WHERE is_active = 1
ORDER BY start_time;

-- Статистика для графика: количество животных по типам
SELECT animal_type_name AS label, COUNT(*) AS total
FROM view_animals_full
WHERE is_active = 1
GROUP BY animal_type_name;

-- Статистика для графика: продажи билетов по датам
SELECT DATE(created_at) AS label, SUM(total_price) AS total
FROM ticket_orders
GROUP BY DATE(created_at)
ORDER BY label;

-- Статистика для графика: популярность экскурсий по вместимости группы
SELECT title AS label, max_people AS total
FROM excursions
WHERE is_active = 1
ORDER BY max_people DESC;

/* =========================
   ПРЕДСТАВЛЕНИЯ
   ========================= */

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
SELECT ae.id, a.name AS animal_name, u.full_name AS employee_name, ae.responsibility, ae.assigned_at
FROM animal_employee ae
JOIN animals a ON a.id = ae.animal_id
JOIN employees e ON e.id = ae.employee_id
JOIN users u ON u.id = e.user_id;

CREATE OR REPLACE VIEW view_excursions_full AS
SELECT ex.*, u.full_name AS employee_name
FROM excursions ex
JOIN employees e ON e.id = ex.employee_id
JOIN users u ON u.id = e.user_id;

/* =========================
   ФУНКЦИИ И ПРОЦЕДУРЫ
   ========================= */

DROP FUNCTION IF EXISTS calculate_ticket_total;
DROP FUNCTION IF EXISTS get_animal_age;
DROP FUNCTION IF EXISTS count_animals_in_enclosure;
DROP PROCEDURE IF EXISTS add_animal;
DROP PROCEDURE IF EXISTS update_animal;
DROP PROCEDURE IF EXISTS delete_animal_with_relations;
DROP PROCEDURE IF EXISTS buy_ticket;
DROP PROCEDURE IF EXISTS assign_employee_to_animal;
DROP PROCEDURE IF EXISTS create_excursion;

DELIMITER //

CREATE FUNCTION calculate_ticket_total(orderId INT) RETURNS DECIMAL(10,2)
DETERMINISTIC
BEGIN
    DECLARE total DECIMAL(10,2);
    SELECT COALESCE(SUM(subtotal), 0) INTO total
    FROM ticket_order_items
    WHERE order_id = orderId;
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
    SELECT COUNT(*) INTO total
    FROM animals
    WHERE enclosure_id = enclosureId AND is_active = 1;
    RETURN total;
END//

CREATE PROCEDURE add_animal(
    IN p_species_id INT,
    IN p_enclosure_id INT,
    IN p_name VARCHAR(120),
    IN p_gender VARCHAR(20),
    IN p_birth_date DATE,
    IN p_arrival_date DATE,
    IN p_description TEXT,
    IN p_photo VARCHAR(255)
)
BEGIN
    INSERT INTO animals (species_id, enclosure_id, name, gender, birth_date, arrival_date, description, photo, is_active)
    VALUES (p_species_id, p_enclosure_id, p_name, p_gender, p_birth_date, p_arrival_date, p_description, p_photo, 1);
END//

CREATE PROCEDURE update_animal(
    IN p_id INT,
    IN p_species_id INT,
    IN p_enclosure_id INT,
    IN p_name VARCHAR(120),
    IN p_gender VARCHAR(20),
    IN p_birth_date DATE,
    IN p_arrival_date DATE,
    IN p_description TEXT,
    IN p_photo VARCHAR(255),
    IN p_is_active BOOLEAN
)
BEGIN
    UPDATE animals
    SET species_id = p_species_id,
        enclosure_id = p_enclosure_id,
        name = p_name,
        gender = p_gender,
        birth_date = p_birth_date,
        arrival_date = p_arrival_date,
        description = p_description,
        photo = COALESCE(p_photo, photo),
        is_active = p_is_active
    WHERE id = p_id;
END//

CREATE PROCEDURE delete_animal_with_relations(IN p_id INT)
BEGIN
    DELETE FROM animal_employee WHERE animal_id = p_id;
    DELETE FROM animals WHERE id = p_id;
END//

CREATE PROCEDURE buy_ticket(IN p_user_id INT, IN p_visit_date DATE)
BEGIN
    INSERT INTO ticket_orders (user_id, visit_date, total_price, status)
    VALUES (p_user_id, p_visit_date, 0, 'paid');
    SELECT LAST_INSERT_ID() AS order_id;
END//

CREATE PROCEDURE assign_employee_to_animal(
    IN p_animal_id INT,
    IN p_employee_id INT,
    IN p_responsibility VARCHAR(180)
)
BEGIN
    INSERT INTO animal_employee (animal_id, employee_id, responsibility, assigned_at)
    VALUES (p_animal_id, p_employee_id, p_responsibility, CURDATE());
END//

CREATE PROCEDURE create_excursion(
    IN p_employee_id INT,
    IN p_title VARCHAR(160),
    IN p_description TEXT,
    IN p_start_time DATETIME,
    IN p_duration INT,
    IN p_max_people INT,
    IN p_price DECIMAL(10,2)
)
BEGIN
    INSERT INTO excursions (employee_id, title, description, start_time, duration_minutes, max_people, price, is_active)
    VALUES (p_employee_id, p_title, p_description, p_start_time, p_duration, p_max_people, p_price, 1);
END//

DELIMITER ;
/* =========================
   ДОПОЛНИТЕЛЬНЫЕ ПРЕДСТАВЛЕНИЯ ДЛЯ ОТЧЕТОВ И СЛУЖЕБНЫХ СПИСКОВ
   ========================= */

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

CREATE OR REPLACE VIEW view_animal_responsibilities AS
SELECT ae.id, ae.animal_id, ae.employee_id, a.name AS animal_name, u.full_name AS employee_name, ae.responsibility, ae.assigned_at
FROM animal_employee ae
JOIN animals a ON a.id = ae.animal_id
JOIN employees e ON e.id = ae.employee_id
JOIN users u ON u.id = e.user_id;

DROP PROCEDURE IF EXISTS delete_employee_with_relations;
DROP PROCEDURE IF EXISTS delete_user_with_relations;

DELIMITER //

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
