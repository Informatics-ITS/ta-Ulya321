class OrderRequest {
  final int userId;
  final String roomNumber;
  final String buildingName;
  final int shippingFee;
  final String paymentMethod;
  final DateTime? deliveryTime;
  final String? deliveryNotes;
  final List<OrderItemRequest> items;

  OrderRequest({
    required this.userId,
    required this.roomNumber,
    required this.buildingName,
    required this.shippingFee,
    required this.paymentMethod,
    this.deliveryTime,
    this.deliveryNotes,
    required this.items,
  });

  Map<String, dynamic> toJson() {
    return {
      'user_id': userId,
      'room_number': roomNumber,
      'building_name': buildingName,
      'shipping_fee': shippingFee,
      'payment_method': paymentMethod,
      'delivery_time': deliveryTime?.toIso8601String(),
      'delivery_notes': deliveryNotes,
      'items': items.map((item) => item.toJson()).toList(),
    };
  }
}

class OrderItemRequest {
  final int menuId;
  final int quantity;
  final String? notes;

  OrderItemRequest({
    required this.menuId,
    required this.quantity,
    this.notes,
  });

  Map<String, dynamic> toJson() {
    return {
      'menu_id': menuId,
      'quantity': quantity,
      'notes': notes,
    };
  }
}