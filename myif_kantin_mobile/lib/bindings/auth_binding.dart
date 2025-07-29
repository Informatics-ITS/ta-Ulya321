import 'package:get/get.dart';
import '../controllers/auth_controller.dart';
import '../services/api_service.dart';

class AuthBinding extends Bindings {
  @override
  void dependencies() {
    Get.lazyPut<ApiService>(() => ApiService(), fenix: true);
    Get.lazyPut<AuthController>(() => AuthController(), fenix: true);
  }
}