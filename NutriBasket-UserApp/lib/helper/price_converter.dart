import 'package:animated_flip_counter/animated_flip_counter.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart' as intl;
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';

class PriceConverter {
  static String convertPrice(double? price,
      {double? discount,
      String? discountType,
      bool forDM = false,
      bool isFoodVariation = false,
      String? formatedStringPrice,
      bool forTaxi = false,
      int? itemId}) {
    if (discount != null && discountType != null) {
      if (discountType == 'amount' && !isFoodVariation) {
        price = price! - discount;
      } else if (discountType == 'percent') {
        price = price! - ((discount / 100) * price);
      }
    }

    // Apply B2B pricing if user is B2B and itemId is provided
    if (itemId != null && B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice = B2BPricingHelper.getB2BContractPrice(itemId);
      if (b2bPrice != null) {
        price = b2bPrice;
      }
    }

    bool isRightSide =
        Get.find<SplashController>().configModel!.currencySymbolDirection ==
            'right';

    if (forTaxi && price! > 100000) {
      return '${isRightSide ? '' : '${Get.find<SplashController>().configModel!.currencySymbol!} '}'
          '${intl.NumberFormat.compact().format(price)}'
          '${isRightSide ? ' ${Get.find<SplashController>().configModel!.currencySymbol!}' : ''}';
    }
    return '${isRightSide ? '' : '${Get.find<SplashController>().configModel!.currencySymbol!} '}'
        '${formatedStringPrice ?? toFixed(price!).toStringAsFixed(forDM ? 0 : Get.find<SplashController>().configModel!.digitAfterDecimalPoint!).replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]},')}'
        '${isRightSide ? ' ${Get.find<SplashController>().configModel!.currencySymbol!}' : ''}';
  }

  static Widget convertAnimationPrice(double? price,
      {double? discount,
      String? discountType,
      bool forDM = false,
      TextStyle? textStyle,
      int? itemId}) {
    if (discount != null && discountType != null) {
      if (discountType == 'amount') {
        price = price! - discount;
      } else if (discountType == 'percent') {
        price = price! - ((discount / 100) * price);
      }
    }

    // Apply B2B pricing if user is B2B and itemId is provided
    if (itemId != null && B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice = B2BPricingHelper.getB2BContractPrice(itemId);
      if (b2bPrice != null) {
        price = b2bPrice;
      }
    }

    bool isRightSide =
        Get.find<SplashController>().configModel!.currencySymbolDirection ==
            'right';
    return Directionality(
      textDirection: TextDirection.ltr,
      child: AnimatedFlipCounter(
        duration: const Duration(milliseconds: 500),
        value: toFixed(price!),
        textStyle: textStyle ?? robotoMedium,
        fractionDigits: forDM
            ? 0
            : Get.find<SplashController>().configModel!.digitAfterDecimalPoint!,
        prefix: isRightSide
            ? ''
            : '${Get.find<SplashController>().configModel!.currencySymbol!} ',
        suffix: isRightSide
            ? '${Get.find<SplashController>().configModel!.currencySymbol!} '
            : '',
      ),
    );
  }

  static double? convertWithDiscount(
      double? price, double? discount, String? discountType,
      {bool isFoodVariation = false, int? itemId}) {
    if (discountType == 'amount' && !isFoodVariation) {
      price = price! - discount!;
    } else if (discountType == 'percent') {
      price = price! - ((discount! / 100) * price);
    }

    // Apply B2B pricing if user is B2B and itemId is provided
    if (itemId != null && B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bPrice = B2BPricingHelper.getB2BContractPrice(itemId);
      if (b2bPrice != null) {
        return b2bPrice;
      }
    }

    return price;
  }

  static double calculation(
      double amount, double? discount, String type, double quantity) {
    double calculatedAmount = 0;
    if (type == 'amount' || type == 'fixed') {
      calculatedAmount = discount! * quantity;
    } else if (type == 'percent') {
      calculatedAmount = (discount! / 100) * (amount * quantity);
    }
    return calculatedAmount;
  }

  static String percentageCalculation(
      String price, String discount, String discountType) {
    return '$discount${discountType == 'percent' ? '%' : Get.find<SplashController>().configModel!.currencySymbol} OFF';
  }

  static double toFixed(double price) {
    return double.parse(price.toStringAsFixed(
        Get.find<SplashController>().configModel!.digitAfterDecimalPoint!));
  }

  static double? convertWithDiscountForVariation(
      double? price, double? discount, String? discountType,
      {bool isFoodVariation = false, int? itemId, String? variationType}) {
    if (discountType == 'amount' && !isFoodVariation) {
      price = price! - discount!;
    } else if (discountType == 'percent') {
      price = price! - ((discount! / 100) * price);
    }

    // Apply B2B pricing if user is B2B and itemId and variationType are provided
    if (itemId != null &&
        variationType != null &&
        B2BPricingHelper.shouldUseB2BPricing()) {
      final b2bVariationPrice =
          B2BPricingHelper.getB2BContractVariationPrice(itemId, variationType);
      if (b2bVariationPrice != null) {
        return b2bVariationPrice;
      }
    }

    return price;
  }
}
