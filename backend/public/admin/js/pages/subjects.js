const SubjectPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
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

            const query = Utils.buildQueryString(params);
            const res = await API.get(`/subjects?${query}`);
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
        const tbody = document.getElementById('subject-table-body');
        const paginationContainer = document.getElementById('subject-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(6);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6">${Components.emptyState('暂无科目数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => `
            <tr class="table-row">
                <td>${item.id}</td>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-purple-500 flex items-center justify-center text-white">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-800">${Utils.escapeHtml(item.name || '')}</p>
                            <p class="text-xs text-gray-400">${Utils.escapeHtml(item.code || '')}</p>
                        </div>
                    </div>
                </td>
                <td>${Utils.escapeHtml(item.description || '-')}</td>
                <td>
                    <span class="status-badge ${item.status == 1 ? 'status-active' : 'status-inactive'}">
                        ${item.status == 1 ? '启用' : '禁用'}
                    </span>
                </td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="SubjectPage.edit(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="SubjectPage.delete(${item.id})">
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
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        this.state.keyword = '';
        this.state.page = 1;
        this.loadData();
    },

    add() {
        this.showModal();
    },

    async edit(id) {
        try {
            const res = await API.get(`/subjects/${id}`);
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
            <div>
                <label class="form-label">科目名称 <span class="text-red-500">*</span></label>
                <input type="text" name="name" class="input-field" 
                       value="${data?.name || ''}" placeholder="请输入科目名称">
            </div>
            <div>
                <label class="form-label">科目编码</label>
                <input type="text" name="code" class="input-field" 
                       value="${data?.code || ''}" placeholder="请输入科目编码">
            </div>
            <div>
                <label class="form-label">科目描述</label>
                <textarea name="description" class="textarea-field" rows="3"
                          placeholder="请输入科目描述">${data?.description || ''}</textarea>
            </div>
            <div>
                <label class="form-label">排序</label>
                <input type="number" name="sort" class="input-field" 
                       value="${data?.sort || 0}" placeholder="数字越小越靠前">
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
            title: isEdit ? '编辑科目' : '新增科目',
            content: form,
            width: '500px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!data.name) {
                    Toast.error('请输入科目名称');
                    return false;
                }

                try {
                    if (isEdit) {
                        await API.put(`/subjects/${data.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/subjects', data);
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
        Modal.confirm('确定要删除该科目吗？', async () => {
            try {
                await API.delete(`/subjects/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('subjects', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">科目管理</h1>
            <button class="btn btn-primary" onclick="SubjectPage.add()">
                <i class="fas fa-plus mr-2"></i>
                新增科目
            </button>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索科目名称">
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="SubjectPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="SubjectPage.resetSearch()">
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
                            <th>科目</th>
                            <th>描述</th>
                            <th>状态</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="subject-table-body">
                        ${Components.loadingRow(6)}
                    </tbody>
                </table>
            </div>

            <div id="subject-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    SubjectPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        loading: false
    };

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            SubjectPage.search();
        }
    });

    SubjectPage.loadData();
});
