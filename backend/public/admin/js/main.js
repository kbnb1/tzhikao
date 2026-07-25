const API_BASE = '/admin/v1';

const AppState = {
    token: localStorage.getItem('admin_token') || '',
    adminInfo: null,
    currentRoute: 'login',
    sidebarCollapsed: false,
    mobileSidebarOpen: false
};

const API = {
    getToken() {
        return AppState.token;
    },

    setToken(token) {
        AppState.token = token;
        localStorage.setItem('admin_token', token);
    },

    clearToken() {
        AppState.token = '';
        localStorage.removeItem('admin_token');
    },

    async request(url, options = {}) {
        const token = this.getToken();
        const headers = {
            'Content-Type': 'application/json',
            ...(token ? { 'Authorization': `Bearer ${token}` } : {}),
            ...(options.headers || {})
        };

        try {
            const response = await fetch(`${API_BASE}${url}`, {
                ...options,
                headers
            });

            const data = await response.json();

            if (response.status === 401 || data.code === 401) {
                this.clearToken();
                Router.navigate('login');
                throw new Error('登录已过期，请重新登录');
            }

            if (data.code !== 0) {
                throw new Error(data.msg || '请求失败');
            }

            return data;
        } catch (error) {
            if (error.message === '登录已过期，请重新登录') {
                Toast.error(error.message);
            }
            throw error;
        }
    },

    get(url) {
        return this.request(url, { method: 'GET' });
    },

    post(url, data) {
        return this.request(url, {
            method: 'POST',
            body: JSON.stringify(data || {})
        });
    },

    put(url, data) {
        return this.request(url, {
            method: 'PUT',
            body: JSON.stringify(data || {})
        });
    },

    delete(url) {
        return this.request(url, { method: 'DELETE' });
    }
};

const Toast = {
    show(message, type = 'info', duration = 3000) {
        const container = document.getElementById('toast-container');
        const toast = document.createElement('div');
        
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500',
            warning: 'bg-yellow-500',
            info: 'bg-blue-500'
        };

        const icons = {
            success: 'fa-check-circle',
            error: 'fa-times-circle',
            warning: 'fa-exclamation-circle',
            info: 'fa-info-circle'
        };

        toast.className = `toast ${colors[type]} text-white px-4 py-3 rounded-lg shadow-lg flex items-center gap-3 min-w-[280px] max-w-md`;
        toast.innerHTML = `
            <i class="fas ${icons[type]}"></i>
            <span class="flex-1">${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            toast.style.transition = 'all 0.3s ease';
            setTimeout(() => toast.remove(), 300);
        }, duration);
    },

    success(message) {
        this.show(message, 'success');
    },

    error(message) {
        this.show(message, 'error');
    },

    warning(message) {
        this.show(message, 'warning');
    },

    info(message) {
        this.show(message, 'info');
    }
};

const Modal = {
    show(options) {
        const { title, content, onConfirm, onCancel, confirmText = '确认', cancelText = '取消', width = '500px' } = options;
        
        const container = document.getElementById('modal-container');
        const modal = document.createElement('div');
        modal.className = 'modal-overlay fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4';
        modal.innerHTML = `
            <div class="modal-content bg-white rounded-xl shadow-2xl w-full" style="max-width: ${width}">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">${title}</h3>
                    <button class="modal-close text-gray-400 hover:text-gray-600 text-xl">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body px-6 py-4 max-h-[70vh] overflow-y-auto">
                    ${typeof content === 'string' ? content : ''}
                </div>
                <div class="flex justify-end gap-3 px-6 py-4 border-t border-gray-200">
                    <button class="btn btn-secondary modal-cancel">${cancelText}</button>
                    <button class="btn btn-primary modal-confirm">${confirmText}</button>
                </div>
            </div>
        `;

        container.appendChild(modal);

        if (typeof content !== 'string' && content instanceof HTMLElement) {
            modal.querySelector('.modal-body').innerHTML = '';
            modal.querySelector('.modal-body').appendChild(content);
        }

        const close = () => {
            modal.style.opacity = '0';
            modal.style.transition = 'opacity 0.2s ease';
            setTimeout(() => modal.remove(), 200);
        };

        modal.querySelector('.modal-close').onclick = () => {
            close();
            onCancel && onCancel();
        };

        modal.querySelector('.modal-cancel').onclick = () => {
            close();
            onCancel && onCancel();
        };

        modal.querySelector('.modal-confirm').onclick = async () => {
            if (onConfirm) {
                const result = onConfirm(modal);
                if (result !== false) {
                    close();
                }
            } else {
                close();
            }
        };

        modal.onclick = (e) => {
            if (e.target === modal) {
                close();
                onCancel && onCancel();
            }
        };

        return { modal, close };
    },

    confirm(message, onConfirm) {
        const { modal, close } = this.show({
            title: '确认操作',
            content: `<p class="text-gray-600">${message}</p>`,
            confirmText: '确定',
            cancelText: '取消',
            onConfirm: () => {
                onConfirm && onConfirm();
            }
        });
        return { modal, close };
    },

    loading(message = '加载中...') {
        const container = document.getElementById('modal-container');
        const modal = document.createElement('div');
        modal.className = 'modal-overlay fixed inset-0 bg-black/30 flex items-center justify-center z-50';
        modal.innerHTML = `
            <div class="bg-white rounded-xl px-8 py-6 flex items-center gap-4">
                <div class="loading-spinner"></div>
                <span class="text-gray-600">${message}</span>
            </div>
        `;
        container.appendChild(modal);
        return {
            close: () => modal.remove()
        };
    }
};

const Router = {
    routes: {},
    currentPage: null,

    register(path, handler) {
        this.routes[path] = handler;
    },

    navigate(path, params = {}) {
        AppState.currentRoute = path;
        window.location.hash = path;
        this.render(path, params);
    },

    async render(path, params = {}) {
        const app = document.getElementById('app');
        
        const token = API.getToken();
        
        if (path !== 'login' && !token) {
            this.navigate('login');
            return;
        }

        if (path === 'login' && token) {
            this.navigate('dashboard');
            return;
        }

        const handler = this.routes[path];
        
        if (handler) {
            try {
                app.innerHTML = '<div class="flex items-center justify-center h-screen"><div class="loading-spinner"></div></div>';
                await handler(app, params);
            } catch (error) {
                console.error('Page render error:', error);
                Toast.error('页面加载失败');
            }
        } else {
            app.innerHTML = `
                <div class="flex flex-col items-center justify-center h-screen">
                    <i class="fas fa-exclamation-triangle text-6xl text-yellow-500 mb-4"></i>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">页面不存在</h2>
                    <p class="text-gray-500 mb-4">您访问的页面不存在或已被删除</p>
                    <button class="btn btn-primary" onclick="Router.navigate('dashboard')">
                        返回首页
                    </button>
                </div>
            `;
        }
    },

    init() {
        window.addEventListener('hashchange', () => {
            const path = window.location.hash.slice(1) || 'login';
            this.render(path);
        });

        const path = window.location.hash.slice(1) || 'login';
        this.render(path);
    }
};

const Utils = {
    formatDate(timestamp) {
        if (!timestamp) return '-';
        const date = new Date(timestamp * 1000);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        const seconds = String(date.getSeconds()).padStart(2, '0');
        return `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
    },

    formatNumber(num) {
        if (num === undefined || num === null) return 0;
        return num.toLocaleString('zh-CN');
    },

    debounce(func, wait = 300) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    },

    buildQueryString(params) {
        return Object.keys(params)
            .filter(key => params[key] !== undefined && params[key] !== '')
            .map(key => `${encodeURIComponent(key)}=${encodeURIComponent(params[key])}`)
            .join('&');
    }
};

const Components = {
    pagination(total, page, pageSize, onChange) {
        const totalPages = Math.ceil(total / pageSize);
        
        const container = document.createElement('div');
        container.className = 'pagination';
        
        const left = document.createElement('div');
        left.className = 'text-sm text-gray-500';
        left.textContent = `共 ${total} 条记录，第 ${page} / ${totalPages} 页`;
        
        const right = document.createElement('div');
        right.className = 'pagination-buttons';

        const createBtn = (text, disabled, active, onClick) => {
            const btn = document.createElement('button');
            btn.className = `pagination-btn ${active ? 'active' : ''}`;
            btn.innerHTML = text;
            btn.disabled = disabled;
            btn.onclick = onClick;
            return btn;
        };

        right.appendChild(createBtn('<i class="fas fa-angle-left"></i>', page <= 1, false, () => {
            if (page > 1) onChange(page - 1);
        }));

        const showPages = 5;
        let startPage = Math.max(1, page - Math.floor(showPages / 2));
        let endPage = Math.min(totalPages, startPage + showPages - 1);
        
        if (endPage - startPage + 1 < showPages) {
            startPage = Math.max(1, endPage - showPages + 1);
        }

        if (startPage > 1) {
            right.appendChild(createBtn('1', false, false, () => onChange(1)));
            if (startPage > 2) {
                const dots = document.createElement('span');
                dots.className = 'pagination-btn';
                dots.style.border = 'none';
                dots.style.background = 'transparent';
                dots.textContent = '...';
                right.appendChild(dots);
            }
        }

        for (let i = startPage; i <= endPage; i++) {
            right.appendChild(createBtn(i, false, i === page, () => onChange(i)));
        }

        if (endPage < totalPages) {
            if (endPage < totalPages - 1) {
                const dots = document.createElement('span');
                dots.className = 'pagination-btn';
                dots.style.border = 'none';
                dots.style.background = 'transparent';
                dots.textContent = '...';
                right.appendChild(dots);
            }
            right.appendChild(createBtn(totalPages, false, false, () => onChange(totalPages)));
        }

        right.appendChild(createBtn('<i class="fas fa-angle-right"></i>', page >= totalPages, false, () => {
            if (page < totalPages) onChange(page + 1);
        }));

        container.appendChild(left);
        container.appendChild(right);

        return container;
    },

    emptyState(message = '暂无数据', icon = 'fa-inbox') {
        return `
            <div class="empty-state">
                <i class="fas ${icon} empty-state-icon"></i>
                <p class="text-base">${message}</p>
            </div>
        `;
    },

    loadingRow(cols, rows = 5) {
        let html = '';
        for (let i = 0; i < rows; i++) {
            html += '<tr>';
            for (let j = 0; j < cols; j++) {
                html += `<td><div class="h-4 bg-gray-200 rounded animate-pulse"></div></td>`;
            }
            html += '</tr>';
        }
        return html;
    }
};

const Layout = {
    menuItems: [
        { key: 'dashboard', icon: 'fa-tachometer-alt', label: '仪表盘' },
        { key: 'users', icon: 'fa-users', label: '用户管理' },
        { key: 'subjects', icon: 'fa-book', label: '科目管理' },
        { key: 'questions', icon: 'fa-question-circle', label: '题目管理' },
        { key: 'exam-papers', icon: 'fa-file-alt', label: '试卷管理' },
        { key: 'ai-config', icon: 'fa-robot', label: 'AI配置管理' },
        {
            key: 'community',
            icon: 'fa-comments',
            label: '社区管理',
            children: [
                { key: 'community-posts', label: '帖子管理' },
                { key: 'community-comments', label: '评论管理' }
            ]
        },
        { key: 'achievements', icon: 'fa-trophy', label: '成就管理' },
        {
            key: 'config',
            icon: 'fa-cog',
            label: '系统配置',
            children: [
                { key: 'page-config', label: '页面配置' },
                { key: 'admin-settings', label: '管理员设置' }
            ]
        }
    ],

    async render(content) {
        if (!AppState.adminInfo) {
            try {
                const res = await API.get('/admin/info');
                AppState.adminInfo = res.data;
            } catch (e) {
                console.error('Failed to get admin info:', e);
            }
        }

        const app = document.getElementById('app');
        app.innerHTML = '';

        const layout = document.createElement('div');
        layout.className = 'flex min-h-screen';

        const sidebar = this.createSidebar();
        const mainContent = document.createElement('div');
        mainContent.className = 'flex-1 flex flex-col main-content content-transition';
        
        const header = this.createHeader();
        const contentArea = document.createElement('div');
        contentArea.className = 'flex-1 p-6 overflow-auto';
        contentArea.id = 'page-content';
        
        if (content instanceof HTMLElement) {
            contentArea.appendChild(content);
        } else if (typeof content === 'string') {
            contentArea.innerHTML = content;
        }

        const overlay = document.createElement('div');
        overlay.className = 'mobile-overlay';
        overlay.id = 'mobile-overlay';
        overlay.onclick = () => Layout.toggleMobileSidebar(false);

        mainContent.appendChild(header);
        mainContent.appendChild(contentArea);
        layout.appendChild(sidebar);
        layout.appendChild(overlay);
        layout.appendChild(mainContent);

        app.appendChild(layout);
    },

    createSidebar() {
        const sidebar = document.createElement('div');
        sidebar.className = 'sidebar sidebar-transition bg-sidebar-bg w-64 flex flex-col';
        sidebar.id = 'sidebar';

        sidebar.innerHTML = `
            <div class="h-16 flex items-center px-6 border-b border-sidebar-hover">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-brain text-white"></i>
                    </div>
                    <span class="text-white font-bold text-lg">智考AI管理</span>
                </div>
            </div>
            <nav class="flex-1 py-4 overflow-y-auto" id="sidebar-menu">
                ${this.renderMenu()}
            </nav>
        `;

        return sidebar;
    },

    renderMenu() {
        let html = '';
        const currentRoute = AppState.currentRoute;

        this.menuItems.forEach(item => {
            const isActive = currentRoute === item.key || 
                (item.children && item.children.some(child => child.key === currentRoute));
            
            if (item.children) {
                html += `
                    <div class="sidebar-menu-group">
                        <div class="sidebar-menu-item ${isActive ? '' : ''}" onclick="Layout.toggleSubmenu(this)">
                            <i class="fas ${item.icon} w-5 mr-3"></i>
                            <span class="flex-1">${item.label}</span>
                            <i class="fas fa-chevron-down text-xs transition-transform ${isActive ? 'rotate-180' : ''}"></i>
                        </div>
                        <div class="sidebar-submenu ${isActive ? 'open' : ''}">
                            ${item.children.map(child => `
                                <div class="sidebar-menu-item sidebar-submenu-item ${currentRoute === child.key ? 'active' : ''}" 
                                     onclick="Router.navigate('${child.key}')">
                                    ${child.label}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                html += `
                    <div class="sidebar-menu-item ${currentRoute === item.key ? 'active' : ''}" 
                         onclick="Router.navigate('${item.key}')">
                        <i class="fas ${item.icon} w-5 mr-3"></i>
                        <span>${item.label}</span>
                    </div>
                `;
            }
        });

        return html;
    },

    toggleSubmenu(element) {
        const submenu = element.nextElementSibling;
        const icon = element.querySelector('.fa-chevron-down');
        
        if (submenu.classList.contains('open')) {
            submenu.classList.remove('open');
            icon.classList.remove('rotate-180');
        } else {
            submenu.classList.add('open');
            icon.classList.add('rotate-180');
        }
    },

    createHeader() {
        const header = document.createElement('div');
        header.className = 'h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6';
        
        const adminInfo = AppState.adminInfo || {};
        
        header.innerHTML = `
            <div class="flex items-center gap-4">
                <button class="md:hidden text-gray-500 hover:text-gray-700" onclick="Layout.toggleMobileSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <button class="hidden md:block text-gray-500 hover:text-gray-700" onclick="Layout.toggleSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <button class="flex items-center gap-3 hover:bg-gray-50 px-3 py-2 rounded-lg transition-colors" onclick="Layout.toggleUserMenu()">
                        <div class="w-8 h-8 bg-primary-500 rounded-full flex items-center justify-center text-white font-medium">
                            ${(adminInfo.nickname || adminInfo.username || 'A').charAt(0).toUpperCase()}
                        </div>
                        <span class="text-gray-700 text-sm hidden sm:block">${adminInfo.nickname || adminInfo.username || '管理员'}</span>
                        <i class="fas fa-chevron-down text-xs text-gray-400"></i>
                    </button>
                    <div id="user-dropdown" class="hidden absolute right-0 top-full mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-2 z-50">
                        <div class="px-4 py-3 border-b border-gray-100">
                            <p class="font-medium text-gray-800">${adminInfo.nickname || adminInfo.username || '管理员'}</p>
                            <p class="text-sm text-gray-500">${adminInfo.username || ''}</p>
                        </div>
                        <div class="py-1">
                            <button class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2" onclick="Router.navigate('admin-settings')">
                                <i class="fas fa-cog w-4"></i>
                                设置
                            </button>
                            <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2" onclick="Layout.logout()">
                                <i class="fas fa-sign-out-alt w-4"></i>
                                退出登录
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        return header;
    },

    toggleSidebar() {
        AppState.sidebarCollapsed = !AppState.sidebarCollapsed;
        const sidebar = document.getElementById('sidebar');
        const mainContent = document.querySelector('.main-content');
        
        if (AppState.sidebarCollapsed) {
            sidebar.style.width = '0';
            sidebar.style.overflow = 'hidden';
            mainContent.style.marginLeft = '0';
        } else {
            sidebar.style.width = '256px';
            sidebar.style.overflow = '';
            mainContent.style.marginLeft = '0';
        }
    },

    toggleMobileSidebar(open) {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('mobile-overlay');
        
        const isOpen = open !== undefined ? open : !AppState.mobileSidebarOpen;
        AppState.mobileSidebarOpen = isOpen;
        
        if (isOpen) {
            sidebar.classList.add('open');
            overlay.classList.add('open');
        } else {
            sidebar.classList.remove('open');
            overlay.classList.remove('open');
        }
    },

    toggleUserMenu() {
        const dropdown = document.getElementById('user-dropdown');
        dropdown.classList.toggle('hidden');
        
        const closeMenu = (e) => {
            if (!e.target.closest('.relative')) {
                dropdown.classList.add('hidden');
                document.removeEventListener('click', closeMenu);
            }
        };
        
        setTimeout(() => {
            document.addEventListener('click', closeMenu);
        }, 0);
    },

    async logout() {
        Modal.confirm('确定要退出登录吗？', async () => {
            try {
                await API.post('/logout');
            } catch (e) {
                console.error('Logout error:', e);
            }
            API.clearToken();
            AppState.adminInfo = null;
            Router.navigate('login');
            Toast.success('已退出登录');
        });
    },

    updateMenu() {
        const menu = document.getElementById('sidebar-menu');
        if (menu) {
            menu.innerHTML = this.renderMenu();
        }
    }
};

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const script = document.createElement('script');
        script.src = src;
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
    });
}

async function initApp() {
    const pages = [
        'login',
        'dashboard',
        'users',
        'subjects',
        'questions',
        'exam-papers',
        'ai-config',
        'community-posts',
        'community-comments',
        'achievements',
        'page-config',
        'admin-settings'
    ];

    for (const page of pages) {
        try {
            await loadScript(`js/pages/${page}.js`);
        } catch (e) {
            console.error(`Failed to load page ${page}:`, e);
        }
    }

    Router.init();
}

document.addEventListener('DOMContentLoaded', initApp);
