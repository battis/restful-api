CREATE TABLE `record_fixtures` (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    field1 VARCHAR(255),
    field2 INTEGER,
    field3 VARCHAR(255)
);

INSERT INTO `record_fixtures` (id, field1, field2, field3) VALUES
    (1, 'testRow1', 123, NULL),
    (2, 'test row 2', 456, NULL);
