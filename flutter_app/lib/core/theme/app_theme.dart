import 'package:flutter/material.dart';

class AppTheme {
  static const teal = Color(0xFF003135);
  static const deep = Color(0xFF024950);
  static const accent = Color(0xFF0FA4AF);
  static const warm = Color(0xFF964734);
  static const pale = Color(0xFFAFDDE5);

  static ThemeData get theme {
    final scheme = ColorScheme.fromSeed(seedColor: accent, brightness: Brightness.light);
    return ThemeData(
      useMaterial3: true,
      colorScheme: scheme.copyWith(primary: accent, secondary: warm, surface: Colors.white),
      scaffoldBackgroundColor: const Color(0xFFF5FBFB),
      fontFamily: 'Arial',
      appBarTheme: const AppBarTheme(backgroundColor: teal, foregroundColor: Colors.white, elevation: 0, centerTitle: false),
      cardTheme: CardThemeData(color: Colors.white, elevation: 0, margin: EdgeInsets.zero, shape: RoundedRectangleBorder(borderRadius: BorderRadius.all(Radius.circular(20)))),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFDCE9EA))),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: Color(0xFFDCE9EA))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(14), borderSide: const BorderSide(color: accent, width: 1.5)),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(style: ElevatedButton.styleFrom(backgroundColor: accent, foregroundColor: Colors.white, elevation: 0, padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)))),
    );
  }
}
