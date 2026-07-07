import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/favourite/controllers/favourite_controller.dart';
import 'package:sixam_mart/features/order/domain/models/order_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';

class OrderToTemplateWidget extends StatelessWidget {
  final List<OrderModel> orders;
  final FavouriteController favouriteController;

  const OrderToTemplateWidget({
    super.key,
    required this.orders,
    required this.favouriteController,
  });

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Padding(
          padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
          child: Text(
            'convert_order_to_template'.tr,
            style: robotoBold.copyWith(
              fontSize: Dimensions.fontSizeLarge,
            ),
          ),
        ),
        if (orders.isEmpty) _buildEmptyOrdersView() else _buildOrdersList(),
      ],
    );
  }

  Widget _buildEmptyOrdersView() {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
      child: Column(
        children: [
          Icon(
            Icons.receipt_long_outlined,
            size: 64,
            color: Colors.grey[400],
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Text(
            'no_orders_found'.tr,
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeLarge,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Text(
            'place_orders_to_convert_to_templates'.tr,
            style: robotoRegular.copyWith(
              fontSize: Dimensions.fontSizeDefault,
              color: Colors.grey[500],
            ),
            textAlign: TextAlign.center,
          ),
        ],
      ),
    );
  }

  Widget _buildOrdersList() {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: orders.length,
      itemBuilder: (context, index) {
        OrderModel order = orders[index];
        return _buildOrderCard(order);
      },
    );
  }

  Widget _buildOrderCard(OrderModel order) {
    return Card(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Order Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Order #${order.id}',
                        style: robotoBold.copyWith(
                          fontSize: Dimensions.fontSizeDefault,
                        ),
                      ),
                      Text(
                        order.createdAt ?? '',
                        style: robotoRegular.copyWith(
                          fontSize: Dimensions.fontSizeSmall,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeSmall,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: _getStatusColor(order.orderStatus),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    order.orderStatus ?? '',
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Colors.white,
                    ),
                  ),
                ),
              ],
            ),

            const SizedBox(height: Dimensions.paddingSizeSmall),

            // Order Details
            if (order.orderAmount != null)
              Text(
                'Total: \$${order.orderAmount!.toStringAsFixed(2)}',
                style: robotoMedium.copyWith(
                  fontSize: Dimensions.fontSizeDefault,
                  color: Colors.green[700],
                ),
              ),

            if (order.detailsCount != null)
              Text(
                '${order.detailsCount} items',
                style: robotoRegular.copyWith(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Colors.grey[600],
                ),
              ),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            // Convert Button
            CustomButton(
              onPressed: () => _showConvertOrderDialog(order),
              buttonText: 'convert_to_template'.tr,
              height: 35,
              color: Colors.blue,
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String? status) {
    switch (status?.toLowerCase()) {
      case 'delivered':
        return Colors.green;
      case 'processing':
        return Colors.orange;
      case 'pending':
        return Colors.yellow[700]!;
      case 'cancelled':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  void _showConvertOrderDialog(OrderModel order) {
    final TextEditingController nameController = TextEditingController();
    final TextEditingController descriptionController = TextEditingController();

    // Pre-fill with order details
    nameController.text = 'Order ${order.id} Template';
    descriptionController.text =
        'Template created from order #${order.id} on ${order.createdAt}';

    showDialog(
      context: Get.context!,
      builder: (context) => AlertDialog(
        title: Text('convert_order_to_template'.tr),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'This will create a template with all items from order #${order.id}',
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeDefault,
                color: Colors.grey[600],
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            TextField(
              controller: nameController,
              decoration: InputDecoration(
                labelText: 'template_name'.tr,
                border: const OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            TextField(
              controller: descriptionController,
              decoration: InputDecoration(
                labelText: 'description'.tr,
                border: const OutlineInputBorder(),
              ),
              maxLines: 3,
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('cancel'.tr),
          ),
          CustomButton(
            onPressed: () {
              if (nameController.text.isNotEmpty) {
                favouriteController.convertOrderToTemplate(
                  order.id!,
                  nameController.text,
                  descriptionController.text,
                );
                Navigator.of(context).pop();
              } else {
                showCustomSnackBar('please_enter_template_name'.tr,
                    isError: true);
              }
            },
            buttonText: 'convert'.tr,
            color: Colors.blue,
          ),
        ],
      ),
    );
  }
}
