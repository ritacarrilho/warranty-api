
import 'package:flutter/material.dart';
import 'package:warranty_app/models/category.dart';
import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/services/helper.dart';

class HorizontalCategoryMenu extends StatefulWidget {
  final List<Category>? categories;
  final void Function(int categoryId)? onCategorySelected; // Define the callback

  HorizontalCategoryMenu({Key? key, this.categories, this.onCategorySelected}) : super(key: key);

  @override
  _HorizontalCategoryMenuState createState() => _HorizontalCategoryMenuState();
}

class _HorizontalCategoryMenuState extends State<HorizontalCategoryMenu> {
  List<Category>? categories; // List to store fetched categories
  final HttpHelper httpHelper = HttpHelper(); // Instantiate your HTTP helper class
  List<Equipment> equipmentList = [];

  @override
  void initState() {
    super.initState();
    fetchCategories();
    fetchEquipments()
;  }

  Future<void> fetchCategories() async {
    final List<Category>? fetchedCategories = await httpHelper.getCategories();
      print(fetchedCategories);
    if (fetchedCategories != null) {
      setState(() {
        categories = fetchedCategories;
      });
    }
  }

  Future<void> fetchEquipments() async {
    try {
      final List<Equipment>? fetchedEquipments = await httpHelper.getEquipments();
      if (fetchedEquipments != null) {
        setState(() {
          equipmentList = fetchedEquipments;
        });
      } else {
        throw Exception('Failed to load equipment');
      }
    } catch (e) {
      print('Error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      scrollDirection: Axis.horizontal,
      child: Row(
        children: <Widget>[
          if (categories == null)
            CircularProgressIndicator() // Display a loading indicator while fetching data
          else
            for (Category category in categories!)
              GestureDetector(
                onTap: () {
                  if (widget.onCategorySelected != null) {
                    print(category.id);
                    print(equipmentList);
                    widget.onCategorySelected!(category.id); // Trigger the callback with the selected category ID
                  }
                },
                child: Card(
                  elevation: 2.0, // Card elevation
                  child: Padding(
                    padding: const EdgeInsets.all(8.0),
                    child: Text(category.label), // Display category name
                  ),
                ),
              ),
        ],
      ),
    );
  }
}
