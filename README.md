Install backend dependencies:
Bash
composer install
Install frontend dependencies:
Bash
npm install
Environment Setup
Copy the environment file:
Bash
cp .env.example .env
Generate application key:
Bash
php artisan key:generate
Configure your database in .env.
Run migrations and seeder:
Bash
php artisan migrate
php artisan db:seed
Running the Application
Start Laravel server:
Bash
php artisan serve
Start Vite development server:
Bash
npm run dev
Broadcasting & Realtime (Laravel Reverb)
Start the Reverb server:
Bash
php artisan reverb:start
