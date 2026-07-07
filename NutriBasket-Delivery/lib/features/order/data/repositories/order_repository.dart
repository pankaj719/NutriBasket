import 'package:http/http.dart' as http;
import 'package:sixam_mart_delivery/api/api_client.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/ignore_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/update_status_body_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/repositories/order_repository_interface.dart';

// Add this import if you have an ApiClient class in your project
// import 'path_to_api_client.dart';

class OrderRepository implements OrderRepositoryInterface {
  final dynamic apiClient; // Replace with your actual ApiClient type

  OrderRepository({required this.apiClient});

  // ...other methods...

  @override
  Future<void> requestQuantityChange({
    required String orderId,
    required String itemId,
    required double newQuantity,
    required String reason,
  }) async {
    print('Sending change request: $orderId, $itemId, $newQuantity, $reason');
    final response = await apiClient.postData(
      '/api/v1/order-change-requests/$orderId',
      {
        'item_id': itemId,
        'new_quantity': newQuantity,
        'reason': reason,
      },
    );
    if (response.statusCode != 200) {
      throw Exception('Failed to submit change request');
    }
  }

  @override
  Future acceptOrder(int? orderID) {
    // TODO: implement acceptOrder
    throw UnimplementedError();
  }

  @override
  Future add(value) {
    // TODO: implement add
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    // TODO: implement delete
    throw UnimplementedError();
  }

  @override
  Future get(int? id) {
    // TODO: implement get
    throw UnimplementedError();
  }

  @override
  Future getCancelReasons() {
    // TODO: implement getCancelReasons
    throw UnimplementedError();
  }

  @override
  Future getCompletedOrderList(int offset) {
    // TODO: implement getCompletedOrderList
    throw UnimplementedError();
  }

  @override
  List<IgnoreModel> getIgnoreList() {
    // TODO: implement getIgnoreList
    throw UnimplementedError();
  }

  @override
  Future getLatestOrders() {
    // TODO: implement getLatestOrders
    throw UnimplementedError();
  }

  @override
  Future getList() {
    // TODO: implement getList
    throw UnimplementedError();
  }

  @override
  Future getOrderDetails(int? orderID) {
    // TODO: implement getOrderDetails
    throw UnimplementedError();
  }

  @override
  void setIgnoreList(List<IgnoreModel> ignoreList) {
    // TODO: implement setIgnoreList
  }

  @override
  Future update(Map<String, dynamic> body) {
    // TODO: implement update
    throw UnimplementedError();
  }

  @override
  Future updateOrderStatus(UpdateStatusBodyModel updateStatusBody,
      List<MultipartBody> proofAttachment) {
    // TODO: implement updateOrderStatus
    throw UnimplementedError();
  }

  @override
  Future<void> requestBulkQuantityChange(
      {required String orderId, required List<Map<String, dynamic>> changes}) {
    // TODO: implement requestBulkQuantityChange
    throw UnimplementedError();
  }
}
