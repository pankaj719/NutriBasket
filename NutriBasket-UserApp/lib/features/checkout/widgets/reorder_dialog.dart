import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/cart/controllers/cart_controller.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart'
    hide AddOn;
import 'package:sixam_mart/features/order/controllers/order_controller.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/features/checkout/domain/models/place_order_body_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/helper/route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/images.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';

class ReorderDialog extends StatefulWidget {
  final List<OrderDetailsModel> orderDetails;
  const ReorderDialog({super.key, required this.orderDetails});

  @override
  State<ReorderDialog> createState() => _ReorderDialogState();
}

class _ReorderDialogState extends State<ReorderDialog> {
  bool _isLoading = false;

  @override
  Widget build(BuildContext context) {
    return Dialog(
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
      ),
      child: Container(
        width: 400,
        padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Icon and Title
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
              decoration: BoxDecoration(
                color: Theme.of(context).primaryColor.withOpacity(0.1),
                shape: BoxShape.circle,
              ),
              child: Icon(
                Icons.replay,
                size: 40,
                color: Theme.of(context).primaryColor,
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),

            Text(
              'repeat_your_order'.tr,
              style: robotoBold.copyWith(
                fontSize: Dimensions.fontSizeLarge,
                color: Theme.of(context).primaryColor,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: Dimensions.paddingSizeSmall),

            Text(
              'add_same_items_to_cart'.tr,
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Theme.of(context).disabledColor,
              ),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),

            // Order Summary
            Container(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              decoration: BoxDecoration(
                color: Theme.of(context).cardColor,
                borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                border: Border.all(
                  color: Theme.of(context).disabledColor.withOpacity(0.3),
                ),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'order_summary'.tr,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                    ),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  ...widget.orderDetails.take(3).map((item) => Padding(
                        padding: const EdgeInsets.only(bottom: 4),
                        child: Row(
                          children: [
                            Expanded(
                              child: Text(
                                '${item.itemDetails?.name ?? ''} x${item.quantity}',
                                style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeSmall,
                                ),
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          ],
                        ),
                      )),
                  if (widget.orderDetails.length > 3)
                    Text(
                      'and_more_items'.tr.replaceAll(
                          '{count}', '${widget.orderDetails.length - 3}'),
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Theme.of(context).disabledColor,
                      ),
                    ),
                ],
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeLarge),

            // Buttons
            Row(
              children: [
                Expanded(
                  child: TextButton(
                    onPressed: () => Get.back(),
                    style: TextButton.styleFrom(
                      padding: const EdgeInsets.symmetric(
                        vertical: Dimensions.paddingSizeDefault,
                      ),
                      shape: RoundedRectangleBorder(
                        borderRadius:
                            BorderRadius.circular(Dimensions.radiusSmall),
                        side: BorderSide(
                          color: Theme.of(context).disabledColor,
                        ),
                      ),
                    ),
                    child: Text(
                      'not_now'.tr,
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).disabledColor,
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: CustomButton(
                    buttonText: _isLoading ? 'adding'.tr : 'repeat_order'.tr,
                    onPressed: _isLoading ? null : _reorderItems,
                    height: 45,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Future<void> _reorderItems() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final cartController = Get.find<CartController>();

      // Clear existing cart first
      await cartController.clearCartOnline();

      // Add each item from the order to cart
      for (OrderDetailsModel orderDetail in widget.orderDetails) {
        if (orderDetail.itemDetails != null) {
          // Prepare add-on IDs and quantities
          List<int?> addOnIds = [];
          List<double?> addOnQtys = [];
          List<AddOns> addOnsList = [];

          if (orderDetail.addOns != null) {
            for (var addOn in orderDetail.addOns!) {
              // Find the add-on ID from the item details
              AddOns? addOnFromItem;
              try {
                addOnFromItem = orderDetail.itemDetails!.addOns?.firstWhere(
                  (itemAddOn) => itemAddOn.name == addOn.name,
                );
              } catch (e) {
                addOnFromItem = null;
              }
              if (addOnFromItem != null) {
                addOnIds.add(addOnFromItem.id);
                addOnQtys.add(addOn.quantity?.toDouble() ?? 0.0);
                addOnsList.add(addOnFromItem);
              }
            }
          }

          // Create CartModel object
          CartModel cartModel = CartModel(
            null, // id
            orderDetail.price,
            orderDetail.price! - (orderDetail.discountOnItem ?? 0),
            orderDetail.variation ?? [],
            [], // foodVariations - empty for now
            orderDetail.discountOnItem ?? 0,
            orderDetail.quantity!.toDouble(),
            [], // addOnIds - empty for now
            addOnsList,
            orderDetail.itemCampaignId != null,
            orderDetail.itemDetails?.stock ?? 0,
            orderDetail.itemDetails,
            orderDetail.itemDetails?.quantityLimit,
          );

          // Add to cart
          await cartController.addToCartOnline(cartModel);
        }
      }

      Get.back(); // Close the dialog

      // Show success message
      showCustomSnackBar('items_added_to_cart_successfully'.tr);

      // Navigate to cart or checkout
      Get.toNamed(RouteHelper.getCartRoute());
    } catch (e) {
      showCustomSnackBar('failed_to_add_items_to_cart'.tr);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  List<OrderVariation> _convertToOrderVariations(
      List<FoodVariation> foodVariations) {
    List<OrderVariation> orderVariations = [];

    for (FoodVariation variation in foodVariations) {
      List<String> labels = [];
      if (variation.variationValues != null) {
        for (VariationValue value in variation.variationValues!) {
          labels.add(value.level ?? '');
        }
      }

      orderVariations.add(OrderVariation(
        name: variation.name,
        values: OrderVariationValue(label: labels),
      ));
    }

    return orderVariations;
  }
}
