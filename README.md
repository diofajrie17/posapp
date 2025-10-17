# POS Application

A comprehensive Point of Sale (POS) application built with Laravel, Inertia.js, and Vue 3, featuring inventory management, sales tracking, member management, and comprehensive reporting.

## 🚀 Features

- **Product Management** - Manage products, categories, and units
- **Inventory Control** - Track stock levels, movements, and perform stock opname
- **Sales & POS** - Complete point of sale system with multiple payment methods
- **Member Management** - Customer/member database with history tracking
- **Expense Tracking** - Record operational expenses and advertising costs
- **Facility Income** - Track income from facilities (parking, rentals, etc.)
- **Comprehensive Reports** - Detailed sales reports, daily summaries, and CSV exports
- **Modern UI** - Professional, uniform design system with responsive layouts

## 📋 Requirements

- **PHP** >= 8.1
- **Composer** >= 2.0
- **Node.js** >= 18.x
- **NPM** >= 9.x
- **MySQL** >= 8.0 or **MariaDB** >= 10.3
- **Apache** or **Nginx** web server

## 🛠️ Installation & Setup

### 1. Clone the Repository

```bash
git clone https://github.com/diofajrie17/posapp.git
cd posapp
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install Node.js Dependencies

```bash
npm install
```

### 4. Environment Configuration

Copy the example environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` file and configure your database connection:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=posapp
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Configure other settings as needed:

```env
APP_NAME="POS Application"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost

# WhatsApp Notifications (optional)
WHATSAPP_API_URL=your_whatsapp_api_url
WHATSAPP_API_TOKEN=your_token
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Create Database

Create a new MySQL database:

```bash
mysql -u root -p
CREATE DATABASE posapp;
exit;
```

### 7. Run Database Migrations

```bash
php artisan migrate
```

### 8. Seed Database (Optional)

Populate the database with sample data:

```bash
php artisan db:seed
```

### 9. Create Storage Link

```bash
php artisan storage:link
```

### 10. Build Frontend Assets

For development:

```bash
npm run dev
```

For production:

```bash
npm run build
```

### 11. Start the Application

Start the Laravel development server:

```bash
php artisan serve
```

The application will be available at: `http://localhost:8000`

## 🎨 Design System

This application uses a comprehensive design system with reusable components:

- **PageHeader** - Consistent page titles with subtitles and action buttons
- **Card** - Uniform content containers with shadow and borders
- **DataTable** - Standardized table styling with pagination
- **Button** - 5 variants (primary, secondary, danger, success, ghost) × 3 sizes
- **EmptyState** - Friendly "no data" messages with icons

For detailed design guidelines, see [DESIGN_SYSTEM.md](DESIGN_SYSTEM.md)

## 📦 Project Structure

```
posapp/
├── app/
│   ├── Http/Controllers/     # Application controllers
│   ├── Models/               # Eloquent models
│   ├── Services/             # Business logic services
│   └── Jobs/                 # Queue jobs
├── database/
│   ├── migrations/           # Database migrations
│   └── seeders/              # Database seeders
├── resources/
│   ├── js/
│   │   ├── Components/       # Vue components
│   │   ├── Layouts/          # Layout components
│   │   └── Pages/            # Inertia.js pages
│   ├── css/                  # Stylesheets
│   └── views/                # Blade templates
├── routes/
│   ├── web.php               # Web routes
│   ├── api.php               # API routes
│   └── auth.php              # Authentication routes
└── public/                   # Public assets
```

## 🔧 Development

### Running Tests

```bash
php artisan test
```

### Code Style

Format code using Laravel Pint:

```bash
./vendor/bin/pint
```

### Watch for Changes

During development, keep the Vite dev server running:

```bash
npm run dev
```

### Clear Cache

```bash
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```

## 📱 Electron Desktop App (Optional)

This project includes an Electron wrapper for desktop deployment:

```bash
cd electron
npm install
npm start
```

## 🔐 Default Credentials

After seeding the database, you can login with:

- **Email**: admin@example.com
- **Password**: password

**Important:** Change these credentials immediately in production!

## 📊 Key Features

### Product Management
- Create, edit, and delete products
- Manage categories and units
- Track stock levels with color-coded indicators
- Low stock alerts

### Inventory
- Real-time stock tracking
- Stock movement history
- Stock opname functionality
- Detailed movement logs (IN/OUT)

### Sales & POS
- Quick and efficient point of sale interface
- Multiple payment methods (cash, transfer, e-wallet)
- Transaction history
- Customer/member association

### Reports
- Comprehensive financial reports
- Daily sales summaries
- Export to CSV
- Filter by date range
- Breakdown by category, payment method, etc.

### Expenses & Facility Income
- Track operational expenses
- Record advertising costs
- Manage facility income (parking, rentals)
- Categorize and analyze spending

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📝 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🐛 Troubleshooting

### Database Connection Issues

```bash
# Check MySQL is running
sudo systemctl status mysql

# Test connection
mysql -u your_username -p
```

### Permission Issues

```bash
# Set correct permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### NPM Build Errors

```bash
# Clear NPM cache and reinstall
rm -rf node_modules package-lock.json
npm cache clean --force
npm install
```

### Assets Not Loading

```bash
# Rebuild assets
npm run build

# Clear Laravel cache
php artisan cache:clear
```

## 📧 Support

For issues and questions, please open an issue on the GitHub repository.

## 🙏 Acknowledgements

Built with:
- [Laravel](https://laravel.com) - The PHP Framework
- [Inertia.js](https://inertiajs.com) - Modern monolith architecture
- [Vue 3](https://vuejs.org) - Progressive JavaScript framework
- [Tailwind CSS](https://tailwindcss.com) - Utility-first CSS framework
- [Electron](https://electronjs.org) - Desktop app framework
