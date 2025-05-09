# 🏌️ Golf POS System

A modern Point of Sale (POS) system built with **Laravel 10 (backend)** and **Vue 3 (frontend)** to manage golf-related transactions, customer billing, rentals, and detailed reporting.



## 📦 Features

- 🧾 Invoice creation with itemized breakdown
- ⛳ Rental and Pro Shop item management
- 💳 Payment method handling (Cash, Credit/Debit)
- 📊 Transaction report views
- 🧾 Printable receipt templates
- 🔐 Authentication and protected views
- 🎨 Comic-style themed UI (custom Tailwind CSS)



## 🔧 Tech Stack

- **Backend:** Laravel 10
- **Frontend:** Vue 3 (Composition API)
- **Styling:** Tailwind CSS
- **Database:** MySQL (external - freesqldatabase.com)
- **Others:** Axios, Laravel Sanctum (authentication)



## 🖥️ Local Setup Instructions

### Prerequisites

- PHP >= 8.1
- Composer
- Node.js + npm
- MySQL
- Git



## 🛠️ Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/limyqi9826/7Golf-Pos.git
cd 7Golf-Pos
```

### 2. Install Backend Dependencies

```bash
composer install
```

### 3. Install Frontend Dependencies

```bash
npm install
npm run dev
```

### 4. Set Up Environment Variables

Create a .env file and configure it:

```bash
APP_NAME=7GolfPOS
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=sql.yourhost.com
DB_PORT=3306
DB_DATABASE=your_db_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### 5. Run Migrations

```bash
php artisan migrate
```

### 6. Start the Development Server
```bash
php artisan serve
```
The app should now be running at http://localhost:8000


## 🧪 Demo Login

Use the following credentials to explore the system:

- **Username:** admin@email.com  
- **Password:** adminlogin

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
