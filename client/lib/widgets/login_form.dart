import 'package:flutter/material.dart';
import 'package:warranty_app/utils/constants.dart';
import 'package:warranty_app/widgets/button.dart';
import 'dart:developer';


class LoginForm extends StatefulWidget {
  final Function(String email, String password) onSubmit;

  LoginForm({required this.onSubmit});

  @override
  _LoginFormState createState() => _LoginFormState();
}

class _LoginFormState extends State<LoginForm> {
  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final GlobalKey<FormState> _formKey = GlobalKey<FormState>();
  String? _emailError;
  String? _passwordError;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: mediumGreen,
      body: Center(
        child: Form(
          key: _formKey,
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Image.asset(
                'assets/logo_dark.png',
                width: 150,
                height: 150,
              ),
              const SizedBox(height: 16.0),
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
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      setState(() {
                        _emailError = 'Please enter your email';
                      });
                      return null;
                    }
                    if (!isValidEmail(value)) {
                      setState(() {
                        _emailError = 'Invalid email format';
                      });
                      return null;
                    }
                    setState(() {
                      _emailError = null;
                    });
                    return null;
                  },
                ),
              ),
              const SizedBox(height: 8.0),
              if (_emailError != null)
                Text(
                  _emailError!,
                  style: TextStyle(color: Colors.red),
                ),
              const SizedBox(height: 16.0),
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
                  validator: (value) {
                    if (value == null || value.isEmpty) {
                      setState(() {
                        _passwordError = 'Please enter your password';
                      });
                      return null;
                    }
                    setState(() {
                      _passwordError = null;
                    });
                    return null;
                  },
                ),
              ),
              const SizedBox(height: 8.0),
              if (_passwordError != null)
                Text(
                  _passwordError!,
                  style: TextStyle(color: Colors.red),
                ),
              const SizedBox(height: 16.0),
              CustomButton(
                text: 'Login',
                textColor: lightWhite,
                backgroundColor: darkGreen,
                onPressed: () {
                  if (_formKey.currentState!.validate()) {
                    final email = emailController.text;
                    final password = passwordController.text;
                    widget.onSubmit(email, password);
                  }
                },
              ),
            ],
          ),
        ),
      ),
    );
  }

  // regex to validate email format
  bool isValidEmail(String email) {
    final emailRegExp = RegExp(r'^[\w-]+(\.[\w-]+)*@[\w-]+(\.[\w-]+)+$');
    return emailRegExp.hasMatch(email);
  }
}