import 'package:flutter/material.dart';
import 'package:warranty_app/pages/add_equipment_page.dart';
import 'package:warranty_app/pages/app_page.dart';
import 'package:warranty_app/pages/document_page.dart';
import 'package:warranty_app/pages/equipments_page.dart';
import 'package:warranty_app/pages/historic_page.dart';
import 'package:warranty_app/pages/home_page.dart';
import 'package:warranty_app/pages/login_page.dart';
import 'package:warranty_app/pages/parameters_page.dart';
import 'package:warranty_app/pages/register_page.dart';
import 'package:warranty_app/pages/settings_page.dart';
import 'package:warranty_app/pages/warranties_page.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/bottom_nav_menu.dart';
import 'package:warranty_app/widgets/button.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatefulWidget {
  @override
  _MyAppState createState() => _MyAppState();
}

class _MyAppState extends State<MyApp> {
  int _selectedIndex = 0;

  void _onItemTapped(int index) {
    setState(() {
      _selectedIndex = index;
    });

    // Add your navigation logic here based on the index.
    // You can use a Navigator or some other method to navigate to the selected page.
    switch (index) {
      case 0:
        Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => HomePage()));
        break;
      case 1:
        Navigator.pushReplacement(context, MaterialPageRoute(builder: (context) => EquipmentsPage()));
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
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      initialRoute: '/app',
      routes: {
        '/app': (context) => AppPage(),
        '/register': (context) => RegisterPage(),
        '/login': (context) => LoginPage(),
        '/home': (context) => HomePage(),
        '/equipments': (context) => EquipmentsPage(),
        '/warranties': (context) => WarrantiesPage(),
        '/settings': (context) => SettingsPage(),
        '/historic': (context) => HistoricPage(),
        '/equipment': (context) => AddEquipmentPage(),
      },
      home: Scaffold(
        body: Center(
          // Your page content goes here
          child: Text("Page content here"),
        ),
        bottomNavigationBar: BottomNavigation(
          selectedIndex: _selectedIndex,
          onItemTapped: _onItemTapped,
        ),
      ),
    );
  }
}