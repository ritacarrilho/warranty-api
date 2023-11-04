import 'package:flutter/material.dart';
import 'dart:convert';
import 'dart:typed_data';
import 'package:http/http.dart' as http;
import 'package:warranty_app/models/document.dart';
import 'package:warranty_app/services/helper.dart';

class DocumentListWidget extends StatefulWidget {
  @override
  _DocumentListWidgetState createState() => _DocumentListWidgetState();
}

class _DocumentListWidgetState extends State<DocumentListWidget> {
  // Define the fetchDocuments method
  final HttpHelper httpHelper = HttpHelper(); // Create an instance of HttpHelper

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<List<Document>>(
      future: httpHelper.fetchDocuments(), // Call fetchDocuments from HttpHelper
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return CircularProgressIndicator();
        } else if (snapshot.hasError) {
          return Text('Error: ${snapshot.error}');
        } else {
          final documents = snapshot.data;

          if (documents == null || documents.isEmpty) {
            return Text('No documents available.');
          }

          return ListView.builder(
            itemCount: documents.length,
            itemBuilder: (context, index) {
              final document = documents[index];
              return ListTile(
                title: Text(document.name),
                leading: Image.memory(
                  base64Decode(document.imageData),
                  width: 50,
                  height: 50,
                  fit: BoxFit.cover,
                ),
              );
            },
          );
        }
      },
    );
  }
}