import 'package:flutter/material.dart';
import 'package:warranty_app/pages/app_page.dart';
import 'package:warranty_app/pages/home_page.dart';
import 'package:warranty_app/pages/login_page.dart';
import 'package:warranty_app/pages/register_page.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';

void main() {
  runApp(MyApp());
}

class MyApp extends StatelessWidget {
  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      initialRoute: '/',
      routes: {
        '/': (context) => AppPage(),
        '/register': (context) => RegisterPage(),
        '/login': (context) => LoginPage(),
        '/home': (context) => HomePage(),
      },
    );
  }
}
