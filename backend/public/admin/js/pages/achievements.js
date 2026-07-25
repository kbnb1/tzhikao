const AchievementPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        type: '',
        loading: false
    },

    getTypeText(type) {
        const types = {
            1: '学习类',
            2: '考试类',
            3: '社区类',
            4: '特殊类'
        };
        return types[type] || '未知';
    },

    getTypeColor(type) {
        const colors = {
            1: 'bg-blue-100 text-blue-600',
            2: 'bg-green-100 text-green-600',
            3: 'bg-purple-100 text-purple-600',
            4: 'bg-yellow-100 text-yellow-600'
        };
        return colors[type] || 'bg-gray-100 text-gray-600';
    },

    async loadData() {
        this.state.loading = true;
        this.renderTable();

        try {
            const params = {
                page: this.state.page,
                page_size: this.state.pageSize
            };
            if (this.state.keyword) params.keyword = this.state.keyword;
            if (this.state.type) params.type = this.state.type;

            const query = Utils.buildQueryString(params);
            const res = await API.get(`/achievements?${query}`);
            this.state.list = res.data.list || [];
            this.state.total = res.data.total || 0;
        } catch (error) {
            Toast.error(error.message || '加载失败');
            this.state.list = [];
            this.state.total = 0;
        }

        this.state.loading = false;
        this.renderTable();
    },

    renderTable() {
        const tbody = document.getElementById('achievement-table-body');
        const paginationContainer = document.getElementById('achievement-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(7);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7">${Components.emptyState('暂无成就数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => `
            <tr class="table-row">
                <td>${item.id}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl ${item.bg_color ? '' : 'bg-gradient-to-br from-yellow-400 to-orange-500'} flex items-center justify-center text-white text-lg"
                             ${item.bg_color ? `style="background: ${item.bg_color}"` : ''}>
                            <i class="fas ${item.icon || 'fa-trophy'}"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">${Utils.escapeHtml(item.name || '')}</p>
                            <p class="text-xs text-gray-400">${Utils.escapeHtml(item.description || '')}</p>
                        </div>
                    </div>
                </td>
                <td><span class="px-2 py-1 ${this.getTypeColor(item.type)} text-xs rounded">${this.getTypeText(item.type)}</span></td>
                <td>${item.user_count || 0}人获得</td>
                <td>
                    <span class="status-badge ${item.status == 1 ? 'status-active' : 'status-inactive'}">
                        ${item.status == 1 ? '启用' : '禁用'}
                    </span>
                </td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="AchievementPage.edit(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="AchievementPage.delete(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');

        if (paginationContainer) {
            paginationContainer.innerHTML = '';
            if (this.state.total > 0) {
                const pagination = Components.pagination(
                    this.state.total,
                    this.state.page,
                    this.state.pageSize,
                    (page) => {
                        this.state.page = page;
                        this.loadData();
                    }
                );
                paginationContainer.appendChild(pagination);
            }
        }
    },

    search() {
        this.state.keyword = document.getElementById('search-keyword').value.trim();
        this.state.type = document.getElementById('filter-type').value;
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        document.getElementById('filter-type').value = '';
        this.state.keyword = '';
        this.state.type = '';
        this.state.page = 1;
        this.loadData();
    },

    add() {
        this.showModal();
    },

    async edit(id) {
        try {
            const res = await API.get(`/achievements/${id}`);
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
                    <label class="form-label">成就名称 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" class="input-field" 
                           value="${data?.name || ''}" placeholder="请输入成就名称">
                </div>
                <div>
                    <label class="form-label">类型</label>
                    <select name="type" class="select-field">
                        <option value="1" ${data?.type == 1 ? 'selected' : ''}>学习类</option>
                        <option value="2" ${data?.type == 2 ? 'selected' : ''}>考试类</option>
                        <option value="3" ${data?.type == 3 ? 'selected' : ''}>社区类</option>
                        <option value="4" ${data?.type == 4 ? 'selected' : ''}>特殊类</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">成就描述</label>
                <textarea name="description" class="textarea-field" rows="2"
                          placeholder="请输入成就描述">${data?.description || ''}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">图标</label>
                    <input type="text" name="icon" class="input-field" 
                           value="${data?.icon || 'fa-trophy'}" placeholder="Font Awesome图标名">
                </div>
                <div>
                    <label class="form-label">背景色</label>
                    <input type="color" name="bg_color" class="input-field h-10 p-1" 
                           value="${data?.bg_color || '#fbbf24'}">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">解锁条件</label>
                    <input type="text" name="condition" class="input-field" 
                           value="${data?.condition || ''}" placeholder="解锁条件描述">
                </div>
                <div>
                    <label class="form-label">条件值</label>
                    <input type="number" name="condition_value" class="input-field" 
                           value="${data?.condition_value || 0}" placeholder="条件数值">
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
            title: isEdit ? '编辑成就' : '新增成就',
            content: form,
            width: '550px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!data.name) {
                    Toast.error('请输入成就名称');
                    return false;
                }

                try {
                    if (isEdit) {
                        await API.put(`/achievements/${data.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/achievements', data);
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

    delete(id) {
        Modal.confirm('确定要删除该成就吗？', async () => {
            try {
                await API.delete(`/achievements/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('achievements', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">成就管理</h1>
            <button class="btn btn-primary" onclick="AchievementPage.add()">
                <i class="fas fa-plus mr-2"></i>
                新增成就
            </button>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索成就名称">
                </div>
                <div class="w-36">
                    <select id="filter-type" class="select-field">
                        <option value="">全部类型</option>
                        <option value="1">学习类</option>
                        <option value="2">考试类</option>
                        <option value="3">社区类</option>
                        <option value="4">特殊类</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="AchievementPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="AchievementPage.resetSearch()">
                        <i class="fas fa-redo mr-2"></i>
                        重置
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>成就</th>
                            <th>类型</th>
                            <th>获得人数</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="achievement-table-body">
                        ${Components.loadingRow(7)}
                    </tbody>
                </table>
            </div>

            <div id="achievement-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    AchievementPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        type: '',
        loading: false
    };

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            AchievementPage.search();
        }
    });

    AchievementPage.loadData();
});
