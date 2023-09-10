class Category {
  int id;
  String label;

  Category({ required this.id, required this.label});

  factory Category.fromJson(Map<String, dynamic> parsedJson) {
    return Category(
      id: parsedJson['id'],
      label: parsedJson['label'],
    );
  }
}