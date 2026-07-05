# Authentication API Contract

This module handles authentication operations including login, logout, and retrieving the currently authenticated user session.

---

## 1. Login
Authenticate a user and generate a Sanctum API token.

- **Endpoint:** `POST /api/login`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
- **Authentication:** None

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `email` | string | Yes | email, exists in `users` | The registered email address |
| `password` | string | Yes | min:8 | The user's account password |

#### Example Request Body
```json
{
    "email": "admin@gmail.com",
    "password": "konfirmasi"
}
```

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "token": "1|laravel_sanctum_token_value_here",
        "user": {
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
    }
}
```

#### Failure - Validation Error (422 Unprocessable Content)
```json
{
    "success": false,
    "message": "The email field is required. (and/or other validation messages)",
    "data": null
}
```

#### Failure - Unauthorized (401 Unauthorized)
```json
{
    "success": false,
    "message": "Invalid credentials",
    "data": null
}
```

---

## 2. Logout
Revoke the authenticated user's current token.

- **Endpoint:** `POST /api/logout`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Tokens revoked",
    "data": null
}
```

---

## 3. Get Authenticated User (Me)
Retrieve details of the currently authenticated user.

- **Endpoint:** `GET /api/me`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Current authenticated user detail",
    "data": {
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
}
```
