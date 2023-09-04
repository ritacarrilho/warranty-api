import 'package:flutter/material.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';

class AppPage extends StatefulWidget {
  const AppPage({super.key});

  @override
  State<AppPage> createState() => _AppPageState();
}

class _AppPageState extends State<AppPage> {
  @override
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: mediumGreen,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset(
              'assets/logo.png',
              width: 300,
              height: 300,
            ),
            CustomButton(
              text: 'Register',
              textColor: darkGreen,
              onPressed: () {
                Navigator.pushNamed(context, '/register');
              },
              backgroundColor: darkWhite,
            ),
            SizedBox(height: 10),
            CustomButton(
              text: 'Login',
              textColor: darkGreen,
              onPressed: () {
                Navigator.pushNamed(context, '/login');
              },
              backgroundColor: darkWhite,
            ),
          ],
        ),
      ),
    );
  }
}
