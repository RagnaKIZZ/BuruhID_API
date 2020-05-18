<?php

use Slim\App;
use Slim\Http\Request;
use Slim\Http\Response;

return function (App $app) {
    date_default_timezone_set('Asia/Jakarta');
    $container = $app->getContainer();

    // $app->get('/[{name}]', function (Request $request, Response $response, array $args) use ($container) {
    //     // Sample log message
    //     $container->get('logger')->info("Slim-Skeleton '/' route");

    //     // Render index view
    //     return $container->get('renderer')->render($response, 'index.phtml', $args);
    // });

    

     //---------------------------------------------------------------------------------------------------------//
    //----------------------------------------------CUSTOMER API-----------------------------------------------//
   //---------------------------------------------------------------------------------------------------------//



    $app->post('/user/register_customer', function($request, $response){
        $nama = $request->getParsedBodyParam('nama');
        $email = $request->getParsedBodyParam('email');
        $telepon = $request->getParsedBodyParam('telepon');
        $password = $request->getParsedBodyParam('password');

        $queryTelp = "SELECT * FROM tb_user WHERE telepon = :telepon";

        $queryEmail = "SELECT * FROM tb_user WHERE email = :email";

        $query = "INSERT INTO tb_user (nama, email, telepon, `password`) VALUES
        (:nama, :email, :telepon , MD5(:password))";

        if(empty($telepon)||empty($nama)||empty($email)||empty($password)){
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $stmt = $this->db->prepare($queryTelp);
        if($stmt->execute([':telepon' => $telepon])){
            $result = $stmt->fetch();
            $row_telepon = $result['telepon'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Email atau nomor telepon telah terdaftar!"]);
            }
        }

        $stmt = $this->db->prepare($queryEmail);
        if($stmt->execute([':email' => $email])){
            $result = $stmt->fetch();
            $row_telepon = $result['email'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Email atau nomor telepon telah terdaftar!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if($stmt->execute([':nama' => $nama, ':email' => $email, 
        ':telepon' => $telepon, ':password' => $password])){
            return $response->withJson(["code"=>200, "msg"=>"Register berhasil!"]);
        }
            return $response->withJson(["code"=>201, "msg"=>"Register gagal!"]);

    });

    $app->post('/user/login_customer', function($request, $response){
        $email      = $request->getParsedBodyParam('email');
        $password   = $request->getParsedBodyParam('password');
        $param      = "1";
        $token      = hash('sha256', md5(date('Y-m-d H:i:s'),$email)) ;

        if (empty($email) || empty($password)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }

        $query = "SELECT `user_id`,nama, email, telepon, foto, token_login, is_login, waktu_update FROM tb_user WHERE email = :email AND `password` = MD5(:password)";
        $queryUpdate = "UPDATE tb_user set token_login = :token, is_login = '$param' WHERE `user_id` = :id ";
        
        $stmt = $this->db->prepare($query);
        if($stmt->execute([':email' => $email, ':password' => $password])){
            $result = $stmt->fetch();
            $rowIsLogin = $result['is_login'];
            $rowID      = $result['user_id'];
            if ($result) {
                if($rowIsLogin === "0"){
                    $stmtLogin = $this->db->prepare($queryUpdate);
                    if($stmtLogin->execute([':id' => $rowID, ':token' => $token])){
                        $stmt1 = $this->db->prepare($query);
                        if($stmt1->execute([':email' => $email, ':password' => $password])){
                            $result1 = $stmt1->fetch();
                            if ($result1) {
                                return $response->withJson(["code"=>200, "msg"=>"Login berhasil!", "data" => $result1]);
                            }
                            return $response->withJson(["code"=>201, "msg"=>"Login gagal update!"]);
                        }
                        return $response->withJson(["code"=>201, "msg"=>"Login gagal!"]);
                        
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Login gagal update status!"]);
                    }
                }else{
                    return $response->withJson(["code"=>201, "msg"=>"Anda telah login diperangkat tertentu!"]);
                }
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Email atau password salah!"]);
            }
        }
        return $response->withJson(["code"=>201, "msg"=>"Email atau password salah!"]);

       
    });

    $app->post('/user/logout_customer', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token');

        $queryCheck = "SELECT * FROM tb_user WHERE `user_id` = :id AND `token_login` = :token AND is_login = '1'";
        $query = "UPDATE tb_user set is_login = '0' WHERE `user_id` = :id AND `token_login` = :token AND is_login = '1'";
        
        $stmt1 = $this->db->prepare($queryCheck);
        if($stmt1->execute([':id' => $id, ':token' => $token_login])){
            $result = $stmt1->fetch();
            if ($result) {
                $stmt = $this->db->prepare($query);
                if($stmt->execute([':id' => $id, ':token' => $token_login])){
                    return $response->withJson(["code"=>200, "msg"=>"Logout berhasil!"]);
                }
        return $response->withJson(["code"=>201, "msg"=>"Logout gagal!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Logout gagal1!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Logout gagal!"]);
       
    });
    

    // $app->post('/number', function($request, $response){
    //     $number = $request->getParsedBodyParam('number');

    //     // $query = "SELECT * FROM numb WHERE `number` > $number";
    //     $query = "INSERT INTO numb (`number`) VALUES ($number)";

    //     $stmt = $this->db->prepare($query);
    //     if($stmt->execute()){
    //             // $result = $stmt->fetchAll();
    //             return $response->withJson(["code"=>200, "msg"=>"Register berhasil!", "data" => $result]);
    //         }else{
    //             return $response->withJson(["code"=>201, "msg"=>"Register gagal!"]);
    //         }
    // });


    $app->post('/user/update_firebase_token', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $token_firebase = $request->getParsedBodyParam('token_firebase');

        if (empty($id) || empty($token_login) || empty($token_firebase)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }

        $query = "UPDATE tb_user set token_firebase = :firebase WHERE `user_id` = :id AND `token_login` = :token_login";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':firebase' => $token_firebase, ':id' => $id, ':token_login' => $token_login])) {
            return $response->withJson(["code"=>200, "msg"=>"Update token berhasil!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Update token gagal!"]);
    });

    //done
    $app->post('/user/update_name', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $nama           = $request->getParsedBodyParam('nama');

        if (empty($nama) || empty($token_login) || empty($id)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }

        $query = "UPDATE tb_user set nama = :nama WHERE `user_id` = :id AND `token_login` = :token_login";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':nama' => $nama, ':id' => $id, ':token_login' => $token_login])) {
            return $response->withJson(["code"=>200, "msg"=>"Update nama berhasil!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Update nama gagal!"]);
    });

    //done
    $app->post('/user/update_password', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $password_lama  = $request->getParsedBodyParam('password_lama');
        $password_baru  = $request->getParsedBodyParam('password_baru');

        if (empty($password_baru) || empty($password_lama) || empty($id) || empty($token_login)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }
        $querySelect = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $query = "UPDATE tb_user set `password` = MD5(:password_baru) WHERE `user_id` = :id 
                  AND `token_login` = :token_login AND `password` = MD5(:password_lama)";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmt1 = $this->db->prepare($query);
                if ($stmt1->execute([':id' => $id, ':token_login' => $token_login,
                ':password_lama' => $password_lama, ':password_baru' => $password_baru])) {
                    return $response->withJson(["code"=>200, "msg"=>"Update password berhasil!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Update password gagal!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Update password gagal!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Update password gagal!"]);       
    });


    $app->post('/user/update_email', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $password    = $request->getParsedBodyParam('password');
        $email       = $request->getParsedBodyParam('email');

        $timeParam   = date('Y-m-d H:i:s', time()+2*24*60*60);
        $timeUpdate  = date('Y-m-d H:i:s', time());


        if (empty($id)||empty($token_login)||empty($password)||empty($email)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $queryEmail = "SELECT * FROM tb_user WHERE email = :email";
        $query = "SELECT `user_id`, token_login, waktu_update 
        FROM tb_user WHERE `user_id` = :id AND token_login = :token";

        $queryUpdate = "UPDATE tb_user SET email = :email, waktu_update = :waktu WHERE `user_id` = :id AND `password` = MD5(:pass) ";

        $stmt = $this->db->prepare($queryEmail);
        if($stmt->execute([':email' => $email])){
            $result = $stmt->fetch();
            $row_telepon = $result['email'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Email telah terdaftar!"]);
            }
        }

        $stmtUpdate = $this->db->prepare($queryUpdate);
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            $rowUpdate = $result['waktu_update'];
            // return $rowUpdate;
            if ($result) {
              if (empty($rowUpdate)) {
                if ($stmtUpdate->execute([':id' => $id, ':email' => $email,
                ':pass' => $password, ':waktu' => $timeUpdate])) {
                    return $response->withJson(["code"=>200, "msg"=>"Update email berhasil!"]);
                }
            }else if ($rowUpdate <= $timeParam) {
                if ($stmtUpdate->execute([':id' => $user_id, ':email' => $email,
                ':pass' => $password, ':waktu' => $timeUpdate])) {
                    return $response->withJson(["code"=>200, "msg"=>"Update email berhasil!"]);
                }
            }else if ($rowUpdate > $timeParam) {
                return $response->withJson(["code"=>201, "msg"=>"Email dan nomor telepon hanya bisa diganti 2 hari sekali!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
    }
    return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
    });


    $app->post('/user/update_telepon', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $password    = $request->getParsedBodyParam('password');
        $telepon     = $request->getParsedBodyParam('telepon');

        $timeParam   = date('Y-m-d H:i:s', time()+2*24*60*60);
        $timeUpdate  = date('Y-m-d H:i:s', time());

        // return $timeParam;

        if (empty($id)||empty($token_login)||empty($password)||empty($telepon)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $queryTelepon = "SELECT * FROM tb_user WHERE telepon = :telepon";
        $query = "SELECT `user_id`, token_login, waktu_update 
        FROM tb_user WHERE `user_id` = :id AND token_login = :token";

        $queryUpdate = "UPDATE tb_user SET telepon = :telepon, waktu_update = :waktu WHERE `user_id` = :id AND `password` = MD5(:pass) ";

        $stmt = $this->db->prepare($queryTelepon);
        if($stmt->execute([':telepon' => $telepon])){
            $result = $stmt->fetch();
            $row_telepon = $result['telepon'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Nomor telepon telah terdaftar!"]);
            }
        }

        $stmtUpdate = $this->db->prepare($queryUpdate);
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            $rowUpdate = $result['waktu_update'];
            // return $rowUpdate;
            if ($result) {
              if (empty($rowUpdate)) {
                if ($stmtUpdate->execute([':id' => $id, ':telepon' => $telepon,
                ':pass' => $password, ':waktu' => $timeUpdate])) {
                    return $response->withJson(["code"=>200, "msg"=>"Update telepon berhasil!"]);
                }
            }else if ($rowUpdate > $timeParam) {
                if ($stmtUpdate->execute([':id' => $id, ':telepon' => $telepon,
                ':pass' => $password, ':waktu' => $timeUpdate])) {
                    return $response->withJson(["code"=>200, "msg"=>"Update telepon berhasil!"]);
                }
            }else if ($rowUpdate < $timeParam) {
                return $response->withJson(["code"=>201, "msg"=>"Email dan nomor telepon hanya bisa diganti 2 hari sekali!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
    }
    return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
    });

    
    $app->post('/user/update_foto', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $nama           = $request->getParsedBodyParam('nama');
        $uploadedFiles  = $request->getUploadedFiles();

        if (empty($id) || empty($token_login)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }

        $queryCheck = "SELECT foto FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $stmt = $this->db->prepare($queryCheck);
        if($stmt->execute([':id' => $id, ':token' => $token_login])){
            $result     = $stmt->fetch();
            $rowFoto    = $result['foto'];
            if ($rowFoto <> null) {
                $directory = $this->get('settings')['upload_customer'];
                unlink($directory.'/'.$rowFoto);
            }
        }

        $sql_uuid = "SELECT UUID() as uuid";
        $stmt_uuid = $this->db->prepare($sql_uuid);
        $stmt_uuid->execute();
        $uuid = $stmt_uuid->fetchColumn(0);

        $uploadedFile = $uploadedFiles['foto'];

        if($uploadedFile->getError()===UPLOAD_ERR_OK){
            $exetension = pathinfo($uploadedFile->getClientFilename(),PATHINFO_EXTENSION);
            $file_name = sprintf('%s.%0.8s', $uuid.$nama, $exetension);
            $directory = $this->get('settings')['upload_customer'];
            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $file_name);
        
          $sql = "UPDATE tb_user set foto= :foto WHERE `user_id` = :id AND token_login = :token_login";
          }
        
          $stmt = $this->db->prepare($sql);
          if($stmt->execute([':id' => $id, ':foto' => $file_name, ':token_login' => $token_login])){
            return $response->withJson(["code"=>200, "msg"=>"Foto berhasil di update!"]);
          }
          return $response->withJson(["code"=>201, "msg"=>"Foto gagal di update!"]);

    });

    //list bank
    $app->get('/list_bank/', function($request, $response){
        $sql = "SELECT * FROM `tb_bank`";
        $stmt = $this->db->prepare($sql);
        if ($stmt->execute()) {
            $result = $stmt->fetchAll();
            if ($result) {
                return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "data" => $result]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });

    //pilih tukang
    $app->post('/user/select_worker', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $anggota     = $request->getParsedBodyParam('anggota');
        $kota        = $request->getParsedBodyParam('kota');
        $kecamatan   = $request->getParsedBodyParam('kecamatan');
        $page        = $request->getParsedBodyParam('page');
        $page_count  = ($page-1)*30;
        $condition   = null;

        if (empty($id)||empty($token_login)||empty($anggota)||empty($kota)||empty($kecamatan)||empty($page)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        if ($anggota === '1') {
            $condition = "tb_tukang.anggota = $anggota";
        }else{
            $condition = "tb_tukang.anggota >= $anggota";
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryGetCount = "SELECT
        tb_tukang.tukang_id,
        tb_tukang.nama,
        tb_tukang.email,
        tb_tukang.telepon,
        tb_tukang.anggota,
        tb_tukang.foto,
        tb_tukang.rating,
        (SELECT GROUP_CONCAT(CONCAT(alamat_tukang.alamat, ', ', alamat_tukang.kecamatan, ', ',
        alamat_tukang.kota, ', ', alamat_tukang.provinsi)) FROM alamat_tukang WHERE tukang_id = tb_tukang.tukang_id ) alamat
        FROM
        tb_tukang
        INNER JOIN status_tukang ON tb_tukang.tukang_id = status_tukang.tukang_id
        INNER JOIN alamat_tukang ON tb_tukang.tukang_id = alamat_tukang.tukang_id
        WHERE (status_tukang.aktivasi = '1' AND status_tukang.aktif = '1' AND status_tukang.kerja = '0')
        AND (alamat_tukang.kota = '$kota' OR alamat_tukang.kecamatan = '$kecamatan')
        AND ($condition)";

        $stmt = $this->db->prepare($queryGetCount);
        if($stmt->execute()){
            $result = $stmt->fetchAll();
            if($result){
                $jumlah = count($result);
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $queryGetWorker = "SELECT
        tb_tukang.tukang_id,
        tb_tukang.nama,
        tb_tukang.email,
        tb_tukang.telepon,
        tb_tukang.anggota,
        tb_tukang.foto,
        tb_tukang.rating,
        (SELECT GROUP_CONCAT(CONCAT(alamat_tukang.alamat, ', ', alamat_tukang.kecamatan, ', ',
        alamat_tukang.kota, ', ', alamat_tukang.provinsi)) FROM alamat_tukang WHERE tukang_id = tb_tukang.tukang_id ) alamat
        FROM
        tb_tukang
        INNER JOIN status_tukang ON tb_tukang.tukang_id = status_tukang.tukang_id
        INNER JOIN alamat_tukang ON tb_tukang.tukang_id = alamat_tukang.tukang_id
        WHERE (status_tukang.aktivasi = '1' AND status_tukang.aktif = '1' AND status_tukang.kerja = '0')
        AND (alamat_tukang.kota = '$kota' OR alamat_tukang.kecamatan = '$kecamatan')
        AND ($condition)
        ORDER BY tb_tukang.rating AND tb_tukang.anggota DESC LIMIT 30 OFFSET $page_count";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtGetWorker = $this->db->prepare($queryGetWorker);
                if ($stmtGetWorker->execute()) {
                    $resultWorker = $stmtGetWorker->fetchAll();
                    if ($resultWorker) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "item_count" => $jumlah, "data" => $resultWorker]);
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                    }
                }
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data1!"]);
            }
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data2!"]);
    });

    $app->post('/user/getCurrentPrice', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');

        if (empty($id)||empty($token_login)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryGetPrice = "SELECT max(harga_id) AS id, harga FROM tb_harga GROUP BY harga ORDER BY id DESC";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmt1 = $this->db->prepare($queryGetPrice);
                if ($stmt1->execute()) {
                    $result1 = $stmt1->fetch();
                    if ($result1) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "data" => $result1]);
                    }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!!"]);
            }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!!!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!!!!"]);
    });


    //make order
    $app->post('/user/make_order', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $tukang_id   = $request->getParsedBodyParam('tukang_id');
        // $kode_order  = $request->getParsedBodyParam('kode');
        $alamat      = $request->getParsedBodyParam('alamat');
        $jobdesk     = $request->getParsedBodyParam('jobdesk');
        $start_date  = $request->getParsedBodyParam('start_date');
        $end_date    = $request->getParsedBodyParam('end_date');
        $nominal     = $request->getParsedBodyParam('nominal');
        $angka       = $request->getParsedBodyParam('angka_unik');
        $promo_id    = $request->getParsedBodyParam('promo_id');

        if (empty($id)||empty($token_login)||empty($token_login||
        empty($alamat)||empty($jobdesk)||empty($start_date)||empty($end_date)||empty($nominal))) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }
        
        // $nominal = doubleval($nominall);
        $newStartDate = date('Y-m-d H:i:s', strtotime($start_date));
        $newEndDate   = date('Y-m-d', strtotime($end_date));

        //untuk autentifikasi, 1
        $query = "SELECT `user_id`, token_login, telepon FROM tb_user WHERE `user_id` = :id AND token_login = :token";

        //untuk menambahkan order, 2
        $queryMakeOrder = "INSERT INTO tb_order (`user_id`, `tukang_id`, 
        `alamat`, `jobdesk`, `harga`, angka_unik, `promo_id`, `start_date`, `end_date`) VALUES (:id, :tukang_id, :alamat,
        :jobdesk, :harga, :angka, :promo, :startdate, :enddate)";     

        //untuk mendapatkan id order yang nantinya akan ditambahkan dengan nomor telepon, 3
        $queryIdOrder = "SELECT `user_id`, max(id) AS id FROM tb_order WHERE `user_id` = :id GROUP BY `user_id` ORDER BY id DESC ";

        //update memasukan code order, 6
        $queryUpdateOrder = "UPDATE tb_order set code_order = :code_order WHERE id = :id AND `user_id` = :u_id";

        //update status tukang
        $queryStatusTukang = "UPDATE status_tukang set kerja = '1' WHERE tukang_id = :id";

        $queryGetStatusTukang = "SELECT kerja FROM status_tukang WHERE tukang_id = :id";

        $queryGetOrderr = "SELECT promo_id, status_order FROM tb_order WHERE `user_id` = :id AND status_order = '1'";

        $queryCheck     = "SELECT status_pembayaran FROM tb_pembayaran WHERE `user_id` = :id";

        $stmt = $this->db->prepare($queryCheck);
        if ($stmt->execute([':id' => $id])) {
            $hasil = $stmt->fetch();
            $rowStatusP = $hasil['status_pembayaran'];
            if ($hasil) {
                if ($rowStatusP != '0' && $rowStatusP != '2'  && $rowStatusP != '1') {
                    return $response->withJson(["code"=>201, "msg"=>"Selesaikan pembayaran terlebih dahulu!"]); 
                }
                }
            }

        $stmt = $this->db->prepare($queryGetOrderr);
        if ($stmt->execute([':id' => $id])) {
            $hasil = $stmt->fetch();
            if ($hasil) {
                return $response->withJson(["code"=>201, "msg"=>"Order sebelumnya belum dikonfirmasi pekerja!"]); 
                }
            }

        $stmt = $this->db->prepare($queryGetStatusTukang);
        if ($stmt->execute([':id' => $tukang_id])) {
            $hasil = $stmt->fetch();
            $rowStatus = $hasil['kerja'];
            if ($rowStatus === '1') {
                return $response->withJson(["code"=>201, "msg"=>"Pekerja sedang bekerja!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            $rowTelepon = $result['telepon'];
            if ($result) {
                $stmtInsertOrder = $this->db->prepare($queryMakeOrder);
                if ($stmtInsertOrder->execute([':id' => $id, ':tukang_id' => $tukang_id, ':alamat' => $alamat,
                ':jobdesk' => $jobdesk, ':harga' => $nominal, ':angka' => $angka, ':promo' => $promo_id,
                ':startdate' => $newStartDate, ':enddate' => $newEndDate])) {
                    $stmtSelectOrder = $this->db->prepare($queryIdOrder);
                    if ($stmtSelectOrder->execute([':id' => $id])) {
                        $stmtKerja = $this->db->prepare($queryStatusTukang);
                        $stmtKerja->execute([':id' => $tukang_id]);  
                        $result2 = $stmtSelectOrder->fetch();
                        $rowIdOrder = $result2['id'];
                        if ($result2) {
                            $code_order = "OR".$rowTelepon.$rowIdOrder;
                            $stmtUpdateOrder = $this->db->prepare($queryUpdateOrder);
                            if ($stmtUpdateOrder->execute([':code_order' => $code_order, ':u_id' => $id, ':id' => $rowIdOrder])) {
                                return $response->withJson(["code"=>200, "msg"=>"Order berhasil!"]); 
                            }else{
                                return $response->withJson(["code"=>201, "msg"=>"Update order gagal!"]);
                            }
                        }else{
                            return $response->withJson(["code"=>201, "msg"=>"Select order gagal!"]);
                        }
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Select order gagal!"]);
                    }
                }else{
                    return $response->withJson(["code"=>201, "msg"=>"Insert order gagal!"]);
                }
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    }
    });


    //cancel order
    $app->post('/user/cancel_order', function($request, $response){
        $user_id     = $request->getParsedBodyParam('user_id');
        $token_login = $request->getParsedBodyParam('token_login');
        $order_id    = $request->getParsedBodyParam('order_id');
        $tukang_id   = $request->getParsedBodyParam('tukang_id');

        $query          =   "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryCheck     =   "SELECT id, status_order FROM tb_order WHERE `id` = :order_id AND `user_id` = :id";
        $queryCancel    =   "UPDATE
                            tb_order
                            INNER JOIN status_tukang ON tb_order.tukang_id = status_tukang.tukang_id
                            SET tb_order.status_order = '0', status_tukang.kerja = '0'
                            WHERE tb_order.status_order = '1' AND status_tukang.tukang_id = :tukang_id AND tb_order.id = :order";
        $queryCancel2   =   "UPDATE
                            tb_order
                            INNER JOIN tb_pembayaran ON tb_order.id = tb_pembayaran.order_id
                            INNER JOIN status_tukang ON tb_order.tukang_id = status_tukang.tukang_id
                            SET tb_order.status_order = '0', tb_pembayaran.status_pembayaran = '4', status_tukang.kerja = '0'
                            WHERE (tb_pembayaran.status_pembayaran = '0' OR tb_pembayaran.status_pembayaran = '2')
                            AND tb_order.status_order = '2' AND status_tukang.tukang_id = :tukang_id
                            AND tb_order.id = :order AND tb_order.user_id = :id";

        $stmt = $this->db->prepare($queryCheck);
        $stmtUser = $this->db->prepare($query);
        $stmtUpdate = $this->db->prepare($queryCancel);
        $stmtUpdate2 = $this->db->prepare($queryCancel2);

        if (empty($user_id)||empty($token_login)||empty($order_id)||empty($tukang_id)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        if ($stmtUser->execute([':id' => $user_id, ':token' =>$token_login])) {
            $result1 = $stmtUser->fetch();
            if ($result1) {   
                if ($stmt->execute([':id' => $user_id, ':order_id' => $order_id])) {
                    $result     = $stmt->fetch();
                    $rowStatus  = $result['status_order']; 
                    if ($result && $rowStatus === '1') {
                        if ($stmtUpdate->execute([':tukang_id' => $tukang_id, ':order' => $order_id])) {
                            return $response->withJson(["code"=>200, "msg"=>"Order dibatalkan!"]);
                       }
                       return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
                    }elseif ($result && $rowStatus === '2') {
                        if ($stmtUpdate2->execute([':tukang_id' => $tukang_id, ':id' => $user_id, ':order' => $order_id])) {
                            return $response->withJson(["code"=>200, "msg"=>"Order dibatalkan!"]);
                       }
                       return $response->withJson(["code"=>201, "msg"=>"Pembayran sedang diproses!"]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });


    $app->post('/user/bayar_order', function($request, $response){
        $user_id     = $request->getParsedBodyParam('user_id');
        $token_login = $request->getParsedBodyParam('token_login');
        $payment_id  = $request->getParsedBodyParam('payment_id');
        $order_id    = $request->getParsedBodyParam('order_id');
        $uploadedFiles  = $request->getUploadedFiles();

        if (empty($user_id)||empty($token_login)||empty($order_id)||empty($payment_id)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        if (!$uploadedFiles) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query          = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryCheck     = "SELECT status_pembayaran, bukti_pembayaran FROM tb_pembayaran WHERE `user_id` = :id AND order_id = :order AND id = :payment_id";
        $queryUpdate    = "UPDATE tb_pembayaran SET status_pembayaran = '1', bukti_pembayaran = :foto WHERE id = :payment_id";

        $stmt = $this->db->prepare($queryCheck);       
        $stmtUser = $this->db->prepare($query);
        $stmtUpdate = $this->db->prepare($queryUpdate);        

        $sql_uuid = "SELECT UUID() as uuid";
        $stmt_uuid = $this->db->prepare($sql_uuid);
        $stmt_uuid->execute();
        $uuid = $stmt_uuid->fetchColumn(0);

        $uploadedFile = $uploadedFiles['foto'];

        if ($stmtUser->execute([':id' => $user_id, ':token' =>$token_login])) {
            $result1 = $stmtUser->fetch();
            if ($result1) { 
                if ($stmt->execute([':id' => $user_id, ':order' => $order_id, ':payment_id' => $payment_id])) {
                    $result = $stmt->fetch();
                    $rowStatus = $result['status_pembayaran'];
                    $rowFoto    = $result['bukti_pembayaran'];
                    if ($rowStatus === '0'||$rowStatus === '2') {
                        if ($rowFoto <> null) {
                            $directory = $this->get('settings')['upload_payment'];
                            unlink($directory.'/'.$rowFoto);
                        }
                        if($uploadedFile->getError()===UPLOAD_ERR_OK){
                            $exetension = pathinfo($uploadedFile->getClientFilename(),PATHINFO_EXTENSION);
                            $file_name = sprintf('%s.%0.8s', $uuid.$order_id, $exetension);
                            $directory = $this->get('settings')['upload_payment'];
                            $uploadedFile->moveTo($directory . DIRECTORY_SEPARATOR . $file_name);
                
                            if($stmtUpdate->execute([':payment_id' => $payment_id, ':foto' => $file_name])){
                                return $response->withJson(["code"=>200, "msg"=>"Pembayaran berhasil diproses!"]);
                              }
                              return $response->withJson(["code"=>201, "msg"=>"Pembayaran gagal!"]);
                          }
                          return $response->withJson(["code"=>201, "msg"=>"Pembayaran gagal!"]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Pembayaran gagal!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Pembayaran gagal!"]);
              }
              return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);        
    });


    $app->post('/user/finish_order', function($request, $response){
        $user_id     = $request->getParsedBodyParam('user_id');
        $token_login = $request->getParsedBodyParam('token_login');
        $order_id    = $request->getParsedBodyParam('order_id');
        $tukang_id   = $request->getParsedBodyParam('tukang_id');
        $end_order   = date('Y-m-d H:i:s', time());

        $query          = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        
        $queryFinish    =  "UPDATE
                            tb_order
                            INNER JOIN status_tukang ON tb_order.tukang_id = status_tukang.tukang_id
                            SET tb_order.status_order = '4', status_tukang.kerja = '0', tb_order.finish_date = :endorder
                            WHERE tb_order.status_order = '3' AND status_tukang.tukang_id = :tukang_id 
                            AND tb_order.id = :order";

        $queryCheck     =   "SELECT id, status_order FROM tb_order WHERE `id` = :order_id AND `user_id` = :id";

        $stmtUser = $this->db->prepare($query);
        $stmtUpdate = $this->db->prepare($queryFinish);
        $stmt = $this->db->prepare($queryCheck);       

        if (empty($user_id)||empty($token_login)||empty($order_id)||empty($tukang_id)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        if ($stmtUser->execute([':id' => $user_id, ':token' =>$token_login])) {
            $result1 = $stmtUser->fetch();
            if ($result1) {   
                if ($stmt->execute([':id' => $user_id, ':order_id' => $order_id])) {
                    $result3     = $stmt->fetch();
                    $rowStatus  = $result3['status_order']; 
                    if ($result3 && $rowStatus === '3') {
                        if ($stmtUpdate->execute([':tukang_id' => $tukang_id, ':order' => $order_id, ':endorder' => $end_order])) {
                            return $response->withJson(["code"=>200, "msg"=>"Order selesai!"]);
                        }
                        return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Order tidak bisa atau sudah diselesaikan!"]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Parameter salah!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });

    $app->post('/user/give_rating', function($request, $response){
        $user_id     = $request->getParsedBodyParam('user_id');
        $token_login = $request->getParsedBodyParam('token_login');
        $order_id    = $request->getParsedBodyParam('order_id');
        $tukang_id   = $request->getParsedBodyParam('tukang_id');
        $komentar    = $request->getParsedBodyParam('komentar');
        $rating      = $request->getParsedBodyParam('rating');

        if (empty($user_id)||empty($token_login)||empty($order_id)||empty($tukang_id)
        ||empty($rating)||empty($komentar)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query          = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryTukang    = "SELECT rating FROM tb_tukang WHERE `tukang_id` = :id";
        $queryRating    = "INSERT INTO tb_penilaian_order (`user_id`, `tukang_id`, `order_id`, rating, komentar)
        VALUES (:userid, :tukangid, :orderid, :rating, :komentar)";
        $queryUpdate    = "UPDATE tb_tukang SET rating = :rating WHERE tukang_id = :tukangid";
        $queryCheckOrder = "SELECT status_order FROM tb_order WHERE id = :orderid";

        $stmtCheck = $this->db->prepare($queryCheckOrder);
        if ($stmtCheck->execute([':orderid' => $order_id])) {
            $result2 = $stmtCheck->fetch();
            $rowStatusOrder = $result2['status_order'];
            if ($rowStatusOrder != '4') {
                return $response->withJson(["code"=>201, "msg"=>"Order belum selesai!"]);
            }
        }

        $stmtGetRating = $this->db->prepare($queryTukang);
        if ($stmtGetRating->execute([':id' => $tukang_id])) {
            $result1 = $stmtGetRating->fetch();
            $rowRating = $result1['rating'];
            if ($rowRating) {
                $sendRating = ($rowRating+$rating)/2;
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $stmtUpdate = $this->db->prepare($queryUpdate);
        $stmtInsert = $this->db->prepare($queryRating);
        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $user_id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {   
                if ($stmtInsert->execute([':userid' => $user_id, ':tukangid' => $tukang_id,
                ':orderid' => $order_id, ':rating' => $rating, ':komentar' => $komentar]) &&
                $stmtUpdate->execute([':tukangid' => $tukang_id, ':rating' => $sendRating])) {
                    return $response->withJson(["code"=>200, "msg"=>"Rating berhasil diupdate!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });

    $app->post('/user/get_promo', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $code        = $request->getParsedBodyParam('kode');
        $nominall     = $request->getParsedBodyParam('nominal');
        $end_pem     = date('Y-m-d H:i:s', time());

        if (empty($id)||empty($token_login)||empty($code)||empty($nominall)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $nominal = doubleval($nominall);

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryPromo = "SELECT * FROM tb_promo WHERE (kode_promo = :kode AND min_harga <= :nominal)
        AND `start_date` <= :waktu AND end_date > :waktu";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {               
                $stmtGetPromo = $this->db->prepare($queryPromo);
                if ($stmtGetPromo->execute([':kode' => $code, ':nominal' => $nominal, ':waktu' => $end_pem])) {
                    $resultPromo = $stmtGetPromo->fetch();
                    if ($resultPromo) {
                        return $response->withJson(["code"=>200, "msg"=>"Promo diperoleh!", "data" => $resultPromo]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Promo tidak berlaku!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Promo tidak ditemukan!"]);
            }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    }
    });

    

    $app->post('/user/list_order', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $page        = $request->getParsedBodyParam('page');
        $page_count  = ($page-1)*30;

        if (empty($id)||empty($token_login)||empty($page)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryListOrder = "SELECT
        tb_order.id,
        tb_order.tukang_id,
        tb_order.code_order,
        tb_order.alamat,
        tb_order.jobdesk,
        tb_order.status_order,
        tb_order.order_date,
        tb_order.start_date,
        tb_order.end_date,
        tb_tukang.nama,
        tb_tukang.telepon,
        tb_tukang.foto,
        tb_tukang.anggota,
        tb_tukang.rating
        FROM
        tb_order
        INNER JOIN tb_tukang ON tb_order.tukang_id = tb_tukang.tukang_id
        WHERE ( tb_order.status_order >=1 AND tb_order.status_order < 4)
        AND (tb_order.user_id = :id)";

        $queryListOrder2 = "SELECT
        tb_order.id,
        tb_order.tukang_id,
        tb_order.code_order,
        tb_order.alamat,
        tb_order.jobdesk,
        tb_order.status_order,
        tb_order.order_date,
        tb_order.start_date,
        tb_order.end_date,
        tb_tukang.nama,
        tb_tukang.telepon,
        tb_tukang.foto,
        tb_tukang.anggota,
        tb_tukang.rating
        FROM
        tb_order
        INNER JOIN tb_tukang ON tb_order.tukang_id = tb_tukang.tukang_id
        WHERE (tb_order.status_order >=1 AND tb_order.status_order < 4)
        AND (tb_order.user_id = :id)
        ORDER BY  tb_order.order_date DESC LIMIT 30 OFFSET $page_count";

        $stmt = $this->db->prepare($queryListOrder);
        if ($stmt->execute([':id' => $id])) {
            $result = $stmt->fetchAll();
            if ($result) {
               $jumlah = count($result);
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtList = $this->db->prepare($queryListOrder2);
                if ($stmtList->execute([':id' => $id])) {
                    $resultList = $stmtList->fetchAll();
                    if ($resultList) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "item_count" => $jumlah, "data" => $resultList]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    }
    });

    $app->post('/user/list_order_history', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $page        = $request->getParsedBodyParam('page');
        $page_count  = ($page-1)*30;

        if (empty($id)||empty($token_login)||empty($page)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryListOrder = "SELECT
        tb_order.id,
        tb_order.tukang_id,
        tb_order.code_order,
        tb_order.alamat,
        tb_order.jobdesk,
        tb_order.status_order,
        tb_order.order_date,
        tb_order.start_date,
        tb_order.end_date,
        tb_tukang.nama,
        tb_tukang.telepon,
        tb_tukang.foto,
        tb_tukang.anggota,
        tb_tukang.rating,
        tb_order.finish_date
        FROM
        tb_order
        INNER JOIN tb_tukang ON tb_order.tukang_id = tb_tukang.tukang_id
        WHERE ( tb_order.status_order = 0 OR tb_order.status_order = 4)
        AND (tb_order.user_id = :id)";

        $queryListOrder2 = "SELECT
        tb_order.id,
        tb_order.tukang_id,
        tb_order.code_order,
        tb_order.alamat,
        tb_order.jobdesk,
        tb_order.status_order,
        tb_order.order_date,
        tb_order.start_date,
        tb_order.end_date,
        tb_tukang.nama,
        tb_tukang.telepon,
        tb_tukang.foto,
        tb_tukang.anggota,
        tb_tukang.rating,
        tb_order.finish_date
        FROM
        tb_order
        INNER JOIN tb_tukang ON tb_order.tukang_id = tb_tukang.tukang_id
        WHERE (tb_order.status_order = 0 OR tb_order.status_order = 4)
        AND (tb_order.user_id = :id)
        ORDER BY  tb_order.order_date ASC LIMIT 30 OFFSET $page_count";

        $stmt = $this->db->prepare($queryListOrder);
        if ($stmt->execute([':id' => $id])) {
            $result = $stmt->fetchAll();
            if ($result) {
               $jumlah = count($result);
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtList = $this->db->prepare($queryListOrder2);
                if ($stmtList->execute([':id' => $id])) {
                    $resultList = $stmtList->fetchAll();
                    if ($resultList) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "item_count" => $jumlah, "data" => $resultList]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    }
    });

    $app->post('/user/list_payment', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $page        = $request->getParsedBodyParam('page');
        $page_count  = ($page-1)*30;

        if (empty($id)||empty($token_login)||empty($page)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryPayment = "SELECT
        tb_pembayaran.id,
        tb_pembayaran.order_id,
        tb_pembayaran.nominal,
        tb_pembayaran.code_pembayaran,
        tb_pembayaran.status_pembayaran,
        tb_pembayaran.bukti_pembayaran,
        tb_pembayaran.create_date,
        tb_pembayaran.end_date,
        tb_pembayaran.tukang_id,
        tb_pembayaran.user_id,
        tb_tukang.anggota
        FROM
        tb_pembayaran
        INNER JOIN tb_tukang ON tb_pembayaran.tukang_id = tb_tukang.tukang_id
        WHERE tb_pembayaran.`user_id` = :id 
        AND (tb_pembayaran.status_pembayaran < 3)";

        $queryPaymentList = "SELECT
        tb_pembayaran.id,
        tb_pembayaran.order_id,
        tb_pembayaran.nominal,
        tb_pembayaran.code_pembayaran,
        tb_pembayaran.status_pembayaran,
        tb_pembayaran.bukti_pembayaran,
        tb_pembayaran.create_date,
        tb_pembayaran.end_date,
        tb_pembayaran.tukang_id,
        tb_pembayaran.user_id,
        tb_tukang.anggota
        FROM
        tb_pembayaran
        INNER JOIN tb_tukang ON tb_pembayaran.tukang_id = tb_tukang.tukang_id
        WHERE tb_pembayaran.`user_id` = :id 
        AND (tb_pembayaran.status_pembayaran < 3)
        ORDER BY tb_pembayaran.`create_date` LIMIT 30 OFFSET $page_count";

          $stmt = $this->db->prepare($queryPayment);
          if ($stmt->execute([':id' => $id])) {
              $result = $stmt->fetchAll();
              if ($result) {
                $jumlah = count($result);   
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtList = $this->db->prepare($queryPaymentList);
                if ($stmtList->execute([':id' => $id])) {
                    $resultList = $stmtList->fetchAll();
                    if ($result) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "item_count" => $jumlah, "data" => $resultList]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });

    $app->post('/user/list_payment_history', function($request, $response){
        $id          = $request->getParsedBodyParam('id');
        $token_login = $request->getParsedBodyParam('token_login');
        $page        = $request->getParsedBodyParam('page');
        $page_count  = ($page-1)*30;

        if (empty($id)||empty($token_login)||empty($page)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query = "SELECT `user_id`, token_login FROM tb_user WHERE `user_id` = :id AND token_login = :token";
        $queryPayment = "SELECT
        tb_pembayaran.id,
        tb_pembayaran.order_id,
        tb_pembayaran.nominal,
        tb_pembayaran.code_pembayaran,
        tb_pembayaran.status_pembayaran,
        tb_pembayaran.bukti_pembayaran,
        tb_pembayaran.create_date,
        tb_pembayaran.end_date,
        tb_pembayaran.tukang_id,
        tb_pembayaran.user_id,
        tb_tukang.anggota
        FROM
        tb_pembayaran
        INNER JOIN tb_tukang ON tb_pembayaran.tukang_id = tb_tukang.tukang_id
        WHERE tb_pembayaran.`user_id` = :id 
        AND ( tb_pembayaran.status_pembayaran > 2)";

        $queryPaymentList = "SELECT
        tb_pembayaran.id,
        tb_pembayaran.order_id,
        tb_pembayaran.nominal,
        tb_pembayaran.code_pembayaran,
        tb_pembayaran.status_pembayaran,
        tb_pembayaran.bukti_pembayaran,
        tb_pembayaran.create_date,
        tb_pembayaran.end_date,
        tb_pembayaran.tukang_id,
        tb_pembayaran.user_id,
        tb_tukang.anggota
        FROM
        tb_pembayaran
        INNER JOIN tb_tukang ON tb_pembayaran.tukang_id = tb_tukang.tukang_id
        WHERE tb_pembayaran.`user_id` = :id 
        AND ( tb_pembayaran.status_pembayaran > 2)
        ORDER BY tb_pembayaran.`create_date` LIMIT 30 OFFSET $page_count";

          $stmt = $this->db->prepare($queryPayment);
          if ($stmt->execute([':id' => $id])) {
              $result = $stmt->fetchAll();
              if ($result) {
                $jumlah = count($result);   
            }else{
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtList = $this->db->prepare($queryPaymentList);
                if ($stmtList->execute([':id' => $id])) {
                    $resultList = $stmtList->fetchAll();
                    if ($result) {
                        return $response->withJson(["code"=>200, "msg"=>"Berhasil mendapatkan data!", "item_count" => $jumlah, "data" => $resultList]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Gagal mendapatkan data!"]);
    });


    

    // $app->post('/user/getwork', function($request, $response){
    //     $id = $request->getParsedBodyParam('id');

    //     $query = "SELECT rating FROM tb_tukang WHERE tukang_id = $id";

    //     $stmt = $this->db->prepare($query);
    //     if ($stmt->execute()) {
    //         $result = $stmt->fetch();
    //         $rowRating = $result['rating'];
    //         $jumlah = $rowRating / 2;
    //         echo $jumlah;
    //     }
    // });

    // to get current time indonesia
    // $app->get('/user/update_email/', function($request, $response){
        //get current timestamp
        // $date = date('m/d/Y h:i:s a', time());
        //timestamp+2
        // $date = date('m/d/Y h:i:s a', time()+2*60*60);
        // return $date;
        // $int = 3;
        // $string = "asdf";
        // $connect = $string.$int;
        // return $connect;
        // $time = strtotime('10/16/2003  10:03');

    
    // $time = $request->getParsedBodyParam('time');
    // $newformat = date('Y-m-d H:i:s',strtotime($time));
    // // $ymd = DateTime::createFromFormat('Y-m-d H:i:s', $time)->format('Y-m-d H:i:s');

    //     $query = "INSERT INTO tb_nomer (`row_number`) VALUES ('$newformat')";

    //     $stmt = $this->db->prepare($query);
    //     if ($stmt->execute()) {
           
    //     }
    
    // });





     //---------------------------------------------------------------------------------------------------------//
    //------------------------------------------------TUKANG API-----------------------------------------------//
   //---------------------------------------------------------------------------------------------------------//







    $app->post('/tukang/register_tukang', function($request, $response){
        $nama       = $request->getParsedBodyParam('nama');
        $email      = $request->getParsedBodyParam('email');
        $telepon    = $request->getParsedBodyParam('telepon');
        $password   = $request->getParsedBodyParam('password');
        $nik        = $request->getParsedBodyParam('nik');
        $anggota    = $request->getParsedBodyParam('anggota');

        $provinsi   = $request->getParsedBodyParam('provinsi');
        $kota       = $request->getParsedBodyParam('kota');
        $kec        = $request->getParsedBodyParam('kec');
        $alamat     = $request->getParsedBodyParam('alamat');
        $bank       = $request->getParsedBodyParam('bank');
        $rekening   = $request->getParsedBodyParam('rekening');

        $queryTelp = "SELECT telepon FROM tb_tukang WHERE telepon = :telepon";

        $queryEmail = "SELECT email FROM tb_tukang WHERE email = :email";

        $queryNIK = "SELECT nik FROM tb_tukang WHERE nik = :nik";

        $queryTukang = "INSERT INTO tb_tukang (nama, email, telepon, nik, anggota, `password`)
        VALUES (:nama, :email, :telepon, :nik, :anggota, MD5(:password))";

        $queryAlamat = "INSERT INTO alamat_tukang (tukang_id, provinsi, kota, kecamatan, alamat) 
        VALUES (:tukang_id, :provinsi, :kota, :kecamatan, :alamat)";

        $queryStatus = "INSERT INTO status_tukang(tukang_id) VALUES (:tukang_id)";
        $queryRekening = "INSERT INTO rekening_tukang(tukang_id, bank, rekening) VALUES (:tukang_id, :bank, :rekening)";


        $queryID = "SELECT telepon, max(tukang_id) AS id FROM tb_tukang WHERE `telepon` = :telepon GROUP BY telepon ORDER BY id DESC ";

        if(empty($telepon)||empty($nama)||empty($email)||empty($password)||empty($nik)||empty($anggota)
        ||empty($provinsi)||empty($kota)||empty($kec)||empty($alamat)||empty($bank)||empty($rekening)){
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $stmt = $this->db->prepare($queryTelp);
        if($stmt->execute([':telepon' => $telepon])){
            $result = $stmt->fetch();
            $row_telepon = $result['telepon'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Email atau nomor telepon telah terdaftar!"]);
            }
        }

        $stmt = $this->db->prepare($queryEmail);
        if($stmt->execute([':email' => $email])){
            $result = $stmt->fetch();
            $row_telepon = $result['email'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"Email atau nomor telepon telah terdaftar!"]);
            }
        }

        $stmt = $this->db->prepare($queryNIK);
        if($stmt->execute([':nik' => $nik])){
            $result = $stmt->fetch();
            $row_telepon = $result['nik'];
            if($row_telepon <> null){
                return $response->withJson(["code"=>201, "msg"=>"NIK telah terdaftar!"]);
            }
        }

        $stmt = $this->db->prepare($queryTukang);
        if($stmt->execute([':nama' => $nama, ':email' => $email, ':telepon' => $telepon,
        ':nik' => $nik, ':anggota' => $anggota, ':password' => $password])){
            $stmtID = $this->db->prepare($queryID);
            if($stmtID->execute([':telepon' => $telepon])){
                $result = $stmtID->fetch();
                $rowID  = $result['id'];
                if ($result) {
                    $stmtAlamat = $this->db->prepare($queryAlamat);
                    $stmtStatus = $this->db->prepare($queryStatus);
                    $stmtRekening = $this->db->prepare($queryRekening);
                    if ($stmtAlamat->execute([':tukang_id' => $rowID, ':provinsi' => $provinsi,
                    ':kota' => $kota, ':kecamatan' => $kec, ':alamat' => $alamat])
                     && $stmtStatus->execute([':tukang_id' => $rowID])
                     && $stmtRekening->execute([':tukang_id' => $rowID, ':bank' => $bank, ':rekening' =>$rekening])) {
                        return $response->withJson(["code"=>200, "msg"=>"Register berhasil!"]);
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Alamat gagal!"]);
                    }
                }else{
                    return $response->withJson(["code"=>201, "msg"=>"ID gagal!"]);
                }
            }else{
                return $response->withJson(["code"=>201, "msg"=>"ID gagal!"]);
            }
        }
        return $response->withJson(["code"=>201, "msg"=>"Register gagal!"]);
    });

    $app->post('/tukang/login_tukang', function($request, $response){
        $email      = $request->getParsedBodyParam('email');
        $password   = $request->getParsedBodyParam('password');
        $token      = hash('sha256', md5(date('Y-m-d H:i:s'),$email)) ;
        $param = "1";

        $query = "SELECT
        tb_tukang.tukang_id,
        tb_tukang.nama,
        tb_tukang.email,
        tb_tukang.telepon,
        tb_tukang.nik,
        tb_tukang.anggota,
        tb_tukang.foto,
        tb_tukang.token_login,
        tb_tukang.rating,
        status_tukang.login,
        status_tukang.aktivasi,
        (SELECT GROUP_CONCAT(CONCAT(alamat_tukang.alamat, ', ', alamat_tukang.kecamatan, ', ',
        alamat_tukang.kota, ', ', alamat_tukang.provinsi)) FROM alamat_tukang WHERE tukang_id = tb_tukang.tukang_id ) alamat
        FROM
        tb_tukang
        INNER JOIN status_tukang ON tb_tukang.tukang_id = status_tukang.tukang_id
        WHERE  tb_tukang.email = :email AND  tb_tukang.`password` = MD5(:password)";

        $queryUpdate = "UPDATE status_tukang set `login` = $param WHERE tukang_id = :id ";
        $queryToken = "UPDATE tb_tukang set token_login = :token WHERE tukang_id = :id ";

        if (empty($email) || empty($password)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }


        $stmt = $this->db->prepare($query);
        if($stmt->execute([':email' => $email, ':password' => $password])){
            $result = $stmt->fetch();
            $rowID      = $result['tukang_id'];
            $rowIsLogin = $result['login'];
            $rowAktivasi = $result['aktivasi'];
            if ($result) {
                if($rowIsLogin === "0" && $rowAktivasi === "1"){
                    $stmtStatus = $this->db->prepare($queryUpdate);
                    $stmtToken  = $this->db->prepare($queryToken);
                    if ($stmtStatus->execute([':id' => $rowID]) &&
                     $stmtToken->execute([':token' => $token, ':id' => $rowID])) {
                        return $response->withJson(["code"=>200, "msg"=>"Login berhasil!","data" => $result]);
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Login gagal update status!"]);
                    }
                }else{
                    if ($rowAktivasi === "0") {
                        return $response->withJson(["code"=>201, "msg"=>"Mohon aktivasi akun anda ke kantor BURUH ID terdekat!"]);
                    }else{
                        return $response->withJson(["code"=>201, "msg"=>"Anda telah login diperangkat tertentu!"]);
                    }
                }
            }else{
                    return $response->withJson(["code"=>201, "msg"=>"Email atau password salah!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Email atau password salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Email atau password salah!"]);
    });

    $app->post('/tukang/update_firebase_token', function($request, $response){
        $id             = $request->getParsedBodyParam('id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $token_firebase = $request->getParsedBodyParam('token_firebase');

        if (empty($id) || empty($token_login) || empty($token_firebase)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi data!"]);
        }

        $query = "UPDATE tb_tukang set token_firebase = :firebase WHERE `tukang_id` = :id AND `token_login` = :token_login";

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':firebase' => $token_firebase, ':id' => $id, ':token_login' => $token_login])) {
            return $response->withJson(["code"=>200, "msg"=>"Update token berhasil!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Update token gagal!"]);
    });

    $app->post('/tukang/respon_order', function($request, $response){
        $user_id        = "";
        $tukang_id      = $request->getParsedBodyParam('tukang_id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $order_id       = $request->getParsedBodyParam('order_id');
        $status         = $request->getParsedBodyParam('status');
        $harga          = "";
        $end_pem        = date('Y-m-d H:i:s', time()+2*60*60);

        $nominal = "";

        if (empty($tukang_id)||empty($token_login)||empty($order_id)||$status > 2) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $queryUser      = "SELECT telepon FROM tb_user WHERE `user_id` = :id";
        $queryGetOrderr = "SELECT promo_id, status_order, `user_id`, harga, angka_unik  FROM tb_order WHERE `id` = :id";
        $queryGetPromo  = "SELECT isi_promo FROM tb_promo WHERE `id` = :id";
        $query          = "SELECT `tukang_id`, token_login FROM tb_tukang WHERE `tukang_id` = :id AND token_login = :token";
        $updateRespon   = "UPDATE tb_order SET status_order = :status_order WHERE tukang_id = :id AND id = :order";
        $queryPayment   = "INSERT INTO tb_pembayaran (`order_id`, `user_id`, `tukang_id`,
        nominal, end_date) VALUES (:order_id, :id, :tukang_id, :nominal, :enddate)";

        $queryIdPembayaran     = "SELECT `user_id`, max(id) AS id FROM tb_pembayaran WHERE `user_id` = :id GROUP BY `user_id` ORDER BY id DESC ";
        $queryUpdatePembayaran = "UPDATE tb_pembayaran SET code_pembayaran = :code_pembayaran WHERE id = :id AND `user_id` = :u_id";

        $queryStatusTukang = "UPDATE status_tukang set kerja = :kerja WHERE tukang_id = :id";

        $stmt = $this->db->prepare($queryGetOrderr);
        if ($stmt->execute([':id' => $order_id])) {
            $hasil = $stmt->fetch();
            $user_id = $hasil['user_id'];
            $harga = $hasil['harga'];
            $rowStart = $hasil['status_order'];
            $rowPromo = $hasil['promo_id'];
            $rowAngka = $hasil['angka_unik'];
            if ($hasil) {
                if ($rowStart != '1') {
                    return $response->withJson(["code"=>201, "msg"=>"Update Status gagal!"]); 
                }else{
                    if (!empty($rowPromo)) {
                        $stmtP = $this->db->prepare($queryGetPromo);
                        if ($stmtP->execute([':id' => $rowPromo])) {
                            $result     = $stmtP->fetch();
                            $isiPromo   = $result['isi_promo'];
                            $nominal    = $harga-($harga*$isiPromo)+$rowAngka;
                        }
                    }else{
                        $nominal = $harga+$rowAngka;
                    }
                }       
            }
        }

        $stmt = $this->db->prepare($query);
        if ($stmt->execute([':id' => $tukang_id, ':token' =>$token_login])) {
            $result = $stmt->fetch();
            if ($result) {
                $stmtUpOrder    = $this->db->prepare($updateRespon);
                $stmtUpOrder->execute([':status_order' => $status, ':id' => $tukang_id, ':order' => $order_id]);
                if ($status === '2') {
                    $stmtInsertPembayaran = $this->db->prepare($queryPayment);
                    if ($stmtInsertPembayaran->execute([':order_id' => $order_id, ':id' => $user_id, ':tukang_id' => $tukang_id,
                        ':nominal' => $nominal, ':enddate' => $end_pem])) {
                        $stmtTel    = $this->db->prepare($queryUser);
                        $stmtIdPem  = $this->db->prepare($queryIdPembayaran);
                        $stmtUpPem  = $this->db->prepare($queryUpdatePembayaran);
                        $stmtTel->execute([':id' => $user_id]);
                        $stmtIdPem->execute([':id' => $user_id]);
                        $result2    = $stmtTel->fetch();
                        $rowTelepon = $result2['telepon'];                           
                        $result3    = $stmtIdPem->fetch();
                        $rowIdPem   = $result3['id'];
                        $code_pembayaran = "TR".$rowTelepon.$rowIdPem;
                        if ($stmtUpPem->execute([':code_pembayaran' => $code_pembayaran, ':id' => $rowIdPem, ':u_id' => $user_id])) {
                            return $response->withJson(["code"=>200, "msg"=>"Order diterima!"]); 
                        }else{
                            return $response->withJson(["code"=>201, "msg"=>"Update pembayaran gagal!"]); 
                        }    
                    }else{
                         return $response->withJson(["code"=>201, "msg"=>"Insert pembayaran gagal!"]); 
                    }
                }elseif($status === '0'){
                    $stmtKerja = $this->db->prepare($queryStatusTukang);
                    if ($stmtKerja->execute([':id' => $tukang_id, ':kerja' => $status])) {
                        return $response->withJson(["code"=>200, "msg"=>"Order ditolak!"]);
                    }   
                }else{
                    return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
                }
            }
            return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Input salah!"]);
    });



    $app->post('/tukang/start_working', function($request, $response){
        // $user_id        = $request->getParsedBodyParam('user_id');
        $tukang_id      = $request->getParsedBodyParam('tukang_id');
        $token_login    = $request->getParsedBodyParam('token_login');
        $order_id       = $request->getParsedBodyParam('order_id');
        $start_date     = date('Y-m-d H:i:s', time());

        if (empty($tukang_id)||empty($token_login)||empty($order_id)) {
            return $response->withJson(["code"=>201, "msg"=>"Lengkapi Data"]);
        }

        $query          = "SELECT `tukang_id`, token_login FROM tb_tukang WHERE `tukang_id` = :id AND token_login = :token";
        $updateRespon   = "UPDATE tb_order SET status_order = '3' WHERE tukang_id = :id AND id = :order";
        $queryGetOrderr = "SELECT
                            tb_order.start_date,
                            tb_order.status_order,
                            tb_pembayaran.status_pembayaran
                            FROM
                            tb_order
                            INNER JOIN tb_pembayaran ON tb_order.id = tb_pembayaran.order_id
                            WHERE tb_pembayaran.status_pembayaran = '3' 
                            AND tb_order.status_order = '2' AND tb_order.id = :id";
                            
        $queryStatusTukang = "UPDATE status_tukang set kerja = '2' WHERE tukang_id = :id";

        $stmtTukang = $this->db->prepare($query);
        if ($stmtTukang->execute([':id' => $tukang_id, ':token' => $token_login])) {
            $result1 = $stmtTukang->fetch();
            if ($result1) {
                $stmt = $this->db->prepare($queryGetOrderr);
                if ($stmt->execute([':id' => $order_id])) {
                    $result = $stmt->fetch();
                    $rowStart = $result['start_date'];
                    if ($result && $rowStart <= $start_date) {
                        $stmtStart = $this->db->prepare($updateRespon);
                        $stmtStart->execute([':id' => $tukang_id, ':order' => $order_id]);
                        $stmtStat = $this->db->prepare($queryStatusTukang);
                        $stmtStat->execute([':id' => $tukang_id]);
                        return $response->withJson(["code"=>200, "msg"=>"Pekerjaan dimulai!"]);
                    }elseif($result && $rowStart >= $start_date){
                        return $response->withJson(["code"=>201, "msg"=>"Belum waktunya!"]);
                    }
                    return $response->withJson(["code"=>201, "msg"=>"Input Salah!"]);
                }
                return $response->withJson(["code"=>201, "msg"=>"Input Salah!"]);
            }
            return $response->withJson(["code"=>201, "msg"=>"Input Salah!"]);
        }
        return $response->withJson(["code"=>201, "msg"=>"Input Salah!"]);        
    }); 

};
