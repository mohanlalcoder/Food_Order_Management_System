# QuickBite – Food Ordering and Order Management System

## Project Overview

QuickBite is a food ordering and order management application that allows customers to browse available food items, add items to their cart, place orders, and track order status.

The application is developed using React for the frontend, Laravel for the backend REST APIs, and MySQL for database management.

This project demonstrates full-stack development, API integration, database relationships, input validation, order processing, and automated testing.

## Features

- Display available food items with names, descriptions, prices, and images.
- Add food items to the shopping cart.
- Increase or decrease item quantities.
- Calculate the order total.
- Place orders using customer name, delivery address, and phone number.
- View order details and current status.
- Track order status through periodic API requests.
- Validate customer details and order items.
- Manage order statuses through backend APIs.
- Test backend APIs and frontend functionality.

## Technology Stack

### Frontend
- React
- Vite
- JavaScript
- HTML5
- CSS3
- Axios
- Vitest
- React Testing Library

### Backend
- PHP
- Laravel
- REST APIs
- Laravel Form Request Validation
- Eloquent ORM

### Database
- MySQL

### Development Tools
- Git and GitHub
- Postman
- Visual Studio Code

## Application Architecture

The application follows a frontend-backend architecture.

1. **React Frontend:** Displays the menu, shopping cart, checkout form, and order status.
2. **Laravel Backend:** Handles API requests, validates input, processes orders, and manages order status.
3. **MySQL Database:** Stores menu items, customer orders, and order item details.

The frontend communicates with the backend using HTTP requests to REST APIs.

## Database Structure

The application uses three main database tables.

### 1. menu_items
Stores food menu information.

- `id`
- `name`
- `description`
- `price`
- `image_url`
- `is_available`
- `created_at`
- `updated_at`

### 2. orders
Stores customer and order information.

- `id`
- `customer_name`
- `address`
- `phone`
- `status`
- `total_amount`
- `created_at`
- `updated_at`

### 3. order_items
Stores the food items associated with each order.

- `id`
- `order_id`
- `menu_item_id`
- `quantity`
- `unit_price`
- `created_at`
- `updated_at`

An order can contain multiple order items. Laravel Eloquent relationships are used to connect the database tables.

## Order Status Workflow

The order status follows this workflow:

`Received → Preparing → Out for Delivery → Delivered`

Orders may also be cancelled where permitted by the backend business rules.

The frontend periodically checks the backend for status updates so customers can track their orders.

## REST API Endpoints

| Method | Endpoint | Description |
|---|---|---|
| GET | `/api/menu` | Retrieve available menu items |
| GET | `/api/orders` | Retrieve orders |
| POST | `/api/orders` | Place a new order |
| GET | `/api/orders/{id}` | Retrieve a specific order |
| PATCH | `/api/orders/{id}/status` | Update order status |
| DELETE | `/api/orders/{id}` | Delete an order |

### Example: Place an Order

**Request:** `POST /api/orders`

```json
{
  "customer_name": "John Doe",
  "address": "123 Main Street, Chennai",
  "phone": "9876543210",
  "items": [
    {
      "menu_item_id": 1,
      "quantity": 2
    }
  ]
}
```

The backend validates the request, retrieves food prices from the database, calculates the total amount, and saves the order and its items.

## Prerequisites

Install the following software before running the application:

- PHP 8.2 or a version compatible with your Laravel installation
- Composer
- Node.js and npm
- MySQL
- Git

## Installation and Setup

### 1. Clone the Repository

```bash
git clone https://github.com/mohanlalcoder/Food_Order_Management_System.git
cd quickbite
```

Replace the repository URL with your actual GitHub repository URL.

### 2. Set Up the Laravel Backend

```bash
cd backend
composer install
```

Create the environment file:

```bash
cp .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

### 3. Configure the Database

Create a MySQL database named `quickbite`.

Update the database settings in `backend/.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=quickbite
DB_USERNAME=root
DB_PASSWORD=
```

Update the username and password according to your local MySQL configuration.

### 4. Run Migrations and Seed Sample Data

```bash
php artisan migrate
php artisan db:seed
```

If your project uses a dedicated menu seeder, ensure it is registered in `DatabaseSeeder.php` before running the seeding command.

### 5. Start the Laravel Server

```bash
php artisan serve
```

The backend will normally run at:

`http://127.0.0.1:8000`

### 6. Set Up the React Frontend

Open another terminal:

```bash
cd frontend
npm install
```

Create a `.env` file inside the `frontend` directory:

```env
VITE_API_URL=http://127.0.0.1:8000/api
```

Start the frontend:

```bash
npm run dev
```

Vite will display the local development URL, normally:

`http://localhost:5173`

Open this URL in your browser to use the application.

## Running Tests

### Backend Tests

From the `backend` directory, run:

```bash
php artisan test
```

Backend tests cover important workflows such as:

- Menu retrieval
- Successful order placement
- Request validation
- Server-side price calculation
- Unavailable menu item validation
- Order retrieval
- Order status transition validation
- Order deletion

Configure a dedicated test database before running tests. Do not run destructive tests against your production database.

### Frontend Tests

From the `frontend` directory, run:

```bash
npm test
```

Frontend tests cover UI behavior such as:

- Rendering menu items
- Adding items to the cart
- Submitting the checkout form

Ensure the Vitest configuration and test scripts are configured in `package.json` and `vite.config.js`.

## API Testing with Postman

The REST APIs can also be tested using Postman.

1. Start the Laravel backend.
2. Send a GET request to `http://127.0.0.1:8000/api/menu`.
3. Send a POST request to `http://127.0.0.1:8000/api/orders` with valid customer and item data.
4. Copy the returned order ID.
5. Use GET `/api/orders/{id}` to retrieve the order.
6. Use PATCH `/api/orders/{id}/status` to test a permitted status transition.

For the status update request, use JSON such as:

```json
{
  "status": "preparing"
}
```

## Security and Validation

- Validate customer information and order quantities on the backend.
- Calculate order totals using prices stored in the database.
- Use database transactions when creating orders.
- Keep environment variables and database credentials out of version control.
- Configure CORS to allow only the intended frontend origin in production.
- Protect order retrieval, status updates, and deletion with appropriate authorization before using the application in a real production environment.


## Author

**Mohanlal**

Full Stack Developer

## License

This project was developed as a full-stack application assessment project.