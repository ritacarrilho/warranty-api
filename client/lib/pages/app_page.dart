import 'package:flutter/material.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';
import 'package:connectivity/connectivity.dart'; // Import the connectivity package

class AppPage extends StatefulWidget {
  const AppPage({super.key});

  @override
  State<AppPage> createState() => _AppPageState();
}

class _AppPageState extends State<AppPage> {
  bool hasInternetConnection = false; // Initially assume no internet connection

  @override
  void initState() {
    super.initState();
    // Comment out the checkInternetConnection() in initState
  }

  Future<bool> checkInternetConnection() async {
    var connectivityResult = await Connectivity().checkConnectivity();
    if (connectivityResult == ConnectivityResult.mobile ||
        connectivityResult == ConnectivityResult.wifi) {
      return true;
    } else {
      return false;
    }
  }

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
            FutureBuilder<bool>(
              future: checkInternetConnection(),
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.done) {
                  hasInternetConnection = snapshot.data ?? false;
                  if (hasInternetConnection) {
                    return Column(
                      children: [
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
                    );
                  } else {
                    return Text(
                      'No internet connection', // Display a message if there's no internet connection
                      style: TextStyle(color: Colors.white),
                    );
                  }
                } else {
                  return CircularProgressIndicator(); // Show a loading indicator while checking connectivity
                }
              },
            ),
          ],
        ),
      ),
    );
  }
}