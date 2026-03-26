# CS12-Laravel

## Requirements
- WSL 2 Any Distro
- Docker Desktop

## File Structure
```
soft-eng(Could be any directory name)/
├── cs12-laravel/
└── cs12-nextjs/
```
Place the respective env inside of each dir root location e.g
the .env(for laravel) should be placed inside cs12-laravel
```
soft-eng(Could be any directory name)/
├── cs12-laravel/
│   ├── app/  
|   └── .env(for laravel)
└── cs12-nextjs/
    ├── app/
    ├── components/
    └── .env(for nextjs)
    └── package.json
```

*Note: Place your project directory inside of wsl so that it wont be slow placing it in windows will cause it slow performance by 14s insatead of 400ms* 
## How To Run The Docker
1. Enter cs12-laravel
2. turn on docker desktop
3. run `docker compose up`
4. Web application now exposed in `localhost:3000`

## Entering the containers
To  be able to configure and perform operation in both potsgres and laravel 
e.g `php artisan migrate` you first have to enter their containers.

1. Laravel: `docker exec -it cs12-laravel-backend-1 bash`
2. Postgres: `docker exec -it cs12-laravel-postgres-1 psql -U postgres`
3. Nextjs: `docker exec -it cs12-laravel-frontend-1 bash`

## Migration
To create all the necessary tables in postgres
1. `docker exec -it cs12-laravel-backend-1 bash`
2. `php artisan migrate`

## Seeding
 *Note: its not what you think jay mark*

To Ensure that data is populated in dashboard 

1. `docker exec -it cs12-laravel-backend-1 bash`
2. `php artisan db:seed --class=LeadSeeder` 
3. `php artisan db:seed --class=CustomerSeeder`
4. `php artisan db:seed --class=JobSeeder`
5. `php artisan db:seed --class=EstimateSeeder`
6. `php artisan db:seed --class=InvoiceSeeder`

to edit the content of their  data you have to change their factory functions
```
cs12-laravel/
├── app/
└── database/
    └── factories/
        └── LeadFactory.php
```