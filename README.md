<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Pawlan

Pawlan is a Fitness Center Management System built with expressive, elegant syntax. We believe development must be an enjoyable and creative experience. Built on top of **Laravel** (Backend) and **Vue.js** (Frontend via Vite), Pawlan takes the pain out of managing fitness center operations by easing common tasks such as:

- Simple, fast class scheduling and session management.
- Powerful trainer profile and specialization tracking.
- Expressive, intuitive member bookings and payment status.
- Robust user role management (Admin, Trainer, Member).

## Getting Started (Installation)

To get this project up and running on your local machine, ensure you have PHP 8.2+, Composer, Node.js, and MySQL installed. Follow these steps:

**1. Clone the repository**
```bash
git clone <your-repository-url>
cd pawlan
2. Install dependencies
Install the required packages for both backend and frontend:

Bash
composer install
npm install
3. Setup the environment
Duplicate the example environment file and generate the application key:

Bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
npm run build
Note: Open the .env file and configure your database settings (e.g., DB_DATABASE=pawlan). Ensure that the database has been created in your local MySQL server.

4. Migrate and seed the database
Run the following command to create all necessary database tables and populate them with initial dummy data:

Bash
php artisan migrate:fresh --seed
5. Run the development servers
Because this project utilizes Laravel and Vite concurrently, you must run two separate local servers in two different terminal tabs:

Terminal 1 (Backend API):

Bash
php artisan serve
Terminal 2 (Frontend UI):

Bash
npm run dev
Once both servers are running, open your web browser and navigate to http://127.0.0.1:8000.
