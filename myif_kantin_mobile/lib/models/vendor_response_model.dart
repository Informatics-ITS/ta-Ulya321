import 'vendor_model.dart';
import 'menu_model.dart';

class VendorResponse {
  final bool success;
  final String message;
  final List<Vendor> data;

  VendorResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory VendorResponse.fromJson(Map<String, dynamic> json) {
    return VendorResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: (json['data'] as List<dynamic>?)
              ?.map((item) => Vendor.fromJson(item))
              .toList() ??
          [],
    );
  }
}

class MenuResponse {
  final bool success;
  final String message;
  final MenuData? data;

  MenuResponse({
    required this.success,
    required this.message,
    this.data,
  });

  factory MenuResponse.fromJson(Map<String, dynamic> json) {
    return MenuResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? MenuData.fromJson(json['data']) : null,
    );
  }
}

class MenuData {
  final Vendor vendor;
  final List<Menu> menus;

  MenuData({
    required this.vendor,
    required this.menus,
  });

  factory MenuData.fromJson(Map<String, dynamic> json) {
    return MenuData(
      vendor: Vendor.fromJson(json['vendor']),
      menus: (json['menus'] as List<dynamic>?)
              ?.map((item) => Menu.fromJson(item))
              .toList() ??
          [],
    );
  }
}