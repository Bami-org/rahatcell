-- bassir
ALTER TABLE balance ADD COLUMN bank_id INT DEFAULT 0;

ALTER TABLE transactions ADD COLUMN bank_id INT DEFAULT 0;

ALTER TABLE transactions ADD COLUMN profit;

ALTER TABLE balance ADD COLUMN profit;