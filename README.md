# Ecommerce Order Challenge

## Contact Details
>**Creator** : Muhamad Risman
>**Email** : real.risman@gmail.com

## Requirements
  * PHP 7.1.3 or higher;
  * PDO-SQLite PHP extension enabled;
  
## Project Details
* **Framework** : Symfony 4.3.2
* **Mailing** : SendGrid

## Setup Project
* copy **.env** to **.env.local** for your local development
* setup your database and put the information into **.env.local**
* create account **https://sendgrid.com/** and put **SENDGRID_KEY** from your account to **.env.local**

## Run Project
* `composer install`
* `php bin/console doctrine:migrations:migrate`
* `php bin/console app:import`
* `php -S 127.0.0.1:8000 -t ./public` or `symfony server:start`

## List of Commands
### Import data challenge to database
* `php bin/console import:run`

### Export data challenge to any format (csv, json, yaml, xml)
* `php bin/console export:run {filename} {format}`
> **Example** : `php bin/console app:export order-summary csv`

### Also you can email the output file using this command
* `php bin/console export:run {filename} {format} {email}`
> **Example** : `php bin/console app:export order-summary csv real.risman@gmail.com`

## API documentation
* You can access on `127.0.0.1:8000/api`

## Other commands
* `php bin/console debug:router` to get list of routes
