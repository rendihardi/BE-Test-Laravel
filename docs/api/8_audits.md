# Audit Trails API Contract

This module provides history tracking for changes made to database models (Users, Roles, Categories, Products).

> [!IMPORTANT]
> All endpoints in this module are protected by the `role:Administrator` middleware. Only users with the `Administrator` role can access these routes.

---

## 1. User Audits
Retrieve history logs for changes to User accounts.

- **Endpoint:** `GET /api/audits/users`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search by IP, user agent, or changed values |
| `event` | string | No | nullable, in: created, updated, deleted, restored | Filter by event type |
| `auditable_id` | string (uuid)| No | nullable | Filter by specific User UUID |
| `row_per_page` | integer | No | nullable, default: 10, max: 100 | Items per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "User Audits Data",
    "data": {
        "data": [
            {
                "id": "019f2fd0-ee33-7185-bc99-ad13cda7de11",
                "event": "created",
                "auditable_type": "App\\Models\\User",
                "auditable_id": "019f2fce-bb70-7129-9753-afd81f3d304e",
                "old_values": [],
                "new_values": {
                    "name": "budianto",
                    "email": "budi@gmail.com",
                    "uuid": "019f2fce-bb70-7129-9753-afd81f3d304e"
                },
                "url": "http://127.0.0.1:8000/api/users",
                "ip_address": "127.0.0.1",
                "user_agent": "PostmanRuntime/7.51.1",
                "created_at": "2026-07-05T01:05:05+00:00",
                "user": {
                    "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
                    "name": "Administrator",
                    "email": "admin@gmail.com"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/audits/users",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 2. Role Audits
Retrieve history logs for Role and Permission modifications.

- **Endpoint:** `GET /api/audits/roles`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
Same parameters as **User Audits**. Note that `auditable_id` for role audits will be the integer Role ID (e.g. `?auditable_id=3`).

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Role Audits Data",
    "data": {
        "data": [
            {
                "id": "019f2fd1-ff12-7109-b131-cd9a1bdece11",
                "event": "created",
                "auditable_type": "App\\Models\\Role",
                "auditable_id": "3",
                "old_values": [],
                "new_values": {
                    "name": "head-warehouse",
                    "guard_name": "sanctum"
                },
                "url": "http://127.0.0.1:8000/api/roles",
                "ip_address": "127.0.0.1",
                "user_agent": "PostmanRuntime/7.51.1",
                "created_at": "2026-07-05T01:23:43+00:00",
                "user": {
                    "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
                    "name": "Administrator",
                    "email": "admin@gmail.com"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/audits/roles",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 3. Category Audits
Retrieve history logs for Category actions.

- **Endpoint:** `GET /api/audits/categories`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
Same parameters as **User Audits**.

---

## 4. Product Audits
Retrieve history logs for Product creations and modifications.

- **Endpoint:** `GET /api/audits/products`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`, `role:Administrator`

### Query Parameters
Same parameters as **User Audits**.
```
