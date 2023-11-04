import 'package:flutter/material.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/register_form.dart';

class RegisterPage extends StatefulWidget {
  const RegisterPage({super.key});

  @override
  State<RegisterPage> createState() => _RegisterPageState();
}

class _RegisterPageState extends State<RegisterPage> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: mediumGreen,
      appBar: AppBar(
        backgroundColor: mediumGreen,
      ),
      resizeToAvoidBottomInset: false,
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Center(
          child: Expanded( // Wrap RegisterForm with Expanded
            child: RegisterForm(
              onSubmit: (firstName, lastName, phone, email, password) {
                // Handle form submission here
              },
            ),
          ),
        ),
      ),
    );
  }
}





