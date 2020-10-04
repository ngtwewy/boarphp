<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-10-22
// +----------------------------------------------------------------------

use think\Route;

//资源
Route::get('items$', 'item/Item/index'); //资源列表
Route::get('items/:id$', 'item/Item/read'); //资源详情

// 资源分类
Route::get('items/categories$', 'item/ItemCategory/index');
Route::get('items/categories/:id', 'item/ItemCategory/read');

//资源评论
Route::get('items/:id/comments', 'item/ItemComment/index'); //评论列表
Route::post('items/:id/comments', 'item/ItemComment/add');  //添加评论
Route::delete('items/comments/:id', 'item/ItemComment/delete'); //删除评论

//资源喜欢
Route::get('items/:id/like', 'item/ItemLike/index'); //我喜欢过的资源
Route::put('items/:id/like', 'item/ItemLike/add'); //喜欢资源
Route::delete('items/:id/like', 'item/ItemLike/delete'); //删除资源

//资源属性
Route::get('items/:id/like', 'item/ItemLike/index'); //所有属性列表
Route::post('items/:id/like', 'item/ItemLike/add'); //添加属性
Route::delete('items/like/:id', 'item/ItemLike/delete'); //删除我添加属性
