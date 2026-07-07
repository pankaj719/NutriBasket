import 'package:get/get.dart';
import 'package:sixam_mart/common/models/response_model.dart';
import 'package:sixam_mart/api/api_client.dart';
import 'package:sixam_mart/features/favourite/domain/repositories/favourite_repository_interface.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/util/app_constants.dart';

class FavouriteRepository
    implements FavouriteRepositoryInterface<ResponseModel> {
  final ApiClient apiClient;
  FavouriteRepository({required this.apiClient});

  @override
  Future<Response> getList({int? offset}) async {
    return await apiClient.getData(AppConstants.wishListGetUri);
  }

  @override
  Future<ResponseModel> add(dynamic a, {bool isStore = false, int? id}) async {
    ResponseModel responseModel;
    Response response = await apiClient.postData(
        '${AppConstants.addWishListUri}${isStore ? 'store_id=' : 'item_id='}$id',
        null,
        handleError: false);
    if (response.statusCode == 200) {
      responseModel = ResponseModel(true, response.body['message']);
    } else {
      responseModel = ResponseModel(false, response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> delete(int? id, {bool isStore = false}) async {
    ResponseModel responseModel;
    Response response = await apiClient.deleteData(
        '${AppConstants.removeWishListUri}${isStore ? 'store_id=' : 'item_id='}$id',
        handleError: false);
    if (response.statusCode == 200) {
      responseModel = ResponseModel(true, response.body['message']);
    } else {
      responseModel = ResponseModel(false, response.statusText);
    }
    return responseModel;
  }

  @override
  Future get(String? id) {
    throw UnimplementedError();
  }

  @override
  Future update(Map<String, dynamic> body, int? id) {
    throw UnimplementedError();
  }

  // Template functionality implementation
  @override
  Future<Response> getTemplateList() async {
    return await apiClient.getData(AppConstants.templateListUri,
        handleError: false);
  }

  @override
  Future<ResponseModel> createTemplate(
      String name, String description, List<TemplateItem> items) async {
    ResponseModel responseModel;
    Map<String, dynamic> body = {
      'name': name,
      'description': description,
      'items': items
          .map((item) => {
                'item_id': item.itemId,
                'quantity': item.quantity,
                'notes': item.notes ?? '',
              })
          .toList(),
    };

    Response response = await apiClient
        .postData(AppConstants.createTemplateUri, body, handleError: false);

    if (response.statusCode == 201) {
      responseModel = ResponseModel(
          true, response.body['message'] ?? 'Template created successfully');
    } else if (response.statusCode == 403) {
      // Handle validation errors
      String errorMessage = 'Validation failed';
      if (response.body['errors'] != null) {
        List<String> errors = [];
        for (var error in response.body['errors']) {
          errors.add(error['message']);
        }
        errorMessage = errors.join(', ');
      }
      responseModel = ResponseModel(false, errorMessage);
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> updateTemplate(int templateId, String name,
      String description, List<TemplateItem> items) async {
    ResponseModel responseModel;
    Map<String, dynamic> body = {
      'name': name,
      'description': description,
      'items': items
          .map((item) => {
                'item_id': item.itemId,
                'quantity': item.quantity,
                'notes': item.notes ?? '',
              })
          .toList(),
    };

    Response response = await apiClient.putData(
        '${AppConstants.updateTemplateUri}$templateId', body,
        handleError: false);

    if (response.statusCode == 200) {
      responseModel = ResponseModel(
          true, response.body['message'] ?? 'Template updated successfully');
    } else if (response.statusCode == 404) {
      responseModel = ResponseModel(false, 'Template not found');
    } else if (response.statusCode == 403) {
      // Handle validation errors
      String errorMessage = 'Validation failed';
      if (response.body['errors'] != null) {
        List<String> errors = [];
        for (var error in response.body['errors']) {
          errors.add(error['message']);
        }
        errorMessage = errors.join(', ');
      }
      responseModel = ResponseModel(false, errorMessage);
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> deleteTemplate(int templateId) async {
    ResponseModel responseModel;
    Response response = await apiClient.deleteData(
        '${AppConstants.deleteTemplateUri}$templateId',
        handleError: false);

    if (response.statusCode == 200) {
      responseModel = ResponseModel(
          true, response.body['message'] ?? 'Template deleted successfully');
    } else if (response.statusCode == 404) {
      responseModel = ResponseModel(false, 'Template not found');
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> addItemToTemplate(
      int templateId, TemplateItem item) async {
    ResponseModel responseModel;
    Map<String, dynamic> body = {
      'item_id': item.itemId,
      'quantity': item.quantity,
      'notes': item.notes ?? '',
    };

    Response response = await apiClient.postData(
        '${AppConstants.updateTemplateUri}$templateId/items', body,
        handleError: false);

    if (response.statusCode == 200) {
      responseModel = ResponseModel(true,
          response.body['message'] ?? 'Item added to template successfully');
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> removeItemFromTemplate(
      int templateId, int itemId) async {
    ResponseModel responseModel;
    Response response = await apiClient.deleteData(
        '${AppConstants.updateTemplateUri}$templateId/items/$itemId',
        handleError: false);

    if (response.statusCode == 200) {
      responseModel = ResponseModel(
          true,
          response.body['message'] ??
              'Item removed from template successfully');
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> updateTemplateItemQuantity(
      int templateId, int itemId, int quantity) async {
    ResponseModel responseModel;
    Map<String, dynamic> body = {'quantity': quantity};

    Response response = await apiClient.putData(
        '${AppConstants.updateTemplateUri}$templateId/items/$itemId', body,
        handleError: false);

    if (response.statusCode == 200) {
      responseModel = ResponseModel(true,
          response.body['message'] ?? 'Item quantity updated successfully');
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }

  @override
  Future<ResponseModel> convertOrderToTemplate(
      int orderId, String templateName, String description) async {
    ResponseModel responseModel;
    Map<String, dynamic> body = {
      'order_id': orderId,
      'name': templateName,
      'description': description,
    };

    Response response = await apiClient.postData(
        AppConstants.convertOrderToTemplateUri, body,
        handleError: false);

    if (response.statusCode == 201) {
      responseModel = ResponseModel(
          true,
          response.body['message'] ??
              'Template created from order successfully');
    } else if (response.statusCode == 404) {
      responseModel = ResponseModel(false, 'Order not found or not accessible');
    } else if (response.statusCode == 403) {
      // Handle validation errors
      String errorMessage = 'Validation failed';
      if (response.body['errors'] != null) {
        List<String> errors = [];
        for (var error in response.body['errors']) {
          errors.add(error['message']);
        }
        errorMessage = errors.join(', ');
      }
      responseModel = ResponseModel(false, errorMessage);
    } else {
      responseModel =
          ResponseModel(false, response.body['message'] ?? response.statusText);
    }
    return responseModel;
  }
}
