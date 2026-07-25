<?php

use think\facade\Route;

Route::get('think', function () {
    return 'hello,ThinkPHP8!';
});

Route::get('hello/:name', 'index/hello');

Route::group(function () {
    Route::group('v1', function () {
        Route::group(function () {
            Route::post('login', 'api.v1.Auth/login');
            Route::post('register', 'api.v1.Auth/register');
            Route::post('refresh', 'api.v1.Auth/refresh');
            Route::post('send-code', 'api.v1.Auth/sendCode');
            Route::post('forgot-password', 'api.v1.Auth/forgotPassword');
        })->prefix('');

        Route::group(function () {
            Route::get('user/info', 'api.v1.User/info');
            Route::put('user/profile', 'api.v1.User/updateProfile');
            Route::put('user/password', 'api.v1.User/changePassword');
            Route::post('user/avatar', 'api.v1.User/uploadAvatar');

            // 考试模块相关接口
            Route::get('exam/subjects', 'api.v1.Exam/subjectList');
            Route::get('exam/papers', 'api.v1.Exam/examPaperList');
            Route::get('exam/paper/detail', 'api.v1.Exam/examPaperDetail');
            Route::post('exam/start', 'api.v1.Exam/startExam');
            Route::post('exam/submit-answer', 'api.v1.Exam/submitSingleAnswer');
            Route::post('exam/submit', 'api.v1.Exam/submitExam');
            Route::get('exam/records', 'api.v1.Exam/examRecordList');
            Route::get('exam/record/detail', 'api.v1.Exam/examRecordDetail');

            // 学习提醒相关接口
            Route::get('reminders', 'api.v1.Reminder/list');
            Route::post('reminders', 'api.v1.Reminder/add');
            Route::put('reminders/:id', 'api.v1.Reminder/edit');
            Route::delete('reminders/:id', 'api.v1.Reminder/delete');
            Route::put('reminders/:id/toggle', 'api.v1.Reminder/toggle');

            // 成就系统相关接口
            Route::get('achievements', 'api.v1.Achievement/list');
            Route::get('achievements/mine', 'api.v1.Achievement/mine');
            Route::get('achievements/:id', 'api.v1.Achievement/detail');

            // 学习社区相关接口
            Route::get('community/posts', 'api.v1.Community/postList');
            Route::get('community/posts/:id', 'api.v1.Community/postDetail');
            Route::post('community/posts', 'api.v1.Community/postCreate');
            Route::delete('community/posts/:id', 'api.v1.Community/postDelete');
            Route::post('community/posts/:id/like', 'api.v1.Community/postLike');
            Route::post('community/posts/:id/favorite', 'api.v1.Community/postFavorite');
            Route::get('community/posts/:postId/comments', 'api.v1.Community/commentList');
            Route::post('community/posts/:postId/comments', 'api.v1.Community/commentCreate');
            Route::delete('community/comments/:id', 'api.v1.Community/commentDelete');
            Route::post('community/comments/:id/like', 'api.v1.Community/commentLike');
        })->middleware(['jwt_auth']);
    });

    Route::group('admin', function () {
        Route::group('v1', function () {
            Route::group(function () {
                Route::post('login', 'admin.v1.Auth/login');
            })->prefix('');

            Route::group(function () {
                // 认证相关
                Route::post('logout', 'admin.v1.Auth/logout');
                Route::get('admin/info', 'admin.v1.Auth/info');

                // 仪表盘
                Route::get('dashboard', 'admin.v1.Dashboard/index');

                // 用户管理
                Route::get('users', 'admin.v1.User/list');
                Route::get('users/:id', 'admin.v1.User/detail');
                Route::post('users', 'admin.v1.User/create');
                Route::put('users/:id', 'admin.v1.User/update');
                Route::delete('users/:id', 'admin.v1.User/delete');
                Route::put('users/:id/toggle-status', 'admin.v1.User/toggleStatus');

                // 科目管理
                Route::get('subjects', 'admin.v1.Subject/list');
                Route::get('subjects/all', 'admin.v1.Subject/all');
                Route::get('subjects/:id', 'admin.v1.Subject/detail');
                Route::post('subjects', 'admin.v1.Subject/create');
                Route::put('subjects/:id', 'admin.v1.Subject/update');
                Route::delete('subjects/:id', 'admin.v1.Subject/delete');

                // 题目管理
                Route::get('questions', 'admin.v1.Question/list');
                Route::get('questions/:id', 'admin.v1.Question/detail');
                Route::post('questions', 'admin.v1.Question/create');
                Route::put('questions/:id', 'admin.v1.Question/update');
                Route::delete('questions/:id', 'admin.v1.Question/delete');
                Route::post('questions/batch-delete', 'admin.v1.Question/batchDelete');
                Route::post('questions/batch-import', 'admin.v1.Question/batchImport');

                // 试卷管理
                Route::get('exam-papers', 'admin.v1.ExamPaper/list');
                Route::get('exam-papers/:id', 'admin.v1.ExamPaper/detail');
                Route::get('exam-papers/:id/questions', 'admin.v1.ExamPaper/questions');
                Route::post('exam-papers', 'admin.v1.ExamPaper/create');
                Route::put('exam-papers/:id', 'admin.v1.ExamPaper/update');
                Route::delete('exam-papers/:id', 'admin.v1.ExamPaper/delete');

                // AI配置管理
                Route::get('ai-configs', 'admin.v1.AiConfig/list');
                Route::get('ai-configs/all', 'admin.v1.AiConfig/all');
                Route::get('ai-configs/:id', 'admin.v1.AiConfig/detail');
                Route::post('ai-configs', 'admin.v1.AiConfig/create');
                Route::put('ai-configs/:id', 'admin.v1.AiConfig/update');
                Route::delete('ai-configs/:id', 'admin.v1.AiConfig/delete');
                Route::put('ai-configs/:id/default', 'admin.v1.AiConfig/setDefault');

                // 页面配置管理
                Route::get('page-configs', 'admin.v1.PageConfig/list');
                Route::get('page-configs/:id', 'admin.v1.PageConfig/detail');
                Route::get('page-configs/key/:key', 'admin.v1.PageConfig/getByKey');
                Route::post('page-configs', 'admin.v1.PageConfig/create');
                Route::put('page-configs/:id', 'admin.v1.PageConfig/update');
                Route::delete('page-configs/:id', 'admin.v1.PageConfig/delete');

                // 社区管理
                Route::get('community/posts', 'admin.v1.Community/postList');
                Route::get('community/posts/:id', 'admin.v1.Community/postDetail');
                Route::put('community/posts/:id/review', 'admin.v1.Community/postReview');
                Route::delete('community/posts/:id', 'admin.v1.Community/postDelete');
                Route::post('community/posts/batch-delete', 'admin.v1.Community/postBatchDelete');
                Route::get('community/comments', 'admin.v1.Community/commentList');
                Route::delete('community/comments/:id', 'admin.v1.Community/commentDelete');

                // 成就管理
                Route::get('achievements', 'admin.v1.Achievement/list');
                Route::get('achievements/all', 'admin.v1.Achievement/all');
                Route::get('achievements/:id', 'admin.v1.Achievement/detail');
                Route::get('achievements/:id/users', 'admin.v1.Achievement/userList');
                Route::post('achievements', 'admin.v1.Achievement/create');
                Route::put('achievements/:id', 'admin.v1.Achievement/update');
                Route::delete('achievements/:id', 'admin.v1.Achievement/delete');
            })->middleware(['admin_auth']);
        });
    });
})->middleware(['cors']);

