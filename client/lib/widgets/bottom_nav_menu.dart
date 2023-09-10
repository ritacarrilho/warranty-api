import 'package:flutter/material.dart';
import 'package:warranty_app/pages/document_page.dart';
import 'package:warranty_app/pages/equipments_page.dart';
import 'package:warranty_app/pages/historic_page.dart';
import 'package:warranty_app/pages/home_page.dart';
import 'package:warranty_app/pages/settings_page.dart';


class BottomNavigation extends StatelessWidget {
  final int selectedIndex;
  final Function(int) onItemTapped;

  BottomNavigation({
    required this.selectedIndex,
    required this.onItemTapped,
  });

  void _onItemTapped(int index, BuildContext context) {
    switch (index) {
      case 0:
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => HomePage()), // Replace with your EquipmentsPage
        );
        break;
      case 1:
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => EquipmentsPage()), // Replace with your EquipmentsPage
        );
        break;
      case 2:
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => HistoricPage()), // Replace with your HistoricPage
        );
        break;
      case 3:
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => DocumentPage()), // Replace with your DocumentsPage
        );
        break;
      case 4:
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => SettingsPage()), // Replace with your SettingsPage
        );
        break;
      default:
        break;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.green, // Change to your preferred color
      child: BottomNavigationBar(
        backgroundColor: Colors.blueAccent,
        selectedItemColor: Colors.white, // Change to your preferred color
        showSelectedLabels: false,
        items: const <BottomNavigationBarItem>[
          BottomNavigationBarItem(
            icon: Icon(
              Icons.home,
              size: 40,
            ),
            label: "Home",
          ),
          BottomNavigationBarItem(
            icon: Icon(
              Icons.crop_square,
              size: 40,
            ),
            label: "Equipments",
          ),
          BottomNavigationBarItem(
            icon: Icon(
              Icons.lock_clock,
              size: 40,
            ),
            label: "Historic",
          ),
          BottomNavigationBarItem(
            icon: Icon(
              Icons.file_copy,
              size: 40,
            ),
            label: "Documents",
          ),
          BottomNavigationBarItem(
            icon: Icon(
              Icons.settings,
              size: 40,
            ),
            label: "Settings",
          ),
        ],
        currentIndex: selectedIndex,
        onTap: (index) => _onItemTapped(index, context),
      ),
    );
  }
}