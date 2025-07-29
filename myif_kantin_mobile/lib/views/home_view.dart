import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/main_controller.dart';
import '../controllers/cart_controller.dart';
import '../fragments/home_fragment.dart';
import '../fragments/cart_fragment.dart';
import '../fragments/orders_fragment.dart';
import '../fragments/profile_fragment.dart';

class HomeView extends StatelessWidget {
  final mainController = Get.put(MainController());
  final cartController = Get.put(CartController());

  final List<Widget> fragments = [
    HomeFragment(),
    CartFragment(),
    OrdersFragment(),
    ProfileFragment(),
  ];

  @override
  Widget build(BuildContext context) {
    if (Get.arguments != null && Get.arguments is int) {
      final tabIndex = Get.arguments as int;
      if (tabIndex >= 0 && tabIndex < fragments.length) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          mainController.changeTabIndex(tabIndex);
        });
      }
    }

    return Obx(() {
      final currentIndex = mainController.currentIndex.value;
      
      return Scaffold(
        body: IndexedStack(
          index: currentIndex,
          children: fragments,
        ),
        bottomNavigationBar: BottomNavigationBar(
          type: BottomNavigationBarType.fixed,
          currentIndex: currentIndex,
          onTap: mainController.changeTabIndex,
          selectedItemColor: Colors.blue[600],
          unselectedItemColor: Colors.grey[600],
          backgroundColor: Colors.white,
          elevation: 8,
          selectedFontSize: 12,
          unselectedFontSize: 11,
          items: [
            BottomNavigationBarItem(
              icon: Icon(Icons.home),
              activeIcon: Icon(Icons.home, color: Colors.blue[600]),
              label: 'Home',
            ),
            BottomNavigationBarItem(
              icon: _buildCartIcon(),
              activeIcon: _buildCartIcon(isActive: true),
              label: 'Cart',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.receipt_long),
              activeIcon: Icon(Icons.receipt_long, color: Colors.blue[600]),
              label: 'Orders',
            ),
            BottomNavigationBarItem(
              icon: Icon(Icons.person),
              activeIcon: Icon(Icons.person, color: Colors.blue[600]),
              label: 'Profile',
            ),
          ],
        ),
      );
    });
  }

  Widget _buildCartIcon({bool isActive = false}) {
    return Obx(() {
      final totalItems = cartController.totalItems;
      final iconColor = isActive ? Colors.blue[600] : Colors.grey[600];
      
      return Stack(
        clipBehavior: Clip.none,
        children: [
          Icon(Icons.shopping_cart, color: iconColor),
          if (totalItems > 0)
            Positioned(
              right: -6,
              top: -6,
              child: Container(
                padding: EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                decoration: BoxDecoration(
                  color: Colors.red[600],
                  borderRadius: BorderRadius.circular(10),
                ),
                constraints: BoxConstraints(minWidth: 16, minHeight: 16),
                child: Text(
                  totalItems > 99 ? '99+' : totalItems.toString(),
                  style: TextStyle(
                    color: Colors.white,
                    fontSize: 10,
                    fontWeight: FontWeight.bold,
                  ),
                  textAlign: TextAlign.center,
                ),
              ),
            ),
        ],
      );
    });
  }
}