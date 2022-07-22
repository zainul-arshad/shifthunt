<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', [App\Http\Controllers\FrontendController::class, 'index']);
Route::get('/login', function(){
	return view('auth.login');
});
Route::get('form/{customer_id}',[App\Http\Controllers\FormController::class, 'form']);
Route::post('storeLastForms',[App\Http\Controllers\FormController::class, 'storeLastForms']);
Route::post('lastStepModify',[App\Http\Controllers\FormController::class, 'lastStepModify']);
Route::get('/invitation', [App\Http\Controllers\PreRegistrationController::class, 'index']);
Route::post('preregistrationStore', [
						'as' => 'preregistrationStore', 'uses' => 'PreRegistrationController@regstore'
			]);
			
Route::post('storeForm',[App\Http\Controllers\FormController::class, 'store']);
Auth::routes();
Route::group(['middleware' => 'auth'], function () {
	Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
	Route::get('/logout', 'Auth\LoginController@logout');
	Route::post('delete-profile-image', [
						'as' => 'delete-profile-image', 'uses' => 'Client\ProfileController@deletePhoto'
			]);
	Route::post('delete-customer-profile-image', [
						'as' => 'delete-customer-profile-image', 'uses' => 'Customer\ProfileController@deletePhoto'
			]);
	Route::post('delete-position-image', [
						'as' => 'delete-position-image', 'uses' => 'PositionsController@deletePhoto'
			]);
	Route::post('delete-gposition-image', [
						'as' => 'delete-gposition-image', 'uses' => 'GPositionsController@deletePhoto'
			]);	
	Route::group(['middleware' =>'admin'], function () {
			//Route::get('/dashboard', [App\Http\Controllers\HomeController::class, 'index'])->name('dashboard');
			

		//client
			Route::resource('client','ClientController');
			Route::get('filter_client', [
						'as' => 'filter_client', 'uses' => 'ClientController@filter_client'
			]);
			Route::post('clientStore', [
						'as' => 'clientStore', 'uses' => 'ClientController@store'
			]);
			Route::post('clientUpdate', [
						'as' => 'clientUpdate', 'uses' => 'ClientController@update'
			]);
			Route::post('client/delete', [
						'as' => 'client/delete', 'uses' => 'ClientController@destroy'
			]);
            Route::get('slotlist/{client_id}', [
						'as' => 'slotlist', 'uses' => 'ClientController@slotlist'
			]);
			Route::get('slotlistview/{slot_id}', [
						'as' => 'slotlistview', 'uses' => 'ClientController@slotlistview'
			]);
            Route::get('slotlist_filter', [
						'as' => 'slotlist_filter', 'uses' => 'ClientController@slotlist_filter'
			]);
			Route::get('slotlist_filter_completed', [
						'as' => 'slotlist_filter_completed', 'uses' => 'ClientController@slotlist_filter_completed'
			]);
			Route::get('slotlist_filter_today', [
						'as' => 'slotlist_filter_today', 'uses' => 'ClientController@slotlist_filter_today'
			]);
			Route::get('slotlist_filter_absent', [
						'as' => 'slotlist_filter_absent', 'uses' => 'ClientController@slotlist_filter_absent'
			]);
		//customer
			Route::resource('customer','CustomerController');
			Route::get('filter_customer', [
						'as' => 'filter_customer', 'uses' => 'CustomerController@filter_customer'
			]);
			Route::post('customerStore', [
						'as' => 'customerStore', 'uses' => 'CustomerController@store'
			]);
			Route::post('customerUpdate', [
						'as' => 'customerUpdate', 'uses' => 'CustomerController@update'
			]);
			Route::post('customer/delete', [
						'as' => 'customer/delete', 'uses' => 'CustomerController@destroy'
			]);
            Route::get('customer-registration/{id}', [
						'as' => 'customer-registration', 'uses' => 'CustomerController@customerRegistration'
			]);
			Route::post('customer-registration-status', [
						'as' => 'customer-registration-status', 'uses' => 'CustomerController@customerRegistrationStatus'
			]);
			
		//settings
			Route::resource('settings','SettingsController');
			Route::get('filter_settings', [
						'as' => 'filter_settings', 'uses' => 'SettingsController@filter_settings'
			]);
			Route::post('settingsUpdate', [
						'as' => 'settingsUpdate', 'uses' => 'SettingsController@update'
			]);

		//slot
			Route::resource('slot','SlotController');
			Route::get('filter_slot', [
						'as' => 'filter_slot', 'uses' => 'SlotController@filter_slot'
			]);
			Route::get('admin/filter_slot', [
						'as' => 'filter_slot', 'uses' => 'ShiftController@filter_slot'
			]);
			Route::get('admin/filter_slot_completed', [
						'as' => 'filter_slot_completed', 'uses' => 'ShiftController@filter_slot_completed'
			]);	
			Route::get('admin/filter_slot_today', [
						'as' => 'filter_slot_today', 'uses' => 'ShiftController@filter_slot_today'
			]);
			Route::get('admin/filter_slot_absent', [
						'as' => 'filter_slot_absent', 'uses' => 'ShiftController@filter_slot_absent'
			]);
			
			Route::post('slotStore', [
						'as' => 'slotStore', 'uses' => 'SlotController@store'
			]);
			Route::post('slotUpdate', [
						'as' => 'slotUpdate', 'uses' => 'SlotController@update'
			]);
			Route::post('slot/delete', [
						'as' => 'slot/delete', 'uses' => 'SlotController@destroy'
			]);
			

		//Booking
			Route::resource('booking','BookingController');
			Route::get('filter_booking', [
						'as' => 'filter_booking', 'uses' => 'BookingController@filter_booking'
			]);
			Route::post('bookingStore', [
						'as' => 'bookingStore', 'uses' => 'BookingController@store'
			]);
			Route::post('bookingUpdate', [
						'as' => 'bookingUpdate', 'uses' => 'BookingController@update'
			]);

		//positions
			Route::resource('positions','PositionsController');
			Route::get('filter_positions', [
						'as' => 'filter_positions', 'uses' => 'PositionsController@filter_positions'
			]);
			Route::post('positionsStore', [
						'as' => 'positionsStore', 'uses' => 'PositionsController@store'
			]);
			Route::post('positionsUpdate', [
						'as' => 'positionsUpdate', 'uses' => 'PositionsController@update'
			]);
			Route::post('positions/delete', [
						'as' => 'positions/delete', 'uses' => 'PositionsController@destroy'
			]);	

			Route::post('deleteRequirement', [
						'as' => 'deleteRequirement', 'uses' => 'PositionsController@deleteRequirement'
			]);	
			//
			Route::resource('banner','BannerController');
			Route::get('filter_banner', [
						'as' => 'filter_banner', 'uses' => 'BannerController@filter_banner'
			]);
			Route::post('bannerStore', [
						'as' => 'bannerStore', 'uses' => 'BannerController@store'
			]);
			
			Route::post('banner/delete', [
						'as' => 'banner/delete', 'uses' => 'BannerController@destroy'
			]);
			//slot for approve
			Route::get('slot-requests', [
						'as' => 'slot-requests', 'uses' => 'SlotController@slot_requests'
			]);
			Route::get('slot-request-filter', [
						'as' => 'slot-request-filter', 'uses' => 'ShiftController@slot_request_filter'
			]);
			Route::post('approveSlot', [
						'as' => 'approveSlot', 'uses' => 'SlotController@approveSlot'
			]);
			Route::get('slot-request/{id}', [
						'as' => 'slot-request', 'uses' => 'SlotController@slotRequestView'
			]);
			Route::post('admin/markAsComplete', [
						'as' => 'markAsComplete', 'uses' => 'SlotController@markAsComplete'
			]);	
			Route::post('admin/markAsAbsent', [
						'as' => 'markAsAbsent', 'uses' => 'SlotController@markAsAbsent'
			]);
			Route::get('getApproveForm', [
						'as' => 'getApproveForm', 'uses' => 'ShiftController@getApproveForm'
			]);
			//pre-reg backend

			Route::resource('invitations','PreRegistrationInfoController');
			Route::get('filter_preregistration', [
						'as' => 'filter_preregistration', 'uses' => 'PreRegistrationInfoController@filter_preregistration'
			]);
			Route::post('inviteSend', [
						'as' => 'inviteSend', 'uses' => 'PreRegistrationInfoController@inviteSend'
			]);
			Route::get('filterInviteSend', [
						'as' => 'filterInviteSend', 'uses' => 'PreRegistrationInfoController@filterInviteSend'
			]);
			Route::get('filterInviteApprove', [
						'as' => 'filterInviteApprove', 'uses' => 'PreRegistrationInfoController@filterInviteApprove'
			]);
			Route::post('approveInvite', [
						'as' => 'approveInvite', 'uses' => 'PreRegistrationInfoController@approveInvite'
			]);
			
			//global positions
			Route::resource('global-positions','GPositionsController');
			Route::get('filter_gpositions', [
						'as' => 'filter_gpositions', 'uses' => 'GPositionsController@filter_gpositions'
			]);
			Route::post('gpositionsStore', [
						'as' => 'gpositionsStore', 'uses' => 'GPositionsController@store'
			]);
			Route::post('gpositionsUpdate', [
						'as' => 'gpositionsUpdate', 'uses' => 'GPositionsController@update'
			]);
			Route::post('global-positions/delete', [
						'as' => 'global-positions/delete', 'uses' => 'GPositionsController@destroy'
			]);	

			Route::post('deleteGRequirement', [
						'as' => 'deleteGRequirement', 'uses' => 'GPositionsController@deleteRequirement'
			]);	

			
	});
	Route::group(['middleware' =>'customer','prefix'=>'carer'], function () {
		//profile
			Route::resource('profile','Customer\ProfileController');
			Route::get('edit-profile', [
						'as' => 'edit-profile', 'uses' => 'Customer\ProfileController@editProfile'
			]);
			Route::post('update-profile', [
						'as' => 'update-profile', 'uses' => 'Customer\ProfileController@updateProfile'
			]);
			Route::get('slots', [
						'as' => 'slots', 'uses' => 'Customer\ProfileController@slots'
			]);
			
	});
	Route::group(['middleware' =>'client'], function () {
		//profile
			Route::resource('profile','Client\ProfileController');
			Route::get('edit-profile', [
						'as' => 'edit-profile', 'uses' => 'Client\ProfileController@editProfile'
			]);
			Route::post('update-profile', [
						'as' => 'update-profile', 'uses' => 'Client\ProfileController@updateProfile'
			]);
			
		//positions
			Route::resource('positions','PositionsController');
			Route::get('filter_positions', [
						'as' => 'filter_positions', 'uses' => 'PositionsController@filter_positions'
			]);
			Route::post('positionsStore', [
						'as' => 'positionsStore', 'uses' => 'PositionsController@store'
			]);
			Route::post('positionsUpdate', [
						'as' => 'positionsUpdate', 'uses' => 'PositionsController@update'
			]);
			Route::post('positions/delete', [
						'as' => 'positions/delete', 'uses' => 'PositionsController@destroy'
			]);	

			Route::post('deleteRequirement', [
						'as' => 'deleteRequirement', 'uses' => 'PositionsController@deleteRequirement'
			]);	
			Route::get('getTemplate', [
						'as' => 'getTemplate', 'uses' => 'PositionsController@getTemplate'
			]);

			//slot
			Route::resource('slot','SlotController');
			Route::get('filter_slot', [
						'as' => 'filter_slot', 'uses' => 'SlotController@filter_slot'
			]);
			Route::get('filter_slot_completed', [
						'as' => 'filter_slot_completed', 'uses' => 'SlotController@filter_slot_completed'
			]);	
			Route::get('filter_slot_today', [
						'as' => 'filter_slot_today', 'uses' => 'SlotController@filter_slot_today'
			]);
			Route::get('filter_slot_absent', [
						'as' => 'filter_slot_absent', 'uses' => 'SlotController@filter_slot_absent'
			]);
			Route::post('slotStore', [
						'as' => 'slotStore', 'uses' => 'SlotController@store'
			]);
			Route::post('slotUpdate', [
						'as' => 'slotUpdate', 'uses' => 'SlotController@update'
			]);
			Route::post('slot/delete', [
						'as' => 'slot/delete', 'uses' => 'SlotController@destroy'
			]);	
			//slot-completion-update
			Route::get('slot-completion-update', [
						'as' => 'slot-completion-update', 'uses' => 'SlotController@slot_completion_update'
			]);
			Route::post('slot-completion-store', [
						'as' => 'slot-completion-store', 'uses' => 'SlotController@slot_completion_store'
			]);
		//Booking
			Route::resource('booking','BookingController');
			Route::get('filter_booking', [
						'as' => 'filter_booking', 'uses' => 'BookingController@filter_booking'
			]);
			Route::post('bookingStore', [
						'as' => 'bookingStore', 'uses' => 'BookingController@store'
			]);
			Route::post('bookingUpdate', [
						'as' => 'bookingUpdate', 'uses' => 'BookingController@update'
			]);
			Route::post('changeBookingStatus', [
						'as' => 'changeBookingStatus', 'uses' => 'BookingController@changeBookingStatus'
			]);		
		//markAsComplete
			Route::post('markAsComplete', [
						'as' => 'markAsComplete', 'uses' => 'SlotController@markAsComplete'
			]);	
			Route::post('markAsAbsent', [
						'as' => 'markAsAbsent', 'uses' => 'SlotController@markAsAbsent'
			]);	
			Route::get('checkBookedCount', [
						'as' => 'checkBookedCount', 'uses' => 'ShiftController@checkBookedCount'
			]);
			Route::get('checkApprovable', [
						'as' => 'checkApprovable', 'uses' => 'BookingController@checkApprovable'
			]);
			
	});
});
