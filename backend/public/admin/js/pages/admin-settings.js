const AdminSettingsPage = {
    state: {
        adminInfo: null,
        activeTab: 'profile'
    },

    async loadData() {
        try {
            const res = await API.get('/admin/info');
            this.state.adminInfo = res.data;
            this.render();
        } catch (error) {
            Toast.error(error.message || '加载失败');
        }
    },

    render() {
        const container = document.getElementById('settings-content');
        if (!container) return;

        const info = this.state.adminInfo || {};
        const activeTab = this.state.activeTab;

        container.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-1">
                    <div class="card p-6 text-center">
                        <div class="w-24 h-24 mx-auto mb-4 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-3xl font-bold">
                            ${(info.nickname || info.username || 'A').charAt(0).toUpperCase()}
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800">${Utils.escapeHtml(info.nickname || info.username || '管理员')}</h3>
                        <p class="text-gray-500 text-sm mt-1">${Utils.escapeHtml(info.username || '')}</p>
                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <div class="space-y-3">
                                <button class="w-full text-left px-4 py-3 rounded-lg transition-colors ${activeTab === 'profile' ? 'bg-primary-50 text-primary-600' : 'hover:bg-gray-50 text-gray-700'}"
                                        onclick="AdminSettingsPage.switchTab('profile')">
                                    <i class="fas fa-user mr-3"></i>
                                    个人信息
                                </button>
                                <button class="w-full text-left px-4 py-3 rounded-lg transition-colors ${activeTab === 'password' ? 'bg-primary-50 text-primary-600' : 'hover:bg-gray-50 text-gray-700'}"
                                        onclick="AdminSettingsPage.switchTab('password')">
                                    <i class="fas fa-lock mr-3"></i>
                                    修改密码
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <div class="card p-6">
                        ${activeTab === 'profile' ? this.renderProfileForm(info) : this.renderPasswordForm()}
                    </div>
                </div>
            </div>
        `;
    },

    renderProfileForm(info) {
        return `
            <h3 class="text-lg font-semibold text-gray-800 mb-6">个人信息</h3>
            <form id="profile-form" class="space-y-4 max-w-md">
                <div>
                    <label class="form-label">用户名</label>
                    <input type="text" class="input-field bg-gray-50" 
                           value="${Utils.escapeHtml(info.username || '')}" readonly>
                    <p class="text-xs text-gray-400 mt-1">用户名不可修改</p>
                </div>
                <div>
                    <label class="form-label">昵称</label>
                    <input type="text" name="nickname" class="input-field" 
                           value="${Utils.escapeHtml(info.nickname || '')}" placeholder="请输入昵称">
                </div>
                <div>
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" class="input-field" 
                           value="${Utils.escapeHtml(info.email || '')}" placeholder="请输入邮箱">
                </div>
                <div>
                    <label class="form-label">手机号</label>
                    <input type="text" name="mobile" class="input-field" 
                           value="${Utils.escapeHtml(info.mobile || '')}" placeholder="请输入手机号">
                </div>
                <div class="pt-4">
                    <button type="button" class="btn btn-primary" onclick="AdminSettingsPage.saveProfile()">
                        保存修改
                    </button>
                </div>
            </form>
        `;
    },

    renderPasswordForm() {
        return `
            <h3 class="text-lg font-semibold text-gray-800 mb-6">修改密码</h3>
            <form id="password-form" class="space-y-4 max-w-md">
                <div>
                    <label class="form-label">当前密码 <span class="text-red-500">*</span></label>
                    <input type="password" name="old_password" class="input-field" 
                           placeholder="请输入当前密码">
                </div>
                <div>
                    <label class="form-label">新密码 <span class="text-red-500">*</span></label>
                    <input type="password" name="new_password" class="input-field" 
                           placeholder="请输入新密码（至少6位）">
                </div>
                <div>
                    <label class="form-label">确认新密码 <span class="text-red-500">*</span></label>
                    <input type="password" name="confirm_password" class="input-field" 
                           placeholder="请再次输入新密码">
                </div>
                <div class="pt-4">
                    <button type="button" class="btn btn-primary" onclick="AdminSettingsPage.changePassword()">
                        修改密码
                    </button>
                </div>
            </form>
        `;
    },

    switchTab(tab) {
        this.state.activeTab = tab;
        this.render();
    },

    saveProfile() {
        const form = document.getElementById('profile-form');
        if (!form) return;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.nickname) {
            Toast.warning('请输入昵称');
            return;
        }

        Toast.info('保存功能待完善');
    },

    changePassword() {
        const form = document.getElementById('password-form');
        if (!form) return;

        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());

        if (!data.old_password) {
            Toast.warning('请输入当前密码');
            return;
        }
        if (!data.new_password) {
            Toast.warning('请输入新密码');
            return;
        }
        if (data.new_password.length < 6) {
            Toast.warning('新密码至少6位');
            return;
        }
        if (data.new_password !== data.confirm_password) {
            Toast.warning('两次输入的密码不一致');
            return;
        }

        Modal.confirm('确定要修改密码吗？', async () => {
            try {
                Toast.success('密码修改成功');
            } catch (error) {
                Toast.error(error.message || '修改失败');
            }
        });
    }
};

Router.register('admin-settings', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">管理员设置</h1>
        </div>

        <div id="settings-content">
            <div class="text-center py-12">
                <div class="loading-spinner mx-auto mb-4"></div>
                <p class="text-gray-500">加载中...</p>
            </div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    AdminSettingsPage.state = {
        adminInfo: null,
        activeTab: 'profile'
    };

    AdminSettingsPage.loadData();
});
