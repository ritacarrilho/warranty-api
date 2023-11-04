
import 'package:flutter/material.dart';
import 'package:warranty_app/models/category.dart';
import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/services/helper.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/bottom_nav_menu.dart';
import 'package:warranty_app/widgets/equipments_list_card.dart';
import 'package:warranty_app/widgets/horizontal_category_menu.dart';

class EquipmentsPage extends StatefulWidget {
  @override
  State<EquipmentsPage> createState() => _EquipmentsPageState();
}

class _EquipmentsPageState extends State<EquipmentsPage> {
  int? selectedCategoryId;
  List<Category>? categories;
  List<Equipment> equipmentList = []; // Equipment list
  String searchQuery = '';
  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  @override
  void initState() {
    super.initState();
    fetchCategories();
  }

  Future<void> fetchCategories() async {
    try {
      final HttpHelper httpHelper = HttpHelper(); // Instantiate your HTTP helper class
      final List<Category>? fetchedCategories = await httpHelper.getCategories();

      if (fetchedCategories != null) {
        setState(() {
          categories = fetchedCategories;
        });
      }
    } catch (e) {
      print('Error: $e');
    }
  }

  // Fetch equipment for the selected category
  Future<void> fetchEquipments(int categoryId) async {
    try {
      final HttpHelper httpHelper = HttpHelper(); // Instantiate your HTTP helper class
      final List<Equipment>? fetchedEquipments =
      await httpHelper.getEquipments(); // Fetch all equipment

      if (fetchedEquipments != null) {
        List<Equipment> filteredEquipments = [];

        for (Equipment equipment in fetchedEquipments) {
          if (equipment.category == categoryId) {
            filteredEquipments.add(equipment);
          }
        }

        setState(() {
          equipmentList = filteredEquipments;
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
    return Scaffold(
      appBar: AppBar(
        title: Text('Equipments'),
        backgroundColor: mediumGreen,
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            SizedBox(height: 160),
            if (categories == null)
              CircularProgressIndicator()
            else
              HorizontalCategoryMenu(
                categories: categories,
                onCategorySelected: (categoryId) {
                  setState(() {
                    selectedCategoryId = categoryId;
                    print(selectedCategoryId);
                    fetchEquipments(categoryId); // Fetch equipment for the selected category
                  });
                },
              ),
            SizedBox(height: 16),
            Column(
              children: [
                Padding(
                  padding: const EdgeInsets.all(16.0),
                  child: TextField(
                    onChanged: (value) {
                      setState(() {
                        searchQuery = value.toLowerCase(); // Update the search query
                      });
                    },
                    decoration: InputDecoration(
                      hintText: 'Search Equipment by Name',
                      prefixIcon: Icon(Icons.search),
                    ),
                  ),
                ),
              ]
            ),
            if (equipmentList.isEmpty)
              Text('No equipment available for this category')
            else
              Container(
                height: 90.0, // Adjust the height as needed
                child: PageView.builder(
                  itemCount: equipmentList.length,
                  itemBuilder: (context, index) {
                  return EquipmentsListCard(
                    equipment: equipmentList[index],
                  );
                  },
                ),
              )
          ],
        ),
      ),
      // bottomNavigationBar: BottomNavigation(
        //selectedIndex: _selectedIndex,
        //onItemTapped: _onItemTapped,
      //),
    );
  }
}