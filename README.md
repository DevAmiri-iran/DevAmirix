# DevAmirix

**DevAmirix** is a modern PHP framework that simplifies web application development. It offers a robust, modular architecture and a comprehensive set of tools to build dynamic, secure, and scalable web applications with ease.

---

## Table of Contents

- [Introduction](#introduction)
- [Features](#features)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Routing](#routing)
    - [Database & Migrations](#database--migrations)
    - [Templating](#templating)
    - [Middleware](#middleware)
    - [API Management](#api-management)
    - [Artisan Commands](#artisan-commands)
- [Dependencies](#dependencies)
- [Contributing](#contributing)
- [License](#license)
- [Contact](#contact)

---

## Introduction

**DevAmirix** is designed to streamline the development process by providing you with an elegant routing system, a powerful Blade-based templating engine with caching and minification, database migrations, built-in API management (with JWT and CSRF protection), and a CLI tool for common tasks. Whether you’re building a small project or a large-scale application, DevAmirix has you covered.

---

## Features

- **Elegant Routing:** Define routes for GET, POST, PUT, PATCH, DELETE, etc., with support for middleware and route groups.
- **Blade Templating Engine:** Enjoy a robust templating system with view caching and automatic HTML, CSS, and JS minification.
- **Database Migrations & Factories:** Simplify database schema management with migrations and generate dummy data using factories.
- **API Management:** Secure and validate API requests with built-in tools including JWT authentication and CSRF protection.
- **Artisan CLI:** Run migrations, execute factories, and perform other tasks using the integrated command-line interface.
- **Security:** Robust encryption and JWT-based authentication ensure your data stays secure.
- **Email Integration:** Send emails effortlessly using SMTP, sendmail, or a logging driver.
- **Centralized Configuration & Session Management:** Easily manage environment settings and sessions using dedicated configuration files.
- **Performance Enhancements:** Built-in caching and asset minification boost your application’s performance.

---

## Installation

### Requirements

- PHP 8.0 or higher
- [Composer](https://getcomposer.org/)
- MySQL (or any other supported database)

### Steps

1. **Clone the Repository:**

   ```bash
   git clone https://github.com/DevAmiri-iran/DevAmirix.git
   cd DevAmirix
   ```
   OR
    ```bash
    composer create-project devamiri/devamirix
    cd DevAmirix
    ```

2. **Install Dependencies via Composer:**

   ```bash
   composer install
   ```

3. **Create Environment File:**

   Copy the provided `.env.example` to `.env` and update your environment variables accordingly:

   ```bash
   cp .env.example .env
   ```

4. **(Optional) Set Up the Database:**

   If you plan to use database features, ensure that you configure your database credentials in the `.env` file and uncomment the database initialization in `app/bootstrap.php`.

---

## Configuration

All configuration files reside in the `app/config` directory. Adjust these files as needed, or use the dot notation helper functions to access and update settings dynamically.

---

## Usage

### Routing

Define routes effortlessly using the Route class. For example:

```php
// Render a view
Route::view('/', 'home');

// API endpoint
Route::api('/login', 'loginHandler');

// Closure-based route
Route::get('/welcome', function () {
    return 'Welcome to DevAmirix!';
});
```

### Database & Migrations

Activate database functionality in your bootstrap file:

```php
System::useDatabase();
```

Create migrations in the `app/database/migrations` directory. Example:

```php
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Capsule\Manager as Capsule;

return new class {
    public string $table = 'users';

    public function up(): void {
        Capsule::Schema()->create($this->table, function (Blueprint $table) {
            $table->id();
            $table->string('username', 50);
            $table->string('password', 50);
            $table->string('email', 100);
            $table->string('phone_number', 20)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Capsule::Schema()->dropIfExists($this->table);
    }
};
```

### Templating

Render views using the Blade templating engine:

```php
View::render('welcome', ['name' => 'DevAmirix']);
```

### Middleware

Create custom middleware in the `app/Middleware` directory:

```php
namespace App\Middleware;

class Auth {
    public function handle(array $params): void {
        if (!cookie()->has('user')) {
            redirect(url('login'));
        }
    }
}
```

Attach middleware to your routes as follows:

```php
Route::middleware(Auth::class)->get('/dashboard', 'dashboardHandler');
```

### API Management

Manage and secure API requests using the built-in APIManager:

```php
use App\Support\APIManager;

$API = new APIManager(true, true);
$API->validateParameters('password');

$API->handle(function ($request) {
    if ($request['password'] === '123') {
        APIManager::respond(true, 'Your password is correct');
    } else {
        APIManager::respond(false, 'Your password is not correct');
    }
});
```

### Artisan Commands

DevAmirix includes a built-in CLI tool to assist with routine tasks:

```bash
# Run all migrations
php artisan migrate *

# Run a specific factory (e.g., UserFactory)
php artisan factory UserFactory
```

---

## Dependencies

- **PHP:** 8.0+
- **Composer:** [getcomposer.org](https://getcomposer.org/)
- **Database:** MySQL (or other supported systems)
- **SwiftMailer:** [swiftmailer.symfony.com](https://swiftmailer.symfony.com/)
- **BladeOne:** [GitHub - eftec/BladeOne](https://github.com/eftec/BladeOne)
- **Illuminate Database:** Refer to [Laravel's Database Documentation](https://laravel.com/docs/master/database)

---

## Contributing

Contributions are welcome! Please review our [contributing guidelines](https://github.com/DevAmiri-iran/DevAmirix/blob/main/CONTRIBUTING.md) before submitting pull requests. Your help in improving DevAmirix is greatly appreciated.

---

## License

DevAmirix is open-sourced software licensed under the [MIT License](LICENSE).

---

## Contact

For more information, please visit our website at [devamiri.ir](https://devamiri.ir) or connect with us on [GitHub](https://github.com/DevAmiri-iran).

---

Happy coding with **DevAmirix**!