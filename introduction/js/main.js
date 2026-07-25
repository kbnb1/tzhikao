const defaultConfig = {
    title: '智考AI - 智能高考预测助手',
    description: '基于人工智能的高考预测助手，智能刷题、错题分析、分数预测，助你决胜高考',
    stats: [
        { value: 500000, label: '累计用户', suffix: '' },
        { value: 98, label: '预测准确率%', suffix: '' },
        { value: 1000, label: '万+题库', suffix: '' }
    ],
    features: [
        { icon: 'fa-brain', title: 'AI智能出题', desc: '基于知识图谱和个人薄弱点，智能推荐最适合你的题目，针对性提升效率。', color: 'from-primary to-secondary' },
        { icon: 'fa-chart-line', title: '分数预测', desc: '深度学习算法分析你的答题数据，精准预测高考分数，让努力有目标。', color: 'from-blue-500 to-cyan-500' },
        { icon: 'fa-book-open', title: '海量题库', desc: '涵盖近十年高考真题、模拟题，全科目全覆盖，题目持续更新中。', color: 'from-green-500 to-emerald-500' },
        { icon: 'fa-exclamation-triangle', title: '错题本', desc: '自动收集错题，智能分类归纳，错题重做巩固记忆，不再错第二次。', color: 'from-orange-500 to-amber-500' },
        { icon: 'fa-clock', title: '学习计划', desc: '根据备考时间和目标分数，AI自动生成个性化学习计划，科学备考。', color: 'from-pink-500 to-rose-500' },
        { icon: 'fa-video', title: '视频解析', desc: '难题配有详细视频讲解，名师带你分析解题思路，举一反三。', color: 'from-purple-500 to-violet-500' },
        { icon: 'fa-trophy', title: '模拟考试', desc: '真实模拟高考环境，计时答题、自动评分，提前适应考试节奏。', color: 'from-red-500 to-pink-500' },
        { icon: 'fa-users', title: '学习社区', desc: '和百万考生一起交流学习心得，分享备考经验，共同进步成长。', color: 'from-indigo-500 to-blue-600' }
    ],
    testimonials: [
        { name: '张同学', role: '2024届考生 · 提升85分', content: '用了智考AI三个月，分数提高了80多分！AI预测的分数和我高考实际分数只差10分，太神奇了。强烈推荐给所有高三同学！', rating: 5, avatar: '张' },
        { name: '李同学', role: '高二 · 错题本重度用户', content: '错题本功能太棒了，自动整理分类，复习起来效率超高。以前总是在同一个地方犯错，现在错题重做几遍就记住了。', rating: 5, avatar: '李' },
        { name: '王同学', role: '高三 · 刷题达人', content: 'AI智能出题真的很智能，总是出我不会的题，针对性很强。不像其他APP，题目太简单或者太难，这个刚刚好。', rating: 4.5, avatar: '王' },
        { name: '陈女士', role: '高三学生家长', content: '作为家长，我很满意这个APP。孩子用了之后学习积极性提高了很多，成绩也稳步上升。学习计划功能让孩子不再盲目刷题。', rating: 5, avatar: '陈' },
        { name: '刘同学', role: '高二 · 视频课爱好者', content: '视频解析讲得很清楚，比我们老师讲的还好懂。遇到不会的题看一遍视频就明白了，节省了很多时间。', rating: 5, avatar: '刘' },
        { name: '赵同学', role: '高三 · 模考常客', content: '模拟考试功能很实用，提前适应考试节奏。数据分析也很详细，能清楚看到自己的薄弱环节，对症下药。', rating: 5, avatar: '赵' }
    ],
    faqs: [
        { question: '智考AI是免费的吗？', answer: '智考AI提供免费版本，包含基础刷题、错题本等功能。同时我们也提供会员服务，解锁AI预测、视频解析、模拟考试等高级功能。新用户注册即可享受7天会员免费试用。' },
        { question: 'AI分数预测准确吗？', answer: '经过大量真实考生验证，智考AI的分数预测准确率达到98%以上。预测基于你的答题数据、历史成绩、知识点掌握情况等多维度分析，越用越准确。建议完成至少5套完整试卷后再查看预测结果。' },
        { question: '题库都包含哪些内容？', answer: '智考AI题库涵盖语文、数学、英语、物理、化学、生物、政治、历史、地理等全部高考科目。包含近十年高考真题、各地模拟题、名校月考题等，题目总量超过100万道，并且持续更新中。' },
        { question: '支持哪些设备使用？', answer: '智考AI目前支持Android手机和平板，iOS版本正在开发中，敬请期待。同时我们也提供网页版，可以在电脑上使用。学习数据多端同步，随时随地都能学习。' },
        { question: '我的学习数据安全吗？', answer: '我们非常重视用户隐私和数据安全。所有用户数据都经过加密存储，严格按照国家相关法律法规保护用户隐私。我们承诺不会向任何第三方出售或泄露用户个人信息。' },
        { question: '如何联系客服？', answer: '您可以通过APP内的"我的-帮助与反馈"联系我们的客服团队，也可以发送邮件至 support@zhikaoai.com。客服工作时间为每天9:00-22:00，我们会尽快回复您的问题。' }
    ],
    download: {
        androidUrl: '#',
        iosUrl: '#',
        iosComingSoon: true
    }
};

let pageConfig = defaultConfig;

async function loadPageConfig() {
    try {
        const response = await fetch('/page-config/introduction');
        if (response.ok) {
            const data = await response.json();
            if (data.code === 200 && data.data) {
                const content = JSON.parse(data.data.content || '{}');
                pageConfig = { ...defaultConfig, ...content };
                applyPageConfig();
            }
        }
    } catch (error) {
        console.log('Using default config');
    }
}

function applyPageConfig() {
    if (pageConfig.title) {
        document.title = pageConfig.title;
    }
}

function initNavbar() {
    const navbar = document.getElementById('navbar');
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const mobileMenu = document.getElementById('mobile-menu');

    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            navbar.classList.add('navbar-scrolled');
        } else {
            navbar.classList.remove('navbar-scrolled');
        }
    });

    mobileMenuBtn.addEventListener('click', () => {
        mobileMenu.classList.toggle('hidden');
        const icon = mobileMenuBtn.querySelector('i');
        if (mobileMenu.classList.contains('hidden')) {
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        } else {
            icon.classList.remove('fa-bars');
            icon.classList.add('fa-times');
        }
    });

    const mobileLinks = mobileMenu.querySelectorAll('a');
    mobileLinks.forEach(link => {
        link.addEventListener('click', () => {
            mobileMenu.classList.add('hidden');
            const icon = mobileMenuBtn.querySelector('i');
            icon.classList.remove('fa-times');
            icon.classList.add('fa-bars');
        });
    });
}

function animateNumber(element, target, duration = 2000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const timer = setInterval(() => {
        current += increment;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        
        if (target >= 10000) {
            element.textContent = Math.floor(current / 10000) + '万+';
        } else if (target >= 1000) {
            element.textContent = Math.floor(current / 1000) + '万+';
        } else {
            element.textContent = Math.floor(current);
        }
    }, 16);
}

function initStatsAnimation() {
    const statNumbers = document.querySelectorAll('.stat-number');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.animated) {
                entry.target.dataset.animated = 'true';
                const target = parseInt(entry.target.dataset.target);
                animateNumber(entry.target, target);
            }
        });
    }, { threshold: 0.5 });

    statNumbers.forEach(stat => observer.observe(stat));
}

function initScrollAnimations() {
    const fadeElements = document.querySelectorAll('.fade-in');
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
            }
        });
    }, { 
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    });

    fadeElements.forEach(el => observer.observe(el));
}

function initFAQ() {
    const faqItems = document.querySelectorAll('.faq-item');
    
    faqItems.forEach(item => {
        const question = item.querySelector('.faq-question');
        const answer = item.querySelector('.faq-answer');
        const icon = question.querySelector('i');
        
        question.addEventListener('click', () => {
            const isOpen = item.classList.contains('active');
            
            faqItems.forEach(otherItem => {
                otherItem.classList.remove('active');
                const otherAnswer = otherItem.querySelector('.faq-answer');
                const otherIcon = otherItem.querySelector('.faq-question i');
                otherAnswer.classList.remove('show');
                otherIcon.style.transform = 'rotate(0deg)';
            });
            
            if (!isOpen) {
                item.classList.add('active');
                answer.classList.add('show');
                icon.style.transform = 'rotate(180deg)';
            }
        });
    });
}

function initSmoothScroll() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                const navbarHeight = document.getElementById('navbar').offsetHeight;
                const targetPosition = targetElement.offsetTop - navbarHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });
}

function initScreenshotsScroll() {
    const track = document.querySelector('.screenshots-track');
    if (!track) return;
    
    let isDown = false;
    let startX;
    let scrollLeft;
    
    track.addEventListener('mousedown', (e) => {
        isDown = true;
        track.style.cursor = 'grabbing';
        startX = e.pageX - track.offsetLeft;
        scrollLeft = track.scrollLeft;
    });
    
    track.addEventListener('mouseleave', () => {
        isDown = false;
        track.style.cursor = 'grab';
    });
    
    track.addEventListener('mouseup', () => {
        isDown = false;
        track.style.cursor = 'grab';
    });
    
    track.addEventListener('mousemove', (e) => {
        if (!isDown) return;
        e.preventDefault();
        const x = e.pageX - track.offsetLeft;
        const walk = (x - startX) * 2;
        track.scrollLeft = scrollLeft - walk;
    });
}

function init() {
    loadPageConfig();
    initNavbar();
    initStatsAnimation();
    initScrollAnimations();
    initFAQ();
    initSmoothScroll();
    initScreenshotsScroll();
    
    setTimeout(() => {
        document.querySelectorAll('.hero-section .fade-in').forEach(el => {
            el.classList.add('visible');
        });
    }, 100);
}

document.addEventListener('DOMContentLoaded', init);
