import 'package:get/get.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/common/models/module_model.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart';
import 'package:sixam_mart/features/cart/domain/models/online_cart_model.dart';
import 'package:sixam_mart/features/cart/domain/services/cart_service_interface.dart';
import 'package:sixam_mart/features/checkout/domain/models/place_order_body_model.dart';
import 'package:sixam_mart/features/home/screens/home_screen.dart';
import 'package:sixam_mart/features/item/controllers/item_controller.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/helper/auth_helper.dart';
import 'package:sixam_mart/helper/date_converter.dart';
import 'package:sixam_mart/helper/module_helper.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';
import 'package:sixam_mart/util/app_constants.dart';
import 'package:sixam_mart/features/cart/domain/repositories/cart_repository_interface.dart';
import 'package:sixam_mart/features/cart/domain/repositories/cart_repository.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/profile/controllers/profile_controller.dart';

class CartController extends GetxController implements GetxService {
  final CartServiceInterface cartServiceInterface;
  final CartRepositoryInterface cartRepositoryInterface;

  CartController(
      {required this.cartServiceInterface,
      required this.cartRepositoryInterface});

  List<CartModel> _cartList = [];
  List<CartModel> get cartList => _cartList;

  double _subTotal = 0;
  double get subTotal => _subTotal;

  double _itemPrice = 0;
  double get itemPrice => _itemPrice;

  double _itemDiscountPrice = 0;
  double get itemDiscountPrice => _itemDiscountPrice;

  double _addOns = 0;
  double get addOns => _addOns;

  double _variationPrice = 0;
  double get variationPrice => _variationPrice;

  List<List<AddOns>> _addOnsList = [];
  List<List<AddOns>> get addOnsList => _addOnsList;

  List<bool> _availableList = [];
  List<bool> get availableList => _availableList;

  List<String> notAvailableList = [
    'Remove it from my cart',
    'I\'ll wait until it\'s restocked',
    'Please cancel the order',
    'Call me ASAP',
    'Notify me when it\'s back'
  ];
  bool _addCutlery = false;
  bool get addCutlery => _addCutlery;

  int _notAvailableIndex = -1;
  int get notAvailableIndex => _notAvailableIndex;

  int _currentIndex = 0;
  int get currentIndex => _currentIndex;

  bool _isLoading = false;
  bool get isLoading => _isLoading;

  bool _needExtraPackage = false;
  bool get needExtraPackage => _needExtraPackage;

  bool _isExpanded = true;
  bool get isExpanded => _isExpanded;

  int? _directAddCartItemIndex = -1;
  int? get directAddCartItemIndex => _directAddCartItemIndex;

  void setDirectlyAddToCartIndex(int? index) {
    _directAddCartItemIndex = index;
  }

  void toggleExtraPackage({bool willUpdate = true}) {
    _needExtraPackage = !_needExtraPackage;
    if (willUpdate) {
      update();
    }
  }

  void setAvailableIndex(int index, {bool willUpdate = true}) {
    _notAvailableIndex = index;
    if (willUpdate) {
      update();
    }
  }

  void updateCutlery({bool willUpdate = true}) {
    _addCutlery = !_addCutlery;
    if (willUpdate) {
      update();
    }
  }

  Future<void> forcefullySetModule(int moduleId) async {
    ModuleModel? module = cartServiceInterface.forcefullySetModule(
        Get.find<SplashController>().module,
        Get.find<SplashController>().moduleList,
        moduleId);
    if (module != null) {
      await Get.find<SplashController>().setModule(module);
      HomeScreen.loadData(true);
    }
  }

  double calculationCart() {
    _addOnsList = [];
    _availableList = [];
    _itemPrice = 0;
    _itemDiscountPrice = 0;
    _addOns = 0;
    _variationPrice = 0;
    bool isFoodVariation = false;
    double variationWithoutDiscountPrice = 0;
    bool haveVariation = false;
    for (var cartModel in cartList) {
      isFoodVariation = ModuleHelper.getModuleConfig(cartModel.item!.moduleType)
          .newVariation!;
      double? discount = cartModel.item!.storeDiscount == 0
          ? cartModel.item!.discount
          : cartModel.item!.storeDiscount;
      String? discountType = cartModel.item!.storeDiscount == 0
          ? cartModel.item!.discountType
          : 'percent';

      List<AddOns> addOnList = cartServiceInterface.prepareAddonList(cartModel);

      _addOnsList.add(addOnList);
      _availableList.add(DateConverter.isAvailable(
          cartModel.item!.availableTimeStarts,
          cartModel.item!.availableTimeEnds));

      _addOns = cartServiceInterface.calculateAddonPrice(
          _addOns, addOnList, cartModel);

      _variationPrice = cartServiceInterface.calculateVariationPrice(
          isFoodVariation, cartModel, discount, discountType, _variationPrice);

      variationWithoutDiscountPrice =
          cartServiceInterface.calculateVariationWithoutDiscountPrice(
              isFoodVariation, cartModel, variationWithoutDiscountPrice);
      haveVariation =
          cartServiceInterface.checkVariation(isFoodVariation, cartModel);

      // Apply B2B pricing to item price
      double price = cartModel.item!.price!;
      if (B2BPricingHelper.shouldUseB2BPricing()) {
        final b2bPrice =
            B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
        if (b2bPrice != null) {
          price = b2bPrice;
        }
      }

      double itemPrice = haveVariation
          ? variationWithoutDiscountPrice
          : (price * cartModel.quantity!);

      // Calculate discount price using the correct price (B2B or original)
      double discountPrice = haveVariation
          ? (variationWithoutDiscountPrice - _variationPrice)
          : (itemPrice -
              (PriceConverter.convertWithDiscount(
                      price, discount, discountType)! *
                  cartModel.quantity!));

      _itemPrice = _itemPrice + itemPrice;
      _itemDiscountPrice = _itemDiscountPrice + discountPrice;

      haveVariation = false;
    }
    if (isFoodVariation) {
      _itemDiscountPrice = _itemDiscountPrice +
          (variationWithoutDiscountPrice - _variationPrice);
      _variationPrice = variationWithoutDiscountPrice;
      _subTotal = (_itemPrice - _itemDiscountPrice) + _addOns + _variationPrice;
    } else {
      _subTotal = (_itemPrice - _itemDiscountPrice);
    }

    return _subTotal;
  }

  Future<void> addToCart(CartModel cartModel, int? index) async {
    if (index != null && index != -1) {
      _cartList.replaceRange(index, index + 1, [cartModel]);
    } else {
      _cartList.add(cartModel);
    }
    Get.find<ItemController>()
        .setExistInCart(cartModel.item, null, notify: true);
    await cartServiceInterface.addSharedPrefCartList(_cartList);

    calculationCart();
    update();
  }

  int? getCartId(int cartIndex) {
    return cartServiceInterface.getCartId(cartIndex, _cartList);
  }

  Future<void> setQuantity(
      bool isIncrement, int cartIndex, int? stock, int? quantityLimit) async {
    _isLoading = true;
    update();

    _cartList[cartIndex].quantity =
        await cartServiceInterface.decideItemQuantity(
            isIncrement,
            _cartList,
            cartIndex,
            stock,
            quantityLimit,
            Get.find<SplashController>()
                .configModel!
                .moduleConfig!
                .module!
                .stock!);

    double discountedPrice =
        await cartServiceInterface.calculateDiscountedPrice(
            _cartList[cartIndex],
            _cartList[cartIndex].quantity!,
            ModuleHelper.getModuleConfig(_cartList[cartIndex].item!.moduleType)
                .newVariation!);
    if (ModuleHelper.getModuleConfig(_cartList[cartIndex].item!.moduleType)
        .newVariation!) {
      await Get.find<ItemController>()
          .setExistInCart(_cartList[cartIndex].item, null, notify: true);
    }

    // Only update online cart if cart has an ID
    int? cartId = _cartList[cartIndex].id;
    if (cartId != null && cartId > 0) {
      await updateCartQuantityOnline(
          cartId, discountedPrice, _cartList[cartIndex].quantity!);
    } else {
      // For local cart items (like template items), just update locally
      await cartServiceInterface.addSharedPrefCartList(_cartList);
      calculationCart();
      _isLoading = false;
      update();
    }
  }

  void setManualQuantity(double quantity, int cartIndex) async {
    if (quantity > 0) {
      _cartList[cartIndex].quantity = quantity;
      await cartServiceInterface.addSharedPrefCartList(_cartList);
      calculationCart();
      update();
    }
  }

  Future<void> removeFromCart(int index, {Item? item}) async {
    int? cartId = _cartList[index].id;
    CartModel cartModel = _cartList[index];
    _cartList.removeAt(index);
    await cartServiceInterface.addSharedPrefCartList(_cartList);
    if (item != null) {
      Get.find<ItemController>().setExistInCart(item, null, notify: true);
    }

    // Also remove from online cart if cart has an ID
    if (cartId != null && cartId > 0) {
      await cartServiceInterface.removeCartItemOnline(cartId);
    }

    calculationCart();
    update();
  }

  Future<void> clearCartList() async {
    _cartList = [];
    await cartServiceInterface.addSharedPrefCartList(_cartList);
    calculationCart();
    update();
  }

  Future<void> updateCartQuantityOnline(
      int cartId, double discountedPrice, double quantity) async {
    _isLoading = true;
    update();
    bool response = await cartServiceInterface.updateCartQuantityOnline(
        cartId, discountedPrice, quantity);
    _isLoading = false;
    if (response) {
      getCartDataOnline();
    }
    update();
  }

  Future<void> getCartDataOnline() async {
    List<OnlineCartModel>? onlineCartList =
        await cartServiceInterface.getCartDataOnline();
    if (onlineCartList != null) {
      _cartList = cartServiceInterface.formatOnlineCartToLocalCart(
          onlineCartModel: onlineCartList);
      calculationCart();
    }
    update();
  }

  Future<void> addToCartOnline(CartModel cartModel) async {
    // Frontend validation for minimum quantity
    if (cartModel.quantity == null || cartModel.quantity! <= 0) {
      showCustomSnackBar('Quantity must be greater than 0');
      return;
    }
    // Calculate the correct price with B2B pricing applied
    double? price = cartModel.discountedPrice;
    if (B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice =
          B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
      if (b2bPrice != null) {
        // Apply B2B pricing and any existing discounts
        double? discount = cartModel.item!.storeDiscount == 0
            ? cartModel.item!.discount
            : cartModel.item!.storeDiscount;
        String? discountType = cartModel.item!.storeDiscount == 0
            ? cartModel.item!.discountType
            : 'percent';

        price = PriceConverter.convertWithDiscount(
                b2bPrice, discount, discountType) ??
            b2bPrice;
      }
    }

    // Convert CartModel to OnlineCartModel
    OnlineCartModel onlineCart = OnlineCartModel(
      id: cartModel.id,
      userId: Get.find<ProfileController>().userInfoModel?.id,
      moduleId: Get.find<SplashController>().module?.id,
      itemId: cartModel.item!.id,
      price: double.tryParse(price.toString()),
      quantity: cartModel.quantity,
      addOnIds: cartModel.addOnIds!.map((e) => e.id!).toList(),
      addOnQtys: cartModel.addOnIds!.map((e) => e.quantity ?? 0.0).toList(),
      itemType: 'Item',
      isGuest: AuthHelper.isGuestLoggedIn(),
      model: 'Item',
      // Add other fields as needed
    );

    List<OnlineCartModel>? response =
        await cartServiceInterface.addToCartOnline(onlineCart);
    if (response != null) {
      getCartDataOnline();
    }
  }

  Future<void> updateCartOnline(CartModel cartModel) async {
    // Frontend validation for minimum quantity
    if (cartModel.quantity == null || cartModel.quantity! <= 0) {
      showCustomSnackBar('Quantity must be greater than 0');
      return;
    }
    // If cart_id is null, this is a new item, so use addToCartOnline
    if (cartModel.id == null) {
      await addToCartOnline(cartModel);
      return;
    }
    // Calculate the correct price with B2B pricing applied
    double? price = cartModel.discountedPrice;
    if (B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice =
          B2BPricingHelper.getB2BContractPrice(cartModel.item!.id!);
      if (b2bPrice != null) {
        // Apply B2B pricing and any existing discounts
        double? discount = cartModel.item!.storeDiscount == 0
            ? cartModel.item!.discount
            : cartModel.item!.storeDiscount;
        String? discountType = cartModel.item!.storeDiscount == 0
            ? cartModel.item!.discountType
            : 'percent';

        price = PriceConverter.convertWithDiscount(
                b2bPrice, discount, discountType) ??
            b2bPrice;
      }
    }

    // Convert CartModel to OnlineCart
    OnlineCartModel onlineCart = OnlineCartModel(
      id: cartModel.id,
      userId: Get.find<ProfileController>().userInfoModel?.id,
      moduleId: Get.find<SplashController>().module?.id,
      itemId: cartModel.item!.id,
      price: double.tryParse(price.toString()),
      quantity: cartModel.quantity,
      addOnIds: cartModel.addOnIds!.map((e) => e.id!).toList(),
      addOnQtys: cartModel.addOnIds!.map((e) => e.quantity ?? 0.0).toList(),
      itemType: 'Item',
      isGuest: AuthHelper.isGuestLoggedIn(),
      model: 'Item',
    );

    List<OnlineCartModel>? response =
        await cartServiceInterface.updateCartOnline(onlineCart);
    if (response != null) {
      getCartDataOnline();
    }
  }

  Future<void> removeFromCartOnline(CartModel cartModel) async {
    bool response =
        await cartServiceInterface.removeCartItemOnline(cartModel.id!);
    if (response) {
      getCartDataOnline();
    }
  }

  Future<void> clearCartOnline() async {
    bool response = await cartServiceInterface.clearCartOnline();
    if (response) {
      getCartDataOnline();
    }
  }

  // Add missing methods that are being called by other parts of the code
  double cartQuantity(int itemId, List<CartModel> cartList) {
    return cartServiceInterface.cartQuantity(itemId, cartList);
  }

  String cartVariant(int itemId, List<CartModel> cartList) {
    return cartServiceInterface.cartVariant(itemId, cartList);
  }

  int isExistInCart(List<CartModel> cartList, int? itemID, String variationType,
      bool isUpdate, int? cartIndex) {
    return cartServiceInterface.isExistInCart(
        cartList, itemID, variationType, isUpdate, cartIndex);
  }

  void setCurrentIndex(int index) {
    // This method is called but not implemented in the service interface
    // Adding a simple implementation
    update();
  }

  void setExpanded(bool expanded) {
    // This method is called but not implemented in the service interface
    // Adding a simple implementation
    update();
  }
}
