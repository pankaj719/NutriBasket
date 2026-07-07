import 'package:get/get.dart';
import 'package:sixam_mart/features/splash/controllers/splash_controller.dart';
import 'package:sixam_mart/features/item/domain/models/basic_medicine_model.dart';
import 'dart:convert'; // Added for jsonDecode

class ItemModel {
  int? totalSize;
  String? limit;
  int? offset;
  List<Item>? items;
  List<Categories>? categories;

  ItemModel(
      {this.totalSize, this.limit, this.offset, this.items, this.categories});

  ItemModel.fromJson(Map<String, dynamic> json) {
    totalSize = json['total_size'];
    limit = json['limit'].toString();
    offset =
        (json['offset'] != null && json['offset'].toString().trim().isNotEmpty)
            ? int.parse(json['offset'].toString())
            : null;
    if (json['products'] != null) {
      items = [];
      json['products'].forEach((v) {
        items!.add(Item.fromJson(v));
        // if (v['module_type'] == null ||
        //     !Get.find<SplashController>().getModuleConfig(v['module_type']).newVariation! ||
        //     v['variations'] == null ||
        //     v['variations'].isEmpty ||
        //     (v['food_variations'] != null && v['food_variations'].isNotEmpty)) {
        //   items!.add(Item.fromJson(v));
        // }
      });
    }
    if (json['items'] != null) {
      items = [];
      json['items'].forEach((v) {
        if (v['module_type'] == null ||
            !Get.find<SplashController>()
                .getModuleConfig(v['module_type'])
                .newVariation! ||
            v['variations'] == null ||
            v['variations'].isEmpty ||
            (v['food_variations'] != null && v['food_variations'].isNotEmpty)) {
          items!.add(Item.fromJson(v));
        }
      });
    }
    if (json['categories'] != null) {
      categories = <Categories>[];
      json['categories'].forEach((v) {
        categories!.add(Categories.fromJson(v));
      });
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['total_size'] = totalSize;
    data['limit'] = limit;
    data['offset'] = offset;
    if (items != null) {
      data['products'] = items!.map((v) => v.toJson()).toList();
    }
    if (categories != null) {
      data['categories'] = categories!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class Item {
  int? id;
  String? name;
  String? description;
  String? imageFullUrl;
  List<String>? imagesFullUrl;
  int? categoryId;
  List<CategoryIds>? categoryIds;
  List<Variation>? variations;
  List<FoodVariation>? foodVariations;
  List<AddOns>? addOns;
  List<ChoiceOptions>? choiceOptions;
  double? price;
  double? tax;
  double? discount;
  String? discountType;
  String? availableTimeStarts;
  String? availableTimeEnds;
  int? storeId;
  String? storeName;
  int? zoneId;
  double? storeDiscount;
  bool? scheduleOrder;
  double? avgRating;
  int? ratingCount;
  int? veg;
  int? moduleId;
  String? moduleType;
  String? unitType;
  int? stock;
  String? availableDateStarts;
  int? organic;
  int? quantityLimit;
  int? flashSale;
  bool? isStoreHalalActive;
  bool? isHalalItem;
  bool? isPrescriptionRequired;
  List<String>? nutritionsName;
  List<String>? allergiesName;
  List<String>? genericName;

  Item({
    this.id,
    this.name,
    this.description,
    this.imageFullUrl,
    this.imagesFullUrl,
    this.categoryId,
    this.categoryIds,
    this.variations,
    this.foodVariations,
    this.addOns,
    this.choiceOptions,
    this.price,
    this.tax,
    this.discount,
    this.discountType,
    this.availableTimeStarts,
    this.availableTimeEnds,
    this.storeId,
    this.storeName,
    this.zoneId,
    this.storeDiscount,
    this.scheduleOrder,
    this.avgRating,
    this.ratingCount,
    this.veg,
    this.moduleId,
    this.moduleType,
    this.unitType,
    this.stock,
    this.organic,
    this.quantityLimit,
    this.flashSale,
    this.isStoreHalalActive,
    this.isHalalItem,
    this.isPrescriptionRequired,
    this.nutritionsName,
    this.allergiesName,
    this.genericName,
  });

  Item.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    description = json['description'];
    imageFullUrl = json['image_full_url'];
    if (json['images_full_url'] != null) {
      imagesFullUrl = [];
      json['images_full_url'].forEach((v) {
        if (v != null) {
          imagesFullUrl!.add(v.toString());
        }
      });
    }
    categoryId = json['category_id'];
    if (json['category_ids'] != null) {
      categoryIds = [];
      if (json['category_ids'] is String) {
        // Parse JSON string to list
        try {
          List<dynamic> categoryIdsList = jsonDecode(json['category_ids']);
          for (var v in categoryIdsList) {
            categoryIds!.add(CategoryIds.fromJson(v));
          }
        } catch (e) {
          print('Error parsing category_ids JSON string: $e');
        }
      } else if (json['category_ids'] is List) {
        // Already a list
        for (var v in json['category_ids']) {
          categoryIds!.add(CategoryIds.fromJson(v));
        }
      }
    }
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

    foodVariations = [];
    if (json['food_variations'] != null && json['food_variations'].isNotEmpty) {
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
        if (json['add_ons'].length > 0 && json['add_ons'][0] != '[') {
          for (var v in json['add_ons']) {
            addOns!.add(AddOns.fromJson(v));
          }
        } else if (json['addons'] != null) {
          for (var v in json['addons']) {
            addOns!.add(AddOns.fromJson(v));
          }
        }
      }
    }
    if (json['choice_options'] != null) {
      choiceOptions = [];
      if (json['choice_options'] is String) {
        // Parse JSON string to list
        try {
          List<dynamic> choiceOptionsList = jsonDecode(json['choice_options']);
          for (var v in choiceOptionsList) {
            choiceOptions!.add(ChoiceOptions.fromJson(v));
          }
        } catch (e) {
          print('Error parsing choice_options JSON string: $e');
        }
      } else if (json['choice_options'] is List) {
        // Already a list
        for (var v in json['choice_options']) {
          choiceOptions!.add(ChoiceOptions.fromJson(v));
        }
      }
    }
    price = json['price']?.toDouble() ?? 0.0;
    tax = json['tax']?.toDouble() ?? 0.0;
    discount = json['discount']?.toDouble() ?? 0.0;
    discountType = json['discount_type'];
    availableTimeStarts = json['available_time_starts'];
    availableTimeEnds = json['available_time_ends'];
    storeId = json['store_id'];
    storeName = json['store_name'];
    zoneId = json['zone_id'];
    storeDiscount = json['store_discount']?.toDouble() ?? 0.0;
    scheduleOrder = json['schedule_order'];
    avgRating = json['avg_rating']?.toDouble() ?? 0.0;
    ratingCount = json['rating_count'];
    moduleId = json['module_id'];
    moduleType = json['module_type'];
    veg = json['veg'] != null ? int.parse(json['veg'].toString()) : 0;
    stock = json['stock'];
    unitType = json['unit_type'];
    availableDateStarts = json['available_date_starts'];
    organic = json['organic'];
    quantityLimit = json['maximum_cart_quantity'];
    flashSale = json['flash_sale'];
    isStoreHalalActive = json['halal_tag_status'] == 1;
    isHalalItem = json['is_halal'] == 1;
    isPrescriptionRequired = json['is_prescription_required'] == 1;
    nutritionsName = json['nutritions_name']?.cast<String>();
    allergiesName = json['allergies_name']?.cast<String>();
    genericName = json['generic_name']?.cast<String>();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['description'] = description;
    data['image_full_url'] = imageFullUrl;
    data['images_full_url'] = imagesFullUrl;
    data['category_id'] = categoryId;
    if (categoryIds != null) {
      data['category_ids'] = categoryIds!.map((v) => v.toJson()).toList();
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
    if (choiceOptions != null) {
      data['choice_options'] = choiceOptions!.map((v) => v.toJson()).toList();
    }
    data['price'] = price;
    data['tax'] = tax;
    data['discount'] = discount;
    data['discount_type'] = discountType;
    data['available_time_starts'] = availableTimeStarts;
    data['available_time_ends'] = availableTimeEnds;
    data['store_id'] = storeId;
    data['store_name'] = storeName;
    data['zone_id'] = zoneId;
    data['store_discount'] = storeDiscount;
    data['schedule_order'] = scheduleOrder;
    data['avg_rating'] = avgRating;
    data['rating_count'] = ratingCount;
    data['veg'] = veg;
    data['module_id'] = moduleId;
    data['module_type'] = moduleType;
    data['stock'] = stock;
    data['unit_type'] = unitType;
    data['available_date_starts'] = availableDateStarts;
    data['organic'] = organic;
    data['maximum_cart_quantity'] = quantityLimit;
    data['flash_sale'] = flashSale;
    data['halal_tag_status'] = isStoreHalalActive;
    data['is_halal'] = isHalalItem;
    data['is_prescription_required'] = isPrescriptionRequired;
    data['nutritions_name'] = nutritionsName;
    data['allergies_name'] = allergiesName;
    data['generic_name'] = genericName;
    return data;
  }
}

class CategoryIds {
  int? id;
  int? position;

  CategoryIds({this.id, this.position});

  CategoryIds.fromJson(Map<String, dynamic> json) {
    id = int.tryParse(json['id'].toString()) ?? 0;
    position = int.tryParse(json['position'].toString()) ?? 0;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['position'] = position;
    return data;
  }
}

class Variation {
  String? type;
  double? price;
  int? stock;

  Variation({this.type, this.price, this.stock});

  Variation.fromJson(Map<String, dynamic> json) {
    type = json['type'];
    price = json['price']?.toDouble() ?? 0.0;
    stock = int.tryParse(json['stock']?.toString() ?? '0') ?? 0;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['type'] = type;
    data['price'] = price;
    data['stock'] = stock;
    return data;
  }
}

class AddOns {
  int? id;
  String? name;
  double? price;

  AddOns({
    this.id,
    this.name,
    this.price,
  });

  AddOns.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    name = json['name'];
    price = json['price']?.toDouble() ?? 0.0;
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['name'] = name;
    data['price'] = price;
    return data;
  }
}

class ChoiceOptions {
  String? name;
  String? title;
  List<String>? options;

  ChoiceOptions({this.name, this.title, this.options});

  ChoiceOptions.fromJson(Map<String, dynamic> json) {
    name = json['name'];
    title = json['title'];
    options = json['options'].cast<String>();
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['name'] = name;
    data['title'] = title;
    data['options'] = options;
    return data;
  }
}

class FoodVariation {
  String? name;
  bool? multiSelect;
  int? min;
  int? max;
  bool? required;
  List<VariationValue>? variationValues;

  FoodVariation(
      {this.name,
      this.multiSelect,
      this.min,
      this.max,
      this.required,
      this.variationValues});

  FoodVariation.fromJson(Map<String, dynamic> json) {
    if (json['max'] != null) {
      name = json['name'];
      multiSelect = json['type'] == 'multi';
      min = multiSelect! ? int.parse(json['min'].toString()) : 0;
      max = multiSelect! ? int.parse(json['max'].toString()) : 0;
      required = json['required'] == 'on';
      if (json['values'] != null) {
        variationValues = [];
        json['values'].forEach((v) {
          variationValues!.add(VariationValue.fromJson(v));
        });
      }
    }
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['name'] = name;
    data['type'] = multiSelect;
    data['min'] = min;
    data['max'] = max;
    data['required'] = required;
    if (variationValues != null) {
      data['values'] = variationValues!.map((v) => v.toJson()).toList();
    }
    return data;
  }
}

class VariationValue {
  String? level;
  double? optionPrice;
  bool? isSelected;

  VariationValue({this.level, this.optionPrice, this.isSelected});

  VariationValue.fromJson(Map<String, dynamic> json) {
    level = json['label'];
    optionPrice = double.parse(json['optionPrice'].toString());
    isSelected = json['isSelected'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['label'] = level;
    data['optionPrice'] = optionPrice;
    data['isSelected'] = isSelected;
    return data;
  }
}
