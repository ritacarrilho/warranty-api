import 'package:flutter/material.dart';
import 'package:warranty_app/sections/warranties_home_section.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/bottom_nav_menu.dart';

class HomePage extends StatefulWidget {
  const HomePage({super.key});

  @override
  State<HomePage> createState() => _HomePageState();
}

class _HomePageState extends State<HomePage> {
  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    setState(() {
    _selectedIndex = index;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Welcome Friend'),
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
            const SizedBox(height: 50),
            WarrantiesHomeSection(),
            const SizedBox(height: 10),
            // const BottomNavigationMenu()
          ],
        ),
      ),
      bottomNavigationBar: BottomNavigation(
        selectedIndex: _selectedIndex,
        onItemTapped: _onItemTapped,
      ),
    );
  }
}





