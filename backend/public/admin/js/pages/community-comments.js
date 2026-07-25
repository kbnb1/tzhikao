const CommunityCommentPage = {
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
            const res = await API.get(`/community/comments?${query}`);
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
        const tbody = document.getElementById('comment-table-body');
        const paginationContainer = document.getElementById('comment-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(6);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="6">${Components.emptyState('暂无评论数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => `
            <tr class="table-row">
                <td>${item.id}</td>
                <td>
                    <div class="max-w-xs">
                        <p class="text-gray-800 truncate">${Utils.escapeHtml(item.content || '')}</p>
                    </div>
                </td>
                <td>${Utils.escapeHtml(item.user_name || item.username || '-')}</td>
                <td>
                    <span class="text-primary-500 text-sm cursor-pointer hover:underline" onclick="CommunityCommentPage.viewPost(${item.post_id})">
                        #${item.post_id || '-'}
                    </span>
                </td>
                <td>
                    <div class="flex items-center gap-2 text-xs text-gray-500">
                        <i class="far fa-thumbs-up"></i> ${item.like_count || 0}
                    </div>
                </td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-danger" onclick="CommunityCommentPage.delete(${item.id})">
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

    viewPost(postId) {
        Toast.info('帖子ID：' + postId);
    },

    delete(id) {
        Modal.confirm('确定要删除该评论吗？', async () => {
            try {
                await API.delete(`/community/comments/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('community-comments', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">评论管理</h1>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索评论内容">
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="CommunityCommentPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="CommunityCommentPage.resetSearch()">
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
                            <th>评论内容</th>
                            <th>用户</th>
                            <th>帖子ID</th>
                            <th>点赞</th>
                            <th>评论时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="comment-table-body">
                        ${Components.loadingRow(7)}
                    </tbody>
                </table>
            </div>

            <div id="comment-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    CommunityCommentPage.state = {
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
            CommunityCommentPage.search();
        }
    });

    CommunityCommentPage.loadData();
});
