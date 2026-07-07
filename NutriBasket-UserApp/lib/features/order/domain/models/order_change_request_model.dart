class OrderChangeRequestModel {
  int? id;
  int? orderId;
  int? itemId;
  String? itemName;
  double? currentQuantity;
  double? requestedQuantity;
  String? reason;
  String? status;
  String? createdAt;
  String? updatedAt;

  OrderChangeRequestModel({
    this.id,
    this.orderId,
    this.itemId,
    this.itemName,
    this.currentQuantity,
    this.requestedQuantity,
    this.reason,
    this.status,
    this.createdAt,
    this.updatedAt,
  });

  OrderChangeRequestModel.fromJson(Map<String, dynamic> json) {
    id = json['id'];
    orderId = json['order_id'];
    itemId = json['item_id'];
    itemName = json['item_name'];
    currentQuantity = json['current_quantity']?.toDouble();
    requestedQuantity = json['requested_quantity']?.toDouble();
    reason = json['reason'];
    status = json['status'];
    createdAt = json['created_at'];
    updatedAt = json['updated_at'];
  }

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['id'] = id;
    data['order_id'] = orderId;
    data['item_id'] = itemId;
    data['item_name'] = itemName;
    data['current_quantity'] = currentQuantity;
    data['requested_quantity'] = requestedQuantity;
    data['reason'] = reason;
    data['status'] = status;
    data['created_at'] = createdAt;
    data['updated_at'] = updatedAt;
    return data;
  }
}

class OrderChangeRequestBodyModel {
  int? itemId;
  double? newQuantity;
  String? reason;

  OrderChangeRequestBodyModel({
    this.itemId,
    this.newQuantity,
    this.reason,
  });

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    data['item_id'] = itemId;
    data['new_quantity'] = newQuantity;
    if (reason != null && reason!.isNotEmpty) {
      data['reason'] = reason;
    }
    return data;
  }
}

class BulkOrderChangeRequestBodyModel {
  List<OrderChangeRequestBodyModel>? changes;
  String? reason;

  BulkOrderChangeRequestBodyModel({
    this.changes,
    this.reason,
  });

  Map<String, dynamic> toJson() {
    final Map<String, dynamic> data = <String, dynamic>{};
    if (changes != null) {
      data['changes'] = changes!.map((v) => v.toJson()).toList();
    }
    if (reason != null && reason!.isNotEmpty) {
      data['reason'] = reason;
    }
    return data;
  }
}
