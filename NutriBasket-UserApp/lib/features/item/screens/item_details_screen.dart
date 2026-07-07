import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/cart/controllers/cart_controller.dart';
import 'package:sixam_mart/features/item/controllers/item_controller.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/features/checkout/domain/models/place_order_body_model.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';
import 'package:sixam_mart/helper/route_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/images.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/cart_snackbar.dart';
import 'package:sixam_mart/common/widgets/confirmation_dialog.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/common/widgets/menu_drawer.dart';
import 'package:sixam_mart/features/checkout/screens/checkout_screen.dart';
import 'package:sixam_mart/features/item/widgets/details_app_bar_widget.dart';
import 'package:sixam_mart/features/item/widgets/details_web_view_widget.dart';
import 'package:sixam_mart/features/item/widgets/item_image_view_widget.dart';
import 'package:sixam_mart/features/item/widgets/item_title_view_widget.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';
import 'package:sixam_mart/common/widgets/smart_quantity_text_field.dart';

class ItemDetailsScreen extends StatefulWidget {
  final Item? item;
  final bool inStorePage;
  final bool? isCampaign;
  const ItemDetailsScreen(
      {super.key,
      required this.item,
      required this.inStorePage,
      this.isCampaign});

  @override
  State<ItemDetailsScreen> createState() => _ItemDetailsScreenState();
}

class _ItemDetailsScreenState extends State<ItemDetailsScreen> {
  final Size size = Get.size;
  final GlobalKey<ScaffoldMessengerState> _globalKey = GlobalKey();
  final GlobalKey<DetailsAppBarWidgetState> _key = GlobalKey();

  @override
  void initState() {
    super.initState();

    Get.find<ItemController>().getProductDetails(widget.item!);
    Get.find<ItemController>().setSelect(0, false);
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<CartController>(builder: (cartController) {
      return GetBuilder<ItemController>(
        builder: (itemController) {
          int? stock = 0;
          CartModel? cartModel;
          OnlineCart? cart;
          double priceWithAddons = 0;
          int? cartId = cartController.getCartId(itemController.cartIndex);
          if (itemController.item != null &&
              itemController.variationIndex != null) {
            List<String> variationList = [];
            for (int index = 0;
                index < itemController.item!.choiceOptions!.length;
                index++) {
              variationList.add(itemController.item!.choiceOptions![index]
                  .options![itemController.variationIndex![index]]
                  .replaceAll(' ', ''));
            }
            String variationType = '';
            bool isFirst = true;
            for (var variation in variationList) {
              if (isFirst) {
                variationType = '$variationType$variation';
                isFirst = false;
              } else {
                variationType = '$variationType-$variation';
              }
            }

            double? price = itemController.getStartingPrice(itemController.item!);
            Variation? variation;
            stock = itemController.item!.stock ?? 0;
            for (Variation v in itemController.item!.variations!) {
              if (v.type == variationType) {
                price = v.price;
                variation = v;
                stock = v.stock;
                break;
              }
            }

            double? discount =
                (itemController.item!.availableDateStarts != null ||
                        itemController.item!.storeDiscount == 0)
                    ? itemController.item!.discount
                    : itemController.item!.storeDiscount;
            String? discountType =
                (itemController.item!.availableDateStarts != null ||
                        itemController.item!.storeDiscount == 0)
                    ? itemController.item!.discountType
                    : 'percent';
            double priceWithDiscount = PriceConverter.convertWithDiscount(
                price, discount, discountType)!;
            double priceWithQuantity =
                priceWithDiscount * itemController.quantity!;
            double addonsCost = 0;
            List<AddOn> addOnIdList = [];
            List<AddOns> addOnsList = [];
            for (int index = 0;
                index < itemController.item!.addOns!.length;
                index++) {
              if (itemController.addOnActiveList[index]) {
                addonsCost = addonsCost +
                    (itemController.item!.addOns![index].price! *
                        itemController.addOnQtyList[index]!);
                addOnIdList.add(AddOn(
                    id: itemController.item!.addOns![index].id,
                    quantity: itemController.addOnQtyList[index]?.toDouble()));
                addOnsList.add(itemController.item!.addOns![index]);
              }
            }

            cartModel = CartModel(
                null,
                price,
                priceWithDiscount,
                variation != null ? [variation] : [],
                [],
                (price! -
                    PriceConverter.convertWithDiscount(
                        price, discount, discountType)!),
                itemController.quantity,
                addOnIdList,
                addOnsList,
                itemController.item!.availableDateStarts != null,
                stock,
                itemController.item,
                itemController.item?.quantityLimit);

            List<int?> listOfAddOnId =
                _getSelectedAddonIds(addOnIdList: addOnIdList);
            List<double> listOfAddOnQty =
                _getSelectedAddonQtnList(addOnIdList: addOnIdList);

            cart = OnlineCart(
                cartId,
                widget.item!.id,
                null,
                priceWithDiscount.toString(),
                '',
                variation != null ? [variation] : [],
                null,
                itemController.cartIndex != -1
                    ? cartController
                        .cartList[itemController.cartIndex].quantity!
                    : itemController.quantity!,
                listOfAddOnId,
                addOnsList,
                listOfAddOnQty,
                'Item',
                itemType: 'Item');
            priceWithAddons = priceWithQuantity +
                (Get.find<SplashController>()
                        .configModel!
                        .moduleConfig!
                        .module!
                        .addOn!
                    ? addonsCost
                    : 0);
          }

          return Scaffold(
            key: _globalKey,
            backgroundColor: Theme.of(context).cardColor,
            endDrawer: const MenuDrawer(),
            endDrawerEnableOpenDragGesture: false,
            appBar: ResponsiveHelper.isDesktop(context)
                ? const CustomAppBar(title: '')
                : DetailsAppBarWidget(key: _key),
            body: SafeArea(
                child: (itemController.item != null)
                    ? ResponsiveHelper.isDesktop(context)
                        ? DetailsWebViewWidget(
                            cartModel: cartModel,
                            stock: stock,
                            priceWithAddOns: priceWithAddons,
                            cart: cart,
                          )
                        : Column(children: [
                            Expanded(
                                child: SingleChildScrollView(
                                    padding: const EdgeInsets.all(
                                        Dimensions.paddingSizeSmall),
                                    physics: const BouncingScrollPhysics(),
                                    child: Center(
                                        child: SizedBox(
                                            width: Dimensions.webMaxWidth,
                                            child: Column(
                                              crossAxisAlignment:
                                                  CrossAxisAlignment.start,
                                              children: [
                                                ItemImageViewWidget(
                                                    item: itemController.item,
                                                    isCampaign:
                                                        widget.isCampaign ??
                                                            false),
                                                const SizedBox(height: 20),

                                                Builder(builder: (context) {
                                                  return ItemTitleViewWidget(
                                                    item: itemController.item,
                                                    inStorePage:
                                                        widget.inStorePage,
                                                    isCampaign: itemController
                                                            .item!
                                                            .availableDateStarts !=
                                                        null,
                                                    inStock: (Get.find<
                                                                SplashController>()
                                                            .configModel!
                                                            .moduleConfig!
                                                            .module!
                                                            .stock! &&
                                                        stock! <= 0),
                                                  );
                                                }),
                                                const Divider(
                                                    height: 20, thickness: 2),

                                                // Variation
                                                ListView.builder(
                                                  shrinkWrap: true,
                                                  itemCount: itemController
                                                      .item!
                                                      .choiceOptions!
                                                      .length,
                                                  physics:
                                                      const NeverScrollableScrollPhysics(),
                                                  itemBuilder:
                                                      (context, index) {
                                                    return Column(
                                                        crossAxisAlignment:
                                                            CrossAxisAlignment
                                                                .start,
                                                        children: [
                                                          Text(
                                                              itemController
                                                                  .item!
                                                                  .choiceOptions![
                                                                      index]
                                                                  .title!,
                                                              style: robotoMedium
                                                                  .copyWith(
                                                                      fontSize:
                                                                          Dimensions
                                                                              .fontSizeLarge)),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeExtraSmall),
                                                          GridView.builder(
                                                            gridDelegate:
                                                                const SliverGridDelegateWithFixedCrossAxisCount(
                                                              crossAxisCount: 3,
                                                              crossAxisSpacing:
                                                                  20,
                                                              mainAxisSpacing:
                                                                  10,
                                                              childAspectRatio:
                                                                  (1 / 0.25),
                                                            ),
                                                            shrinkWrap: true,
                                                            physics:
                                                                const NeverScrollableScrollPhysics(),
                                                            itemCount:
                                                                itemController
                                                                    .item!
                                                                    .choiceOptions![
                                                                        index]
                                                                    .options!
                                                                    .length,
                                                            itemBuilder:
                                                                (context, i) {
                                                              return InkWell(
                                                                onTap: () {
                                                                  itemController
                                                                      .setCartVariationIndex(
                                                                          index,
                                                                          i,
                                                                          itemController
                                                                              .item);
                                                                },
                                                                child:
                                                                    Container(
                                                                  alignment:
                                                                      Alignment
                                                                          .center,
                                                                  padding: const EdgeInsets
                                                                      .symmetric(
                                                                      horizontal:
                                                                          Dimensions
                                                                              .paddingSizeExtraSmall),
                                                                  decoration:
                                                                      BoxDecoration(
                                                                    color: itemController.variationIndex![index] !=
                                                                            i
                                                                        ? Theme.of(context)
                                                                            .disabledColor
                                                                        : Theme.of(context)
                                                                            .primaryColor,
                                                                    borderRadius:
                                                                        BorderRadius
                                                                            .circular(5),
                                                                    border: itemController.variationIndex![index] !=
                                                                            i
                                                                        ? Border.all(
                                                                            color:
                                                                                Theme.of(context).disabledColor,
                                                                            width: 2)
                                                                        : null,
                                                                  ),
                                                                  child: Text(
                                                                    itemController
                                                                        .item!
                                                                        .choiceOptions![
                                                                            index]
                                                                        .options![
                                                                            i]
                                                                        .trim(),
                                                                    maxLines: 1,
                                                                    overflow:
                                                                        TextOverflow
                                                                            .ellipsis,
                                                                    style: robotoRegular
                                                                        .copyWith(
                                                                      color: itemController.variationIndex![index] != i
                                                                          ? Colors
                                                                              .black
                                                                          : Colors
                                                                              .white,
                                                                    ),
                                                                  ),
                                                                ),
                                                              );
                                                            },
                                                          ),
                                                          SizedBox(
                                                              height: index !=
                                                                      itemController
                                                                              .item!
                                                                              .choiceOptions!
                                                                              .length -
                                                                          1
                                                                  ? Dimensions
                                                                      .paddingSizeLarge
                                                                  : 0),
                                                        ]);
                                                  },
                                                ),
                                                itemController
                                                        .item!
                                                        .choiceOptions!
                                                        .isNotEmpty
                                                    ? const SizedBox(
                                                        height: Dimensions
                                                            .paddingSizeLarge)
                                                    : const SizedBox(),

                                                // Quantity
                                                GetBuilder<CartController>(
                                                    builder: (cartController) {
                                                  return Row(children: [
                                                    Text('quantity'.tr,
                                                        style: robotoMedium.copyWith(
                                                            fontSize: Dimensions
                                                                .fontSizeLarge)),
                                                    const Expanded(
                                                        child: SizedBox()),
                                                    SmartQuantityTextField(
                                                      initialValue: itemController.cartIndex != -1
                                                          ? cartController.cartList[itemController.cartIndex].quantity
                                                          : itemController.quantity,
                                                      onChanged: (value) {
                                                        if (itemController.cartIndex != -1) {
                                                          cartController.setManualQuantity(value, itemController.cartIndex);
                                                        } else {
                                                          itemController.setManualQuantity(value);
                                                        }
                                                      },
                                                    ),
                                                    const SizedBox(width: 8),
                                                    Text('kg',
                                                        style: robotoMedium),
                                                  ]);
                                                }),
                                                const SizedBox(
                                                    height: Dimensions
                                                        .paddingSizeLarge),

                                                Row(children: [
                                                  Text('${'total_amount'.tr}:',
                                                      style: robotoMedium.copyWith(
                                                          fontSize: Dimensions
                                                              .fontSizeLarge)),
                                                  const SizedBox(
                                                      width: Dimensions
                                                          .paddingSizeExtraSmall),
                                                  Text(
                                                    PriceConverter.convertPrice(itemController
                                                                .cartIndex !=
                                                            -1
                                                        ? _getItemDetailsDiscountPrice(
                                                            cart: Get.find<
                                                                        CartController>()
                                                                    .cartList[
                                                                itemController
                                                                    .cartIndex])
                                                        : priceWithAddons),
                                                    textDirection:
                                                        TextDirection.ltr,
                                                    style: robotoBold.copyWith(
                                                        color: Theme.of(context)
                                                            .primaryColor,
                                                        fontSize: Dimensions
                                                            .fontSizeLarge),
                                                  ),
                                                ]),
                                                const SizedBox(
                                                    height: Dimensions
                                                        .paddingSizeExtraLarge),

                                                itemController.item!
                                                        .isPrescriptionRequired!
                                                    ? Container(
                                                        padding: const EdgeInsets
                                                            .symmetric(
                                                            horizontal: Dimensions
                                                                .paddingSizeSmall,
                                                            vertical: Dimensions
                                                                .paddingSizeExtraSmall),
                                                        margin: const EdgeInsets
                                                            .only(
                                                            bottom: Dimensions
                                                                .paddingSizeSmall),
                                                        decoration:
                                                            BoxDecoration(
                                                          color: Theme.of(
                                                                  context)
                                                              .colorScheme
                                                              .error
                                                              .withOpacity(0.1),
                                                          borderRadius: BorderRadius
                                                              .circular(Dimensions
                                                                  .radiusSmall),
                                                        ),
                                                        child: Text(
                                                          '* ${'prescription_required'.tr}',
                                                          style: robotoRegular.copyWith(
                                                              fontSize: Dimensions
                                                                  .fontSizeSmall,
                                                              color: Theme.of(
                                                                      context)
                                                                  .colorScheme
                                                                  .error),
                                                        ),
                                                      )
                                                    : const SizedBox(),

                                                (itemController.item!
                                                                .description !=
                                                            null &&
                                                        itemController
                                                            .item!
                                                            .description!
                                                            .isNotEmpty)
                                                    ? Column(
                                                        crossAxisAlignment:
                                                            CrossAxisAlignment
                                                                .start,
                                                        children: [
                                                          Text('description'.tr,
                                                              style:
                                                                  robotoMedium),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeExtraSmall),
                                                          Text(
                                                              itemController
                                                                  .item!
                                                                  .description!,
                                                              style:
                                                                  robotoRegular),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeLarge),
                                                        ],
                                                      )
                                                    : const SizedBox(),

                                                (widget.item!.nutritionsName !=
                                                            null &&
                                                        widget
                                                            .item!
                                                            .nutritionsName!
                                                            .isNotEmpty)
                                                    ? Column(
                                                        crossAxisAlignment:
                                                            CrossAxisAlignment
                                                                .start,
                                                        children: [
                                                          Text(
                                                              'nutrition_details'
                                                                  .tr,
                                                              style:
                                                                  robotoMedium),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeExtraSmall),
                                                          Wrap(
                                                              children: List.generate(
                                                                  widget
                                                                      .item!
                                                                      .nutritionsName!
                                                                      .length,
                                                                  (index) {
                                                            return Text(
                                                              '${widget.item!.nutritionsName![index]}${widget.item!.nutritionsName!.length - 1 == index ? '.' : ', '}',
                                                              style: robotoRegular.copyWith(
                                                                  color: Theme.of(
                                                                          context)
                                                                      .textTheme
                                                                      .bodyLarge!
                                                                      .color
                                                                      ?.withOpacity(
                                                                          0.5)),
                                                            );
                                                          })),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeLarge),
                                                        ],
                                                      )
                                                    : const SizedBox(),

                                                (widget.item!.allergiesName !=
                                                            null &&
                                                        widget
                                                            .item!
                                                            .allergiesName!
                                                            .isNotEmpty)
                                                    ? Column(
                                                        crossAxisAlignment:
                                                            CrossAxisAlignment
                                                                .start,
                                                        children: [
                                                          Text(
                                                              'allergic_ingredients'
                                                                  .tr,
                                                              style:
                                                                  robotoMedium),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeExtraSmall),
                                                          Wrap(
                                                              children: List.generate(
                                                                  widget
                                                                      .item!
                                                                      .allergiesName!
                                                                      .length,
                                                                  (index) {
                                                            return Text(
                                                              '${widget.item!.allergiesName![index]}${widget.item!.allergiesName!.length - 1 == index ? '.' : ', '}',
                                                              style: robotoRegular.copyWith(
                                                                  color: Theme.of(
                                                                          context)
                                                                      .textTheme
                                                                      .bodyLarge!
                                                                      .color
                                                                      ?.withOpacity(
                                                                          0.5)),
                                                            );
                                                          })),
                                                          const SizedBox(
                                                              height: Dimensions
                                                                  .paddingSizeLarge),
                                                        ],
                                                      )
                                                    : const SizedBox(),
                                              ],
                                            ))))),
                            GetBuilder<CartController>(
                                builder: (cartController) {
                              return Container(
                                width: 1170,
                                padding: const EdgeInsets.all(
                                    Dimensions.paddingSizeSmall),
                                child: CustomButton(
                                  isLoading: cartController.isLoading,
                                  buttonText: itemController
                                              .item!.availableDateStarts !=
                                          null
                                      ? 'order_now'.tr
                                      : itemController.cartIndex != -1
                                          ? 'update_in_cart'.tr
                                          : 'add_to_cart'.tr,
                                  onPressed: () async {
                                    if (itemController
                                            .item!.availableDateStarts !=
                                        null) {
                                      Get.toNamed(
                                          RouteHelper.getCheckoutRoute(
                                              'campaign'),
                                          arguments: CheckoutScreen(
                                            storeId: null,
                                            fromCart: false,
                                            cartList: [cartModel!],
                                          ));
                                    }
                                    if (itemController.cartIndex == -1) {
                                      await cartController
                                          .addToCartOnline(cartModel!);
                                      itemController.setExistInCart(
                                          widget.item, null);
                                      showCartSnackBar();
                                      _key.currentState!.shake();
                                    } else {
                                      await cartController
                                          .updateCartOnline(cartModel!);
                                      showCartSnackBar();
                                      _key.currentState!.shake();
                                    }
                                  },
                                ),
                              );
                            }),
                          ])
                    : const Center(child: CircularProgressIndicator())),
          );
        },
      );
    });
  }

  List<int?> _getSelectedAddonIds({required List<AddOn> addOnIdList}) {
    List<int?> listOfAddOnId = [];
    for (var addOn in addOnIdList) {
      listOfAddOnId.add(addOn.id);
    }
    return listOfAddOnId;
  }

  List<double> _getSelectedAddonQtnList({required List<AddOn> addOnIdList}) {
    List<double> listOfAddOnQty = [];
    for (var addOn in addOnIdList) {
      listOfAddOnQty.add(addOn.quantity!);
    }
    return listOfAddOnQty;
  }

  double _getItemDetailsDiscountPrice({required CartModel cart}) {
    double discountedPrice = 0;

    double? discount = cart.item!.storeDiscount == 0
        ? cart.item!.discount!
        : cart.item!.storeDiscount!;
    String? discountType =
        (cart.item!.storeDiscount == 0) ? cart.item!.discountType : 'percent';
    String variationType = cart.variation != null && cart.variation!.isNotEmpty
        ? cart.variation![0].type!
        : '';

    if (cart.variation != null && cart.variation!.isNotEmpty) {
      for (Variation variation in cart.item!.variations!) {
        if (variation.type == variationType) {
          discountedPrice = (PriceConverter.convertWithDiscount(
                  variation.price!, discount, discountType)! *
              cart.quantity!);
          break;
        }
      }
    } else {
      discountedPrice = (PriceConverter.convertWithDiscount(
              cart.item!.price!, discount, discountType)! *
          cart.quantity!);
    }

    return discountedPrice;
  }
}
