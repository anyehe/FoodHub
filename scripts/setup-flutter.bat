@echo off
cd /d "%~dp0..\flutter_app"
where flutter >nul 2>nul || (echo Flutter not found. Install Flutter and Android Studio first.&exit /b 1)
if not exist android flutter create --platforms=android .
flutter pub get
flutter run --dart-define=FOODHUB_API=http://10.0.2.2:8000/api/v1
