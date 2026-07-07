import 'package:sixam_mart/features/favourite/controllers/favourite_controller.dart';
import 'package:sixam_mart/helper/responsive_helper.dart';
import 'package:sixam_mart/util/dimensions.dart';
import 'package:sixam_mart/common/widgets/footer_view.dart';
import 'package:sixam_mart/common/widgets/item_view.dart';
import 'package:sixam_mart/common/widgets/custom_button.dart';
import 'package:sixam_mart/common/widgets/custom_snackbar.dart';
import 'package:sixam_mart/features/favourite/domain/models/template_model.dart';
import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'package:sixam_mart/features/auth/controllers/auth_controller.dart';
import 'package:flutter/material.dart';
import 'package:get/get.dart';

class FavItemViewWidget extends StatefulWidget {
  final bool isStore;
  final bool isSearch;
  const FavItemViewWidget(
      {super.key, required this.isStore, this.isSearch = false});

  @override
  State<FavItemViewWidget> createState() => _FavItemViewWidgetState();
}

class _FavItemViewWidgetState extends State<FavItemViewWidget> {
  final FavouriteController favouriteController =
      Get.find<FavouriteController>();
  List<TemplateItem> _templateItems = [];
  bool _showTemplateMode = false;
  final TextEditingController _templateNameController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _templateNameController.text = 'My Template';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: GetBuilder<FavouriteController>(builder: (favouriteController) {
        return RefreshIndicator(
          onRefresh: () async {
            await favouriteController.getFavouriteList();
            await favouriteController.getTemplateList();
          },
          child: SingleChildScrollView(
            physics: const AlwaysScrollableScrollPhysics(),
            child: FooterView(
              child: SizedBox(
                width: Dimensions.webMaxWidth,
                child: Padding(
                  padding: EdgeInsets.only(
                      bottom: ResponsiveHelper.isDesktop(context) ? 0 : 80.0),
                  child: Column(
                    children: [
                      // Template Mode Toggle
                      if (!widget.isStore) _buildTemplateModeToggle(),

                      // Template Items View (when in template mode)
                      if (_showTemplateMode && !widget.isStore)
                        _buildTemplateItemsView(),

                      // Regular Favourites View
                      if (!_showTemplateMode || widget.isStore)
                        ItemsView(
                          isStore: widget.isStore,
                          items: favouriteController.wishItemList,
                          stores: favouriteController.wishStoreList,
                          noDataText: 'no_wish_data_found'.tr,
                          isFeatured: true,
                        ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        );
      }),
    );
  }

  Widget _buildTemplateModeToggle() {
    return Container(
      margin: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
      decoration: BoxDecoration(
        color: Theme.of(context).cardColor,
        borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
        border: Border.all(color: Colors.grey[300]!),
      ),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Text(
                'template_mode'.tr,
                style: TextStyle(
                  fontSize: Dimensions.fontSizeLarge,
                  fontWeight: FontWeight.bold,
                ),
              ),
              Switch(
                value: _showTemplateMode,
                onChanged: (value) {
                  setState(() {
                    _showTemplateMode = value;
                    if (!_showTemplateMode) {
                      _templateItems.clear();
                    }
                  });
                },
              ),
            ],
          ),
          if (_showTemplateMode) ...[
            const SizedBox(height: Dimensions.paddingSizeDefault),
            TextField(
              controller: _templateNameController,
              decoration: InputDecoration(
                labelText: 'template_name'.tr,
                border: const OutlineInputBorder(),
                hintText: 'Enter template name',
              ),
            ),
            const SizedBox(height: Dimensions.paddingSizeDefault),
            Row(
              children: [
                Expanded(
                  child: CustomButton(
                    onPressed: _templateItems.isEmpty ? null : _saveTemplate,
                    buttonText: 'save_template'.tr,
                    height: 40,
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: CustomButton(
                    onPressed:
                        _templateItems.isEmpty ? null : _addTemplateToCart,
                    buttonText: 'add_to_cart'.tr,
                    height: 40,
                    color: Colors.green,
                  ),
                ),
              ],
            ),
          ],
        ],
      ),
    );
  }

  Widget _buildTemplateItemsView() {
    return Column(
      children: [
        // Saved Templates from Database
        if (favouriteController.templateList != null &&
            favouriteController.templateList!.isNotEmpty)
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Text(
                  'saved_templates'.tr,
                  style: TextStyle(
                    fontSize: Dimensions.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ),
              ListView.builder(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: favouriteController.templateList!.length,
                itemBuilder: (context, index) {
                  TemplateModel template =
                      favouriteController.templateList![index];
                  return _buildSavedTemplateCard(template);
                },
              ),
              const Divider(),
            ],
          )
        else if (!favouriteController.isLoadingTemplates)
          Container(
            margin: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            decoration: BoxDecoration(
              color: Colors.grey[50],
              borderRadius: BorderRadius.circular(Dimensions.radiusDefault),
              border: Border.all(color: Colors.grey[300]!),
            ),
            child: Column(
              children: [
                Icon(
                  Icons.list_alt_outlined,
                  size: 48,
                  color: Colors.grey[400],
                ),
                const SizedBox(height: Dimensions.paddingSizeDefault),
                Text(
                  'no_saved_templates'.tr,
                  style: TextStyle(
                    fontSize: Dimensions.fontSizeLarge,
                    fontWeight: FontWeight.bold,
                    color: Colors.grey[600],
                  ),
                ),
                const SizedBox(height: Dimensions.paddingSizeSmall),
                Text(
                  'create_your_first_template'.tr,
                  style: TextStyle(
                    fontSize: Dimensions.fontSizeDefault,
                    color: Colors.grey[500],
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),

        // Current Template Items List (for creating new template)
        if (_templateItems.isNotEmpty) ...[
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Text(
              'current_template_items'.tr,
              style: TextStyle(
                fontSize: Dimensions.fontSizeLarge,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          ListView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: _templateItems.length,
            itemBuilder: (context, index) {
              TemplateItem item = _templateItems[index];
              return _buildTemplateItemCard(item, index);
            },
          ),
        ],

        // Available Favourites to Add
        if (favouriteController.wishItemList != null &&
            favouriteController.wishItemList!.isNotEmpty)
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Padding(
                padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
                child: Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Text(
                      'add_from_favourites'.tr,
                      style: TextStyle(
                        fontSize: Dimensions.fontSizeLarge,
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    Text(
                      'tap_items_to_add'.tr,
                      style: TextStyle(
                        fontSize: Dimensions.fontSizeSmall,
                        color: Colors.grey[600],
                      ),
                    ),
                  ],
                ),
              ),
              ItemsView(
                isStore: false,
                items: favouriteController.wishItemList,
                stores: null,
                noDataText: 'no_wish_data_found'.tr,
                isFeatured: true,
              ),
            ],
          ),

        // Add All Favourites Button
        if (favouriteController.wishItemList != null &&
            favouriteController.wishItemList!.isNotEmpty)
          Padding(
            padding: const EdgeInsets.all(Dimensions.paddingSizeDefault),
            child: Row(
              children: [
                Expanded(
                  child: CustomButton(
                    onPressed: _addAllFavouritesToTemplate,
                    buttonText: 'add_all_favourites'.tr,
                    height: 40,
                    color: Colors.blue,
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: CustomButton(
                    onPressed: _showAddIndividualItemsDialog,
                    buttonText: 'add_selected'.tr,
                    height: 40,
                    color: Colors.orange,
                  ),
                ),
              ],
            ),
          ),
      ],
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
                    style: TextStyle(
                      fontSize: Dimensions.fontSizeDefault,
                      fontWeight: FontWeight.bold,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (item.price != null)
                    Text(
                      '\$${item.price!.toStringAsFixed(2)}',
                      style: TextStyle(
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
                    style: TextStyle(
                      fontSize: Dimensions.fontSizeDefault,
                      fontWeight: FontWeight.bold,
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
              onPressed: () => _removeItemFromTemplate(index),
              icon: const Icon(Icons.delete_outline),
              color: Colors.red,
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildSavedTemplateCard(TemplateModel template) {
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
                        style: TextStyle(
                          fontSize: Dimensions.fontSizeLarge,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      if (template.description != null &&
                          template.description!.isNotEmpty) ...[
                        const SizedBox(height: Dimensions.paddingSizeSmall),
                        Text(
                          template.description!,
                          style: TextStyle(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ],
                  ),
                ),
                PopupMenuButton<String>(
                  onSelected: (value) {
                    switch (value) {
                      case 'edit':
                        if (template.items != null) {
                          _templateItems = List.from(template.items!);
                        }
                        _templateNameController.text = template.name ?? '';
                        setState(() {
                          _showTemplateMode = true;
                        });
                        break;
                      case 'delete':
                        if (template.id != null) {
                          _removeTemplate(template.id!);
                        }
                        break;
                    }
                  },
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

            // Template Items Preview
            if (template.items != null && template.items!.isNotEmpty) ...[
              Text(
                '${template.items!.length} ${'items'.tr}',
                style: TextStyle(
                  fontSize: Dimensions.fontSizeSmall,
                  color: Colors.grey[600],
                ),
              ),
              const SizedBox(height: Dimensions.paddingSizeSmall),

              // Show first 3 items
              ...template.items!.take(3).map((item) => Padding(
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
                            style: TextStyle(
                              fontSize: Dimensions.fontSizeSmall,
                            ),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                        Text(
                          'x${item.quantity}',
                          style: TextStyle(
                            fontSize: Dimensions.fontSizeSmall,
                            color: Colors.grey[600],
                          ),
                        ),
                      ],
                    ),
                  )),

              if (template.items!.length > 3)
                Text(
                  '+${template.items!.length - 3} ${'more'.tr}',
                  style: TextStyle(
                    fontSize: Dimensions.fontSizeSmall,
                    color: Colors.grey[500],
                  ),
                ),
            ],

            const SizedBox(height: Dimensions.paddingSizeDefault),

            // Action Buttons
            Row(
              children: [
                Expanded(
                  child: CustomButton(
                    onPressed: () =>
                        favouriteController.addTemplateToCart(template),
                    buttonText: 'add_to_cart'.tr,
                    height: 35,
                  ),
                ),
                const SizedBox(width: Dimensions.paddingSizeSmall),
                Expanded(
                  child: CustomButton(
                    onPressed: () {
                      if (template.items != null) {
                        _templateItems = List.from(template.items!);
                      }
                      _templateNameController.text = template.name ?? '';
                      setState(() {
                        _showTemplateMode = true;
                      });
                    },
                    buttonText: 'load_template'.tr,
                    height: 35,
                    color: Colors.purple,
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _addItemToTemplate(Item? item) {
    if (item == null) return;

    // Check if item already exists in template
    int existingIndex = _templateItems
        .indexWhere((templateItem) => templateItem.itemId == item.id);

    if (existingIndex != -1) {
      // Item already exists, increase quantity
      setState(() {
        _templateItems[existingIndex] = _templateItems[existingIndex].copyWith(
          quantity: _templateItems[existingIndex].quantity! + 1,
        );
      });
      showCustomSnackBar('item_quantity_increased'.tr, isError: false);
    } else {
      // Add new item to template
      setState(() {
        _templateItems.add(TemplateItem(
          itemId: item.id,
          quantity: 1,
          item: item,
          price: item.price,
        ));
      });
      showCustomSnackBar('item_added_to_template'.tr, isError: false);
    }
  }

  void _updateItemQuantity(int index, int newQuantity) {
    if (newQuantity > 0) {
      setState(() {
        _templateItems[index] =
            _templateItems[index].copyWith(quantity: newQuantity);
      });
    }
  }

  void _removeItemFromTemplate(int index) {
    setState(() {
      _templateItems.removeAt(index);
    });
    showCustomSnackBar('item_removed_from_template'.tr, isError: false);
  }

  void _saveTemplate() async {
    if (_templateNameController.text.isEmpty) {
      showCustomSnackBar('please_enter_template_name'.tr, isError: true);
      return;
    }

    if (_templateItems.isEmpty) {
      showCustomSnackBar('please_add_items_to_template'.tr, isError: true);
      return;
    }

    // Check if user is authenticated
    try {
      final authController = Get.find<AuthController>();
      if (!authController.isLoggedIn()) {
        showCustomSnackBar('please_login_to_save_template'.tr, isError: true);
        return;
      }
    } catch (e) {
      showCustomSnackBar('please_login_to_save_template'.tr, isError: true);
      return;
    }

    try {
      print('====> Saving template with name: ${_templateNameController.text}');
      print('====> Template items count: ${_templateItems.length}');

      await favouriteController.createTemplate(
        _templateNameController.text,
        'Template created from favourites',
        _templateItems,
      );

      // Clear template items after saving
      setState(() {
        _templateItems.clear();
        _templateNameController.clear();
        _showTemplateMode = false;
      });

      showCustomSnackBar('template_saved_successfully'.tr, isError: false);
    } catch (e) {
      print('====> Error saving template: $e');
      showCustomSnackBar('error_saving_template: $e'.tr, isError: true);
    }
  }

  void _addAllFavouritesToTemplate() {
    if (favouriteController.wishItemList == null ||
        favouriteController.wishItemList!.isEmpty) {
      showCustomSnackBar('no_favourites_to_add'.tr, isError: true);
      return;
    }

    setState(() {
      for (Item? item in favouriteController.wishItemList!) {
        if (item != null) {
          // Check if item already exists in template
          int existingIndex = _templateItems
              .indexWhere((templateItem) => templateItem.itemId == item.id);

          if (existingIndex != -1) {
            // Item already exists, increase quantity
            _templateItems[existingIndex] =
                _templateItems[existingIndex].copyWith(
              quantity: _templateItems[existingIndex].quantity! + 1,
            );
          } else {
            // Add new item to template
            _templateItems.add(TemplateItem(
              itemId: item.id,
              quantity: 1,
              item: item,
              price: item.price,
            ));
          }
        }
      }
    });

    showCustomSnackBar('all_favourites_added_to_template'.tr, isError: false);
  }

  void _showAddIndividualItemsDialog() {
    if (favouriteController.wishItemList == null ||
        favouriteController.wishItemList!.isEmpty) {
      showCustomSnackBar('no_favourites_to_add'.tr, isError: true);
      return;
    }

    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('select_items_to_add'.tr),
        content: SizedBox(
          width: double.maxFinite,
          height: 300,
          child: ListView.builder(
            itemCount: favouriteController.wishItemList!.length,
            itemBuilder: (context, index) {
              Item? item = favouriteController.wishItemList![index];
              if (item == null) return const SizedBox.shrink();

              return ListTile(
                leading: item.imageFullUrl != null
                    ? Container(
                        width: 40,
                        height: 40,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(4),
                          image: DecorationImage(
                            image: NetworkImage(item.imageFullUrl!),
                            fit: BoxFit.cover,
                          ),
                        ),
                      )
                    : const Icon(Icons.image),
                title: Text(item.name ?? ''),
                subtitle: Text('\$${item.price?.toStringAsFixed(2) ?? '0.00'}'),
                trailing: IconButton(
                  onPressed: () {
                    _addItemToTemplate(item);
                    Navigator.of(context).pop();
                  },
                  icon: const Icon(Icons.add_circle_outline),
                  color: Colors.green,
                ),
              );
            },
          ),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('cancel'.tr),
          ),
        ],
      ),
    );
  }

  void _addTemplateToCart() {
    if (_templateItems.isEmpty) {
      showCustomSnackBar('no_items_to_add'.tr, isError: true);
      return;
    }

    // Create a temporary template model for cart addition
    TemplateModel tempTemplate = TemplateModel(
      name: _templateNameController.text,
      description: 'Template from favourites',
      items: _templateItems,
    );

    favouriteController.addTemplateToCart(tempTemplate);
    showCustomSnackBar('template_items_added_to_cart'.tr, isError: false);
  }

  void _removeTemplate(int templateId) async {
    final bool? confirm = await showDialog<bool>(
      context: Get.context!,
      builder: (context) => AlertDialog(
        title: Text('confirm_delete_template'.tr),
        content: Text('are_you_sure_you_want_to_delete_this_template'.tr),
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: Text('cancel'.tr),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: Text('delete'.tr),
          ),
        ],
      ),
    );

    if (confirm == true) {
      try {
        await favouriteController.deleteTemplate(templateId);
        showCustomSnackBar('template_deleted_successfully'.tr, isError: false);
        // Refresh the template list after deletion
        await favouriteController.getTemplateList();
      } catch (e) {
        showCustomSnackBar('error_deleting_template: $e'.tr, isError: true);
      }
    }
  }
}
