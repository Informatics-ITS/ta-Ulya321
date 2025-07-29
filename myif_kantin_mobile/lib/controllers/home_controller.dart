import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:flutter/material.dart';
import '../models/user_model.dart';
import '../models/vendor_model.dart';
import '../services/api_service.dart';
import 'dart:async';

class HomeController extends GetxController {
  final storage = GetStorage();
  final apiService = ApiService();
  
  var isLoading = false.obs;
  var isRefreshing = false.obs;
  var currentUser = Rx<User?>(null);
  
  var isSearching = false.obs;
  var isLoadingSearch = false.obs;
  var searchResults = <Vendor>[].obs;
  var searchText = ''.obs;
  
  final searchController = TextEditingController();
  Timer? _debounceTimer;

  @override
  void onInit() {
    super.onInit();
    loadUserData();
  }

  @override
  void onClose() {
    searchController.dispose();
    _debounceTimer?.cancel();
    super.onClose();
  }

  void loadUserData() {
    final userData = storage.read('user');
    if (userData != null) {
      currentUser.value = User.fromJson(Map<String, dynamic>.from(userData));
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

  void onSearchChanged(String query) {
    searchText.value = query;
    _debounceTimer?.cancel();
    
    if (query.trim().isEmpty) {
      clearSearch();
      return;
    }

    isSearching.value = true;
    
    _debounceTimer = Timer(Duration(milliseconds: 500), () {
      performSearch(query.trim());
    });
  }

  Future<void> performSearch(String query) async {
    if (query.isEmpty) return;

    try {
      isLoadingSearch.value = true;
      searchResults.clear();

      final response = await apiService.searchContent(query);
      
      if (response.success) {
        searchResults.assignAll(response.vendors);
      }
    } catch (e) {
      _showError('Search failed');
    } finally {
      isLoadingSearch.value = false;
    }
  }

  void clearSearch() {
    searchController.clear();
    searchText.value = '';
    isSearching.value = false;
    isLoadingSearch.value = false;
    searchResults.clear();
    _debounceTimer?.cancel();
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
      final months = [
        'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
        'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
      ];
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
}