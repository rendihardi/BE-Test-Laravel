# Stock Transactions API Contract

This module manages stock adjustments and histories (Stock In, Stock Out, Adjust Stock).

> [!NOTE]
> All transactions are secured against race conditions via pessimistic locking (`lockForUpdate`).

---

## 1. List All Stock Transactions
Retrieve list of transactions.

- **Endpoint:** `GET /api/stock-transactions`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `search` | string | No | nullable | Search by reference number or notes |
| `limit` | integer | No | nullable, min:1 | Limit the number of records returned |
| `product_id` | string (uuid) | No | nullable | Filter by product ID |
| `type` | string | No | in: IN, OUT, ADJUSTMENT | Filter by transaction type |
| `created_by` | string (uuid) | No | nullable | Filter by user who created |
| `start_date` | date | No | nullable | Filter start date |
| `end_date` | date | No | nullable | Filter end date |
| `sort_by` | string | No | in: transaction_date, reference_number, type, quantity | Field to sort |
| `sort_order` | string | No | in: asc, desc, ASC, DESC | Sorting direction |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Stock Transaction",
    "data": [
        {
            "id": "019f2fdf-ee31-7128-9f12-def3b5a1cf30",
            "reference_number": "TRX-IN-20260705-0001",
            "transaction_date": "2026-07-05",
            "type": "IN",
            "quantity": 100,
            "notes": "Restock from supplier",
            "document_path": "transactions/documents/unique_name.pdf",
            "created_at": "2026-07-05T01:30:00.000000Z",
            "product": {
                "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
                "product_name": "Logitech Wireless Mouse"
            },
            "creator": {
                "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
                "name": "Administrator"
            }
        }
    ]
}
```

---

## 2. List Stock Transactions (Paginated)
Retrieve paginated list of stock transactions.

- **Endpoint:** `GET /api/stock-transactions/paginated`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Query Parameters
Include all parameters from **List All Stock Transactions** plus:
| Parameter | Type | Required | Rules | Description |
|---|---|---|---|---|
| `row_per_page` | integer | Yes | required | Number of records per page |

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Data Stock Transaction",
    "data": {
        "data": [
            {
                "id": "019f2fdf-ee31-7128-9f12-def3b5a1cf30",
                "reference_number": "TRX-IN-20260705-0001",
                "transaction_date": "2026-07-05",
                "type": "IN",
                "quantity": 100,
                "notes": "Restock from supplier",
                "document_path": "transactions/documents/unique_name.pdf",
                "created_at": "2026-07-05T01:30:00.000000Z",
                "product": {
                    "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
                    "product_name": "Logitech Wireless Mouse"
                },
                "creator": {
                    "uuid": "019f2fcd-af8d-71d7-89cf-9d21ae545122",
                    "name": "Administrator"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/stock-transactions/paginated",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 3. Create General Stock Transaction
Create a direct transaction (normally done through dedicated endpoints below, but supported for general purposes).

- **Endpoint:** `POST /api/stock-transactions`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `product_id` | string (uuid) | Yes | exists:products,id | Product ID |
| `type` | string | Yes | in: IN, OUT, ADJUSTMENT | Transaction type |
| `quantity` | integer | Yes | min:1 | Quantity |
| `notes` | string | No | nullable | Optional notes |
| `document` | file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Reference document |

---

## 4. Stock In
Add stock to a specific product.

- **Endpoint:** `POST /api/products/{id}/stock-in`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `quantity` | integer | Yes | min:1 | Quantity to add |
| `notes` | string | No | nullable | Optional details |
| `document` | file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Reference document |

#### Response
```json
{
    "success": true,
    "message": "Stock In Successful",
    "data": {
        "id": "019f2fdf-ee31-7128-9f12-def3b5a1cf30",
        "reference_number": "TRX-IN-20260705-0001",
        "transaction_date": "2026-07-05",
        "type": "IN",
        "quantity": 100,
        "notes": "Restock from supplier",
        "document_path": "transactions/documents/unique_name.pdf"
    }
}
```

---

## 5. Stock Out
Deduct stock from a specific product.

> [!WARNING]
> If quantity to deduct exceeds the product's `current_stock`, a 400 Bad Request error is returned.

- **Endpoint:** `POST /api/products/{id}/stock-out`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `quantity` | integer | Yes | min:1 | Quantity to deduct |
| `notes` | string | No | nullable | Optional details |
| `document` | file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Reference document |

#### Response
##### Success (200 OK)
```json
{
    "success": true,
    "message": "Stock Out Successful",
    "data": {
        "id": "019f2fe0-ff31-7128-9f12-def3b5a1cf35",
        "reference_number": "TRX-OUT-20260705-0002",
        "transaction_date": "2026-07-05",
        "type": "OUT",
        "quantity": 10,
        "notes": "Dispatch to main store",
        "document_path": "transactions/documents/unique_name.pdf"
    }
}
```

##### Failure - Insufficient Stock (400 Bad Request)
```json
{
    "success": false,
    "message": "Insufficient stock. Current stock is 5, but trying to deduct 10.",
    "data": null
}
```

---

## 6. Adjust Stock
Directly override or adjust a product's stock count.

- **Endpoint:** `POST /api/products/{id}/adjust-stock`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `quantity` | integer | Yes | min:0 | The new stock count |
| `notes` | string | No | nullable | Optional details |
| `document` | file | No | nullable, file, mimes:pdf, min:100, max:500 (100KB - 500KB) | Reference document |

#### Response
##### Success (200 OK)
```json
{
    "success": true,
    "message": "Stock Adjustment Successful",
    "data": {
        "id": "019f2fe1-aa31-7128-9f12-def3b5a1cf40",
        "reference_number": "TRX-ADJ-20260705-0003",
        "transaction_date": "2026-07-05",
        "type": "ADJUSTMENT",
        "quantity": 15,
        "notes": "Physical audit correction",
        "document_path": "transactions/documents/unique_name.pdf"
    }
}
```

---

## 7. Common Failure Scenarios
The following responses are returned across all Transaction endpoints under failed conditions:

### Validation Error (422 Unprocessable Content)
Occurs when the payload fails validation rules (e.g. quantity is empty, or the reference document is too small/large or not a PDF).
```json
{
    "success": false,
    "message": "The document field must be a file of type: pdf. (and/or) The document field must be between 100 and 500 kilobytes.",
    "data": null
}
```

### Product Not Found (404 Not Found)
Occurs when adjusting stock for a non-existent product UUID.
```json
{
    "success": false,
    "message": "Product not found.",
    "data": null
}
```

