package com.zhikao.ai.net;

import android.os.Handler;
import android.os.Looper;
import android.text.TextUtils;

import com.google.gson.Gson;
import com.google.gson.internal.$Gson$Types;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.app.MyApplication;

import java.io.IOException;
import java.lang.reflect.ParameterizedType;
import java.lang.reflect.Type;
import java.util.HashMap;
import java.util.Map;
import java.util.concurrent.TimeUnit;

import okhttp3.Call;
import okhttp3.Callback;
import okhttp3.FormBody;
import okhttp3.MediaType;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.RequestBody;
import okhttp3.Response;
import okhttp3.logging.HttpLoggingInterceptor;

/**
 * 网络请求客户端封装
 * 使用OkHttp实现，支持GET/POST请求，自动添加Token，统一JSON解析
 */
public class ApiClient {

    private static final String TAG = "ApiClient";
    private static final MediaType JSON = MediaType.parse("application/json; charset=utf-8");

    private static ApiClient instance;
    private OkHttpClient okHttpClient;
    private Gson gson;
    private Handler handler;

    private ApiClient() {
        gson = new Gson();
        handler = new Handler(Looper.getMainLooper());

        OkHttpClient.Builder builder = new OkHttpClient.Builder()
                .connectTimeout(ApiConfig.TIMEOUT, TimeUnit.SECONDS)
                .readTimeout(ApiConfig.TIMEOUT, TimeUnit.SECONDS)
                .writeTimeout(ApiConfig.TIMEOUT, TimeUnit.SECONDS);

        // 调试模式下添加日志拦截器
        if (ApiConfig.DEBUG) {
            HttpLoggingInterceptor loggingInterceptor = new HttpLoggingInterceptor();
            loggingInterceptor.setLevel(HttpLoggingInterceptor.Level.BODY);
            builder.addInterceptor(loggingInterceptor);
        }

        // 添加Token拦截器
        builder.addInterceptor(chain -> {
            Request original = chain.request();
            Request.Builder requestBuilder = original.newBuilder()
                    .header("Content-Type", "application/json")
                    .header("Accept", "application/json");

            // 自动添加Token
            String token = MyApplication.getToken();
            if (!TextUtils.isEmpty(token)) {
                requestBuilder.header("Authorization", "Bearer " + token);
            }

            Request request = requestBuilder.build();
            return chain.proceed(request);
        });

        okHttpClient = builder.build();
    }

    /**
     * 获取单例实例
     */
    public static ApiClient getInstance() {
        if (instance == null) {
            synchronized (ApiClient.class) {
                if (instance == null) {
                    instance = new ApiClient();
                }
            }
        }
        return instance;
    }

    /**
     * GET请求
     * @param url 请求地址
     * @param callback 回调
     * @param <T> 泛型
     */
    public <T> void get(String url, ApiCallback<T> callback) {
        get(url, new HashMap<>(), callback);
    }

    /**
     * GET请求（带参数）
     * @param url 请求地址
     * @param params 请求参数
     * @param callback 回调
     * @param <T> 泛型
     */
    public <T> void get(String url, Map<String, String> params, ApiCallback<T> callback) {
        // 拼接URL参数
        StringBuilder urlBuilder = new StringBuilder(getFullUrl(url));
        if (params != null && !params.isEmpty()) {
            urlBuilder.append("?");
            int i = 0;
            for (Map.Entry<String, String> entry : params.entrySet()) {
                if (i > 0) {
                    urlBuilder.append("&");
                }
                urlBuilder.append(entry.getKey()).append("=").append(entry.getValue());
                i++;
            }
        }

        Request request = new Request.Builder()
                .url(urlBuilder.toString())
                .get()
                .build();

        execute(request, callback);
    }

    /**
     * POST请求（表单参数）
     * @param url 请求地址
     * @param params 请求参数
     * @param callback 回调
     * @param <T> 泛型
     */
    public <T> void post(String url, Map<String, String> params, ApiCallback<T> callback) {
        FormBody.Builder formBuilder = new FormBody.Builder();
        if (params != null && !params.isEmpty()) {
            for (Map.Entry<String, String> entry : params.entrySet()) {
                formBuilder.add(entry.getKey(), entry.getValue());
            }
        }

        RequestBody body = formBuilder.build();
        Request request = new Request.Builder()
                .url(getFullUrl(url))
                .post(body)
                .build();

        execute(request, callback);
    }

    /**
     * POST请求（JSON参数）
     * @param url 请求地址
     * @param jsonBody JSON请求体
     * @param callback 回调
     * @param <T> 泛型
     */
    public <T> void postJson(String url, String jsonBody, ApiCallback<T> callback) {
        RequestBody body = RequestBody.create(jsonBody, JSON);
        Request request = new Request.Builder()
                .url(getFullUrl(url))
                .post(body)
                .build();

        execute(request, callback);
    }

    /**
     * POST请求（对象参数）
     * @param url 请求地址
     * @param object 请求对象
     * @param callback 回调
     * @param <T> 泛型
     */
    public <T> void postObject(String url, Object object, ApiCallback<T> callback) {
        String json = gson.toJson(object);
        postJson(url, json, callback);
    }

    /**
     * 执行请求
     * @param request 请求对象
     * @param callback 回调
     * @param <T> 泛型
     */
    private <T> void execute(Request request, ApiCallback<T> callback) {
        okHttpClient.newCall(request).enqueue(new Callback() {
            @Override
            public void onFailure(Call call, IOException e) {
                postFailure(callback, -1, e.getMessage());
            }

            @Override
            public void onResponse(Call call, Response response) throws IOException {
                if (response.isSuccessful()) {
                    String body = response.body() != null ? response.body().string() : "";
                    try {
                        Type type = getGenericType(callback);
                        T data = gson.fromJson(body, type);
                        postSuccess(callback, data);
                    } catch (Exception e) {
                        postFailure(callback, -2, "数据解析失败：" + e.getMessage());
                    }
                } else {
                    postFailure(callback, response.code(), response.message());
                }
            }
        });
    }

    /**
     * 在主线程回调成功
     */
    private <T> void postSuccess(ApiCallback<T> callback, T data) {
        if (callback == null) return;
        handler.post(() -> {
            callback.onSuccess(data);
            callback.onComplete();
        });
    }

    /**
     * 在主线程回调失败
     */
    private <T> void postFailure(ApiCallback<T> callback, int code, String message) {
        if (callback == null) return;
        handler.post(() -> {
            callback.onFailure(code, message);
            callback.onComplete();
        });
    }

    /**
     * 获取完整的URL地址
     */
    private String getFullUrl(String url) {
        if (url.startsWith("http://") || url.startsWith("https://")) {
            return url;
        }
        return ApiConfig.BASE_URL + url;
    }

    /**
     * 获取泛型类型
     */
    private Type getGenericType(ApiCallback<?> callback) {
        Type[] types = callback.getClass().getGenericInterfaces();
        if (types.length == 0) {
            return Object.class;
        }
        ParameterizedType parameterizedType = (ParameterizedType) types[0];
        Type[] actualTypeArguments = parameterizedType.getActualTypeArguments();
        if (actualTypeArguments.length == 0) {
            return Object.class;
        }
        return actualTypeArguments[0];
    }

    /**
     * 获取OkHttpClient实例（用于自定义请求）
     */
    public OkHttpClient getOkHttpClient() {
        return okHttpClient;
    }

    /**
     * 获取Gson实例
     */
    public Gson getGson() {
        return gson;
    }
}
