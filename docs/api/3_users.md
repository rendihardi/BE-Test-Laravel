# Users API Contract

This module handles user operations including listing, creating, reading, updating, and deleting users, and assigning roles.

> [!IMPORTANT]
> All endpoints in this module are protected by the `role:Administrator` middleware. Only users with the `Administrator` role can access these routes.

---

## 1. List All Users (Unpaginated)
Retrieve all users in the system.

- **Endpoint:** `GET /api/users`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search users by name or email |
| `limit` | integer | No | nullable, min:1 | Limit the number of records returned |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data User",
    "data": [
        {
            "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
            "name": "Administrator",
            "email": "admin@gmail.com",
            "roles": [
                {
                    "id": 1,
                    "name": "Administrator",
                    "guard_name": "sanctum"
                }
            ]
        }
    ]
}
```

---

## 2. List Users (Paginated)
Retrieve paginated list of users.

- **Endpoint:** `GET /api/users/paginated`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search users by name or email |
| `row_per_page` | integer | Yes | required | Number of users per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data User",
    "data": {
        "data": [
            {
                "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
                "name": "Administrator",
                "email": "admin@gmail.com",
                "roles": [
                    {
                        "id": 1,
                        "name": "Administrator",
                        "guard_name": "sanctum"
                    }
                ]
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/users/paginated",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 3. Create User
Create a new user and assign roles by Role ID or Name.

- **Endpoint:** `POST /api/users`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | Yes | max:255 | Full name of the user |
| `email` | string | Yes | email, unique:users,email, max:255 | Unique email address |
| `password` | string | Yes | min:8 | User password |
| `roles` | array | No | nullable | Array of role names (strings) or role IDs (integers) |
| `roles.*` | string\|int | No | exists:roles (via ID or name) | Role must exist in the database |

#### Example Request Body
```json
{
    "name": "budianto",
    "email": "budi@gmail.com",
    "password": "konfirmasi",
    "roles": [1]
}
```

### Response
#### Success (201 Created)
```json
{
    "success": true,
    "message": "Data User Created",
    "data": {
        "uuid": "019f2fce-bb70-7129-9753-afd81f3d304e",
        "name": "budianto",
        "email": "budi@gmail.com",
        "roles": [
            {
                "id": 1,
                "name": "Administrator",
                "guard_name": "sanctum"
            }
        ]
    }
}
```

---

## 4. Get User Detail
Retrieve details of a user by UUID.

- **Endpoint:** `GET /api/users/{uuid}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data User Detail",
    "data": {
        "uuid": "019f2fce-bb70-7129-9753-afd81f3d304e",
        "name": "budianto",
        "email": "budi@gmail.com",
        "roles": [
            {
                "id": 1,
                "name": "Administrator",
                "guard_name": "sanctum"
            }
        ]
    }
}
```

#### Failure (404 Not Found)
```json
{
    "success": false,
    "message": "User not found.",
    "data": null
}
```

---

## 5. Update User
Modify an existing user's details and sync roles.

- **Endpoint:** `PUT /api/users/{uuid}`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | No | max:255 | Updated name of the user |
| `email` | string | No | email, unique:users,email,{uuid},uuid | Updated unique email address |
| `password` | string | No | nullable, min:8 | New password (if resetting) |
| `roles` | array | No | nullable | New array of role names or IDs to sync |
| `roles.*` | string\|int | No | exists:roles | Role must exist in the database |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data User Updated",
    "data": {
        "uuid": "019f2fce-bb70-7129-9753-afd81f3d304e",
        "name": "budianto edit",
        "email": "budiedit@gmail.com",
        "roles": [
            {
                "id": 2,
                "name": "Staff",
                "guard_name": "sanctum"
            }
        ]
    }
}
```

---

## 6. Delete User
Soft delete a user.

- **Endpoint:** `DELETE /api/users/{uuid}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data User Deleted",
    "data": null
}
```
