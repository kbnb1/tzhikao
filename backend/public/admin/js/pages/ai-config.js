const AiConfigPage = {
    state: {
        list: [],
        loading: false
    },

    async loadData() {
        this.state.loading = true;
        this.render();

        try {
            const res = await API.get('/ai-configs');
            this.state.list = res.data.list || res.data || [];
        } catch (error) {
            Toast.error(error.message || '加载失败');
            this.state.list = [];
        }

        this.state.loading = false;
        this.render();
    },

    render() {
        const container = document.getElementById('ai-config-list');
        if (!container) return;

        if (this.state.loading) {
            container.innerHTML = `
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    ${[1,2,3].map(() => `
                        <div class="card p-6 animate-pulse">
                            <div class="h-6 bg-gray-200 rounded w-1/2 mb-4"></div>
                            <div class="h-4 bg-gray-200 rounded w-full mb-2"></div>
                            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
                        </div>
                    `).join('')}
                </div>
            `;
            return;
        }

        if (this.state.list.length === 0) {
            container.innerHTML = Components.emptyState('暂无AI配置', 'fa-robot');
            return;
        }

        container.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                ${this.state.list.map(item => `
                    <div class="card p-6 relative ${item.is_default ? 'ring-2 ring-primary-500' : ''}">
                        ${item.is_default ? `
                            <span class="absolute top-4 right-4 px-2 py-1 bg-primary-500 text-white text-xs rounded-full">
                                默认
                            </span>
                        ` : ''}
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white">
                                <i class="fas fa-robot text-xl"></i>
                            </div>
                            <div>
                                <h3 class="font-semibold text-gray-800">${Utils.escapeHtml(item.name || '')}</h3>
                                <p class="text-xs text-gray-500">${Utils.escapeHtml(item.provider || '')}</p>
                            </div>
                        </div>
                        <div class="space-y-2 text-sm text-gray-600 mb-4">
                            <p><span class="text-gray-400">模型：</span>${Utils.escapeHtml(item.model || '-')}</p>
                            <p><span class="text-gray-400">API地址：</span>${Utils.escapeHtml(item.api_url || '-')}</p>
                        </div>
                        <div class="flex items-center gap-2 pt-4 border-t border-gray-100">
                            ${!item.is_default ? `
                                <button class="btn btn-sm btn-secondary flex-1" onclick="AiConfigPage.setDefault(${item.id})">
                                    <i class="fas fa-star mr-1"></i>
                                    设为默认
                                </button>
                            ` : `
                                <button class="btn btn-sm btn-secondary flex-1" disabled>
                                    <i class="fas fa-check mr-1"></i>
                                    已设为默认
                                </button>
                            `}
                            <button class="btn btn-sm btn-secondary" onclick="AiConfigPage.edit(${item.id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="AiConfigPage.delete(${item.id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                `).join('')}
            </div>
        `;
    },

    add() {
        this.showModal();
    },

    async edit(id) {
        try {
            const res = await API.get(`/ai-configs/${id}`);
            this.showModal(res.data);
        } catch (error) {
            Toast.error(error.message || '获取信息失败');
        }
    },

    showModal(data = null) {
        const isEdit = !!data;
        const form = document.createElement('form');
        form.className = 'space-y-4';
        form.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">配置名称 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="input-field" 
                           value="${data?.name || ''}" placeholder="请输入配置名称">
                </div>
                <div>
                    <label class="form-label">服务商</label>
                    <select name="provider" class="select-field">
                        <option value="openai" ${data?.provider == 'openai' ? 'selected' : ''}>OpenAI</option>
                        <option value="azure" ${data?.provider == 'azure' ? 'selected' : ''}>Azure OpenAI</option>
                        <option value="anthropic" ${data?.provider == 'anthropic' ? 'selected' : ''}>Anthropic</option>
                        <option value="baidu" ${data?.provider == 'baidu' ? 'selected' : ''}>百度文心</option>
                        <option value="alibaba" ${data?.provider == 'alibaba' ? 'selected' : ''}>阿里通义</option>
                        <option value="zhipu" ${data?.provider == 'zhipu' ? 'selected' : ''}>智谱AI</option>
                        <option value="other" ${data?.provider == 'other' ? 'selected' : ''}>其他</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">API地址</label>
                <input type="text" name="api_url" class="input-field" 
                       value="${data?.api_url || ''}" placeholder="https://api.example.com/v1">
            </div>
            <div>
                <label class="form-label">API Key <span class="text-red-500">*</span></label>
                <input type="password" name="api_key" class="input-field" 
                       value="${data?.api_key || ''}" placeholder="请输入API Key">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">模型名称</label>
                    <input type="text" name="model" class="input-field" 
                           value="${data?.model || 'gpt-3.5-turbo'}" placeholder="如：gpt-3.5-turbo">
                </div>
                <div>
                    <label class="form-label">温度</label>
                    <input type="number" name="temperature" step="0.1" min="0" max="2" class="input-field" 
                           value="${data?.temperature ?? 0.7}" placeholder="0-2之间">
                </div>
            </div>
            <div>
                <label class="form-label">状态</label>
                <select name="status" class="select-field">
                    <option value="1" ${data?.status == 1 ? 'selected' : ''}>启用</option>
                    <option value="0" ${data?.status == 0 ? 'selected' : ''}>禁用</option>
                </select>
            </div>
        `;

        Modal.show({
            title: isEdit ? '编辑AI配置' : '新增AI配置',
            content: form,
            width: '550px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!data.name) {
                    Toast.error('请输入配置名称');
                    return false;
                }
                if (!data.api_key && !isEdit) {
                    Toast.error('请输入API Key');
                    return false;
                }

                try {
                    if (isEdit) {
                        if (!data.api_key) delete data.api_key;
                        await API.put(`/ai-configs/${data.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/ai-configs', data);
                        Toast.success('创建成功');
                    }
                    this.loadData();
                    return true;
                } catch (error) {
                    Toast.error(error.message || '操作失败');
                    return false;
                }
            }
        });
    },

    setDefault(id) {
        Modal.confirm('确定要将此配置设为默认吗？', async () => {
            try {
                await API.put(`/ai-configs/${id}/default`);
                Toast.success('设置成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '设置失败');
            }
        });
    },

    delete(id) {
        Modal.confirm('确定要删除该AI配置吗？', async () => {
            try {
                await API.delete(`/ai-configs/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('ai-config', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">AI配置管理</h1>
            <button class="btn btn-primary" onclick="AiConfigPage.add()">
                <i class="fas fa-plus mr-2"></i>
                新增配置
            </button>
        </div>

        <div class="mb-6 p-4 bg-blue-50 rounded-xl border border-blue-100">
            <div class="flex items-start gap-3">
                <i class="fas fa-info-circle text-blue-500 mt-0.5"></i>
                <div class="text-sm text-blue-700">
                    <p class="font-medium">提示</p>
                    <p class="mt-1">配置多个AI服务商，可随时切换默认服务商。系统将使用默认配置进行AI相关功能调用。</p>
                </div>
            </div>
        </div>

        <div id="ai-config-list"></div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    AiConfigPage.state = {
        list: [],
        loading: false
    };

    AiConfigPage.loadData();
});
