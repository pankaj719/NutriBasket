import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/favourite/controllers/favourite_controller.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/features/favourite/screens/template_edit_screen.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';

class TemplateWidget extends StatelessWidget {
  final FavouriteController favouriteController;

  const TemplateWidget({super.key, required this.favouriteController});

  @override
  Widget build(BuildContext context) {
    return GetBuilder<FavouriteController>(
      builder: (controller) {
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Template Header
            Padding(
              padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(
                    'templates'.tr,
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                    ),
                  ),
                  Row(
                    children: [
                      IconButton(
                        onPressed: () => favouriteController.refreshTemplates(),
                        icon: const Icon(Icons.refresh),
                        tooltip: 'refresh_templates'.tr,
                      ),
                      const SizedBox(width: Dimensions.paddingSizeSmall),
                      CustomButton(
                        onPressed: () => _showCreateTemplateDialog(context),
                        buttonText: 'create_template'.tr,
                        width: 120,
                        height: 35,
                      ),
                    ],
                  ),
                ],
              ),
            ),

            // Template List
            if (controller.isLoadingTemplates)
              const Center(child: CircularProgressIndicator())
            else if (controller.templateList == null ||
                controller.templateList!.isEmpty)
              _buildEmptyTemplateView()
            else
              _buildTemplateList(),
          ],
        );
      },
    );
  }

  Widget _buildEmptyTemplateView() {
    return Container(
      padding: const EdgeInsets.all(Dimensions.paddingSizeLarge),
      child: Column(
        children: [
          Icon(
            Icons.list_alt_outlined,
            size: 64,
            color: Colors.grey[400],
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Text(
            'no_templates_found'.tr,
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeLarge,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Text(
            'create_your_first_template'.tr,
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

  Widget _buildTemplateList() {
    return ListView.builder(
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      itemCount: favouriteController.templateList!.length,
      itemBuilder: (context, index) {
        TemplateModel template = favouriteController.templateList![index];
        return _buildTemplateCard(template);
      },
    );
  }

  Widget _buildTemplateCard(TemplateModel template) {
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
            // Template Header
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        template.name ?? '',
                        style: robotoBold.copyWith(
                          fontSize: Dimensions.fontSizeDefault,
                        ),
                      ),
                      if (template.description != null &&
                          template.description!.isNotEmpty)
                        Text(
                          template.description!,
                          style: robotoRegular.copyWith(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Colors.grey[600],
                          ),
                        ),
                    ],
                  ),
                ),
                PopupMenuButton<String>(
                  onSelected: (value) => _handleTemplateAction(value, template),
                  itemBuilder: (context) => [
                    PopupMenuItem(
                      value: 'edit',
                      child: Row(
                        children: [
                          const Icon(Icons.edit, size: 16),
                          const SizedBox(width: 8),
                          Text('edit'.tr),
                        ],
                      ),
                    ),
                    PopupMenuItem(
                      value: 'delete',
                      child: Row(
                        children: [
                          const Icon(Icons.delete, size: 16, color: Colors.red),
                          const SizedBox(width: 8),
                          Text('delete'.tr,
                              style: const TextStyle(color: Colors.red)),
                        ],
                      ),
                    ),
                  ],
                  child: const Icon(Icons.more_vert),
                ),
              ],
            ),

            const SizedBox(height: Dimensions.paddingSizeSmall),

            // Template Items
            if (template.items != null && template.items!.isNotEmpty)
              Column(
                children: [
                  Text(
                    '${template.items!.length} ${'items'.tr}',
                    style: robotoMedium.copyWith(
                      fontSize: Dimensions.fontSizeSmall,
                      color: Colors.grey[600],
                    ),
                  ),
                  const SizedBox(height: Dimensions.paddingSizeSmall),
                  ...template.items!
                      .take(3)
                      .map((item) => _buildTemplateItem(item)),
                  if (template.items!.length > 3)
                    Text(
                      '+${template.items!.length - 3} ${'more'.tr}',
                      style: robotoRegular.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Colors.grey[500],
                      ),
                    ),
                ],
              ),

            const SizedBox(height: Dimensions.paddingSizeDefault),

            // Action Buttons
            Row(
              children: [
                Expanded(
                  child: CustomButton(
                    onPressed: () => _addTemplateToCart(template),
                    buttonText: 'add_to_cart'.tr,
                    height: 35,
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: CustomButton(
                    onPressed: () => _editTemplate(template),
                    buttonText: 'edit'.tr,
                    height: 35,
                    color: Colors.grey[300],
                    textColor: Colors.black87,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildTemplateItem(TemplateItem item) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        children: [
          if (item.item?.imageFullUrl != null)
            Container(
              width: 30,
              height: 30,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(4),
                image: DecorationImage(
                  image: NetworkImage(item.item!.imageFullUrl!),
                  fit: BoxFit.cover,
                ),
              ),
            ),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              item.item?.name ?? '',
              style: robotoRegular.copyWith(
                fontSize: Dimensions.fontSizeSmall,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          Text(
            'x${item.quantity}',
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeSmall,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }

  void _showCreateTemplateDialog(BuildContext context) {
    final TextEditingController nameController = TextEditingController();
    final TextEditingController descriptionController = TextEditingController();

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('create_template'.tr),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
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
                List<TemplateItem> items =
                    favouriteController.convertFavouritesToTemplateItems();
                favouriteController.createTemplate(
                  nameController.text,
                  descriptionController.text,
                  items,
                );
                Navigator.of(context).pop();
              } else {
                showCustomSnackBar('please_enter_template_name'.tr,
                    isError: true);
              }
            },
            buttonText: 'create'.tr,
          ),
        ],
      ),
    );
  }

  void _handleTemplateAction(String action, TemplateModel template) {
    switch (action) {
      case 'edit':
        _editTemplate(template);
        break;
      case 'delete':
        _deleteTemplate(template);
        break;
    }
  }

  void _editTemplate(TemplateModel template) {
    Get.to(() => TemplateEditScreen(template: template));
  }

  void _deleteTemplate(TemplateModel template) {
    showDialog(
      context: Get.context!,
      builder: (context) => AlertDialog(
        title: Text('delete_template'.tr),
        content: Text('are_you_sure_delete_template'.tr),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('cancel'.tr),
          ),
          CustomButton(
            onPressed: () {
              favouriteController.deleteTemplate(template.id!);
              Navigator.of(context).pop();
            },
            buttonText: 'delete'.tr,
            color: Colors.red,
          ),
        ],
      ),
    );
  }

  void _addTemplateToCart(TemplateModel template) {
    favouriteController.addTemplateToCart(template);
  }
}
