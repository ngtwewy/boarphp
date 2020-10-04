<?php
// +----------------------------------------------------------------------
// | Copyright (c) 2018 http://www.restfulapi.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: ngtwewy <62006464@qq.com> 
// +----------------------------------------------------------------------
// | DateTime: 2018-08-19 02:13:41
// +----------------------------------------------------------------------

use think\Route;

//朋友圈评论
Route::get('moments/:id/comments$', 'moment/MomentComment/index');
Route::post('moments/comments$', 'moment/MomentComment/add');
Route::delete('moments/comments/:id$', 'moment/MomentComment/delete');


//朋友圈点赞
Route::put('moments/:id/like', 'moment/Moment/like');
Route::post('moments/:id/like$', 'moment/Moment/like');
Route::delete('moments/:id/like', 'moment/Moment/cancelLike');
Route::post('moments/:id/like/delete', 'moment/Moment/cancelLike');

//朋友圈
Route::get('moments/:id', 'moment/Moment/read');
Route::get('moments', 'moment/Moment/index');
Route::post('moments$', 'moment/Moment/add');
Route::delete('moments/:id', 'moment/Moment/delete');
Route::post('moments/:id/delete', 'moment/Moment/delete');










