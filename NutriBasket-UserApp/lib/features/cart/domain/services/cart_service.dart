import 'package:get/get_utils/get_utils.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/common/models/module_model.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart';
import 'package:sixam_mart/features/cart/domain/models/online_cart_model.dart';
import 'package:sixam_mart/features/cart/domain/repositories/cart_repository_interface.dart';
import 'package:sixam_mart/features/cart/domain/services/cart_service_interface.dart';
import 'package:sixam_mart/features/checkout/domain/models/place_order_body_model.dart';
import 'package:sixam_mart/helper/module_helper.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart'
    as item_variation;
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';

class CartService implements CartServiceInterface {
  final CartRepositoryInterface cartRepositoryInterface;
  CartService({required this.cartRepositoryInterface});

  @override
  Future<List<OnlineCartModel>?> addToCartOnline(OnlineCartModel cart) async {
    return await cartRepositoryInterface.add(cart);
  }

  @override
  Future<List<OnlineCartModel>?> updateCartOnline(OnlineCartModel cart) async {
    return await cartRepositoryInterface.update(cart.toJson(), null);
  }

  @override
  Future<bool> updateCartQuantityOnline(
      int cartId, double price, double quantity) async {
    return await cartRepositoryInterface.update({}, cartId,
        price: price, quantity: quantity, isUpdateQty: true);
  }

  @override
  Future<List<OnlineCartModel>?> getCartDataOnline() async {
    return await cartRepositoryInterface.getList();
  }

  @override
  Future<bool> removeCartItemOnline(int cartId) async {
    return await cartRepositoryInterface.delete(cartId);
  }

  @override
  Future<bool> clearCartOnline() async {
    return await cartRepositoryInterface.delete(null, isRemoveAll: true);
  }

  @override
  int availableSelectedIndex(int selectedIndex, int index) {
    int notAvailableIndex = selectedIndex;
    if (notAvailableIndex == index) {
      notAvailableIndex = -1;
    } else {
      notAvailableIndex = index;
    }
    return notAvailableIndex;
  }

  @override
  ModuleModel? forcefullySetModule(ModuleModel? selectedModule,
      List<ModuleModel>? moduleList, int moduleId) {
    ModuleModel? module;
    if (selectedModule == null && moduleList != null) {
      for (ModuleModel m in moduleList) {
        if (m.id == moduleId) {
          module = m;
          break;
        }
      }
    }
    return module;
  }

  @override
  List<AddOns> prepareAddonList(CartModel cartModel) {
    List<AddOns> addOnList = [];
    for (var addOnId in cartModel.addOnIds!) {
      for (AddOns addOns in cartModel.item!.addOns!) {
        if (addOns.id == addOnId.id) {
          addOnList.add(addOns);
          break;
        }
      }
    }
    return addOnList;
  }

  @override
  double calculateAddonPrice(
      double addOnPrice, List<AddOns> addOnList, CartModel cartModel) {
    for (int index = 0; index < addOnList.length; index++) {
      // Apply B2B pricing to addon prices
      double addonPrice = addOnList[index].price!;
      if (B2BPricingHelper.shouldUseB2BPricing()) {
        // For addons, we can use the item's B2B pricing as a base
        // or implement specific addon B2B pricing if needed
        final b2bItemPrice =
            B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
        if (b2bItemPrice != null) {
          // Apply proportional pricing to addons based on B2B item price
          double priceRatio = b2bItemPrice / cartModel.item!.price!;
          addonPrice = addonPrice * priceRatio;
        }
      }
      addOnPrice = addOnPrice +
          (addonPrice * (cartModel.addOnIds![index].quantity ?? 0.0));
    }
    return addOnPrice;
  }

  @override
  double calculateVariationPrice(bool isFoodVariation, CartModel cartModel,
      double? discount, String? discountType, double variationPrice) {
    double price = variationPrice;
    if (isFoodVariation) {
      for (int index = 0;
          index < cartModel.item!.foodVariations!.length;
          index++) {
        for (int i = 0;
            i < cartModel.item!.foodVariations![index].variationValues!.length;
            i++) {
          if (cartModel.foodVariations![index][i]!) {
            // Apply B2B pricing for food variations
            double variationPrice = cartModel
                .item!.foodVariations![index].variationValues![i].optionPrice!;
            if (B2BPricingHelper.shouldUseB2BPricing()) {
              final b2bVariationPrice =
                  B2BPricingHelper.getB2BContractVariationPrice(
                      cartModel.item!.id!,
                      cartModel.item!.foodVariations![index].name!);
              if (b2bVariationPrice != null) {
                variationPrice = b2bVariationPrice;
              }
            }
            price += (PriceConverter.convertWithDiscount(
                    variationPrice, discount, discountType,
                    isFoodVariation: true)! *
                cartModel.quantity!);
          }
        }
      }
    } else {
      String variationType = '';
      for (int i = 0; i < cartModel.variation!.length; i++) {
        variationType = cartModel.variation![i].type!;
      }

      for (item_variation.Variation variation in cartModel.item!.variations!) {
        if (variation.type == variationType) {
          // Apply B2B pricing for regular variations
          double variationPrice = variation.price!;
          if (B2BPricingHelper.shouldUseB2BPricing()) {
            final b2bVariationPrice =
                B2BPricingHelper.getB2BContractVariationPrice(
                    cartModel.item!.id!, variationType);
            if (b2bVariationPrice != null) {
              variationPrice = b2bVariationPrice;
            }
          }
          price = (PriceConverter.convertWithDiscount(
                  variationPrice, discount, discountType)! *
              cartModel.quantity!);
          break;
        }
      }
    }
    return price;
  }

  @override
  double calculateVariationWithoutDiscountPrice(
      bool isFoodVariation, CartModel cartModel, double variationPrice) {
    double price = variationPrice;
    if (isFoodVariation) {
      for (int index = 0;
          index < cartModel.item!.foodVariations!.length;
          index++) {
        for (int i = 0;
            i < cartModel.item!.foodVariations![index].variationValues!.length;
            i++) {
          if (cartModel.foodVariations![index][i]!) {
            // Apply B2B pricing for food variations without discount
            double variationPrice = cartModel
                .item!.foodVariations![index].variationValues![i].optionPrice!;
            if (B2BPricingHelper.shouldUseB2BPricing()) {
              final b2bVariationPrice =
                  B2BPricingHelper.getB2BContractVariationPrice(
                      cartModel.item!.id!,
                      cartModel.item!.foodVariations![index].name!);
              if (b2bVariationPrice != null) {
                variationPrice = b2bVariationPrice;
              }
            }
            price += (variationPrice * cartModel.quantity!);
          }
        }
      }
    } else {
      String variationType = '';
      for (int i = 0; i < cartModel.variation!.length; i++) {
        variationType = cartModel.variation![i].type!;
      }

      for (item_variation.Variation variation in cartModel.item!.variations!) {
        if (variation.type == variationType) {
          // Apply B2B pricing for regular variations without discount
          double variationPrice = variation.price!;
          if (B2BPricingHelper.shouldUseB2BPricing()) {
            final b2bVariationPrice =
                B2BPricingHelper.getB2BContractVariationPrice(
                    cartModel.item!.id!, variationType);
            if (b2bVariationPrice != null) {
              variationPrice = b2bVariationPrice;
            }
          }
          price = (variationPrice * cartModel.quantity!);
          break;
        }
      }
    }
    return price;
  }

  @override
  bool checkVariation(bool isFoodVariation, CartModel cartModel) {
    bool haveVariation = false;
    if (isFoodVariation) {
      for (int index = 0;
          index < cartModel.item!.foodVariations!.length;
          index++) {
        for (int i = 0;
            i < cartModel.item!.foodVariations![index].variationValues!.length;
            i++) {
          if (cartModel.foodVariations![index][i]!) {
            haveVariation = true;
            break;
          }
        }
      }
    } else {
      if (cartModel.variation!.isNotEmpty) {
        haveVariation = true;
      }
    }
    return haveVariation;
  }

  @override
  Future<void> addSharedPrefCartList(List<CartModel> cartProductList) async {
    await cartRepositoryInterface.addSharedPrefCartList(cartProductList);
  }

  @override
  int? getCartId(int cartIndex, List<CartModel> cartList) {
    if (cartIndex != -1) {
      return cartList.isNotEmpty ? cartList[cartIndex].id : null;
    } else {
      return null;
    }
  }

  @override
  Future<double> decideItemQuantity(bool isIncrement, List<CartModel> cartList,
      int cartIndex, int? stock, int? quantityLimit, bool moduleStock) async {
    double quantity = cartList[cartIndex].quantity!;
    if (isIncrement) {
      // Removed stock validation - allow ordering even when out of stock
      if (quantityLimit != null) {
        if (quantity >= quantityLimit && quantityLimit != 0) {
          showCustomSnackBar('${'maximum_quantity_limit'.tr} $quantityLimit');
        } else {
          quantity = quantity + 1.0;
        }
      } else {
        quantity = quantity + 1.0;
      }
    } else {
      quantity = quantity - 1.0;
      // Ensure quantity doesn't go below minimum
      if (quantity <= 0) {
        quantity = 0.01;
      }
    }
    return quantity;
  }

  @override
  Future<double> calculateDiscountedPrice(
      CartModel cartModel, double quantity, bool isFoodVariation) async {
    double? discount = cartModel.item!.storeDiscount == 0
        ? cartModel.item!.discount
        : cartModel.item!.storeDiscount;
    String? discountType = cartModel.item!.storeDiscount == 0
        ? cartModel.item!.discountType
        : 'percent';
    double variationPrice = 0;
    double addonPrice = 0;

    // Apply B2B pricing to base item price
    double baseItemPrice = cartModel.item!.price!;
    if (B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice =
          B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
      if (b2bPrice != null) {
        baseItemPrice = b2bPrice;
      }
    }

    if (isFoodVariation) {
      for (int index = 0;
          index < cartModel.item!.foodVariations!.length;
          index++) {
        for (int i = 0;
            i < cartModel.item!.foodVariations![index].variationValues!.length;
            i++) {
          if (cartModel.foodVariations![index][i]!) {
            // Apply B2B pricing to food variations
            double variationOptionPrice = cartModel
                .item!.foodVariations![index].variationValues![i].optionPrice!;
            if (B2BPricingHelper.shouldUseB2BPricing()) {
              final b2bVariationPrice =
                  B2BPricingHelper.getB2BContractVariationPrice(
                      cartModel.item!.id!,
                      cartModel.item!.foodVariations![index].name!);
              if (b2bVariationPrice != null) {
                variationOptionPrice = b2bVariationPrice;
              }
            }
            variationPrice += (PriceConverter.convertWithDiscount(
                    variationOptionPrice, discount, discountType,
                    isFoodVariation: true)! *
                cartModel.quantity!);
          }
        }
      }

      List<AddOns> addOnList = [];
      for (var addOnId in cartModel.addOnIds!) {
        for (AddOns addOns in cartModel.item!.addOns!) {
          if (addOns.id == addOnId.id) {
            addOnList.add(addOns);
            break;
          }
        }
      }
      for (int index = 0; index < addOnList.length; index++) {
        // Apply B2B pricing to addon prices
        double addonPrice = addOnList[index].price!;
        if (B2BPricingHelper.shouldUseB2BPricing()) {
          final b2bItemPrice =
              B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
          if (b2bItemPrice != null) {
            double priceRatio = b2bItemPrice / cartModel.item!.price!;
            addonPrice = addonPrice * priceRatio;
          }
        }
        addonPrice = addonPrice +
            (addonPrice * (cartModel.addOnIds![index].quantity ?? 0.0));
      }
    }
    double discountedPrice = addonPrice +
        variationPrice +
        (baseItemPrice * quantity) -
        PriceConverter.calculation(
            baseItemPrice, discount, discountType!, quantity);
    return discountedPrice;
  }

  @override
  List<CartModel> formatOnlineCartToLocalCart(
      {required List<OnlineCartModel> onlineCartModel}) {
    List<CartModel> cartList = [];
    for (OnlineCartModel cart in onlineCartModel) {
      double price = cart.item!.price!;
      double? discount = cart.item!.storeDiscount == 0
          ? cart.item!.discount!
          : cart.item!.storeDiscount!;
      String? discountType =
          (cart.item!.storeDiscount == 0) ? cart.item!.discountType : 'percent';

      // Apply B2B pricing
      if (B2BPricingHelper.shouldUseB2BPricing()) {
        final b2bPrice = B2BPricingHelper.getB2BContractPrice(cart.item!.id!);
        if (b2bPrice != null) {
          price = b2bPrice;
        }
      }

      double discountedPrice =
          PriceConverter.convertWithDiscount(price, discount, discountType)!;

      double? discountAmount = price - discountedPrice;
      double? quantity = cart.quantity;
      int? stock = cart.item!.stock ?? 0;

      List<List<bool?>> selectedFoodVariations = [];
      List<bool> collapsVariation = [];
      if (cart.item!.foodVariations!.isNotEmpty) {
        for (int i = 0; i < cart.item!.foodVariations!.length; i++) {
          selectedFoodVariations.add([]);
          collapsVariation.add(false);
          for (int j = 0;
              j < cart.item!.foodVariations![i].variationValues!.length;
              j++) {
            selectedFoodVariations[i].add(false);
          }
        }
      }

      List<AddOn> addOnList = [];
      List<AddOns> addOnsList = [];
      if (cart.addOnIds!.isNotEmpty) {
        for (int i = 0; i < cart.addOnIds!.length; i++) {
          addOnList.add(AddOn(
              id: cart.addOnIds![i], quantity: cart.addOnQtys![i]?.toDouble()));
          for (AddOns addOns in cart.item!.addOns!) {
            if (addOns.id == cart.addOnIds![i]) {
              addOnsList.add(addOns);
              break;
            }
          }
        }
      }

      int? quantityLimit = cart.item!.quantityLimit;

      cartList.add(
        CartModel(
          cart.id,
          price,
          discountedPrice,
          cart.productVariation ?? [],
          selectedFoodVariations,
          discountAmount,
          quantity?.toDouble(),
          addOnList,
          addOnsList,
          false,
          stock,
          cart.item,
          quantityLimit,
        ),
      );
    }

    return cartList;
  }

  @override
  int isExistInCart(List<CartModel> cartList, int? itemID, String variationType,
      bool isUpdate, int? cartIndex) {
    for (int index = 0; index < cartList.length; index++) {
      if (cartList[index].item!.id == itemID &&
          (cartList[index].variation!.isNotEmpty
              ? cartList[index].variation![0].type == variationType
              : true)) {
        if ((isUpdate && index == cartIndex)) {
          return -1;
        } else {
          return index;
        }
      }
    }
    return -1;
  }

  @override
  bool existAnotherStoreItem(
      int? storeID, int? moduleId, List<CartModel> cartList) {
    for (CartModel cartModel in cartList) {
      if (cartModel.item!.storeId != storeID &&
          cartModel.item!.moduleId == moduleId) {
        return true;
      }
    }
    return false;
  }

  @override
  double cartQuantity(int itemId, List<CartModel> cartList) {
    double quantity = 0;
    for (CartModel cart in cartList) {
      if (cart.item!.id == itemId) {
        quantity += cart.quantity!;
      }
    }
    return quantity;
  }

  @override
  String cartVariant(int itemId, List<CartModel> cartList) {
    String variant = '';
    for (CartModel cart in cartList) {
      if (cart.item!.id == itemId) {
        if (!ModuleHelper.getModuleConfig(cart.item!.moduleType)
            .newVariation!) {
          variant = (cart.variation != null && cart.variation!.isNotEmpty)
              ? cart.variation![0].type!
              : '';
        }
      }
    }
    return variant;
  }

  @override
  double calculatePriceWithAddons(
      CartModel cartModel,
      double price,
      double addonPrice,
      double? discount,
      String? discountType,
      double quantity) {
    double addonsCost = 0;
    if (cartModel.addOnIds!.isNotEmpty) {
      List<AddOns> addOnList = [];
      for (var addOnId in cartModel.addOnIds!) {
        for (AddOns addOns in cartModel.item!.addOns!) {
          if (addOns.id == addOnId.id) {
            addOnList.add(addOns);
            break;
          }
        }
      }
      for (int index = 0; index < addOnList.length; index++) {
        // Apply B2B pricing to addon prices
        double addonPrice = addOnList[index].price!;
        if (B2BPricingHelper.shouldUseB2BPricing()) {
          final b2bItemPrice =
              B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
          if (b2bItemPrice != null) {
            double priceRatio = b2bItemPrice / cartModel.item!.price!;
            addonPrice = addonPrice * priceRatio;
          }
        }
        addonsCost = addonsCost +
            (addonPrice * (cartModel.addOnIds![index].quantity ?? 0.0));
      }
    }

    // Apply B2B pricing to the main item price
    double finalPrice = price;
    if (B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice =
          B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
      if (b2bPrice != null) {
        finalPrice = b2bPrice;
      }
    }

    double discountedPrice = addonsCost +
        addonPrice +
        (finalPrice * quantity) -
        PriceConverter.calculation(
            finalPrice, discount, discountType!, quantity);
    return discountedPrice;
  }
}
