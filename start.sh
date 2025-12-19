php artisan migrate:fresh --seed
php artisan module:migrate --seed Employee
php artisan module:migrate --seed Leave
php artisan module:migrate Attendance
php artisan module:migrate OverTime
php artisan module:migrate --seed Weather
php artisan module:migrate --seed Tag
# php artisan module:migrate --seed Monitoring
php artisan module:migrate Storage
php artisan module:migrate --seed Payroll
php artisan command:generateSystemKeyPair
php artisan command:updateWeather
php artisan passport:install

# mysql -h [hostname] -u [username] -p [database_name] < world.sql