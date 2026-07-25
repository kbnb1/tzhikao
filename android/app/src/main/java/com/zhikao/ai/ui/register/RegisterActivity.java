package com.zhikao.ai.ui.register;

import android.content.Intent;
import android.os.Bundle;
import android.os.CountDownTimer;
import android.text.TextUtils;
import android.view.View;
import android.widget.EditText;
import android.widget.TextView;

import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.AppCompatCheckBox;

import com.google.android.material.button.MaterialButton;
import com.google.android.material.textfield.TextInputLayout;
import com.zhikao.ai.R;
import com.zhikao.ai.app.ApiConfig;
import com.zhikao.ai.app.MyApplication;
import com.zhikao.ai.model.User;
import com.zhikao.ai.net.ApiCallback;
import com.zhikao.ai.net.ApiClient;
import com.zhikao.ai.ui.login.LoginActivity;
import com.zhikao.ai.utils.SharedPrefs;
import com.zhikao.ai.utils.ToastUtils;

import java.util.HashMap;
import java.util.Map;

/**
 * 注册页
 */
public class RegisterActivity extends AppCompatActivity implements View.OnClickListener {

    private TextInputLayout tilPhone;
    private TextInputLayout tilCode;
    private TextInputLayout tilPassword;
    private TextInputLayout tilConfirmPassword;
    private EditText etPhone;
    private EditText etCode;
    private EditText etPassword;
    private EditText etConfirmPassword;
    private TextView tvGetCode;
    private MaterialButton btnRegister;
    private TextView tvLogin;
    private AppCompatCheckBox cbAgree;

    private CountDownTimer countDownTimer;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        initViews();
        initListeners();
    }

    /**
     * 初始化视图
     */
    private void initViews() {
        tilPhone = findViewById(R.id.til_phone);
        tilCode = findViewById(R.id.til_code);
        tilPassword = findViewById(R.id.til_password);
        tilConfirmPassword = findViewById(R.id.til_confirm_password);
        etPhone = findViewById(R.id.et_phone);
        etCode = findViewById(R.id.et_code);
        etPassword = findViewById(R.id.et_password);
        etConfirmPassword = findViewById(R.id.et_confirm_password);
        tvGetCode = findViewById(R.id.tv_get_code);
        btnRegister = findViewById(R.id.btn_register);
        tvLogin = findViewById(R.id.tv_login);
        cbAgree = findViewById(R.id.cb_agree);
    }

    /**
     * 初始化监听器
     */
    private void initListeners() {
        tvGetCode.setOnClickListener(this);
        btnRegister.setOnClickListener(this);
        tvLogin.setOnClickListener(this);

        // 返回按钮
        findViewById(R.id.toolbar).setOnClickListener(v -> finish());
    }

    @Override
    public void onClick(View v) {
        int id = v.getId();
        if (id == R.id.tv_get_code) {
            sendVerifyCode();
        } else if (id == R.id.btn_register) {
            register();
        } else if (id == R.id.tv_login) {
            goToLogin();
        }
    }

    /**
     * 发送验证码
     */
    private void sendVerifyCode() {
        String phone = etPhone.getText().toString().trim();
        if (TextUtils.isEmpty(phone)) {
            tilPhone.setError("请输入手机号");
            return;
        }
        if (phone.length() != 11) {
            tilPhone.setError("请输入正确的手机号");
            return;
        }
        tilPhone.setError(null);

        // 调用发送验证码接口
        Map<String, String> params = new HashMap<>();
        params.put("phone", phone);
        params.put("type", "register");

        ApiClient.getInstance().post(ApiConfig.SEND_CODE, params, new ApiCallback<Object>() {
            @Override
            public void onSuccess(Object data) {
                ToastUtils.showShort("验证码已发送");
                startCountDown();
            }

            @Override
            public void onFailure(int code, String message) {
                ToastUtils.showShort("发送失败：" + message);
            }

            @Override
            public void onComplete() {
            }
        });
    }

    /**
     * 开始倒计时
     */
    private void startCountDown() {
        tvGetCode.setEnabled(false);
        countDownTimer = new CountDownTimer(ApiConfig.CODE_COUNT_DOWN * 1000L, 1000) {
            @Override
            public void onTick(long millisUntilFinished) {
                tvGetCode.setText(millisUntilFinished / 1000 + "s后重发");
            }

            @Override
            public void onFinish() {
                tvGetCode.setText(R.string.get_verify_code);
                tvGetCode.setEnabled(true);
            }
        };
        countDownTimer.start();
    }

    /**
     * 注册
     */
    private void register() {
        String phone = etPhone.getText().toString().trim();
        String code = etCode.getText().toString().trim();
        String password = etPassword.getText().toString().trim();
        String confirmPassword = etConfirmPassword.getText().toString().trim();

        // 验证输入
        if (TextUtils.isEmpty(phone)) {
            tilPhone.setError("请输入手机号");
            return;
        }
        if (phone.length() != 11) {
            tilPhone.setError("请输入正确的手机号");
            return;
        }
        if (TextUtils.isEmpty(code)) {
            tilCode.setError("请输入验证码");
            return;
        }
        if (code.length() != 6) {
            tilCode.setError("请输入正确的验证码");
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
        if (!password.equals(confirmPassword)) {
            tilConfirmPassword.setError("两次密码输入不一致");
            return;
        }
        if (!cbAgree.isChecked()) {
            ToastUtils.showShort("请先阅读并同意用户协议和隐私政策");
            return;
        }

        // 清除错误提示
        tilPhone.setError(null);
        tilCode.setError(null);
        tilPassword.setError(null);
        tilConfirmPassword.setError(null);

        // 调用注册接口
        Map<String, String> params = new HashMap<>();
        params.put("phone", phone);
        params.put("code", code);
        params.put("password", password);

        ApiClient.getInstance().post(ApiConfig.REGISTER, params, new ApiCallback<User>() {
            @Override
            public void onSuccess(User user) {
                // 注册成功
                if (user != null) {
                    if (!TextUtils.isEmpty(user.getToken())) {
                        MyApplication.setToken(user.getToken());
                    }
                    MyApplication.setLogin(true);
                    String userJson = ApiClient.getInstance().getGson().toJson(user);
                    SharedPrefs.putString(ApiConfig.SP_USER_INFO, userJson);
                    SharedPrefs.putString(ApiConfig.SP_USER_ID, user.getId());

                    ToastUtils.showShort("注册成功");
                    goToMain();
                }
            }

            @Override
            public void onFailure(int code, String message) {
                ToastUtils.showShort("注册失败：" + message);
            }

            @Override
            public void onComplete() {
            }
        });
    }

    /**
     * 跳转到登录页
     */
    private void goToLogin() {
        Intent intent = new Intent(this, LoginActivity.class);
        startActivity(intent);
        finish();
    }

    /**
     * 跳转到主页
     */
    private void goToMain() {
        Intent intent = new Intent(this, LoginActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        // 取消倒计时
        if (countDownTimer != null) {
            countDownTimer.cancel();
        }
    }
}
