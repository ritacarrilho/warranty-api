import 'package:flutter/material.dart';
import 'package:warranty_app/models/warranty.dart';

class WarrantyDetailPage extends StatelessWidget {
  final Warranty warranty;

  WarrantyDetailPage(this.warranty);

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Warranty Detail'),
      ),
      body: Center(
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
            ),
            // Add more details or content here
          ],
        ),
      ),
    );
  }
}