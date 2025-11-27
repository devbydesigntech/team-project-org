# API Documentation - Phase 1

Base URL: `http://localhost:8000/api/v1`

All endpoints return JSON responses using Laravel API Resources.

## Organizations

### List All Organizations
```
GET /organizations
```
Returns all organizations with their users and teams.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Dreamers Syndicate",
      "created_at": "2025-11-27T09:43:01.000000Z",
      "updated_at": "2025-11-27T09:43:01.000000Z",
      "users": [...],
      "teams": [...]
    }
  ]
}
```

### Create Organization
```
POST /organizations
Content-Type: application/json

{
  "name": "New Organization"
}
```

**Validation:**
- `name`: required, string, max:255

**Response:** 201 Created

### Show Organization
```
GET /organizations/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "name": "Dreamers Syndicate",
    "created_at": "2025-11-27T09:43:01.000000Z",
    "updated_at": "2025-11-27T09:43:01.000000Z"
  }
}
```

### Update Organization
```
PUT /organizations/{id}
Content-Type: application/json

{
  "name": "Updated Organization"
}
```

**Validation:**
- `name`: sometimes|required, string, max:255

**Response:** 200 OK

### Delete Organization
```
DELETE /organizations/{id}
```

**Response:** 204 No Content

---

## Roles

### List All Roles
```
GET /roles
```

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "executive"
    }
  ]
}
```

### Create Role
```
POST /roles
Content-Type: application/json

{
  "name": "manager"
}
```

**Validation:**
- `name`: required, string, max:255, unique:roles

**Response:** 201 Created

### Show Role
```
GET /roles/{id}
```

### Update Role
```
PUT /roles/{id}
Content-Type: application/json

{
  "name": "updated_role"
}
```

**Validation:**
- `name`: sometimes|required, string, max:255, unique:roles

**Response:** 200 OK

### Delete Role
```
DELETE /roles/{id}
```

**Response:** 204 No Content

---

## Users

### List All Users
```
GET /users
```
Returns all users with their organization, role, and teams.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "role_id": 1,
      "name": "Alice Executive",
      "email": "alice@dreamers.com",
      "created_at": "2025-11-27T09:43:01.000000Z",
      "updated_at": "2025-11-27T09:43:01.000000Z",
      "organization": {...},
      "role": {...},
      "teams": [...]
    }
  ]
}
```

**Note:** Password field is never returned in responses.

### Create User
```
POST /users
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "organization_id": 1,
  "role_id": 2
}
```

**Validation:**
- `name`: required, string, max:255
- `email`: required, string, email, max:255, unique:users
- `password`: required, string, min:8 (automatically hashed)
- `organization_id`: required, exists:organizations,id
- `role_id`: required, exists:roles,id

**Response:** 201 Created

### Show User
```
GET /users/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "role_id": 1,
    "name": "Alice Executive",
    "email": "alice@dreamers.com",
    "created_at": "2025-11-27T09:43:01.000000Z",
    "updated_at": "2025-11-27T09:43:01.000000Z",
    "organization": {...},
    "role": {...},
    "teams": [...]
  }
}
```

### Update User
```
PUT /users/{id}
Content-Type: application/json

{
  "name": "Updated Name",
  "email": "newemail@example.com"
}
```

**Validation:**
- All fields are optional (use sometimes)
- `name`: sometimes|required, string, max:255
- `email`: sometimes|required, string, email, max:255, unique:users,email,{id}
- `password`: sometimes|required, string, min:8
- `organization_id`: sometimes|required, exists:organizations,id
- `role_id`: sometimes|required, exists:roles,id

**Response:** 200 OK

### Delete User
```
DELETE /users/{id}
```

**Response:** 204 No Content

---

## Teams

### List All Teams
```
GET /teams
```
Returns all teams with their organization, manager, and members.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "organization_id": 1,
      "manager_id": 2,
      "name": "Engineering Team",
      "created_at": "2025-11-27T09:43:03.000000Z",
      "updated_at": "2025-11-27T09:43:03.000000Z",
      "organization": {...},
      "manager": {...},
      "members": [...]
    }
  ]
}
```

### Create Team
```
POST /teams
Content-Type: application/json

{
  "organization_id": 1,
  "manager_id": 2,
  "name": "New Team"
}
```

**Validation:**
- `name`: required, string, max:255
- `organization_id`: required, exists:organizations,id
- `manager_id`: nullable, exists:users,id

**Response:** 201 Created

### Show Team
```
GET /teams/{id}
```

**Response:**
```json
{
  "data": {
    "id": 1,
    "organization_id": 1,
    "manager_id": 2,
    "name": "Engineering Team",
    "created_at": "2025-11-27T09:43:03.000000Z",
    "updated_at": "2025-11-27T09:43:03.000000Z",
    "organization": {...},
    "manager": {...},
    "members": [...]
  }
}
```

### Update Team
```
PUT /teams/{id}
Content-Type: application/json

{
  "name": "Updated Team Name",
  "manager_id": 3
}
```

**Validation:**
- `name`: sometimes|required, string, max:255
- `organization_id`: sometimes|required, exists:organizations,id
- `manager_id`: nullable, exists:users,id

**Response:** 200 OK

### Delete Team
```
DELETE /teams/{id}
```

**Response:** 204 No Content

### Add Member to Team
```
POST /teams/{id}/members
Content-Type: application/json

{
  "user_id": 5,
  "team_role": "Developer"
}
```

**Validation:**
- `user_id`: required, exists:users,id
- `team_role`: nullable, string, max:255

**Response:** 200 OK

### Remove Member from Team
```
DELETE /teams/{id}/members/{userId}
```

**Response:**
```json
{
  "message": "Member removed successfully"
}
```

---

## Testing Endpoints

You can test these endpoints using curl:

```bash
# List organizations
curl http://localhost:8000/api/v1/organizations

# Create organization
curl -X POST http://localhost:8000/api/v1/organizations \
  -H "Content-Type: application/json" \
  -d '{"name":"Test Organization"}'

# Create user
curl -X POST http://localhost:8000/api/v1/users \
  -H "Content-Type: application/json" \
  -d '{
    "name":"John Doe",
    "email":"john@example.com",
    "password":"password123",
    "organization_id":1,
    "role_id":2
  }'

# Add team member
curl -X POST http://localhost:8000/api/v1/teams/1/members \
  -H "Content-Type: application/json" \
  -d '{"user_id":5,"team_role":"Developer"}'
```

---

## Error Responses

### Validation Error (422)
```json
{
  "message": "The name field is required.",
  "errors": {
    "name": [
      "The name field is required."
    ]
  }
}
```

### Not Found (404)
```json
{
  "message": "No query results for model [App\\Models\\Organization] {id}"
}
```

### Server Error (500)
```json
{
  "message": "Server Error"
}
```
