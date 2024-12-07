-- najib dev
ALTER TABLE customer
ADD COLUMN customer_type ENUM('wholesale', 'retail') NOT NULL;

CREATE TABLE profits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profit_toman DECIMAL(15, 2) NOT NULL,
    profit_dollar DECIMAL(15, 2) NOT NULL,
    profit_lyra DECIMAL(15, 2) NOT NULL,
    profit_euro DECIMAL(15, 2) NOT NULL,
    profit_date DATE NOT NULL,
    profit_type ENUM(
        'daily',
        'weekly',
        'monthly',
        'yearly',
        'total'
    ) NOT NULL,
    status ENUM('pending', 'completed') NOT NULL DEFAULT 'pending'
);