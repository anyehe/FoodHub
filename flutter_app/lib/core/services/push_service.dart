import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'api_client.dart';

class PushService {
  static bool initialized = false;

  static Future<void> initialize() async {
    try {
      await Firebase.initializeApp();
      initialized = true;
      await FirebaseMessaging.instance.requestPermission(alert: true, badge: true, sound: true);
      final token = await FirebaseMessaging.instance.getToken();
      if (token != null) await register(token);
      FirebaseMessaging.instance.onTokenRefresh.listen(register);
    } catch (_) {
      // Firebase configuration is optional during local development.
    }
  }

  static Future<void> register(String token) async {
    try {
      await ApiClient.post('/devices', {'token': token, 'platform': 'flutter'});
    } catch (_) {
      // Notification registration must never prevent the app from starting.
    }
  }
}
