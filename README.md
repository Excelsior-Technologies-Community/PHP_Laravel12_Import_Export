# PHP_Laravel12_Import_Export

A professional **Laravel 12 Product CRUD System** with **Excel/CSV Import & Export**, multiple image upload, soft deletes, and a clean **Bootstrap 5** interface.

---

## Features

 Full CRUD Operations (Create, Read, Update, Delete)  
 Soft Deletes with Trash & Restore functionality  
 Multiple Image Upload per product  
 Image Preview & Delete support  
 Excel / CSV Import & Export using `maatwebsite/excel`  
 Form Validation  
 Pagination  
 Responsive UI using **Bootstrap 5**

---

## 🛠️ Tech Stack

- Framework: Laravel 12  
- Frontend: Blade + Bootstrap 5  
- Database: MySQL  
- Excel Support: Maatwebsite / Laravel-Excel  

---

# Project Screenshots

<img width="1738" height="573" alt="image" src="https://github.com/user-attachments/assets/73944146-19d2-40e5-9bc9-81a0482ba842" />
<img width="1750" height="810" alt="image" src="https://github.com/user-attachments/assets/b79233fe-5c73-46ea-8ddb-637d2887f4cf" />
<img width="1708" height="724" alt="image" src="https://github.com/user-attachments/assets/eb595892-1e77-44e2-9189-bec0587a22f4" />
<img width="1778" height="967" alt="image" src="https://github.com/user-attachments/assets/bc5e154f-5ad4-48be-a785-b965d1fda267" />


## Suggested Repository Names

You can use any of these repo names:

```text
laravel12-product-crud
laravel12-crud-with-import-export
laravel12-import-export-products
product-management-laravel12



Recommended for Import/Export focus:

laravel12-import-export

Installation Guide
1. Clone Repository
git clone https://github.com/your-username/laravel12-import-export.git
cd laravel12-import-export

2. Install Dependencies
composer install
npm install
npm run build

3. Setup Environment

Copy .env.example to .env and update:

DB_DATABASE=product_crud
DB_USERNAME=root
DB_PASSWORD=


Generate App Key:

php artisan key:generate

4. Run Migrations
php artisan migrate

5. Create Storage Link
php artisan storage:link

6. Install Excel Package
composer require maatwebsite/excel
php artisan vendor:publish --provider="Maatwebsite\Excel\ExcelServiceProvider" --tag=config

7. Run Project
php artisan serve


Open in browser:

http://127.0.0.1:8000

Project Structure

app/
 ├── Models/
 │    ├── Product.php
 │    └── ProductImage.php
 ├── Http/
 │    └── Controllers/
 │         └── ProductController.php

resources/
 └── views/
      └── products/
           ├── index.blade.php
           ├── create.blade.php
           ├── edit.blade.php
           ├── show.blade.php
           └── trash.blade.php

Excel / CSV Import & Export

Supported Import Columns

Your Excel/CSV file must contain:

name, description, price, quantity, category, sku

Export Functionality

Export all products as .xlsx or .csv

Download directly from the Product List page

Import Functionality

Upload Excel or CSV file

Validates data before inserting

Supports bulk insert
