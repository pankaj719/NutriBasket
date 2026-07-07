import 'package:sixam_mart_delivery/features/auth/controllers/auth_controller.dart';
import 'package:sixam_mart_delivery/features/profile/controllers/profile_controller.dart';
import 'package:sixam_mart_delivery/helper/route_helper.dart';
import 'package:sixam_mart_delivery/common/widgets/custom_snackbar_widget.dart';
import 'package:get/get.dart';

class ApiChecker {
  static void checkApi(Response response) {
    if (response.statusCode == 401) {
      Get.find<AuthController>().clearSharedData();
      Get.find<ProfileController>().stopLocationRecord();
      Get.offAllNamed(RouteHelper.getSignInRoute());
    } else if (response.statusCode == 404) {
      // Handle 404 errors more gracefully
      showCustomSnackBar(
          'Service temporarily unavailable. Please try again later.',
          isError: true);
    } else if (response.statusCode == 500) {
      showCustomSnackBar('Server error. Please try again later.',
          isError: true);
    } else if (response.statusCode == 403) {
      showCustomSnackBar('Access denied. Please contact support.',
          isError: true);
    } else if (response.statusCode == 422) {
      // Validation errors
      String message = 'Validation error';
      if (response.body != null && response.body is Map) {
        if (response.body['message'] != null) {
          message = response.body['message'];
        } else if (response.body['errors'] != null) {
          message = response.body['errors'].toString();
        }
      }
      showCustomSnackBar(message, isError: true);
    } else {
      // For other errors, show the status text if available, otherwise a generic message
      String errorMessage =
          response.statusText ?? 'An error occurred. Please try again.';
      showCustomSnackBar(errorMessage, isError: true);
    }
  }
}
