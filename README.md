# Dreamers Laravel Assessment - Team Review System

A REST API service built with Laravel 12 that allows employees to review projects, team members, and managers within a worldwide syndicate organization.

## Project Overview

This application manages a multi-team organization where:
- **Executives** have full access and administrative privileges
- **Managers** oversee teams and their projects
- **Associates** work on projects and provide feedback
- **Internal Advisors** can temporarily assist other teams

## Tech Stack

- **Framework:** Laravel 12
- **Database:** MySQL 8.0
- **PHP:** 8.3+
- **Server:** Nginx
- **Containerization:** Docker & Docker Compose

## Prerequisites

Before you begin, ensure you have installed:
- [Docker](https://www.docker.com/get-started) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 2.0 or higher)
- Git

## Quick Start

### 1. Clone the Repository

```bash
git clone git@github.com:devbydesigntech/team-project-org.git
cd team-project-org
```

### 2. Start Docker Containers

```bash
docker-compose up -d
```

This will start three services:
- **PHP-FPM** (Laravel application)
- **Nginx** (web server on port 8000)
- **MySQL** (database on port 3307)

### 3. Install Dependencies & Setup Database

```bash
# Install Composer dependencies
docker-compose exec app composer install

# Generate application key
docker-compose exec app php artisan key:generate

# Run migrations
docker-compose exec app php artisan migrate

# Seed the database with sample data
docker-compose exec app php artisan db:seed
```

### 4. Access the Application

The application will be available at: **http://localhost:8000**

## Database Configuration

The MySQL database is configured with:
- **Database:** laravel
- **Username:** laravel
- **Password:** root
- **Host (internal):** mysql
- **Port (external):** 3307

To modify these settings, update the `.env` file.

## Seeded Data

The database comes with pre-seeded data for testing:

**Organization:** Dreamers Syndicate

**Roles:**
- executive
- manager
- associate

**Users:**
- Alice Executive (alice@dreamers.com) - Executive
- Bob Manager (bob@dreamers.com) - Manager
- Carol Manager (carol@dreamers.com) - Manager
- David Associate (david@dreamers.com) - Associate
- Eve Associate (eve@dreamers.com) - Associate
- Frank Associate (frank@dreamers.com) - Associate
- Grace Associate (grace@dreamers.com) - Associate

**Default Password:** `password` (for all users)

**Teams:**
- **Engineering Team** (Manager: Bob)
  - David Associate - Senior Developer
  - Eve Associate - Developer
- **Design Team** (Manager: Carol)
  - Frank Associate - Senior Designer
  - Grace Associate - Designer

## Common Commands

### Application Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Laravel Artisan Commands

```bash
# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Fresh migration with seeding
docker-compose exec app php artisan migrate:fresh --seed

# Clear caches
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

# Interactive shell (Tinker)
docker-compose exec app php artisan tinker
```

### Testing

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test --filter=UserTest

# Run tests with coverage
docker-compose exec app php artisan test --coverage
```

### Database Access

```bash
# Access MySQL CLI
docker-compose exec mysql mysql -ularavel -proot laravel

# Access container shell
docker-compose exec app bash
```

### Composer & Dependencies

```bash
# Install dependencies
docker-compose exec app composer install

# Update dependencies
docker-compose exec app composer update

# Add a new package
docker-compose exec app composer require package-name
```

## Project Structure

```
.
├── app/
│   ├── Models/
│   │   ├── Organization.php
│   │   ├── Role.php
│   │   ├── User.php
│   │   └── Team.php
│   └── ...
├── database/
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_organizations_table.php
│   │   ├── 0001_01_01_000001_create_roles_table.php
│   │   ├── 0001_01_01_000002_create_users_table.php
│   │   ├── 2025_11_27_093805_create_teams_table.php
│   │   └── 2025_11_27_093811_create_team_members_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       └── OrganizationSeeder.php
├── tests/
│   └── Feature/
│       ├── OrganizationTest.php
│       ├── RoleTest.php
│       ├── UserTest.php
│       └── TeamTest.php
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── local.ini
├── docker-compose.yml
├── Dockerfile
└── README.md
```

## Development Status

### Phase 1 - Core Structure ✅ (Completed)

- [x] Organizations model and migration
- [x] Roles model and migration (executive, manager, associate)
- [x] Users model with relationships and helper methods
- [x] Teams model with manager assignment
- [x] Team members pivot table
- [x] Database seeders
- [x] Comprehensive tests (25 passing)

### Phase 2 - Projects and Team Collaboration (In Progress)

- [ ] Projects model and migration
- [ ] Project-Team pivot table
- [ ] Tests

### Phase 3 - Advisory System (Planned)

- [ ] Advisory assignments model
- [ ] Temporary advisor role logic
- [ ] Tests

### Phase 4 - Reviews and Visibility Logic (Planned)

- [ ] Reviews model
- [ ] Role-based visibility rules
- [ ] Anonymous reviewer feature
- [ ] Tests

### Phase 5 - Permissions and Guards (Planned)

- [ ] Policy classes
- [ ] Role-based authorization
- [ ] API endpoints
- [ ] Tests

## API Features (Coming Soon)

This REST API will support:

- **User Management:** Create and manage users with roles
- **Team Management:** Create teams with managers and associates
- **Project Management:** Create projects and assign teams
- **Advisory System:** Temporary advisory role assignments
- **Review System:** Submit, edit, and delete reviews
- **Role-Based Access Control:** Different visibility and permissions based on roles

## Troubleshooting

### Port Already in Use

If port 8000 or 3307 is already in use, modify `docker-compose.yml`:

```yaml
nginx:
  ports:
    - "8080:80"  # Change from 8000

mysql:
  ports:
    - "3308:3306"  # Change from 3307
```

### Permission Issues

```bash
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### MySQL Connection Issues

Wait a few seconds for MySQL to initialize, then retry:

```bash
docker-compose exec app php artisan migrate
```

### Rebuild Everything

```bash
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d
```

Then run the setup commands again (install, migrate, seed).

## Contributing

This project follows Laravel best practices and KISS principles. When contributing:

1. Write tests for new features
2. Follow PSR-12 coding standards
3. Update documentation
4. Keep database design normalized
5. Use meaningful commit messages

## Testing

All Phase 1 features are fully tested:

```bash
docker-compose exec app php artisan test
```

**Current Test Coverage:**
- OrganizationTest: 4 tests
- RoleTest: 4 tests
- UserTest: 8 tests
- TeamTest: 7 tests

**Total: 25 tests passing (54 assertions)**

## License

This project is part of the Dreamers Laravel Assessment.

## Support

For questions or issues, please refer to the `instructions.md` file or contact the project maintainers.