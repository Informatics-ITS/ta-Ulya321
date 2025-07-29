import 'menu_model.dart';
import 'vendor_model.dart';

class CartItem {
  final Menu menu;
  final Vendor vendor;
  int quantity;
  String? notes;

  CartItem({
    required this.menu,
    required this.vendor,
    this.quantity = 1,
    this.notes,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) {
    return CartItem(
      menu: Menu.fromJson(json['menu']),
      vendor: Vendor.fromJson(json['vendor']),
      quantity: json['quantity'] ?? 1,
      notes: json['notes'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'menu': menu.toJson(),
      'vendor': vendor.toJson(),
      'quantity': quantity,
      'notes': notes,
    };
  }

  int get totalPrice => menu.price * quantity;

  String get formattedTotalPrice {
    return 'Rp ${totalPrice.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}

class Cart {
  List<CartItem> items;

  Cart({List<CartItem>? items}) : items = items ?? <CartItem>[];

  factory Cart.fromJson(List<dynamic> json) {
    return Cart(
      items: json.map((item) => CartItem.fromJson(item)).toList(),
    );
  }

  List<Map<String, dynamic>> toJson() {
    return items.map((item) => item.toJson()).toList();
  }

  int get totalItems => items.fold(0, (sum, item) => sum + item.quantity);

  int get totalPrice => items.fold(0, (sum, item) => sum + item.totalPrice);

  String get formattedTotalPrice {
    return 'Rp ${totalPrice.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }

  bool get isEmpty => items.isEmpty;
  bool get isNotEmpty => items.isNotEmpty;
}