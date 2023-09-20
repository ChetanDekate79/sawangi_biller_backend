<?php
use App\Http\Controllers\HostelConsumptionReport_Controller;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
}); */

/*Route::get('/', function() {
    return View::make('index'); 
}); 
Route::any('{catchall}', function() {
  return View::make('index'); 
})->where('catchall', '.*');
*/





 //Clear route cache
 Route::get('/route-cache', function() {
     \Artisan::call('route:cache');
     return 'Routes cache cleared';
 });

 //Clear config cache
 Route::get('/config-cache', function() {
     \Artisan::call('config:cache');
     return 'Config cache cleared';
 }); 

 // Clear application cache
 Route::get('/clear-cache', function() {
     \Artisan::call('cache:clear');
     return 'Application cache cleared';
 });

 // Clear view cache
 Route::get('/view-clear', function() {
     \Artisan::call('view:clear');
     return 'View cache cleared';
 });

 // Clear cache using reoptimized class
 Route::get('/optimize-clear', function() {
     \Artisan::call('optimize:clear');
     return 'View cache cleared';
 });

 Route::post('/download-pdf', 'HostelConsumptionReport_Controller@downloadPDF')->name('download-pdf');
