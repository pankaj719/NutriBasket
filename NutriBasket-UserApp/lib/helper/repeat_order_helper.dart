import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/helper/route_helper.dart';
import 'package:sixam_mart/util/app_constants.dart';

class RepeatOrderHelper {
  static const String _keyLastOrder = 'last_order_details';
  static const String _keyLastOrderId = 'last_order_id';

  // Store the last order details
  static Future<void> storeLastOrder(
      String orderId, List<OrderDetailsModel> orderDetails) async {
    try {
      final prefs = await SharedPreferences.getInstance();

      // Store order ID
      await prefs.setString(_keyLastOrderId, orderId);

      // Store order details
      final orderDetailsJson =
          orderDetails.map((detail) => detail.toJson()).toList();
      await prefs.setString(_keyLastOrder, jsonEncode(orderDetailsJson));

      print(
          'DEBUG: Stored last order: $orderId with ${orderDetails.length} items');
    } catch (e) {
      print('DEBUG: Error storing last order: $e');
    }
  }

  // Get the last order details
  static Future<Map<String, dynamic>?> getLastOrder() async {
    try {
      final prefs = await SharedPreferences.getInstance();

      final orderId = prefs.getString(_keyLastOrderId);
      final orderDetailsJson = prefs.getString(_keyLastOrder);

      if (orderId != null && orderDetailsJson != null) {
        final orderDetailsData = jsonDecode(orderDetailsJson) as List;
        final orderDetails = orderDetailsData
            .map((detail) => OrderDetailsModel.fromJson(detail))
            .toList();

        return {
          'orderId': orderId,
          'orderDetails': orderDetails,
        };
      }
    } catch (e) {
      print('DEBUG: Error getting last order: $e');
    }
    return null;
  }

  // Simple reorder functionality - navigate to first item
  static Future<void> reorderLastOrder() async {
    try {
      final lastOrder = await getLastOrder();

      if (lastOrder != null) {
        final orderDetails =
            lastOrder['orderDetails'] as List<OrderDetailsModel>;

        if (orderDetails.isNotEmpty) {
          final firstItem = orderDetails.first;

          if (firstItem.itemDetails != null) {
            // Navigate to the first item details
            Get.toNamed(RouteHelper.getItemDetailsRoute(
                firstItem.itemDetails!.id, false));

            // Show helpful message
            showCustomSnackBar(
              'showing_first_item_from_last_order'.tr,
              isError: false,
            );
          }
        }
      } else {
        showCustomSnackBar('no_previous_order_found'.tr, isError: true);
      }
    } catch (e) {
      print('DEBUG: Error during reorder: $e');
      showCustomSnackBar('failed_to_load_previous_order'.tr, isError: true);
    }
  }

  // Clear stored order data
  static Future<void> clearLastOrder() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.remove(_keyLastOrderId);
      await prefs.remove(_keyLastOrder);
      print('DEBUG: Cleared last order data');
    } catch (e) {
      print('DEBUG: Error clearing last order: $e');
    }
  }
}
