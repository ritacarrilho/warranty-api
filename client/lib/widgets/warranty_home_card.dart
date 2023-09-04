import 'package:flutter/material.dart';
import 'package:warranty_app/models/warranty.dart';

class WarrantyHomeCard extends StatelessWidget {
  final Warranty warranty;

  WarrantyHomeCard({required this.warranty});

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Card(
        elevation: 0,
        color: Theme.of(context).colorScheme.surfaceVariant,
        child: SizedBox(
          width: 300,
          height: 100,
          child: Center(
              child: Column(
                  children: [
                    Text(
                      'Reference: ${warranty.reference}',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    ),
                    Text(
                      'Expire Date: ${warranty.endDate}',
                      style: TextStyle(fontWeight: FontWeight.bold),
                    )
                ]
              )
          ),
        ),
      ),
    );



    return Container(
      width: 200, // Adjust the width as needed
      margin: EdgeInsets.all(8.0),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(8.0),
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.grey.withOpacity(0.5),
            spreadRadius: 2,
            blurRadius: 5,
            offset: Offset(0, 3),
          ),
        ],
      ),
      child: Padding(
        padding: const EdgeInsets.all(8.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              'Reference: ${warranty.reference}',
              style: TextStyle(fontWeight: FontWeight.bold),
            ),
            SizedBox(height: 8.0),
            Text('End Date: ${warranty.endDate}'),
            // You can add more information here as needed
          ],
        ),
      ),
    );
  }
}