import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/models/manufacturer.dart';

class Warranty {
  int id;
  String reference;
  String? startDate;
  String endDate;
  int equipmentId;
  int? manufacturerId;

  Warranty({ required this.id, required this.reference, this.startDate, required this.endDate, required this.equipmentId, this.manufacturerId});

  factory Warranty.fromJson(Map<String, dynamic> parsedJson) {
    return Warranty(
      id: parsedJson['id'],
      reference: parsedJson['reference'],
      startDate: parsedJson['start_date'],
      endDate: parsedJson['end_date'],
      equipmentId: parsedJson['equipment_id'],
      manufacturerId: parsedJson['manufacturer_id'],
    );
  }
}