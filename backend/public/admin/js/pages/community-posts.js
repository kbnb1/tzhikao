const CommunityPostPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        status: '',
        loading: false,
        selectedIds: []
    },

    getStatusText(status) {
        const map = {
            0: { text: '待审核', class: 'status-pending' },
            1: { text: '已通过', class: 'status-active' },
            2: { text: '已拒绝', class: 'status-inactive' }
        };
        return map[status] || { text: '未知', class: '' };
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
            const res = await API.get(`/community/posts?${query}`);
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
        const tbody = document.getElementById('post-table-body');
        const paginationContainer = document.getElementById('post-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(7);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7">${Components.emptyState('暂无帖子数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => {
            const status = this.getStatusText(item.status);
            return `
            <tr class="table-row">
                <td><input type="checkbox" class="checkbox-custom" 
                    ${this.state.selectedIds.includes(item.id) ? 'checked' : ''}
                    onchange="CommunityPostPage.toggleSelect(${item.id})"></td>
                <td>${item.id}</td>
                <td>
                    <div class="max-w-md">
                        <p class="font-medium text-gray-800 truncate">${Utils.escapeHtml(item.title || item.content?.substring(0, 50) || '')}</p>
                        <p class="text-xs text-gray-400 truncate mt-1">${Utils.escapeHtml(item.content || '').substring(0, 80)}...</p>
                    </div>
                </td>
                <td>${Utils.escapeHtml(item.user_name || item.username || '-')}</td>
                <td>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="far fa-eye"></i> ${item.view_count || 0}
                        <i class="far fa-thumbs-up ml-2"></i> ${item.like_count || 0}
                        <i class="far fa-comment ml-2"></i> ${item.comment_count || 0}
                    </div>
                </td>
                <td><span class="status-badge ${status.class}">${status.text}</span></td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="CommunityPostPage.view(${item.id})">
                            <i class="fas fa-eye"></i>
                        </button>
                        ${item.status == 0 ? `
                            <button class="btn btn-sm btn-success" onclick="CommunityPostPage.review(${item.id}, 1)">
                                <i class="fas fa-check"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="CommunityPostPage.review(${item.id}, 2)">
                                <i class="fas fa-times"></i>
                            </button>
                        ` : ''}
                        <button class="btn btn-sm btn-danger" onclick="CommunityPostPage.delete(${item.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </td>
            </tr>
        `;
        }).join('');

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

    toggleSelect(id) {
        const index = this.state.selectedIds.indexOf(id);
        if (index > -1) {
            this.state.selectedIds.splice(index, 1);
        } else {
            this.state.selectedIds.push(id);
        }
    },

    search() {
        this.state.keyword = document.getElementById('search-keyword').value.trim();
        this.state.status = document.getElementById('filter-status').value;
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        document.getElementById('filter-status').value = '';
        this.state.keyword = '';
        this.state.status = '';
        this.state.page = 1;
        this.loadData();
    },

    async view(id) {
        try {
            const res = await API.get(`/community/posts/${id}`);
            const post = res.data;
            
            const content = document.createElement('div');
            content.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h4 class="font-semibold text-gray-800 text-lg">${Utils.escapeHtml(post.title || '')}</h4>
                        <div class="flex items-center gap-4 mt-2 text-sm text-gray-500">
                            <span>发布者：${Utils.escapeHtml(post.user_name || post.username || '未知')}</span>
                            <span>发布时间：${Utils.formatDate(post.create_time)}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 text-sm">
                        <span class="text-gray-500"><i class="far fa-eye mr-1"></i> ${post.view_count || 0} 浏览</span>
                        <span class="text-gray-500"><i class="far fa-thumbs-up mr-1"></i> ${post.like_count || 0} 点赞</span>
                        <span class="text-gray-500"><i class="far fa-comment mr-1"></i> ${post.comment_count || 0} 评论</span>
                    </div>
                    <div class="pt-4 border-t border-gray-100">
                        <p class="text-gray-700 whitespace-pre-wrap">${Utils.escapeHtml(post.content || '')}</p>
                    </div>
                </div>
            `;

            Modal.show({
                title: '帖子详情',
                content: content,
                width: '600px',
                confirmText: '关闭',
                cancelText: '',
                onConfirm: () => true
            });
        } catch (error) {
            Toast.error(error.message || '获取详情失败');
        }
    },

    review(id, status) {
        const msg = status == 1 ? '确定要通过该帖子的审核吗？' : '确定要拒绝该帖子吗？';
        Modal.confirm(msg, async () => {
            try {
                await API.put(`/community/posts/${id}/review`, { status });
                Toast.success('操作成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '操作失败');
            }
        });
    },

    batchDelete() {
        if (this.state.selectedIds.length === 0) {
            Toast.warning('请先选择要删除的帖子');
            return;
        }
        Modal.confirm(`确定要删除选中的 ${this.state.selectedIds.length} 条帖子吗？`, async () => {
            try {
                await API.post('/community/posts/batch-delete', { ids: this.state.selectedIds });
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    },

    delete(id) {
        Modal.confirm('确定要删除该帖子吗？', async () => {
            try {
                await API.delete(`/community/posts/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('community-posts', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">帖子管理</h1>
            <button class="btn btn-danger" onclick="CommunityPostPage.batchDelete()">
                <i class="fas fa-trash mr-2"></i>
                批量删除
            </button>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索标题/内容">
                </div>
                <div class="w-36">
                    <select id="filter-status" class="select-field">
                        <option value="">全部状态</option>
                        <option value="0">待审核</option>
                        <option value="1">已通过</option>
                        <option value="2">已拒绝</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="CommunityPostPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="CommunityPostPage.resetSearch()">
                        <i class="fas fa-redo mr-2"></i>
                        重置
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" class="checkbox-custom"></th>
                            <th>ID</th>
                            <th>帖子内容</th>
                            <th>发布者</th>
                            <th>数据</th>
                            <th>状态</th>
                            <th>发布时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="post-table-body">
                        ${Components.loadingRow(8)}
                    </tbody>
                </table>
            </div>

            <div id="post-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    CommunityPostPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        status: '',
        loading: false,
        selectedIds: []
    };

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            CommunityPostPage.search();
        }
    });

    CommunityPostPage.loadData();
});
