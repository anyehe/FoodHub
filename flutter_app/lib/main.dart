import 'package:flutter/material.dart';
import 'core/services/session.dart';
import 'core/theme/app_theme.dart';
import 'screens/home_screen.dart';
import 'screens/login_screen.dart';
import 'screens/rider_screen.dart';
import 'core/services/push_service.dart';
Future<void> main() async { WidgetsFlutterBinding.ensureInitialized(); await Session.load(); if (Session.isLoggedIn && Session.role != 'customer' && Session.role != 'rider') { await Session.clear(); } if (Session.isLoggedIn) { await PushService.initialize(); } runApp(const FoodHubApp()); }
class FoodHubApp extends StatelessWidget { const FoodHubApp({super.key}); @override Widget build(BuildContext context){ Widget home=const LoginScreen(); if(Session.isLoggedIn){home=Session.role=='rider'?const RiderScreen():const HomeScreen();} return MaterialApp(title:'FoodHub',debugShowCheckedModeBanner:false,theme:AppTheme.theme,home:home); } }
