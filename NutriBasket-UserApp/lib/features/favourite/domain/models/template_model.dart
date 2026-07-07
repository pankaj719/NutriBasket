import 'package:sixam_mart/features/item/domain/models/item_model.dart';
import 'dart:convert'; // Added for jsonDecode

class TemplateModel {
  int? id;
  String? name;
  String? description;
  String? createdAt;
  String? updatedAt;
  List<TemplateItem>? items;
  int? userId;
  bool? isDefault;

  TemplateModel({
    this.id,
    this.name,
    this.description,
    this.createdAt,
    this.updatedAt,
    this.items,
    this.userId,
    this.isDefault,
  });

  TemplateModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    description = json['description'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
    userId = json['user_id'];
    isDefault = json['is_default'] ?? false;

    if (json['items'] != null) {
      items = [];
      json['items'].forEach((v) {
        items!.add(TemplateItem.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['description'] = description;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    data['user_id'] = userId;
    data['is_default'] = isDefault;

    if (items != null) {
      data['items'] = items!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class TemplateItem {
  int? id;
  int? templateId;
  int? itemId;
  int? quantity;
  Item? item;
  List<Variation>? variations;
  List<FoodVariation>? foodVariations;
  List<AddOns>? addOns;
  String? variant;
  double? price;
  String? notes;

  TemplateItem({
    this.id,
    this.templateId,
    this.itemId,
    this.quantity,
    this.item,
    this.variations,
    this.foodVariations,
    this.addOns,
    this.variant,
    this.price,
    this.notes,
  });

  TemplateItem.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    templateId = json['template_id'];
    itemId = json['item_id'];
    quantity = json['quantity'];
    variant = json['variant'];
    price = json['price']?.toDouble();
    notes = json['notes'];

    if (json['item'] != null) {
      item = Item.fromJson(json['item']);
    }

    // Handle variations - could be a string or a list
    variations = [];
    if (json['variations'] != null) {
      if (json['variations'] is String) {
        // Parse JSON string to list
        try {
          List<dynamic> variationsList = jsonDecode(json['variations']);
          for (var v in variationsList) {
            variations!.add(Variation.fromJson(v));
          }
        } catch (e) {
          print('Error parsing variations JSON string: $e');
        }
      } else if (json['variations'] is List) {
        // Already a list
        for (var v in json['variations']) {
          variations!.add(Variation.fromJson(v));
        }
      }
    }

    // Handle food variations - could be a string or a list
    foodVariations = [];
    if (json['food_variations'] != null) {
      if (json['food_variations'] is String) {
        // Parse JSON string to list
        try {
          List<dynamic> foodVariationsList =
              jsonDecode(json['food_variations']);
          for (var v in foodVariationsList) {
            foodVariations!.add(FoodVariation.fromJson(v));
          }
        } catch (e) {
          print('Error parsing food_variations JSON string: $e');
        }
      } else if (json['food_variations'] is List) {
        // Already a list
        for (var v in json['food_variations']) {
          foodVariations!.add(FoodVariation.fromJson(v));
        }
      }
    }

    // Handle add_ons - could be a string or a list
    if (json['add_ons'] != null) {
      addOns = [];
      if (json['add_ons'] is String) {
        // Parse JSON string to list
        try {
          List<dynamic> addOnsList = jsonDecode(json['add_ons']);
          for (var v in addOnsList) {
            addOns!.add(AddOns.fromJson(v));
          }
        } catch (e) {
          print('Error parsing add_ons JSON string: $e');
        }
      } else if (json['add_ons'] is List) {
        // Already a list
        for (var v in json['add_ons']) {
          addOns!.add(AddOns.fromJson(v));
        }
      }
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['template_id'] = templateId;
    data['item_id'] = itemId;
    data['quantity'] = quantity;
    data['variant'] = variant;
    data['price'] = price;
    data['notes'] = notes;

    if (item != null) {
      data['item'] = item!.toJson();
    }

    if (variations != null) {
      data['variations'] = variations!.map((v) => v.toJson()).toList();
    }

    if (foodVariations != null) {
      data['food_variations'] = foodVariations!.map((v) => v.toJson()).toList();
    }

    if (addOns != null) {
      data['add_ons'] = addOns!.map((v) => v.toJson()).toList();
    }

    return data;
  }

  TemplateItem copyWith({
    int? id,
    int? templateId,
    int? itemId,
    int? quantity,
    Item? item,
    List<Variation>? variations,
    List<FoodVariation>? foodVariations,
    List<AddOns>? addOns,
    String? variant,
    double? price,
    String? notes,
  }) {
    return TemplateItem(
      id: id ?? this.id,
      templateId: templateId ?? this.templateId,
      itemId: itemId ?? this.itemId,
      quantity: quantity ?? this.quantity,
      item: item ?? this.item,
      variations: variations ?? this.variations,
      foodVariations: foodVariations ?? this.foodVariations,
      addOns: addOns ?? this.addOns,
      variant: variant ?? this.variant,
      price: price ?? this.price,
      notes: notes ?? this.notes,
    );
  }
}

class TemplateListModel {
  int? totalSize;
  String? limit;
  int? offset;
  List<TemplateModel>? templates;

  TemplateListModel({
    this.totalSize,
    this.limit,
    this.offset,
    this.templates,
  });

  TemplateListModel.fromJson(Map<String, dynamic> json) {
    totalSize = json['total_size'];
    limit = json['limit'].toString();
    offset =
        (json['offset'] != null && json['offset'].toString().trim().isNotEmpty)
            ? int.parse(json['offset'].toString())
            : null;

    if (json['templates'] != null) {
      templates = [];
      json['templates'].forEach((v) {
        templates!.add(TemplateModel.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['total_size'] = totalSize;
    data['limit'] = limit;
    data['offset'] = offset;

    if (templates != null) {
      data['templates'] = templates!.map((v) => v.toJson()).toList();
    }

    return data;
  }
}
