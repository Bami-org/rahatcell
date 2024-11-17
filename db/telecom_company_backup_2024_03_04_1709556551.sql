

CREATE TABLE `balance` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `balance` int(11) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `balance_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4;

INSERT INTO balance VALUES("1","3","5000","خدمات آنلاین نعمان کوهستانی","2024-02-25 18:55:51","2024-03-02 19:08:32");
INSERT INTO balance VALUES("4","4","30000","تست بیلانس","2024-02-27 12:50:57","2024-03-02 19:08:32");
INSERT INTO balance VALUES("5","1","1500","500 به علاوه یک هزار اضافه شد","2024-02-29 21:55:31","2024-03-04 04:18:39");
INSERT INTO balance VALUES("8","8","800","300 دیگر به علاوه 500 اضافه شد","2024-03-04 16:23:11","2024-03-04 16:23:11");



CREATE TABLE `bank` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;

INSERT INTO bank VALUES("1","Ziraat Bank","غرفه شماره 2 احمد","2024-02-25 18:05:31");
INSERT INTO bank VALUES("3","Azizi Bank","بانک عزیزی نمایندگی کابل","2024-02-25 18:12:51");



CREATE TABLE `category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

INSERT INTO category VALUES("1","شبکه های افغانستان","2024-02-29 14:28:11","2024-02-29 14:28:11");
INSERT INTO category VALUES("2","شبکه های ایران","2024-02-29 14:28:20","2024-02-29 14:28:20");
INSERT INTO category VALUES("3","برنامه ها","2024-02-29 14:28:34","2024-02-29 14:28:34");
INSERT INTO category VALUES("4","بازی های آنلاین","2024-02-29 15:29:43","2024-02-29 15:29:43");



CREATE TABLE `currency` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `symbol` varchar(32) NOT NULL,
  `created` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;

INSERT INTO currency VALUES("1","تومن","IR","2024-02-24");
INSERT INTO currency VALUES("2","دالر","$","2024-02-24");
INSERT INTO currency VALUES("4","لیر","TL","2024-02-24");
INSERT INTO currency VALUES("5","یورو","ER","2024-02-26");



CREATE TABLE `customer` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `phone` varchar(32) NOT NULL,
  `address` text NOT NULL,
  `parent_id` int(10) unsigned NOT NULL DEFAULT 0,
  `currency_id` int(11) unsigned NOT NULL,
  `username` varchar(32) NOT NULL,
  `password` varchar(64) NOT NULL,
  `pin_code` varchar(32) NOT NULL DEFAULT '1234',
  `status` enum('Active','Deactive') NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;

INSERT INTO customer VALUES("1","Qasim Sarwari","098678624","Mazar-i-sharif Afghanistan","0","1","qasim","1234","1234","Active","2024-02-24 18:02:36","2024-02-24 18:02:36");
INSERT INTO customer VALUES("3","نعمان کوهستانی","00909545454","استانبول ترکیه","0","2","noman0090","noman11","0","Active","2024-02-24 19:35:22","2024-02-24 19:35:22");
INSERT INTO customer VALUES("4","فیض الله","00909767676","کارابوک ترکیه","0","4","faizy22","faiz1234","2222","Active","2024-02-24 20:24:41","2024-02-24 20:24:41");
INSERT INTO customer VALUES("7","عباس","0778989989","Dynkondi Afghanistan","4","4","abbas","1122","2222","Active","2024-03-01 10:49:47","2024-03-01 10:49:47");
INSERT INTO customer VALUES("8","ادریس","0788900999","ولایت بلخ افغانستان","1","1","edris11","112233","1234","Active","2024-03-03 13:14:59","2024-03-03 13:14:59");
INSERT INTO customer VALUES("9","احمد","009849949494","تهران ایران","4","4","ahmad","ahmad","1234","Active","2024-03-03 20:25:22","2024-03-03 20:25:22");
INSERT INTO customer VALUES("10","نسیم","0796574592","شولگره بلخ","4","4","nasim","n1234","1234","Active","2024-03-03 20:44:04","2024-03-03 20:44:04");
INSERT INTO customer VALUES("12","باسط احمد","0081834344","جرمنی آلمان","3","0","basit","basit","1234","Active","2024-03-04 16:31:45","2024-03-04 16:31:45");



CREATE TABLE `orders` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `product_id` int(10) unsigned NOT NULL,
  `customer_id` int(10) unsigned NOT NULL,
  `account_address` varchar(64) NOT NULL,
  `status` enum('Pending','Success','Rejected') NOT NULL DEFAULT 'Pending',
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4;

INSERT INTO orders VALUES("1","2","3","0798678624","Success","2024-02-27 12:00:51","2024-02-29 22:17:42");
INSERT INTO orders VALUES("2","3","4","faizy.2023","Pending","2024-03-01 08:28:14","2024-03-01 08:28:14");
INSERT INTO orders VALUES("3","3","7","077798869","Success","2024-03-02 17:23:37","2024-03-02 17:23:37");
INSERT INTO orders VALUES("4","7","7","0780659865","Rejected","2024-03-02 17:34:56","2024-03-02 17:34:56");
INSERT INTO orders VALUES("5","2","1","0798678624","Pending","2024-03-02 19:47:23","2024-03-02 19:47:23");
INSERT INTO orders VALUES("6","3","1","0730238892","Success","2024-03-02 19:49:21","2024-03-02 19:49:21");
INSERT INTO orders VALUES("7","3","1","788555558","Pending","2024-03-03 19:24:05","2024-03-03 19:24:05");
INSERT INTO orders VALUES("8","3","1","777798989","Success","2024-03-03 19:27:14","2024-03-03 19:27:14");



CREATE TABLE `payment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `pay_amount` float NOT NULL,
  `bank_id` int(10) unsigned NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

INSERT INTO payment VALUES("3","3","1400","3","پرداختی نعمان بابت خرید کریدیت","2024-03-01 07:17:48","2024-03-01 07:17:48");
INSERT INTO payment VALUES("4","1","1000","1","برای بار دوم تست شد","2024-03-01 14:14:54","2024-03-01 14:14:54");
INSERT INTO payment VALUES("6","4","15000","1","فیض الله 15000 هزار پرداخت کرد","2024-03-01 14:35:16","2024-03-01 14:35:16");



CREATE TABLE `product` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
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
  `created` date NOT NULL DEFAULT current_timestamp(),
  `updated` date NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4;

INSERT INTO product VALUES("2","100 یوسی","4","4","14","10.00","11.00","1200","1250","12","13","11","12","یوسی گیم PUBG موبایل 100 uc","2024-02-26","2024-02-26");
INSERT INTO product VALUES("3","50 افغانی","5","1","9","2.00","2.00","230","250","4.3","4.5","1.7","2","کریدیت 100 افغانی سیم کارت های افغانستان","2024-02-27","2024-02-27");
INSERT INTO product VALUES("4","10 الماس","6","3","18","3.00","2.70","230","250","3.5","3.8","3","3.5","10 الماس Bigo live","2024-02-29","2024-02-29");
INSERT INTO product VALUES("5","100 الماس","6","4","15","12.00","12.00","1230","1250","13.5","14","10","11","100 الماس بازی کلش آف کلنس","2024-02-29","2024-02-29");
INSERT INTO product VALUES("6","20 یوسی","4","4","14","8.00","9.00","1000","1200","10","12","9","10","10 یوسی بازی پابجی","2024-02-29","2024-02-29");
INSERT INTO product VALUES("7","100 افغانی","5","1","9","2.00","1.80","2000","2200","4","5","1","1.3","100 افغانی کریدیت کارت","2024-03-02","2024-03-02");



CREATE TABLE `sub_category` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `up_category` int(10) unsigned NOT NULL,
  `photo` varchar(64) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `up_category` (`up_category`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4;

INSERT INTO sub_category VALUES("8","بسته ها","1","afgancell.png","2024-02-29 14:46:05","2024-03-03 07:10:02");
INSERT INTO sub_category VALUES("9","کریدیت کارت","1","roshan.png","2024-02-29 14:46:35","2024-02-29 14:46:35");
INSERT INTO sub_category VALUES("10","ایران سیل","2","mtn.png","2024-02-29 15:26:31","2024-02-29 15:26:31");
INSERT INTO sub_category VALUES("11","همراه اول","2","irancell.png","2024-02-29 15:26:39","2024-02-29 15:26:39");
INSERT INTO sub_category VALUES("12","الماس vimo","3","vimo.png","2024-02-29 15:30:06","2024-02-29 15:30:06");
INSERT INTO sub_category VALUES("13","الماس ایمو","3","imo.png","2024-02-29 15:30:57","2024-02-29 15:30:57");
INSERT INTO sub_category VALUES("14","PUBG Mobile","4","pubgmobile.jpg","2024-02-29 15:32:12","2024-02-29 15:32:12");
INSERT INTO sub_category VALUES("15","Clash of Clans","4","ClashofClans.png","2024-02-29 15:32:25","2024-02-29 15:32:25");
INSERT INTO sub_category VALUES("16","رایتل","2","etisalat.jpg","2024-02-29 16:01:58","2024-03-03 04:57:35");
INSERT INTO sub_category VALUES("17","Exxen","4","exxen.png","2024-02-29 16:13:17","2024-02-29 16:13:17");
INSERT INTO sub_category VALUES("18","الماس Bigo Live","3","bigolive.jpg","2024-02-29 16:58:10","2024-02-29 16:58:10");



CREATE TABLE `transactions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(10) unsigned NOT NULL,
  `amount` float NOT NULL,
  `tr_type` enum('Payment','Receipt') NOT NULL,
  `description` text NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4;

INSERT INTO transactions VALUES("1","3","1400","Payment","پرداختی نعمان بابت خرید کریدیت","2024-03-01 07:17:48","2024-03-01 07:17:48");
INSERT INTO transactions VALUES("2","1","500","Receipt","500 افغانی برای قاسم اضافه شد","2024-03-01 07:21:10","2024-03-01 07:21:10");
INSERT INTO transactions VALUES("3","1","1000","Payment","برای بار دوم تست شد","2024-03-01 14:14:54","2024-03-01 14:14:54");
INSERT INTO transactions VALUES("4","4","30000","Receipt","تست بیلانس","2024-03-01 14:28:22","2024-03-01 14:28:22");
INSERT INTO transactions VALUES("5","4","15000","Payment","فیض الله 15000 هزار پرداخت کرد","2024-03-01 14:35:16","2024-03-01 14:35:16");
INSERT INTO transactions VALUES("6","7","2500","Receipt","2500 لیر اضافه شد","2024-03-02 18:11:21","2024-03-02 18:11:21");
INSERT INTO transactions VALUES("7","7","500","Payment","عباس 500 لیر پرداخت کرد","2024-03-02 18:32:55","2024-03-02 18:32:55");
INSERT INTO transactions VALUES("10","1","500","Receipt","","2024-03-04 16:17:30","2024-03-04 16:17:30");
INSERT INTO transactions VALUES("11","1","1000","Receipt","بازهم برای تست بخش ها اضافه شد","2024-03-04 16:18:39","2024-03-04 16:18:39");
INSERT INTO transactions VALUES("12","1","500","Receipt","500 به علاوه یک هزار اضافه شد","2024-03-04 16:19:04","2024-03-04 16:19:04");
INSERT INTO transactions VALUES("13","9","2300","Receipt","برای احمد 2300 اضافه شد","2024-03-04 16:21:08","2024-03-04 16:21:08");
INSERT INTO transactions VALUES("14","9","200","Receipt","200 اضافه شد","2024-03-04 16:22:09","2024-03-04 16:22:09");
INSERT INTO transactions VALUES("15","8","500","Receipt","برای ادریس 500 اضافه شد","2024-03-04 16:23:11","2024-03-04 16:23:11");
INSERT INTO transactions VALUES("16","8","300","Receipt","300 دیگر به علاوه 500 اضافه شد","2024-03-04 16:23:36","2024-03-04 16:23:36");



CREATE TABLE `units` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4;

INSERT INTO units VALUES("2","TL","2024-02-25 17:47:45","2024-02-25 17:47:45");
INSERT INTO units VALUES("3","تومن","2024-02-25 17:47:59","2024-02-25 17:47:59");
INSERT INTO units VALUES("4","UC","2024-02-25 17:48:06","2024-02-25 17:48:06");
INSERT INTO units VALUES("5","AF","2024-02-25 17:49:26","2024-02-25 17:49:26");
INSERT INTO units VALUES("6","الماس","2024-02-29 17:04:30","2024-02-29 17:04:30");



CREATE TABLE `user` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `user_type` enum('admin','user') NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp(),
  `updated` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;

INSERT INTO user VALUES("1","admin","admin","admin","2024-02-25 11:47:43","2024-02-25 11:47:43");
INSERT INTO user VALUES("2","user1","1234","user","2024-03-04 16:34:46","2024-03-04 16:34:46");

