import 'package:get/get.dart';

class MainController extends GetxController {
  var currentIndex = 0.obs;

  void changeTabIndex(int index) {
    currentIndex.value = index;
  }

  void goToHome() {
    currentIndex.value = 0;
  }

  void goToCart() {
    currentIndex.value = 1;
  }

  void goToOrders() {
    currentIndex.value = 2;
  }

  void goToProfile() {
    currentIndex.value = 3;
  }
}