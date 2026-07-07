# B2B Invoice Feature

## Overview
This feature adds invoice functionality to the NutriBasket delivery app, allowing delivery personnel to view and download invoices for orders.

## Features
- **View Invoice**: Display a detailed invoice for any order
- **Download Invoice**: Download invoice as PDF with proper naming (Order_[ID]_Invoice.pdf)
- **Print Support**: Ready for printing functionality
- **Professional Layout**: Clean, professional invoice design with store details, customer info, and itemized breakdown

## Implementation Details

### Files Added
1. `lib/features/order/domain/models/b2b_invoice_model.dart` - Invoice data models and API service
2. `lib/features/order/screens/b2b_invoice_screen.dart` - Invoice display screen
3. `INVOICE_FEATURE.md` - This documentation

### Files Modified
1. `lib/features/order/screens/order_details_screen.dart` - Added invoice button
2. `pubspec.yaml` - Added required dependencies

### Dependencies Added
- `path_provider: ^2.1.2` - For file system access
- `url_launcher: ^6.2.5` - For opening files

## API Integration

### Endpoints Used
- `GET /api/v1/orders/{orderId}` - Fetch order details
- `GET /api/v1/orders/{orderId}/invoice` - Download invoice PDF

### Authentication
- Uses Bearer token from AuthController
- Automatically retrieves token from shared preferences

## Invoice Layout

### Sections Included
1. **Header** - Invoice title, order ID, and date
2. **Order Information** - Order ID, status, date, payment method
3. **Store Information** - Store name, contact details, address
4. **Customer Information** - Customer name, contact details, address
5. **Order Items** - Itemized list with quantities and prices
6. **Summary** - Subtotal, discounts, taxes, delivery charge, total
7. **Payment Information** - Payment methods and status

### Features
- **Currency Formatting** - Indian Rupee (₹) formatting
- **Date Formatting** - DD/MM/YYYY format
- **Responsive Design** - Works on different screen sizes
- **Error Handling** - Graceful error handling with retry options
- **Loading States** - Proper loading indicators

## Usage

### For Delivery Personnel
1. Navigate to any order details screen
2. Look for the "Invoice" card section
3. Tap the "View" button
4. View the detailed invoice
5. Use the download button (top-right) to save as PDF

### For Developers
```dart
// Navigate to invoice screen
Get.to(() => B2BInvoiceScreen(
  orderId: orderId,
  token: authToken,
));
```

## Error Handling
- Network errors are handled gracefully
- Shows retry button for failed loads
- Permission requests for file downloads
- User-friendly error messages

## Future Enhancements
- Print functionality
- Share invoice via email/WhatsApp
- Offline invoice caching
- Multiple invoice formats
- Invoice history

## Testing
- Test with different order statuses
- Test with various payment methods
- Test download functionality
- Test error scenarios
- Test on different devices

## Security
- Token-based authentication
- Secure file downloads
- Permission-based file access
- No sensitive data exposure

## Performance
- Lazy loading of invoice data
- Efficient memory usage
- Optimized UI rendering
- Minimal network requests 