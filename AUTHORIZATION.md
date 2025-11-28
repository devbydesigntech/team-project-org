# Authorization Documentation - Phase 1

This document describes the authorization policies implemented for Phase 1 of the Dreamers Laravel Assessment project.

## Overview

Authorization policies have been implemented to ensure that only users with the appropriate roles can perform certain actions. The system uses Laravel's Policy classes to enforce these rules.

## Role-Based Permissions

### Executive Role

Executives have full administrative privileges and can:

- ✅ Create, update, and delete **Organizations**
- ✅ Create, update, and delete **Roles**
- ✅ Add **Users** to the company
- ✅ Update user information (including role assignments)
- ✅ Delete users from the company
- ✅ Create, update, and delete **Teams**
- ✅ Assign users to teams
- ✅ Remove users from teams
- ✅ View all resources

**Key Responsibilities:**
- Only executives can add users to the company
- Only executives can create teams
- Only executives can assign users to teams/roles

### Manager Role

Managers have limited privileges:

- ✅ View organizations
- ✅ View roles
- ✅ View users
- ✅ View teams
- ❌ **Cannot** create, update, or delete any resources
- ❌ **Cannot** add users to the company
- ❌ **Cannot** create teams
- ❌ **Cannot** assign users to teams

### Associate Role

Associates have view-only privileges:

- ✅ View organizations
- ✅ View roles
- ✅ View users
- ✅ View teams
- ❌ **Cannot** create, update, or delete any resources
- ❌ **Cannot** add users to the company
- ❌ **Cannot** create teams
- ❌ **Cannot** assign users to teams

## Implementation Details

### Policy Classes

Four policy classes have been created:

1. **OrganizationPolicy** (`app/Policies/OrganizationPolicy.php`)
   - Controls access to organization CRUD operations
   - Only executives can create/update/delete
   - All users can view

2. **RolePolicy** (`app/Policies/RolePolicy.php`)
   - Controls access to role CRUD operations
   - Only executives can create/update/delete
   - All users can view

3. **UserPolicy** (`app/Policies/UserPolicy.php`)
   - Controls access to user CRUD operations
   - Only executives can create (add users to company)
   - Only executives can update (including role assignments)
   - Only executives can delete
   - All users can view
   - Includes custom `assignRole()` method

4. **TeamPolicy** (`app/Policies/TeamPolicy.php`)
   - Controls access to team CRUD operations
   - Only executives can create/update/delete
   - Only executives can add/remove members
   - All users can view
   - Includes custom `addMember()` and `removeMember()` methods

### Controller Authorization

All API controllers use the `authorize()` method to check permissions before performing actions:

```php
// Example from OrganizationController
public function store(Request $request)
{
    $this->authorize('create', Organization::class);
    // ... rest of the method
}

public function update(Request $request, Organization $organization)
{
    $this->authorize('update', $organization);
    // ... rest of the method
}
```

### Authorization Responses

When a user attempts an unauthorized action, the API returns:

**403 Forbidden**
```json
{
  "message": "This action is unauthorized."
}
```

## Testing

Comprehensive authorization tests have been created in `tests/Feature/Policies/`:

- **OrganizationPolicyTest.php** - 8 tests
- **RolePolicyTest.php** - 8 tests
- **UserPolicyTest.php** - 8 tests
- **TeamPolicyTest.php** - 12 tests

**Total: 36 authorization tests, all passing ✅**

### Test Coverage

Each policy test suite covers:
- Executive **can** perform administrative actions
- Manager **cannot** perform administrative actions
- Associate **cannot** perform administrative actions
- All roles **can** view resources

### Running Authorization Tests

```bash
# Run all policy tests
docker exec laravel_app php artisan test --filter=Policy

# Run specific policy tests
docker exec laravel_app php artisan test --filter=OrganizationPolicyTest
docker exec laravel_app php artisan test --filter=UserPolicyTest
docker exec laravel_app php artisan test --filter=TeamPolicyTest
docker exec laravel_app php artisan test --filter=RolePolicyTest
```

## API Usage Examples

### Authenticated Requests

To make authorized API requests, users must be authenticated. In tests, this is done using:

```php
$this->actingAs($executive)->postJson('/api/v1/organizations', $data);
```

### Executive Creating an Organization

```bash
# As an executive (authenticated)
curl -X POST http://localhost:8000/api/v1/organizations \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {executive_token}" \
  -d '{"name":"New Organization"}'

# Response: 201 Created
```

### Manager Attempting to Create an Organization

```bash
# As a manager (authenticated)
curl -X POST http://localhost:8000/api/v1/organizations \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer {manager_token}" \
  -d '{"name":"New Organization"}'

# Response: 403 Forbidden
{
  "message": "This action is unauthorized."
}
```

## Future Enhancements (Phase 4+)

When reviews are implemented in Phase 4, additional policies will be needed:

- **ReviewPolicy** - Control who can create/edit/delete reviews
  - All users can create reviews
  - Users can edit/delete their own reviews
  - Executives can delete (but not edit) any review
  - Only executives can see reviewer names

## Summary

The authorization system ensures that:

1. ✅ Only executives can add users to the company
2. ✅ Only executives can create projects and teams
3. ✅ Only executives can assign users to teams/roles
4. ✅ Managers and associates have appropriate view-only access
5. ✅ All authorization rules are enforced at the controller level
6. ✅ Comprehensive tests verify all permissions are working correctly

All Phase 1 authorization requirements have been successfully implemented and tested.
