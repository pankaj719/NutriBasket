# User App Integration Guide for Order Modifications

## Overview
The backend now supports order modifications where items can be marked as "NA" (Not Available) and quantities can be adjusted. This guide explains how to integrate these features into your user app.

## API Response Changes

### Order Details API Response
The order details API now includes additional fields:

```json
{
  "order_modified": true/false,
  "modification_reason": "Some items were unavailable",
  "modified_at": "2025-01-29T12:00:00Z",
  "items": [
    {
      "id": 123,
      "name": "Potato",
      "quantity": "NA",  // Shows "NA" for unavailable items
      "price": 0,        // Price is 0 for NA items
      "total_price": 0,  // Total is 0 for NA items
      "is_na": true,     // Indicates this item is not available
      "modification_reason": "Out of stock"
    },
    {
      "id": 124,
      "name": "Tomato",
      "quantity": 2,     // Normal quantity for available items
      "price": 50,
      "total_price": 100,
      "is_na": false
    }
  ]
}
```

## UI Implementation Guidelines

### 1. Order Modification Alert
When `order_modified` is `true`, show a prominent alert to the user:

```dart
// Flutter Example
if (orderDetails.orderModified) {
  Container(
    padding: EdgeInsets.all(16),
    margin: EdgeInsets.all(16),
    decoration: BoxDecoration(
      color: Colors.orange.shade50,
      border: Border.all(color: Colors.orange),
      borderRadius: BorderRadius.circular(8),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(Icons.warning, color: Colors.orange),
            SizedBox(width: 8),
            Text(
              'Order Modified',
              style: TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.orange.shade800,
              ),
            ),
          ],
        ),
        SizedBox(height: 8),
        Text(
          orderDetails.modificationReason ?? 'Some items were unavailable',
          style: TextStyle(color: Colors.orange.shade700),
        ),
        if (orderDetails.modifiedAt != null)
          Text(
            'Modified on: ${formatDate(orderDetails.modifiedAt)}',
            style: TextStyle(
              fontSize: 12,
              color: Colors.orange.shade600,
            ),
          ),
      ],
    ),
  )
}
```

### 2. NA Items Display
For items marked as `is_na: true`:

```dart
// Flutter Example
Widget buildItemCard(OrderItem item) {
  return Card(
    child: ListTile(
      leading: Image.network(item.imageUrl),
      title: Text(item.name),
      subtitle: item.isNa 
        ? Text(
            'Not Available',
            style: TextStyle(
              color: Colors.red,
              fontWeight: FontWeight.bold,
            ),
          )
        : Text('Quantity: ${item.quantity}'),
      trailing: item.isNa
        ? Text(
            'NA',
            style: TextStyle(
              color: Colors.red,
              fontWeight: FontWeight.bold,
            ),
          )
        : Text('\$${item.totalPrice}'),
    ),
  );
}
```

### 3. Order Summary Updates
Update the order summary to reflect NA items:

```dart
// Flutter Example
Widget buildOrderSummary(OrderDetails order) {
  double totalAmount = 0;
  int availableItems = 0;
  int naItems = 0;
  
  for (var item in order.items) {
    if (item.isNa) {
      naItems++;
    } else {
      totalAmount += item.totalPrice;
      availableItems++;
    }
  }
  
  return Column(
    children: [
      if (naItems > 0)
        Text(
          '$naItems item(s) not available',
          style: TextStyle(color: Colors.red),
        ),
      Text(
        'Total: \$${totalAmount.toStringAsFixed(2)}',
        style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
      ),
    ],
  );
}
```

## Push Notifications
The backend automatically sends push notifications when orders are modified. Ensure your app handles these notifications:

```dart
// Flutter Example - Handle push notification
void handleOrderModificationNotification(Map<String, dynamic> data) {
  if (data['type'] == 'order_modified') {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('Order Modified'),
        content: Text(data['message'] ?? 'Your order has been modified due to item unavailability.'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('OK'),
          ),
        ],
      ),
    );
  }
}
```

## Best Practices

1. **Always check `order_modified`** before displaying order details
2. **Show clear visual indicators** for NA items (red text, warning icons)
3. **Display modification reason** prominently to inform users
4. **Update order totals** to exclude NA items
5. **Handle push notifications** gracefully
6. **Provide clear messaging** about why items are unavailable

## Testing Scenarios

1. **Normal Order**: All items available, no modifications
2. **Partial NA Order**: Some items marked as NA
3. **Fully NA Order**: All items marked as NA
4. **Quantity Modified**: Items with adjusted quantities
5. **Mixed Scenario**: Both NA items and quantity modifications

## Error Handling

- Handle cases where `modification_reason` is null
- Gracefully handle missing `modified_at` timestamps
- Provide fallback text for missing data
- Handle network errors when fetching modified orders

## Backward Compatibility

The new fields are optional, so existing apps will continue to work. The `order_modified` field defaults to `false` for orders without modifications. 