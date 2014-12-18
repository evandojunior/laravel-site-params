<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
*/

Route::get('/', function()
{
	return View::make('hello');
});

/*
 * Admin
 */
Route::group(array('prefix' => 'admin', 'before' => 'auth:admin'), function() {
    
    Route::post('editar', array('as' => 'admin.editar', function() {
            DB::table('tb_site_params')->where('id', 1)->update(array(Input::get('key') => Input::get('value')));
    }));
    
    Route::post('editarImagem', array('as' => 'admin.editar.imagem', function() {
            try {
                $param = Input::get('param');

                if (strlen($param) == 0)
                    throw new Exception('Nenhum parametro informado.');

                $file = Input::file('file');

                if (is_null($file))
                    throw new Exception('Você não selecionou um arquivo');

                $destinationPath = public_path() . DIRECTORY_SEPARATOR . 'uploads';
                $filename = date('YmdHis') . '_' . $file->getClientOriginalName();

                if ($file->move($destinationPath, $filename)) {
                    $img_path = '/uploads/' . $filename;

                    DB::table('tb_site_params')
                            ->where('id', 1)
                            ->update(array($param => $img_path));


                    return Redirect::back()->with('alert', 'Imagem atualizada com sucesso!');
                } else
                    throw new Exception('Erro ao enviar imagem.');
            } catch (Exception $e) {
                return Redirect::back()->with('erro', $e->getMessage());
            }
    }));

});

Route::get('admin', function() {
    return Redirect::to('users/login');
});

// Confide routes
Route::get('users/login', 'UsersController@login');
Route::post('users/login', 'UsersController@doLogin');
Route::get('users/logout', 'UsersController@logout');    