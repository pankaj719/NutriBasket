import 'package:sixam_mart_delivery/features/order/widgets/edit_quantity_dialog.dart';
import 'package:sixam_mart_delivery/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart_delivery/features/order/domain/models/order_model.dart';
import 'package:sixam_mart_delivery/helper/price_converter_helper.dart';
import 'package:sixam_mart_delivery/util/dimensions.dart';
import 'package:sixam_mart_delivery/util/styles.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_image_widget.dart';
import 'package:sixam_mart_delivery/features/order/controllers/order_controller.dart';

class OrderItemWidget extends StatelessWidget {
  final OrderModel order;
  final OrderDetailsModel orderDetails;
  const OrderItemWidget(
      {super.key, required this.order, required this.orderDetails});

  @override
  Widget build(BuildContext context) {
    String addOnText = '';
    for (var addOn in orderDetails.addOns!) {
      addOnText =
          '$addOnText${(addOnText.isEmpty) ? '' : ',  '}${addOn.name} (${addOn.quantity!.toStringAsFixed(1)})';
    }

    String? variationText = '';
    if (orderDetails.variation!.isNotEmpty) {
      List<String> variationTypes = orderDetails.variation![0].type!.split('-');
      if (variationTypes.length ==
          orderDetails.itemDetails!.choiceOptions!.length) {
        int index = 0;
        for (var choice in orderDetails.itemDetails!.choiceOptions!) {
          variationText =
              '${variationText!}${(index == 0) ? '' : ',  '}${choice.title} - ${variationTypes[index]}';
          index = index + 1;
        }
      } else {
        variationText = orderDetails.itemDetails!.variations![0].type;
      }
    } else if (orderDetails.foodVariation!.isNotEmpty) {
      for (FoodVariation variation in orderDetails.foodVariation!) {
        variationText =
            '${variationText!}${variationText.isNotEmpty ? ', ' : ''}${variation.name} (';
        for (VariationValue value in variation.variationValues!) {
          variationText =
              '${variationText!}${variationText.endsWith('(') ? '' : ', '}${value.level}';
        }
        variationText = '${variationText!})';
      }
    }

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          orderDetails.itemDetails!.imageFullUrl != null
              ? ClipRRect(
                  borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                  child: CustomImageWidget(
                    height: 50,
                    width: 50,
                    fit: BoxFit.cover,
                    image: '${orderDetails.itemDetails!.imageFullUrl}',
                  ),
                )
              : const SizedBox(width: 50, height: 50),
          const SizedBox(width: Dimensions.paddingSizeSmall),
          // Item info and price
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Name and quantity row
                Row(
                  children: [
                    Expanded(
                      child: Text(
                        orderDetails.itemDetails!.name!,
                        style: robotoMedium.copyWith(
                            fontSize: Dimensions.fontSizeSmall),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    Text(
                      '${'quantity'.tr}:',
                      style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall),
                    ),
                    Text(
                      orderDetails.quantity!.toStringAsFixed(1),
                      style: robotoMedium.copyWith(
                        color: Theme.of(context).primaryColor,
                        fontSize: Dimensions.fontSizeSmall,
                      ),
                    ),
                    IconButton(
                      icon: Icon(Icons.edit, size: 18),
                      padding: EdgeInsets.zero,
                      constraints: BoxConstraints(),
                      onPressed: () {
                        showDialog(
                          context: context,
                          builder: (context) => EditQuantityDialog(
                            item: orderDetails,
                            onSubmit: (newQty, reason) {
                              Get.find<OrderController>().requestQuantityChange(
                                orderId: order.id.toString(),
                                itemId: orderDetails.id.toString(),
                                newQuantity: newQty,
                                reason: reason,
                              );
                            },
                          ),
                        );
                      },
                    ),
                  ],
                ),
                const SizedBox(height: Dimensions.paddingSizeExtraSmall),
                // Price, discount, unit/veg row
                Row(
                  children: [
                    Text(
                      PriceConverterHelper.convertPrice(
                          orderDetails.price! - orderDetails.discountOnItem!),
                      style: robotoMedium,
                    ),
                    const SizedBox(width: 5),
                    orderDetails.discountOnItem! > 0
                        ? Text(
                            PriceConverterHelper.convertPrice(
                                orderDetails.price),
                            style: robotoMedium.copyWith(
                              decoration: TextDecoration.lineThrough,
                              fontSize: Dimensions.fontSizeSmall,
                              color: Theme.of(context).disabledColor,
                            ),
                          )
                        : const SizedBox(),
                    const SizedBox(width: 8),
                    ((Get.find<SplashController>()
                                    .getModule(order.moduleType)
                                    .unit! &&
                                orderDetails.itemDetails!.unitType != null) ||
                            (Get.find<SplashController>()
                                    .configModel!
                                    .toggleVegNonVeg! &&
                                Get.find<SplashController>()
                                    .getModule(order.moduleType)
                                    .vegNonVeg!))
                        ? Container(
                            padding: const EdgeInsets.symmetric(
                                vertical: Dimensions.paddingSizeExtraSmall,
                                horizontal: Dimensions.paddingSizeSmall),
                            decoration: BoxDecoration(
                              borderRadius:
                                  BorderRadius.circular(Dimensions.radiusSmall),
                              color: Theme.of(context)
                                  .primaryColor
                                  .withOpacity(0.1),
                            ),
                            child: Text(
                              Get.find<SplashController>()
                                      .getModule(order.moduleType)
                                      .unit!
                                  ? orderDetails.itemDetails!.unitType ?? ''
                                  : orderDetails.itemDetails!.veg == 0
                                      ? 'non_veg'.tr
                                      : 'veg'.tr,
                              style: robotoRegular.copyWith(
                                  fontSize: Dimensions.fontSizeExtraSmall,
                                  color: Theme.of(context).primaryColor),
                            ),
                          )
                        : const SizedBox(),
                  ],
                ),
                // Addons
                if (Get.find<SplashController>()
                        .getModule(order.moduleType)
                        .addOn! &&
                    addOnText.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(
                        top: Dimensions.paddingSizeExtraSmall),
                    child: Row(
                      children: [
                        Text('${'addons'.tr}: ',
                            style: robotoMedium.copyWith(
                                fontSize: Dimensions.fontSizeSmall)),
                        Flexible(
                          child: Text(
                            addOnText,
                            style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor),
                          ),
                        ),
                      ],
                    ),
                  ),
                // Variations
                if (variationText!.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(
                        top: Dimensions.paddingSizeExtraSmall),
                    child: Row(
                      children: [
                        Text('${'variations'.tr}: ',
                            style: robotoMedium.copyWith(
                                fontSize: Dimensions.fontSizeSmall)),
                        Flexible(
                          child: Text(
                            variationText,
                            style: robotoRegular.copyWith(
                                fontSize: Dimensions.fontSizeSmall,
                                color: Theme.of(context).disabledColor),
                          ),
                        ),
                      ],
                    ),
                  ),
                // Change request status
                if (orderDetails.changeRequestStatus != null)
                  Padding(
                    padding: const EdgeInsets.only(top: 4.0),
                    child: Text(
                      'Change Request: ${orderDetails.changeRequestStatus}',
                      style: TextStyle(color: Colors.orange, fontSize: 12),
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
