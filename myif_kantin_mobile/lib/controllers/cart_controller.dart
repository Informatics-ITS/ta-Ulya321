import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:flutter/material.dart';
import '../models/cart_model.dart';
import '../models/menu_model.dart';
import '../models/vendor_model.dart';

class CartController extends GetxController {
  final storage = GetStorage();
  static const String cartKey = 'shopping_cart';

  var cart = Cart().obs;

  @override
  void onInit() {
    super.onInit();
    loadCart();
  }

  void loadCart() {
    try {
      final cartData = storage.read(cartKey);
      if (cartData != null && cartData is List && cartData.isNotEmpty) {
        cart.value = Cart.fromJson(List<dynamic>.from(cartData));
      } else {
        cart.value = Cart();
      }
      cart.refresh();
    } catch (e) {
      print('Error loading cart: $e');
      cart.value = Cart();
      storage.remove(cartKey);
    }
  }

  void saveCart() {
    try {
      storage.write(cartKey, cart.value.toJson());
      cart.refresh();
    } catch (e) {
      print('Error saving cart: $e');
      _showError('Error', 'Failed to save cart');
    }
  }

  void addToCart({
    required Menu menu,
    required Vendor vendor,
    int quantity = 1,
    String? notes,
  }) {
    if (quantity <= 0 || quantity > 99) {
      _showError('Error', 'Invalid quantity');
      return;
    }

    try {
      final currentItems = List<CartItem>.from(cart.value.items);
      final existingIndex = currentItems.indexWhere(
        (item) => item.menu.menuId == menu.menuId && item.vendor.vendorId == vendor.vendorId,
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
          vendor: vendor,
          quantity: quantity,
          notes: notes?.trim(),
        ));
      }

      cart.value = Cart(items: currentItems);
      saveCart();
      _showMessage('Added to Cart', '${menu.name} x$quantity added to cart', Colors.green);
    } catch (e) {
      print('Error adding to cart: $e');
      _showError('Error', 'Failed to add item to cart');
    }
  }

  void removeFromCart(int menuId, int vendorId) {
    try {
      final currentItems = List<CartItem>.from(cart.value.items);
      final removedItem = currentItems.firstWhereOrNull(
        (item) => item.menu.menuId == menuId && item.vendor.vendorId == vendorId,
      );

      currentItems.removeWhere(
        (item) => item.menu.menuId == menuId && item.vendor.vendorId == vendorId,
      );

      cart.value = Cart(items: currentItems);
      saveCart();

      if (removedItem != null) {
        _showMessage('Removed', '${removedItem.menu.name} removed from cart', Colors.orange);
      }
    } catch (e) {
      print('Error removing from cart: $e');
      _showError('Error', 'Failed to remove item');
    }
  }

  void updateQuantity(int menuId, int vendorId, int quantity) {
    if (quantity < 0 || quantity > 99) return;

    try {
      final currentItems = List<CartItem>.from(cart.value.items);
      final index = currentItems.indexWhere(
        (item) => item.menu.menuId == menuId && item.vendor.vendorId == vendorId,
      );

      if (index != -1) {
        if (quantity <= 0) {
          currentItems.removeAt(index);
        } else {
          currentItems[index].quantity = quantity;
        }
      }

      cart.value = Cart(items: currentItems);
      saveCart();
    } catch (e) {
      print('Error updating quantity: $e');
      _showError('Error', 'Failed to update quantity');
    }
  }

  void increaseQuantity(int menuId, int vendorId) {
    final item = cart.value.items.firstWhereOrNull(
      (item) => item.menu.menuId == menuId && item.vendor.vendorId == vendorId,
    );
    
    if (item != null) {
      updateQuantity(menuId, vendorId, item.quantity + 1);
    }
  }

  void decreaseQuantity(int menuId, int vendorId) {
    final item = cart.value.items.firstWhereOrNull(
      (item) => item.menu.menuId == menuId && item.vendor.vendorId == vendorId,
    );
    
    if (item != null) {
      if (item.quantity > 1) {
        updateQuantity(menuId, vendorId, item.quantity - 1);
      } else {
        removeFromCart(menuId, vendorId);
      }
    }
  }

  void clearCart() {
    cart.value = Cart();
    saveCart();
    _showMessage('Cart Cleared', 'All items removed from cart', Colors.red);
  }

  void showClearCartConfirmation() {
    Get.dialog(
      AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(15)),
        title: Row(
          children: [
            Icon(Icons.warning, color: Colors.red[600]),
            SizedBox(width: 8),
            Text('Clear Cart'),
          ],
        ),
        content: Text('Are you sure you want to remove all items from cart?'),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('Cancel', style: TextStyle(color: Colors.grey[600])),
          ),
          ElevatedButton(
            onPressed: () {
              Get.back();
              clearCart();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: Text('Clear Cart'),
          ),
        ],
      ),
    );
  }

  void goToCheckout() {
    if (cart.value.isEmpty) {
      _showMessage('Cart Empty', 'Please add items to cart first', Colors.orange);
      return;
    }

    final storage = GetStorage();
    final token = storage.read('token');
    
    if (token == null) {
      _showMessage('Login Required', 'Please login to proceed with checkout', Colors.red);
      Get.toNamed('/login');
      return;
    }

    Get.toNamed('/checkout');
  }

  void _showMessage(String title, String message, Color backgroundColor) {
    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.BOTTOM,
      backgroundColor: backgroundColor,
      colorText: Colors.white,
      duration: Duration(seconds: 2),
      margin: EdgeInsets.all(16),
    );
  }

  void _showError(String title, String message) {
    _showMessage(title, message, Colors.red);
  }

  int get totalItems => cart.value.totalItems;
  int get totalPrice => cart.value.totalPrice;
  String get formattedTotalPrice => cart.value.formattedTotalPrice;
  bool get isEmpty => cart.value.isEmpty;
  bool get isNotEmpty => cart.value.isNotEmpty;
}