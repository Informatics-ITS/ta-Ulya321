import 'package:get/get.dart';
import '../views/login_view.dart';
import '../views/register_view.dart';
import '../views/home_view.dart';
import '../views/vendor_menu_view.dart';
import '../views/checkout_view.dart';
import '../views/order_confirmation_view.dart';
import '../bindings/auth_binding.dart';
import '../bindings/home_binding.dart';
import '../bindings/menu_binding.dart';
import '../bindings/order_binding.dart';
import '../bindings/vendor_binding.dart';
import 'app_routes.dart';

class AppPages {
  static const INITIAL = AppRoutes.LOGIN;

  static final routes = [
    GetPage(
      name: AppRoutes.LOGIN,
      page: () => LoginView(),
      binding: AuthBinding(),
    ),
    
    GetPage(
      name: AppRoutes.REGISTER,
      page: () => RegisterView(),
      binding: AuthBinding(),
    ),
    
    GetPage(
      name: AppRoutes.HOME,
      page: () => HomeView(),
      binding: HomeBinding(),
    ),
    
    GetPage(
      name: AppRoutes.MAIN,
      page: () => HomeView(),
      binding: HomeBinding(),
    ),
    
    GetPage(
      name: AppRoutes.VENDOR_MENU,
      page: () => VendorMenuView(),
      binding: MenuBinding(),
    ),
    
    GetPage(
      name: AppRoutes.CHECKOUT,
      page: () => CheckoutView(),
      binding: OrderBinding(),
    ),
    
    GetPage(
      name: AppRoutes.ORDER_CONFIRMATION,
      page: () => OrderConfirmationView(),
      binding: OrderBinding(),
    ),
  ];
}