# Excel Export & Import API Contract

This module provides background (queued) export and import of data using Excel files with support for dynamic column selection.

---

## 1. Trigger Queued Export
Submit a request to generate an Excel sheet in the background.

### 1a. Export Products
- **Endpoint:** `POST /api/excel/export/products`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

#### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `columns` | array | No | array | List of column names to export: `id`, `product_code`, `product_name`, `price`, `current_stock`, `stock_status`, `category_name`, `is_active`, `created_at` |
| `search` | string | No | nullable | Text search filter |
| `category_id` | string (uuid) | No | exists:categories,id | Filter by category ID |
| `stock_status`| string | No | in: in stock, low stock, out of stock | Filter by stock status |
| `is_active` | string | No | in: true, false | Filter active products |
| `min_price` | numeric | No | min:0 | Minimum price |
| `max_price` | numeric | No | min:0 | Maximum price |
| `sort_by` | string | No | in: product_name, product_code, price, current_stock, created_at | Sorting column |
| `sort_order` | string | No | in: asc, desc, ASC, DESC | Sorting direction |

#### Example Request Body
```json
{
    "columns": ["product_code", "product_name", "price", "current_stock", "stock_status"],
    "category_id": "019f2fce-bb70-7129-9753-afd81f3d304e",
    "stock_status": "low stock",
    "sort_by": "price",
    "sort_order": "desc"
}
```

#### Success Response (202 Accepted)
```json
{
    "success": true,
    "message": "Export task queued successfully",
    "data": {
        "job_id": "019f2feb-bb70-7129-9753-afd81f3d304e",
        "status": "pending",
        "module": "product",
        "type": "export"
    }
}
```

---

### 1b. Export Stock Transactions (Stock In / Stock Out / Adjustments / All)
- **Endpoints:**
  - `POST /api/excel/export/stock-in` (Stock In only)
  - `POST /api/excel/export/stock-out` (Stock Out only)
  - `POST /api/excel/export/adjustments` (Adjustments only)
  - `POST /api/excel/export/stock-transactions` (All Transactions)
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

#### Request Body
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `columns` | array | No | array | Columns to export: `id`, `trx_code`, `transaction_date`, `type`, `qty`, `stock_before`, `stock_after`, `product_name`, `category_name`, `price`, `remarks`, `created_by`, `created_at` |
| `search` | string | No | nullable | Search by code or notes |
| `product_id` | string (uuid) | No | exists:products,id | Filter by specific product |
| `created_by` | string (uuid) | No | exists:users,uuid | Filter by user who created |
| `start_date` | date | No | nullable | Filter start date |
| `end_date` | date | No | nullable | Filter end date |
| `sort_by` | string | No | nullable | Sorting column |
| `sort_order` | string | No | in: asc, desc, ASC, DESC | Sorting direction |

#### Example Request Body
```json
{
    "columns": ["trx_code", "transaction_date", "type", "product_name", "qty", "stock_before", "stock_after"],
    "start_date": "2026-07-01",
    "end_date": "2026-07-31"
}
```

#### Success Response (202 Accepted)
```json
{
    "success": true,
    "message": "Export task queued successfully",
    "data": {
        "job_id": "019f2feb-ee31-7128-9f12-def3b5a1cf30",
        "status": "pending",
        "module": "stockin", // or stockout, adjustment depending on url
        "type": "export"
    }
}
```

---

## 2. Product Import & Templates

---

### 2a. Trigger Queued Product Import
Upload an Excel file to import or update products in the background.

- **Endpoint:** `POST /api/excel/import`
- **Headers:** 
  - `Accept: application/json`
  - `Content-Type: multipart/form-data`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

---

### 2b. Download Product Import Template
Download the pre-configured Excel or CSV template for product imports.

- **Endpoint:** `GET /api/excel/templates/product`
- **Headers:** 
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

#### Query Parameters
| Parameter | Type | Required | Description | Default |
|---|---|---|---|---|
| `format` | string | No | File format option: `xlsx` or `csv` | `xlsx` |

#### Response
Returns the template file as a binary stream download.

---

### Request Body (Form Data)
| Field | Type | Required | Rules | Description |
|---|---|---|---|---|
| `file` | file | Yes | mimes:xlsx,xls,csv, max:10240 (10MB) | The Excel sheet containing products |

#### Supported Column Headers in Excel (Dynamic Mapping)
The importer reads the header row and maps columns dynamically. You can use standard headers:
* `Product Name` or `name` (Required)
* `Product Code` or `code` (Optional; if existing, product is updated; otherwise generated)
* `Price` (Optional, defaults to 0)
* `Current Stock` or `stock` (Optional, defaults to 0)
* `Category Name` or `category` (Optional; if not found, created automatically)
* `Is Active` or `active` (Optional, `true`/`false`/`1`/`0`)
* `Attributes` or `specs` (Optional, JSON or plain string)

#### Download Template / Example Files
You can download the prepared import templates directly:
- **Excel Template (.xlsx):** [/templates/product_import_template.xlsx](/templates/product_import_template.xlsx)
- **CSV Template (.csv):** [/templates/product_import_template.csv](/templates/product_import_template.csv)

#### Example Data Structure
| Product Name | Price | Current Stock | Category Name | Is Active | Attributes |
|---|---|---|---|---|---|
| Smartphone Wireless Pro | 12000000 | 50 | Electronics | true | `{"brand":"TechBrand","warranty":"2 years"}` |
| Ergonomic Office Chair | 1500000 | 15 | Furniture | true | `{"material":"Mesh","color":"Slate Gray"}` |
| Organic Green Tea | 45000 | 120 | Food & Beverage | true | `{"weight":"250g","organic":true}` |
| Mechanical Keyboard RGB | 850000 | 0 | Electronics | false | `{"switches":"Red Linear"}` |

### Response
#### Success (202 Accepted)
```json
{
    "success": true,
    "message": "Import task queued successfully",
    "data": {
        "job_id": "019f2feb-cf99-7124-9111-cde81a3d311b",
        "status": "pending",
        "module": "product",
        "type": "import"
    }
}
```

---

## 3. List Excel Jobs
Get all export and import background tasks triggered by the current user.

- **Endpoint:** `GET /api/excel/jobs`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Excel Jobs List",
    "data": {
        "data": [
            {
                "id": "019f2feb-bb70-7129-9753-afd81f3d304e",
                "type": "export",
                "module": "product",
                "status": "completed",
                "download_url": "http://127.0.0.1:8000/storage/exports/product_export_abc123xyz_1688530000.xlsx",
                "error_message": null,
                "created_at": "2026-07-05T03:45:00+00:00",
                "updated_at": "2026-07-05T03:45:05+00:00"
            }
        ],
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 1,
            "path": "http://127.0.0.1:8000/api/excel/jobs",
            "per_page": 10,
            "to": 1,
            "total": 1
        }
    }
}
```

---

## 4. Check Job Status
Check processing status and retrieve download link / error details for a specific task.

- **Endpoint:** `GET /api/excel/jobs/{id}`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
##### Processing Status
```json
{
    "success": true,
    "message": "Excel Job Detail",
    "data": {
        "id": "019f2feb-bb70-7129-9753-afd81f3d304e",
        "type": "export",
        "module": "product",
        "status": "processing",
        "download_url": null,
        "error_message": null,
        "created_at": "2026-07-05T03:45:00+00:00",
        "updated_at": "2026-07-05T03:45:02+00:00"
    }
}
```

##### Completed Status (with Download URL)
```json
{
    "success": true,
    "message": "Excel Job Detail",
    "data": {
        "id": "019f2feb-bb70-7129-9753-afd81f3d304e",
        "type": "export",
        "module": "product",
        "status": "completed",
        "download_url": "http://127.0.0.1:8000/storage/exports/product_export_abc123xyz_1688530000.xlsx",
        "error_message": null,
        "created_at": "2026-07-05T03:45:00+00:00",
        "updated_at": "2026-07-05T03:45:05+00:00"
    }
}
```

##### Failed Status (with Error Message)
```json
{
    "success": true,
    "message": "Excel Job Detail",
    "data": {
        "id": "019f2feb-bb70-7129-9753-afd81f3d304e",
        "type": "export",
        "module": "product",
        "status": "failed",
        "download_url": null,
        "error_message": "SQLSTATE[42S22]: Column not found: 1054 Unknown column 'invalid_col' in 'field list'",
        "created_at": "2026-07-05T03:45:00+00:00",
        "updated_at": "2026-07-05T03:45:01+00:00"
    }
}
```
