import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:warranty_app/models/category.dart';
import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/services/helper.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';

class AddEquipmentPage extends StatefulWidget {
  @override
  _AddEquipmentPageState createState() => _AddEquipmentPageState();
}

class _AddEquipmentPageState extends State<AddEquipmentPage> {
  TextEditingController _nameTextEditingController = TextEditingController();
  TextEditingController _modelTextEditingController = TextEditingController();
  TextEditingController _brandTextEditingController = TextEditingController();
  TextEditingController _serialCodeTextEditingController = TextEditingController();
  TextEditingController _purchaseDateTextEditingController = TextEditingController();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  final HttpHelper httpHelper = HttpHelper();

  int selectedCategoryId = 0;

  String name = '';
  String? model = '';
  String? brand = '';
  String serialCode = '';
  String? purchaseDate = '';

  // Define the Equipment object
  Equipment equipment = Equipment(
    name: '',
    brand: null,
    model: null,
    serialCode: '',
    isActive: true,
    purchaseDate: null,
    category: 0,
  );

  List<Category>? categories;

  @override
  void initState() {
    super.initState();
    fetchCategories();
  }

  @override
  void dispose() {
    _nameTextEditingController.dispose();
    _modelTextEditingController.dispose();
    _brandTextEditingController.dispose();
    _serialCodeTextEditingController.dispose();
    _purchaseDateTextEditingController.dispose();
    super.dispose();
  }

  void _assignEquipmentObject() {
    setState(() {
      name = _nameTextEditingController.text;
      model = _modelTextEditingController.text;
      brand = _brandTextEditingController.text;
      serialCode =  _serialCodeTextEditingController.text;
      purchaseDate =  _purchaseDateTextEditingController.text;

      equipment.name = name;
      equipment.model = model;
      equipment.brand = brand;
      equipment.serialCode = serialCode;
      equipment.purchaseDate = purchaseDate;
      equipment.isActive = true;
      equipment.category = selectedCategoryId;
    });

    print('Name: $name');
    print('Model: $model');
    print('Brand: $brand');
    print('Serial Code: $serialCode');
    print('Purchase Date: $purchaseDate');
    print(equipment.name);
    print(equipment.model);
    print(equipment.brand);
    print(equipment.serialCode);
    print(equipment.purchaseDate);
    print(equipment.isActive);
    print(equipment.category);

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

  Future<void> _submitForm() async {
    if (_formKey.currentState!.validate()) {
      // Form is valid, perform the HTTP POST request here
      try {
        final addedEquipment = await httpHelper.postEquipment(equipment);
        Navigator.pop(context);
      } catch (e) {
        showDialog(
          context: context,
          builder: (context) => AlertDialog(
            title: Text('Error'),
            content: Text('An error occurred while adding equipment. Please try again later.'),
            actions: [
              TextButton(
                onPressed: () {
                  Navigator.pop(context);
                },
                child: Text('OK'),
              ),
            ],
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Add Equipment'),
        backgroundColor: mediumGreen,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16.0),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: <Widget>[
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: DropdownButtonFormField<int>(
                  value: selectedCategoryId,
                  onChanged: (value) {
                    setState(() {
                      selectedCategoryId = value!;
                    });
                  },
                  items: [
                    // Default "Select Category" item with a value of 0
                    DropdownMenuItem<int>(
                      value: 0,
                      child: Text('Select Category'),
                    ),
                    // Add your categories here
                    ...(categories?.map((Category category) {
                      return DropdownMenuItem<int>(
                        value: category.id,
                        child: Text(category.label),
                      );
                    }).toList() ?? []),
                  ],
                  decoration: const InputDecoration(
                    labelText: 'Category',
                    border: OutlineInputBorder(),
                  ),
                  validator: (value) {
                    if (value == null || value == 0) {
                      return 'Please select a category';
                    }
                    return null;
                  },
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _nameTextEditingController,
                  decoration: const InputDecoration(
                      labelText: 'Name',
                      border: OutlineInputBorder(),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _modelTextEditingController,
                  decoration: const InputDecoration(
                      labelText: 'Model',
                      border: OutlineInputBorder(),
                  )
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _brandTextEditingController,
                  decoration: const InputDecoration(
                      labelText: 'Brand',
                      border: OutlineInputBorder(),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _serialCodeTextEditingController,
                  decoration: const InputDecoration(
                      labelText: 'Serial Code',
                      border: OutlineInputBorder(),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(16.0),
                child: TextField(
                  controller: _purchaseDateTextEditingController,
                  decoration: const InputDecoration(
                    labelText: 'Purchase Date - YYYY-MM-DD',
                    border: OutlineInputBorder(), ),
                ),
              ),
              CustomButton(
                textColor: lightWhite,
                backgroundColor: mediumGreen,
                text: "Create",
                onPressed: _submitForm, // Call _submitForm when the button is pressed
              ),
            ],
          ),
        ),
      ),
    );
  }
}