# Dashboard API Contract

This module provides data metrics and summaries for frontend visualization widgets.

---

## 1. Get Cards Summary
Retrieve statistical highlights for summary cards.

- **Endpoint:** `GET /api/dashboard/cards`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Dashboard Cards Data",
    "data": {
        "total_products": 25,
        "total_categories": 5,
        "current_stock": 350,
        "today_transactions": 8
    }
}
```

---

## 2. Get Pie Chart (Product Distribution)
Retrieve categories and their product counts.

- **Endpoint:** `GET /api/dashboard/pie-chart`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Dashboard Pie Chart Data",
    "data": [
        {
            "category_id": "019f2fce-bb70-7129-9753-afd81f3d304e",
            "category_name": "Electronics",
            "product_count": 10
        },
        {
            "category_id": "019f2fce-cf99-7124-9111-cde81a3d311b",
            "category_name": "Office Stationery",
            "product_count": 15
        }
    ]
}
```

---

## 3. Get Low Stock Warnings
Retrieve up to 5 products with stock less than 15.

- **Endpoint:** `GET /api/dashboard/low-stock`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Dashboard Low Stock Products Data",
    "data": [
        {
            "id": "019f2fdc-dd30-7195-bf89-ce23a9cf3200",
            "product_code": "BRG0507260001",
            "product_name": "Laptop Keyboard Protector",
            "current_stock": 3,
            "stock_status": "low stock",
            "price": "75000.00",
            "category": {
                "id": "019f2fce-bb70-7129-9753-afd81f3d304e",
                "name": "Electronics"
            }
        }
    ]
}
```

---

## 4. Get Stock Chart (IN vs OUT monthly)
Retrieve total monthly stock incoming (`IN`) vs outgoing (`OUT`) quantities for the last 12 months.

- **Endpoint:** `GET /api/dashboard/stock-chart`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Dashboard Stock Chart Data",
    "data": [
        {
            "month": "2025-08",
            "label": "August 2025",
            "stock_in": 120,
            "stock_out": 85
        },
        {
            "month": "2025-09",
            "label": "September 2025",
            "stock_in": 150,
            "stock_out": 110
        },
        ...
        {
            "month": "2026-07",
            "label": "July 2026",
            "stock_in": 40,
            "stock_out": 15
        }
    ]
}
```

---

## 5. Get Recent Transactions
Retrieve the last 5 stock transactions across the system.

- **Endpoint:** `GET /api/dashboard/recent-transactions`
- **Headers:** 
  - `Accept: application/json`
  - `Authorization: Bearer {token}`
- **Authentication:** `auth:sanctum`

### Response
#### Success (200 OK)
```json
{
    "success": true,
    "message": "Dashboard Recent Transactions Data",
    "data": [
        {
            "id": "019f2fdf-ee31-7128-9f12-def3b5a1cf30",
            "reference_number": "TRX-IN-20260705-0001",
            "transaction_date": "2026-07-05",
            "type": "IN",
            "quantity": 100,
            "notes": "Restock Logitech Mouse",
            "document_path": "transactions/documents/doc.pdf",
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
