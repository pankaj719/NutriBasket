import 'package:get/get.dart';
import 'package:sixam_mart/common/models/response_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/features/store/domain/models/store_model.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';

abstract class FavouriteServiceInterface {
  Future<Response> getFavouriteList();
  Future<ResponseModel> addFavouriteList(int? id, bool isStore);
  Future<ResponseModel> removeFavouriteList(int? id, bool isStore);
  List<Item?> wishItemList(Item item);
  List<int?> wishItemIdList(Item item);
  List<Store?> wishStoreList(dynamic store);
  List<int?> wishStoreIdList(dynamic store);

  // Template functionality
  Future<Response> getTemplateList();
  Future<ResponseModel> createTemplate(
      String name, String description, List<TemplateItem> items);
  Future<ResponseModel> updateTemplate(int templateId, String name,
      String description, List<TemplateItem> items);
  Future<ResponseModel> deleteTemplate(int templateId);
  Future<ResponseModel> addItemToTemplate(int templateId, TemplateItem item);
  Future<ResponseModel> removeItemFromTemplate(int templateId, int itemId);
  Future<ResponseModel> updateTemplateItemQuantity(
      int templateId, int itemId, int quantity);
  Future<ResponseModel> convertOrderToTemplate(
      int orderId, String templateName, String description);
}
