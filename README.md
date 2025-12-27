# 🔧 Maintenance Center Backend

Backend API for the Maintenance Center Management System, built with **Symfony 6.4**.

## 📋 Prerequisites

- **Docker & Docker Compose** (for the database)
- **PHP 8.1+** with extensions: `pdo_mysql`, `intl`, `mbstring`
- **Composer**
- **Symfony CLI** (recommended for local server)

## 🚀 Quick Start

### 1. Clone the repository
```bash
git clone <backend-repo-url>
cd group-5-back-end
```

### 2. Install dependencies
```bash
composer install
```

### 3. Start the database (Docker)
```bash
docker compose up -d
```
> The MySQL database runs on port **3308** to avoid conflicts with local installations.

### 4. Configure environment
Copy the `.env` file and adjust if needed:
```bash
cp .env .env.local
```
The default database configuration is:
```
DATABASE_URL="mysql://app_user:app_password@127.0.0.1:3308/maintenance_db?serverVersion=8.0.32&charset=utf8mb4"
```

### 5. Run migrations
```bash
php bin/console doctrine:migrations:migrate -n
```

### 6. Load fixtures (optional, creates test users)
```bash
php bin/console doctrine:fixtures:load -n
```
This creates:
- **Admin**: `admin@local.host` / `password`
- **Technician**: `tech@local.host` / `password`

### 7. Generate JWT keys (if not already done)
```bash
php bin/console lexik:jwt:generate-keypair
```

### 8. Start the server
```bash
symfony server:start
```
The API will be available at `http://127.0.0.1:8000/api` (port may vary).

## 📚 API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/login_check` | Authenticate and get JWT token |
| GET | `/api/machines` | List all machines |
| POST | `/api/machines` | Create a new machine |
| ... | ... | ... |

## 🛑 Stop the database
```bash
docker compose down
```

## 🗂 Project Structure

```
src/
├── Controller/    # API Controllers
├── Entity/        # Doctrine Entities (User, Machine, Intervention, etc.)
├── Repository/    # Database Repositories
├── DataFixtures/  # Test data seeders
```

## 👥 Team
- ELKHALDI Zakaria
- LABRIHI Ahmed
- KHIYATI Saad
