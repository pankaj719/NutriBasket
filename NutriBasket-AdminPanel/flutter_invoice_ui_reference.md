# Flutter B2B Invoice UI Reference

This reference provides a complete Flutter implementation that mirrors the Laravel B2B invoice structure.

## 1. Data Models

### B2BOrder Model
```dart
class B2BOrder {
  final int id;
  final String orderStatus;
  final DateTime createdAt;
  final double orderAmount;
  final double deliveryCharge;
  final double totalTaxAmount;
  final double storeDiscountAmount;
  final double couponDiscountAmount;
  final double refBonusAmount;
  final double dmTips;
  final double additionalCharge;
  final double extraPackagingAmount;
  final String paymentMethod;
  final double adjusment;
  final String taxStatus;
  final B2BStore? store;
  final B2BCustomer? customer;
  final List<B2BOrderDetail> details;
  final List<B2BPayment>? payments;
  final String? deliveryAddress;

  B2BOrder({
    required this.id,
    required this.orderStatus,
    required this.createdAt,
    required this.orderAmount,
    required this.deliveryCharge,
    required this.totalTaxAmount,
    required this.storeDiscountAmount,
    required this.couponDiscountAmount,
    required this.refBonusAmount,
    required this.dmTips,
    required this.additionalCharge,
    required this.extraPackagingAmount,
    required this.paymentMethod,
    required this.adjusment,
    required this.taxStatus,
    this.store,
    this.customer,
    required this.details,
    this.payments,
    this.deliveryAddress,
  });

  factory B2BOrder.fromJson(Map<String, dynamic> json) {
    return B2BOrder(
      id: json['id'],
      orderStatus: json['order_status'],
      createdAt: DateTime.parse(json['created_at']),
      orderAmount: double.parse(json['order_amount'].toString()),
      deliveryCharge: double.parse(json['delivery_charge'].toString()),
      totalTaxAmount: double.parse(json['total_tax_amount'].toString()),
      storeDiscountAmount: double.parse(json['store_discount_amount'].toString()),
      couponDiscountAmount: double.parse(json['coupon_discount_amount'].toString()),
      refBonusAmount: double.parse(json['ref_bonus_amount'].toString()),
      dmTips: double.parse(json['dm_tips'].toString()),
      additionalCharge: double.parse(json['additional_charge'].toString()),
      extraPackagingAmount: double.parse(json['extra_packaging_amount'].toString()),
      paymentMethod: json['payment_method'],
      adjusment: double.parse(json['adjusment'].toString()),
      taxStatus: json['tax_status'] ?? 'excluded',
      store: json['store'] != null ? B2BStore.fromJson(json['store']) : null,
      customer: json['customer'] != null ? B2BCustomer.fromJson(json['customer']) : null,
      details: (json['details'] as List)
          .map((detail) => B2BOrderDetail.fromJson(detail))
          .toList(),
      payments: json['payments'] != null
          ? (json['payments'] as List)
              .map((payment) => B2BPayment.fromJson(payment))
              .toList()
          : null,
      deliveryAddress: json['delivery_address'],
    );
  }
}
```

### Supporting Models
```dart
class B2BStore {
  final String name;
  final String address;
  final String phone;
  final String? gstCode;
  final bool gstStatus;

  B2BStore({
    required this.name,
    required this.address,
    required this.phone,
    this.gstCode,
    required this.gstStatus,
  });

  factory B2BStore.fromJson(Map<String, dynamic> json) {
    return B2BStore(
      name: json['name'],
      address: json['address'],
      phone: json['phone'],
      gstCode: json['gst_code'],
      gstStatus: json['gst_status'] ?? false,
    );
  }
}

class B2BCustomer {
  final String fName;
  final String lName;
  final String phone;

  B2BCustomer({
    required this.fName,
    required this.lName,
    required this.phone,
  });

  factory B2BCustomer.fromJson(Map<String, dynamic> json) {
    return B2BCustomer(
      fName: json['f_name'],
      lName: json['l_name'],
      phone: json['phone'],
    );
  }

  String get fullName => '$fName $lName';
}

class B2BOrderDetail {
  final int quantity;
  final double price;
  final double taxAmount;
  final Map<String, dynamic> itemDetails;
  final List<dynamic> variation;
  final List<dynamic> addOns;

  B2BOrderDetail({
    required this.quantity,
    required this.price,
    required this.taxAmount,
    required this.itemDetails,
    required this.variation,
    required this.addOns,
  });

  factory B2BOrderDetail.fromJson(Map<String, dynamic> json) {
    return B2BOrderDetail(
      quantity: json['quantity'],
      price: double.parse(json['price'].toString()),
      taxAmount: double.parse(json['tax_amount'].toString()),
      itemDetails: json['item_details'] is String 
          ? Map<String, dynamic>.from(jsonDecode(json['item_details']))
          : Map<String, dynamic>.from(json['item_details']),
      variation: json['variation'] is String 
          ? jsonDecode(json['variation'])
          : json['variation'],
      addOns: json['add_ons'] is String 
          ? jsonDecode(json['add_ons'])
          : json['add_ons'],
    );
  }

  double get totalAmount => price * quantity;
}

class B2BPayment {
  final String paymentMethod;
  final String paymentStatus;
  final double amount;

  B2BPayment({
    required this.paymentMethod,
    required this.paymentStatus,
    required this.amount,
  });

  factory B2BPayment.fromJson(Map<String, dynamic> json) {
    return B2BPayment(
      paymentMethod: json['payment_method'],
      paymentStatus: json['payment_status'],
      amount: double.parse(json['amount'].toString()),
    );
  }
}
```

## 2. Utility Functions

```dart
import 'package:intl/intl.dart';
import 'dart:convert';

class InvoiceUtils {
  static String formatCurrency(double amount) {
    return NumberFormat.currency(
      symbol: '₹',
      decimalDigits: 2,
    ).format(amount);
  }

  static String formatDate(DateTime date) {
    return DateFormat('dd/MMM/yyyy HH:mm').format(date);
  }

  static Map<String, dynamic>? parseDeliveryAddress(String? addressJson) {
    if (addressJson == null) return null;
    try {
      return jsonDecode(addressJson);
    } catch (e) {
      return null;
    }
  }
}
```

## 3. Main Invoice Screen Structure

```dart
class B2BInvoiceScreen extends StatelessWidget {
  final B2BOrder order;

  const B2BInvoiceScreen({Key? key, required this.order}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Invoice'),
        actions: [
          IconButton(
            icon: const Icon(Icons.print),
            onPressed: () => _printInvoice(context),
          ),
        ],
      ),
      body: SingleChildScrollView(
        child: Container(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              _buildPrintButton(),
              const SizedBox(height: 16),
              _buildInvoiceContent(),
            ],
          ),
        ),
      ),
    );
  }
}
```

## 4. Invoice Sections

### Store Header
```dart
Widget _buildStoreHeader() {
  if (order.store == null) return const SizedBox.shrink();

  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        // Store Logo
        Image.asset(
          'assets/images/invoice-logo.png',
          height: 60,
          errorBuilder: (context, error, stackTrace) => Container(
            height: 60,
            width: 60,
            decoration: BoxDecoration(
              color: Colors.grey[300],
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.store, size: 30),
          ),
        ),
        const SizedBox(height: 8),
        // Store Name
        Text(
          order.store!.name,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
          ),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 4),
        // Store Address
        Text(
          order.store!.address,
          style: const TextStyle(fontSize: 14),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 4),
        // Store Phone
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Phone: '),
            Text(order.store!.phone),
          ],
        ),
      ],
    ),
  );
}
```

### Receipt Header
```dart
Widget _buildReceiptHeader() {
  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        Row(
          children: [
            Expanded(child: Image.asset('assets/images/invoice-star.png')),
            const SizedBox(width: 8),
            const Text(
              'CASH RECEIPT',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(child: Image.asset('assets/images/invoice-star.png')),
          ],
        ),
      ],
    ),
  );
}
```

### Order Information
```dart
Widget _buildOrderInfo() {
  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        // Order ID
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Text('Order ID: '),
            Text(
              order.id.toString(),
              style: const TextStyle(fontWeight: FontWeight.bold),
            ),
          ],
        ),
        const SizedBox(height: 4),
        // Order Date
        Text(InvoiceUtils.formatDate(order.createdAt)),
        const SizedBox(height: 4),
        // GST Number (if available)
        if (order.store?.gstStatus == true && order.store?.gstCode != null)
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              const Text('GST No: '),
              Text(order.store!.gstCode!),
            ],
          ),
      ],
    ),
  );
}
```

### Customer Information
```dart
Widget _buildCustomerInfo() {
  final deliveryAddress = InvoiceUtils.parseDeliveryAddress(order.deliveryAddress);
  
  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Contact Information',
          style: TextStyle(
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
        const SizedBox(height: 8),
        if (deliveryAddress != null) ...[
          Row(
            children: [
              const Text('Contact Name: '),
              Text(deliveryAddress['contact_person_name'] ?? ''),
            ],
          ),
          Row(
            children: [
              const Text('Phone: '),
              Text(deliveryAddress['contact_person_number'] ?? ''),
            ],
          ),
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text('Address: '),
              Expanded(
                child: Text(deliveryAddress['address'] ?? ''),
              ),
            ],
          ),
        ] else if (order.customer != null) ...[
          Row(
            children: [
              const Text('Contact Name: '),
              Text(order.customer!.fullName),
            ],
          ),
          Row(
            children: [
              const Text('Phone: '),
              Text(order.customer!.phone),
            ],
          ),
        ],
      ],
    ),
  );
}
```

### Order Items Table
```dart
Widget _buildOrderItems() {
  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        // Table Header
        Container(
          padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
          decoration: BoxDecoration(
            border: Border.all(color: Colors.black),
          ),
          child: const Row(
            children: [
              Expanded(flex: 3, child: Text('Description')),
              Expanded(flex: 1, child: Text('Qty', textAlign: TextAlign.center)),
              Expanded(flex: 2, child: Text('Price', textAlign: TextAlign.right)),
            ],
          ),
        ),
        // Order Items
        ...order.details.map((detail) => _buildOrderItem(detail)),
      ],
    ),
  );
}

Widget _buildOrderItem(B2BOrderDetail detail) {
  final itemName = detail.itemDetails['name'] ?? 'Unknown Item';
  
  return Container(
    padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
    decoration: BoxDecoration(
      border: Border(
        bottom: BorderSide(color: Colors.grey[300]!),
      ),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Expanded(
              flex: 3,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    itemName,
                    style: const TextStyle(fontWeight: FontWeight.bold),
                  ),
                  if (detail.variation.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    _buildVariations(detail.variation),
                  ],
                  if (detail.addOns.isNotEmpty) ...[
                    const SizedBox(height: 4),
                    _buildAddOns(detail.addOns),
                  ],
                ],
              ),
            ),
            Expanded(
              flex: 1,
              child: Text(
                detail.quantity.toString(),
                textAlign: TextAlign.center,
              ),
            ),
            Expanded(
              flex: 2,
              child: Text(
                InvoiceUtils.formatCurrency(detail.totalAmount),
                textAlign: TextAlign.right,
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      ],
    ),
  );
}
```

### Variations and Add-ons
```dart
Widget _buildVariations(List<dynamic> variations) {
  return Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      const Text(
        'Variations:',
        style: TextStyle(fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
      ),
      ...variations.map((variation) {
        if (variation is Map<String, dynamic>) {
          if (variation.containsKey('name') && variation.containsKey('values')) {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('${variation['name']}:'),
                ...(variation['values'] as List).map((value) => Padding(
                  padding: const EdgeInsets.only(left: 16),
                  child: Text('${value['label']}: ${InvoiceUtils.formatCurrency(value['optionPrice'])}'),
                )),
              ],
            );
          } else {
            return Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: variation.entries.map((entry) {
                if (entry.key != 'stock') {
                  return Text('${entry.key}: ${entry.value}');
                }
                return const SizedBox.shrink();
              }).toList(),
            );
          }
        }
        return const SizedBox.shrink();
      }),
    ],
  );
}

Widget _buildAddOns(List<dynamic> addOns) {
  return Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      const Text(
        'Add-ons:',
        style: TextStyle(fontWeight: FontWeight.bold, decoration: TextDecoration.underline),
      ),
      ...addOns.map((addon) => Padding(
        padding: const EdgeInsets.only(left: 16),
        child: Text('${addon['name']}: ${addon['quantity']} x ${InvoiceUtils.formatCurrency(addon['price'])}'),
      )),
    ],
  );
}
```

### Order Summary
```dart
Widget _buildOrderSummary() {
  double subtotal = 0;
  double addOnsCost = 0;
  double totalTax = 0;

  // Calculate totals
  for (var detail in order.details) {
    subtotal += detail.totalAmount;
    totalTax += detail.taxAmount * detail.quantity;
    
    // Calculate add-ons cost
    for (var addon in detail.addOns) {
      addOnsCost += addon['price'] * addon['quantity'];
    }
  }

  final totalDiscount = order.storeDiscountAmount + order.couponDiscountAmount + order.refBonusAmount;

  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        _buildSummaryRow('Subtotal', subtotal + addOnsCost, 
            order.taxStatus == 'included' ? ' (TAX Included)' : ''),
        _buildSummaryRow('Discount', -totalDiscount),
        _buildSummaryRow('Coupon Discount', -order.couponDiscountAmount),
        if (order.refBonusAmount > 0)
          _buildSummaryRow('Referral Discount', -order.refBonusAmount),
        if (order.taxStatus == 'excluded' || order.taxStatus == null)
          _buildSummaryRow('VAT/Tax', order.totalTaxAmount, prefix: '+'),
        _buildSummaryRow('Delivery Man Tips', order.dmTips, prefix: '+'),
        _buildSummaryRow('Delivery Charge', order.deliveryCharge),
        _buildSummaryRow('Additional Charge', order.additionalCharge, prefix: '+'),
        if (order.extraPackagingAmount > 0)
          _buildSummaryRow('Extra Packaging Amount', order.extraPackagingAmount, prefix: '+'),
        const Divider(thickness: 2),
        _buildSummaryRow('Total', order.orderAmount, isTotal: true),
      ],
    ),
  );
}

Widget _buildSummaryRow(String label, double amount, {String prefix = '', String suffix = '', bool isTotal = false}) {
  return Padding(
    padding: const EdgeInsets.symmetric(vertical: 2),
    child: Row(
      children: [
        Expanded(
          flex: 3,
          child: Text(
            '$label$suffix:',
            style: isTotal ? const TextStyle(fontWeight: FontWeight.bold) : null,
          ),
        ),
        Expanded(
          flex: 2,
          child: Text(
            '$prefix${InvoiceUtils.formatCurrency(amount)}',
            textAlign: TextAlign.right,
            style: isTotal ? const TextStyle(fontWeight: FontWeight.bold) : null,
          ),
        ),
      ],
    ),
  );
}
```

### Payment Information
```dart
Widget _buildPaymentInfo() {
  if (order.payments == null || order.payments!.isEmpty) return const SizedBox.shrink();

  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: order.payments!.map((payment) {
        String label;
        if (payment.paymentStatus == 'paid') {
          if (payment.paymentMethod == 'cash_on_delivery') {
            label = 'Paid with Cash (COD)';
          } else {
            label = 'Paid by ${payment.paymentMethod}';
          }
        } else {
          label = 'Due Amount (${payment.paymentMethod == 'cash_on_delivery' ? 'COD' : payment.paymentMethod})';
        }

        return _buildSummaryRow(label, payment.amount);
      }).toList(),
    ),
  );
}
```

### Thank You Section
```dart
Widget _buildThankYouSection() {
  return Container(
    padding: const EdgeInsets.all(16),
    child: Column(
      children: [
        Row(
          children: [
            Expanded(child: Image.asset('assets/images/invoice-star.png')),
            const SizedBox(width: 8),
            const Text(
              'THANK YOU',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(width: 8),
            Expanded(child: Image.asset('assets/images/invoice-star.png')),
          ],
        ),
        const SizedBox(height: 8),
        const Text(
          '© NutriBasket. All rights reserved.',
          style: TextStyle(fontSize: 12),
          textAlign: TextAlign.center,
        ),
      ],
    ),
  );
}
```

## 5. API Service

```dart
import 'package:http/http.dart' as http;
import 'dart:convert';

class B2BInvoiceApiService {
  static Future<B2BOrder> getOrderDetails(int orderId) async {
    final response = await http.get(
      Uri.parse('your-api-base-url/admin/b2b-order/$orderId'),
      headers: {
        'Authorization': 'Bearer your-token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return B2BOrder.fromJson(data);
    } else {
      throw Exception('Failed to load order details');
    }
  }
}
```

## 6. Usage Example

```dart
class InvoiceExample extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Invoice Example')),
      body: Center(
        child: ElevatedButton(
          onPressed: () async {
            try {
              final order = await B2BInvoiceApiService.getOrderDetails(123);
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => B2BInvoiceScreen(order: order),
                ),
              );
            } catch (e) {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Error: $e')),
              );
            }
          },
          child: const Text('View Invoice'),
        ),
      ),
    );
  }
}
```

## 7. Dependencies (pubspec.yaml)

```yaml
dependencies:
  flutter:
    sdk: flutter
  intl: ^0.18.0
  http: ^0.13.5
  printing: ^5.9.3
  pdf: ^3.8.4

assets:
  - assets/images/invoice-logo.png
  - assets/images/invoice-star.png
```

## 8. Key Features

1. **Complete Data Model**: Mirrors the Laravel B2B order structure
2. **Responsive Layout**: Adapts to different screen sizes
3. **Print Functionality**: Ready for PDF generation
4. **Currency Formatting**: Proper Indian Rupee formatting
5. **Variations & Add-ons**: Handles complex product variations
6. **Payment Details**: Shows all payment information
7. **Tax Calculations**: Handles included/excluded tax scenarios
8. **Error Handling**: Graceful handling of missing data

## 9. API Endpoint Reference

The Flutter app should call the same API endpoint that the Laravel invoice uses:

```
GET /admin/b2b-order/{id}
```

This endpoint returns the complete order data with all relationships (store, customer, details, payments) that the invoice template uses.

## 10. Styling Notes

- Use consistent padding (16px) throughout
- Maintain proper spacing between sections
- Use bold text for important information (totals, order ID)
- Align currency amounts to the right
- Use dividers to separate sections
- Keep the layout clean and professional

This reference provides everything you need to create an identical invoice UI in your Flutter app that matches the Laravel implementation. 