import 'package:get/get_connect/http/src/response/response.dart';
import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/features/order/domain/models/order_change_request_model.dart';
import 'package:sixam_mart/interfaces/repository_interface.dart';

abstract class OrderRepositoryInterface extends RepositoryInterface {
  @override
  Future get(String? id, {String? guestId});
  @override
  Future getList(
      {int? offset,
      bool isRunningOrder = false,
      bool isHistoryOrder = false,
      bool isCancelReasons = false,
      bool isRefundReasons = false,
      bool fromDashboard,
      bool isSupportReasons = false});
  Future<Response> submitRefundRequest(Map<String, String> body, XFile? data);
  Future<Response> trackOrder(String? orderID, String? guestId,
      {String? contactNumber});
  Future<bool> cancelOrder(String orderID, String? reason, {String? guestId});
  Future<Response> switchToCOD(String? orderID, {String? guestId});
  Future<Response> submitOrderChangeRequest(
      String orderId, OrderChangeRequestBodyModel requestBody,
      {String? guestId});
  Future<Response> submitBulkOrderChangeRequest(
      String orderId, BulkOrderChangeRequestBodyModel requestBody,
      {String? guestId});

  // B2B Order Management
  Future<Response> getB2bEditableOrder(String orderId, {String? guestId});
  Future<Response> submitB2bOrderChanges(
      String orderId, Map<String, dynamic> requestBody,
      {String? guestId});
  Future<Response> getB2bChangeRequests(String orderId, {String? guestId});
}
