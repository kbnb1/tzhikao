const ExamPaperPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        subjectId: '',
        subjects: [],
        loading: false
    },

    async loadSubjects() {
        try {
            const res = await API.get('/subjects/all');
            this.state.subjects = res.data || [];
            const select = document.getElementById('filter-subject');
            if (select && this.state.subjects.length > 0) {
                select.innerHTML = '<option value="">全部科目</option>' + 
                    this.state.subjects.map(s => `<option value="${s.id}">${Utils.escapeHtml(s.name)}</option>`).join('');
            }
        } catch (e) {
            console.error('Load subjects error:', e);
        }
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
            if (this.state.subjectId) params.subject_id = this.state.subjectId;

            const query = Utils.buildQueryString(params);
            const res = await API.get(`/exam-papers?${query}`);
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
        const tbody = document.getElementById('paper-table-body');
        const paginationContainer = document.getElementById('paper-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(7);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="7">${Components.emptyState('暂无试卷数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => {
            const subject = this.state.subjects.find(s => s.id == item.subject_id);
            return `
            <tr class="table-row">
                <td>${item.id}</td>
                <td>
                    <div>
                        <p class="font-medium text-gray-800">${Utils.escapeHtml(item.title || '')}</p>
                        <p class="text-xs text-gray-400">${subject ? Utils.escapeHtml(subject.name) : '-'}</p>
                    </div>
                </td>
                <td>${item.question_count || 0}</td>
                <td>${item.total_score || 0}分</td>
                <td>${item.duration || 0}分钟</td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="ExamPaperPage.viewQuestions(${item.id})" title="题目管理">
                            <i class="fas fa-list"></i>
                        </button>
                        <button class="btn btn-sm btn-secondary" onclick="ExamPaperPage.edit(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="ExamPaperPage.delete(${item.id})">
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

    search() {
        this.state.keyword = document.getElementById('search-keyword').value.trim();
        this.state.subjectId = document.getElementById('filter-subject').value;
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        document.getElementById('filter-subject').value = '';
        this.state.keyword = '';
        this.state.subjectId = '';
        this.state.page = 1;
        this.loadData();
    },

    add() {
        this.showModal();
    },

    async edit(id) {
        try {
            const res = await API.get(`/exam-papers/${id}`);
            this.showModal(res.data);
        } catch (error) {
            Toast.error(error.message || '获取信息失败');
        }
    },

    showModal(data = null) {
        const isEdit = !!data;
        const subjectOptions = this.state.subjects.map(s => 
            `<option value="${s.id}" ${data?.subject_id == s.id ? 'selected' : ''}>${Utils.escapeHtml(s.name)}</option>`
        ).join('');

        const form = document.createElement('form');
        form.className = 'space-y-4';
        form.innerHTML = `
            <div>
                <label class="form-label">试卷标题 <span class="text-red-500">*</span></label>
                <input type="text" name="title" class="input-field" 
                       value="${data?.title || ''}" placeholder="请输入试卷标题">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">科目 <span class="text-red-500">*</span></label>
                    <select name="subject_id" class="select-field">
                        <option value="">请选择科目</option>
                        ${subjectOptions}
                    </select>
                </div>
                <div>
                    <label class="form-label">考试时长（分钟）</label>
                    <input type="number" name="duration" class="input-field" 
                           value="${data?.duration || 60}" placeholder="考试时长">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">总分</label>
                    <input type="number" name="total_score" class="input-field" 
                           value="${data?.total_score || 100}" placeholder="试卷总分">
                </div>
                <div>
                    <label class="form-label">及格分</label>
                    <input type="number" name="pass_score" class="input-field" 
                           value="${data?.pass_score || 60}" placeholder="及格分数">
                </div>
            </div>
            <div>
                <label class="form-label">试卷描述</label>
                <textarea name="description" class="textarea-field" rows="3"
                          placeholder="请输入试卷描述">${data?.description || ''}</textarea>
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
            title: isEdit ? '编辑试卷' : '新增试卷',
            content: form,
            width: '550px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!data.title) {
                    Toast.error('请输入试卷标题');
                    return false;
                }
                if (!data.subject_id) {
                    Toast.error('请选择科目');
                    return false;
                }

                try {
                    if (isEdit) {
                        await API.put(`/exam-papers/${data.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/exam-papers', data);
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

    async viewQuestions(paperId) {
        const paper = this.state.list.find(p => p.id === paperId);
        const content = document.createElement('div');
        content.className = 'space-y-4';
        content.innerHTML = `
            <div class="flex items-center justify-between">
                <h4 class="font-semibold text-gray-800">${paper?.title || ''} - 题目列表</h4>
                <button class="btn btn-primary btn-sm">
                    <i class="fas fa-plus mr-1"></i>
                    添加题目
                </button>
            </div>
            <div id="paper-questions-loading">
                <div class="text-center py-8">
                    <div class="loading-spinner mx-auto mb-2"></div>
                    <p class="text-gray-500 text-sm">加载中...</p>
                </div>
            </div>
            <div id="paper-questions-list"></div>
        `;

        Modal.show({
            title: '试卷题目管理',
            content: content,
            width: '700px',
            confirmText: '关闭',
            cancelText: '',
            onConfirm: () => true
        });

        try {
            const res = await API.get(`/exam-papers/${paperId}/questions`);
            const questions = res.data.list || res.data || [];
            const listEl = content.querySelector('#paper-questions-list');
            const loadingEl = content.querySelector('#paper-questions-loading');
            
            loadingEl.style.display = 'none';
            
            if (questions.length === 0) {
                listEl.innerHTML = Components.emptyState('暂无题目');
            } else {
                listEl.innerHTML = `
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        ${questions.map((q, index) => `
                            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                                <span class="w-8 h-8 bg-primary-100 text-primary-600 rounded-full flex items-center justify-center text-sm font-medium">
                                    ${index + 1}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-800 truncate">${Utils.escapeHtml(q.content || '')}</p>
                                    <p class="text-xs text-gray-400">
                                        ${q.type == 1 ? '单选' : q.type == 2 ? '多选' : q.type == 3 ? '判断' : q.type == 4 ? '填空' : '简答'} 
                                        · ${q.score || 0}分
                                    </p>
                                </div>
                                <button class="text-red-500 hover:text-red-600 p-1">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        `).join('')}
                    </div>
                `;
            }
        } catch (error) {
            const loadingEl = content.querySelector('#paper-questions-loading');
            if (loadingEl) {
                loadingEl.innerHTML = `<p class="text-red-500 text-center py-8">加载失败：${error.message}</p>`;
            }
        }
    },

    delete(id) {
        Modal.confirm('确定要删除该试卷吗？', async () => {
            try {
                await API.delete(`/exam-papers/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('exam-papers', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">试卷管理</h1>
            <button class="btn btn-primary" onclick="ExamPaperPage.add()">
                <i class="fas fa-plus mr-2"></i>
                新增试卷
            </button>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索试卷标题">
                </div>
                <div class="w-40">
                    <select id="filter-subject" class="select-field">
                        <option value="">全部科目</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="ExamPaperPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="ExamPaperPage.resetSearch()">
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
                            <th>试卷</th>
                            <th>题目数</th>
                            <th>总分</th>
                            <th>时长</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="paper-table-body">
                        ${Components.loadingRow(7)}
                    </tbody>
                </table>
            </div>

            <div id="paper-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    ExamPaperPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        subjectId: '',
        subjects: [],
        loading: false
    };

    await ExamPaperPage.loadSubjects();

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            ExamPaperPage.search();
        }
    });

    ExamPaperPage.loadData();
});
