import 'package:flutter_slidable/flutter_slidable.dart';
import 'package:sixam_mart/common/widgets/custom_asset_image_widget.dart';
import 'package:sixam_mart/common/widgets/custom_ink_well.dart';
import 'package:sixam_mart/features/cart/controllers/cart_controller.dart';
import 'package:sixam_mart/features/language/controllers/language_controller.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/images.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/common/widgets/item_bottom_sheet.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';

class CartItemWidget extends StatelessWidget {
  final CartModel cart;
  final int cartIndex;
  final List<AddOns> addOns;
  final bool isAvailable;
  const CartItemWidget(
      {super.key,
      required this.cart,
      required this.cartIndex,
      required this.isAvailable,
      required this.addOns});

  @override
  Widget build(BuildContext context) {
    double? startingPrice = _calculatePriceWithVariation(item: cart.item);
    double? endingPrice =
        _calculatePriceWithVariation(item: cart.item, isStartingPrice: false);
    String? variationText = _setupVariationText(cart: cart);
    String addOnText = _setupAddonsText(cart: cart) ?? '';

    double? discount = cart.item!.storeDiscount == 0
        ? cart.item!.discount
        : cart.item!.storeDiscount;
    String? discountType =
        cart.item!.storeDiscount == 0 ? cart.item!.discountType : 'percent';
    String genericName = '';

    if (cart.item!.genericName != null && cart.item!.genericName!.isNotEmpty) {
      for (String name in cart.item!.genericName!) {
        genericName += name;
      }
    }

    return Padding(
      padding: const EdgeInsets.only(bottom: Dimensions.paddingSizeDefault),
      child: Slidable(
        key: UniqueKey(),
        endActionPane: ActionPane(
          motion: const ScrollMotion(),
          extentRatio: 0.25,
          children: [
            SlidableAction(
              onPressed: (context) {
                Get.dialog(
                  AlertDialog(
                    title: Text('delete'.tr),
                    content: Text('you_want_to_delete_this_location'.tr),
                    actions: [
                      TextButton(
                        onPressed: () => Get.back(),
                        child: Text('cancel'.tr),
                      ),
                      TextButton(
                        onPressed: () {
                          Get.back();
                          Get.find<CartController>()
                              .removeFromCart(cartIndex, item: cart.item);
                        },
                        style: TextButton.styleFrom(
                          foregroundColor: Theme.of(context).colorScheme.error,
                        ),
                        child: Text('delete'.tr),
                      ),
                    ],
                  ),
                );
              },
              backgroundColor: Theme.of(context).colorScheme.error,
              borderRadius: BorderRadius.horizontal(
                  right: Radius.circular(
                      Get.find<LocalizationController>().isLtr
                          ? Dimensions.radiusDefault
                          : 0),
                  left: Radius.circular(Get.find<LocalizationController>().isLtr
                      ? 0
                      : Dimensions.radiusDefault)),
              foregroundColor: Colors.white,
              icon: Icons.delete_outline,
              label: 'delete'.tr,
            ),
          ],
        ),
        child: Container(
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
            boxShadow: !ResponsiveHelper.isMobile(context)
                ? [const BoxShadow()]
                : [
                    const BoxShadow(
                      color: Colors.black12,
                      blurRadius: 5,
                      spreadRadius: 1,
                    )
                  ],
          ),
          child: CustomInkWell(
            onTap: () {
              ResponsiveHelper.isMobile(context)
                  ? showModalBottomSheet(
                      context: context,
                      isScrollControlled: true,
                      backgroundColor: Colors.transparent,
                      builder: (con) => ItemBottomSheet(
                          item: cart.item, cartIndex: cartIndex, cart: cart),
                    )
                  : showDialog(
                      context: context,
                      builder: (con) => Dialog(
                            child: ItemBottomSheet(
                                item: cart.item,
                                cartIndex: cartIndex,
                                cart: cart),
                          ));
            },
            radius: Dimensions.radiusDefault,
            padding: const EdgeInsets.symmetric(
                vertical: Dimensions.paddingSizeExtraSmall,
                horizontal: Dimensions.paddingSizeExtraSmall),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Stack(
                      children: [
                        ClipRRect(
                          borderRadius:
                              BorderRadius.circular(Dimensions.radiusDefault),
                          child: CustomImage(
                            image: '${cart.item!.imageFullUrl}',
                            height:
                                ResponsiveHelper.isDesktop(context) ? 90 : 60,
                            width:
                                ResponsiveHelper.isDesktop(context) ? 90 : 60,
                            fit: BoxFit.cover,
                          ),
                        ),
                        isAvailable
                            ? const SizedBox()
                            : Positioned(
                                top: 0,
                                left: 0,
                                bottom: 0,
                                right: 0,
                                child: Container(
                                  alignment: Alignment.center,
                                  decoration: BoxDecoration(
                                      borderRadius: BorderRadius.circular(
                                          Dimensions.radiusSmall),
                                      color: Colors.black.withOpacity(0.6)),
                                  child: Text('not_available_now_break'.tr,
                                      textAlign: TextAlign.center,
                                      style: robotoRegular.copyWith(
                                        color: Colors.white,
                                        fontSize: 8,
                                      )),
                                ),
                              ),
                      ],
                    ),
                    const SizedBox(width: Dimensions.paddingSizeSmall),
                    Expanded(
                      child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Row(children: [
                              Flexible(
                                child: Text(
                                  cart.item!.name!,
                                  style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeSmall),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                              const SizedBox(
                                  width: Dimensions.paddingSizeExtraSmall),
                              // Edit button
                              GestureDetector(
                                onTap: () {
                                  ResponsiveHelper.isMobile(context)
                                      ? showModalBottomSheet(
                                          context: context,
                                          isScrollControlled: true,
                                          backgroundColor: Colors.transparent,
                                          builder: (con) => ItemBottomSheet(
                                              item: cart.item,
                                              cartIndex: cartIndex,
                                              cart: cart),
                                        )
                                      : showDialog(
                                          context: context,
                                          builder: (con) => Dialog(
                                                child: ItemBottomSheet(
                                                    item: cart.item,
                                                    cartIndex: cartIndex,
                                                    cart: cart),
                                              ));
                                },
                                child: Container(
                                  padding: const EdgeInsets.all(4),
                                  decoration: BoxDecoration(
                                    color: Theme.of(context)
                                        .primaryColor
                                        .withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(
                                    Icons.edit_outlined,
                                    size: 16,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                              ),
                              const SizedBox(
                                  width: Dimensions.paddingSizeExtraSmall),
                              // Delete button
                              GestureDetector(
                                onTap: () {
                                  Get.dialog(
                                    AlertDialog(
                                      title: Text('delete'.tr),
                                      content: Text(
                                          'you_want_to_delete_this_location'
                                              .tr),
                                      actions: [
                                        TextButton(
                                          onPressed: () => Get.back(),
                                          child: Text('cancel'.tr),
                                        ),
                                        TextButton(
                                          onPressed: () {
                                            Get.back();
                                            Get.find<CartController>()
                                                .removeFromCart(cartIndex,
                                                    item: cart.item);
                                          },
                                          style: TextButton.styleFrom(
                                            foregroundColor: Theme.of(context)
                                                .colorScheme
                                                .error,
                                          ),
                                          child: Text('delete'.tr),
                                        ),
                                      ],
                                    ),
                                  );
                                },
                                child: Container(
                                  padding: const EdgeInsets.all(4),
                                  decoration: BoxDecoration(
                                    color: Theme.of(context)
                                        .colorScheme
                                        .error
                                        .withOpacity(0.1),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Icon(
                                    Icons.delete_outline,
                                    size: 16,
                                    color: Theme.of(context).colorScheme.error,
                                  ),
                                ),
                              ),
                              const SizedBox(
                                  width: Dimensions.paddingSizeExtraSmall),
                              ((Get.find<SplashController>()
                                              .configModel!
                                              .moduleConfig!
                                              .module!
                                              .unit! &&
                                          cart.item!.unitType != null &&
                                          !Get.find<SplashController>()
                                              .getModuleConfig(
                                                  cart.item!.moduleType)
                                              .newVariation!) ||
                                      (Get.find<SplashController>()
                                              .configModel!
                                              .moduleConfig!
                                              .module!
                                              .vegNonVeg! &&
                                          Get.find<SplashController>()
                                              .configModel!
                                              .toggleVegNonVeg!))
                                  ? !Get.find<SplashController>()
                                          .configModel!
                                          .moduleConfig!
                                          .module!
                                          .unit!
                                      ? CustomAssetImageWidget(
                                          cart.item!.veg == 0
                                              ? Images.nonVegImage
                                              : Images.vegImage,
                                          height: 11,
                                          width: 11,
                                        )
                                      : Container(
                                          padding: const EdgeInsets.symmetric(
                                              vertical: Dimensions
                                                  .paddingSizeExtraSmall,
                                              horizontal:
                                                  Dimensions.paddingSizeSmall),
                                          decoration: BoxDecoration(
                                            borderRadius: BorderRadius.circular(
                                                Dimensions.radiusSmall),
                                            color: Theme.of(context)
                                                .primaryColor
                                                .withOpacity(0.1),
                                          ),
                                          child: Text(
                                            cart.item!.unitType ?? '',
                                            style: robotoMedium.copyWith(
                                                fontSize: Dimensions
                                                    .fontSizeExtraSmall,
                                                color: Theme.of(context)
                                                    .primaryColor),
                                          ),
                                        )
                                  : const SizedBox(),
                              SizedBox(
                                  width: cart.item!.isStoreHalalActive! &&
                                          cart.item!.isHalalItem!
                                      ? Dimensions.paddingSizeExtraSmall
                                      : 0),
                              cart.item!.isStoreHalalActive! &&
                                      cart.item!.isHalalItem!
                                  ? const CustomAssetImageWidget(
                                      Images.halalTag,
                                      height: 13,
                                      width: 13)
                                  : const SizedBox(),
                            ]),
                            (genericName.isNotEmpty)
                                ? Padding(
                                    padding: const EdgeInsets.only(top: 2.0),
                                    child: Row(children: [
                                      Flexible(
                                        child: Text(
                                          genericName,
                                          style: robotoMedium.copyWith(
                                            fontSize: Dimensions.fontSizeSmall,
                                            color:
                                                Theme.of(context).disabledColor,
                                          ),
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ),
                                    ]),
                                  )
                                : const SizedBox(),
                            const SizedBox(height: 2),
                            Wrap(children: [
                              Text(
                                '${PriceConverter.convertPrice(startingPrice, discount: discount, discountType: discountType, itemId: cart.item!.id)}'
                                '${endingPrice != null ? ' - ${PriceConverter.convertPrice(endingPrice, discount: discount, discountType: discountType, itemId: cart.item!.id)}' : ''}',
                                style: robotoBold.copyWith(
                                    fontSize: Dimensions.fontSizeSmall),
                                textDirection: TextDirection.ltr,
                              ),
                              SizedBox(
                                  width: discount! > 0
                                      ? Dimensions.paddingSizeExtraSmall
                                      : 0),
                              discount > 0
                                  ? Text(
                                      '${PriceConverter.convertPrice(startingPrice, itemId: cart.item!.id)}'
                                      '${endingPrice != null ? ' - ${PriceConverter.convertPrice(endingPrice, itemId: cart.item!.id)}' : ''}',
                                      textDirection: TextDirection.ltr,
                                      style: robotoRegular.copyWith(
                                        color: Theme.of(context).disabledColor,
                                        decoration: TextDecoration.lineThrough,
                                        fontSize: Dimensions.fontSizeExtraSmall,
                                      ),
                                    )
                                  : const SizedBox(),
                            ]),
                            cart.item!.isPrescriptionRequired!
                                ? Padding(
                                    padding: EdgeInsets.symmetric(
                                        vertical:
                                            ResponsiveHelper.isDesktop(context)
                                                ? Dimensions
                                                    .paddingSizeExtraSmall
                                                : 2),
                                    child: Text(
                                      '* ${'prescription_required'.tr}',
                                      style: robotoRegular.copyWith(
                                          fontSize:
                                              Dimensions.fontSizeExtraSmall,
                                          color: Theme.of(context)
                                              .colorScheme
                                              .error),
                                    ),
                                  )
                                : const SizedBox(),
                            addOnText.isNotEmpty
                                ? Padding(
                                    padding: const EdgeInsets.only(
                                        top: Dimensions.paddingSizeExtraSmall),
                                    child: Row(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text('${'addons'.tr}: ',
                                              style: robotoMedium.copyWith(
                                                  fontSize: Dimensions
                                                      .fontSizeSmall)),
                                          Flexible(
                                              child: Text(
                                            addOnText,
                                            style: robotoRegular.copyWith(
                                                fontSize:
                                                    Dimensions.fontSizeSmall,
                                                color: Theme.of(context)
                                                    .disabledColor),
                                          )),
                                        ]),
                                  )
                                : const SizedBox(),
                            variationText!.isNotEmpty
                                ? Padding(
                                    padding: const EdgeInsets.only(
                                        top: Dimensions.paddingSizeExtraSmall),
                                    child: Row(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text('${'variations'.tr}: ',
                                              style: robotoMedium.copyWith(
                                                  fontSize: Dimensions
                                                      .fontSizeSmall)),
                                          Flexible(
                                              child: Text(
                                            variationText,
                                            style: robotoRegular.copyWith(
                                                fontSize:
                                                    Dimensions.fontSizeSmall,
                                                color: Theme.of(context)
                                                    .disabledColor),
                                          )),
                                        ]),
                                  )
                                : const SizedBox(),
                          ]),
                    ),
                    GetBuilder<CartController>(builder: (cartController) {
                      return Padding(
                        padding: const EdgeInsets.only(
                            top: Dimensions.paddingSizeDefault + 2),
                        child: Container(
                          padding: const EdgeInsets.symmetric(
                            horizontal: Dimensions.paddingSizeSmall,
                            vertical: Dimensions.paddingSizeExtraSmall,
                          ),
                          decoration: BoxDecoration(
                            border: Border.all(
                              color: Theme.of(context)
                                  .disabledColor
                                  .withOpacity(0.3),
                              width: 1,
                            ),
                            borderRadius:
                                BorderRadius.circular(Dimensions.radiusSmall),
                          ),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Text(
                                cart.quantity?.toStringAsFixed(1) ?? '0.0',
                                style: robotoMedium.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                ),
                              ),
                              const SizedBox(width: 4),
                              Text(
                                'kg',
                                style: robotoMedium.copyWith(
                                  fontSize: Dimensions.fontSizeDefault,
                                ),
                              ),
                            ],
                          ),
                        ),
                      );
                    }),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  double? _calculatePriceWithVariation(
      {required Item? item, bool isStartingPrice = true}) {
    double? startingPrice;
    double? endingPrice;
    bool newVariation = Get.find<SplashController>()
            .getModuleConfig(item!.moduleType)
            .newVariation ??
        false;

    if (item.variations!.isNotEmpty && !newVariation) {
      List<double?> priceList = [];
      for (var variation in item.variations!) {
        double variationPrice = variation.price!;
        if (B2BPricingHelper.shouldUseB2BPricing()) {
          final b2bVariationPrice =
              B2BPricingHelper.getB2BContractVariationPrice(
                  item.id!, variation.type!);
          if (b2bVariationPrice != null) {
            variationPrice = b2bVariationPrice;
          }
        }
        priceList.add(variationPrice);
      }
      priceList.sort((a, b) => a!.compareTo(b!));
      startingPrice = priceList[0];
      if (priceList[0]! < priceList[priceList.length - 1]!) {
        endingPrice = priceList[priceList.length - 1];
      }
    } else {
      double basePrice = item.price!;
      if (B2BPricingHelper.shouldUseB2BPricing()) {
        final b2bPrice = B2BPricingHelper.getB2BContractPrice(item.id!);
        if (b2bPrice != null) {
          basePrice = b2bPrice;
        }
      }
      startingPrice = basePrice;
    }
    if (isStartingPrice) {
      return startingPrice;
    } else {
      return endingPrice;
    }
  }

  String? _setupVariationText({required CartModel cart}) {
    String? variationText = '';

    if (Get.find<SplashController>()
        .getModuleConfig(cart.item!.moduleType)
        .newVariation!) {
      if (cart.foodVariations!.isNotEmpty) {
        for (int index = 0; index < cart.foodVariations!.length; index++) {
          if (cart.foodVariations![index].contains(true)) {
            variationText =
                '${variationText!}${variationText.isNotEmpty ? ', ' : ''}${cart.item!.foodVariations![index].name} (';
            for (int i = 0; i < cart.foodVariations![index].length; i++) {
              if (cart.foodVariations![index][i]!) {
                variationText =
                    '${variationText!}${variationText.endsWith('(') ? '' : ', '}${cart.item!.foodVariations![index].variationValues![i].level}';
              }
            }
            variationText = '${variationText!})';
          }
        }
      }
    } else {
      if (cart.variation!.isNotEmpty) {
        List<String> variationTypes = cart.variation![0].type!.split('-');
        if (variationTypes.length == cart.item!.choiceOptions!.length) {
          int index0 = 0;
          for (var choice in cart.item!.choiceOptions!) {
            variationText =
                '${variationText!}${(index0 == 0) ? '' : ',  '}${choice.title} - ${variationTypes[index0]}';
            index0 = index0 + 1;
          }
        } else {
          variationText = cart.item!.variations![0].type;
        }
      }
    }
    return variationText;
  }

  String? _setupAddonsText({required CartModel cart}) {
    String addOnText = '';
    int index0 = 0;
    List<int?> ids = [];
    List<double> qtys = [];
    for (var addOn in cart.addOnIds!) {
      ids.add(addOn.id);
      qtys.add(addOn.quantity ?? 0.0);
    }
    for (var addOn in cart.item!.addOns!) {
      if (ids.contains(addOn.id)) {
        addOnText =
            '$addOnText${(index0 == 0) ? '' : ',  '}${addOn.name} (${qtys[index0]})';
        index0 = index0 + 1;
      }
    }
    return addOnText;
  }
}
