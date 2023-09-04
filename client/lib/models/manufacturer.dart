import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/models/equipment.dart';

class Manufacturer {
  String id;
  String reference;
  DateTime? startDate;
  DateTime endDate;
  Equipment equipmentId;

  Manufacturer({ required this.id, required this.reference, this.startDate, required this.endDate, required this.equipmentId});

  factory Manufacturer.fromJson(Map<String, dynamic> parsedJson) {
    return Manufacturer(
      id: parsedJson['id'],
      reference: parsedJson['reference'],
      startDate: parsedJson['start_date'],
      endDate: parsedJson['label'],
      equipmentId: parsedJson['label'],

    );
  }
}