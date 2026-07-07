import 'package:sixam_mart_delivery/api/api_client.dart';
import 'package:sixam_mart_delivery/common/models/response_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/ignore_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_cancellation_body.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/update_status_body_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/repositories/order_repository_interface.dart';
import 'package:sixam_mart_delivery/util/app_constants.dart';
import 'package:get/get.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:convert';

class OrderRepository implements OrderRepositoryInterface {
  final ApiClient apiClient;
  final SharedPreferences sharedPreferences;
  OrderRepository({required this.apiClient, required this.sharedPreferences});

  @override
  Future<List<CancellationData>?> getCancelReasons() async {
    List<CancellationData>? orderCancelReasons;
    Response response = await apiClient.getData(
        '${AppConstants.orderCancellationUri}?offset=1&limit=30&type=deliveryman');
    if (response.statusCode == 200) {
      OrderCancellationBody orderCancellationBody =
          OrderCancellationBody.fromJson(response.body);
      orderCancelReasons = [];
      for (var element in orderCancellationBody.reasons!) {
        orderCancelReasons.add(element);
      }
    }
    return orderCancelReasons;
  }

  @override
  Future<Response> get(int? id) async {
    // Use packager order details endpoint
    Response response =
        await apiClient.getData('${AppConstants.packagerOrderDetailsUri}$id');
    return response;
  }

  @override
  Future<PaginatedOrderModel?> getCompletedOrderList(int offset) async {
    PaginatedOrderModel? paginatedOrderModel;
    // Use packager orders endpoint for completed orders
    Response response = await apiClient.getData(
        '${AppConstants.packagerOrdersUri}?offset=$offset&limit=10&status=delivered');
    if (response.statusCode == 200) {
      paginatedOrderModel = PaginatedOrderModel.fromJson(response.body);
    }
    return paginatedOrderModel;
  }

  @override
  Future<List<OrderModel>?> getList() async {
    List<OrderModel>? currentOrderList;
    // Use packager orders endpoint for current orders
    Response response = await apiClient.getData(AppConstants.packagerOrdersUri);
    if (response.statusCode == 200) {
      currentOrderList = [];
      // Handle both {orders: [...]} and direct list
      if (response.body is List) {
        response.body.forEach(
            (order) => currentOrderList!.add(OrderModel.fromJson(order)));
      } else if (response.body is Map && response.body['orders'] is List) {
        response.body['orders'].forEach(
            (order) => currentOrderList!.add(OrderModel.fromJson(order)));
      }
    }
    return currentOrderList;
  }

  @override
  Future<List<OrderModel>?> getLatestOrders() async {
    List<OrderModel>? latestOrderList;
    // Use packager orders endpoint for latest orders
    Response response = await apiClient.getData(AppConstants.packagerOrdersUri);
    if (response.statusCode == 200) {
      latestOrderList = [];
      // Handle both {orders: [...]} and direct list
      if (response.body is List) {
        response.body.forEach(
            (order) => latestOrderList!.add(OrderModel.fromJson(order)));
      } else if (response.body is Map && response.body['orders'] is List) {
        response.body['orders'].forEach(
            (order) => latestOrderList!.add(OrderModel.fromJson(order)));
      }
    }
    return latestOrderList;
  }

  @override
  Future<ResponseModel> updateOrderStatus(
      UpdateStatusBodyModel updateStatusBody,
      List<MultipartBody> proofAttachment) async {
    ResponseModel responseModel;
    Response response = await apiClient.postMultipartData(
        AppConstants.updateOrderStatusUri,
        updateStatusBody.toJson(),
        proofAttachment);
    if (response.statusCode == 200) {
      responseModel = ResponseModel(true, response.body['message']);
    } else {
      responseModel = ResponseModel(false, response.statusText);
    }
    return responseModel;
  }

  @override
  Future<List<OrderDetailsModel>?> getOrderDetails(int? orderID) async {
    List<OrderDetailsModel>? orderDetailsList;
    Response response = await apiClient
        .getData('${AppConstants.packagerOrderDetailsUri}$orderID');
    if (response.statusCode == 200) {
      orderDetailsList = [];
      response.body.forEach(
          (order) => orderDetailsList!.add(OrderDetailsModel.fromJson(order)));
    }
    return orderDetailsList;
  }

  @override
  Future<ResponseModel> acceptOrder(int? orderID) async {
    ResponseModel responseModel;
    Response response = await apiClient
        .postData(AppConstants.acceptOrderUri, {'order_id': orderID});
    if (response.statusCode == 200) {
      responseModel = ResponseModel(true, response.body['message']);
    } else {
      responseModel = ResponseModel(false, response.statusText);
    }
    return responseModel;
  }

  @override
  List<IgnoreModel> getIgnoreList() {
    List<IgnoreModel> ignoreList = [];
    if (sharedPreferences.containsKey(AppConstants.ignoreList)) {
      List<String> stringList =
          sharedPreferences.getStringList(AppConstants.ignoreList) ?? [];
      for (var ignore in stringList) {
        ignoreList.add(IgnoreModel.fromJson(jsonDecode(ignore)));
      }
    }
    return ignoreList;
  }

  @override
  void setIgnoreList(List<IgnoreModel> ignoreList) {
    List<String> stringList = [];
    for (var ignore in ignoreList) {
      stringList.add(jsonEncode(ignore.toJson()));
    }
    sharedPreferences.setStringList(AppConstants.ignoreList, stringList);
  }

  @override
  List<IgnoreModel> tempList(
      DateTime currentTime, List<IgnoreModel> ignoredRequests) {
    List<IgnoreModel> tempList = [];
    tempList.addAll(ignoredRequests);
    for (int index = 0; index < tempList.length; index++) {
      if (currentTime.difference(tempList[index].time!).inMinutes > 10) {
        tempList.removeAt(index);
      }
    }
    return tempList;
  }

  @override
  List<int?> prepareIgnoreIdList(List<IgnoreModel> ignoredRequests) {
    List<int?> ignoredIdList = [];
    for (var ignore in ignoredRequests) {
      ignoredIdList.add(ignore.id);
    }
    return ignoredIdList;
  }

  @override
  List<OrderModel> processLatestOrders(
      List<OrderModel> latestOrderList, List<int?> ignoredIdList) {
    List<OrderModel> latestOrderList0 = [];
    for (var order in latestOrderList) {
      if (!ignoredIdList.contains(order.id)) {
        latestOrderList0.add(order);
      }
    }
    return latestOrderList0;
  }

  String _getUserToken() {
    return sharedPreferences.getString(AppConstants.token) ?? "";
  }

  // Packager-specific methods
  @override
  Future<List<OrderModel>?> getAssignedOrders() async {
    List<OrderModel>? orderList;
    Response response = await apiClient.getData('${AppConstants.packagerOrdersUri}?limit=100');
    if (response.statusCode == 200) {
      orderList = [];
      // Handle both {orders: [...]} and direct list
      if (response.body is List) {
        response.body
            .forEach((order) => orderList!.add(OrderModel.fromJson(order)));
      } else if (response.body is Map && response.body['orders'] is List) {
        response.body['orders']
            .forEach((order) => orderList!.add(OrderModel.fromJson(order)));
      }
    }
    return orderList;
  }

  @override
  Future<List<OrderModel>?> getOrdersByStatus(String status) async {
    List<OrderModel>? orderList;
    Response response = await apiClient
        .getData('${AppConstants.packagerOrdersByStatusUri}$status?limit=100');
    if (response.statusCode == 200) {
      orderList = [];
      response.body
          .forEach((order) => orderList!.add(OrderModel.fromJson(order)));
    }
    return orderList;
  }

  @override
  Future<List<OrderModel>?> getPackagingOrders() async {
    List<OrderModel>? orderList;
    Response response =
        await apiClient.getData('${AppConstants.packagerPackagingOrdersUri}?limit=100');
    if (response.statusCode == 200) {
      orderList = [];
      response.body
          .forEach((order) => orderList!.add(OrderModel.fromJson(order)));
    }
    return orderList;
  }

  @override
  Future<List<OrderModel>?> getPickedUpOrders() async {
    List<OrderModel>? orderList;
    Response response =
        await apiClient.getData('${AppConstants.packagerPickedUpOrdersUri}?limit=100');
    if (response.statusCode == 200) {
      orderList = [];
      response.body
          .forEach((order) => orderList!.add(OrderModel.fromJson(order)));
    }
    return orderList;
  }

  @override
  Future<Response> getPackagerOrderDetails(int orderId) async {
    return await apiClient
        .getData(AppConstants.packagerOrderDetailsUri + orderId.toString());
  }

  @override
  Future<ResponseModel> updatePackagerOrderStatus(
      Map<String, dynamic> body) async {
    ResponseModel responseModel;
    Response response = await apiClient.putData(
        AppConstants.packagerOrderUpdateStatusUri, body);
    if (response.statusCode == 200) {
      responseModel = ResponseModel(true, response.body['message']);
    } else {
      responseModel = ResponseModel(false, response.statusText);
    }
    return responseModel;
  }

  // RepositoryInterface implementation
  @override
  Future add(value) {
    throw UnimplementedError();
  }

  @override
  Future delete(int? id) {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body) {
    throw UnimplementedError();
  }
}
