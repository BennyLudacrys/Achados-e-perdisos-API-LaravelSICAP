<?php

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
//rota da página inicial
Route::get('/', function () {
    return view('welcome');
});
//rotas de autenticação
Auth::routes();
//rota para adicionar um anúncio pelo método da loja
Route::post('/annonce/store','AnnonceController@store');
//rota para exibir a interface para adicionar um anúncio através do método create
Route::get('/annonce/create','AnnonceController@create');
//rota de exibição do anúncio (perdido/encontrado) através do método de índice
Route::get('/annoncel','AnnonceController@index');
//rota para exibir os dados do anúncio de acordo com seu id através do método show
Route::get('/annonce/{annonce}','AnnonceController@show');
//exibir rota da interface de atualização de um anúncio através do método de edição
Route::get('/annonce/{annonce}/edit','AnnonceController@edit');
//rota de atualização de um anúncio através do método de atualização
Route::put('/annonce/{annonce}/update','AnnonceController@update');
//rota para deletar um anúncio pelo método destroy
Route::get('/annonce/{annonce}/destroy','AnnonceController@destroy');
//rota de exibição de dados para anúncios criados pelo usuário conectado através do método AnnoncesCreated
Route::get('/annonces','AnnonceController@annoncescreated');
//exibir a rota da interface do usuário de atualização por meio do método de edição
Route::get('/user/edit','UserController@edit');
//rota de atualização do usuário através do método de atualização
Route::put('/user/{id}/update','UserController@update');
//rota de exibição da imagem do anúncio
Route::get('annonce_affichage/fetch_image/{id}', 'AnnonceController@fetch_image');
//rota de pesquisa de anúncios
Route::get('/search', 'AnnonceController@search');
//route d'affichage des notifications
Route::get('/notifications','AnnonceController@notifications');
//rota de remoção de notificação
Route::get('/notifications/destroy{id}','AnnonceController@destroyNotifiation');
//rota de marcar uma notificação como lida
Route::get('/notifications/MarkAsRead{id}','AnnonceController@MarkAsReadNotifiation');
//rota de marcar uma notificação como não lida
Route::get('/notifications/MarkAsUnread{id}','AnnonceController@MarkAsUnreadNotifiation');
//rota para enviar uma mensagem de acordo com a página de um anúncio
Route::post('/message/store','MessageController@store');
//rota de exibição de mensagem do usuário conectado
Route::get('/messages','MessageController@index');
//exibir direcionar a discussão com um usuário
Route::get('/messages/{id}','MessageController@show');
//rota para enviar uma mensagem no chat
Route::post('/message/storeDescussion','MessageController@storeDiscussion');
//rota que permite deletar uma mensagem através do método destroy
Route::get('/message/destroy/{id}','MessageController@destroy');
//rota de marcar uma notificação de mensagem como lida
Route::get('/message/MarkAsRead{id}','MessageController@MarkAsReadNotifiation');
//rota para exibição da interface para exibição do formulário de verificação de senha pelo método affVerifMotDePasse
Route::get('/user/affVerifMotDePasse','UserController@affVerifMotDePasse');
//rota de verificação de senha pelo método verifMotDePasse
Route::get('/user/verifMotDePasse','UserController@verifMotDePasse');
//rota para atualizar a senha de um usuário através do método updateMotDePasse
Route::put('/user/updateMotDePasse','UserController@updateMotDePasse');
//exibição de dados do usuário
Route::get('/user/show/{id}','UserController@show');










Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
