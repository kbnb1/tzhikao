const UserPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        status: '',
        loading: false
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
            if (this.state.status !== '') params.status = this.state.status;

            const query = Utils.buildQueryString(params);
            const res = await API.get(`/users?${query}`);
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
        const tbody = document.getElementById('user-table-body');
        const paginationContainer = document.getElementById('user-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(7);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7">${Components.emptyState('暂无用户数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(user => `
            <tr class="table-row">
                <td>${user.id}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center text-sm font-medium">
                            ${(user.nickname || user.username || 'U').charAt(0).toUpperCase()}
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">${Utils.escapeHtml(user.nickname || user.username)}</p>
                            <p class="text-xs text-gray-400">${Utils.escapeHtml(user.username || '')}</p>
                        </div>
                    </div>
                </td>
                <td>${Utils.escapeHtml(user.mobile || '-')}</td>
                <td>${Utils.escapeHtml(user.email || '-')}</td>
                <td>
                    <span class="status-badge ${user.status == 1 ? 'status-active' : 'status-inactive'}">
                        ${user.status == 1 ? '正常' : '禁用'}
                    </span>
                </td>
                <td>${Utils.formatDate(user.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="UserPage.editUser(${user.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm ${user.status == 1 ? 'btn-danger' : 'btn-success'}" 
                                onclick="UserPage.toggleStatus(${user.id})">
                            <i class="fas ${user.status == 1 ? 'fa-ban' : 'fa-check'}"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="UserPage.deleteUser(${user.id})">
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
        this.state.status = document.getElementById('search-status').value;
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        document.getElementById('search-status').value = '';
        this.state.keyword = '';
        this.state.status = '';
        this.state.page = 1;
        this.loadData();
    },

    addUser() {
        this.showUserModal();
    },

    async editUser(id) {
        try {
            const res = await API.get(`/users/${id}`);
            this.showUserModal(res.data);
        } catch (error) {
            Toast.error(error.message || '获取用户信息失败');
        }
    },

    showUserModal(user = null) {
        const isEdit = !!user;
        const form = document.createElement('form');
        form.className = 'space-y-4';
        form.innerHTML = `
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">用户名 <span class="text-red-500">*</span></label>
                    <input type="text" name="username" class="input-field" 
                           value="${user?.username || ''}" ${isEdit ? 'readonly' : ''}
                           placeholder="请输入用户名">
                </div>
                <div>
                    <label class="form-label">昵称</label>
                    <input type="text" name="nickname" class="input-field" 
                           value="${user?.nickname || ''}" placeholder="请输入昵称">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">手机号</label>
                    <input type="text" name="mobile" class="input-field" 
                           value="${user?.mobile || ''}" placeholder="请输入手机号">
                </div>
                <div>
                    <label class="form-label">邮箱</label>
                    <input type="email" name="email" class="input-field" 
                           value="${user?.email || ''}" placeholder="请输入邮箱">
                </div>
            </div>
            <div>
                <label class="form-label">${isEdit ? '新密码（留空则不修改）' : '密码 <span class="text-red-500">*</span>'}</label>
                <input type="password" name="password" class="input-field" placeholder="${isEdit ? '留空不修改' : '请输入密码'}">
            </div>
            <div>
                <label class="form-label">状态</label>
                <select name="status" class="select-field">
                    <option value="1" ${user?.status == 1 ? 'selected' : ''}>正常</option>
                    <option value="0" ${user?.status == 0 ? 'selected' : ''}>禁用</option>
                </select>
            </div>
        `;

        Modal.show({
            title: isEdit ? '编辑用户' : '新增用户',
            content: form,
            width: '500px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!isEdit && !data.username) {
                    Toast.error('请输入用户名');
                    return false;
                }
                if (!isEdit && !data.password) {
                    Toast.error('请输入密码');
                    return false;
                }

                try {
                    if (isEdit) {
                        if (!data.password) delete data.password;
                        await API.put(`/users/${user.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/users', data);
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

    toggleStatus(id) {
        Modal.confirm('确定要修改该用户的状态吗？', async () => {
            try {
                await API.put(`/users/${id}/toggle-status`);
                Toast.success('操作成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '操作失败');
            }
        });
    },

    deleteUser(id) {
        Modal.confirm('确定要删除该用户吗？此操作不可恢复。', async () => {
            try {
                await API.delete(`/users/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('users', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">用户管理</h1>
            <button class="btn btn-primary" onclick="UserPage.addUser()">
                <i class="fas fa-plus mr-2"></i>
                新增用户
            </button>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索用户名/昵称/手机号">
                </div>
                <div class="w-40">
                    <select id="search-status" class="select-field">
                        <option value="">全部状态</option>
                        <option value="1">正常</option>
                        <option value="0">禁用</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="UserPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="UserPage.resetSearch()">
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
                            <th>用户</th>
                            <th>手机号</th>
                            <th>邮箱</th>
                            <th>状态</th>
                            <th>注册时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="user-table-body">
                        ${Components.loadingRow(7)}
                    </tbody>
                </table>
            </div>

            <div id="user-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    UserPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        status: '',
        loading: false
    };

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            UserPage.search();
        }
    });

    UserPage.loadData();
});
