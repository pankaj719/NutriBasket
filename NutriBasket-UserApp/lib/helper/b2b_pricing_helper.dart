import 'package:flutter/foundation.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/profile/controllers/profile_controller.dart';
import 'package:sixam_mart/features/profile/domain/models/userinfo_model.dart';
import 'package:sixam_mart/helper/price_converter.dart';

class B2BPricingHelper {
  /// Check if current user is a B2B user
  static bool isB2BUser() {
    ProfileController profileController = Get.find<ProfileController>();
    UserInfoModel? userInfo = profileController.userInfoModel;

    // Check both isB2BUser field and userType field for B2B validation
    bool isB2BFromFlag = userInfo?.isB2BUser == true;
    bool isB2BFromType = userInfo?.userType?.toLowerCase() == 'b2b';

    if (kDebugMode) {
      print('B2BPricingHelper Debug:');
      print('  isB2BUser field: ${userInfo?.isB2BUser}');
      print('  userType field: ${userInfo?.userType}');
      print('  isB2BFromFlag: $isB2BFromFlag');
      print('  isB2BFromType: $isB2BFromType');
      print('  Final result: ${isB2BFromFlag || isB2BFromType}');
    }

    return isB2BFromFlag || isB2BFromType;
  }

  /// Get B2B contract pricing for a specific item
  static double? getB2BContractPrice(int itemId) {
    try {
      final profileController = Get.find<ProfileController>();
      final b2bPricing = profileController.userInfoModel?.b2bContractPricing;

      if (kDebugMode) {
        print('B2BPricingHelper.getB2BContractPrice Debug:');
        print('  itemId: $itemId');
        print('  b2bPricing: $b2bPricing');
        print('  contains key ${itemId.toString()}: ${b2bPricing?.containsKey(itemId.toString())}');
        if (b2bPricing != null) {
          print('  value for $itemId: ${b2bPricing[itemId.toString()]}');
        }
      }

      if (b2bPricing != null && b2bPricing.containsKey(itemId.toString())) {
        final price = b2bPricing[itemId.toString()]?.toDouble();
        if (kDebugMode) {
          print('  Returning B2B price: $price');
        }
        return price;
      }
      if (kDebugMode) {
        print('  No B2B price found, returning null');
      }
      return null;
    } catch (e) {
      if (kDebugMode) {
        print('  Error getting B2B price: $e');
      }
      return null;
    }
  }

  /// Get B2B contract pricing for a specific item variation
  static double? getB2BContractVariationPrice(
      int itemId, String variationType) {
    try {
      final profileController = Get.find<ProfileController>();
      final b2bPricing = profileController.userInfoModel?.b2bContractPricing;

      if (b2bPricing != null) {
        final itemPricing = b2bPricing[itemId.toString()];
        if (itemPricing is Map<String, dynamic> &&
            itemPricing.containsKey('variations')) {
          final variations = itemPricing['variations'] as Map<String, dynamic>;
          return variations[variationType]?.toDouble();
        }
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  /// Apply B2B pricing to an item price
  static double applyB2BPricing(double originalPrice, int itemId,
      {double? discount, String? discountType}) {
    if (!isB2BUser()) {
      // If not B2B user, apply regular pricing logic
      return PriceConverter.convertWithDiscount(
              originalPrice, discount, discountType) ??
          originalPrice;
    }

    // Get B2B contract price
    final b2bPrice = getB2BContractPrice(itemId);
    if (b2bPrice != null) {
      return b2bPrice;
    }

    // If no B2B contract price found, fall back to regular pricing
    return PriceConverter.convertWithDiscount(
            originalPrice, discount, discountType) ??
        originalPrice;
  }

  /// Apply B2B pricing to item variation
  static double applyB2BVariationPricing(
      double originalPrice, int itemId, String variationType,
      {double? discount, String? discountType}) {
    if (!isB2BUser()) {
      // If not B2B user, apply regular pricing logic
      return PriceConverter.convertWithDiscount(
              originalPrice, discount, discountType) ??
          originalPrice;
    }

    // Get B2B contract variation price
    final b2bVariationPrice =
        getB2BContractVariationPrice(itemId, variationType);
    if (b2bVariationPrice != null) {
      return b2bVariationPrice;
    }

    // If no B2B contract variation price found, fall back to regular pricing
    return PriceConverter.convertWithDiscount(
            originalPrice, discount, discountType) ??
        originalPrice;
  }

  /// Check if B2B pricing should be used for current user
  static bool shouldUseB2BPricing() {
    return isB2BUser();
  }

  /// Get user type for API calls
  static String getUserType() {
    try {
      final profileController = Get.find<ProfileController>();
      return profileController.userInfoModel?.userType ?? 'customer';
    } catch (e) {
      return 'customer';
    }
  }
}
