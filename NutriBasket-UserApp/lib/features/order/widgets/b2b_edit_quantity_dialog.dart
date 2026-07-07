import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/common/widgets/custom_text_field.dart';
import 'package:sixam_mart/common/widgets/custom_image.dart';
import 'package:sixam_mart/features/order/controllers/order_controller.dart';
import 'package:sixam_mart/features/order/domain/models/order_details_model.dart';
import 'package:sixam_mart/features/order/domain/models/order_model.dart';
import 'package:sixam_mart/features/profile/controllers/profile_controller.dart';
import 'package:sixam_mart/helper/price_converter.dart';
import 'package:sixam_mart/helper/b2b_pricing_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';

class B2bEditQuantityDialog extends StatefulWidget {
  final List<OrderDetailsModel> orderDetails;
  final OrderModel order;
  final String orderId;

  const B2bEditQuantityDialog({
    super.key,
    required this.orderDetails,
    required this.order,
    required this.orderId,
  });

  @override
  State<B2bEditQuantityDialog> createState() => _B2bEditQuantityDialogState();
}

class _B2bEditQuantityDialogState extends State<B2bEditQuantityDialog> {
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

  Map<String, dynamic> _getChanges() {
    Map<String, dynamic> changes = {};

    for (int i = 0; i < widget.orderDetails.length; i++) {
      final currentValue =
          double.tryParse(_quantityControllers[i]?.text ?? '0') ?? 0.0;
      final originalValue = _originalQuantities[i] ?? 0.0;

      if (currentValue != originalValue && currentValue >= 0) {
        changes['item_${widget.orderDetails[i].id}'] = currentValue;
      }
    }

    if (_reasonController.text.trim().isNotEmpty) {
      changes['reason'] = _reasonController.text.trim();
    }

    return changes;
  }

  Future<void> _submitChanges() async {
    if (!_hasChanges()) {
      showCustomSnackBar('no_changes_made'.tr, isError: true);
      return;
    }

    // Check if user is B2B before making request
    final profileController = Get.find<ProfileController>();
    final isB2BUser = B2BPricingHelper.isB2BUser();

    debugPrint('====> B2B Edit Debug Info:');
    debugPrint('User ID: ${profileController.userInfoModel?.id}');
    debugPrint('User Type: ${profileController.userInfoModel?.userType}');
    debugPrint('Is B2B User: ${profileController.userInfoModel?.isB2BUser}');
    debugPrint('B2B Helper Check: $isB2BUser');

    if (!isB2BUser) {
      showCustomSnackBar('Only B2B users can edit orders after delivery',
          isError: true);
      return;
    }

    // First check if the order is editable by calling the B2B editable order endpoint
    debugPrint('====> Checking B2B editable order for: ${widget.orderId}');
    await Get.find<OrderController>().getB2bEditableOrder(widget.orderId);
    final editableOrderData = Get.find<OrderController>().b2bEditableOrderData;

    if (editableOrderData == null) {
      showCustomSnackBar(
          'Order is not editable or you do not have permission to edit this order',
          isError: true);
      return;
    }

    debugPrint('====> B2B Editable Order Data: $editableOrderData');

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

      debugPrint('====> B2B API Request Body: $changes');

      bool success = await Get.find<OrderController>().submitB2bOrderChanges(
        widget.orderId,
        changes,
      );

      if (success) {
        Get.back();
        
        // Refresh order data after successful submission
        // Order should now be acknowledged and move to history
        await Get.find<OrderController>().getRunningOrders(1);
        await Get.find<OrderController>().getHistoryOrders(1);
        
        // Track the order to get updated status
        await Get.find<OrderController>().trackOrder(
          widget.orderId, 
          null, 
          false,
        );
        
        // Fetch B2B change requests to show in UI
        await Get.find<OrderController>().getB2bChangeRequests(widget.orderId);
        
        showCustomSnackBar(
          'b2b_order_change_submitted'.tr,
          isError: false,
        );
      }
    } catch (e) {
      debugPrint('====> B2B Error: $e');
      showCustomSnackBar('failed_to_submit_request'.tr, isError: true);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    String timeRemaining = Get.find<OrderController>()
        .getTimeRemainingForAcknowledgment(widget.order);

    return Dialog(
      child: ConstrainedBox(
        constraints: BoxConstraints(
          maxWidth: MediaQuery.of(context).size.width * 0.9,
          maxHeight: MediaQuery.of(context).size.height * 0.8,
        ),
        child: Container(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Header
              Row(
                children: [
                  Icon(
                    Icons.edit,
                    color: Theme.of(context).primaryColor,
                    size: 24,
                  ),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  Expanded(
                    child: Text(
                      'edit_quantities_after_delivery'.tr,
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeDefault,
                      ),
                    ),
                  ),
                  IconButton(
                    onPressed: () => Get.back(),
                    icon: const Icon(Icons.close),
                  ),
                ],
              ),
              const SizedBox(height: Dimensions.paddingSizeSmall),

              // Time remaining warning
              if (timeRemaining.isNotEmpty)
                Container(
                  padding: const EdgeInsets.all(Dimensions.paddingSizeSmall),
                  decoration: BoxDecoration(
                    color: Colors.orange.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(Dimensions.radiusSmall),
                    border: Border.all(color: Colors.orange),
                  ),
                  child: Row(
                    children: [
                      Icon(Icons.access_time, color: Colors.orange, size: 20),
                      const SizedBox(width: Dimensions.paddingSizeSmall),
                      Expanded(
                        child: Text(
                          '${'order_will_be_acknowledged_in'.tr} $timeRemaining',
                          style: robotoMedium.copyWith(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Colors.orange,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),

              const SizedBox(height: Dimensions.paddingSizeDefault),

              // Quantity list
              Expanded(
                child: ListView.builder(
                  itemCount: widget.orderDetails.length,
                  itemBuilder: (context, index) {
                    final orderDetail = widget.orderDetails[index];
                    final item = orderDetail.itemDetails;

                    return Card(
                      margin: const EdgeInsets.only(
                          bottom: Dimensions.paddingSizeSmall),
                      child: Padding(
                        padding:
                            const EdgeInsets.all(Dimensions.paddingSizeSmall),
                        child: Row(
                          children: [
                            // Item image
                            ClipRRect(
                              borderRadius:
                                  BorderRadius.circular(Dimensions.radiusSmall),
                              child: CustomImage(
                                image: item?.imageFullUrl ?? '',
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
                                    item?.name ?? 'Unknown Item',
                                    style: robotoMedium.copyWith(
                                      fontSize: Dimensions.fontSizeSmall,
                                    ),
                                    maxLines: 2,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    PriceConverter.convertPrice(
                                        orderDetail.price),
                                    style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeExtraSmall,
                                      color: Theme.of(context).disabledColor,
                                    ),
                                  ),
                                  const SizedBox(height: 4),
                                  Text(
                                    '${'original'.tr}: ${_originalQuantities[index]}',
                                    style: robotoRegular.copyWith(
                                      fontSize: Dimensions.fontSizeExtraSmall,
                                      color: Theme.of(context).hintColor,
                                    ),
                                  ),
                                ],
                              ),
                            ),

                            // Quantity input
                            SizedBox(
                              width: 80,
                              child: CustomTextField(
                                controller: _quantityControllers[index],
                                inputType: const TextInputType.numberWithOptions(decimal: true),
                                inputAction: TextInputAction.next,
                                isAmount: true,
                                hintText: '0',
                                showTitle: false,
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),

              const SizedBox(height: Dimensions.paddingSizeDefault),

              // Reason input
              CustomTextField(
                controller: _reasonController,
                titleText: 'reason_for_change'.tr,
                hintText: 'enter_reason'.tr,
                maxLines: 3,
                inputType: TextInputType.multiline,
                inputAction: TextInputAction.done,
              ),

              const SizedBox(height: Dimensions.paddingSizeDefault),

              // Action buttons
              Row(
                children: [
                  Expanded(
                    child: TextButton(
                      onPressed: () => Get.back(),
                      style: TextButton.styleFrom(
                        padding: const EdgeInsets.symmetric(
                            vertical: Dimensions.paddingSizeDefault),
                      ),
                      child: Text(
                        'cancel_changes'.tr,
                        style: robotoMedium.copyWith(
                          color: Theme.of(context).disabledColor,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: Dimensions.paddingSizeSmall),
                  Expanded(
                    child: CustomButton(
                      buttonText: 'submit_changes'.tr,
                      isLoading: _isLoading,
                      onPressed: _hasChanges() ? _submitChanges : null,
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
