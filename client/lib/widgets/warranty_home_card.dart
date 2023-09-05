import 'package:flutter/material.dart';
import 'package:warranty_app/models/warranty.dart';
import 'package:warranty_app/pages/warranty_detail.dart';
import 'package:warranty_app/utils/constants.dart';

class WarrantyHomeCard extends StatelessWidget {
  final Warranty warranty;

  WarrantyHomeCard({required this.warranty});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () {
        // Navigate to the warranty detail page when the card is tapped
        Navigator.push(
          context,
          MaterialPageRoute(
            builder: (context) => WarrantyDetailPage(warranty),
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
              'Reference: ${warranty.reference}',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            Text(
              'Expire Date: ${warranty.endDate}',
              style: TextStyle(fontWeight: FontWeight.bold),
            )
          ],
        ),
      ),
    );
  }
}