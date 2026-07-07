import 'package:flutter/material.dart';
import 'package:sixam_mart/common/models/response_model.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/features/store/domain/models/store_model.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/favourite/domain/services/favourite_service_interface.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/features/cart/controllers/cart_controller.dart';
import 'package:sixam_mart/features/cart/domain/models/cart_model.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';

class FavouriteController extends GetxController implements GetxService {
  final FavouriteServiceInterface favouriteServiceInterface;
  FavouriteController({required this.favouriteServiceInterface});

  List<Item?>? _wishItemList;
  List<Item?>? get wishItemList => _wishItemList;

  List<Store?>? _wishStoreList;
  List<Store?>? get wishStoreList => _wishStoreList;

  List<int?> _wishItemIdList = [];
  List<int?> get wishItemIdList => _wishItemIdList;

  List<int?> _wishStoreIdList = [];
  List<int?> get wishStoreIdList => _wishStoreIdList;

  bool _isRemoving = false;
  bool get isRemoving => _isRemoving;

  // Template functionality
  List<TemplateModel>? _templateList;
  List<TemplateModel>? get templateList => _templateList;

  TemplateModel? _selectedTemplate;
  TemplateModel? get selectedTemplate => _selectedTemplate;

  bool _isLoadingTemplates = false;
  bool get isLoadingTemplates => _isLoadingTemplates;

  bool _isCreatingTemplate = false;
  bool get isCreatingTemplate => _isCreatingTemplate;

  void addToFavouriteList(Item? product, int? storeID, bool isStore,
      {bool getXSnackBar = false}) async {
    _isRemoving = true;
    update();
    if (isStore) {
      _wishStoreList ??= [];
      _wishStoreIdList.add(storeID);
      _wishStoreList!.add(Store());
    } else {
      _wishItemList ??= [];
      _wishItemList!.add(product);
      _wishItemIdList.add(product!.id);
    }
    ResponseModel responseModel = await favouriteServiceInterface
        .addFavouriteList(isStore ? storeID : product!.id, isStore);
    if (responseModel.isSuccess) {
      showCustomSnackBar(responseModel.message,
          isError: false, getXSnackBar: getXSnackBar);
    } else {
      if (isStore) {
        for (var storeId in _wishStoreIdList) {
          if (storeId == storeID) {
            _wishStoreIdList.removeAt(_wishStoreIdList.indexOf(storeId));
          }
        }
      } else {
        for (var productId in _wishItemIdList) {
          if (productId == product!.id) {
            _wishItemIdList.removeAt(_wishItemIdList.indexOf(productId));
          }
        }
      }
      showCustomSnackBar(responseModel.message,
          isError: true, getXSnackBar: getXSnackBar);
    }
    _isRemoving = false;
    update();
  }

  void removeFromFavouriteList(int? id, bool isStore,
      {bool getXSnackBar = false}) async {
    _isRemoving = true;
    update();

    int idIndex = -1;
    int? storeId, itemId;
    Store? store;
    Item? item;
    if (isStore) {
      idIndex = _wishStoreIdList.indexOf(id);
      if (idIndex != -1) {
        storeId = id;
        _wishStoreIdList.removeAt(idIndex);
        store = _wishStoreList![idIndex];
        _wishStoreList!.removeAt(idIndex);
      }
    } else {
      idIndex = _wishItemIdList.indexOf(id);
      if (idIndex != -1) {
        itemId = id;
        _wishItemIdList.removeAt(idIndex);
        item = _wishItemList![idIndex];
        _wishItemList!.removeAt(idIndex);
      }
    }
    ResponseModel responseModel =
        await favouriteServiceInterface.removeFavouriteList(id, isStore);
    if (responseModel.isSuccess) {
      showCustomSnackBar(responseModel.message,
          isError: false, getXSnackBar: getXSnackBar);
    } else {
      showCustomSnackBar(responseModel.message,
          isError: true, getXSnackBar: getXSnackBar);
      if (isStore) {
        _wishStoreIdList.add(storeId);
        _wishStoreList!.add(store);
      } else {
        _wishItemIdList.add(itemId);
        _wishItemList!.add(item);
      }
    }
    _isRemoving = false;
    update();
  }

  Future<void> getFavouriteList() async {
    _wishItemList = null;
    _wishStoreList = null;
    Response response = await favouriteServiceInterface.getFavouriteList();
    if (response.statusCode == 200) {
      update();
      _wishItemList = [];
      _wishStoreList = [];
      _wishStoreIdList = [];
      _wishItemIdList = [];

      if (response.body['item'] != null) {
        response.body['item'].forEach((item) async {
          if (item['module_type'] == null ||
              !Get.find<SplashController>()
                  .getModuleConfig(item['module_type'])
                  .newVariation! ||
              item['variations'] == null ||
              item['variations'].isEmpty ||
              (item['food_variations'] != null &&
                  item['food_variations'].isNotEmpty)) {
            Item i = Item.fromJson(item);
            if (Get.find<SplashController>().module == null) {
              _wishItemList!.addAll(favouriteServiceInterface.wishItemList(i));
              _wishItemIdList
                  .addAll(favouriteServiceInterface.wishItemIdList(i));
            } else {
              _wishItemList!.add(i);
              _wishItemIdList.add(i.id);
            }
          }
        });
      }

      response.body['store'].forEach((store) async {
        if (Get.find<SplashController>().module == null) {
          _wishStoreList!
              .addAll(favouriteServiceInterface.wishStoreList(store));
          _wishStoreIdList
              .addAll(favouriteServiceInterface.wishStoreIdList(store));
        } else {
          Store? s;
          try {
            s = Store.fromJson(store);
          } catch (e) {
            debugPrint('exception create in store list create : $e');
          }
          if (s != null &&
              Get.find<SplashController>().module!.id == s.moduleId) {
            _wishStoreList!.add(s);
            _wishStoreIdList.add(s.id);
          }
        }
      });
    }
    update();
  }

  void removeFavourite() {
    _wishItemIdList = [];
    _wishStoreIdList = [];
  }

  // Template functionality methods
  Future<void> getTemplateList() async {
    _isLoadingTemplates = true;
    update();

    Response response = await favouriteServiceInterface.getTemplateList();
    print('====> Template API Response Status: ${response.statusCode}');
    print('====> Template API Response Body: ${response.body}');

    if (response.statusCode == 200) {
      _templateList = [];
      if (response.body['templates'] != null) {
        print('====> Found ${response.body['templates'].length} templates');
        response.body['templates'].forEach((template) {
          print('====> Processing template: ${template['name']}');
          print(
              '====> Template items count: ${template['items']?.length ?? 0}');
          if (template['items'] != null) {
            print('====> First item data: ${template['items'][0]}');
          }
          try {
            _templateList!.add(TemplateModel.fromJson(template));
            print('====> Successfully added template: ${template['name']}');
          } catch (e) {
            print('====> Error processing template ${template['name']}: $e');
          }
        });
      } else {
        print('====> No templates found in response');
      }
      print('====> Final template list length: ${_templateList?.length}');
    } else {
      print(
          '====> Template API Error: ${response.statusCode} - ${response.body}');
      showCustomSnackBar('failed_to_load_templates'.tr, isError: true);
    }

    _isLoadingTemplates = false;
    update();
  }

  Future<void> refreshTemplates() async {
    await getTemplateList();
  }

  void setSelectedTemplate(TemplateModel? template) {
    _selectedTemplate = template;
    update();
  }

  Future<void> createTemplate(
      String name, String description, List<TemplateItem> items) async {
    _isCreatingTemplate = true;
    update();

    // Validate input
    if (name.trim().isEmpty) {
      showCustomSnackBar('please_enter_template_name'.tr, isError: true);
      _isCreatingTemplate = false;
      update();
      return;
    }

    if (items.isEmpty) {
      showCustomSnackBar('please_add_items_to_template'.tr, isError: true);
      _isCreatingTemplate = false;
      update();
      return;
    }

    // Validate items
    for (TemplateItem item in items) {
      if (item.itemId == null) {
        showCustomSnackBar('invalid_item_in_template'.tr, isError: true);
        _isCreatingTemplate = false;
        update();
        return;
      }
      if (item.quantity == null || item.quantity! < 1) {
        showCustomSnackBar('invalid_quantity_in_template'.tr, isError: true);
        _isCreatingTemplate = false;
        update();
        return;
      }
    }

    ResponseModel response = await favouriteServiceInterface.createTemplate(
        name.trim(), description.trim(), items);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }

    _isCreatingTemplate = false;
    update();
  }

  Future<void> updateTemplate(int templateId, String name, String description,
      List<TemplateItem> items) async {
    ResponseModel response = await favouriteServiceInterface.updateTemplate(
        templateId, name, description, items);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  Future<void> deleteTemplate(int templateId) async {
    ResponseModel response =
        await favouriteServiceInterface.deleteTemplate(templateId);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  Future<void> addItemToTemplate(int templateId, TemplateItem item) async {
    ResponseModel response =
        await favouriteServiceInterface.addItemToTemplate(templateId, item);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  Future<void> removeItemFromTemplate(int templateId, int itemId) async {
    ResponseModel response = await favouriteServiceInterface
        .removeItemFromTemplate(templateId, itemId);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  Future<void> updateTemplateItemQuantity(
      int templateId, int itemId, int quantity) async {
    ResponseModel response = await favouriteServiceInterface
        .updateTemplateItemQuantity(templateId, itemId, quantity);
    if (response.isSuccess) {
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  Future<void> convertOrderToTemplate(
      int orderId, String templateName, String description) async {
    ResponseModel response = await favouriteServiceInterface
        .convertOrderToTemplate(orderId, templateName, description);
    if (response.isSuccess) {
      showCustomSnackBar(response.message, isError: false);
      await getTemplateList(); // Refresh template list
    } else {
      showCustomSnackBar(response.message, isError: true);
    }
  }

  // Helper method to convert favourite items to template items
  List<TemplateItem> convertFavouritesToTemplateItems() {
    List<TemplateItem> templateItems = [];
    if (_wishItemList != null) {
      for (Item? item in _wishItemList!) {
        if (item != null) {
          templateItems.add(TemplateItem(
            itemId: item.id,
            quantity: 1,
            item: item,
            price: item.price,
          ));
        }
      }
    }
    return templateItems;
  }

  // Helper method to add template items to cart
  Future<void> addTemplateToCart(TemplateModel template) async {
    final CartController cartController = Get.find<CartController>();

    try {
      print(
          'Current cart items before adding template: ${cartController.cartList.length}');

      if (template.items != null && template.items!.isNotEmpty) {
        int addedItems = 0;

        // Clear existing cart first to avoid conflicts
        await cartController.clearCartList();
        await cartController.clearCartOnline();

        for (TemplateItem templateItem in template.items!) {
          if (templateItem.item != null) {
            print('Adding item to cart: ${templateItem.item!.name}');
            print('Template item data: ${templateItem.toJson()}');

            // Validate template item
            if (templateItem.item!.id == null) {
              print('Skipping item with null ID: ${templateItem.item!.name}');
              continue;
            }

            // Create CartModel from TemplateItem with B2B pricing support
            double price =
                templateItem.price ?? templateItem.item!.price ?? 0.0;

            // Apply B2B pricing if available
            if (B2BPricingHelper.shouldUseB2BPricing()) {
              final b2bPrice =
                  B2BPricingHelper.getB2BContractPrice(templateItem.item!.id!);
              if (b2bPrice != null) {
                price = b2bPrice;
                print(
                    'Applied B2B pricing: $b2bPrice for item: ${templateItem.item!.name}');
              }
            }

            double discount = templateItem.item!.discount ?? 0.0;
            double discountPrice = PriceConverter.convertWithDiscount(
                    price, discount, templateItem.item!.discountType) ??
                price;

            // Validate and prepare data for CartModel
            List<Variation> variations =
                templateItem.variations ?? <Variation>[];
            List<List<bool?>> foodVariations = templateItem.foodVariations
                    ?.map((fv) =>
                        fv.variationValues
                            ?.map((v) => v.isSelected ?? false)
                            .toList() ??
                        <bool?>[])
                    .toList() ??
                <List<bool?>>[];
            List<double> addOnQtys = [];
            // TemplateItem doesn't have addOnIds, so we'll use default quantities
            for (var addOn in templateItem.addOns ?? []) {
              addOnQtys.add(1.0); // Default quantity of 1.0 for each addon
            }
            List<AddOn> addOnIds = templateItem.addOns
                    ?.map((addon) => AddOn(id: addon.id, quantity: 1))
                    .toList() ??
                <AddOn>[];
            List<AddOns> addOns = templateItem.addOns ?? <AddOns>[];

            print('Prepared data for CartModel:');
            print('  Variations: ${variations.length}');
            print('  Food variations: ${foodVariations.length}');
            print('  Add-on IDs: ${addOnIds.length}');
            print('  Add-ons: ${addOns.length}');

            try {
              // Ensure the item has a valid module type
              if (templateItem.item!.moduleType == null) {
                // Set a default module type based on module_id or use 'food' as fallback
                templateItem.item!.moduleType =
                    'food'; // Default to food module
                print(
                    'Set default module type to food for item: ${templateItem.item!.name}');
              }

              // Create new cart item with proper null safety
              CartModel cartModel = CartModel(
                null, // id - will be assigned by cart controller
                price, // price (now with B2B pricing if applicable)
                discountPrice, // discountedPrice
                variations, // variation
                foodVariations, // foodVariations
                (price - discountPrice), // discountAmount
                templateItem.quantity!.toDouble(), // quantity
                addOnIds, // addOnIds
                addOns, // addOns
                false, // isCampaign
                templateItem.item!.stock ?? 0, // stock
                templateItem.item, // item
                templateItem.item!.quantityLimit, // quantityLimit
              );

              print(
                  'Cart model created: ${cartModel.item?.name}, quantity: ${cartModel.quantity}');

              // Add to cart using the cart controller's method
              await cartController.addToCart(cartModel, null);

              // Also add to online cart to ensure synchronization
              await cartController.addToCartOnline(cartModel);

              print('Item added to cart successfully');
              addedItems++;
            } catch (e, stackTrace) {
              print('Error creating CartModel: $e');
              print('Stack trace: $stackTrace');
              print('Template item data: ${templateItem.toJson()}');
              continue; // Skip this item and continue with the next one
            }
          }
        }

        if (addedItems > 0) {
          // Force update the cart to ensure it's saved
          cartController.calculationCart();
          cartController.update();

          print(
              'Cart items after adding template: ${cartController.cartList.length}');
          print('Cart total amount: ${cartController.subTotal}');
          print('Cart items details:');
          for (int i = 0; i < cartController.cartList.length; i++) {
            print(
                '  Item ${i + 1}: ${cartController.cartList[i].item?.name} - Qty: ${cartController.cartList[i].quantity} - Price: ${cartController.cartList[i].price}');
          }

          // Force save cart to shared preferences
          await cartController.cartServiceInterface
              .addSharedPrefCartList(cartController.cartList);
          print('Cart saved to shared preferences');

          showCustomSnackBar('template_items_added_to_cart'.tr, isError: false);
        } else {
          showCustomSnackBar('no_valid_items_to_add'.tr, isError: true);
        }
      } else {
        showCustomSnackBar('no_items_to_add'.tr, isError: true);
      }
    } catch (e) {
      print('Error adding template to cart: $e');
      showCustomSnackBar('error_adding_template_to_cart'.tr, isError: true);
    }
  }
}
