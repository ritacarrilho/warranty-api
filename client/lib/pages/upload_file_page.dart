import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:warranty_app/services/helper.dart';
import 'package:file_picker/file_picker.dart';


class UploadFilePage extends StatefulWidget {
  const UploadFilePage({super.key});

  @override
  State<UploadFilePage> createState() => _UploadFilePageState();
}

class _UploadFilePageState extends State<UploadFilePage> {
  final HttpHelper httpHelper = HttpHelper();
  TextEditingController _controller = TextEditingController();

  @override
  Widget build(BuildContext context) {
    return  Scaffold(
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
              decoration: InputDecoration(labelText: 'Enter Document Name'),
            ),
            SizedBox(height: 16),
            ElevatedButton(
              onPressed: () async {
                try {
                  FilePickerResult? result = await FilePicker.platform.pickFiles(
                    type: FileType.custom,
                    allowedExtensions: ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
                  );

                  if (result != null && result.files.isNotEmpty) {
                    final filePath = result.files.first.path!;
                    final uploadResponse = await httpHelper.uploadDocument(filePath);

                    if (uploadResponse != null) {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Document uploaded successfully'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                      _controller.clear();
                    } else {
                      ScaffoldMessenger.of(context).showSnackBar(
                        SnackBar(
                          content: Text('Error uploading document'),
                          duration: Duration(seconds: 2),
                        ),
                      );
                    }
                  } else {
                    ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text('No file selected'),
                        duration: Duration(seconds: 2),
                      ),
                    );
                  }
                } catch (error) {
                  // Handle any errors that may occur
                  print('Error: $error');
                  // Show an error message to the user
                  ScaffoldMessenger.of(context).showSnackBar(
                    SnackBar(
                      content: Text('An error occurred: $error'),
                      duration: Duration(seconds: 2),
                    ),
                  );
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
