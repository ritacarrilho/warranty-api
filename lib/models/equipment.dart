import 'package:flutter/foundation.dart';

class Equipment {
  int? id;
  String name;
  String? brand;
  String? model;
  String serialCode;
  bool isActive;
  String? purchaseDate;
  int category;

  Equipment({ this.id, required this.name, this.brand, this.model, required this.serialCode, required this.isActive, this.purchaseDate, required this.category});

  factory Equipment.fromJson(Map<String, dynamic> parsedJson) {
    return Equipment(
      id: parsedJson['id'],
      name: parsedJson['name'],
      brand: parsedJson['brand'],
      serialCode: parsedJson['serial_code'],
      isActive: parsedJson['is_active'],
      purchaseDate: parsedJson['purchase_date'],
      category: parsedJson['category'],
    );
  }

    Map<String, dynamic> toJson() {
      return {
        'name': name,
        'brand': brand,
        'model': model,
        'serial_code': serialCode,
        'is_active': isActive,
        'purchase_date': purchaseDate,
        'category': category,
      };
    }
  }
