import 'package:get/get.dart';
import 'package:sixam_mart/interfaces/repository_interface.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';

abstract class FavouriteRepositoryInterface<ResponseModel>
    implements RepositoryInterface<ResponseModel> {
  @override
  Future<ResponseModel> add(dynamic a, {bool isStore = false, int? id});
  @override
  Future<ResponseModel> delete(int? id, {bool isStore = false});

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
