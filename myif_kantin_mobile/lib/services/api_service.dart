import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'dart:convert';
import '../models/auth_response_model.dart';
import '../models/vendor_response_model.dart';
import '../models/order_request_model.dart';
import '../models/order_response_model.dart';
import '../models/vendor_model.dart';
import '../models/menu_model.dart';
import '../config/config.dart';

class ApiService extends GetConnect {
  final storage = GetStorage();

  @override
  void onInit() {
    super.onInit();
    
    httpClient.timeout = Duration(seconds: 30);
    
    httpClient.addRequestModifier<dynamic>((request) {
      request.headers['Content-Type'] = 'application/json';
      request.headers['Accept'] = 'application/json';
      
      final token = storage.read('token');
      if (token != null) {
        request.headers['Authorization'] = 'Bearer $token';
      }
      
      print('📤 Request: ${request.method} ${request.url}');
      
      return request;
    });

    httpClient.addResponseModifier((request, response) {
      print('📥 Response: ${response.statusCode} - ${response.statusText}');
      print('📥 Body: ${response.bodyString}');
      return response;
    });
  }

  Future<T> _executeWithRetry<T>(
    Future<T> Function() apiCall,
    T Function(String) onError,
    {int maxRetries = 3}
  ) async {
    for (int attempt = 1; attempt <= maxRetries; attempt++) {
      try {
        return await apiCall();
      } catch (e) {
        print('💥 Attempt $attempt failed: $e');
        
        if (attempt == maxRetries) {
          String errorMessage = 'Network error';
          if (e.toString().contains('TimeoutException')) {
            errorMessage = 'Connection timeout. Please check your internet connection.';
          } else if (e.toString().contains('SocketException')) {
            errorMessage = 'Cannot connect to server. Please check your connection.';
          } else if (e.toString().contains('HandshakeException')) {
            errorMessage = 'SSL connection error. Please try again.';
          }
          return onError(errorMessage);
        }
        
        await Future.delayed(Duration(seconds: attempt));
      }
    }
    
    return onError('Maximum retry attempts reached');
  }

  Future<AuthResponse> register({
    required String name,
    required String username,
    required String email,
    required String phone,
    required String password,
    required String passwordConfirmation,
  }) async {
    return _executeWithRetry(
      () async {
        final requestData = {
          'name': name.trim(),
          'username': username.trim(),
          'email': email.trim(),
          'phone': phone.trim(),
          'password': password,
          'password_confirmation': passwordConfirmation,
        };

        final response = await post('$apiBaseUrl/api/register', requestData);

        if (response.hasError) {
          String errorMessage = 'Registration failed';
          
          if (response.statusCode == 422) {
            try {
              final errorData = response.body;
              if (errorData is Map && errorData.containsKey('message')) {
                errorMessage = errorData['message'];
              }
              if (errorData is Map && errorData.containsKey('errors')) {
                final errors = errorData['errors'] as Map;
                final firstError = errors.values.first;
                if (firstError is List && firstError.isNotEmpty) {
                  errorMessage = firstError[0];
                }
              }
            } catch (e) {
              errorMessage = 'Validation error. Please check your input.';
            }
          } else if (response.statusCode == 409) {
            errorMessage = 'Username or email already exists';
          } else if (response.statusCode == 500) {
            errorMessage = 'Server error. Please try again later.';
          } else {
            errorMessage = response.statusText ?? 'Registration failed';
          }
          
          return AuthResponse(success: false, message: errorMessage);
        }

        if (response.body == null) {
          return AuthResponse(success: false, message: 'Invalid response from server');
        }

        return AuthResponse.fromJson(response.body);
      },
      (error) => AuthResponse(success: false, message: error),
    );
  }

  Future<AuthResponse> login(String username, String password) async {
    return _executeWithRetry(
      () async {
        final requestData = {
          'username': username.trim(),
          'password': password,
        };

        final response = await post('$apiBaseUrl/api/login', requestData);

        if (response.hasError) {
          String errorMessage = 'Login failed';
          
          switch (response.statusCode) {
            case 401:
              errorMessage = 'Invalid username or password';
              break;
            case 422:
              errorMessage = 'Validation error. Please check your input.';
              break;
            case 500:
              errorMessage = 'Server error. Please try again later.';
              break;
            default:
              errorMessage = response.statusText ?? 'Login failed';
          }
          
          return AuthResponse(success: false, message: errorMessage);
        }

        if (response.body == null) {
          return AuthResponse(success: false, message: 'Invalid response from server');
        }

        return AuthResponse.fromJson(response.body);
      },
      (error) => AuthResponse(success: false, message: error),
    );
  }

  Future<VendorResponse> getVendors() async {
    return _executeWithRetry(
      () async {
        final response = await get('$apiBaseUrl/api/vendors');

        if (response.hasError || response.body == null) {
          return VendorResponse(
            success: false,
            message: 'Failed to load vendors',
            data: [],
          );
        }

        return VendorResponse.fromJson(response.body);
      },
      (error) => VendorResponse(success: false, message: error, data: []),
    );
  }

  Future<MenuResponse> getVendorMenus(int vendorId) async {
    return _executeWithRetry(
      () async {
        final response = await get('$apiBaseUrl/api/vendors/$vendorId/menus');

        if (response.hasError || response.body == null) {
          return MenuResponse(
            success: false,
            message: 'Failed to load menus',
          );
        }

        return MenuResponse.fromJson(response.body);
      },
      (error) => MenuResponse(success: false, message: error),
    );
  }

  Future<QRISResponse> getVendorQRIS(int vendorId) async {
    return _executeWithRetry(
      () async {
        final response = await get('$apiBaseUrl/api/vendors/$vendorId/qris');

        if (response.hasError || response.body == null) {
          return QRISResponse(
            success: false,
            message: 'Failed to load QRIS',
          );
        }

        return QRISResponse.fromJson(response.body);
      },
      (error) => QRISResponse(success: false, message: error),
    );
  }

  Future<OrderResponse> createOrder(OrderRequest orderRequest) async {
    return _executeWithRetry(
      () async {
        final response = await post('$apiBaseUrl/api/orders', orderRequest.toJson());

        if (response.hasError || response.body == null) {
          return OrderResponse(success: false, message: 'Failed to create order');
        }

        return OrderResponse.fromJson(response.body);
      },
      (error) => OrderResponse(success: false, message: error),
    );
  }

  Future<PaymentProofResponse> uploadPaymentProof({
    required int orderId,
    required int userId,
    required String paymentProofBase64,
    required String fileExtension,
  }) async {
    return _executeWithRetry(
      () async {
        final requestData = {
          'user_id': userId,
          'payment_proof_base64': paymentProofBase64,
          'file_extension': fileExtension,
        };

        final response = await post('$apiBaseUrl/api/orders/$orderId/payment-proof', requestData);

        if (response.hasError || response.body == null) {
          return PaymentProofResponse(
            success: false,
            message: 'Failed to upload payment proof',
          );
        }

        return PaymentProofResponse.fromJson(response.body);
      },
      (error) => PaymentProofResponse(success: false, message: error),
    );
  }

  Future<UserOrdersResponse> getUserOrders() async {
    return _executeWithRetry(
      () async {
        final user = storage.read('user');
        if (user == null) {
          return UserOrdersResponse(success: false, message: 'User not logged in', data: []);
        }

        final userData = Map<String, dynamic>.from(user);
        final userId = userData['user_id'];
        if (userId == null) {
          return UserOrdersResponse(success: false, message: 'User ID not found', data: []);
        }

        final response = await get('$apiBaseUrl/api/orders?user_id=$userId');

        if (response.hasError || response.body == null) {
          return UserOrdersResponse(
            success: false,
            message: 'Failed to load orders',
            data: [],
          );
        }

        return UserOrdersResponse.fromJson(response.body);
      },
      (error) => UserOrdersResponse(success: false, message: error, data: []),
    );
  }

  Future<OrderResponse> getOrderById(int orderId) async {
    return _executeWithRetry(
      () async {
        final user = storage.read('user');
        if (user == null) {
          return OrderResponse(success: false, message: 'User not logged in');
        }

        final userData = Map<String, dynamic>.from(user);
        final userId = userData['user_id'];
        
        if (userId == null) {
          return OrderResponse(success: false, message: 'User ID not found');
        }

        final response = await get('$apiBaseUrl/api/orders/$orderId?user_id=$userId');
        
        if (response.hasError || response.body == null) {
          return OrderResponse(
            success: false,
            message: 'Failed to load order',
          );
        }

        return OrderResponse.fromJson(response.body);
      },
      (error) => OrderResponse(success: false, message: error),
    );
  }

  Future<AuthResponse> updateProfile(int userId, Map<String, dynamic> userData) async {
    return _executeWithRetry(
      () async {
        final response = await put('$apiBaseUrl/api/users/$userId', userData);

        if (response.hasError || response.body == null) {
          return AuthResponse(success: false, message: 'Failed to update profile');
        }

        return AuthResponse.fromJson(response.body);
      },
      (error) => AuthResponse(success: false, message: error),
    );
  }

  Future<SearchResponse> searchContent(String query) async {
    return _executeWithRetry(
      () async {
        final vendorsResponse = await get('$apiBaseUrl/api/vendors');
        final menusResponse = await get('$apiBaseUrl/api/menus?search=$query');

        if (vendorsResponse.hasError || menusResponse.hasError) {
          return SearchResponse(success: false, message: 'Search failed', vendors: []);
        }

        final vendors = <Vendor>[];
        final vendorsFromApi = VendorResponse.fromJson(vendorsResponse.body);
        final menusFromApi = MenuResponse.fromJson(menusResponse.body);

        if (vendorsFromApi.success) {
          final filteredVendors = vendorsFromApi.data.where((vendor) {
            return vendor.name.toLowerCase().contains(query.toLowerCase()) ||
                   vendor.description.toLowerCase().contains(query.toLowerCase());
          }).toList();
          vendors.addAll(filteredVendors);
        }

        if (menusFromApi.success && menusFromApi.data != null) {
          final menuVendors = <int, Vendor>{};
          
          for (final menu in menusFromApi.data!.menus) {
            if (menu.vendor != null) {
              menuVendors[menu.vendor!.vendorId] = menu.vendor!;
            }
          }
          
          for (final vendor in menuVendors.values) {
            if (!vendors.any((v) => v.vendorId == vendor.vendorId)) {
              vendors.add(vendor);
            }
          }
        }

        return SearchResponse(
          success: true,
          message: 'Search completed',
          vendors: vendors,
        );
      },
      (error) => SearchResponse(success: false, message: error, vendors: []),
    );
  }
}

class PaymentProofResponse {
  final bool success;
  final String message;
  final Map<String, dynamic>? data;

  PaymentProofResponse({
    required this.success,
    required this.message,
    this.data,
  });

  factory PaymentProofResponse.fromJson(Map<String, dynamic> json) {
    return PaymentProofResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'],
    );
  }
}

class QRISResponse {
  final bool success;
  final String message;
  final QRISData? data;

  QRISResponse({
    required this.success,
    required this.message,
    this.data,
  });

  factory QRISResponse.fromJson(Map<String, dynamic> json) {
    return QRISResponse(
      success: json['success'] ?? false,
      message: json['message'] ?? '',
      data: json['data'] != null ? QRISData.fromJson(json['data']) : null,
    );
  }
}

class QRISData {
  final int vendorId;
  final String vendorName;
  final String qrisImage;
  final String qrisImageUrl;

  QRISData({
    required this.vendorId,
    required this.vendorName,
    required this.qrisImage,
    required this.qrisImageUrl,
  });

  factory QRISData.fromJson(Map<String, dynamic> json) {
    return QRISData(
      vendorId: json['vendor_id'] ?? 0,
      vendorName: json['vendor_name'] ?? '',
      qrisImage: json['qris_image'] ?? '',
      qrisImageUrl: json['qris_image_url'] ?? '',
    );
  }
}

class SearchResponse {
  final bool success;
  final String message;
  final List<Vendor> vendors;

  SearchResponse({
    required this.success,
    required this.message,
    required this.vendors,
  });
}