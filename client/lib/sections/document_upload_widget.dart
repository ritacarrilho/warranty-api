import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:warranty_app/services/helper.dart';
import 'package:file_picker/file_picker.dart';
import 'dart:io';

class DocumentUploadWidget extends StatefulWidget {
  @override
  _DocumentUploadWidgetState createState() => _DocumentUploadWidgetState();
}

class _DocumentUploadWidgetState extends State<DocumentUploadWidget> {
  final HttpHelper httpHelper = HttpHelper();
  TextEditingController _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Upload Document'),
      ),
      body: Padding(
        padding: const EdgeInsets.all(16.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: <Widget>[
            TextField(
              controller: _controller,
              decoration: InputDecoration(labelText: 'Enter Document Path'),
            ),
            SizedBox(height: 16),
        ElevatedButton(
          onPressed: () async {
            try {
              FilePickerResult? result = await FilePicker.platform.pickFiles(
                type: FileType.custom,
                allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'], // Specify the allowed file types
              );

              if (result != null && result.files.isNotEmpty) {
                final filePath = result.files.first.path!;
                final uploadResponse = await httpHelper.uploadDocument(filePath);

                if (uploadResponse != null) {
                  // Handle the uploaded document data here (e.g., display it or store it)
                  print('Document uploaded: $uploadResponse');
                } else {
                  // Handle the error here
                  print('Error uploading document');
                }
              } else {
                // User canceled the file selection
                print('No file selected');
              }
            } catch (error) {
              // Handle any errors that may occur
              print('Error: $error');
            }
          },
          child: Text('Upload Document'),
        ),
          ],
        ),
      ),
    );
  }
}