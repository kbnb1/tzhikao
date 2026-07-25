const QuestionPage = {
    state: {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        subjectId: '',
        type: '',
        difficulty: '',
        subjects: [],
        loading: false,
        selectedIds: []
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
            if (this.state.type) params.type = this.state.type;
            if (this.state.difficulty) params.difficulty = this.state.difficulty;

            const query = Utils.buildQueryString(params);
            const res = await API.get(`/questions?${query}`);
            this.state.list = res.data.list || [];
            this.state.total = res.data.total || 0;
        } catch (error) {
            Toast.error(error.message || '加载失败');
            this.state.list = [];
            this.state.total = 0;
        }

        this.state.loading = false;
        this.state.selectedIds = [];
        this.renderTable();
    },

    getTypeText(type) {
        const types = {
            1: '单选题',
            2: '多选题',
            3: '判断题',
            4: '填空题',
            5: '简答题'
        };
        return types[type] || '未知';
    },

    getDifficultyText(level) {
        const levels = {
            1: { text: '简单', class: 'status-active' },
            2: { text: '中等', class: 'status-pending' },
            3: { text: '困难', class: 'status-inactive' }
        };
        return levels[level] || { text: '未知', class: '' };
    },

    renderTable() {
        const tbody = document.getElementById('question-table-body');
        const paginationContainer = document.getElementById('question-pagination');
        
        if (!tbody) return;

        if (this.state.loading) {
            tbody.innerHTML = Components.loadingRow(8);
            return;
        }

        if (this.state.list.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8">${Components.emptyState('暂无题目数据')}</td></tr>`;
            if (paginationContainer) paginationContainer.innerHTML = '';
            return;
        }

        tbody.innerHTML = this.state.list.map(item => {
            const diff = this.getDifficultyText(item.difficulty);
            const subject = this.state.subjects.find(s => s.id == item.subject_id);
            return `
            <tr class="table-row">
                <td><input type="checkbox" class="checkbox-custom" 
                    ${this.state.selectedIds.includes(item.id) ? 'checked' : ''}
                    onchange="QuestionPage.toggleSelect(${item.id})"></td>
                <td>${item.id}</td>
                <td>
                    <div class="max-w-md truncate" title="${Utils.escapeHtml(item.content || '')}">
                        ${Utils.escapeHtml(item.content || '').substring(0, 60)}${item.content?.length > 60 ? '...' : ''}
                    </div>
                </td>
                <td><span class="px-2 py-1 bg-blue-50 text-blue-600 text-xs rounded">${this.getTypeText(item.type)}</span></td>
                <td>${subject ? Utils.escapeHtml(subject.name) : '-'}</td>
                <td><span class="status-badge ${diff.class}">${diff.text}</span></td>
                <td>${Utils.formatDate(item.create_time)}</td>
                <td>
                    <div class="flex items-center gap-2">
                        <button class="btn btn-sm btn-secondary" onclick="QuestionPage.edit(${item.id})">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="QuestionPage.delete(${item.id})">
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

        this.updateSelectAll();
    },

    toggleSelect(id) {
        const index = this.state.selectedIds.indexOf(id);
        if (index > -1) {
            this.state.selectedIds.splice(index, 1);
        } else {
            this.state.selectedIds.push(id);
        }
        this.updateSelectAll();
    },

    toggleSelectAll() {
        const checkbox = document.getElementById('select-all');
        if (checkbox.checked) {
            this.state.selectedIds = this.state.list.map(item => item.id);
        } else {
            this.state.selectedIds = [];
        }
        this.renderTable();
    },

    updateSelectAll() {
        const checkbox = document.getElementById('select-all');
        if (checkbox && this.state.list.length > 0) {
            checkbox.checked = this.state.selectedIds.length === this.state.list.length;
            checkbox.indeterminate = this.state.selectedIds.length > 0 && this.state.selectedIds.length < this.state.list.length;
        }
    },

    batchDelete() {
        if (this.state.selectedIds.length === 0) {
            Toast.warning('请先选择要删除的题目');
            return;
        }
        Modal.confirm(`确定要删除选中的 ${this.state.selectedIds.length} 道题目吗？`, async () => {
            try {
                await API.post('/questions/batch-delete', { ids: this.state.selectedIds });
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    },

    search() {
        this.state.keyword = document.getElementById('search-keyword').value.trim();
        this.state.subjectId = document.getElementById('filter-subject').value;
        this.state.type = document.getElementById('filter-type').value;
        this.state.difficulty = document.getElementById('filter-difficulty').value;
        this.state.page = 1;
        this.loadData();
    },

    resetSearch() {
        document.getElementById('search-keyword').value = '';
        document.getElementById('filter-subject').value = '';
        document.getElementById('filter-type').value = '';
        document.getElementById('filter-difficulty').value = '';
        this.state.keyword = '';
        this.state.subjectId = '';
        this.state.type = '';
        this.state.difficulty = '';
        this.state.page = 1;
        this.loadData();
    },

    add() {
        this.showModal();
    },

    async edit(id) {
        try {
            const res = await API.get(`/questions/${id}`);
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
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">科目 <span class="text-red-500">*</span></label>
                    <select name="subject_id" class="select-field">
                        <option value="">请选择科目</option>
                        ${subjectOptions}
                    </select>
                </div>
                <div>
                    <label class="form-label">题目类型 <span class="text-red-500">*</span></label>
                    <select name="type" class="select-field">
                        <option value="1" ${data?.type == 1 ? 'selected' : ''}>单选题</option>
                        <option value="2" ${data?.type == 2 ? 'selected' : ''}>多选题</option>
                        <option value="3" ${data?.type == 3 ? 'selected' : ''}>判断题</option>
                        <option value="4" ${data?.type == 4 ? 'selected' : ''}>填空题</option>
                        <option value="5" ${data?.type == 5 ? 'selected' : ''}>简答题</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="form-label">题目内容 <span class="text-red-500">*</span></label>
                <textarea name="content" class="textarea-field" rows="4"
                          placeholder="请输入题目内容">${data?.content || ''}</textarea>
            </div>
            <div>
                <label class="form-label">选项（JSON格式或每行一个）</label>
                <textarea name="options" class="textarea-field" rows="4"
                          placeholder="选项A&#10;选项B&#10;选项C&#10;选项D">${data?.options || ''}</textarea>
            </div>
            <div>
                <label class="form-label">正确答案 <span class="text-red-500">*</span></label>
                <input type="text" name="answer" class="input-field" 
                       value="${data?.answer || ''}" placeholder="如：A、B、正确、错误等">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">难度</label>
                    <select name="difficulty" class="select-field">
                        <option value="1" ${data?.difficulty == 1 ? 'selected' : ''}>简单</option>
                        <option value="2" ${data?.difficulty == 2 ? 'selected' : ''}>中等</option>
                        <option value="3" ${data?.difficulty == 3 ? 'selected' : ''}>困难</option>
                    </select>
                </div>
                <div>
                    <label class="form-label">分数</label>
                    <input type="number" name="score" class="input-field" 
                           value="${data?.score || 1}" placeholder="题目分值">
                </div>
            </div>
            <div>
                <label class="form-label">解析</label>
                <textarea name="analysis" class="textarea-field" rows="2"
                          placeholder="答案解析">${data?.analysis || ''}</textarea>
            </div>
        `;

        Modal.show({
            title: isEdit ? '编辑题目' : '新增题目',
            content: form,
            width: '600px',
            confirmText: isEdit ? '保存' : '创建',
            onConfirm: async (modal) => {
                const formData = new FormData(form);
                const data = Object.fromEntries(formData.entries());
                
                if (!data.subject_id) {
                    Toast.error('请选择科目');
                    return false;
                }
                if (!data.type) {
                    Toast.error('请选择题目类型');
                    return false;
                }
                if (!data.content) {
                    Toast.error('请输入题目内容');
                    return false;
                }
                if (!data.answer) {
                    Toast.error('请输入正确答案');
                    return false;
                }

                try {
                    if (isEdit) {
                        await API.put(`/questions/${data.id}`, data);
                        Toast.success('编辑成功');
                    } else {
                        await API.post('/questions', data);
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
        Modal.confirm('确定要删除该题目吗？', async () => {
            try {
                await API.delete(`/questions/${id}`);
                Toast.success('删除成功');
                this.loadData();
            } catch (error) {
                Toast.error(error.message || '删除失败');
            }
        });
    }
};

Router.register('questions', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">题目管理</h1>
            <div class="flex gap-2">
                <button class="btn btn-danger" onclick="QuestionPage.batchDelete()">
                    <i class="fas fa-trash mr-2"></i>
                    批量删除
                </button>
                <button class="btn btn-primary" onclick="QuestionPage.add()">
                    <i class="fas fa-plus mr-2"></i>
                    新增题目
                </button>
            </div>
        </div>

        <div class="card p-6">
            <div class="flex flex-wrap gap-4 mb-6">
                <div class="flex-1 min-w-[200px] max-w-xs">
                    <input type="text" id="search-keyword" class="input-field" 
                           placeholder="搜索题目内容">
                </div>
                <div class="w-36">
                    <select id="filter-subject" class="select-field">
                        <option value="">全部科目</option>
                    </select>
                </div>
                <div class="w-32">
                    <select id="filter-type" class="select-field">
                        <option value="">全部类型</option>
                        <option value="1">单选题</option>
                        <option value="2">多选题</option>
                        <option value="3">判断题</option>
                        <option value="4">填空题</option>
                        <option value="5">简答题</option>
                    </select>
                </div>
                <div class="w-32">
                    <select id="filter-difficulty" class="select-field">
                        <option value="">全部难度</option>
                        <option value="1">简单</option>
                        <option value="2">中等</option>
                        <option value="3">困难</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button class="btn btn-primary" onclick="QuestionPage.search()">
                        <i class="fas fa-search mr-2"></i>
                        搜索
                    </button>
                    <button class="btn btn-secondary" onclick="QuestionPage.resetSearch()">
                        <i class="fas fa-redo mr-2"></i>
                        重置
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th style="width: 40px;"><input type="checkbox" id="select-all" class="checkbox-custom" onchange="QuestionPage.toggleSelectAll()"></th>
                            <th>ID</th>
                            <th>题目内容</th>
                            <th>类型</th>
                            <th>科目</th>
                            <th>难度</th>
                            <th>创建时间</th>
                            <th>操作</th>
                        </tr>
                    </thead>
                    <tbody id="question-table-body">
                        ${Components.loadingRow(8)}
                    </tbody>
                </table>
            </div>

            <div id="question-pagination"></div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    QuestionPage.state = {
        list: [],
        total: 0,
        page: 1,
        pageSize: 10,
        keyword: '',
        subjectId: '',
        type: '',
        difficulty: '',
        subjects: [],
        loading: false,
        selectedIds: []
    };

    await QuestionPage.loadSubjects();

    const searchInput = document.getElementById('search-keyword');
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            QuestionPage.search();
        }
    });

    QuestionPage.loadData();
});
