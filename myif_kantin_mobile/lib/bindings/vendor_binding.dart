import 'package:get/get.dart';
import '../controllers/vendor_controller.dart';
import '../controllers/cart_controller.dart';
import '../services/api_service.dart';

class VendorBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<ApiService>(() => ApiService(), fenix: true);
    Get.lazyPut<CartController>(() => CartController(), fenix: true);
    Get.lazyPut<VendorController>(() => VendorController(), fenix: true);
  }
}