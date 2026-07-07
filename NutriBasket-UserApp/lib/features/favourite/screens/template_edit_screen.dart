import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:sixam_mart/features/favourite/controllers/favourite_controller.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/util/styles.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_app_bar.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';

class TemplateEditScreen extends StatefulWidget {
  final TemplateModel? template;

  const TemplateEditScreen({super.key, this.template});

  @override
  State<TemplateEditScreen> createState() => _TemplateEditScreenState();
}

class _TemplateEditScreenState extends State<TemplateEditScreen> {
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _descriptionController = TextEditingController();
  final FavouriteController _favouriteController =
      Get.find<FavouriteController>();

  List<TemplateItem> _templateItems = [];
  bool _isLoading = false;

  @override
  void initState() {
    super.initState();
    if (widget.template != null) {
      _nameController.text = widget.template!.name ?? '';
      _descriptionController.text = widget.template!.description ?? '';
      _templateItems = List.from(widget.template!.items ?? []);
    } else {
      // Create new template from favourites
      _templateItems = _favouriteController.convertFavouritesToTemplateItems();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: CustomAppBar(
        title:
            widget.template != null ? 'edit_template'.tr : 'create_template'.tr,
        backButton: true,
      ),
      body: Column(
        children: [
          // Template Details
          Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                TextField(
                  controller: _nameController,
                  decoration: InputDecoration(
                    labelText: 'template_name'.tr,
                    border: const OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                TextField(
                  controller: _descriptionController,
                  decoration: InputDecoration(
                    labelText: 'description'.tr,
                    border: const OutlineInputBorder(),
                  ),
                  maxLines: 3,
                ),
              ],
            ),
          ),

          // Template Items
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeDefault,
                    vertical: Dimensions.paddingSizeSmall,
                  ),
                  child: Text(
                    'template_items'.tr,
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeLarge,
                    ),
                  ),
                ),
                Expanded(
                  child: _templateItems.isEmpty
                      ? _buildEmptyItemsView()
                      : _buildTemplateItemsList(),
                ),
              ],
            ),
          ),

          // Action Buttons
          Container(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Row(
              children: [
                Expanded(
                  child: CustomButton(
                    onPressed: _isLoading ? null : _saveTemplate,
                    buttonText: _isLoading ? 'saving'.tr : 'save'.tr,
                    height: 45,
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeDefault),
                Expanded(
                  child: CustomButton(
                    onPressed: _addTemplateToCart,
                    buttonText: 'add_to_cart'.tr,
                    height: 45,
                    color: Colors.green,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyItemsView() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.shopping_cart_outlined,
            size: 64,
            color: Colors.grey[400],
          ),
          const SizedBox(height: Dimensions.paddingSizeDefault),
          Text(
            'no_items_in_template'.tr,
            style: robotoMedium.copyWith(
              fontSize: Dimensions.fontSizeLarge,
              color: Colors.grey[600],
            ),
          ),
          const SizedBox(height: Dimensions.paddingSizeSmall),
          Text(
            'add_items_from_favourites'.tr,
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

  Widget _buildTemplateItemsList() {
    return ListView.builder(
      itemCount: _templateItems.length,
      itemBuilder: (context, index) {
        TemplateItem item = _templateItems[index];
        return _buildTemplateItemCard(item, index);
      },
    );
  }

  Widget _buildTemplateItemCard(TemplateItem item, int index) {
    return Card(
      margin: const EdgeInsets.symmetric(
        horizontal: Dimensions.paddingSizeDefault,
        vertical: Dimensions.paddingSizeSmall,
      ),
      child: Padding(
        padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
        child: Row(
          children: [
            // Item Image
            if (item.item?.imageFullUrl != null)
              Container(
                width: 50,
                height: 50,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(8),
                  image: DecorationImage(
                    image: NetworkImage(item.item!.imageFullUrl!),
                    fit: BoxFit.cover,
                  ),
                ),
              ),
            const SizedBox(width: Dimensions.paddingSizeDefault),

            // Item Details
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    item.item?.name ?? '',
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (item.price != null)
                    Text(
                      '\$${item.price!.toStringAsFixed(2)}',
                      style: robotoMedium.copyWith(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Colors.grey[600],
                      ),
                    ),
                ],
              ),
            ),

            // Quantity Controls
            Row(
              children: [
                IconButton(
                  onPressed: () =>
                      _updateItemQuantity(index, item.quantity! - 1),
                  icon: const Icon(Icons.remove_circle_outline),
                  color: item.quantity! > 1 ? Colors.red : Colors.grey,
                ),
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: Dimensions.paddingSizeSmall,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    border: Border.all(color: Colors.grey[300]!),
                    borderRadius: BorderRadius.circular(4),
                  ),
                  child: Text(
                    '${item.quantity}',
                    style: robotoBold.copyWith(
                      fontSize: Dimensions.fontSizeDefault,
                    ),
                  ),
                ),
                IconButton(
                  onPressed: () =>
                      _updateItemQuantity(index, item.quantity! + 1),
                  icon: const Icon(Icons.add_circle_outline),
                  color: Colors.green,
                ),
              ],
            ),

            // Remove Button
            IconButton(
              onPressed: () => _removeItem(index),
              icon: const Icon(Icons.delete_outline),
              color: Colors.red,
            ),
          ],
        ),
      ),
    );
  }

  void _updateItemQuantity(int index, int newQuantity) {
    if (newQuantity > 0) {
      setState(() {
        _templateItems[index] =
            _templateItems[index].copyWith(quantity: newQuantity);
      });
    }
  }

  void _removeItem(int index) {
    setState(() {
      _templateItems.removeAt(index);
    });
  }

  void _saveTemplate() async {
    if (_nameController.text.isEmpty) {
      showCustomSnackBar('please_enter_template_name'.tr, isError: true);
      return;
    }

    setState(() {
      _isLoading = true;
    });

    try {
      if (widget.template != null) {
        // Update existing template
        await _favouriteController.updateTemplate(
          widget.template!.id!,
          _nameController.text,
          _descriptionController.text,
          _templateItems,
        );
      } else {
        // Create new template
        await _favouriteController.createTemplate(
          _nameController.text,
          _descriptionController.text,
          _templateItems,
        );
      }

      Get.back();
    } catch (e) {
      showCustomSnackBar('error_saving_template'.tr, isError: true);
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _addTemplateToCart() {
    if (_templateItems.isEmpty) {
      showCustomSnackBar('no_items_to_add'.tr, isError: true);
      return;
    }

    // Create a temporary template model for cart addition
    TemplateModel tempTemplate = TemplateModel(
      name: _nameController.text,
      description: _descriptionController.text,
      items: _templateItems,
    );

    _favouriteController.addTemplateToCart(tempTemplate);
    showCustomSnackBar('template_items_added_to_cart'.tr, isError: false);
  }
}
