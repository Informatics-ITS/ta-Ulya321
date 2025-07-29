import 'user_model.dart';
import 'vendor_model.dart';
import 'menu_model.dart';

class Order {
  final int orderId;
  final User user;
  final String roomNumber;
  final String buildingName;
  final String? courierName;
  final int shippingFee;
  final int totalPrice;
  final String status;
  final String paymentStatus;
  final String paymentMethod;
  final String? paymentProof;
  final String? paymentProofUrl;
  final DateTime? deliveryTime;
  final String? formattedDeliveryTime;
  final String? deliveryNotes;
  final List<OrderItem> orderItems;
  final DateTime createdAt;
  final DateTime updatedAt;

  Order({
    required this.orderId,
    required this.user,
    required this.roomNumber,
    required this.buildingName,
    this.courierName,
    required this.shippingFee,
    required this.totalPrice,
    required this.status,
    required this.paymentStatus,
    required this.paymentMethod,
    this.paymentProof,
    this.paymentProofUrl,
    this.deliveryTime,
    this.formattedDeliveryTime,
    this.deliveryNotes,
    required this.orderItems,
    required this.createdAt,
    required this.updatedAt,
  });

  factory Order.fromJson(Map<String, dynamic> json) {
    return Order(
      orderId: json['order_id'] ?? 0,
      user: json['user'] != null ? User.fromJson(json['user']) : User(
        userId: 0,
        name: '',
        phone: '',
        email: '',
        username: '',
        role: 'user',
      ),
      roomNumber: json['room_number'] ?? '',
      buildingName: json['building_name'] ?? '',
      courierName: json['courier_name'],
      shippingFee: _parseToInt(json['shipping_fee']),
      totalPrice: _parseToInt(json['total_price']),
      status: json['status'] ?? '',
      paymentStatus: json['payment_status'] ?? '',
      paymentMethod: json['payment_method'] ?? '',
      paymentProof: json['payment_proof'],
      paymentProofUrl: json['payment_proof_url'],
      deliveryTime: json['delivery_time'] != null 
          ? DateTime.parse(json['delivery_time'])
          : null,
      formattedDeliveryTime: json['formatted_delivery_time'],
      deliveryNotes: json['delivery_notes'],
      orderItems: (json['order_items'] as List<dynamic>?)
          ?.map((item) => OrderItem.fromJson(item))
          .toList() ?? [],
      createdAt: DateTime.parse(json['created_at']),
      updatedAt: DateTime.parse(json['updated_at']),
    );
  }

  static int _parseToInt(dynamic value) {
    if (value == null) return 0;
    if (value is int) return value;
    if (value is double) return value.toInt();
    if (value is String) {
      return int.tryParse(value) ?? double.tryParse(value)?.toInt() ?? 0;
    }
    return 0;
  }

  Map<String, dynamic> toJson() {
    return {
      'order_id': orderId,
      'user': user.toJson(),
      'room_number': roomNumber,
      'building_name': buildingName,
      'courier_name': courierName,
      'shipping_fee': shippingFee,
      'total_price': totalPrice,
      'status': status,
      'payment_status': paymentStatus,
      'payment_method': paymentMethod,
      'payment_proof': paymentProof,
      'payment_proof_url': paymentProofUrl,
      'delivery_time': deliveryTime?.toIso8601String(),
      'formatted_delivery_time': formattedDeliveryTime,
      'delivery_notes': deliveryNotes,
      'order_items': orderItems.map((item) => item.toJson()).toList(),
      'created_at': createdAt.toIso8601String(),
      'updated_at': updatedAt.toIso8601String(),
    };
  }

  String get formattedTotalPrice {
    return 'Rp ${totalPrice.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }

  String get formattedShippingFee {
    return 'Rp ${shippingFee.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }

  int get subtotal => totalPrice - shippingFee;

  String get formattedSubtotal {
    return 'Rp ${subtotal.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }

  Vendor? get vendor {
    if (orderItems.isNotEmpty && orderItems.first.menu.vendor != null) {
      return orderItems.first.menu.vendor;
    }
    return null;
  }
}

class OrderItem {
  final int orderItemId;
  final int menuId;
  final int quantity;
  final int priceEach;
  final int totalPrice;
  final String? notes;
  final Menu menu;

  OrderItem({
    required this.orderItemId,
    required this.menuId,
    required this.quantity,
    required this.priceEach,
    required this.totalPrice,
    this.notes,
    required this.menu,
  });

  factory OrderItem.fromJson(Map<String, dynamic> json) {
    return OrderItem(
      orderItemId: json['order_item_id'] ?? 0,
      menuId: json['menu_id'] ?? 0,
      quantity: json['quantity'] ?? 0,
      priceEach: json['price_each'] ?? 0,
      totalPrice: json['total_price'] ?? 0,
      notes: json['notes'],
      menu: Menu.fromJson(json['menu']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'order_item_id': orderItemId,
      'menu_id': menuId,
      'quantity': quantity,
      'price_each': priceEach,
      'total_price': totalPrice,
      'notes': notes,
      'menu': menu.toJson(),
    };
  }

  String get formattedTotalPrice {
    return 'Rp ${totalPrice.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }

  String get formattedPriceEach {
    return 'Rp ${priceEach.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}