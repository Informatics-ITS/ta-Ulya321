import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:flutter/material.dart';
import '../models/vendor_model.dart';
import '../models/menu_model.dart';
import '../models/cart_model.dart';
import '../services/api_service.dart';
import '../controllers/cart_controller.dart';

class VendorMenuController extends GetxController {
  final apiService = ApiService();
  final storage = GetStorage();
  
  var vendor = Rx<Vendor?>(null);
  var menus = <Menu>[].obs;
  var isLoading = false.obs;
  var isRefreshing = false.obs;
  var cart = Cart().obs;

  @override
  void onInit() {
    super.onInit();
    loadCart();
    if (Get.arguments != null && Get.arguments is Vendor) {
      vendor.value = Get.arguments as Vendor;
      vendor.value = vendor.value!.copyWith(isActive: true);
      loadMenus();
    }
  }

  void loadCart() {
    try {
      final cartData = storage.read('shopping_cart');
      if (cartData != null && cartData is List && cartData.isNotEmpty) {
        cart.value = Cart.fromJson(List<dynamic>.from(cartData));
      }
    } catch (e) {
      cart.value = Cart();
    }
  }

  void saveCart() {
    try {
      storage.write('shopping_cart', cart.value.toJson());
    } catch (e) {
      print('Error saving cart: $e');
    }
  }

  Future<void> loadMenus() async {
    if (vendor.value == null) return;

    try {
      isLoading.value = true;
      final response = await apiService.getVendorMenus(vendor.value!.vendorId);
      
      if (response.success && response.data != null) {
        vendor.value = response.data!.vendor.copyWith(isActive: true);
        
        final updatedMenus = response.data!.menus.map((menu) {
          return menu.copyWith(isAvailable: true);
        }).toList();
        
        menus.assignAll(updatedMenus);
      } else {
        _showError('Error', response.message);
      }
    } catch (e) {
      _showError('Error', 'Failed to load menus');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> refreshMenus() async {
    if (vendor.value == null || isRefreshing.value) return;

    try {
      isRefreshing.value = true;
      final response = await apiService.getVendorMenus(vendor.value!.vendorId);
      
      if (response.success && response.data != null) {
        vendor.value = response.data!.vendor.copyWith(isActive: true);
        
        final updatedMenus = response.data!.menus.map((menu) {
          return menu.copyWith(isAvailable: true);
        }).toList();
        
        menus.assignAll(updatedMenus);
        _showSuccess('Success', 'Menu refreshed successfully');
      } else {
        _showError('Error', response.message);
      }
    } catch (e) {
      _showError('Error', 'Failed to refresh menus');
    } finally {
      isRefreshing.value = false;
    }
  }

  void addToCart(Menu menu, {int quantity = 1, String? notes}) {
    if (vendor.value == null) return;

    if (quantity <= 0 || quantity > 99) {
      _showError('Error', 'Invalid quantity');
      return;
    }

    try {
      final currentItems = List<CartItem>.from(cart.value.items);
      final existingIndex = currentItems.indexWhere(
        (item) => item.menu.menuId == menu.menuId,
      );

      if (existingIndex != -1) {
        final newQuantity = currentItems[existingIndex].quantity + quantity;
        if (newQuantity > 99) {
          _showError('Error', 'Maximum quantity is 99');
          return;
        }
        currentItems[existingIndex].quantity = newQuantity;
        if (notes != null && notes.trim().isNotEmpty) {
          currentItems[existingIndex].notes = notes.trim();
        }
      } else {
        currentItems.add(CartItem(
          menu: menu,
          vendor: vendor.value!,
          quantity: quantity,
          notes: notes?.trim(),
        ));
      }

      cart.value = Cart(items: currentItems);
      saveCart();
      
      _showSuccess('Added to Cart', '${menu.name} x$quantity added to cart');
      
      try {
        if (Get.isRegistered<CartController>()) {
          Get.find<CartController>().loadCart();
        }
      } catch (e) {
        print('CartController not registered: $e');
      }
    } catch (e) {
      print('Error adding to cart: $e');
      _showError('Error', 'Failed to add item to cart');
    }
  }

  void showAddToCartDialog(Menu menu) {
    int quantity = 1;
    final notesController = TextEditingController();

    Get.dialog(
      StatefulBuilder(
        builder: (context, setState) {
          return AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
            title: Row(
              children: [
                Expanded(
                  child: Text(menu.name, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                ),
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: Colors.blue[50],
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    menu.formattedPrice,
                    style: TextStyle(
                      color: Colors.blue[600],
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                    ),
                  ),
                ),
              ],
            ),
            content: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (menu.description.isNotEmpty) ...[
                  Text(menu.description, style: TextStyle(color: Colors.grey[600], fontSize: 14)),
                  SizedBox(height: 16),
                ],
                Text('Quantity', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
                SizedBox(height: 8),
                Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    IconButton(
                      onPressed: () {
                        if (quantity > 1) {
                          setState(() {
                            quantity--;
                          });
                        }
                      },
                      icon: Icon(Icons.remove_circle_outline, color: quantity > 1 ? Colors.blue[600] : Colors.grey[400]),
                    ),
                    Container(
                      padding: EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                      decoration: BoxDecoration(
                        color: Colors.blue[50],
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(quantity.toString(), style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.blue[600])),
                    ),
                    IconButton(
                      onPressed: () {
                        if (quantity < 99) {
                          setState(() {
                            quantity++;
                          });
                        }
                      },
                      icon: Icon(Icons.add_circle_outline, color: quantity < 99 ? Colors.blue[600] : Colors.grey[400]),
                    ),
                  ],
                ),
                SizedBox(height: 16),
                Text('Special Notes (Optional)', style: TextStyle(fontWeight: FontWeight.w600, fontSize: 16)),
                SizedBox(height: 8),
                TextField(
                  controller: notesController,
                  decoration: InputDecoration(
                    hintText: 'Add special instructions...',
                    border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
                    contentPadding: EdgeInsets.all(12),
                  ),
                  maxLines: 2,
                  maxLength: 200,
                ),
              ],
            ),
            actions: [
              TextButton(
                onPressed: () => Get.back(),
                child: Text('Cancel', style: TextStyle(color: Colors.grey[600])),
              ),
              ElevatedButton(
                onPressed: () {
                  final notes = notesController.text.trim();
                  addToCart(menu, quantity: quantity, notes: notes.isEmpty ? null : notes);
                  Get.back();
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.blue[600],
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                ),
                child: Text('Add to Cart'),
              ),
            ],
          );
        },
      ),
    );
  }

  void _showSuccess(String title, String message) {
    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.BOTTOM,
      backgroundColor: Colors.green,
      colorText: Colors.white,
      duration: Duration(seconds: 2),
      margin: EdgeInsets.all(16),
    );
  }

  void _showError(String title, String message) {
    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.TOP,
      backgroundColor: Colors.red,
      colorText: Colors.white,
      margin: EdgeInsets.all(16),
    );
  }
}