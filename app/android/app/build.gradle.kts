plugins {
    id("com.android.application")
    id("kotlin-android")
    // Flutter Gradle Plugin phải đứng sau Android và Kotlin.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.projifylab.app"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        applicationId = "com.projifylab.app"
        minSdk = 24
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    buildTypes {
        release {
            // Tạm ký bằng khoá debug để cài thử. Khi đưa lên Google Play cần tạo khoá riêng.
            signingConfig = signingConfigs.getByName("debug")
        }
    }
}

flutter {
    source = "../.."
}
