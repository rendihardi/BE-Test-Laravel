# Inventory System API Documentation Index

Welcome to the API Contract documentation for the Inventory System backend. All API endpoints except the login route require authentication via Sanctum API Token.

## API Modules

Please select a module below to view its detailed API Contract (HTTP methods, endpoints, validation rules, request bodies, query params, and JSON response payloads):

1. 🔐 [Authentication API Contract](1_auth.md)
   - Login, Logout, and Current Authenticated User (Me).
2. 👥 [Roles & Permissions API Contract](2_roles.md)
   - CRUD Operations for Roles and Permission syncs.
3. 👤 [Users API Contract](3_users.md)
   - Admin-only CRUD operations for user accounts and role assignments.
4. 📁 [Categories API Contract](4_categories.md)
   - CRUD Operations for Product Categories.
5. 📦 [Products API Contract](5_products.md)
   - CRUD Operations for Products, Upload specifications PDF, Images, stock calculation and advanced filtering.
6. 🔄 [Stock Transactions API Contract](6_stock_transactions.md)
   - Track stock movements, Restock (Stock In), Dispatch (Stock Out) with race-condition protection (pessimistic lock), and Adjust stock counts.
7. 📊 [Dashboard API Contract](7_dashboard.md)
   - Summary statistics cards, pie chart distribution data, and low-stock warnings table.
8. 🕵️ [Audit Trails API Contract](8_audits.md)
   - Admin-only audit logs to track changes in Users, Roles, Categories, and Products.
9. 📊 [Excel Export & Import API Contract](9_excel.md)
   - Background queued export/import of products and transaction histories with dynamic field selection.

---

## Global API Behavior

### Common Headers
To interact with authenticated endpoints, include the following headers:
```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer {your_sanctum_token_here}
```

### Response Formats
The API returns a uniform JSON envelope for all responses:

#### Success Envelope
```json
{
    "success": true,
    "message": "Human readable message describing the success action",
    "data": { ... } // or array [ ... ]
}
```

#### Error Envelope
```json
{
    "success": false,
    "message": "Human readable error description",
    "data": null
}
```
