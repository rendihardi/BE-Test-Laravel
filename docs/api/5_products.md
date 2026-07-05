# Products API Contract

This module handles products management including images, product specifications PDF, dynamic stock status calculation, and sorting.

---

## 1. List All Products (Unpaginated)
Retrieve all products.

- **Endpoint:** `GET /api/products`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search by product name or product code |
| `limit` | integer | No | nullable, min:1 | Limit the number of records returned |
| `category_id` | string (uuid) | No | nullable | Filter by category ID |
| `is_active` | string | No | nullable, 'true'/'false' | Filter by active status |
| `min_price` | numeric | No | nullable | Filter by minimum price |
| `max_price` | numeric | No | nullable | Filter by maximum price |
| `stock_status`| string | No | in: "in stock", "low stock", "out of stock" | Filter by calculated stock status |
| `sort_by` | string | No | in: product_name, product_code, price, current_stock, created_at | Field to sort by |
| `sort_order` | string | No | in: asc, desc, ASC, DESC | Sorting direction |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Product",
    "data": [
        {
            "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
            "product_code": "BRG0507260001",
            "product_name": "Logitech Wireless Mouse",
            "image": "products/images/unique_name.jpg",
            "price": "250000.00",
            "current_stock": 25,
            "stock_status": "in stock",
            "attributes": {
                "color": "black",
                "dpi": 1000
            },
            "specification_pdf": "products/pdfs/unique_name.pdf",
            "is_active": true,
            "category": {
                "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
                "name": "Electronics"
            }
        }
    ]
}
```

---

## 2. List Products (Paginated)
Retrieve paginated list of products.

- **Endpoint:** `GET /api/products/paginated`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
Include all parameters from **List All Products** plus the following:
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `row_per_page` | integer | Yes | required | Number of products per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Product",
    "data": {
        "data": [
            {
                "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
                "product_code": "BRG0507260001",
                "product_name": "Logitech Wireless Mouse",
                "image": "products/images/unique_name.jpg",
                "price": "250000.00",
                "current_stock": 25,
                "stock_status": "in stock",
                "attributes": {
                    "color": "black",
                    "dpi": 1000
                },
                "specification_pdf": "products/pdfs/unique_name.pdf",
                "is_active": true,
                "category": {
                    "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
                    "name": "Electronics"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/products/paginated",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 3. Create Product
Create a new product.

- **Endpoint:** `POST /api/products`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data` (since it handles files)
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body (Form Data)
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `category_id` | string (uuid) | Yes | exists:categories,id | Category of the product |
| `product_code` | string | No | nullable, unique:products,product_code, max:50 | If blank, generated as `BRG` + date + 4 digit sequence |
| `product_name` | string | Yes | max:150 | Product name |
| `image` | file | No | nullable, image, mimes:jpeg,png,jpg,gif,webp, max:2048 (2MB) | Image file |
| `price` | numeric | Yes | min:0 | Product price |
| `current_stock`| integer | No | nullable, min:0 | Initial stock (defaults to 0) |
| `attributes` | array | No | nullable | Custom attributes JSON structure |
| `specification_pdf`| file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Product spec PDF file |
| `is_active` | boolean | No | nullable, boolean | Defaults to true |

### Response
#### Success (201 Created)
```json
{
    "success": true,
    "message": "Data Product Created",
    "data": {
        "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
        "product_code": "BRG0507260001",
        "product_name": "Logitech Wireless Mouse",
        "image": "products/images/unique_name.jpg",
        "price": "250000.00",
        "current_stock": 0,
        "stock_status": "out of stock",
        "attributes": null,
        "specification_pdf": "products/pdfs/unique_name.pdf",
        "is_active": true,
        "category": {
            "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
            "name": "Electronics"
            // ...
        }
    }
}
```

---

## 4. Get Product Detail
Retrieve a product's details.

- **Endpoint:** `GET /api/products/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Product Detail",
    "data": {
        "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
        "product_code": "BRG0507260001",
        "product_name": "Logitech Wireless Mouse",
        "image": "products/images/unique_name.jpg",
        "price": "250000.00",
        "current_stock": 25,
        "stock_status": "in stock",
        "attributes": null,
        "specification_pdf": "products/pdfs/unique_name.pdf",
        "is_active": true,
        "category": {
            "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
            "name": "Electronics"
        }
    }
}
```

#### Failure (404 Not Found)
```json
{
    "success": false,
    "message": "Product not found.",
    "data": null
}
```

---

## 5. Update Product
Modify an existing product.

> [!WARNING]
> Updating `product_code` is forbidden. Any value passed to `product_code` will be ignored or rejected.

- **Endpoint:** `POST /api/products/{id}?_method=PUT`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data` (since it handles files)
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body (Form Data)
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `category_id` | string (uuid) | No | exists:categories,id | Category of the product |
| `product_name` | string | No | max:150 | Product name |
| `image` | file | No | nullable, image, mimes:jpeg,png,jpg,gif,webp, max:2048 | Image file |
| `price` | numeric | No | min:0 | Product price |
| `attributes` | array | No | nullable | Custom attributes |
| `specification_pdf`| file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Product spec PDF |
| `is_active` | boolean | No | nullable, boolean | Status of product |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Product Updated",
    "data": {
        "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
        "product_code": "BRG0507260001",
        "product_name": "Logitech Wireless Mouse V2",
        "image": "products/images/unique_name.jpg",
        "price": "275000.00",
        "current_stock": 25,
        "stock_status": "in stock",
        "attributes": null,
        "specification_pdf": "products/pdfs/unique_name.pdf",
        "is_active": true,
        "category": {
            "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
            "name": "Electronics"
        }
    }
}
```

---

## 6. Delete Product
Soft delete a product.

- **Endpoint:** `DELETE /api/products/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Product Deleted",
    "data": null
}
```
