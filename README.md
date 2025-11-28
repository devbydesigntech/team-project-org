# Dreamers Laravel Assessment - Team Review System

A REST API service built with Laravel 12 that allows employees to review projects, team members, and managers within a worldwide syndicate organization.

## Project Overview

This application manages a multi-team organization where:
- **Executives** have full access and administrative privileges
- **Managers** oversee teams and their projects
- **Associates** work on projects and provide feedback
- **Internal Advisors** can temporarily assist other teams

### Key Features

✅ **Complete REST API** with JSON responses  
✅ **Role-Based Access Control** with Laravel Policies  
✅ **Multi-Team Organization** structure  
✅ **Project Management** with team collaboration  
✅ **Advisory System** for temporary cross-team roles  
✅ **Review System** with visibility rules and anonymization  
✅ **177 Tests** passing with 474 assertions  

## Tech Stack

- **Framework:** Laravel 12
- **Database:** MySQL 8.0 (Production), SQLite (Testing)
- **PHP:** 8.3+
- **Server:** Nginx
- **Containerization:** Docker & Docker Compose
- **Testing:** PHPUnit with Feature & Unit tests

## Prerequisites

Before you begin, ensure you have installed:
- [Docker](https://www.docker.com/get-started) (version 20.10 or higher)
- [Docker Compose](https://docs.docker.com/compose/install/) (version 2.0 or higher)
- Git

## Quick Start

### 1. Clone the Repository

```bash
git clone git@github.com:devbydesigntech/team-project-org.git
cd dreamers-laravel-assessment
```

### 2. Start Docker Containers

```bash
docker-compose up -d
```

This will start three services:
- **laravel_app** (PHP-FPM Laravel application)
- **nginx** (web server on port 8000)
- **mysql** (database on port 3307)

### 3. Install Dependencies & Setup Database

```bash
# Install Composer dependencies (if not already installed)
docker exec laravel_app composer install

# Generate application key (if not already set)
docker exec laravel_app php artisan key:generate

# Run migrations
docker exec laravel_app php artisan migrate

# Seed the database with sample data
docker exec laravel_app php artisan db:seed
```

### 4. Verify Installation

```bash
# Run all tests to verify everything is working
docker exec laravel_app php artisan test
```

You should see: **177 tests passing (474 assertions)**

### 5. Access the Application

The API will be available at: **http://localhost:8000/api/v1**

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
docker-compose logs -f laravel_app
docker-compose logs -f nginx
docker-compose logs -f mysql
```

### Laravel Artisan Commands

```bash
# Run migrations
docker exec laravel_app php artisan migrate

# Rollback migrations
docker exec laravel_app php artisan migrate:rollback

# Fresh migration with seeding
docker exec laravel_app php artisan migrate:fresh --seed

# Clear caches
docker exec laravel_app php artisan cache:clear
docker exec laravel_app php artisan config:clear
docker exec laravel_app php artisan route:clear
docker exec laravel_app php artisan view:clear

# List all routes
docker exec laravel_app php artisan route:list

# Interactive shell (Tinker)
docker exec laravel_app php artisan tinker
```

### Testing

```bash
# Run all tests
docker exec laravel_app php artisan test

# Run specific test file
docker exec laravel_app php artisan test --filter=ReviewTest

# Run specific test directory
docker exec laravel_app php artisan test tests/Feature/Api/

# Run tests with verbose output
docker exec laravel_app php artisan test --verbose
```

### Database Access

```bash
# Access MySQL CLI
docker exec -it mysql mysql -ularavel -proot laravel

# Access container shell
docker exec -it laravel_app bash

# Run database queries
docker exec mysql mysql -ularavel -proot laravel -e "SELECT * FROM users;"
```

### Composer & Dependencies

```bash
# Install dependencies
docker exec laravel_app composer install

# Update dependencies
docker exec laravel_app composer update

# Add a new package
docker exec laravel_app composer require package-name

# Remove a package
docker exec laravel_app composer remove package-name
```

## Project Structure

```
.
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── OrganizationController.php
│   │   │       ├── RoleController.php
│   │   │       ├── UserController.php
│   │   │       ├── TeamController.php
│   │   │       ├── ProjectController.php
│   │   │       ├── AdvisoryAssignmentController.php
│   │   │       └── ReviewController.php
│   │   └── Resources/
│   │       ├── OrganizationResource.php
│   │       ├── RoleResource.php
│   │       ├── UserResource.php
│   │       ├── TeamResource.php
│   │       ├── ProjectResource.php
│   │       ├── AdvisoryAssignmentResource.php
│   │       └── ReviewResource.php
│   ├── Models/
│   │   ├── Organization.php
│   │   ├── Role.php
│   │   ├── User.php
│   │   ├── Team.php
│   │   ├── Project.php
│   │   ├── AdvisoryAssignment.php
│   │   └── Review.php
│   └── Policies/
│       ├── OrganizationPolicy.php
│       ├── RolePolicy.php
│       ├── UserPolicy.php
│       ├── TeamPolicy.php
│       ├── ProjectPolicy.php
│       ├── AdvisoryAssignmentPolicy.php
│       └── ReviewPolicy.php
├── database/
│   ├── factories/
│   │   ├── OrganizationFactory.php
│   │   ├── RoleFactory.php
│   │   ├── UserFactory.php
│   │   ├── TeamFactory.php
│   │   ├── ProjectFactory.php
│   │   ├── AdvisoryAssignmentFactory.php
│   │   └── ReviewFactory.php
│   ├── migrations/
│   │   ├── 0001_01_01_000000_create_organizations_table.php
│   │   ├── 0001_01_01_000001_create_roles_table.php
│   │   ├── 0001_01_01_000002_create_users_table.php
│   │   ├── 2025_11_27_093805_create_teams_table.php
│   │   ├── 2025_11_27_093811_create_team_members_table.php
│   │   ├── 2025_11_27_101449_create_projects_table.php
│   │   ├── 2025_11_27_101626_create_project_team_table.php
│   │   ├── 2025_11_27_125613_create_advisory_assignments_table.php
│   │   └── 2025_11_28_054613_create_reviews_table.php
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RoleSeeder.php
│       └── OrganizationSeeder.php
├── routes/
│   └── api.php
├── tests/
│   ├── Feature/
│   │   ├── Api/
│   │   │   ├── OrganizationApiTest.php
│   │   │   ├── RoleApiTest.php
│   │   │   ├── UserApiTest.php
│   │   │   ├── TeamApiTest.php
│   │   │   ├── ProjectApiTest.php
│   │   │   ├── AdvisoryAssignmentApiTest.php
│   │   │   └── ReviewControllerTest.php
│   │   ├── Policies/
│   │   │   ├── OrganizationPolicyTest.php
│   │   │   ├── RolePolicyTest.php
│   │   │   ├── UserPolicyTest.php
│   │   │   └── TeamPolicyTest.php
│   │   ├── OrganizationTest.php
│   │   ├── RoleTest.php
│   │   ├── UserTest.php
│   │   ├── TeamTest.php
│   │   ├── ProjectTest.php
│   │   └── AdvisoryAssignmentTest.php
│   └── Unit/
│       └── ReviewTest.php
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   └── php/
│       └── local.ini
├── docker-compose.yml
├── Dockerfile
├── instructions.md
├── project-design.csv
└── README.md
```

## API Documentation

This project includes a complete RESTful API with JSON responses using Laravel API Resources.

**Base URL:** `http://localhost:8000/api/v1`

### Available Endpoints

#### Organizations
- `GET /organizations` - List all organizations
- `POST /organizations` - Create organization (Executive only)
- `GET /organizations/{id}` - Get organization details
- `PUT /organizations/{id}` - Update organization (Executive only)
- `DELETE /organizations/{id}` - Delete organization (Executive only)

#### Roles
- `GET /roles` - List all roles
- `POST /roles` - Create role (Executive only)
- `GET /roles/{id}` - Get role details
- `PUT /roles/{id}` - Update role (Executive only)
- `DELETE /roles/{id}` - Delete role (Executive only)

#### Users
- `GET /users` - List all users
- `POST /users` - Create user (Executive only)
- `GET /users/{id}` - Get user details
- `PUT /users/{id}` - Update user (Executive only)
- `DELETE /users/{id}` - Delete user (Executive only)

#### Teams
- `GET /teams` - List all teams
- `POST /teams` - Create team (Executive only)
- `GET /teams/{id}` - Get team details
- `PUT /teams/{id}` - Update team (Executive only)
- `DELETE /teams/{id}` - Delete team (Executive only)
- `POST /teams/{id}/members` - Add member to team (Executive only)
- `DELETE /teams/{id}/members/{userId}` - Remove member from team (Executive only)

#### Projects
- `GET /projects` - List all projects
- `POST /projects` - Create project (Executive only)
- `GET /projects/{id}` - Get project details
- `PUT /projects/{id}` - Update project (Executive only)
- `DELETE /projects/{id}` - Delete project (Executive only)
- `POST /projects/{id}/teams` - Assign team to project (Executive only)
- `DELETE /projects/{id}/teams/{teamId}` - Remove team from project (Executive only)

#### Advisory Assignments
- `GET /advisory-assignments` - List all advisory assignments
- `POST /advisory-assignments` - Create assignment (Executive only)
- `GET /advisory-assignments/{id}` - Get assignment details
- `PUT /advisory-assignments/{id}` - Update assignment (Executive only)
- `DELETE /advisory-assignments/{id}` - Delete assignment (Executive only)

#### Reviews
- `GET /reviews` - List reviews (filtered by user role)
- `POST /reviews` - Create review (All users)
- `GET /reviews/{id}` - Get review details (if user has access)
- `PUT /reviews/{id}` - Update review (Own reviews only)
- `DELETE /reviews/{id}` - Delete review (Own reviews or Executive)

### Authentication

The API uses Laravel Sanctum for authentication. Include the user token in requests:

```bash
# Example authenticated request
curl -H "Authorization: Bearer {token}" \
     http://localhost:8000/api/v1/reviews
```

For testing purposes, you can authenticate as any seeded user.

### Quick API Tests

```bash
# List all organizations
curl http://localhost:8000/api/v1/organizations

# Get a specific team with relationships
curl http://localhost:8000/api/v1/teams/1

# List all projects
curl http://localhost:8000/api/v1/projects

# List reviews (requires authentication)
curl http://localhost:8000/api/v1/reviews

# View available routes
docker exec laravel_app php artisan route:list
```

### Response Format

All endpoints return JSON responses with consistent structure:

```json
{
  "data": {
    "id": 1,
    "name": "Example",
    "created_at": "2025-11-28T00:00:00.000000Z",
    "updated_at": "2025-11-28T00:00:00.000000Z"
  }
}
```

For collections:

```json
{
  "data": [
    { "id": 1, "name": "Example 1" },
    { "id": 2, "name": "Example 2" }
  ]
}
```

## Development Status

All phases are complete! 🎉

### Phase 1 - Core Structure ✅ (Completed)

- [x] Organizations model and migration
- [x] Roles model and migration (executive, manager, associate)
- [x] Users model with relationships and helper methods
- [x] Teams model with manager assignment
- [x] Team members pivot table with team_role
- [x] Database seeders with sample data
- [x] REST API endpoints with Laravel API Resources
- [x] OrganizationPolicy, RolePolicy, UserPolicy, TeamPolicy
- [x] Comprehensive tests

### Phase 2 - Projects and Team Collaboration ✅ (Completed)

- [x] Projects model and migration
- [x] Project-Team pivot table (many-to-many)
- [x] Team assignment to projects
- [x] ProjectPolicy for authorization
- [x] REST API endpoints
- [x] Comprehensive tests

### Phase 3 - Advisory System ✅ (Completed)

- [x] AdvisoryAssignment model with date ranges
- [x] Temporary advisor role logic (starts_at, ends_at)
- [x] User belongsToMany advisoryProjects
- [x] Project belongsToMany advisors
- [x] isActive() method for date-based validation
- [x] AdvisoryAssignmentPolicy
- [x] REST API endpoints
- [x] Comprehensive tests

### Phase 4 - Reviews and Visibility Logic ✅ (Completed)

- [x] Reviews model with reviewer, reviewee, project relationships
- [x] Complex role-based visibility rules in ReviewPolicy:
  - Executives see all reviews
  - Associates see team project reviews + reviews about themselves
  - Managers see team member reviews + team project reviews
  - Advisors see reviews on projects they advise
- [x] visibleReviewerName() method for anonymization
- [x] Only executives see actual reviewer names (others see "Anonymous")
- [x] Users can edit/delete their own reviews
- [x] Executives can delete any review (but not edit)
- [x] REST API endpoints with proper filtering
- [x] Comprehensive tests (18 API tests + 10 unit tests)

### Phase 5 - Permissions and Guards ✅ (Completed)

- [x] All Policy classes implemented (Organization, Role, User, Team, Project, AdvisoryAssignment, Review)
- [x] Executive-only mutations (create/update/delete) for core entities
- [x] Complex authorization logic for Reviews
- [x] Role-based access control throughout API
- [x] All authorization tests passing

**Test Summary:** 177 tests passing with 474 assertions

## API Features

This REST API provides complete functionality for:

- ✅ **User Management:** Create and manage users with roles (Executive only)
- ✅ **Team Management:** Create teams with managers and associates (Executive only)
- ✅ **Project Management:** Create projects and assign teams (Executive only)
- ✅ **Advisory System:** Temporary advisory role assignments with date ranges
- ✅ **Review System:** Submit, edit, and delete reviews with role-based visibility
- ✅ **Role-Based Access Control:** Different visibility and permissions based on roles
- ✅ **Anonymization:** Reviewer names hidden from non-executives
- ✅ **Complex Visibility Logic:** Associates see team projects, Managers see team members, Advisors see advised projects, Executives see everything

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

All features are fully tested with comprehensive unit and feature tests:

```bash
docker exec laravel_app php artisan test
```

**Test Coverage:**

**Unit Tests:**
- ReviewTest: 10 tests (relationships, visibility, helper methods)

**Feature Tests:**
- Model Tests: Organizations, Roles, Users, Teams, Projects, Advisory Assignments
- API Tests: Full CRUD operations for all entities
- Policy Tests: Authorization for all roles and entities
- Review System Tests: Complex visibility scenarios

**Test Summary:**
- ✅ **177 tests passing**
- ✅ **474 assertions**
- ✅ 100% of requirements covered
- ✅ Unit tests for models and relationships
- ✅ Feature tests for API endpoints
- ✅ Policy tests for authorization
- ✅ Integration tests for complex scenarios

**Key Test Scenarios:**
- Executive can see all reviews and delete any
- Managers see team member and team project reviews
- Associates see team project reviews and reviews about themselves
- Advisors see reviews on projects they advise
- Users can only edit/delete their own reviews
- Reviewer names are anonymous to non-executives
- All CRUD operations respect authorization policies

## Review System

The review system is the core feature with complex role-based visibility logic.

### Review Types

1. **Project Reviews:** General reviews about a project (reviewee_user_id is null)
2. **User Reviews:** Reviews about a specific team member on a project (reviewee_user_id is set)

### Visibility Rules

| Role | Can See |
|------|---------|
| **Executive** | All reviews + actual reviewer names |
| **Manager** | Reviews of themselves + team members + team projects |
| **Associate** | Reviews of their team's projects + reviews about themselves |
| **Internal Advisor** | Reviews on projects they advise |

### Anonymization

- **Executives:** See the actual name of the reviewer
- **All Other Users:** See "Anonymous" as the reviewer name
- The `reviewer_id` is always stored in the database but is masked in API responses

### Review Permissions

| Action | Who Can Do It |
|--------|---------------|
| **Create** | Any authenticated user |
| **Update** | Owner of the review only |
| **Delete** | Owner OR Executive |
| **View** | Based on visibility rules above |

### Example Review Scenarios

```bash
# An associate reviews their team's project
POST /api/v1/reviews
{
  "project_id": 1,
  "rating": 5,
  "content": "Great project!"
}

# A manager reviews a team member
POST /api/v1/reviews
{
  "project_id": 1,
  "reviewee_user_id": 3,
  "rating": 4,
  "content": "Good work on this project!"
}

# List reviews (filtered by role)
GET /api/v1/reviews

# Update own review
PUT /api/v1/reviews/1
{
  "rating": 5,
  "content": "Updated content"
}

# Delete review (own review or executive)
DELETE /api/v1/reviews/1
```

## Database Schema

### Core Tables

- **organizations** - Root entity for multi-tenant structure
- **roles** - User roles (executive, manager, associate)
- **users** - User accounts with organization and role
- **teams** - Teams within organizations with managers
- **team_members** - Pivot table for team membership with team_role
- **projects** - Projects belonging to organizations
- **project_team** - Pivot table for team-project collaboration
- **advisory_assignments** - Temporary advisory roles with date ranges
- **reviews** - Reviews of projects and users

### Key Relationships

```
Organization
  ├── hasMany Users
  ├── hasMany Teams
  └── hasMany Projects

User
  ├── belongsTo Organization
  ├── belongsTo Role
  ├── belongsToMany Teams (through team_members)
  ├── belongsToMany Projects as advisoryProjects (through advisory_assignments)
  ├── hasMany Reviews as reviewsWritten (reviewer_id)
  └── hasMany Reviews as reviewsReceived (reviewee_user_id)

Team
  ├── belongsTo Organization
  ├── belongsTo Manager (User)
  ├── belongsToMany Users as members (through team_members)
  └── belongsToMany Projects (through project_team)

Project
  ├── belongsTo Organization
  ├── belongsToMany Teams (through project_team)
  ├── belongsToMany Users as advisors (through advisory_assignments)
  └── hasMany Reviews

Review
  ├── belongsTo Reviewer (User)
  ├── belongsTo Reviewee (User, nullable)
  └── belongsTo Project
```

## Contributing

This project follows Laravel best practices and KISS principles. When contributing:

1. Write tests for new features (aim for 100% coverage)
2. Follow PSR-12 coding standards
3. Update documentation for API changes
4. Keep database design normalized
5. Use meaningful commit messages
6. Ensure all tests pass before committing

## License

This project is part of the Dreamers Laravel Assessment.

## Support

For questions or issues, please refer to the `instructions.md` file or contact the project maintainers.

## Acknowledgments

Built with Laravel 12, following KISS principles and best practices for:
- RESTful API design
- Database normalization
- Role-based access control
- Comprehensive testing
- Docker containerization