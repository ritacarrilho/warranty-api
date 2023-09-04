class Equipment {
  String id;
  String reference;
  DateTime? startDate;
  DateTime endDate;

  Equipment({ required this.id, required this.reference, this.startDate, required this.endDate});

  factory Equipment.fromJson(Map<String, dynamic> parsedJson) {
    return Equipment(
      id: parsedJson['id'],
      reference: parsedJson['reference'],
      startDate: parsedJson['start_date'],
      endDate: parsedJson['label'],
    );
  }}