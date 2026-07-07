# 📦 Packager API Implementation Guide

## 🎯 **Problem Solved**

The issue was that the packager API was using B2B client relationships to determine order assignments, but you wanted it to use the `packager_id` field directly in the `orders` table. 

**Before**: API showed only orders for Ganshyam (30 orders)
**After**: API now shows orders for all packagers based on `packager_id` field

## 🔧 **Changes Made**

### **1. Updated PackagerController.php**

- **`getOrders()`**: Now uses `where('packager_id', $packager->id)` instead of B2B client relationships
- **`getOrderDetails()`**: Now uses `where('packager_id', $packager->id)` 
- **`updateOrderStatus()`**: Now uses `where('packager_id', $packager->id)`

### **2. Current Order Distribution**

Based on the test results:
- **Mohanlal**: 10 orders (10 in packaging)
- **Ganshyam**: 25 orders (25 in packaging) 
- **Yash Parashar**: 4 orders (3 in packaging, 1 picked up)
- **Vansh**: 1 order (1 in packaging)

## 🚀 **API Endpoints & Testing**

### **Authentication**
```http
POST https://backend.nutribasket.in/api/v1/auth/packager/login
Content-Type: application/json

{
    "phone": "packager_phone",
    "password": "packager_password"
}
```

### **Get Profile**
```http
GET https://backend.nutribasket.in/api/v1/auth/packager/profile
Authorization: Bearer {{token}}
```

### **Get All Orders**
```http
GET https://backend.nutribasket.in/api/v1/auth/packager/orders?limit=20&offset=0
Authorization: Bearer {{token}}
```

### **Get Packaging Orders**
```http
GET https://backend.nutribasket.in/api/v1/auth/packager/orders/packaging?limit=20&offset=0
Authorization: Bearer {{token}}
```

### **Get Order Details**
```http
GET https://backend.nutribasket.in/api/v1/auth/packager/order/{{order_id}}
Authorization: Bearer {{token}}
```

### **Update Order Status (Pass to Delivery)**
```http
POST https://backend.nutribasket.in/api/v1/auth/packager/order/update-status
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "order_id": 100007,
    "status": "picked_up",
    "note": "Order packaged and ready for delivery"
}
```

## 📱 **Flutter Implementation**

### **1. API Service (packager_api_service.dart)**
```dart
class PackagerApiService {
  static const String baseUrl = 'https://backend.nutribasket.in/api/v1';
  static String? _token;

  // Login
  static Future<Map<String, dynamic>> login(String phone, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/auth/packager/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({
        'phone': phone,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      await saveToken(data['token']);
      return data;
    } else {
      throw Exception('Login failed');
    }
  }

  // Get Profile
  static Future<Map<String, dynamic>> getProfile() async {
    await initializeToken();
    final response = await http.get(
      Uri.parse('$baseUrl/auth/packager/profile'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to get profile');
    }
  }

  // Get Orders
  static Future<OrdersResponse> getOrders({String? status, int limit = 20, int offset = 0}) async {
    await initializeToken();
    final queryParams = {
      'limit': limit.toString(),
      'offset': offset.toString(),
    };
    if (status != null) queryParams['status'] = status;

    final response = await http.get(
      Uri.parse('$baseUrl/auth/packager/orders').replace(queryParameters: queryParams),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      return OrdersResponse.fromJson(data);
    } else {
      throw Exception('Failed to get orders');
    }
  }

  // Get Order Details
  static Future<Map<String, dynamic>> getOrderDetails(int orderId) async {
    await initializeToken();
    final response = await http.get(
      Uri.parse('$baseUrl/auth/packager/order/$orderId'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
      },
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to get order details');
    }
  }

  // Update Order Status
  static Future<Map<String, dynamic>> updateOrderStatus(int orderId, String status, {String? note}) async {
    await initializeToken();
    final response = await http.post(
      Uri.parse('$baseUrl/auth/packager/order/update-status'),
      headers: {
        'Authorization': 'Bearer $_token',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({
        'order_id': orderId,
        'status': status,
        if (note != null) 'note': note,
      }),
    );

    if (response.statusCode == 200) {
      return jsonDecode(response.body);
    } else {
      throw Exception('Failed to update order status');
    }
  }
}
```

### **2. Data Models (packager_models.dart)**
```dart
class Order {
  final int id;
  final String orderStatus;
  final double orderAmount;
  final String? paymentStatus;
  final String? paymentMethod;
  final String? createdAt;
  final Map<String, dynamic>? deliveryAddress;
  final Customer? customer;
  final Store? store;
  final DeliveryMan? assignedDeliveryMan;
  final Packager? assignedPackager;
  final List<OrderItem> items;
  final List<Payment> payments;
  final int itemsCount;

  Order({
    required this.id,
    required this.orderStatus,
    required this.orderAmount,
    this.paymentStatus,
    this.paymentMethod,
    this.createdAt,
    this.deliveryAddress,
    this.customer,
    this.store,
    this.assignedDeliveryMan,
    this.assignedPackager,
    required this.items,
    required this.payments,
    required this.itemsCount,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      id: json['id'],
      orderStatus: json['order_status'],
      orderAmount: (json['order_amount'] as num).toDouble(),
      paymentStatus: json['payment_status'],
      paymentMethod: json['payment_method'],
      createdAt: json['created_at'],
      deliveryAddress: json['delivery_address'],
      customer: json['customer'] != null ? Customer.fromJson(json['customer']) : null,
      store: json['store'] != null ? Store.fromJson(json['store']) : null,
      assignedDeliveryMan: json['assigned_delivery_man'] != null 
          ? DeliveryMan.fromJson(json['assigned_delivery_man']) 
          : null,
      assignedPackager: json['assigned_packager'] != null 
          ? Packager.fromJson(json['assigned_packager']) 
          : null,
      items: (json['items'] as List<dynamic>?)
          ?.map((item) => OrderItem.fromJson(item))
          .toList() ?? [],
      payments: (json['payments'] as List<dynamic>?)
          ?.map((payment) => Payment.fromJson(payment))
          .toList() ?? [],
      itemsCount: json['items_count'] ?? 0,
    );
  }
}
```

### **3. Home Screen (screens/home_screen.dart)**
```dart
class HomeScreen extends StatefulWidget {
  @override
  _HomeScreenState createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  List<Order> _orders = [];
  bool _isLoading = true;
  String _selectedStatus = 'packaging';
  Packager? _packager;

  @override
  void initState() {
    super.initState();
    _loadProfile();
    _loadOrders();
  }

  Future<void> _loadProfile() async {
    try {
      final profileData = await PackagerApiService.getProfile();
      setState(() {
        _packager = Packager.fromJson(profileData);
      });
    } catch (e) {
      print('Error loading profile: $e');
    }
  }

  Future<void> _loadOrders() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await PackagerApiService.getOrders(status: _selectedStatus);
      setState(() {
        _orders = response.orders;
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading orders: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Packager Dashboard'),
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: _loadOrders,
          ),
        ],
      ),
      body: Column(
        children: [
          // Status Filter
          Container(
            padding: EdgeInsets.all(16),
            child: Row(
              children: [
                Text('Status: '),
                DropdownButton<String>(
                  value: _selectedStatus,
                  items: [
                    DropdownMenuItem(value: 'packaging', child: Text('Packaging')),
                    DropdownMenuItem(value: 'picked_up', child: Text('Picked Up')),
                  ],
                  onChanged: (value) {
                    setState(() {
                      _selectedStatus = value!;
                    });
                    _loadOrders();
                  },
                ),
              ],
            ),
          ),
          
          // Orders List
          Expanded(
            child: _isLoading
                ? Center(child: CircularProgressIndicator())
                : _orders.isEmpty
                    ? Center(child: Text('No orders found'))
                    : ListView.builder(
                        itemCount: _orders.length,
                        itemBuilder: (context, index) {
                          final order = _orders[index];
                          return Card(
                            margin: EdgeInsets.symmetric(horizontal: 16, vertical: 4),
                            child: ListTile(
                              title: Text('Order #${order.id}'),
                              subtitle: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Status: ${order.orderStatus}'),
                                  Text('Amount: \$${order.orderAmount.toStringAsFixed(2)}'),
                                  Text('Items: ${order.itemsCount}'),
                                  if (order.customer != null)
                                    Text('Customer: ${order.customer!.name}'),
                                ],
                              ),
                              trailing: Icon(Icons.arrow_forward_ios),
                              onTap: () {
                                Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                    builder: (context) => OrderDetailsScreen(order: order),
                                  ),
                                );
                              },
                            ),
                          );
                        },
                      ),
          ),
        ],
      ),
    );
  }
}
```

### **4. Order Details Screen (screens/order_details_screen.dart)**
```dart
class OrderDetailsScreen extends StatefulWidget {
  final Order order;

  OrderDetailsScreen({required this.order});

  @override
  _OrderDetailsScreenState createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen> {
  bool _isLoading = false;
  Order? _updatedOrder;

  @override
  void initState() {
    super.initState();
    _loadOrderDetails();
  }

  Future<void> _loadOrderDetails() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final response = await PackagerApiService.getOrderDetails(widget.order.id);
      setState(() {
        _updatedOrder = Order.fromJson(response);
        _isLoading = false;
      });
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error loading order details: $e')),
      );
    }
  }

  Future<void> _passToDelivery() async {
    setState(() {
      _isLoading = true;
    });

    try {
      await PackagerApiService.updateOrderStatus(
        widget.order.id,
        'picked_up',
        note: 'Order packaged and ready for delivery',
      );
      
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Order passed to delivery successfully!')),
      );
      
      Navigator.pop(context, true); // Return true to refresh the list
    } catch (e) {
      setState(() {
        _isLoading = false;
      });
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error updating order: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final order = _updatedOrder ?? widget.order;

    return Scaffold(
      appBar: AppBar(
        title: Text('Order #${order.id}'),
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Order Info
                  Card(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Order Information', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          SizedBox(height: 8),
                          Text('Status: ${order.orderStatus}'),
                          Text('Amount: \$${order.orderAmount.toStringAsFixed(2)}'),
                          Text('Payment Status: ${order.paymentStatus ?? 'N/A'}'),
                          Text('Created: ${order.createdAt ?? 'N/A'}'),
                        ],
                      ),
                    ),
                  ),
                  
                  SizedBox(height: 16),
                  
                  // Customer Info
                  if (order.customer != null) ...[
                    Card(
                      child: Padding(
                        padding: EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('Customer Information', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                            SizedBox(height: 8),
                            Text('Name: ${order.customer!.name}'),
                            Text('Email: ${order.customer!.email}'),
                            Text('Phone: ${order.customer!.phone}'),
                          ],
                        ),
                      ),
                    ),
                    SizedBox(height: 16),
                  ],
                  
                  // Items List
                  Card(
                    child: Padding(
                      padding: EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Order Items (${order.items.length})', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                          SizedBox(height: 8),
                          ...order.items.map((item) => Padding(
                            padding: EdgeInsets.symmetric(vertical: 4),
                            child: Row(
                              children: [
                                Expanded(
                                  flex: 3,
                                  child: Text(item.itemName),
                                ),
                                Expanded(
                                  flex: 1,
                                  child: Text('Qty: ${item.quantity}'),
                                ),
                                Expanded(
                                  flex: 2,
                                  child: Text('\$${item.total.toStringAsFixed(2)}'),
                                ),
                              ],
                            ),
                          )),
                        ],
                      ),
                    ),
                  ),
                  
                  SizedBox(height: 16),
                  
                  // Pass to Delivery Button
                  if (order.orderStatus == 'packaging')
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _isLoading ? null : _passToDelivery,
                        style: ElevatedButton.styleFrom(
                          padding: EdgeInsets.symmetric(vertical: 16),
                          backgroundColor: Colors.green,
                        ),
                        child: _isLoading
                            ? CircularProgressIndicator(color: Colors.white)
                            : Text(
                                'Pass to Delivery',
                                style: TextStyle(fontSize: 16, color: Colors.white),
                              ),
                      ),
                    ),
                ],
              ),
            ),
    );
  }
}
```

## 🧪 **Testing in Postman**

### **1. Create Postman Collection**
1. Create a new collection called "Packager API"
2. Add environment variables:
   - `base_url`: `https://backend.nutribasket.in/api/v1`
   - `token`: (will be set after login)

### **2. Login Request**
```
POST {{base_url}}/auth/packager/login
Content-Type: application/json

{
    "phone": "packager_phone",
    "password": "packager_password"
}
```

**Tests Tab:**
```javascript
if (pm.response.code === 200) {
    const response = pm.response.json();
    pm.environment.set("token", response.token);
}
```

### **3. Get Orders Request**
```
GET {{base_url}}/auth/packager/orders?limit=20&offset=0
Authorization: Bearer {{token}}
```

### **4. Get Order Details Request**
```
GET {{base_url}}/auth/packager/order/100007
Authorization: Bearer {{token}}
```

### **5. Update Order Status Request**
```
POST {{base_url}}/auth/packager/order/update-status
Authorization: Bearer {{token}}
Content-Type: application/json

{
    "order_id": 100007,
    "status": "picked_up",
    "note": "Order packaged and ready for delivery"
}
```

## ✅ **Verification**

Run the test script to verify the fix:
```bash
php test_packager_api.php
```

This will show you the current distribution of orders among packagers and confirm that the API is now working correctly with the `packager_id` field.

## 🎉 **Summary**

The packager API now correctly:
1. ✅ Shows orders based on `packager_id` field in orders table
2. ✅ Displays orders for all packagers (not just Ganshyam)
3. ✅ Allows packagers to see only their assigned orders
4. ✅ Provides proper order details and status updates
5. ✅ Supports the "Pass to Delivery" functionality

The Flutter app can now be implemented using the provided code structure and will work correctly with the updated API endpoints. 