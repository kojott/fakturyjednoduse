# Fakturace - Invoice Management System

A comprehensive invoice management system built with Laravel, designed for Czech businesses to create, manage, and track invoices.

## Features

- **Customer Management**: Add, edit, and manage your customer database
- **Invoice Creation**: Create regular, proforma, and advance invoices
- **Invoice Management**: Track payment status, due dates, and generate PDF invoices
- **Dashboard**: Get an overview of your financial situation with statistics
- **Multi-user Support**: Each user has their own customers and invoices
- **API Access**: RESTful API for integration with other systems
- **Czech Localization**: Support for Czech tax regulations and ARES integration

## Requirements

- PHP 8.1 or higher
- Composer
- MySQL or PostgreSQL
- Node.js and NPM (for frontend assets)

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/yourusername/fakturace.git
   cd fakturace
   ```

2. Install PHP dependencies:
   ```
   composer install
   ```

3. Install JavaScript dependencies:
   ```
   npm install
   ```

4. Create a copy of the `.env.example` file:
   ```
   cp .env.example .env
   ```

5. Generate an application key:
   ```
   php artisan key:generate
   ```

6. Configure your database in the `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=fakturace
   DB_USERNAME=root
   DB_PASSWORD=
   ```

7. Run database migrations:
   ```
   php artisan migrate
   ```

8. Compile frontend assets:
   ```
   npm run dev
   ```

9. Start the development server:
   ```
   php artisan serve
   ```

10. Visit `http://localhost:8000` in your browser

## Usage

### User Registration and Login

1. Register a new account or login with existing credentials
2. After login, you'll be redirected to the dashboard

### Managing Customers

1. Navigate to "Zákazníci" in the sidebar
2. Add new customers with their company details
3. The system can validate Czech company IDs (IČO) through ARES integration

### Creating Invoices

1. Navigate to "Faktury" in the sidebar
2. Click "Vytvořit fakturu"
3. Select a customer, add invoice items, and set payment details
4. Save the invoice

### Managing Invoices

1. View all invoices in the "Faktury" section
2. Filter by status (paid, unpaid, cancelled)
3. Mark invoices as paid
4. Convert proforma invoices to regular invoices
5. Export invoices as PDF

### API Access

1. Generate an API token in your profile settings
2. Use the token to authenticate API requests
3. API documentation is available at `/api/documentation`

## Development

### Project Structure

The project follows standard Laravel structure:

- `app/Models` - Database models
- `app/Http/Controllers` - Controllers for web and API
- `app/Policies` - Authorization policies
- `database/migrations` - Database migrations
- `resources/views` - Blade templates
- `routes` - Web and API routes

### Key Components

- **User Management**: Authentication and user roles
- **Customer Management**: CRUD operations for customers
- **Invoice System**: Creation and management of invoices
- **PDF Generation**: Export invoices as PDF documents
- **API**: RESTful API for external integrations

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Acknowledgements

- [Laravel](https://laravel.com) - The web framework used
- [Bootstrap](https://getbootstrap.com) - Frontend framework
- [ARES API](https://wwwinfo.mfcr.cz/ares/ares_xml.html.cz) - Czech business registry API
