package com.zhikao.ai.ui.login;

import android.content.Intent;
import android.os.Bundle;
import android.text.TextUtils;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputLayout;
import com.zhikao.ai.R;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.app.MyApplication;
import com.zhikao.ai.model.User;
import com.zhikao.ai.net.ApiCallback;
import com.zhikao.ai.net.ApiClient;
import com.zhikao.ai.ui.main.MainActivity;
import com.zhikao.ai.ui.register.RegisterActivity;
import com.zhikao.ai.utils.SharedPrefs;
import com.zhikao.ai.utils.ToastUtils;

import java.util.HashMap;
import java.util.Map;

/**
 * 登录页
 */
public class LoginActivity extends AppCompatActivity implements View.OnClickListener {

    private TextInputLayout tilPhone;
    private TextInputLayout tilPassword;
    private EditText etPhone;
    private EditText etPassword;
    private MaterialButton btnLogin;
    private TextView tvRegister;
    private TextView tvForgetPassword;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        initViews();
        initListeners();
        // 填充记住的账号
        fillRememberInfo();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tilPhone = findViewById(R.id.til_phone);
        tilPassword = findViewById(R.id.til_password);
        etPhone = findViewById(R.id.et_phone);
        etPassword = findViewById(R.id.et_password);
        btnLogin = findViewById(R.id.btn_login);
        tvRegister = findViewById(R.id.tv_register);
        tvForgetPassword = findViewById(R.id.tv_forget_password);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        btnLogin.setOnClickListener(this);
        tvRegister.setOnClickListener(this);
        tvForgetPassword.setOnClickListener(this);
    }

    /**
     * 填充记住的账号信息
     */
    private void fillRememberInfo() {
        String phone = SharedPrefs.getString(ApiConfig.SP_REMEMBER_PHONE, "");
        String password = SharedPrefs.getString(ApiConfig.SP_REMEMBER_PASSWORD, "");
        if (!TextUtils.isEmpty(phone)) {
            etPhone.setText(phone);
            etPhone.setSelection(phone.length());
        }
        if (!TextUtils.isEmpty(password)) {
            etPassword.setText(password);
        }
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.btn_login) {
            login();
        } else if (id == R.id.tv_register) {
            goToRegister();
        } else if (id == R.id.tv_forget_password) {
            goToForgetPassword();
        }
    }

    /**
     * 登录
     */
    private void login() {
        String phone = etPhone.getText().toString().trim();
        String password = etPassword.getText().toString().trim();

        // 验证输入
        if (TextUtils.isEmpty(phone)) {
            tilPhone.setError("请输入手机号");
            return;
        }
        if (phone.length() != 11) {
            tilPhone.setError("请输入正确的手机号");
            return;
        }
        if (TextUtils.isEmpty(password)) {
            tilPassword.setError("请输入密码");
            return;
        }
        if (password.length() < 6) {
            tilPassword.setError("密码长度不能少于6位");
            return;
        }

        // 清除错误提示
        tilPhone.setError(null);
        tilPassword.setError(null);

        // 调用登录接口
        Map<String, String> params = new HashMap<>();
        params.put("phone", phone);
        params.put("password", password);

        ApiClient.getInstance().post(ApiConfig.LOGIN, params, new ApiCallback<User>() {
            @Override
            public void onSuccess(User user) {
                // 登录成功
                if (user != null) {
                    // 保存用户信息和Token
                    if (!TextUtils.isEmpty(user.getToken())) {
                        MyApplication.setToken(user.getToken());
                    }
                    MyApplication.setLogin(true);
                    // 保存用户信息
                    String userJson = ApiClient.getInstance().getGson().toJson(user);
                    SharedPrefs.putString(ApiConfig.SP_USER_INFO, userJson);
                    SharedPrefs.putString(ApiConfig.SP_USER_ID, user.getId());

                    // 记住账号
                    SharedPrefs.putString(ApiConfig.SP_REMEMBER_PHONE, phone);
                    SharedPrefs.putString(ApiConfig.SP_REMEMBER_PASSWORD, password);

                    ToastUtils.showShort("登录成功");
                    goToMain();
                }
            }

            @Override
            public void onFailure(int code, String message) {
                ToastUtils.showShort("登录失败：" + message);
            }

            @Override
            public void onComplete() {
                // 请求完成
            }
        });
    }

    /**
     * 跳转到注册页
     */
    private void goToRegister() {
        Intent intent = new Intent(this, RegisterActivity.class);
        startActivity(intent);
    }

    /**
     * 跳转到忘记密码页
     */
    private void goToForgetPassword() {
        ToastUtils.showShort("忘记密码功能开发中...");
    }

    /**
     * 跳转到主页
     */
    private void goToMain() {
        Intent intent = new Intent(this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
