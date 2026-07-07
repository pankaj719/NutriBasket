import 'package:get/get.dart';
import 'package:sixam_mart/common/models/response_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/features/store/domain/models/store_model.dart';
import 'package:sixam_mart/features/favourite/domain/repositories/favourite_repository_interface.dart';
import 'package:sixam_mart/features/favourite/domain/services/favourite_service_interface.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/helper/address_helper.dart';

class FavouriteService implements FavouriteServiceInterface {
  final FavouriteRepositoryInterface favouriteRepositoryInterface;
  FavouriteService({required this.favouriteRepositoryInterface});

  @override
  Future<Response> getFavouriteList() async {
    return await favouriteRepositoryInterface.getList();
  }

  @override
  Future<ResponseModel> addFavouriteList(int? id, bool isStore) async {
    return await favouriteRepositoryInterface.add(null,
        isStore: isStore, id: id);
  }

  @override
  Future<ResponseModel> removeFavouriteList(int? id, bool isStore) async {
    return await favouriteRepositoryInterface.delete(id, isStore: isStore);
  }

  @override
  List<Item?> wishItemList(Item item) {
    List<Item?> wishItemList = [];
    for (var zone in AddressHelper.getUserAddressFromSharedPref()!.zoneData!) {
      for (var module in zone.modules!) {
        if (module.id == item.moduleId) {
          if (module.pivot!.zoneId == item.zoneId) {
            wishItemList.add(item);
          }
        }
      }
    }
    return wishItemList;
  }

  @override
  List<int?> wishItemIdList(Item item) {
    List<int?> wishItemIdList = [];
    for (var zone in AddressHelper.getUserAddressFromSharedPref()!.zoneData!) {
      for (var module in zone.modules!) {
        if (module.id == item.moduleId) {
          if (module.pivot!.zoneId == item.zoneId) {
            wishItemIdList.add(item.id);
          }
        }
      }
    }
    return wishItemIdList;
  }

  @override
  List<Store?> wishStoreList(dynamic store) {
    List<Store?> wishStoreList = [];
    for (var zone in AddressHelper.getUserAddressFromSharedPref()!.zoneData!) {
      for (var module in zone.modules!) {
        if (module.id == Store.fromJson(store).moduleId) {
          if (module.pivot!.zoneId == Store.fromJson(store).zoneId) {
            wishStoreList.add(Store.fromJson(store));
          }
        }
      }
    }
    return wishStoreList;
  }

  @override
  List<int?> wishStoreIdList(dynamic store) {
    List<int?> wishStoreIdList = [];
    for (var zone in AddressHelper.getUserAddressFromSharedPref()!.zoneData!) {
      for (var module in zone.modules!) {
        if (module.id == Store.fromJson(store).moduleId) {
          if (module.pivot!.zoneId == Store.fromJson(store).zoneId) {
            wishStoreIdList.add(Store.fromJson(store).id);
          }
        }
      }
    }
    return wishStoreIdList;
  }

  // Template functionality implementation
  @override
  Future<Response> getTemplateList() async {
    return await favouriteRepositoryInterface.getTemplateList();
  }

  @override
  Future<ResponseModel> createTemplate(
      String name, String description, List<TemplateItem> items) async {
    return await favouriteRepositoryInterface.createTemplate(
        name, description, items);
  }

  @override
  Future<ResponseModel> updateTemplate(int templateId, String name,
      String description, List<TemplateItem> items) async {
    return await favouriteRepositoryInterface.updateTemplate(
        templateId, name, description, items);
  }

  @override
  Future<ResponseModel> deleteTemplate(int templateId) async {
    return await favouriteRepositoryInterface.deleteTemplate(templateId);
  }

  @override
  Future<ResponseModel> addItemToTemplate(
      int templateId, TemplateItem item) async {
    return await favouriteRepositoryInterface.addItemToTemplate(
        templateId, item);
  }

  @override
  Future<ResponseModel> removeItemFromTemplate(
      int templateId, int itemId) async {
    return await favouriteRepositoryInterface.removeItemFromTemplate(
        templateId, itemId);
  }

  @override
  Future<ResponseModel> updateTemplateItemQuantity(
      int templateId, int itemId, int quantity) async {
    return await favouriteRepositoryInterface.updateTemplateItemQuantity(
        templateId, itemId, quantity);
  }

  @override
  Future<ResponseModel> convertOrderToTemplate(
      int orderId, String templateName, String description) async {
    return await favouriteRepositoryInterface.convertOrderToTemplate(
        orderId, templateName, description);
  }
}
