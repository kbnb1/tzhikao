package com.zhikao.ai.net;

/**
 * 网络请求回调接口
 * @param <T> 泛型类型
 */
public interface ApiCallback<T> {

    /**
     * 请求成功
     * @param data 返回的数据
     */
    void onSuccess(T data);

    /**
     * 请求失败
     * @param code 错误码
     * @param message 错误信息
     */
    void onFailure(int code, String message);

    /**
     * 请求完成（无论成功失败都会调用）
     */
    void onComplete();
}
