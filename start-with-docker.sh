docker compose exec app php artisan migrate:fresh --seed
docker compose exec app php artisan module:migrate --seed Employee
docker compose exec app php artisan module:migrate --seed Leave
docker compose exec app php artisan module:migrate Attendance
docker compose exec app php artisan module:migrate OverTime
docker compose exec app php artisan module:migrate --seed Weather
docker compose exec app php artisan module:migrate --seed Tag
# docker compose exec app php artisan module:migrate --seed Monitoring
docker compose exec app php artisan module:migrate Storage
docker compose exec app php artisan module:migrate --seed Payroll
docker compose exec app php artisan command:generateSystemKeyPair
docker compose exec app php artisan command:updateWeather
docker compose exec app php artisan passport:install

# mysql -h [hostname] -u [username] -p [database_name] < world.sql