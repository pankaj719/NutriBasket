import 'dart:convert';
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';

// B2B Order Models for Professional Thermal Invoice
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
      storeDiscountAmount:
          double.parse(json['store_discount_amount'].toString()),
      couponDiscountAmount:
          double.parse(json['coupon_discount_amount'].toString()),
      refBonusAmount: double.parse(json['ref_bonus_amount'].toString()),
      dmTips: double.parse(json['dm_tips'].toString()),
      additionalCharge: double.parse(json['additional_charge'].toString()),
      extraPackagingAmount:
          double.parse(json['extra_packaging_amount'].toString()),
      paymentMethod: json['payment_method'],
      adjusment: double.parse(json['adjusment'].toString()),
      taxStatus: json['tax_status'] ?? 'excluded',
      store: json['store'] != null ? B2BStore.fromJson(json['store']) : null,
      customer: json['customer'] != null
          ? B2BCustomer.fromJson(json['customer'])
          : null,
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
  final double quantity;
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
      quantity: json['quantity']?.toDouble() ?? 0.0,
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

// Utility Functions
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

// API Service for B2B Invoice
class B2BInvoiceService {
  static const String baseUrl = 'https://backend.nutribasket.in';

  // Convert existing OrderModel to B2BOrder for invoice display
  static B2BOrder? convertOrderToB2BOrder(dynamic orderData) {
    try {
      print('Converting order data to B2BOrder...');
      if (orderData == null) {
        print('Order data is null');
        return null;
      }

      // Handle both OrderModel and Map<String, dynamic>
      Map<String, dynamic> orderJson;
      if (orderData is Map<String, dynamic>) {
        orderJson = orderData;
        print('Order data is Map<String, dynamic>');
      } else {
        // Convert OrderModel to JSON
        orderJson = orderData.toJson();
        print('Order data converted to JSON');
      }

      print('Order ID: ${orderJson['id']}');
      print('Order Status: ${orderJson['order_status']}');
      print('Order Amount: ${orderJson['order_amount']}');

      // Create B2BOrder from existing order data
      return B2BOrder(
        id: orderJson['id'] ?? 0,
        orderStatus: orderJson['order_status'] ?? 'unknown',
        createdAt: orderJson['created_at'] != null
            ? DateTime.parse(orderJson['created_at'])
            : DateTime.now(),
        orderAmount: (orderJson['order_amount'] ?? 0).toDouble(),
        deliveryCharge: (orderJson['delivery_charge'] ?? 0).toDouble(),
        totalTaxAmount: (orderJson['total_tax_amount'] ?? 0).toDouble(),
        storeDiscountAmount:
            (orderJson['store_discount_amount'] ?? 0).toDouble(),
        couponDiscountAmount:
            (orderJson['coupon_discount_amount'] ?? 0).toDouble(),
        refBonusAmount: (orderJson['ref_bonus_amount'] ?? 0).toDouble(),
        dmTips: (orderJson['dm_tips'] ?? 0).toDouble(),
        additionalCharge: (orderJson['additional_charge'] ?? 0).toDouble(),
        extraPackagingAmount:
            (orderJson['extra_packaging_amount'] ?? 0).toDouble(),
        paymentMethod: orderJson['payment_method'] ?? 'unknown',
        adjusment: 0.0, // Not available in current model
        taxStatus: orderJson['tax_status'] ?? 'excluded',
        store: _createB2BStore(orderJson),
        customer: _createB2BCustomer(orderJson),
        details: _createB2BOrderDetails(orderJson),
        payments: _createB2BPayments(orderJson),
        deliveryAddress: _getDeliveryAddress(orderJson),
      );
    } catch (e) {
      print('Error converting order to B2BOrder: $e');
      return null;
    }
  }

  static B2BStore? _createB2BStore(Map<String, dynamic> orderJson) {
    try {
      final storeData = orderJson['store'];
      if (storeData == null) return null;

      return B2BStore(
        name: storeData['name'] ?? orderJson['store_name'] ?? 'Unknown Store',
        address:
            storeData['address'] ?? orderJson['store_address'] ?? 'No Address',
        phone: storeData['phone'] ?? orderJson['store_phone'] ?? 'No Phone',
        gstCode: storeData['gst_code'],
        gstStatus: storeData['gst_status'] ?? false,
      );
    } catch (e) {
      print('Error creating B2BStore: $e');
      return null;
    }
  }

  static B2BCustomer? _createB2BCustomer(Map<String, dynamic> orderJson) {
    try {
      final customerData = orderJson['customer'];
      if (customerData == null) return null;

      return B2BCustomer(
        fName: customerData['f_name'] ?? '',
        lName: customerData['l_name'] ?? '',
        phone: customerData['phone'] ?? '',
      );
    } catch (e) {
      print('Error creating B2BCustomer: $e');
      return null;
    }
  }

  static List<B2BOrderDetail> _createB2BOrderDetails(
      Map<String, dynamic> orderJson) {
    try {
      final items = orderJson['items'] ?? orderJson['item_models'] ?? [];
      if (items is! List) return [];

      return items.map((item) {
        return B2BOrderDetail(
          quantity: item['quantity'] ?? 0,
          price: (item['price'] ?? 0).toDouble(),
          taxAmount: (item['tax_amount'] ?? 0).toDouble(),
          itemDetails: {
            'name': item['item_name'] ?? item['name'] ?? 'Unknown Item',
            'description': item['description'] ?? '',
          },
          variation: item['variation'] ?? [],
          addOns: item['add_ons'] ?? [],
        );
      }).toList();
    } catch (e) {
      print('Error creating B2BOrderDetails: $e');
      return [];
    }
  }

  static List<B2BPayment>? _createB2BPayments(Map<String, dynamic> orderJson) {
    try {
      final payments = orderJson['payments'];
      if (payments == null || payments is! List) return null;

      return payments.map((payment) {
        return B2BPayment(
          paymentMethod: payment['payment_method'] ?? 'unknown',
          paymentStatus: payment['payment_status'] ?? 'pending',
          amount: (payment['amount'] ?? 0).toDouble(),
        );
      }).toList();
    } catch (e) {
      print('Error creating B2BPayments: $e');
      return null;
    }
  }

  static String? _getDeliveryAddress(Map<String, dynamic> orderJson) {
    try {
      final deliveryAddress = orderJson['delivery_address'];
      if (deliveryAddress != null && deliveryAddress is Map<String, dynamic>) {
        return deliveryAddress['address'];
      }
      return orderJson['delivery_address']?.toString();
    } catch (e) {
      print('Error getting delivery address: $e');
      return null;
    }
  }

  static Future<B2BOrder?> getOrderDetails(int orderId, String token) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/orders/$orderId'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['status'] == 'success') {
          return B2BOrder.fromJson(data['data']);
        }
      }
      return null;
    } catch (e) {
      print('Error fetching order details: $e');
      return null;
    }
  }

  static Future<Uint8List?> downloadInvoice(int orderId, String token) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/api/v1/orders/$orderId/invoice'),
        headers: {
          'Content-Type': 'application/json',
          'Authorization': 'Bearer $token',
        },
      );

      if (response.statusCode == 200) {
        return response.bodyBytes;
      }
      return null;
    } catch (e) {
      print('Error downloading invoice: $e');
      return null;
    }
  }
}
