<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2019 http://restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2019-03-14
// +----------------------------------------------------------------------

use think\Route;

//文章
Route::get('articles$', 'article/Article/index');           // 文章列表
Route::get('articles/:id$', 'article/Article/read');        // 文章详情
Route::post('articles$', 'article/Article/add');            // 添加文章
Route::put('articles/:id$', 'article/Article/edit');        // 更新文章
Route::delete('articles/:id$', 'article/Article/delete');   // 删除文章

// 文章分类
Route::get('articles/categories$', 'article/ArticleCategory/index');
Route::get('articles/categories/:id$', 'article/ArticleCategory/read');

// 单页
Route::get('articles/pages$', 'article/ArticlePage/index');
Route::get('articles/pages/:id$', 'article/ArticlePage/read');

//文章评论
Route::get('articles/:id/comments', 'article/ArticleComment/index'); //评论列表
Route::post('articles/:id/comments', 'article/ArticleComment/add');  //添加评论
Route::delete('articles/:article_id/comments/:id', 'article/ArticleComment/delete'); //删除评论

//文章喜欢
Route::get('articles/like$', 'article/ArticleLike/index');          //我喜欢过的文章
Route::post('articles/:id/like', 'article/ArticleLike/add');        //喜欢文章
Route::delete('articles/:id/like', 'article/ArticleLike/delete');   //删除文章

//文章属性
// Route::get('articles/:id/like', 'article/ArticleLike/index');       //所有属性列表
// Route::post('articles/:id/like', 'article/ArticleLike/add');        //添加属性
// Route::delete('articles/like/:id', 'article/ArticleLike/delete');   //删除我添加属性

Route::put('ttest', 'article/Test/testPut');
Route::patch('ttest', 'article/Test/testPatch');
Route::post('ttest', 'article/Test/testPost');
Route::delete('ttest', 'article/Test/testDelete');