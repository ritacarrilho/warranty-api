import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:warranty_app/sections/warranties_home_section.dart';
import 'package:warranty_app/services/helper.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/warranty_home_card.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Your App Title'), // Replace with your app title
        backgroundColor: mediumGreen,
      ),
      body: SingleChildScrollView(
        child: Column(
          children: [
            const SizedBox(height: 40),
            Text(
              "Next Expiring Warranties",
              style: TextStyle(
                color: darkGreen,
                fontSize: titles,
                  fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 10),
            WarrantiesHomeSection(),
          ],
        ),
      ),
    );
  }
}