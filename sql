-- Create users table
CREATE TABLE users (
    id INT(11) NOT NULL AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) DEFAULT NULL,
    course VARCHAR(100) DEFAULT NULL,
    semester VARCHAR(50) DEFAULT NULL,
    capabilities VARCHAR(50) DEFAULT 'User',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY (email)
);

-- Create books table
CREATE TABLE books (
    book_id VARCHAR(50) NOT NULL,
    title VARCHAR(225) NOT NULL,
    owner_name VARCHAR(100) NOT NULL,
    contact VARCHAR(20) NOT NULL,
    course VARCHAR(100) NOT NULL,
    semester VARCHAR(50) NOT NULL,
    book_condition ENUM('New', 'Good', 'Fair', 'Poor') NOT NULL,
    price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    user_id INT(11) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('Available', 'Sold', 'Reserved') NOT NULL DEFAULT 'Available',
    PRIMARY KEY (book_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create cart table
CREATE TABLE cart (
    cart_id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    book_id VARCHAR(50) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (cart_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

-- Create orders table
CREATE TABLE orders (
    id INT(11) NOT NULL AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    book_id VARCHAR(255) NOT NULL,
    order_date VARCHAR(255) NOT NULL DEFAULT CURRENT_TIMESTAMP,
    address VARCHAR(50) NOT NULL,
    book_title VARCHAR(50) DEFAULT NULL,
    owner_name VARCHAR(10) DEFAULT NULL,
    contact VARCHAR(50) DEFAULT NULL,
    course VARCHAR(50) DEFAULT NULL,
    semester VARCHAR(10) DEFAULT NULL,
    book_condition VARCHAR(50) DEFAULT NULL,
    book_price DECIMAL(10,2) DEFAULT NULL,
    payment_method VARCHAR(20) NOT NULL DEFAULT 'COD',
    status VARCHAR(20) DEFAULT 'Processing',
    PRIMARY KEY (id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create order_items table
CREATE TABLE order_items (
    order_item_id INT(11) NOT NULL AUTO_INCREMENT,
    order_id INT(11) NOT NULL,
    book_id VARCHAR(50) NOT NULL,
    book_title VARCHAR(100) NOT NULL,
    book_price DECIMAL(10,2) NOT NULL,
    quantity INT(11) NOT NULL DEFAULT 1,
    PRIMARY KEY (order_item_id),
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE
);

INSERT INTO books (book_id, title, owner_name, contact, course, semester, book_condition, price, user_id, created_at, status) VALUES
('BOOK-6821db4501', 'Database Systems', 'Rahul Sharma', '9123456780', 'BSc CS', 'Sem 3', 'Good', 149.99, 1, NOW(), 'Available'),
('BOOK-6821db4502', 'Data Structures and Algorithms', 'Priya Verma', '9123456781', 'BSc IT', 'Sem 2', 'New', 199.99, 1, NOW(), 'Available'),
('BOOK-6821db4503', 'Operating Systems Concepts', 'Amit Kumar', '9123456782', 'BSc CS', 'Sem 4', 'Like New', 179.50, 1, NOW(), 'Available'),
('BOOK-6821db4504', 'Computer Networks', 'Sneha Reddy', '9123456783', 'B.Tech CSE', 'Sem 5', 'Fair', 120.00, 1, NOW(), 'Available'),
('BOOK-6821db4505', 'Discrete Mathematics', 'Deepak Singh', '9123456784', 'BSc Mathematics', 'Sem 1', 'New', 159.99, 1, NOW(), 'Available'),
('BOOK-6821db4506', 'Artificial Intelligence', 'Anjali Nair', '9123456785', 'MSc AI', 'Sem 2', 'Good', 220.00, 1, NOW(), 'Available'),
('BOOK-6821db4507', 'Software Engineering', 'Ravi Menon', '9123456786', 'BCA', 'Sem 6', 'Acceptable', 110.50, 1, NOW(), 'Available'),
('BOOK-6821db4508', 'Web Development', 'Pooja Mehta', '9123456787', 'BSc IT', 'Sem 3', 'New', 189.99, 1, NOW(), 'Available'),
('BOOK-6821db4509', 'Computer Graphics', 'Ramesh Iyer', '9123456788', 'B.Tech CSE', 'Sem 4', 'Good', 165.75, 1, NOW(), 'Available'),
('BOOK-6821db4510', 'Machine Learning', 'Kavya Sharma', '9123456789', 'MSc DS', 'Sem 1', 'Like New', 249.99, 1, NOW(), 'Available'),
('BOOK-6821db4511', 'Cloud Computing', 'Abhinav Das', '9123456790', 'BSc CS', 'Sem 5', 'New', 210.00, 1, NOW(), 'Available'),
('BOOK-6821db4512', 'Cyber Security Basics', 'Neha Raj', '9123456791', 'BCA', 'Sem 4', 'Good', 130.00, 1, NOW(), 'Available'),
('BOOK-6821db4513', 'Compiler Design', 'Siddharth Jain', '9123456792', 'B.Tech CSE', 'Sem 6', 'Fair', 140.00, 1, NOW(), 'Available'),
('BOOK-6821db4514', 'Mobile App Development', 'Tanya Rao', '9123456793', 'BSc IT', 'Sem 5', 'Like New', 175.00, 1, NOW(), 'Available'),
('BOOK-6821db4515', 'Information Retrieval', 'Gaurav Patil', '9123456794', 'MSc CS', 'Sem 2', 'New', 200.00, 1, NOW(), 'Available'),
('BOOK-6821db4516', 'Digital Logic Design', 'Lakshmi Iyer', '9123456795', 'BSc CS', 'Sem 2', 'Good', 155.50, 1, NOW(), 'Available'),
('BOOK-6821db4517', 'Theory of Computation', 'Manish Kapoor', '9123456796', 'BSc CS', 'Sem 4', 'Like New', 178.75, 1, NOW(), 'Available'),
('BOOK-6821db4518', 'Python Programming', 'Divya Sinha', '9123456797', 'BSc CS', 'Sem 1', 'New', 185.00, 1, NOW(), 'Available'),
('BOOK-6821db4519', 'Java Programming', 'Suresh Pillai', '9123456798', 'BCA', 'Sem 2', 'Fair', 160.00, 1, NOW(), 'Available'),
('BOOK-6821db4520', 'C++ Programming', 'Ritika Joshi', '9123456799', 'B.Tech CSE', 'Sem 1', 'Good', 170.00, 1, NOW(), 'Available'),
('BOOK-6821db4521', 'Big Data Analytics', 'Kiran Naik', '9123456800', 'MSc DS', 'Sem 3', 'Like New', 225.00, 1, NOW(), 'Available'),
('BOOK-6821db4522', 'Software Testing', 'Pallavi Saxena', '9123456801', 'BSc CS', 'Sem 6', 'Acceptable', 115.00, 1, NOW(), 'Available'),
('BOOK-6821db4523', 'Network Security', 'Raghav Bhat', '9123456802', 'B.Tech CSE', 'Sem 5', 'Good', 190.00, 1, NOW(), 'Available'),
('BOOK-6821db4524', 'UI/UX Design', 'Sanjana Roy', '9123456803', 'BCA', 'Sem 5', 'New', 210.00, 1, NOW(), 'Available'),
('BOOK-6821db4525', 'Data Mining', 'Akash Thakur', '9123456804', 'MSc CS', 'Sem 2', 'Fair', 180.00, 1, NOW(), 'Available'),
('BOOK-6821db4526', 'Blockchain Basics', 'Megha Dey', '9123456805', 'MSc IT', 'Sem 1', 'New', 230.00, 1, NOW(), 'Available'),
('BOOK-6821db4527', 'Introduction to Robotics', 'Nikhil Rao', '9123456806', 'B.Tech Robotics', 'Sem 3', 'Good', 215.00, 1, NOW(), 'Available'),
('BOOK-6821db4528', 'Natural Language Processing', 'Shruti Desai', '9123456807', 'MSc AI', 'Sem 2', 'Like New', 240.00, 1, NOW(), 'Available'),
('BOOK-6821db4529', 'Advanced Java', 'Yogesh Kulkarni', '9123456808', 'BSc CS', 'Sem 5', 'New', 195.00, 1, NOW(), 'Available'),
('BOOK-6821db4530', 'Introduction to HTML & CSS', 'Preeti Anand', '9123456809', 'BSc IT', 'Sem 1', 'New', 145.00, 1, NOW(), 'Available');
