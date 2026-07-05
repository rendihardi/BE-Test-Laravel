# Roles API Contract

This module handles role operations including listing, creating, reading, updating, and deleting roles and assigning permissions to roles.

---

## 1. List All Roles (Unpaginated)
Retrieve all roles.

- **Endpoint:** `GET /api/roles`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search roles by name |
| `limit` | integer | No | nullable, min:1 | Limit the number of records returned |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Role",
    "data": [
        {
            "id": 1,
            "name": "Administrator",
            "guard_name": "sanctum",
            "permissions": []
        },
        {
            "id": 2,
            "name": "Staff",
            "guard_name": "sanctum",
            "permissions": []
        }
    ]
}
```

---

## 2. List Roles (Paginated)
Retrieve paginated list of roles.

- **Endpoint:** `GET /api/roles/paginated`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search roles by name |
| `row_per_page` | integer | Yes | required | Number of roles per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Role",
    "data": {
        "data": [
            {
                "id": 1,
                "name": "Administrator",
                "guard_name": "sanctum",
                "permissions": []
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 2,
            "path": "http://127.0.0.1:8000/api/roles/paginated",
            "per_page": 1,
            "to": 1,
            "total": 2
        }
    }
}
```

---

## 3. Create Role
Create a new role with optional permission names.

- **Endpoint:** `POST /api/roles`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | Yes | unique:roles,name, max:125 | The unique name of the role |
| `guard_name` | string | No | nullable, max:125 | The guard name (defaults to `sanctum`) |
| `permissions` | array | No | nullable | Array of permission names |
| `permissions.*`| string | No | exists:permissions,name | Permission names that must exist in `permissions` table |

#### Example Request Body
```json
{
    "name": "head-warehouse",
    "permissions": ["view-products", "create-transactions"]
}
```

### Response
#### Success (201 Created)
```json
{
    "success": true,
    "message": "Data Role Created",
    "data": {
        "id": 3,
        "name": "head-warehouse",
        "guard_name": "sanctum",
        "permissions": [
            {
                "id": 5,
                "name": "view-products",
                "guard_name": "sanctum"
            }
        ],
        "created_at": "2026-07-05T01:23:43.000000Z",
        "updated_at": "2026-07-05T01:23:43.000000Z"
    }
}
```

---

## 4. Get Role Detail
Retrieve a role's detailed information by ID.

- **Endpoint:** `GET /api/roles/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Role Detail",
    "data": {
        "id": 3,
        "name": "head-warehouse",
        "guard_name": "sanctum",
        "permissions": []
    }
}
```

#### Failure (404 Not Found)
```json
{
    "success": false,
    "message": "Role not found.",
    "data": null
}
```

---

## 5. Update Role
Modify an existing role's name and sync its permissions.

- **Endpoint:** `PUT /api/roles/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | No | unique:roles,name, max:125 | The unique name of the role |
| `guard_name` | string | No | nullable, max:125 | The guard name |
| `permissions` | array | No | nullable | Array of permission names to sync |
| `permissions.*`| string | No | exists:permissions,name | Permission names that must exist |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Role Updated",
    "data": {
        "id": 3,
        "name": "head-warehouse-lead",
        "guard_name": "sanctum",
        "permissions": []
    }
}
```

---

## 6. Delete Role
Remove a role from the database.

- **Endpoint:** `DELETE /api/roles/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Role Deleted",
    "data": null
}
```
