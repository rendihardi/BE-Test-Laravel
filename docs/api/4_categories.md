# Categories API Contract

This module handles product categories.

---

## 1. List All Categories (Unpaginated)
Retrieve all active and inactive categories in the system.

- **Endpoint:** `GET /api/categories`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search categories by name |
| `limit` | integer | No | nullable, min:1 | Limit the number of records returned |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Category",
    "data": [
        {
            "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
            "name": "Electronics",
            "description": "Electronic appliances and components",
            "is_active": true
        }
    ]
}
```

---

## 2. List Categories (Paginated)
Retrieve paginated list of categories.

- **Endpoint:** `GET /api/categories/paginated`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search categories by name |
| `row_per_page` | integer | Yes | required | Number of records per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Category",
    "data": {
        "data": [
            {
                "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
                "name": "Electronics",
                "description": "Electronic appliances and components",
                "is_active": true
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/categories/paginated",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 3. Create Category
Create a new category.

- **Endpoint:** `POST /api/categories`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | Yes | max:100, unique:categories,name | Name of the category |
| `description` | string | No | nullable | Detailed description |
| `is_active` | boolean | No | nullable, boolean | Status of the category (defaults to `true`) |

#### Example Request Body
```json
{
    "name": "Stationery",
    "description": "Office paper and pens",
    "is_active": true
}
```

### Response
#### Success (201 Created)
```json
{
    "success": true,
    "message": "Data Category Created",
    "data": {
        "id": "019f2fce-cf99-7124-9111-cde81a3d311b",
        "name": "Stationery",
        "description": "Office paper and pens",
        "is_active": true
    }
}
```

---

## 4. Get Category Detail
Retrieve details of a category by UUID.

- **Endpoint:** `GET /api/categories/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Category Detail",
    "data": {
        "id": "019f2fce-cf99-7124-9111-cde81a3d311b",
        "name": "Stationery",
        "description": "Office paper and pens",
        "is_active": true
    }
}
```

#### Failure (404 Not Found)
```json
{
    "success": false,
    "message": "Category not found.",
    "data": null
}
```

---

## 5. Update Category
Modify an existing category.

- **Endpoint:** `PUT /api/categories/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `name` | string | No | max:100, unique:categories,name,{id},id | Updated name |
| `description` | string | No | nullable | Updated description |
| `is_active` | boolean | No | nullable, boolean | Updated status |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Category Updated",
    "data": {
        "id": "019f2fce-cf99-7124-9111-cde81a3d311b",
        "name": "Office Stationery",
        "description": "Office paper and pens",
        "is_active": false
    }
}
```

---

## 6. Delete Category
Soft delete a category.

- **Endpoint:** `DELETE /api/categories/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Category Deleted",
    "data": null
}
```
