# Add project specific ProGuard rules here.
# You can control the set of applied configuration files using the
# proguardFiles setting in build.gradle.
#
# For more details, see
#   http://developer.android.com/guide/developing/tools/proguard.html

# If your project uses WebView with JS, uncomment the following
# and specify the fully qualified class name to the JavaScript interface
# class:
#-keepclassmembers class fqcn.of.javascript.interface.for.webview {
#   public *;
#}

# Uncomment this to preserve the line number information for
# debugging stack traces.
#-keepattributes SourceFile,LineNumberTable

# If you keep the line number information, uncomment this to
# hide the original source file name.
#-renamesourcefileattribute SourceFile

# ==========================================
# 基础规则
# ==========================================
-optimizationpasses 5
-dontusemixedcaseclassnames
-dontskipnonpubliclibraryclasses
-dontskipnonpubliclibraryclassmembers
-dontpreverify
-verbose
-optimizations !code/simplification/arithmetic,!field/*,!class/merging/*

# 保留注解
-keepattributes *Annotation*
-keepattributes Signature
-keepattributes LineNumberTable
-keepattributes SourceFile

# 保留R文件
-keep class **.R$* {*;}

# 保留四大组件
-keep public class * extends android.app.Activity
-keep public class * extends android.app.Application
-keep public class * extends android.app.Service
-keep public class * extends android.content.BroadcastReceiver
-keep public class * extends android.content.ContentProvider
-keep public class * extends android.app.backup.BackupAgentHelper
-keep public class * extends android.preference.Preference
-keep public class com.android.vending.licensing.ILicensingService

# 保留View的set/get方法
-keepclassmembers public class * extends android.view.View {
    void set*(***);
    *** get*();
}

# 保留Activity中的onClick方法
-keepclassmembers class * extends android.app.Activity {
    public void *(android.view.View);
}

# 保留枚举
-keepclassmembers enum * {
    public static **[] values();
    public static ** valueOf(java.lang.String);
}

# 保留Parcelable
-keep class * implements android.os.Parcelable {
    public static final android.os.Parcelable$Creator *;
}

# 保留Serializable
-keepclassmembers class * implements java.io.Serializable {
    static final long serialVersionUID;
    private static final java.io.ObjectStreamField[] serialPersistentFields;
    !static !transient <fields>;
    !private <fields>;
    !private <methods>;
    private void writeObject(java.io.ObjectOutputStream);
    private void readObject(java.io.ObjectInputStream);
    java.lang.Object writeReplace();
    java.lang.Object readResolve();
}

# 保留本地方法
-keepclasseswithmembernames class * {
    native <methods>;
}

# 保留构造方法
-keepclasseswithmembers class * {
    public <init>(android.content.Context, android.util.AttributeSet);
    public <init>(android.content.Context, android.util.AttributeSet, int);
}

# ==========================================
# 项目中自定义的规则
# ==========================================

# 保留Model类（用于Gson解析）
-keep class com.zhikao.ai.model.** { *; }

# 保留Api相关
-keep class com.zhikao.ai.net.** { *; }

# 保留Application
-keep class com.zhikao.ai.app.MyApplication { *; }

# 保留Activity
-keep class com.zhikao.ai.ui.** { *; }

# 保留Adapter
-keep class com.zhikao.ai.adapter.** { *; }

# 保留工具类
-keep class com.zhikao.ai.utils.** { *; }

# ==========================================
# OkHttp
# ==========================================
-dontwarn okhttp3.**
-keep class okhttp3.** { *; }
-keep interface okhttp3.** { *; }
-dontwarn okio.**
-keep class okio.** { *; }

# ==========================================
# Retrofit
# ==========================================
-dontwarn retrofit2.**
-keep class retrofit2.** { *; }
-keepattributes Exceptions

# ==========================================
# Gson
# ==========================================
-keep class com.google.gson.** { *; }
-keep class com.google.gson.stream.** { *; }
-keep class com.google.gson.examples.android.model.** { *; }
-keep class * implements com.google.gson.TypeAdapter
-keep class * implements com.google.gson.TypeAdapterFactory
-keep class * implements com.google.gson.JsonSerializer
-keep class * implements com.google.gson.JsonDeserializer

# Gson序列化的类
-keepattributes Signature
-keepattributes *Annotation*
-keep class sun.misc.Unsafe { *; }

# ==========================================
# Glide
# ==========================================
-keep public class * implements com.bumptech.glide.module.GlideModule
-keep class * extends com.bumptech.glide.module.AppGlideModule {
 <init>(...);
}
-keep public enum com.bumptech.glide.load.ImageHeaderParser$** {
  **[] $VALUES;
  public *;
}
-keep class com.bumptech.glide.load.data.ParcelFileDescriptorRewinder$InternalRewinder {
  *** rewind();
}

# ==========================================
# MPAndroidChart
# ==========================================
-keep class com.github.mikephil.charting.** { *; }
-dontwarn com.github.mikephil.charting.**

# ==========================================
# Material Design
# ==========================================
-dontwarn com.google.android.material.**
-keep class com.google.android.material.** { *; }
-keep interface com.google.android.material.** { *; }

# ==========================================
# AndroidX
# ==========================================
-keep class androidx.** { *; }
-keep interface androidx.** { *; }
-dontwarn androidx.**

# ==========================================
# CircleImageView
# ==========================================
-keep class de.hdodenhof.circleimageview.** { *; }

# ==========================================
# PermissionX
# ==========================================
-dontwarn com.permissionx.guolindev.**
-keep class com.permissionx.guolindev.** { *; }

# ==========================================
# Banner
# ==========================================
-dontwarn com.youth.banner.**
-keep class com.youth.banner.** { *; }

# ==========================================
# WebView 相关
# ==========================================
-keep class android.webkit.** {*;}

# ==========================================
# 测试相关
# ==========================================
-dontwarn junit.**
-dontwarn org.junit.**
-dontwarn androidx.test.**
-dontwarn org.mockito.**
