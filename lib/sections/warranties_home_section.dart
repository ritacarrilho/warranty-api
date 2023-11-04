import 'package:flutter/material.dart';
import 'package:warranty_app/models/warranty.dart';
import 'package:warranty_app/services/helper.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/warranty_home_card.dart';


class WarrantiesHomeSection extends StatefulWidget {
  @override
  _WarrantiesHomeSectionState createState() => _WarrantiesHomeSectionState();
}

class _WarrantiesHomeSectionState extends State<WarrantiesHomeSection> {
  List<Warranty> warranties = [];
  HttpHelper helper = HttpHelper();

  @override
  void initState() {
    super.initState();
    helper = HttpHelper();
    _loadWarranties(); // Call the async function to load warranties
  }

  // Define an async function to load warranties
  Future<void> _loadWarranties() async {
    try {
      final loadedWarranties = await helper.getWarranties();
      if (loadedWarranties != null) {
        setState(() {
          warranties = loadedWarranties;
        });
      } else {
        // Handle error
      }
    } catch (e) {
      // Handle the exception
    }
  }

  @override
  Widget build(BuildContext context) {

    return Container(
          height: 200.0, // Adjust the height as needed
          child: PageView.builder(
            itemCount: warranties.length,
            itemBuilder: (context, index) {
              return WarrantyHomeCard(
                warranty: warranties[index],
              );
            },
          ),
        );
  }
}