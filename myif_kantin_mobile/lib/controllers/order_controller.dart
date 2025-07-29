import 'package:get/get.dart';
import 'package:get_storage/get_storage.dart';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'dart:convert';
import 'dart:io';
import '../models/order_model.dart';
import '../models/order_request_model.dart';
import '../models/cart_model.dart';
import '../models/user_model.dart';
import '../services/api_service.dart';

class OrderController extends GetxController {
 final apiService = ApiService();
 final storage = GetStorage();

 var isLoading = false.obs;
 var isPlacingOrder = false.obs;
 var isUploadingProof = false.obs;
 var currentOrder = Rx<Order?>(null);
 var userOrders = <Order>[].obs;
 var selectedPaymentProof = Rx<File?>(null);
 var cart = Cart().obs;
 var currentUser = Rx<User?>(null);

 final roomNumberController = TextEditingController();
 final buildingNameController = TextEditingController();
 final deliveryNotesController = TextEditingController();

 var selectedPaymentMethod = 'qris'.obs;
 var shippingFee = 2000.obs;
 var selectedDeliveryTime = Rx<DateTime?>(null);

 final formKey = GlobalKey<FormState>();

 @override
 void onInit() {
   super.onInit();
   loadData();
 }

 void loadData() {
   loadCart();
   loadUser();
 }

 void loadCart() {
   try {
     final cartData = storage.read('shopping_cart');
     if (cartData != null && cartData is List && cartData.isNotEmpty) {
       cart.value = Cart.fromJson(List<dynamic>.from(cartData));
     }
   } catch (e) {
     cart.value = Cart();
   }
 }

 void loadUser() {
   final userData = storage.read('user');
   if (userData != null) {
     currentUser.value = User.fromJson(Map<String, dynamic>.from(userData));
   }
 }

 String? validateRoomNumber(String? value) {
   if (value == null || value.trim().isEmpty) {
     return 'Room number is required';
   }
   return null;
 }

 String? validateBuildingName(String? value) {
   if (value == null || value.trim().isEmpty) {
     return 'Building name is required';
   }
   return null;
 }

 void setPaymentMethod(String method) {
   selectedPaymentMethod.value = method;
   if (method != 'qris') {
     selectedPaymentProof.value = null;
   }
 }

 void setShippingFee(int fee) {
   if (fee >= 0) {
     shippingFee.value = fee;
   }
 }

 void setDeliveryTime(DateTime? time) {
   selectedDeliveryTime.value = time;
 }

 Future<void> selectDeliveryTime() async {
   final now = DateTime.now();
   final initialDate = selectedDeliveryTime.value ?? now.add(Duration(hours: 1));

   final date = await showDatePicker(
     context: Get.context!,
     initialDate: initialDate.isAfter(now) ? initialDate : now.add(Duration(hours: 1)),
     firstDate: now,
     lastDate: now.add(Duration(days: 7)),
   );

   if (date != null) {
     final time = await showTimePicker(
       context: Get.context!,
       initialTime: TimeOfDay.fromDateTime(initialDate),
     );

     if (time != null) {
       final selectedDateTime = DateTime(date.year, date.month, date.day, time.hour, time.minute);
       if (selectedDateTime.isAfter(now)) {
         selectedDeliveryTime.value = selectedDateTime;
       } else {
         _showError('Invalid Time', 'Please select a future date and time');
       }
     }
   }
 }

 String get formattedDeliveryTime {
   if (selectedDeliveryTime.value == null) return 'Select delivery time (optional)';

   try {
     final time = selectedDeliveryTime.value!;
     final months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
     return '${time.day} ${months[time.month - 1]} ${time.year} at ${time.hour.toString().padLeft(2, '0')}:${time.minute.toString().padLeft(2, '0')}';
   } catch (e) {
     return 'Select delivery time (optional)';
   }
 }

 int get totalPrice => cart.value.totalPrice + shippingFee.value;

 String get formattedTotalPrice {
   return 'Rp ${totalPrice.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}';
 }

 String get formattedShippingFee {
   return 'Rp ${shippingFee.value.toString().replaceAllMapped(RegExp(r'(\d{1,3})(?=(\d{3})+(?!\d))'), (Match m) => '${m[1]}.')}';
 }

 Future<void> pickPaymentProof(ImageSource source) async {
   try {
     final ImagePicker picker = ImagePicker();
     final XFile? image = await picker.pickImage(
       source: source,
       maxWidth: 1024,
       maxHeight: 1024,
       imageQuality: 80,
     );

     if (image == null) return;

     final file = File(image.path);
     if (!await file.exists()) {
       _showError('Error', 'Selected file does not exist');
       return;
     }

     final fileSize = await file.length();
     if (fileSize > 5 * 1024 * 1024) {
       _showError('File Too Large', 'Please select an image smaller than 5MB');
       return;
     }

     selectedPaymentProof.value = file;
     _showSuccess('Image Selected', 'Payment proof image selected successfully');
   } catch (e) {
     _showError('Error', 'Failed to pick image');
   }
 }

 void removePaymentProof() {
   selectedPaymentProof.value = null;
   _showMessage('Image Removed', 'Payment proof image removed', Colors.orange);
 }

 Future<void> uploadPaymentProof(int orderId, File imageFile) async {
   if (isUploadingProof.value || currentUser.value == null) return;

   try {
     isUploadingProof.value = true;

     if (!await imageFile.exists()) {
       _showError('File Error', 'Selected image file no longer exists');
       selectedPaymentProof.value = null;
       return;
     }

     final bytes = await imageFile.readAsBytes();
     final fileExtension = imageFile.path.split('.').last.toLowerCase();
     final base64Image = base64Encode(bytes);
     final paymentProofBase64 = 'data:image/$fileExtension;base64,$base64Image';

     final response = await apiService.uploadPaymentProof(
       orderId: orderId,
       userId: currentUser.value!.userId,
       paymentProofBase64: paymentProofBase64,
       fileExtension: fileExtension,
     );

     if (response.success) {
       _showSuccess('Success', 'Payment proof uploaded successfully');
       selectedPaymentProof.value = null;
       await getOrderById(orderId);
       await loadUserOrders();
     } else {
       _showError('Upload Failed', response.message);
     }
   } catch (e) {
     _showError('Error', 'Failed to upload payment proof');
   } finally {
     isUploadingProof.value = false;
   }
 }

 Future<void> placeOrder() async {
   if (isPlacingOrder.value || !formKey.currentState!.validate()) return;

   if (cart.value.isEmpty) {
     _showError('Empty Cart', 'Please add items to cart before placing order');
     return;
   }

   if (currentUser.value == null) {
     _showError('Authentication Error', 'Please login to place order');
     return;
   }

   try {
     isPlacingOrder.value = true;

     final orderItems = cart.value.items.map((cartItem) {
       return OrderItemRequest(
         menuId: cartItem.menu.menuId,
         quantity: cartItem.quantity,
         notes: cartItem.notes?.trim().isEmpty == true ? null : cartItem.notes?.trim(),
       );
     }).toList();

     final orderRequest = OrderRequest(
       userId: currentUser.value!.userId,
       roomNumber: roomNumberController.text.trim(),
       buildingName: buildingNameController.text.trim(),
       shippingFee: shippingFee.value,
       paymentMethod: selectedPaymentMethod.value,
       deliveryTime: selectedDeliveryTime.value,
       deliveryNotes: deliveryNotesController.text.trim().isEmpty ? null : deliveryNotesController.text.trim(),
       items: orderItems,
     );

     final response = await apiService.createOrder(orderRequest);

     if (response.success && response.data != null) {
       currentOrder.value = response.data!;
       
       storage.remove('shopping_cart');
       cart.value = Cart();
       _clearForm();

       _showSuccess('Order Placed Successfully!', 'Your order #${response.data!.orderId} has been placed.');
       Get.offNamed('/order-confirmation', arguments: response.data!.orderId);
     } else {
       _showError('Order Failed', response.message);
     }
   } catch (e) {
     _showError('Error', 'Failed to place order');
   } finally {
     isPlacingOrder.value = false;
   }
 }

 void _clearForm() {
   roomNumberController.clear();
   buildingNameController.clear();
   deliveryNotesController.clear();
   selectedPaymentMethod.value = 'qris';
   shippingFee.value = 2000;
   selectedDeliveryTime.value = null;
   selectedPaymentProof.value = null;
 }

 Future<void> loadUserOrders() async {
   if (isLoading.value) return;

   try {
     isLoading.value = true;
     final response = await apiService.getUserOrders();

     if (response.success) {
       userOrders.assignAll(response.data);
     }
   } catch (e) {
     _showError('Error', 'Failed to load orders');
   } finally {
     isLoading.value = false;
   }
 }

 Future<void> getOrderById(int orderId) async {
   try {
     isLoading.value = true;
     final response = await apiService.getOrderById(orderId);

     if (response.success && response.data != null) {
       currentOrder.value = response.data!;
     }
   } catch (e) {
     _showError('Error', 'Failed to load order details');
   } finally {
     isLoading.value = false;
   }
 }

 void _showSuccess(String title, String message) {
   Get.snackbar(
     title,
     message,
     snackPosition: SnackPosition.TOP,
     backgroundColor: Colors.green,
     colorText: Colors.white,
     duration: Duration(seconds: 3),
     margin: EdgeInsets.all(16),
   );
 }

 void _showError(String title, String message) {
   Get.snackbar(
     title,
     message,
     snackPosition: SnackPosition.TOP,
     backgroundColor: Colors.red,
     colorText: Colors.white,
     duration: Duration(seconds: 4),
     margin: EdgeInsets.all(16),
   );
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
}