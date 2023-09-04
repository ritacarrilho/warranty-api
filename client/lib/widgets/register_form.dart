import 'package:flutter/material.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';
import 'dart:developer';


class RegisterForm extends StatefulWidget {
  final Function(String firstName, String lastName, String phone, String email, String password) onSubmit;

  RegisterForm({required this.onSubmit});

  @override
  State<RegisterForm> createState() => _RegisterFormState();
}

class _RegisterFormState extends State<RegisterForm> {

  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final TextEditingController firstNameController = TextEditingController();
  final TextEditingController lastNameController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: mediumGreen,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset(
              'assets/logo_dark.png',
              width: 150,
              height: 150,
            ),
            SizedBox(height: 16.0),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(7.0),
                border: Border.all(
                  color: darkGreen,
                  width: 2.0,
                ),
              ),
              child: TextFormField(
                controller: firstNameController,
                decoration: const InputDecoration(
                  labelText: 'First Name',
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.only(left: 16.0),
                ),
              ),
            ),
            SizedBox(height: 16.0),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(7.0),
                border: Border.all(
                  color: darkGreen,
                  width: 2.0,
                ),
              ),
              child: TextFormField(
                controller: lastNameController,
                decoration: const InputDecoration(
                  labelText: 'Last Name',
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.only(left: 16.0),
                ),
              ),
            ),
            SizedBox(height: 16.0),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(7.0),
                border: Border.all(
                  color: darkGreen,
                  width: 2.0,
                ),
              ),
              child: TextFormField(
                controller: phoneController,
                decoration: const InputDecoration(
                  labelText: 'Phone',
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.only(left: 16.0),
                ),
              ),
            ),
            SizedBox(height: 16.0),
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(7.0),
                border: Border.all(
                  color: darkGreen,
                  width: 2.0,
                ),
              ),
              child: TextFormField(
                controller: emailController,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.only(left: 16.0),
                ),
              ),
            ),
            SizedBox(height: 16.0), // Add spacing
            Container(
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(7.0),
                border: Border.all(
                  color: darkGreen,
                  width: 2.0,
                ),
              ),
              child: TextFormField(
                controller: passwordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Password',
                  border: InputBorder.none,
                  contentPadding: EdgeInsets.only(left: 16.0),
                ),
              ),
            ),
            SizedBox(height: 16.0),
            CustomButton(
              text: 'Login',
              textColor: lightWhite,
              backgroundColor: darkGreen,
              onPressed: () {
                final firstName = firstNameController.text;
                final lastName = lastNameController.text;
                final phone = phoneController.text;
                final email = emailController.text;
                final password = passwordController.text;
                log('data: $firstName');
                widget.onSubmit(firstName, lastName, phone, email, password);
              },
            ),
          ],
        ),
      ),
    );
  }
}

