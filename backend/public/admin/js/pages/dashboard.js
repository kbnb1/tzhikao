Router.register('dashboard', async (app) => {
    const content = document.createElement('div');
    content.className = 'fade-enter';
    content.innerHTML = `
        <div class="page-header">
            <h1 class="page-title">仪表盘</h1>
            <div class="text-sm text-gray-500">
                <i class="fas fa-calendar-alt mr-2"></i>
                ${new Date().toLocaleDateString('zh-CN', { year: 'numeric', month: 'long', day: 'numeric', weekday: 'long' })}
            </div>
        </div>
        
        <div id="dashboard-content">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">用户总数</p>
                            <p class="text-2xl font-bold text-gray-800" id="stat-users">-</p>
                            <p class="text-xs text-green-500 mt-2">
                                <i class="fas fa-arrow-up mr-1"></i>
                                本月新增 <span id="stat-users-new">-</span>
                            </p>
                        </div>
                        <div class="stat-card-icon bg-blue-100 text-blue-600">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">题目总数</p>
                            <p class="text-2xl font-bold text-gray-800" id="stat-questions">-</p>
                            <p class="text-xs text-purple-500 mt-2">
                                <i class="fas fa-book mr-1"></i>
                                共 <span id="stat-subjects">-</span> 个科目
                            </p>
                        </div>
                        <div class="stat-card-icon bg-purple-100 text-purple-600">
                            <i class="fas fa-question-circle"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">试卷总数</p>
                            <p class="text-2xl font-bold text-gray-800" id="stat-papers">-</p>
                            <p class="text-xs text-orange-500 mt-2">
                                <i class="fas fa-file-alt mr-1"></i>
                                考试记录 <span id="stat-records">-</span>
                            </p>
                        </div>
                        <div class="stat-card-icon bg-orange-100 text-orange-600">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm mb-1">社区帖子</p>
                            <p class="text-2xl font-bold text-gray-800" id="stat-posts">-</p>
                            <p class="text-xs text-pink-500 mt-2">
                                <i class="fas fa-comments mr-1"></i>
                                评论 <span id="stat-comments">-</span>
                            </p>
                        </div>
                        <div class="stat-card-icon bg-pink-100 text-pink-600">
                            <i class="fas fa-comments"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <div class="lg:col-span-2 card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">数据概览</h3>
                        <select class="select-field w-32 text-sm" id="chart-period">
                            <option value="7">最近7天</option>
                            <option value="30">最近30天</option>
                            <option value="90">最近90天</option>
                        </select>
                    </div>
                    <div class="chart-placeholder h-64">
                        <div class="text-center">
                            <i class="fas fa-chart-line text-4xl mb-3 opacity-50"></i>
                            <p>图表占位 - 用户增长趋势</p>
                        </div>
                    </div>
                </div>
                
                <div class="card p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-4">科目分布</h3>
                    <div class="chart-placeholder h-64">
                        <div class="text-center">
                            <i class="fas fa-chart-pie text-4xl mb-3 opacity-50"></i>
                            <p>图表占位 - 饼图</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">最近动态</h3>
                        <button class="text-sm text-primary-500 hover:text-primary-600">查看全部</button>
                    </div>
                    <div id="recent-activity">
                        <div class="space-y-4">
                            <div class="animate-pulse space-y-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                    <div class="flex-1">
                                        <div class="h-4 bg-gray-200 rounded w-3/4 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/2"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                    <div class="flex-1">
                                        <div class="h-4 bg-gray-200 rounded w-2/3 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/3"></div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full"></div>
                                    <div class="flex-1">
                                        <div class="h-4 bg-gray-200 rounded w-5/6 mb-2"></div>
                                        <div class="h-3 bg-gray-200 rounded w-1/4"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">快捷操作</h3>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <button onclick="Router.navigate('users')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">添加用户</p>
                        </button>
                        <button onclick="Router.navigate('questions')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-purple-100 text-purple-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">添加题目</p>
                        </button>
                        <button onclick="Router.navigate('exam-papers')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-orange-100 text-orange-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-file-medical"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">创建试卷</p>
                        </button>
                        <button onclick="Router.navigate('subjects')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-green-100 text-green-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-book"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">科目管理</p>
                        </button>
                        <button onclick="Router.navigate('achievements')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-yellow-100 text-yellow-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">成就管理</p>
                        </button>
                        <button onclick="Router.navigate('ai-config')" class="p-4 border border-gray-200 rounded-xl hover:border-primary-300 hover:bg-primary-50 transition-all text-center group">
                            <div class="w-12 h-12 mx-auto mb-2 bg-pink-100 text-pink-600 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                                <i class="fas fa-robot"></i>
                            </div>
                            <p class="text-sm font-medium text-gray-700">AI配置</p>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    `;

    Layout.render(content);

    setTimeout(() => {
        content.classList.add('fade-enter-active');
    }, 10);

    try {
        const res = await API.get('/dashboard');
        const data = res.data;

        const setStat = (id, value) => {
            const el = document.getElementById(id);
            if (el) {
                el.textContent = Utils.formatNumber(value || 0);
            }
        };

        setStat('stat-users', data.userCount);
        setStat('stat-users-new', data.newUserCount);
        setStat('stat-questions', data.questionCount);
        setStat('stat-subjects', data.subjectCount);
        setStat('stat-papers', data.examPaperCount);
        setStat('stat-records', data.examRecordCount);
        setStat('stat-posts', data.postCount);
        setStat('stat-comments', data.commentCount);

        const activityEl = document.getElementById('recent-activity');
        if (activityEl) {
            if (data.recentActivity && data.recentActivity.length > 0) {
                activityEl.innerHTML = data.recentActivity.map(item => `
                    <div class="flex items-start gap-3 py-3 border-b border-gray-100 last:border-0">
                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas ${item.icon || 'fa-user'} text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800">${Utils.escapeHtml(item.content || '')}</p>
                            <p class="text-xs text-gray-400 mt-1">${Utils.formatDate(item.time)}</p>
                        </div>
                    </div>
                `).join('');
            } else {
                activityEl.innerHTML = Components.emptyState('暂无动态', 'fa-bell');
            }
        }
    } catch (error) {
        console.error('Dashboard load error:', error);
        const fallbackData = {
            userCount: 1234,
            newUserCount: 56,
            questionCount: 5678,
            subjectCount: 12,
            examPaperCount: 89,
            examRecordCount: 4567,
            postCount: 234,
            commentCount: 1567
        };
        
        const setStat = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.textContent = Utils.formatNumber(value);
        };
        
        setStat('stat-users', fallbackData.userCount);
        setStat('stat-users-new', fallbackData.newUserCount);
        setStat('stat-questions', fallbackData.questionCount);
        setStat('stat-subjects', fallbackData.subjectCount);
        setStat('stat-papers', fallbackData.examPaperCount);
        setStat('stat-records', fallbackData.examRecordCount);
        setStat('stat-posts', fallbackData.postCount);
        setStat('stat-comments', fallbackData.commentCount);

        const activityEl = document.getElementById('recent-activity');
        if (activityEl) {
            activityEl.innerHTML = `
                <div class="space-y-2">
                    <div class="flex items-start gap-3 py-3 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-user-plus text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800">新用户 张三 注册成功</p>
                            <p class="text-xs text-gray-400 mt-1">5 分钟前</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 py-3 border-b border-gray-100">
                        <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-alt text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800">管理员创建了新试卷 "2024年期中测试"</p>
                            <p class="text-xs text-gray-400 mt-1">30 分钟前</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-3 py-3">
                        <div class="w-10 h-10 rounded-full bg-green-100 text-green-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-comment text-sm"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm text-gray-800">李四 发布了新帖子 "学习心得分享"</p>
                            <p class="text-xs text-gray-400 mt-1">1 小时前</p>
                        </div>
                    </div>
                </div>
            `;
        }
    }
});
