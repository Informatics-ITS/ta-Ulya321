class User {
  final int userId;
  final String name;
  final String phone;
  final String email;
  final String username;
  final String role;
  final String? createdAt;

  User({
    required this.userId,
    required this.name,
    required this.phone,
    required this.email,
    required this.username,
    required this.role,
    this.createdAt,
  });

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      userId: json['user_id'] ?? 0,
      name: json['name'] ?? '',
      phone: json['phone'] ?? '',
      email: json['email'] ?? '',
      username: json['username'] ?? '',
      role: json['role'] ?? 'user',
      createdAt: json['created_at'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'user_id': userId,
      'name': name,
      'phone': phone,
      'email': email,
      'username': username,
      'role': role,
      'created_at': createdAt,
    };
  }
}