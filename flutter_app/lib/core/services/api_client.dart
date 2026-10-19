import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';
import 'session.dart';

class ApiClient {
  static Future<dynamic> get(String path) async {
    final r = await http.get(Uri.parse(AppConfig.baseUrl + path), headers: _headers()).timeout(const Duration(seconds: 20));
    return _decode(r);
  }

  static Future<dynamic> post(String path, Map<String, dynamic> body) async {
    final r = await http.post(Uri.parse(AppConfig.baseUrl + path), headers: _headers(), body: jsonEncode(body)).timeout(const Duration(seconds: 20));
    return _decode(r);
  }

  static Future<dynamic> put(String path, Map<String, dynamic> body) async {
    final r = await http.put(Uri.parse(AppConfig.baseUrl + path), headers: _headers(), body: jsonEncode(body)).timeout(const Duration(seconds: 20));
    return _decode(r);
  }

  static Future<dynamic> delete(String path) async {
    final r = await http.delete(Uri.parse(AppConfig.baseUrl + path), headers: _headers()).timeout(const Duration(seconds: 20));
    return _decode(r);
  }

  static Map<String, String> _headers() => {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
    if (Session.token != null) 'Authorization': 'Bearer ${Session.token}',
  };

  static dynamic _decode(http.Response r) {
    final d = r.body.isEmpty ? {} : jsonDecode(r.body);
    if (r.statusCode >= 400) {
      if (d is Map && d['errors'] is Map) {
        final errors = (d['errors'] as Map).values.expand((v) => v is List ? v : [v]).join(' ');
        throw Exception(errors.isEmpty ? 'Request failed' : errors);
      }
      throw Exception(d is Map && d['message'] != null ? d['message'] : 'Request failed');
    }
    return d;
  }
}
