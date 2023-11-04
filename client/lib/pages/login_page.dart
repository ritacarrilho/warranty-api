import 'package:flutter/material.dart';
import 'package:warranty_app/services/helper.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/login_form.dart';
import 'dart:developer';


class LoginPage extends StatefulWidget {
  @override
  _LoginPageState createState() => _LoginPageState();
}

class _LoginPageState extends State<LoginPage> {
  final HttpHelper httpHelper = HttpHelper(); // Create an instance of HttpHelper

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: mediumGreen,
      appBar: AppBar(
        backgroundColor: mediumGreen,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(16.0),
          child: LoginForm(
            onSubmit: (email, password) async {
              log("on submit event");
              try {
                final token = await httpHelper.authenticate(email, password);
                log(token.toString());
                if (token != null) {
                  // Authentication successful, navigate to the home page
                  Navigator.pushNamed(context, '/home');
                } else {
                  // Handle authentication failure
                  // Show an error message or provide feedback to the user
                }
              } catch (e) {
                log(e.toString());
                // Handle other errors, such as network issues or server errors
              }
            },
          ),
        ),
      ),
    );
  }
}