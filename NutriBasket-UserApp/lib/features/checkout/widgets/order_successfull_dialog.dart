import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/features/location/domain/models/zone_response_model.dart';
import 'package:sixam_mart/features/order/controllers/order_controller.dart';
import 'package:sixam_mart/helper/address_helper.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';
import 'package:sixam_mart/helper/route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/images.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/features/checkout/widgets/payment_failed_dialog.dart';

import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/helper/repeat_order_helper.dart';
import 'package:sixam_mart/features/checkout/widgets/reorder_dialog.dart';
import 'package:sixam_mart/features/auth/widgets/auth_dialog_widget.dart';
import 'package:sixam_mart/common/controllers/theme_controller.dart';
import 'package:sixam_mart/features/auth/controllers/auth_controller.dart';
import 'package:sixam_mart/helper/auth_helper.dart';

class OrderSuccessfulDialog extends StatefulWidget {
  final String? orderID;
  final String? contactPersonNumber;
  final bool? createAccount;
  final String guestId;
  const OrderSuccessfulDialog(
      {super.key,
      required this.orderID,
      this.contactPersonNumber,
      this.createAccount = false,
      required this.guestId});

  @override
  State<OrderSuccessfulDialog> createState() => _OrderSuccessfulDialogState();
}

class _OrderSuccessfulDialogState extends State<OrderSuccessfulDialog> {
  bool? _isCashOnDeliveryActive = false;
  String? orderId;

  @override
  void initState() {
    super.initState();

    orderId = widget.orderID!;
    if (widget.orderID != null) {
      if (widget.orderID!.contains('?')) {
        var parts = widget.orderID!.split('?');
        String id = parts[0].trim();
        orderId = id;
      }
    }

    _loadOrderData();
  }

  Future<void> _loadOrderData() async {
    await Get.find<OrderController>().trackOrder(
        orderId.toString(), null, false,
        contactNumber: widget.contactPersonNumber);
    // Wait a bit for track order to complete, then load order details
    await Future.delayed(const Duration(milliseconds: 500));
    await Get.find<OrderController>().getOrderDetails(orderId.toString());
  }

  void _reorderLastOrder() {
    if (Get.find<OrderController>().orderDetails != null &&
        Get.find<OrderController>().orderDetails!.isNotEmpty) {
      Get.dialog(
        ReorderDialog(
          orderDetails: Get.find<OrderController>().orderDetails!,
        ),
        barrierDismissible: true,
      );
    } else {
      RepeatOrderHelper.reorderLastOrder();
    }
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        await Get.offAllNamed(RouteHelper.getInitialRoute());
      },
      child: Dialog(
        shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(Dimensions.radiusSmall)),
        insetPadding: const EdgeInsets.all(30),
        clipBehavior: Clip.antiAliasWithSaveLayer,
        child: GetBuilder<OrderController>(builder: (orderController) {
          double total = 0;
          bool success = true;
          bool parcel = false;
          double? maximumCodOrderAmount;
          if (orderController.trackModel != null) {
            total = ((orderController.trackModel!.orderAmount! / 100) *
                Get.find<SplashController>()
                    .configModel!
                    .loyaltyPointItemPurchasePoint!);
            success = orderController.trackModel!.paymentStatus == 'paid' ||
                orderController.trackModel!.paymentMethod ==
                    'cash_on_delivery' ||
                orderController.trackModel!.paymentMethod ==
                    'partial_payment' ||
                orderController.trackModel!.paymentMethod == 'postpaid';
            parcel = orderController.trackModel!.paymentMethod == 'parcel';
            for (ZoneData zData
                in AddressHelper.getUserAddressFromSharedPref()!.zoneData!) {
              for (Modules m in zData.modules!) {
                if (m.id == Get.find<SplashController>().module!.id) {
                  maximumCodOrderAmount = m.pivot!.maximumCodOrderAmount;
                  break;
                }
              }
              if (zData.id ==
                  AddressHelper.getUserAddressFromSharedPref()!.zoneId) {
                _isCashOnDeliveryActive = zData.cashOnDelivery;
              }
            }

            if (!success &&
                !Get.isDialogOpen! &&
                orderController.trackModel!.orderStatus != 'canceled') {
              Future.delayed(const Duration(seconds: 1), () {
                Get.dialog(
                    PaymentFailedDialog(
                      orderID: orderId,
                      isCashOnDelivery: _isCashOnDeliveryActive,
                      orderAmount: total,
                      maxCodOrderAmount: maximumCodOrderAmount,
                      orderType: parcel ? 'parcel' : 'delivery',
                      guestId: widget.guestId,
                    ),
                    barrierDismissible: false);
              });
            }

            // Store order for future repeat functionality
            if (success &&
                !parcel &&
                orderController.orderDetails != null &&
                orderController.orderDetails!.isNotEmpty) {
              RepeatOrderHelper.storeLastOrder(
                  orderId!, orderController.orderDetails!);
            }
          }

          return orderController.trackModel != null
              ? Container(
                  width: 500,
                  padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
                  child: Column(mainAxisSize: MainAxisSize.min, children: [
                    Padding(
                      padding:
                          const EdgeInsets.all(Dimensions.paddingSizeLarge),
                      child: Image.asset(
                          success ? Images.checked : Images.warning,
                          width: 100,
                          height: 100),
                    ),
                    Text(
                      success
                          ? parcel
                              ? 'you_placed_the_parcel_request_successfully'.tr
                              : 'you_placed_the_order_successfully'.tr
                          : 'your_order_is_failed_to_place'.tr,
                      style: robotoMedium.copyWith(
                          fontSize: Dimensions.fontSizeLarge),
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: Dimensions.paddingSizeSmall),
                    widget.createAccount!
                        ? Padding(
                            padding: const EdgeInsets.only(
                                bottom: Dimensions.paddingSizeSmall),
                            child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  Text(
                                    'and_create_account_successfully'.tr,
                                    style: robotoMedium,
                                  ),
                                  InkWell(
                                    onTap: () {
                                      Get.dialog(const Center(
                                          child: AuthDialogWidget(
                                              exitFromApp: false,
                                              backFromThis: false)));
                                    },
                                    child: Padding(
                                      padding: const EdgeInsets.all(
                                          Dimensions.paddingSizeExtraSmall),
                                      child: Text('sign_in'.tr,
                                          style: robotoMedium.copyWith(
                                              color: Theme.of(context)
                                                  .primaryColor)),
                                    ),
                                  ),
                                ]),
                          )
                        : const SizedBox(),
                    AuthHelper.isGuestLoggedIn()
                        ? SelectableText(
                            '${'order_id'.tr}: $orderId',
                            style: robotoMedium.copyWith(
                                fontSize: Dimensions.fontSizeLarge,
                                color: Theme.of(context).primaryColor),
                          )
                        : const SizedBox(),
                    Padding(
                      padding: const EdgeInsets.symmetric(
                          horizontal: Dimensions.paddingSizeLarge,
                          vertical: Dimensions.paddingSizeSmall),
                      child: Text(
                        success
                            ? parcel
                                ? 'your_parcel_request_is_placed_successfully'
                                    .tr
                                : 'your_order_is_placed_successfully'.tr
                            : 'your_order_is_failed_to_place_because'.tr,
                        style: robotoMedium.copyWith(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Theme.of(context).disabledColor),
                        textAlign: TextAlign.center,
                      ),
                    ),
                    (success &&
                                Get.find<SplashController>()
                                        .configModel!
                                        .loyaltyPointStatus ==
                                    1 &&
                                total.floor() > 0) &&
                            AuthHelper.isLoggedIn()
                        ? Column(children: [
                            Image.asset(
                                Get.find<ThemeController>().darkTheme
                                    ? Images.congratulationDark
                                    : Images.congratulationLight,
                                width: 100,
                                height: 100),
                            Text('congratulations'.tr,
                                style: robotoMedium.copyWith(
                                    fontSize: Dimensions.fontSizeLarge)),
                            const SizedBox(height: Dimensions.paddingSizeSmall),
                            Padding(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: Dimensions.paddingSizeLarge),
                              child: Text(
                                '${'you_have_earned'.tr} ${total.floor().toString()} ${'points_it_will_add_to'.tr}',
                                style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeLarge,
                                    color: Theme.of(context).disabledColor),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ])
                        : const SizedBox.shrink(),
                    const SizedBox(height: 30),
                    if (success && !parcel) ...[
                      Padding(
                        padding: const EdgeInsets.symmetric(
                            horizontal: Dimensions.paddingSizeSmall),
                        child: CustomButton(
                          buttonText: 'repeat_order'.tr,
                          onPressed: _reorderLastOrder,
                          color:
                              Theme.of(context).primaryColor.withOpacity(0.1),
                          textColor: Theme.of(context).primaryColor,
                        ),
                      ),
                      const SizedBox(height: Dimensions.paddingSizeSmall),
                    ],
                    CustomButton(
                        buttonText: 'back_to_home'.tr,
                        onPressed: () {
                          if (AuthHelper.isLoggedIn()) {
                            Get.find<AuthController>()
                                .saveEarningPoint(total.toStringAsFixed(0));
                          }
                          Get.offAllNamed(RouteHelper.getInitialRoute());
                        }),
                  ]),
                )
              : const Center(child: CircularProgressIndicator());
        }),
      ),
    );
  }
}
