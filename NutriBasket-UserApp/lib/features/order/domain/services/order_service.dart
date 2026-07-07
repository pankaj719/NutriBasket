import 'package:flutter/foundation.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';
import 'package:sixam_mart/features/home/controllers/home_controller.dart';
import 'package:sixam_mart/features/order/domain/models/order_cancellation_body.dart';
import 'package:sixam_mart/features/order/domain/models/order_change_request_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_model.dart';
import 'package:sixam_mart/features/order/domain/repositories/order_repository_interface.dart';
import 'package:sixam_mart/features/order/domain/services/order_service_interface.dart';
import 'package:sixam_mart/helper/route_helper.dart';
import 'package:sixam_mart/util/app_constants.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';

class OrderService implements OrderServiceInterface {
  final OrderRepositoryInterface orderRepositoryInterface;
  OrderService({required this.orderRepositoryInterface});

  @override
  Future<PaginatedOrderModel?> getRunningOrderList(
      int offset, bool fromDashboard) async {
    return await orderRepositoryInterface.getList(
        isRunningOrder: true, offset: offset, fromDashboard: fromDashboard);
  }

  @override
  Future<PaginatedOrderModel?> getHistoryOrderList(int offset) async {
    return await orderRepositoryInterface.getList(
        isHistoryOrder: true, offset: offset);
  }

  @override
  Future<List<String?>?> getSupportReasonsList() async {
    return await orderRepositoryInterface.getList(isSupportReasons: true);
  }

  @override
  Future<List<OrderDetailsModel>?> getOrderDetails(
      String orderID, String? guestId) async {
    return await orderRepositoryInterface.get(orderID, guestId: guestId);
  }

  @override
  Future<List<CancellationData>?> getCancelReasons() async {
    return await orderRepositoryInterface.getList(isCancelReasons: true);
  }

  @override
  Future<List<String?>?> getRefundReasons() async {
    return await orderRepositoryInterface.getList(isRefundReasons: true);
  }

  @override
  Future<void> submitRefundRequest(
      int selectedReasonIndex,
      List<String?>? refundReasons,
      String note,
      String? orderId,
      XFile? refundImage) async {
    if (selectedReasonIndex == 0) {
      showCustomSnackBar('please_select_reason'.tr);
    } else {
      Map<String, String> body = {};
      body.addAll(<String, String>{
        'customer_reason': refundReasons![selectedReasonIndex]!,
        'order_id': orderId!,
        'customer_note': note,
      });
      Response response =
          await orderRepositoryInterface.submitRefundRequest(body, refundImage);
      if (response.statusCode == 200) {
        showCustomSnackBar(response.body['message'], isError: false);
        Get.offAllNamed(RouteHelper.getInitialRoute());
      }
    }
  }

  @override
  Future<Response> trackOrder(String? orderID, String? guestId,
      {String? contactNumber}) async {
    return await orderRepositoryInterface.trackOrder(orderID, guestId,
        contactNumber: contactNumber);
  }

  @override
  Future<bool> cancelOrder(String orderID, String? reason,
      {String? guestId}) async {
    return await orderRepositoryInterface.cancelOrder(orderID, reason,
        guestId: guestId);
  }

  @override
  OrderModel? prepareOrderModel(
      PaginatedOrderModel? runningOrderModel, int? orderID) {
    OrderModel? orderModel;
    if (runningOrderModel != null) {
      for (OrderModel order in runningOrderModel.orders!) {
        if (order.id == orderID) {
          orderModel = order;
          break;
        }
      }
    }
    return orderModel;
  }

  @override
  Future<bool> switchToCOD(String? orderID, {String? guestId}) async {
    bool isSuccess = false;
    Response response =
        await orderRepositoryInterface.switchToCOD(orderID, guestId: guestId);
    if (response.statusCode == 200) {
      isSuccess = true;
      await Get.offAllNamed(RouteHelper.getInitialRoute());
      showCustomSnackBar(response.body['message'], isError: false);
    }
    return isSuccess;
  }

  @override
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
    bool forOrder = (addFundUrl == '' &&
        addFundUrl!.isEmpty &&
        subscriptionUrl == '' &&
        subscriptionUrl!.isEmpty);
    bool forSubscription = (subscriptionUrl != null &&
        subscriptionUrl.isNotEmpty &&
        addFundUrl == '' &&
        addFundUrl!.isEmpty);

    if (canRedirect) {
      bool isSuccess = forSubscription
          ? url.startsWith('${AppConstants.baseUrl}/subscription-success')
          : url.startsWith('${AppConstants.baseUrl}/payment-success');
      bool isFailed = forSubscription
          ? url.startsWith('${AppConstants.baseUrl}/subscription-fail')
          : url.startsWith('${AppConstants.baseUrl}/payment-fail');
      bool isCancel = forSubscription
          ? url.startsWith('${AppConstants.baseUrl}/subscription-cancel')
          : url.startsWith('${AppConstants.baseUrl}/payment-cancel');
      if (isSuccess || isFailed || isCancel) {
        canRedirect = false;
        onClose();
      }

      if (forOrder) {
        if (isSuccess) {
          Get.offNamed(RouteHelper.getOrderSuccessRoute(orderID, contactNumber,
              createAccount: createAccount, guestId: guestId));
        } else if (isFailed || isCancel) {
          Get.offNamed(RouteHelper.getOrderSuccessRoute(orderID, contactNumber,
              createAccount: createAccount, guestId: guestId));
        }
      } else {
        if (isSuccess || isFailed || isCancel) {
          if (Get.currentRoute.contains(RouteHelper.payment)) {
            Get.back();
          }
          if (forSubscription) {
            Get.find<HomeController>()
                .saveRegistrationSuccessfulSharedPref(true);
            Get.find<HomeController>().saveIsStoreRegistrationSharedPref(true);
            Get.offAllNamed(RouteHelper.getSubscriptionSuccessRoute(
                status: isSuccess
                    ? 'success'
                    : isFailed
                        ? 'fail'
                        : 'cancel',
                fromSubscription: true,
                storeId: storeId));
          } else {
            Get.back();
            Get.toNamed(RouteHelper.getWalletRoute(
                fundStatus: isSuccess
                    ? 'success'
                    : isFailed
                        ? 'fail'
                        : 'cancel',
                token: UniqueKey().toString()));
          }
        }
      }
    }
  }

  @override
  Future<bool> submitOrderChangeRequest(
      String orderId, OrderChangeRequestBodyModel requestBody,
      {String? guestId}) async {
    bool isSuccess = false;
    Response response = await orderRepositoryInterface
        .submitOrderChangeRequest(orderId, requestBody, guestId: guestId);
    if (response.statusCode == 200) {
      isSuccess = true;
      showCustomSnackBar('order_change_request_submitted'.tr, isError: false);
    } else {
      showCustomSnackBar(
          response.body['message'] ?? 'failed_to_submit_request'.tr,
          isError: true);
    }
    return isSuccess;
  }

  @override
  Future<bool> submitBulkOrderChangeRequest(
      String orderId, BulkOrderChangeRequestBodyModel requestBody,
      {String? guestId}) async {
    bool isSuccess = false;
    Response response = await orderRepositoryInterface
        .submitBulkOrderChangeRequest(orderId, requestBody, guestId: guestId);
    if (response.statusCode == 200) {
      isSuccess = true;
      showCustomSnackBar('bulk_order_change_request_submitted'.tr,
          isError: false);
    } else {
      showCustomSnackBar(
          response.body['message'] ?? 'failed_to_submit_request'.tr,
          isError: true);
    }
    return isSuccess;
  }

  @override
  Future<Map<String, dynamic>?> getB2bEditableOrder(String orderId,
      {String? guestId}) async {
    Response response = await orderRepositoryInterface
        .getB2bEditableOrder(orderId, guestId: guestId);
    if (response.statusCode == 200) {
      return response.body;
    }
    return null;
  }

  @override
  Future<bool> submitB2bOrderChanges(
      String orderId, Map<String, dynamic> requestBody,
      {String? guestId}) async {
    bool isSuccess = false;
    Response response = await orderRepositoryInterface
        .submitB2bOrderChanges(orderId, requestBody, guestId: guestId);

    if (kDebugMode) {
      print('====> B2B Submit Response Status: ${response.statusCode}');
      print('====> B2B Submit Response Body: ${response.body}');
    }

    if (response.statusCode == 200) {
      isSuccess = true;
      showCustomSnackBar('b2b_order_change_submitted'.tr, isError: false);
    } else {
      String errorMessage = 'failed_to_submit_request'.tr;

      // Try to extract specific error message from response
      if (response.body != null) {
        if (response.body is Map && response.body.containsKey('message')) {
          errorMessage = response.body['message'];
        } else if (response.body is String) {
          errorMessage = response.body;
        }
      }

      showCustomSnackBar(errorMessage, isError: true);
    }
    return isSuccess;
  }

  @override
  Future<List<dynamic>?> getB2bChangeRequests(String orderId,
      {String? guestId}) async {
    Response response = await orderRepositoryInterface
        .getB2bChangeRequests(orderId, guestId: guestId);
    if (response.statusCode == 200) {
      return response.body['change_requests'];
    }
    return null;
  }

  @override
  bool isOrderEditableAfterDelivery(OrderModel order) {
    // This method only checks time-based editability for UI display
    // B2B permission check is handled in the dialog itself
    if (order.orderStatus != 'delivered' || order.acknowledgedAt != null) {
      return false;
    }

    if (order.delivered == null) {
      return false;
    }

    DateTime deliveredTime = DateTime.parse(order.delivered!);
    DateTime now = DateTime.now();
    Duration difference = now.difference(deliveredTime);

    // 24 hours = 1440 minutes
    return difference.inMinutes < 1440;
  }

  @override
  String getTimeRemainingForAcknowledgment(OrderModel order) {
    if (order.orderStatus != 'delivered' || order.acknowledgedAt != null) {
      return '';
    }

    if (order.delivered == null) {
      return '';
    }

    DateTime deliveredTime = DateTime.parse(order.delivered!);
    DateTime acknowledgeTime = deliveredTime.add(const Duration(hours: 24));
    DateTime now = DateTime.now();

    if (now.isAfter(acknowledgeTime)) {
      return '';
    }

    Duration remaining = acknowledgeTime.difference(now);
    int hours = remaining.inHours;
    int minutes = remaining.inMinutes % 60;

    if (hours > 0) {
      return '$hours ${'time_hour'.tr}${hours > 1 ? 's' : ''} $minutes ${'time_minute'.tr}${minutes != 1 ? 's' : ''}';
    } else {
      return '$minutes ${'time_minute'.tr}${minutes != 1 ? 's' : ''}';
    }
  }
}
