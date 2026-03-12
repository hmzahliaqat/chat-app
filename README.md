## Install backend dependencies:
composer install
## Install frontend dependencies:
npm install
## Environment Setup
Copy the environment file:
cp .env.example .env
## Generate application key:
php artisan key:generate
## Run migrations and seeder:
php artisan migrate
php artisan db:seed
## Running the Application
## Start Laravel server:
php artisan serve
## Start Vite development server:
npm run dev
## Broadcasting & Realtime (Laravel Reverb)
Start the Reverb server:
php artisan reverb:start
