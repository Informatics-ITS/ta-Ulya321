import 'order_model.dart';

class OrderResponse {
  final bool success;
  final String message;
  final Order? data;
  final Map<String, dynamic>? errors;

  OrderResponse({
    required this.success,
    required this.message,
    this.data,
    this.errors,
  });

  factory OrderResponse.fromJson(Map<String, dynamic> json) {
    return OrderResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? Order.fromJson(json['data']) : null,
      errors: json['errors'],
    );
  }
}

class UserOrdersResponse {
  final bool success;
  final String message;
  final List<Order> data;

  UserOrdersResponse({
    required this.success,
    required this.message,
    required this.data,
  });

  factory UserOrdersResponse.fromJson(Map<String, dynamic> json) {
    return UserOrdersResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: (json['data'] as List<dynamic>?)
              ?.map((item) => Order.fromJson(item))
              .toList() ??
          [],
    );
  }
}