const PageConfigPage = {
    state: {
        configs: [],
        loading: false,
        activeTab: 'intro'
    },

    async loadData() {
        this.state.loading = true;
        this.render();

        try {
            const res = await API.get('/page-configs');
            this.state.configs = res.data.list || res.data || [];
        } catch (error) {
            Toast.error(error.message || '加载失败');
            this.state.configs = [];
        }

        this.state.loading = false;
        this.render();
    },

    render() {
        const container = document.getElementById('config-content');
        if (!container) return;

        if (this.state.loading) {
            container.innerHTML = `
                <div class="text-center py-12">
                    <div class="loading-spinner mx-auto mb-4"></div>
                    <p class="text-gray-500">加载中...</p>
                </div>
            `;
            return;
        }

        const activeTab = this.state.activeTab;
        
        container.innerHTML = `
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">介绍页配置</h3>
                    <form id="intro-config-form" class="space-y-4">
                        <div>
                            <label class="form-label">应用标题</label>
                            <input type="text" name="app_title" class="input-field" 
                                   value="${this.getConfigValue('intro', 'app_title', '智考AI')}"
                                   placeholder="应用名称">
                        </div>
                        <div>
                            <label class="form-label">副标题</label>
                            <input type="text" name="app_subtitle" class="input-field" 
                                   value="${this.getConfigValue('intro', 'app_subtitle', '智能考试学习平台')}"
                                   placeholder="副标题">
                        </div>
                        <div>
                            <label class="form-label">介绍描述</label>
                            <textarea name="description" class="textarea-field" rows="4"
                                      placeholder="介绍页描述文字">${this.getConfigValue('intro', 'description', '')}</textarea>
                        </div>
                        <div>
                            <label class="form-label">功能亮点（每行一个）</label>
                            <textarea name="features" class="textarea-field" rows="4"
                                      placeholder="AI智能出题&#10;海量题库&#10;精准解析">${this.getConfigValue('intro', 'features', '')}</textarea>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="PageConfigPage.saveConfig('intro')">
                            保存配置
                        </button>
                    </form>
                </div>

                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">系统配置</h3>
                    <form id="system-config-form" class="space-y-4">
                        <div>
                            <label class="form-label">系统名称</label>
                            <input type="text" name="system_name" class="input-field" 
                                   value="${this.getConfigValue('system', 'system_name', '智考AI管理系统')}"
                                   placeholder="系统名称">
                        </div>
                        <div>
                            <label class="form-label">版权信息</label>
                            <input type="text" name="copyright" class="input-field" 
                                   value="${this.getConfigValue('system', 'copyright', '© 2024 智考AI')}"
                                   placeholder="版权信息">
                        </div>
                        <div>
                            <label class="form-label">备案号</label>
                            <input type="text" name="icp" class="input-field" 
                                   value="${this.getConfigValue('system', 'icp', '')}"
                                   placeholder="ICP备案号">
                        </div>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">用户注册</p>
                                    <p class="text-xs text-gray-500">开启后允许用户注册账号</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="allow_register" 
                                           ${this.getConfigValue('system', 'allow_register', '1') == '1' ? 'checked' : ''}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">社区功能</p>
                                    <p class="text-xs text-gray-500">开启后显示社区模块</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="community_enabled" 
                                           ${this.getConfigValue('system', 'community_enabled', '1') == '1' ? 'checked' : ''}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-medium text-gray-700">帖子审核</p>
                                    <p class="text-xs text-gray-500">开启后帖子需要审核才能显示</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="post_review" 
                                           ${this.getConfigValue('system', 'post_review', '1') == '1' ? 'checked' : ''}>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="PageConfigPage.saveConfig('system')">
                            保存配置
                        </button>
                    </form>
                </div>
            </div>
        `;
    },

    getConfigValue(group, key, defaultValue = '') {
        const config = this.state.configs.find(c => c.config_key === `${group}_${key}`);
        return config ? config.config_value || '' : defaultValue;
    },

    async saveConfig(group) {
        const form = document.getElementById(`${group}-config-form`);
        if (!form) return;

        const formData = new FormData(form);
        const data = {};
        
        for (let [key, value] of formData.entries()) {
            data[key] = value;
        }

        const checkboxes = form.querySelectorAll('input[type="checkbox"]');
        checkboxes.forEach(cb => {
            data[cb.name] = cb.checked ? '1' : '0';
        });

        try {
            const promises = Object.keys(data).map(key => {
                const configKey = `${group}_${key}`;
                const existing = this.state.configs.find(c => c.config_key === configKey);
                if (existing) {
                    return API.put(`/page-configs/${existing.id}`, {
                        config_key: configKey,
                        config_value: data[key]
                    });
                } else {
                    return API.post('/page-configs', {
                        config_key: configKey,
                        config_value: data[key],
                        config_group: group
                    });
                }
            });

            await Promise.all(promises);
            Toast.success('保存成功');
            this.loadData();
        } catch (error) {
            Toast.error(error.message || '保存失败');
        }
    }
};

Router.register('page-config', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">页面配置</h1>
        </div>

        <div id="config-content">
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

    PageConfigPage.state = {
        configs: [],
        loading: false,
        activeTab: 'intro'
    };

    PageConfigPage.loadData();
});
