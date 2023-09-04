import 'package:flutter/material.dart';
import 'package:warranty_app/pages/document_detail.dart';
import 'package:warranty_app/sections/warranties_home_section.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  Widget build(BuildContext context) {
    return Column(children: [
       // WarrantyHomeSection(),
      DocumentListWidget()
    ],
    );
  }
}
