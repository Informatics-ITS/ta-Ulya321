import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class ProfileController extends GetxController {
  final apiService = ApiService();
  final storage = GetStorage();
  
  var isLoading = false.obs;
  var isRefreshing = false.obs;
  var currentUser = Rx<User?>(null);

  final editFormKey = GlobalKey<FormState>();
  final editNameController = TextEditingController();
  final editPhoneController = TextEditingController();
  final editEmailController = TextEditingController();
  final editUsernameController = TextEditingController();
  final editCurrentPasswordController = TextEditingController();
  final editNewPasswordController = TextEditingController();
  final editConfirmPasswordController = TextEditingController();

  var hidePassword = true.obs;
  var hideNewPassword = true.obs;

  @override
  void onInit() {
    super.onInit();
    loadUserData();
  }

  void loadUserData() {
    final userData = storage.read('user');
    if (userData != null) {
      currentUser.value = User.fromJson(Map<String, dynamic>.from(userData));
    }
  }

  void loadUserDataToEdit() {
    if (currentUser.value != null) {
      editNameController.text = currentUser.value!.name;
      editPhoneController.text = currentUser.value!.phone;
      editEmailController.text = currentUser.value!.email;
      editUsernameController.text = currentUser.value!.username;
    }
  }

  Future<void> refreshProfile() async {
    if (isRefreshing.value) return;
    
    isRefreshing.value = true;
    loadUserData();
    await Future.delayed(Duration(seconds: 1));
    isRefreshing.value = false;
    
    _showSuccess('Profile refreshed successfully');
  }

  void showLogoutConfirmation() {
    Get.dialog(
      AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: Row(
          children: [
            Icon(Icons.logout, color: Colors.red[600]),
            SizedBox(width: 8),
            Text('Logout'),
          ],
        ),
        content: Text('Are you sure you want to logout?'),
        actions: [
          TextButton(
            onPressed: () => Get.back(),
            child: Text('Cancel', style: TextStyle(color: Colors.grey[600])),
          ),
          ElevatedButton(
            onPressed: () {
              Get.back();
              logout();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red[600],
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: Text('Logout'),
          ),
        ],
      ),
    );
  }

  Future<void> logout() async {
    await storage.remove('token');
    await storage.remove('user');
    await storage.remove('shopping_cart');
    
    currentUser.value = null;
    Get.offAllNamed('/login');
  }

  void showEditProfileModal() {
    loadUserDataToEdit();
    clearEditForm();

    Get.dialog(
      Dialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        child: Container(
          width: Get.width * 0.9,
          constraints: BoxConstraints(maxHeight: Get.height * 0.8, maxWidth: 500),
          child: SingleChildScrollView(
            padding: EdgeInsets.all(24),
            child: Form(
              key: editFormKey,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Row(
                    children: [
                      Icon(Icons.edit, color: Colors.blue[600]),
                      SizedBox(width: 8),
                      Expanded(
                        child: Text('Edit Profile', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
                      ),
                      IconButton(
                        onPressed: () => Get.back(),
                        icon: Icon(Icons.close, color: Colors.grey[600]),
                      ),
                    ],
                  ),
                  SizedBox(height: 24),
                  _buildEditTextField(
                    controller: editNameController,
                    validator: (value) => _validateRequired(value, 'Name'),
                    label: 'Full Name',
                    icon: Icons.person,
                  ),
                  SizedBox(height: 16),
                  _buildEditTextField(
                    controller: editPhoneController,
                    validator: (value) => _validateRequired(value, 'Phone'),
                    label: 'Phone Number',
                    icon: Icons.phone,
                    keyboardType: TextInputType.phone,
                  ),
                  SizedBox(height: 16),
                  _buildEditTextField(
                    controller: editEmailController,
                    validator: _validateEmail,
                    label: 'Email',
                    icon: Icons.email,
                    keyboardType: TextInputType.emailAddress,
                  ),
                  SizedBox(height: 16),
                  _buildEditTextField(
                    controller: editUsernameController,
                    validator: (value) => _validateRequired(value, 'Username'),
                    label: 'Username',
                    icon: Icons.account_circle,
                  ),
                  SizedBox(height: 20),
                  Divider(),
                  SizedBox(height: 16),
                  Text('Security', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                  SizedBox(height: 16),
                  Obx(() => _buildEditTextField(
                    controller: editCurrentPasswordController,
                    validator: (value) => _validateRequired(value, 'Current Password'),
                    label: 'Current Password',
                    icon: Icons.lock,
                    obscureText: hidePassword.value,
                    suffixIcon: IconButton(
                      icon: Icon(hidePassword.value ? Icons.visibility : Icons.visibility_off),
                      onPressed: () => hidePassword.value = !hidePassword.value,
                    ),
                  )),
                  SizedBox(height: 16),
                  Obx(() => _buildEditTextField(
                    controller: editNewPasswordController,
                    validator: _validateNewPassword,
                    label: 'New Password (Optional)',
                    icon: Icons.lock_outline,
                    obscureText: hideNewPassword.value,
                    suffixIcon: IconButton(
                      icon: Icon(hideNewPassword.value ? Icons.visibility : Icons.visibility_off),
                      onPressed: () => hideNewPassword.value = !hideNewPassword.value,
                    ),
                  )),
                  SizedBox(height: 16),
                  Obx(() => _buildEditTextField(
                    controller: editConfirmPasswordController,
                    validator: _validateConfirmPassword,
                    label: 'Confirm New Password',
                    icon: Icons.lock_outline,
                    obscureText: hideNewPassword.value,
                  )),
                  SizedBox(height: 24),
                  Row(
                    children: [
                      Expanded(
                        child: TextButton(
                          onPressed: () => Get.back(),
                          child: Text('Cancel', style: TextStyle(color: Colors.grey[600])),
                        ),
                      ),
                      SizedBox(width: 12),
                      Expanded(
                        child: Obx(() => ElevatedButton(
                          onPressed: isLoading.value ? null : updateProfile,
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.blue[600],
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
                          ),
                          child: isLoading.value
                              ? SizedBox(
                                  height: 20,
                                  width: 20,
                                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                                )
                              : Text('Update'),
                        )),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  Future<void> updateProfile() async {
    if (!editFormKey.currentState!.validate() || currentUser.value == null) return;

    isLoading.value = true;

    final updateData = {
      'name': editNameController.text.trim(),
      'phone': editPhoneController.text.trim(),
      'email': editEmailController.text.trim(),
      'username': editUsernameController.text.trim(),
      'current_password': editCurrentPasswordController.text,
    };

    if (editNewPasswordController.text.isNotEmpty) {
      updateData['password'] = editNewPasswordController.text;
      updateData['password_confirmation'] = editConfirmPasswordController.text;
    }

    final response = await apiService.updateProfile(currentUser.value!.userId, updateData);

    if (response.success && response.data != null) {
      await storage.write('user', response.data!.user.toJson());
      currentUser.value = response.data!.user;

      clearEditForm();
      Get.back();
      _showSuccess('Profile updated successfully');
    } else {
      _showError(response.message);
    }

    isLoading.value = false;
  }

  Widget _buildEditTextField({
    required TextEditingController controller,
    required String? Function(String?) validator,
    required String label,
    required IconData icon,
    bool obscureText = false,
    Widget? suffixIcon,
    TextInputType? keyboardType,
  }) {
    return TextFormField(
      controller: controller,
      validator: validator,
      obscureText: obscureText,
      keyboardType: keyboardType,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: Colors.blue[600], size: 20),
        suffixIcon: suffixIcon,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: BorderSide(color: Colors.blue[600]!, width: 2),
        ),
        contentPadding: EdgeInsets.symmetric(horizontal: 12, vertical: 16),
      ),
    );
  }

  String? _validateRequired(String? value, String field) {
    if (value == null || value.trim().isEmpty) {
      return '$field is required';
    }
    return null;
  }

  String? _validateEmail(String? value) {
    if (value == null || value.trim().isEmpty) {
      return 'Email is required';
    }
    if (!GetUtils.isEmail(value.trim())) {
      return 'Enter a valid email';
    }
    return null;
  }

  String? _validateNewPassword(String? value) {
    if (editNewPasswordController.text.isNotEmpty || editConfirmPasswordController.text.isNotEmpty) {
      if (value == null || value.isEmpty) {
        return 'New password is required';
      }
      if (value.length < 8) {
        return 'Password must be at least 8 characters';
      }
    }
    return null;
  }

  String? _validateConfirmPassword(String? value) {
    if (editNewPasswordController.text.isNotEmpty || editConfirmPasswordController.text.isNotEmpty) {
      if (value == null || value.isEmpty) {
        return 'Password confirmation is required';
      }
      if (value != editNewPasswordController.text) {
        return 'Passwords do not match';
      }
    }
    return null;
  }

  void clearEditForm() {
    editCurrentPasswordController.clear();
    editNewPasswordController.clear();
    editConfirmPasswordController.clear();
  }

  String getGreeting() {
    final hour = DateTime.now().hour;
    if (hour < 12) {
      return 'Good Morning';
    } else if (hour < 17) {
      return 'Good Afternoon';
    } else {
      return 'Good Evening';
    }
  }

  String formatJoinDate(String? createdAt) {
    if (createdAt == null) return 'Unknown';
    
    try {
      final date = DateTime.parse(createdAt);
      final months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
      return 'Joined ${months[date.month - 1]} ${date.year}';
    } catch (e) {
      return 'Unknown';
    }
  }

  String getInitials(String name) {
    if (name.isEmpty) return '?';
    
    final words = name.trim().split(' ');
    if (words.length >= 2) {
      return '${words[0][0]}${words[1][0]}'.toUpperCase();
    } else {
      return name.substring(0, 1).toUpperCase();
    }
  }

  void _showSuccess(String message) {
    Get.snackbar(
      'Success',
      message,
      backgroundColor: Colors.green,
      colorText: Colors.white,
      snackPosition: SnackPosition.TOP,
    );
  }

  void _showError(String message) {
    Get.snackbar(
      'Error',
      message,
      backgroundColor: Colors.red,
      colorText: Colors.white,
      snackPosition: SnackPosition.TOP,
    );
  }

  @override
  void onClose() {
    editNameController.dispose();
    editPhoneController.dispose();
    editEmailController.dispose();
    editUsernameController.dispose();
    editCurrentPasswordController.dispose();
    editNewPasswordController.dispose();
    editConfirmPasswordController.dispose();
    super.onClose();
  }
}