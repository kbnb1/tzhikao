Router.register('login', async (app) => {
    app.innerHTML = `
        <div class="min-h-screen bg-gradient-to-br from-indigo-900 via-purple-900 to-indigo-800 flex items-center justify-center p-4">
            <div class="absolute inset-0 overflow-hidden">
                <div class="absolute -top-40 -right-40 w-80 h-80 bg-purple-500/20 rounded-full blur-3xl"></div>
                <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-indigo-500/20 rounded-full blur-3xl"></div>
            </div>
            <div class="relative w-full max-w-md">
                <div class="bg-white/10 backdrop-blur-xl rounded-2xl shadow-2xl border border-white/20 p-8">
                    <div class="text-center mb-8">
                        <div class="inline-flex items-center justify-center w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl mb-4 shadow-lg">
                            <i class="fas fa-brain text-3xl text-white"></i>
                        </div>
                        <h1 class="text-2xl font-bold text-white mb-2">智考AI管理后台</h1>
                        <p class="text-indigo-200 text-sm">智能考试管理系统</p>
                    </div>
                    <form id="login-form" class="space-y-6">
                        <div>
                            <label class="block text-sm font-medium text-indigo-100 mb-2">用户名</label>
                            <div class="relative">
                                <i class="fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300"></i>
                                <input type="text" id="username" name="username" 
                                    class="w-full pl-12 pr-4 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-300 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 transition-all"
                                    placeholder="请输入用户名" autocomplete="username">
                            </div>
                            <p id="username-error" class="mt-1 text-sm text-red-400 hidden"></p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-indigo-100 mb-2">密码</label>
                            <div class="relative">
                                <i class="fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-indigo-300"></i>
                                <input type="password" id="password" name="password" 
                                    class="w-full pl-12 pr-12 py-3 bg-white/10 border border-white/20 rounded-xl text-white placeholder-indigo-300 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-400/30 transition-all"
                                    placeholder="请输入密码" autocomplete="current-password">
                                <button type="button" id="toggle-password" class="absolute right-4 top-1/2 -translate-y-1/2 text-indigo-300 hover:text-white transition-colors">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <p id="password-error" class="mt-1 text-sm text-red-400 hidden"></p>
                        </div>
                        <div class="flex items-center justify-between">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" id="remember" class="w-4 h-4 rounded border-white/30 bg-white/10 text-indigo-500 focus:ring-indigo-400">
                                <span class="text-sm text-indigo-200">记住我</span>
                            </label>
                        </div>
                        <button type="submit" id="login-btn" 
                            class="w-full py-3 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white font-medium rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
                            <span id="login-btn-text">登 录</span>
                            <div id="login-loading" class="loading-spinner hidden" style="width: 20px; height: 20px; border-width: 2px;"></div>
                        </button>
                    </form>
                    <div class="mt-8 text-center">
                        <p class="text-indigo-300/60 text-xs">
                            智考AI管理系统 © 2024
                        </p>
                    </div>
                </div>
            </div>
        </div>
    `;

    const togglePasswordBtn = document.getElementById('toggle-password');
    const passwordInput = document.getElementById('password');
    const loginForm = document.getElementById('login-form');
    const loginBtn = document.getElementById('login-btn');
    const loginBtnText = document.getElementById('login-btn-text');
    const loginLoading = document.getElementById('login-loading');

    togglePasswordBtn.onclick = () => {
        const type = passwordInput.type === 'password' ? 'text' : 'password';
        passwordInput.type = type;
        togglePasswordBtn.innerHTML = type === 'password' 
            ? '<i class="fas fa-eye"></i>' 
            : '<i class="fas fa-eye-slash"></i>';
    };

    const showError = (field, message) => {
        const errorEl = document.getElementById(`${field}-error`);
        const inputEl = document.getElementById(field);
        errorEl.textContent = message;
        errorEl.classList.remove('hidden');
        inputEl.classList.add('ring-2', 'ring-red-400/50');
    };

    const clearError = (field) => {
        const errorEl = document.getElementById(`${field}-error`);
        const inputEl = document.getElementById(field);
        errorEl.classList.add('hidden');
        inputEl.classList.remove('ring-2', 'ring-red-400/50');
    };

    document.getElementById('username').addEventListener('input', () => clearError('username'));
    document.getElementById('password').addEventListener('input', () => clearError('password'));

    loginForm.onsubmit = async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('username').value.trim();
        const password = document.getElementById('password').value;
        
        let hasError = false;
        
        if (!username) {
            showError('username', '请输入用户名');
            hasError = true;
        }
        
        if (!password) {
            showError('password', '请输入密码');
            hasError = true;
        }
        
        if (hasError) return;

        loginBtn.disabled = true;
        loginBtnText.classList.add('hidden');
        loginLoading.classList.remove('hidden');

        try {
            const res = await API.post('/login', { username, password });
            API.setToken(res.data.token);
            AppState.adminInfo = null;
            Toast.success('登录成功');
            
            setTimeout(() => {
                Router.navigate('dashboard');
            }, 500);
        } catch (error) {
            Toast.error(error.message || '登录失败');
            loginBtn.disabled = false;
            loginBtnText.classList.remove('hidden');
            loginLoading.classList.add('hidden');
        }
    };

    passwordInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            loginForm.requestSubmit();
        }
    });
});
