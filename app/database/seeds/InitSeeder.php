<?php

class InitSeeder extends Seeder {

    public function run() {
        $user = new User;
        $user->username = 'admin';
        $user->email = 'admin@admin.com';
        $user->password = Hash::make('admin');
        $user->confirmation_code = md5(uniqid(mt_rand(), true));
        $user->save();


        
    }

}
