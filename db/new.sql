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

ALTER TABLE api_credentials
ADD COLUMN my_loan DECIMAL(15, 2) DEFAULT 0 AFTER base_url,
ADD COLUMN my_money DECIMAL(15, 2) DEFAULT 0 AFTER my_loan;

CREATE TABLE `loans` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `dealer_code` VARCHAR(64) NOT NULL,
    `bank_id` INT(10) UNSIGNED NOT NULL,
    `loan_amount` DECIMAL(10, 2) NOT NULL,
    `loan_term` INT(10) NOT NULL,
    `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = UTF8MB4_GENERAL_CI;
-- create a admin_balance table
CREATE TABLE admin_balance (
    id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    balance DECIMAL(10, 2) NOT NULL,
    description TEXT NOT NULL,
    bank_id INT(10) UNSIGNED NOT NULL,
    created DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id)
) ENGINE = InnoDB;
-- bassir
ALTER TABLE balance ADD COLUMN bank_id INT DEFAULT 0;

ALTER TABLE transactions ADD COLUMN bank_id INT DEFAULT 0;

ALTER TABLE transactions ADD COLUMN profit;

ALTER TABLE balance ADD COLUMN profit;