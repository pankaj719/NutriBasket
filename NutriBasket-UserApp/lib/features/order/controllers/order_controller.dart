import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/common/models/response_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_cancellation_body.dart';
import 'package:sixam_mart/features/order/domain/models/order_change_request_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_model.dart';
import 'package:sixam_mart/features/order/domain/services/order_service_interface.dart';
import 'package:sixam_mart/helper/auth_helper.dart';

class OrderController extends GetxController implements GetxService {
  final OrderServiceInterface orderServiceInterface;

  OrderController({required this.orderServiceInterface});

  PaginatedOrderModel? _runningOrderModel;
  PaginatedOrderModel? get runningOrderModel => _runningOrderModel;

  PaginatedOrderModel? _historyOrderModel;
  PaginatedOrderModel? get historyOrderModel => _historyOrderModel;

  List<OrderDetailsModel>? _orderDetails;
  List<OrderDetailsModel>? get orderDetails => _orderDetails;

  OrderModel? _trackModel;
  OrderModel? get trackModel => _trackModel;

  ResponseModel? _responseModel;
  ResponseModel? get responseModel => _responseModel;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _showCancelled = false;
  bool get showCancelled => _showCancelled;

  bool _showBottomSheet = true;
  bool get showBottomSheet => _showBottomSheet;

  bool _showOneOrder = true;
  bool get showOneOrder => _showOneOrder;

  List<String?>? _refundReasons;
  List<String?>? get refundReasons => _refundReasons;

  int _selectedReasonIndex = 0;
  int get selectedReasonIndex => _selectedReasonIndex;

  XFile? _refundImage;
  XFile? get refundImage => _refundImage;

  String? _cancelReason;
  String? get cancelReason => _cancelReason;

  List<CancellationData>? _orderCancelReasons;
  List<CancellationData>? get orderCancelReasons => _orderCancelReasons;

  bool _isExpanded = false;
  bool get isExpanded => _isExpanded;

  List<String?>? _supportReasons;
  List<String?>? get supportReasons => _supportReasons;

  void expandedUpdate(bool status) {
    _isExpanded = status;
    update();
  }

  void setOrderCancelReason(String? reason) {
    _cancelReason = reason;
    update();
  }

  void selectReason(int index, {bool isUpdate = true}) {
    _selectedReasonIndex = index;
    if (isUpdate) {
      update();
    }
  }

  void showOrders() {
    _showOneOrder = !_showOneOrder;
    update();
  }

  void showRunningOrders({bool canUpdate = true}) {
    _showBottomSheet = !_showBottomSheet;
    if (canUpdate) {
      update();
    }
  }

  void pickRefundImage(bool isRemove) async {
    if (isRemove) {
      _refundImage = null;
    } else {
      _refundImage = await ImagePicker().pickImage(source: ImageSource.gallery);
      update();
    }
  }

  Future<void> getOrderCancelReasons() async {
    _orderCancelReasons = null;
    _orderCancelReasons = await orderServiceInterface.getCancelReasons();
    update();
  }

  Future<void> getRefundReasons() async {
    _selectedReasonIndex = 0;
    _refundReasons = null;
    _refundReasons = await orderServiceInterface.getRefundReasons();
    update();
  }

  Future<void> submitRefundRequest(String note, String? orderId) async {
    _isLoading = true;
    update();
    await orderServiceInterface.submitRefundRequest(
        _selectedReasonIndex, _refundReasons, note, orderId, _refundImage);
    _isLoading = false;
    update();
  }

  Future<void> getRunningOrders(int offset,
      {bool isUpdate = false, bool fromDashboard = false}) async {
    if (offset == 1) {
      _runningOrderModel = null;
      if (isUpdate) {
        update();
      }
    }
    PaginatedOrderModel? orderModel =
        await orderServiceInterface.getRunningOrderList(offset, fromDashboard);
    if (orderModel != null) {
      if (offset == 1) {
        _runningOrderModel = orderModel;
      } else {
        _runningOrderModel!.orders!.addAll(orderModel.orders!);
        _runningOrderModel!.offset = orderModel.offset;
        _runningOrderModel!.totalSize = orderModel.totalSize;
      }
      update();
    }
  }

  Future<void> getHistoryOrders(int offset, {bool isUpdate = false}) async {
    if (offset == 1) {
      _historyOrderModel = null;
      if (isUpdate) {
        update();
      }
    }
    PaginatedOrderModel? orderModel =
        await orderServiceInterface.getHistoryOrderList(offset);
    if (orderModel != null) {
      if (offset == 1) {
        _historyOrderModel = orderModel;
      } else {
        _historyOrderModel!.orders!.addAll(orderModel.orders!);
        _historyOrderModel!.offset = orderModel.offset;
        _historyOrderModel!.totalSize = orderModel.totalSize;
      }
      update();
    }
  }

  Future<void> getSupportReasons() async {
    _supportReasons = await orderServiceInterface.getSupportReasonsList();
    update();
  }

  Future<List<OrderDetailsModel>?> getOrderDetails(String orderID) async {
    _orderDetails = null;
    _isLoading = true;
    _showCancelled = false;

    if (_trackModel == null ||
        (_trackModel!.orderType != 'parcel' &&
            !_trackModel!.prescriptionOrder!)) {
      List<OrderDetailsModel>? detailsList =
          await orderServiceInterface.getOrderDetails(orderID,
              AuthHelper.isLoggedIn() ? null : AuthHelper.getGuestId());
      _isLoading = false;
      if (detailsList != null) {
        _orderDetails = [];
        _orderDetails!.addAll(detailsList);
      }
    } else {
      _isLoading = false;
      _orderDetails = [];
    }
    update();
    return _orderDetails;
  }

  Future<ResponseModel?> trackOrder(
      String? orderID, OrderModel? orderModel, bool fromTracking,
      {String? contactNumber, bool? fromGuestInput = false}) async {
    _trackModel = null;
    _responseModel = null;
    if (!fromTracking) {
      _orderDetails = null;
    }
    _showCancelled = false;
    if (orderModel == null) {
      _isLoading = true;
      Response response = await orderServiceInterface.trackOrder(
        orderID,
        AuthHelper.isLoggedIn() ? null : AuthHelper.getGuestId(),
        contactNumber: contactNumber,
      );
      if (response.statusCode == 200) {
        _trackModel = OrderModel.fromJson(response.body);
        _responseModel = ResponseModel(true, response.body.toString());
      } else {
        _responseModel = ResponseModel(false, response.statusText);
      }
      _isLoading = false;
      update();
    } else {
      _trackModel = orderModel;
      _responseModel = ResponseModel(true, 'Successful');
    }
    return _responseModel;
  }

  Future<ResponseModel?> timerTrackOrder(String orderID,
      {String? contactNumber}) async {
    _showCancelled = false;

    Response response = await orderServiceInterface.trackOrder(
      orderID,
      AuthHelper.isLoggedIn() ? null : AuthHelper.getGuestId(),
      contactNumber: contactNumber,
    );
    if (response.statusCode == 200) {
      _trackModel = OrderModel.fromJson(response.body);
      _responseModel = ResponseModel(true, response.body.toString());
    } else {
      _responseModel = ResponseModel(false, response.statusText);
    }
    update();

    return _responseModel;
  }

  Future<bool> cancelOrder(int? orderID, String? cancelReason,
      {String? guestId}) async {
    _isLoading = true;
    update();
    bool success = await orderServiceInterface
        .cancelOrder(orderID.toString(), cancelReason, guestId: guestId);
    _isLoading = false;
    Get.back();
    if (success) {
      OrderModel? orderModel =
          orderServiceInterface.prepareOrderModel(_runningOrderModel, orderID);
      if (_runningOrderModel != null) {
        _runningOrderModel!.orders!.remove(orderModel);
      }
      _showCancelled = true;
    }
    update();
    return success;
  }

  Future<bool> switchToCOD(String? orderID, {String? guestId}) async {
    _isLoading = true;
    update();
    bool isSuccess =
        await orderServiceInterface.switchToCOD(orderID, guestId: guestId);
    _isLoading = false;
    update();
    return isSuccess;
  }

  void paymentRedirect(
      {required String url,
      required bool canRedirect,
      required String? contactNumber,
      required Function onClose,
      required final String? addFundUrl,
      required final String? subscriptionUrl,
      required final String orderID,
      int? storeId,
      required bool createAccount,
      required String guestId}) {
    orderServiceInterface.paymentRedirect(
      url: url,
      canRedirect: canRedirect,
      contactNumber: contactNumber,
      onClose: onClose,
      addFundUrl: addFundUrl,
      subscriptionUrl: subscriptionUrl,
      orderID: orderID,
      storeId: storeId,
      createAccount: createAccount,
      guestId: guestId,
    );
  }

  Future<bool> submitOrderChangeRequest(
      String orderId, String itemId, double newQuantity) async {
    _isLoading = true;
    update();

    OrderChangeRequestBodyModel requestBody = OrderChangeRequestBodyModel(
      itemId: int.tryParse(itemId),
      newQuantity: newQuantity,
    );

    bool success = await orderServiceInterface.submitOrderChangeRequest(
        orderId, requestBody);
    _isLoading = false;
    update();
    return success;
  }

  Future<bool> submitBulkOrderChangeRequest(
      String orderId, List<OrderChangeRequestBodyModel> changes) async {
    _isLoading = true;
    update();

    BulkOrderChangeRequestBodyModel requestBody =
        BulkOrderChangeRequestBodyModel(
      changes: changes,
    );

    bool success = await orderServiceInterface.submitBulkOrderChangeRequest(
        orderId, requestBody);
    _isLoading = false;
    update();
    return success;
  }

  // B2B Order Management
  Map<String, dynamic>? _b2bEditableOrderData;
  Map<String, dynamic>? get b2bEditableOrderData => _b2bEditableOrderData;

  List<dynamic>? _b2bChangeRequests;
  List<dynamic>? get b2bChangeRequests => _b2bChangeRequests;

  Future<void> getB2bEditableOrder(String orderId) async {
    _isLoading = true;
    update();
    _b2bEditableOrderData =
        await orderServiceInterface.getB2bEditableOrder(orderId);
    _isLoading = false;
    update();
  }

  Future<bool> submitB2bOrderChanges(
      String orderId, Map<String, dynamic> requestBody) async {
    _isLoading = true;
    update();
    bool success =
        await orderServiceInterface.submitB2bOrderChanges(orderId, requestBody);
    _isLoading = false;
    update();
    return success;
  }

  Future<void> getB2bChangeRequests(String orderId) async {
    _isLoading = true;
    update();
    _b2bChangeRequests =
        await orderServiceInterface.getB2bChangeRequests(orderId);
    _isLoading = false;
    update();
  }

  bool isOrderEditableAfterDelivery(OrderModel order) {
    return orderServiceInterface.isOrderEditableAfterDelivery(order);
  }

  String getTimeRemainingForAcknowledgment(OrderModel order) {
    return orderServiceInterface.getTimeRemainingForAcknowledgment(order);
  }
}
