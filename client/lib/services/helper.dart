import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:warranty_app/models/category.dart';
import 'package:warranty_app/models/document.dart';
import 'dart:developer';
import 'package:warranty_app/models/warranty.dart';
import 'package:shared_preferences/shared_preferences.dart';


class HttpHelper {
  final String baseUrl = "http://127.0.0.1:8000";
  final String authMethod = "/auth";
  String? _token;
  final String urlGetCategories = "/api/category";
  final String urlGetWarranties = "/api/warranties";
  final String urlGetDocuments = "/api/documents";
  final String urlGetDocument = "/api/documents/";


  Future<String?> authenticate(String email, String password) async {
    // Check if token is already set
    if (_token != null) {
      return _token;
    }

    final response = await http.post(
      Uri.parse(baseUrl + authMethod),
      headers: <String, String>{
        'Content-Type': 'application/json; charset=UTF-8',
      },
      body: jsonEncode(<String, String>{
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode == 200) {
      final token = jsonDecode(response.body)['token'];
      await saveToken(token); // Store the token
      _token = token; // Assign to _token
      return token;
    } else {
      throw Exception('Failed to authenticate');
    }
  }

  Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    prefs.setString('authToken', token);
  }

  Future<String?> getToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('authToken');
  }

  Future<List<Warranty>?> getWarranties() async {
    // Retrieve the token
    _token = await getToken();

    if (_token == null) {
      throw Exception('Token is missing. Authenticate first.');
    }

    log("the warranties token : " + _token.toString());

    final String warranties = baseUrl + urlGetWarranties;
    http.Response response = await http.get(
      Uri.parse('$warranties?_limit=5&_sort=endDate:asc'),
      headers: <String, String>{
        'Authorization': 'Bearer $_token', // Use the stored token
      },
    );

    log("warranties: "+response.body);

    if (response.statusCode == 200) {
      final responseBody = jsonDecode(response.body).cast<Map<String, dynamic>>();
      List<Warranty> warrantiesList =
      responseBody.map<Warranty>((el) => Warranty.fromJson(el)).toList();

      return warrantiesList;
    } else {
      throw Exception('Failed to get warranties');
    }
  }


  Future<List<Document>> fetchDocuments() async {
    try {
      final _token = await getToken();

      if (_token == null) {
        throw Exception('Token is missing. Authenticate first.');
      }

      final response = await http.get(
        Uri.parse(baseUrl+urlGetDocuments),
        headers: {
          'Authorization': 'Bearer $_token',
        },
      );
      log("docs: "+response.body);

      if (response.statusCode == 200) {
        final List<dynamic> data = json.decode(response.body);
        return data.map((document) {
          return Document(
            id: document['id'],
            name: document['name'],
            path: document['path'],
            imageData: document['imageData'],
          );
        }).toList();
      } else {
        throw Exception('Failed to load documents');
      }
    } catch (error) {
      // Handle network or other errors
      print('Error fetching documents: $error');
      throw Exception('Failed to load documents');
    }
  }


  // Get all categories
  Future<List<Category>?> getCategories() async {
    // Retrieve the token
    _token = await getToken();

    if (_token == null) {
      throw Exception('Token is missing. Authenticate first.');
    }

    final String categories = baseUrl + urlGetCategories;
    final response = await http.get(
      Uri.parse(categories),
      headers: <String, String>{
        'Authorization': 'Bearer $_token',
      },
    );

    if (response.statusCode == 200) {
      final responseBody = jsonDecode(response.body).cast<Map<String, dynamic>>();
      List<Category> categoriesList =
      responseBody.map<Category>((el) => Category.fromJson(el)).toList();
      return categoriesList;
    } else {
      return null;
    }
  }


  Future<String?> fetchImageData(String url) async {
    try {
      _token = await getToken();

      if (_token == null) {
        throw Exception('Token is missing. Authenticate first.');
      }

      final response = await http.get(Uri.parse("http://127.0.0.1:8000/api/images"));

      if (response.statusCode == 200) {
        // Assuming the image data is returned as a base64-encoded string
        return response.body;
      } else {
        throw Exception('Failed to fetch image data');
      }
    } catch (error) {
      print('Error fetching image data: $error');
      return null;
    }
  }


  Future<Map<String, dynamic>?> uploadDocument(String filePath) async {
    try {
      final _token = await getToken();

      if (_token == null) {
        throw Exception('Token is missing. Authenticate first.');
      }

      var headers = {
        'Authorization': 'Bearer $_token',
      };

      var request = http.MultipartRequest('POST', Uri.parse('$baseUrl/api/document'))
        ..headers.addAll(headers)
        ..files.add(await http.MultipartFile.fromPath('path', filePath));

      var response = await request.send();

      if (response.statusCode == 201) {
        var responseData = await response.stream.toBytes();
        return json.decode(utf8.decode(responseData));
      } else {
        throw Exception('Failed to upload document');
      }
    } catch (error) {
      print('Error uploading document: $error');
      return null;
    }
  }

}