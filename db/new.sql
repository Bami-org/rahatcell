-- najib dev
ALTER TABLE customer
ADD COLUMN customer_type ENUM('wholesale', 'retail') NOT NULL;
CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `amount` int(11) NOT NULL,
  `unit_id` int(10) unsigned NOT NULL,
  `category_id` int(10) unsigned NOT NULL,
  `sub_category_id` int(10) unsigned NOT NULL,
  `dollar_buy_price` decimal(10,2) NOT NULL,
  `dollar_sale_price` decimal(10,2) NOT NULL,
  `toman_buy_price` float NOT NULL,
  `toman_sale_price` float NOT NULL,
  `lyra_buy_price` float NOT NULL,
  `lyra_sale_price` float NOT NULL,
  `euro_buy_price` float NOT NULL,
  `euro_sale_price` float NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

CREATE TABLE `currency` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `symbol` varchar(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO currency (id,name,symbol,created) VALUES
(1,'تومن','IR','2024-02-24 00:00:00'),
(2,'دالر','$','2024-02-24 00:00:00'),
(4,'لیر','TL','2024-02-24 00:00:00'),
(5,'یورو','ER','2024-02-26 00:00:00');
CREATE TABLE `balance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `balance` int(11) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `balance_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;


INSERT INTO balance (id,customer_id,balance,description,created,updated) VALUES
(1,3,5000,'خدمات آنلاین نعمان کوهستانی','2024-02-25 18:55:51','2024-03-02 19:08:32'),
(4,4,30000,'تست بیلانس','2024-02-27 12:50:57','2024-03-02 19:08:32'),
(5,1,1500,'500 به علاوه یک هزار اضافه شد','2024-02-29 21:55:31','2024-03-04 04:18:39'),
(8,8,800,'300 دیگر به علاوه 500 اضافه شد','2024-03-04 16:23:11','2024-03-04 16:23:11');
SET FOREIGN_KEY_CHECKS=1;
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

CREATE TABLE `api_transactions` (
    `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `dealer_code` VARCHAR(64) NOT NULL COLLATE 'utf8mb4_general_ci',
    `bank_id` INT(10) UNSIGNED DEFAULT NULL,
    `transaction_type` ENUM(
        'add_money',
        'get_loan',
        'repay_loan'
    ) NOT NULL,
    `amount` DECIMAL(10, 2) NOT NULL,
    `created` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`) USING BTREE,
    INDEX `bank_id` (`bank_id`) USING BTREE,
    CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`) ON UPDATE CASCADE ON DELETE CASCADE
) COLLATE = 'utf8mb4_general_ci' ENGINE = InnoDB;