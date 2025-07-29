import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class AuthController extends GetxController {
  final storage = GetStorage();
  late final ApiService apiService;
  
  var isLoading = false.obs;
  var isLoggedIn = false.obs;
  var currentUser = Rx<User?>(null);
  var hidePassword = true.obs;

  late final GlobalKey<FormState> _loginFormKey;
  late final GlobalKey<FormState> _registerFormKey;
  
  GlobalKey<FormState> get loginFormKey => _loginFormKey;
  GlobalKey<FormState> get registerFormKey => _registerFormKey;

  final loginUsernameController = TextEditingController();
  final loginPasswordController = TextEditingController();

  final registerNameController = TextEditingController();
  final registerUsernameController = TextEditingController();
  final registerEmailController = TextEditingController();
  final registerPhoneController = TextEditingController();
  final registerPasswordController = TextEditingController();
  final registerPasswordConfirmationController = TextEditingController();

  @override
  void onInit() {
    super.onInit();
    _loginFormKey = GlobalKey<FormState>(debugLabel: 'LoginForm_${DateTime.now().millisecondsSinceEpoch}');
    _registerFormKey = GlobalKey<FormState>(debugLabel: 'RegisterForm_${DateTime.now().millisecondsSinceEpoch}');
    
    apiService = Get.put(ApiService());
    checkLoginStatus();
  }

  @override
  void onClose() {
    loginUsernameController.dispose();
    loginPasswordController.dispose();
    registerNameController.dispose();
    registerUsernameController.dispose();
    registerEmailController.dispose();
    registerPhoneController.dispose();
    registerPasswordController.dispose();
    registerPasswordConfirmationController.dispose();
    super.onClose();
  }

  void checkLoginStatus() {
    final token = storage.read('token');
    final userData = storage.read('user');
    
    if (token != null && userData != null) {
      isLoggedIn.value = true;
      currentUser.value = User.fromJson(Map<String, dynamic>.from(userData));
    }
  }

  void togglePasswordVisibility() {
    hidePassword.value = !hidePassword.value;
  }

  String? validateRequired(String? value, String field) {
    if (value == null || value.trim().isEmpty) {
      return '$field is required';
    }
    return null;
  }

  String? validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email is required';
    }
    if (!GetUtils.isEmail(value.trim())) {
      return 'Enter a valid email';
    }
    return null;
  }

  String? validatePassword(String? value) {
    if (value == null || value.isEmpty) {
      return 'Password is required';
    }
    if (value.length < 8) {
      return 'Password must be at least 8 characters';
    }
    return null;
  }

  String? validatePasswordConfirmation(String? value) {
    if (value != registerPasswordController.text) {
      return 'Passwords do not match';
    }
    return null;
  }

  void clearLoginForm() {
    loginUsernameController.clear();
    loginPasswordController.clear();
    if (_loginFormKey.currentState != null) {
      _loginFormKey.currentState!.reset();
    }
  }

  void clearRegisterForm() {
    registerNameController.clear();
    registerUsernameController.clear();
    registerEmailController.clear();
    registerPhoneController.clear();
    registerPasswordController.clear();
    registerPasswordConfirmationController.clear();
    if (_registerFormKey.currentState != null) {
      _registerFormKey.currentState!.reset();
    }
  }

  Future<void> login() async {
    if (!_loginFormKey.currentState!.validate()) return;
    
    isLoading.value = true;
    
    try {
      final response = await apiService.login(
        loginUsernameController.text.trim(),
        loginPasswordController.text,
      );

      if (response.success && response.data != null) {
        await storage.write('token', response.data!.token);
        await storage.write('user', response.data!.user.toJson());
        
        isLoggedIn.value = true;
        currentUser.value = response.data!.user;
        
        clearLoginForm();
        Get.offAllNamed('/home');
        _showSuccess('Login successful');
      } else {
        _showError('Login Failed', response.message);
      }
    } catch (e) {
      _showError('Login Failed', 'An unexpected error occurred');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> register() async {
    if (!_registerFormKey.currentState!.validate()) return;
    
    isLoading.value = true;
    
    try {
      final response = await apiService.register(
        name: registerNameController.text.trim(),
        username: registerUsernameController.text.trim(),
        email: registerEmailController.text.trim(),
        phone: registerPhoneController.text.trim(),
        password: registerPasswordController.text,
        passwordConfirmation: registerPasswordConfirmationController.text,
      );

      if (response.success) {
        clearRegisterForm();
        Get.offNamed('/login');
        _showSuccess('Registration successful. Please login.');
      } else {
        _showError('Registration Failed', response.message);
      }
    } catch (e) {
      _showError('Registration Failed', 'An unexpected error occurred');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> logout() async {
    await storage.remove('token');
    await storage.remove('user');
    await storage.remove('shopping_cart');
    
    isLoggedIn.value = false;
    currentUser.value = null;
    
    clearLoginForm();
    clearRegisterForm();
    Get.offAllNamed('/login');
  }

  void _showSuccess(String message) {
    Get.snackbar(
      'Success',
      message,
      backgroundColor: Colors.green,
      colorText: Colors.white,
      snackPosition: SnackPosition.TOP,
      duration: Duration(seconds: 3),
    );
  }

  void _showError(String title, String message) {
    Get.snackbar(
      title,
      message,
      backgroundColor: Colors.red,
      colorText: Colors.white,
      snackPosition: SnackPosition.TOP,
      duration: Duration(seconds: 4),
    );
  }
}