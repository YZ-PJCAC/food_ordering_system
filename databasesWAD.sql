drop DATABASE db_system;

create DATABASE cvjjintv_db_system;

use cvjjintv_db_system;

----------------------------table product
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each product
    name VARCHAR(255) NOT NULL,                -- Product name
    price DECIMAL(10, 2) NOT NULL,             -- Product price
    image VARCHAR(255) DEFAULT NULL,           -- Path or URL of the product image
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Timestamp for when the product was added
);

ALTER TABLE products ADD COLUMN is_visible TINYINT(1) DEFAULT 1;


-------------------------------table user
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each user
    username VARCHAR(100) NOT NULL UNIQUE,  -- Username
    email VARCHAR(255) NOT NULL UNIQUE,     -- Email address
    password_hash VARCHAR(255) NOT NULL,    -- Password hash
    role ENUM('admin', 'customer') DEFAULT 'customer', -- User role
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Account creation timestamp
);

ALTER TABLE users
ADD COLUMN phone VARCHAR(15),
ADD COLUMN gender VARCHAR(10),
ADD COLUMN birthday DATE;



------------------------categories table
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each category
    name VARCHAR(255) NOT NULL UNIQUE,          -- Category name
    description TEXT DEFAULT NULL,              -- Optional description of the category
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP -- Timestamp for category creation
);


------------------------------------- product categories

CREATE TABLE product_categories (
    product_id INT NOT NULL,                    -- Foreign key referencing products table
    category_id INT NOT NULL,                   -- Foreign key referencing categories table
    PRIMARY KEY (product_id, category_id),      -- Composite primary key
    CONSTRAINT fk_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    CONSTRAINT fk_category FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
);

---------------------------------------- order 
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each order
    user_id INT NOT NULL,                    -- Foreign key referencing users table
    total_amount DECIMAL(10, 2) NOT NULL,    -- Total amount for the order
    status ENUM('pending', 'completed', 'canceled') DEFAULT 'pending', -- Order status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Order creation timestamp
    CONSTRAINT fk_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-------------------------------- order item
CREATE TABLE order_items (
    item_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each item
    order_id INT NOT NULL,                  -- Foreign key referencing orders table
    product_id INT NOT NULL,                -- Foreign key referencing products table
    quantity INT NOT NULL,                  -- Quantity of the product
    price DECIMAL(10, 2) NOT NULL,          -- Price of the product at the time of order
    CONSTRAINT fk_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    CONSTRAINT fk_product_order FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);



------------------------------ transaction table
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each transaction
    order_id INT NOT NULL,                         -- Foreign key referencing orders table
    payment_method ENUM('credit_card', 'paypal', 'bank_transfer') NOT NULL, -- Payment method
    payment_status ENUM('paid', 'failed', 'pending') DEFAULT 'pending', -- Payment status
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Transaction creation timestamp
    CONSTRAINT fk_transaction_order FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE
);

-------------------------------- cart
CREATE TABLE cart (
    cart_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each cart item
    user_id INT NOT NULL,                   -- Foreign key referencing users table
    product_id INT NOT NULL,                -- Foreign key referencing products table
    quantity INT NOT NULL,                  -- Quantity of the product
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Cart creation timestamp
    CONSTRAINT fk_cart_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_cart_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);



--------------------------- optional (review)
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY, -- Unique ID for each review
    user_id INT NOT NULL,                     -- Foreign key referencing users table
    product_id INT NOT NULL,                  -- Foreign key referencing products table
    rating INT CHECK (rating BETWEEN 1 AND 5),-- Rating between 1 and 5
    comment TEXT DEFAULT NULL,                -- Optional review comment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Review creation timestamp
    CONSTRAINT fk_review_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_review_product FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE
);


















------------------------------data product
INSERT INTO products (name, price, image)
VALUES
    ('Laksa Seafood', 14.50, NULL),
    ('Laksa Scallop', 8.50, NULL),
    ('Laksa Udang', 8.00, NULL),
    ('Laksa Sotong', 8.00, NULL),
    ('Laksa Ambal', 8.00, NULL),
    ('Laksa Oyster', 8.00, NULL),
    ('Laksa Ayam', 8.00, NULL),
    ('Laksa Seafood Pattaya', 16.00, NULL),
    ('Laksa Pattaya Scallop', 10.00, NULL),
    ('Laksa Pattaya Udang', 9.50, NULL),
    ('Laksa Pattaya Sotong', 9.50, NULL),
    ('Laksa Pattaya Ambal', 9.50, NULL),
    ('Laksa Pattaya Oyster', 9.50, NULL),
    ('Laksa Pattaya Ayam', 9.50, NULL),
    ('Bubur Ayam Seafood', 13.00, NULL),
    ('Bubur Ayam + Scallop', 7.00, NULL),
    ('Bubur Ayam + Udang', 6.50, NULL),
    ('Bubur Ayam + Sotong', 6.50, NULL),
    ('Bubur Ayam + Ambal', 6.50, NULL),
    ('Bubur Ayam + Oyster', 6.50, NULL),
    ('Bubur Ayam', 5.00, NULL),
    ('Bubur Ayam Seafood Pattaya', 14.50, NULL),
    ('Bubur Ayam Pattaya + Scallop', 8.50, NULL),
    ('Bubur Ayam Pattaya + Udang', 8.00, NULL),
    ('Bubur Ayam Pattaya + Sotong', 8.00, NULL),
    ('Bubur Ayam Pattaya + Ambal', 8.00, NULL),
    ('Bubur Ayam Pattaya + Oyster', 8.00, NULL),
    ('Bubur Ayam Pattaya', 6.50, NULL);

INSERT INTO categories (name, description) VALUES
('Laksa Sarawak', 'Delicious traditional Laksa dishes'),
('Laksa Pattaya', 'Pattaya-style Laksa dishes'),
('Bubur Ayam', 'Chicken porridge with various toppings'),
('Bubur Ayam Pattaya', 'Pattaya-style chicken porridge dishes');

INSERT INTO product_categories (product_id, category_id) VALUES
(1, 1),  -- Laksa Seafood
(2, 1),  -- Laksa Scallop
(3, 1),  -- Laksa Udang
(4, 1),  -- Laksa Sotong
(5, 1),  -- Laksa Ambal
(6, 1),  -- Laksa Oyster
(7, 1),  -- Laksa Ayam
(8, 2),  -- Laksa Seafood Pattaya
(9, 2),  -- Laksa Pattaya Scallop
(10, 2), -- Laksa Pattaya Udang
(11, 2), -- Laksa Pattaya Sotong
(12, 2), -- Laksa Pattaya Ambal
(13, 2), -- Laksa Pattaya Oyster
(14, 2), -- Laksa Pattaya Ayam
(15, 3), -- Bubur Ayam Seafood
(16, 3), -- Bubur Ayam + Scallop
(17, 3), -- Bubur Ayam + Udang
(18, 3), -- Bubur Ayam + Sotong
(19, 3), -- Bubur Ayam + Ambal
(20, 3), -- Bubur Ayam + Oyster
(21, 3), -- Bubur Ayam
(22, 4), -- Bubur Ayam Seafood Pattaya
(23, 4), -- Bubur Ayam Seafood + Scallop
(24, 4), -- Bubur Ayam Seafood + Udang
(25, 4), -- Bubur Ayam Seafood + Sotong
(26, 4), -- Bubur Ayam Seafood + Ambal
(27, 4), -- Bubur Ayam Seafood + Oyster
(28, 4); -- Bubur Ayam Pattaya


ALTER TABLE products ADD is_visible TINYINT(1) DEFAULT 1;
