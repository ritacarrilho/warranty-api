import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:warranty_app/services/helper.dart';

class ImageDisplayWidget extends StatelessWidget {
  final String imageUrl; // Pass the URL of the image here

  ImageDisplayWidget({required this.imageUrl});

  @override
  Widget build(BuildContext context) {
    final HttpHelper httpHelper = HttpHelper();

    return FutureBuilder<String?>(
      future: httpHelper.fetchImageData(imageUrl), // Use the fetchImageData function here
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return CircularProgressIndicator();
        } else if (snapshot.hasError) {
          return Text('Error: ${snapshot.error}');
        } else if (snapshot.data == null) {
          return Text('Image data not available.');
        } else {
          // Decode the base64-encoded image data and display it
          final imageData = snapshot.data!;
          final imageBytes = base64.decode(imageData);

          return Image.memory(
            imageBytes,
            width: 200, // Adjust the width as needed
            height: 200, // Adjust the height as needed
            fit: BoxFit.cover,
          );
        }
      },
    );
  }
}