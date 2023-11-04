import 'package:flutter/material.dart';
import 'package:warranty_app/models/equipment.dart';
import 'package:warranty_app/services/helper.dart';

class EquipmentDetailPage extends StatelessWidget {
  final Equipment equipment;

  EquipmentDetailPage(this.equipment);

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
  equipment.name,
  style: TextStyle(fontWeight: FontWeight.bold),
  ),
  Text(
  equipment.serialCode,
  style: TextStyle(fontWeight: FontWeight.bold),
  ),
  // Add more details or content here
  ],
  ),
  ),
  );
  }
  }