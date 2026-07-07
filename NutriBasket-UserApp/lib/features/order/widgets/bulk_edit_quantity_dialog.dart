import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/features/order/controllers/order_controller.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_change_request_model.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class BulkEditQuantityDialog extends StatefulWidget {
  final List<OrderDetailsModel> orderDetails;
  final String orderId;

  const BulkEditQuantityDialog({
    super.key,
    required this.orderDetails,
    required this.orderId,
  });

  @override
  State<BulkEditQuantityDialog> createState() => _BulkEditQuantityDialogState();
}

class _BulkEditQuantityDialogState extends State<BulkEditQuantityDialog> {
  final Map<int, TextEditingController> _quantityControllers = {};
  final Map<int, double> _originalQuantities = {};
  final TextEditingController _reasonController = TextEditingController();
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    _initializeControllers();
  }

  void _initializeControllers() {
    for (int i = 0; i < widget.orderDetails.length; i++) {
      final orderDetail = widget.orderDetails[i];
      final quantity = orderDetail.quantity ?? 0.0;
      _quantityControllers[i] =
          TextEditingController(text: quantity.toString());
      _originalQuantities[i] = quantity;
    }
  }

  @override
  void dispose() {
    _quantityControllers.values.forEach((controller) => controller.dispose());
    _reasonController.dispose();
    super.dispose();
  }

  bool _hasChanges() {
    for (int i = 0; i < widget.orderDetails.length; i++) {
      final currentValue =
          double.tryParse(_quantityControllers[i]?.text ?? '0') ?? 0.0;
      final originalValue = _originalQuantities[i] ?? 0.0;
      if (currentValue != originalValue) {
        return true;
      }
    }
    return false;
  }

  List<OrderChangeRequestBodyModel> _getChanges() {
    List<OrderChangeRequestBodyModel> changes = [];

    for (int i = 0; i < widget.orderDetails.length; i++) {
      final currentValue =
          double.tryParse(_quantityControllers[i]?.text ?? '0') ?? 0.0;
      final originalValue = _originalQuantities[i] ?? 0.0;

      if (currentValue != originalValue && currentValue > 0) {
        changes.add(OrderChangeRequestBodyModel(
          itemId: widget.orderDetails[i].id,
          newQuantity: currentValue,
          reason: _reasonController.text.trim().isEmpty
              ? null
              : _reasonController.text.trim(),
        ));
      }
    }

    return changes;
  }

  Future<void> _submitChanges() async {
    if (!_hasChanges()) {
      showCustomSnackBar('no_changes_made'.tr, isError: true);
      return;
    }

    // Validate quantities
    for (int i = 0; i < widget.orderDetails.length; i++) {
      final quantity =
          double.tryParse(_quantityControllers[i]?.text ?? '0') ?? 0.0;
      if (quantity < 0) {
        showCustomSnackBar('quantity_cannot_be_zero'.tr, isError: true);
        return;
      }
    }

    setState(() {
      _isLoading = true;
    });

    try {
      final changes = _getChanges();

      if (changes.isEmpty) {
        showCustomSnackBar('no_changes_made'.tr, isError: true);
        setState(() {
          _isLoading = false;
        });
        return;
      }

      bool success;
      if (changes.length == 1) {
        // Use single item endpoint for single change
        final change = changes.first;
        success = await Get.find<OrderController>().submitOrderChangeRequest(
          widget.orderId,
          change.itemId.toString(),
          change.newQuantity!,
        );
      } else {
        // Use bulk endpoint for multiple changes
        success =
            await Get.find<OrderController>().submitBulkOrderChangeRequest(
          widget.orderId,
          changes,
        );
      }

      if (success) {
        Get.back();
        
        // Refresh order data after successful submission
        // If the order was delivered and acknowledged, it should move to history
        await Get.find<OrderController>().getRunningOrders(1);
        await Get.find<OrderController>().getHistoryOrders(1);
        
        // Track the order to get updated status
        await Get.find<OrderController>().trackOrder(
          widget.orderId, 
          null, 
          false,
        );
        
        showCustomSnackBar(
          changes.length == 1
              ? 'order_change_request_submitted'.tr
              : 'bulk_order_change_request_submitted'.tr,
          isError: false,
        );
      }
    } catch (e) {
      showCustomSnackBar('failed_to_submit_request'.tr, isError: true);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.9,
          maxHeight: MediaQuery.of(context).size.height * 0.8,
        ),
        child: Container(
          padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
          decoration: BoxDecoration(
            color: Theme.of(context).cardColor,
            borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'edit_order_quantities'.tr,
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                      color: Theme.of(context).primaryColor,
                    ),
                  ),
                  IconButton(
                    onPressed: () => Get.back(),
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
              const SizedBox(height: Dimensions.paddingSizeDefault),

              // Items list
              Flexible(
                child: ListView.builder(
                  shrinkWrap: true,
                  itemCount: widget.orderDetails.length,
                  itemBuilder: (context, index) {
                    final orderDetail = widget.orderDetails[index];
                    return Container(
                      margin: const EdgeInsets.only(
                          bottom: Dimensions.paddingSizeSmall),
                      padding:
                          const EdgeInsets.all(Dimensions.paddingSizeSmall),
                      decoration: BoxDecoration(
                        border:
                            Border.all(color: Theme.of(context).dividerColor),
                        borderRadius:
                            BorderRadius.circular(Dimensions.radiusSmall),
                      ),
                      child: Row(
                        children: [
                          // Item image
                          ClipRRect(
                            borderRadius:
                                BorderRadius.circular(Dimensions.radiusSmall),
                            child: CustomImage(
                              image: orderDetail.imageFullUrl ?? '',
                              height: 50,
                              width: 50,
                              fit: BoxFit.cover,
                            ),
                          ),
                          const SizedBox(width: Dimensions.paddingSizeSmall),

                          // Item details
                          Expanded(
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Text(
                                  orderDetail.itemDetails?.name ??
                                      'Unknown Item',
                                  style: robotoMedium.copyWith(
                                    fontSize: Dimensions.fontSizeDefault,
                                  ),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(
                                    height: Dimensions.paddingSizeExtraSmall),
                                Text(
                                  PriceConverter.convertPrice(
                                      orderDetail.price),
                                  style: robotoRegular.copyWith(
                                    fontSize: Dimensions.fontSizeSmall,
                                    color: Theme.of(context).primaryColor,
                                  ),
                                ),
                              ],
                            ),
                          ),

                          // Quantity input
                          Container(
                            width: 80,
                            child: CustomTextField(
                              controller: _quantityControllers[index]!,
                              hintText: 'quantity'.tr,
                              inputType: const TextInputType.numberWithOptions(
                                  decimal: true),
                              textAlign: TextAlign.center,
                              isAmount:
                                  true, // This enables decimal input with proper formatting
                              onChanged: (value) {
                                setState(
                                    () {}); // Refresh to show changes indicator
                              },
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: Dimensions.paddingSizeDefault),

              // Reason field
              CustomTextField(
                controller: _reasonController,
                hintText: 'reason_for_change'.tr,
                maxLines: 2,
                capitalization: TextCapitalization.sentences,
              ),

              const SizedBox(height: Dimensions.paddingSizeLarge),

              // Buttons
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: _isLoading ? null : () => Get.back(),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(
                            vertical: Dimensions.paddingSizeDefault),
                        shape: RoundedRectangleBorder(
                          borderRadius:
                              BorderRadius.circular(Dimensions.radiusSmall),
                          side:
                              BorderSide(color: Theme.of(context).primaryColor),
                        ),
                      ),
                      child: Text(
                        'cancel_changes'.tr,
                        style: robotoMedium.copyWith(
                          color: Theme.of(context).primaryColor,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: Dimensions.paddingSizeDefault),
                  Expanded(
                    child: CustomButton(
                      buttonText: 'submit_changes'.tr,
                      isLoading: _isLoading,
                      onPressed:
                          _hasChanges() && !_isLoading ? _submitChanges : null,
                    ),
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
