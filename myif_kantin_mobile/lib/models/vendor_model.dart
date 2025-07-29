class Vendor {
  final int vendorId;
  final String name;
  final String description;
  final String? qrisImage;
  final String? qrisImageUrl;
  final bool isActive;
  final bool hasQris;

  Vendor({
    required this.vendorId,
    required this.name,
    required this.description,
    this.qrisImage,
    this.qrisImageUrl,
    required this.isActive,
    required this.hasQris,
  });

  factory Vendor.fromJson(Map<String, dynamic> json) {
    return Vendor(
      vendorId: json['vendor_id'] ?? 0,
      name: json['name'] ?? '',
      description: json['description'] ?? '',
      qrisImage: json['qris_image'],
      qrisImageUrl: json['qris_image_url'],
      isActive: json['is_active'] ?? true,
      hasQris: json['has_qris'] ?? false,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'vendor_id': vendorId,
      'name': name,
      'description': description,
      'qris_image': qrisImage,
      'qris_image_url': qrisImageUrl,
      'is_active': isActive,
      'has_qris': hasQris,
    };
  }

  Vendor copyWith({
    int? vendorId,
    String? name,
    String? description,
    String? qrisImage,
    String? qrisImageUrl,
    bool? isActive,
    bool? hasQris,
  }) {
    return Vendor(
      vendorId: vendorId ?? this.vendorId,
      name: name ?? this.name,
      description: description ?? this.description,
      qrisImage: qrisImage ?? this.qrisImage,
      qrisImageUrl: qrisImageUrl ?? this.qrisImageUrl,
      isActive: isActive ?? this.isActive,
      hasQris: hasQris ?? this.hasQris,
    );
  }
}