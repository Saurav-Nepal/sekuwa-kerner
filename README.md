# Sekuwa Kerner - Nepali Food Ordering Platform

A fully functional e-commerce website for ordering Nepali sekuwa (grilled meat), snacks, and beverages. Built as a college project using PHP, MySQL, HTML, CSS, and JavaScript.

## 🍢 About Sekuwa Kerner

Sekuwa is a traditional Nepali dish where marinated meat is grilled over charcoal, resulting in a smoky, spicy, and incredibly flavorful experience. This platform allows customers to order authentic Nepali food online with features like cart management, checkout, and order tracking.

## 📋 Features

### Customer Features
- User registration and login
- Browse products by category
- Search and filter products
- Add items to cart
- Checkout with delivery information
- Multiple payment options (Cash on Delivery, eSewa, Khalti - mock)
- Order tracking
- Order history and spending reports

### Admin Features
- Secure admin dashboard
- Product management (add, edit, delete)
- Order management with status updates
- Sales reports and analytics
- Customer order overview

## 🛠️ Tech Stack

- **Backend:** PHP (Procedural)
- **Database:** MySQL
- **Frontend:** HTML5, CSS3, JavaScript
- **Server:** XAMPP (Apache + MySQL)

## 📁 Project Structure

```
sekuwa-kerner/
├── admin/                  # Admin dashboard files
│   ├── dashboard.php
│   ├── products.php
│   ├── add_product.php
│   ├── edit_product.php
│   ├── delete_product.php
│   ├── orders.php
│   ├── reports.php
│   └── admin_auth.php
├── customer/               # Customer account files
│   ├── dashboard.php
│   ├── orders.php
│   ├── order_status.php
│   └── report.php
├── auth/                   # Authentication files
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── cart/                   # Cart and checkout files
│   ├── cart.php
│   ├── checkout.php
│   ├── payment.php
│   └── place_order.php
├── assets/                 # Static assets
│   ├── css/style.css
│   ├── js/script.js
│   └── images/
├── config/                 # Configuration
│   └── db.php
├── index.php               # Home page
├── products.php            # Products listing
├── product_details.php     # Single product view
├── thank_you.php           # Order confirmation
└── database.sql            # Database schema
```

## 🚀 Installation Guide

### Prerequisites
- XAMPP installed on your computer
- Web browser (Chrome, Firefox, Edge, etc.)

### Step 1: Setup XAMPP
1. Download and install XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Start XAMPP Control Panel
3. Start **Apache** and **MySQL** modules

### Step 2: Copy Project Files
1. Copy the entire `sekuwa-kerner` folder
2. Paste it into `C:\xampp\htdocs\` (Windows) or `/Applications/XAMPP/htdocs/` (Mac)

### Step 3: Create Database
1. Open your browser and go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on "New" in the left sidebar
3. Create a new database named `sekuwa_kerner`
4. Click on the `sekuwa_kerner` database
5. Click on "Import" tab at the top
6. Click "Choose File" and select `database.sql` from the project folder
7. Click "Go" to import the database

### Step 4: Configure Database Connection (Optional)
If your MySQL has a different username/password, edit `config/db.php`:
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Change if different
define('DB_PASS', '');            // Change if different
define('DB_NAME', 'sekuwa_kerner');
```

### Step 5: Add Product Images (Optional)
1. Add product images to `assets/images/` folder
2. Name them according to the database entries or use `default.jpg` as fallback

### Step 6: Access the Website
Open your browser and go to:
- **Main Website:** [http://localhost/sekuwa-kerner](http://localhost/sekuwa-kerner)
- **Admin Dashboard:** [http://localhost/sekuwa-kerner/admin/dashboard.php](http://localhost/sekuwa-kerner/admin/dashboard.php)

## 🔐 Demo Credentials

### Admin Account
- **Email:** admin@sekuwakerner.com
- **Password:** password

### Customer Accounts
- **Email:** ram@example.com | **Password:** password
- **Email:** sita@example.com | **Password:** password
- **Email:** hari@example.com | **Password:** password

## 📊 Database Tables

| Table | Description |
|-------|-------------|
| `users` | Customer and admin accounts |
| `categories` | Product categories |
| `products` | Food items |
| `orders` | Customer orders |
| `order_items` | Items in each order |
| `payments` | Payment information |

## 🎨 Design

- **Theme:** Premium dark theme with warm fire-inspired accents (amber, orange, gold)
- **Typography:** Playfair Display (headings) + DM Sans (body) for elegant readability
- **Visual Effects:** Glassmorphism, gradient buttons, smooth animations, glowing accents
- **Layout:** Fully responsive design for all devices
- **UI:** Modern, attractive, and professionally designed with micro-interactions
- **Color Palette:** 
  - Primary: `#E85D04` (Fire Orange)
  - Accent: `#FFBA08` (Golden)
  - Background: `#0D0D0D` (Deep Black)
  - Cards: `#1A1A1A` (Dark Gray)

## ⚡ Security Features

- Password hashing using `password_hash()`
- Prepared statements to prevent SQL injection
- Session-based authentication
- Role-based access control (admin/customer)
- Input sanitization

## 📝 Order Flow

1. **Browse Menu** → Customer views products
2. **Add to Cart** → Products added to session cart
3. **Checkout** → Enter delivery information
4. **Payment** → Select payment method
5. **Place Order** → Order stored in database
6. **Thank You** → Order confirmation displayed
7. **Track Order** → Customer can track status
8. **Admin Updates** → Admin updates order status

## 🔧 Troubleshooting

### Database Connection Error
- Make sure MySQL is running in XAMPP
- Check database credentials in `config/db.php`
- Ensure `sekuwa_kerner` database exists

### Images Not Showing
- Add product images to `assets/images/` folder
- Check image filenames match database entries
- Default image `default.jpg` is used as fallback

### Admin Access Denied
- Make sure you're logged in with admin credentials
- Clear browser cookies and try again

## 👨‍💻 Development Notes

- All PHP files use `session_start()` at the beginning
- Database queries use prepared statements
- Helper functions are in `config/db.php`
- Admin pages include `admin_auth.php` for protection

## 📄 License

This project is created for educational purposes as a college project.

## 🙏 Acknowledgments

- Traditional Nepali cuisine inspiration
- XAMPP for local development environment
- PHP and MySQL community

---

**Made with ❤️ for college project**

*Sekuwa Kerner - Authentic Nepali Sekuwa Delivered to Your Doorstep*

