import 'vendor_model.dart';

class Menu {
  final int menuId;
  final String name;
  final String description;
  final int price;
  final String? photoUrl;
  final bool isAvailable;
  final Vendor? vendor;

  Menu({
    required this.menuId,
    required this.name,
    required this.description,
    required this.price,
    this.photoUrl,
    required this.isAvailable,
    this.vendor,
  });

  factory Menu.fromJson(Map<String, dynamic> json) {
    return Menu(
      menuId: json['menu_id'] ?? 0,
      name: json['name'] ?? '',
      description: json['description'] ?? '',
      price: json['price'] ?? 0,
      photoUrl: json['photo_url'],
      isAvailable: json['is_available'] ?? true,
      vendor: json['vendor'] != null ? Vendor.fromJson(json['vendor']) : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'menu_id': menuId,
      'name': name,
      'description': description,
      'price': price,
      'photo_url': photoUrl,
      'is_available': isAvailable,
      'vendor': vendor?.toJson(),
    };
  }

  Menu copyWith({
    int? menuId,
    String? name,
    String? description,
    int? price,
    String? photoUrl,
    bool? isAvailable,
    Vendor? vendor,
  }) {
    return Menu(
      menuId: menuId ?? this.menuId,
      name: name ?? this.name,
      description: description ?? this.description,
      price: price ?? this.price,
      photoUrl: photoUrl ?? this.photoUrl,
      isAvailable: isAvailable ?? this.isAvailable,
      vendor: vendor ?? this.vendor,
    );
  }

  String get formattedPrice {
    return 'Rp ${price.toString().replaceAllMapped(
      RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'),
      (Match m) => '${m[1]}.',
    )}';
  }
}