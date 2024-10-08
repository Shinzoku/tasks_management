# Task Management - Symfony 6.4

This task management application is developed using the Symfony 6.4 framework. It provides two interfaces:
- An **admin interface** to manage users, task lists, and tasks (CRUD).
- A **user interface** to view and update the progress of tasks in a task list.

## Features

### Admin Side
- Management of **users** (CRUD).
- Management of **task lists** (CRUD).
- Management of **tasks** (CRUD).
- **Email sending** to users (manually by the administrator).
- Option to **resend a verification email** if the user has not confirmed their registration in time.

### User Side (Public)
- **Registration** of a new user with email verification.
- **Login** to access the user interface.
- Viewing of **task lists**.
- Selection of one or more tasks to update the **progress**.

### Security
- **Admin Side** is accessible only to users with the `ROLE_ADMIN` role.
- **User Side** is accessible only to users with the `ROLE_USER` role.
- **Email verification** after registration: the confirmation email must be validated to access the features.
- Administrators can resend verification emails when necessary.

### Pagination and sorting
The application implements pagination using the **Pagerfanta** bundle. This allows you to break down large datasets into smaller, more manageable pages.

Installation :
```bash
composer require pagerfanta/pagerfanta
```
For integration with Symfony :
```bash
composer require pagerfanta/twig
```
#### Pagination
In the user, task list and task listing page, pagination controls are displayed at the bottom of the table to navigate through the available pages. The pagination controls use Bootstrap for styling, ensuring a responsive and user-friendly interface.

- **Previous/Next Page Navigation**: You can navigate between pages using the previous (`« Previous`) and next (`Next »`) arrows.
- **Page Numbers**: Click on the page numbers to jump to a specific page.

#### Sorting
Exemple for user listing page :

The application supports dynamic sorting on the user listing page. You can sort the data by clicking on the column headers. The columns that support sorting are:

- ID: Sorts the users by their ID.
- Firstname: Sorts the users alphabetically by their firstname.
- Lastname: Sorts the users alphabetically by their lastname.
- Email: Sorts the users by their email addresses.
- Roles: Sorts the users by their roles.
- IsVerified: Sorts the users by whether or not their email is verified.
- Creation Date: Sorts the users by the date they were created.
- Update Date: Sorts the users by the date they were updated.

How Sorting Works :

- When you click on a column header, the data is sorted in ascending order. Clicking again will toggle the sort order to descending.
- If a column is currently sorted, an arrow icon (`↑` for ascending or `↓` for descending) will be displayed next to the header.
- If no sorting is applied, a double arrow (`↕`) is displayed, indicating that the column is sortable.

### The first user with admin rights
For this project, I assume that I deliver the project to the client and that the client becomes the administrator when registering.

When the user registers, it checks the database to see if they are the first user to register :
- If he is first, he has the Admin role.
- Otherwise he has the User role.

### Future Features
- Real-time chat.

## Prerequisites

- PHP 8.2 or higher
- Symfony 6.4
- Composer
- Web server (Apache, Nginx, etc.)
- Database (MySQL, PostgreSQL, etc.)

## Installation

1. Clone the repository :

SSH :
```bash
git clone git@github.com:Shinzoku/tasks_management.git
```

HTTPS :
```bash
git clone https://github.com/Shinzoku/tasks_management.git
```
2. Install dependencies :

```bash
composer install
```
3. Configure the database in the `.env` file :

```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/nom_de_la_base"
```
4. Create the database and run migrations :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. Start the Symfony server :

```bash
symfony server:start
```

6. Access the application in your browser :

- Interface utilisateur : http://localhost:8000

## Role Management

- Upon registration, a user is assigned the `ROLE_USER` role.
- The administrator can assign the `ROLE_ADMIN` role through the admin interface to grant access to the admin section.

## Email Sending

The application is configured to send emails using Symfony's mailer service. Modify the mailer settings in the `.env` file :

```dotenv
MAILER_DSN=smtp://user:pass@smtp.example.com:port
```
### Resend Verification Emails

An administrator can resend a verification email to a user :

- The administrator can, through the email sending interface, decide to resend a confirmation email.

## Deployment

To deploy the application to production, follow these steps :

1. Configure the production environment in the `.env` file.

2. Run the compilation and migration commands :

```bash
composer install --no-dev --optimize-autoloader
php bin/console doctrine:migrations:migrate --env=prod
```

3. Ensure the cache and log directories have the correct permissions :

```bash
sudo chown -R www-data:www-data var/cache var/log
```

## Author

This project was created by Nicolas Bernon. Feel free to contact me for questions or suggestions.

## LICENSE

This project is licensed under the MIT license. See the [LICENSE](LICENCE.txt) file for more details.