# Template API Documentation

## Overview
The Template API provides functionality for users to create, manage, and use templates for their favorite items. Templates allow users to save collections of items with quantities and notes for quick reordering.

## Base URL
```
/api/v1/customer/templates
```

## Authentication
All endpoints require authentication using the `auth:api` middleware. Include the Bearer token in the Authorization header.

## Endpoints

### 1. Get Template List
**GET** `/api/v1/customer/templates`

Retrieves all templates for the authenticated user.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "templates": [
        {
            "id": 1,
            "user_id": 123,
            "name": "Weekly Groceries",
            "description": "My weekly shopping list",
            "is_active": true,
            "created_at": "2025-07-08T10:00:00.000000Z",
            "updated_at": "2025-07-08T10:00:00.000000Z",
            "items": [
                {
                    "id": 1,
                    "template_id": 1,
                    "item_id": 456,
                    "quantity": 2,
                    "notes": "Organic preferred",
                    "created_at": "2025-07-08T10:00:00.000000Z",
                    "updated_at": "2025-07-08T10:00:00.000000Z",
                    "item": {
                        "id": 456,
                        "name": "Organic Bananas",
                        "price": 2.99,
                        "image": "bananas.jpg"
                    }
                }
            ]
        }
    ],
    "message": "Templates retrieved successfully"
}
```

### 2. Create Template
**POST** `/api/v1/customer/templates`

Creates a new template with items.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Weekly Groceries",
    "description": "My weekly shopping list",
    "items": [
        {
            "item_id": 456,
            "quantity": 2,
            "notes": "Organic preferred"
        },
        {
            "item_id": 789,
            "quantity": 1,
            "notes": "Large size"
        }
    ]
}
```

**Validation Rules:**
- `name`: required, string, max 255 characters
- `description`: optional, string
- `items`: required, array, minimum 1 item
- `items.*.item_id`: required, integer, must exist in items table
- `items.*.quantity`: required, integer, minimum 1
- `items.*.notes`: optional, string

**Response:**
```json
{
    "template": {
        "id": 1,
        "user_id": 123,
        "name": "Weekly Groceries",
        "description": "My weekly shopping list",
        "is_active": true,
        "created_at": "2025-07-08T10:00:00.000000Z",
        "updated_at": "2025-07-08T10:00:00.000000Z",
        "items": [...]
    },
    "message": "Template created successfully"
}
```

### 3. Update Template
**PUT** `/api/v1/customer/templates/{id}`

Updates an existing template.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "name": "Updated Weekly Groceries",
    "description": "Updated description",
    "is_active": true,
    "items": [
        {
            "item_id": 456,
            "quantity": 3,
            "notes": "Updated notes"
        }
    ]
}
```

**Validation Rules:**
- `name`: optional, string, max 255 characters
- `description`: optional, string
- `is_active`: optional, boolean
- `items`: optional, array, minimum 1 item (if provided)
- `items.*.item_id`: required, integer, must exist in items table
- `items.*.quantity`: required, integer, minimum 1
- `items.*.notes`: optional, string

**Response:**
```json
{
    "template": {
        "id": 1,
        "user_id": 123,
        "name": "Updated Weekly Groceries",
        "description": "Updated description",
        "is_active": true,
        "created_at": "2025-07-08T10:00:00.000000Z",
        "updated_at": "2025-07-08T10:05:00.000000Z",
        "items": [...]
    },
    "message": "Template updated successfully"
}
```

### 4. Delete Template
**DELETE** `/api/v1/customer/templates/{id}`

Deletes a template and all its associated items.

**Headers:**
```
Authorization: Bearer {token}
```

**Response:**
```json
{
    "message": "Template deleted successfully"
}
```

### 5. Convert Order to Template
**POST** `/api/v1/customer/templates/convert-from-order`

Converts a past order into a reusable template.

**Headers:**
```
Authorization: Bearer {token}
Content-Type: application/json
```

**Request Body:**
```json
{
    "order_id": 123,
    "name": "My Favorite Order",
    "description": "Converted from order #123"
}
```

**Validation Rules:**
- `order_id`: required, integer, must exist in orders table
- `name`: required, string, max 255 characters
- `description`: optional, string

**Response:**
```json
{
    "template": {
        "id": 2,
        "user_id": 123,
        "name": "My Favorite Order",
        "description": "Converted from order #123",
        "is_active": true,
        "created_at": "2025-07-08T10:00:00.000000Z",
        "updated_at": "2025-07-08T10:00:00.000000Z",
        "items": [...]
    },
    "message": "Template created from order successfully"
}
```

## Error Responses

### Validation Error (403)
```json
{
    "errors": [
        {
            "code": "name",
            "message": "The name field is required."
        }
    ]
}
```

### Not Found Error (404)
```json
{
    "message": "Template not found"
}
```

### Server Error (500)
```json
{
    "message": "Failed to create template",
    "error": "Database connection error"
}
```

## Database Schema

### Templates Table
```sql
CREATE TABLE templates (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_active (user_id, is_active)
);
```

### Template Items Table
```sql
CREATE TABLE template_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    template_id BIGINT UNSIGNED NOT NULL,
    item_id BIGINT UNSIGNED NOT NULL,
    quantity INT DEFAULT 1,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (template_id) REFERENCES templates(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    UNIQUE KEY unique_template_item (template_id, item_id)
);
```

## Usage Examples

### Creating a Template from Favorites
1. User adds items to favorites/wishlist
2. User selects items from favorites to create a template
3. User provides template name and description
4. System creates template with selected items

### Using Templates for Quick Ordering
1. User selects a template
2. System loads all items from template into cart
3. User can modify quantities before placing order
4. User places order with template items

### Converting Past Orders
1. User views order history
2. User selects "Convert to Template" for a specific order
3. User provides template name
4. System creates template with all items from that order

## Security Considerations
- All endpoints require authentication
- Users can only access their own templates
- Template items are validated against existing items
- Foreign key constraints ensure data integrity
- Soft deletes could be implemented for audit trails 