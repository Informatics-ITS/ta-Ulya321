import 'package:get/get.dart';
import 'package:flutter/material.dart';
import '../models/vendor_model.dart';
import '../services/api_service.dart';

class VendorController extends GetxController {
  final apiService = ApiService();

  var vendors = <Vendor>[].obs;
  var isLoading = false.obs;
  var isRefreshing = false.obs;

  @override
  void onInit() {
    super.onInit();
    loadVendors();
  }

  Future<void> loadVendors() async {
    if (isLoading.value) return;

    try {
      isLoading.value = true;
      final response = await apiService.getVendors();

      print(response);
      
      if (response.success) {
        vendors.assignAll(response.data);
      } else {
        _showError('Error', response.message);
      }
    } catch (e) {
      _showError('Error', 'Failed to load vendors');
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> refreshVendors() async {
    if (isRefreshing.value) return;

    try {
      isRefreshing.value = true;
      final response = await apiService.getVendors();
      
      if (response.success) {
        vendors.assignAll(response.data);
        _showSuccess('Success', 'Vendors refreshed');
      } else {
        _showError('Error', response.message);
      }
    } catch (e) {
      _showError('Error', 'Failed to refresh vendors');
    } finally {
      isRefreshing.value = false;
    }
  }

  void goToVendorMenu(Vendor vendor) {
    if (vendor.vendorId <= 0) {
      _showError('Error', 'Invalid vendor selected');
      return;
    }

    Get.toNamed('/vendor-menu', arguments: vendor);
  }

  void _showSuccess(String title, String message) {
    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.TOP,
      backgroundColor: Colors.green,
      colorText: Colors.white,
      duration: Duration(seconds: 1),
    );
  }

  void _showError(String title, String message) {
    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.TOP,
      backgroundColor: Colors.red,
      colorText: Colors.white,
    );
  }
}