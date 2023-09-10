import 'package:flutter/material.dart';
import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/pages/equipment_detail_page.dart';
import 'package:warranty_app/services/helper.dart';

class EquipmentsListCard extends StatelessWidget {
  final Equipment equipment;

  EquipmentsListCard({required this.equipment});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        // Navigate to the warranty detail page when the card is tapped
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => EquipmentDetailPage(equipment),
          ),
        );
      },
      child: Card(
        color: Colors.white,
        elevation: 2.0,
        margin: EdgeInsets.all(10.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(
              equipment.name,
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            Text(
              equipment.serialCode,
              style: TextStyle(fontWeight: FontWeight.w400),
            )
          ],
        ),
      ),
    );
  }
}