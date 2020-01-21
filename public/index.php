 <?php
use DI\Container;
use Slim\Factory\AppFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use web\Security;
use Slim\Psr7\Request;
use Slim\Psr7\Response;
require '../vendor/autoload.php';
require '../classes/Security.php';

// Create container
$container = new Container(); 
AppFactory::setContainer($container);

// Add Twig helper into the container
$container->set('view', function () {
    return new Twig('../template');
});

// Add Medoo-based database into the container
$container->set('db', function () {
    include_once('../config/database.php');
    return new \Medoo\Medoo($database_config);
});

// Add session helper into the container
$container->set('session', function () {
    return new \SlimSession\Helper();
});

// Create the app
$app = AppFactory::create();

// Add the session middleware
$app->add(new \Slim\Middleware\Session([
  'autorefresh' => true,
  'lifetime' => '1 hour'
]));

// Add twig Twig-View middleware
$app->add(TwigMiddleware::createFromContainer($app));

// ANGGOTA BARU
$app->get('/anggota/signup', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');
  
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'anggota-signup.html',
  ]);
});

// SAVE ANGGOTA BARU
$app->post('/anggota/signup', function (Request $request,Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  // for order no generator, please see web\Security.php
  $anggota = [
    'id' => $form_data["id-anggota"],
    'nama' => $form_data["nama-anggota"],
    'alamat' => $form_data["alamat"],
    'no_telepon' => $form_data["phone"]
  ];
  $db->insert('anggota', $anggota);

  // mengambil data dari database
  $members = $db->select("anggota", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'anggota.html',
    'members' => $members
  ]);
});

// SAVE ANGGOTA BARU API
$app->post('/api/anggota/signup', function (Request $request,Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  // for order no generator, please see web\Security.php
  $anggota = [
    'id' => $form_data["id-anggota"],
    'nama' => $form_data["nama-anggota"],
    'alamat' => $form_data["alamat"],
    'no_telepon' => $form_data["phone"]
  ];
  
  if($db->insert('anggota', $anggota)) {
    $payload = [
      "status" => 200,
      "message" => "Anggota dengan nama ".$form_data['nama-anggota']." Berhasil Ditambah"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 500,
      "message" => "Gagal Menambah Anggota"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
  }
});

// MENAMPILKAN DATA ANGGOTA JSON
$app->get('/api/anggota', function (Request $request,Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $members = $db->select("anggota", "*");
  if($members) {
    $payload = [
      "status" => 200,
      $members
    ];
    $response
      ->getBody()
      ->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Data Anggota Tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// MENAMPILKAN DATA ANGGOTA HTML
$app->get('/anggota', function (Request $request,Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $members = $db->select("anggota", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'anggota.html',
    'members' => $members,
  ]);
});

// MENGHAPUS ANGGOTA
$app->delete('/anggota/remove/{kode}', function (Request $request,Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('anggota', [ 'id' => $args['kode'] ]);
  if($exists){
    $db->delete("anggota", [ "id" => $args['kode'] ]);
  }

  // mengambil data dari database
  $members = $db->select("anggota", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke anggota.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'anggota.html',
    'members' => $members,
  ]);
});

// MENGHAPUS ANGGOTA API
$app->delete('/api/anggota/remove/{kode}', function (Request $request,Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('anggota', [ 'id' => $args['kode'] ]);

  if($exists){
    $nama = $db->select("anggota", "nama", [ 'id' => $args['kode'] ]);
    if( $db->delete("anggota", [ "id" => $args['kode'] ]) ) {
      $payload = [
        "status" => 200,
        "message" => "Anggota dengan nama ". $nama." Berhasil Dihapus"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } else {
      $payload = [
        "status" => 500,
        "message" => "Gagal Menghapus Anggota"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
    }
  } else {
      $payload = [
        "status" => 404,
        "message" => "Anggota tidak ditemukan"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(404);
  }  
});

//PEMINJAMAN BUKU
$app->get('/buku/borrow/{kode}', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $namaBuku = $db->select("buku", "nama", [ "kode" => $args['kode'] ]);
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi-buku.html',
    'kode' => $args['kode'],
    'judul' => $namaBuku[0]
  ]);
});

//SAVE PEMINJAMAN  BUKU HTML
$app->post('/buku/borrow/{kode}', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $form_data = $request->getParsedBody();
  $stok = $form_data["jumlah-buku"];

  $transactions = [
    'id' => Security::random(10),
    'id_anggota' => $form_data["id-anggota"],
    'kode_buku' => $args['kode'],
    'jenis' => "Buku",
    'jumlah_buku' => $stok,
    'tanggal_transaksi' => date("Y-m-d"),
    'tanggal_kembali' => NULL
  ];
  $db->insert('transaksi', $transactions);

  $db->update('buku',
    [ 'stok[-]' => $stok ],
    [ 'kode' => $args['kode'] ]);

  $trans = $db->select("transaksi", "*");
  $respon =  json_encode($trans);
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi.html',
    'transactions' => $trans
    ]);
});

//SAVE PEMINJAMAN  BUKU API
$app->post('/api/buku/{kode}/borrow', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $form_data = $request->getParsedBody();
  $stok = $form_data["jumlah-buku"];

  $transactions = [
    'id' => Security::random(10),
    'id_anggota' => $form_data["id-anggota"],
    'kode_buku' => $args['kode'],
    'jenis' => "Buku",
    'jumlah_buku' => $stok,
    'tanggal_transaksi' => date("Y-m-d"),
    'tanggal_kembali' => NULL,
  ];
  
  $isMember = $db->select("anggota", "*", ["id_anggota" => $form_data["id-anggota"]]);
  if($isMember) {
    if ($db->insert('transaksi', $transactions) &&   $db->update('buku', [ 'stok[-]' => $stok ], [ 'kode' => $args['kode'] ])) {
      $payload = [
        "status" => 200,
        "message" => "Buku dengan kode ".$args['kode']." Berhasil Dipinjam"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
      $payload = [
        "status" => 500,
        "message" => "Peminjaman Gagal"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  } else {
    $payload = [
      "status" => 404,
      "message" => "Anggota Belum Terdaftar"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// MENGEMBALIKAN BUKU YANG DIPINJAM
$app->get('/transaksi/return/{kode}', function ($request, $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');

  $jlhBuku = $db->select('transaksi',
    "jumlah_buku",
    [ 'id' => $args['kode'] ]);
  $jlh = $jlhBuku[0];
  $kodeBuku = $db->select('transaksi',
    "kode_buku",
    [ 'id' => $args['kode'] ]);
  $code = $kodeBuku[0];
  
  $db->update('transaksi',
    [ 'tanggal_kembali' => date("Y-m-d") ],
    [ 'id' => $args['kode'] ]);

  $db->update('buku',
    [ 'stok[+]' => $jlh ],
    [ 'kode' => $code ]);

  $trans = $db->select("transaksi", "*");
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi.html',
    'transactions' => $trans
  ]);
});

// MENGEMBALIKAN BUKU API
$app->put('/api/transaksi/return/{kode}', function ($request, $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');

  $jlhBuku = $db->select('transaksi',
    "jumlah_buku",
    [ 'id' => $args['kode'] ]);
  $jlh = $jlhBuku[0];
  $kodeBuku = $db->select('transaksi',
    "kode_buku",
    [ 'id' => $args['kode'] ]);
  $code = $kodeBuku[0];
  
  if (
    $db->update('transaksi',
    [ 'tanggal_kembali' => date("Y-m-d") ],
    [ 'id' => $args['kode'] ]) &&    
    $db->update('buku',
      [ 'stok[+]' => $jlh ],
      [ 'kode' => $code ])) {
    $payload = [
      "status" => 200,
      "message" => "Buku dengan kode ".$code." dan nomor transaksi ".$args['kode']." Telah dikembalikan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 500,
      "message" => "Penegmbalian buku gagal"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
  }  
});

// MEMINJAM RUANG DISKUSI HTML
$app->get('/transaksi/ruangan', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $trans = $db->select(
    "transaksi",
    [ "[>]anggota" => ["id_anggota" => "id"]
    ],[
      "transaksi.id",
      "transaksi.id_anggota",
      "anggota.nama",
      "transaksi.tanggal_transaksi"
    ],
    [ "jenis" => "Ruangan" ]
  );

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi-ruangan.html',
    'rooms' => $trans
  ]);
});

// MENAMPILKAN SELURUH RUANG DISKUSI YANG DIPINJAM JSON
$app->get('/api/transaksi/ruangan', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  //mengambil data dari database
  $trans = $db->select(
    "transaksi",
    [ "[>]anggota" => ["id_anggota" => "id"]
    ],[
      "transaksi.id",
      "transaksi.id_anggota",
      "anggota.nama",
      "transaksi.tanggal_transaksi"
    ],
    [ "jenis" => "Ruangan" ]
  );
  if($trans) {
    $payload = [
      "status" => 200,
      $trans
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "TRANSAKSI RUANGAN tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// SAVE DATA TRANSAKSI RUANGAN HTML
$app->post('/transaksi/ruangan', function ($request, $args, $response) {
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();
  
  $id = $form_data["id-anggota"];
  $isMember = $db->select("anggota", "*", [ "id" => $id ]);
  if($isMember) {
    $db->insert('transaksi', [
      'id' => Security::random(10),
      'id_anggota' => $form_data["id-anggota"],
      'kode_buku' => "0",
      'jenis' => "Ruangan",
      'jumlah_buku' => NULL,
      'tanggal_transaksi' => date("Y-m-d"),
      'tanggal_kembali' => NULL
    ]);
  } else {
    echo("Belum");
  }

  $trans = $db->select(
    "transaksi",
    [ "[>]anggota" => ["id_anggota" => "id"]
    ],[
      "transaksi.id",
      "transaksi.id_anggota",
      "anggota.nama",
      "transaksi.tanggal_transaksi"
    ],
    [ "jenis" => "Ruangan" ]
  );

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi-ruangan.html',
    'rooms' => $trans
  ]);
});

// SAVE DATA TRANSAKSI RUANG JSON
$app->get('/api/transaksi/ruangan/{id}', function (Request $request,Response $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();
  
  $id = $args["id"]; 
  $isMember = $db->select("anggota", "*", [ "id" => $id ]);
  if($isMember) {
    $nama = $db->select("anggota", "nama", [ "id" => $id ]);
    if($db->insert("transaksi", ['id_anggota' => $id,'nama_anggota' => $nama[0],'tanggal_pemesanan' => date("Y-m-d H:i:s")])) {
      $payload = [
        "status" => 200,
        "message" => "TRANSAKSI RUANGAN BERHASIL"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
      $payload = [
        "status" => 404,
        "message" => "TRANSAKSI RUANGAN GAGAL"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
    }
  } else {
    $payload = [
      "status" => 500,
      "message" => "Transaksi Ruangan Belum Terdaftar"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
  }  
});

//BUKU BARU
$app->get('/buku/baru', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'buku-baru.html',
  ]);
});      

//SAVE BUKU BARU HTML
$app->post('/buku/baru', function (Request $request, Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');                 
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  // for order no generator, please see web\Security.php
  $books = [
    'kode' => $form_data["kode-buku"],
    'nama' => $form_data["nama-buku"],
    'jenis' => $form_data["jenis-buku"],
    'stok' => $form_data["stok"],
    'ISBN' => $form_data["isbn"],
    'nama_penerbit' => $form_data["nama-penerbit"],
  ];
  // save it permanently
  $db->insert('buku', $books);

  // mengambil data dari database
  $books = $db->select("buku", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'buku.html',
    'books' => $books,
  ]);
});

//SAVE BUKU BARU API
$app->post('/api/buku/baru', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');                 
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  // for order no generator, please see web\Security.php
  $books = [
    'kode' => $form_data["kode-buku"],
    'nama' => $form_data["nama-buku"],
    'jenis' => $form_data["jenis-buku"],
    'stok' => $form_data["stok"],
    'ISBN' => $form_data["isbn"],
    'nama_penerbit' => $form_data["nama-penerbit"],
  ];

  if($db->insert('buku', $books)) {
    $payload = [
      "status" => 200,
      "message" => "Buku dengan kode ".$form_data["kode-buku"]." Telah ditambahkan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 500,
      "message" => "Menambah Buku Gagal"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
  } 
});

// MENAMPILKAN DATA BUKU HTML
$app->get('/buku', function ($request, $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $books = $db->select("buku", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'buku.html',
    'books' => $books,
  ]);
});

//MENAMPILKAN SELURUH DATA BUKU JSON
$app->get('/api/buku', function (Request $request, Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $allbuku = $db->select("buku", "*");
  if($allbuku) {
    $payload = [
      "status" => 200,
      $allbuku
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Buku tidak "
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// MENGHAPUS BUKU
$app->delete('/buku/remove/{kode}', function (Request $request,Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('buku', [ 'kode' => $args['kode'] ]);
  if($exists){
    $db->delete("buku", [ "kode" => $args['kode'] ]);
  }

  // mengambil data dari database
  $books = $db->select("buku", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'buku.html',
    'books' => $books,
  ]);
});

// MENGHAPUS BUKU API
$app->delete('/api/buku/remove/{kode}', function (Request $request,Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('buku', [ 'kode' => $args['kode'] ]);
  if($exists){
    $nama = $db->select("buku", "nama", [ 'kode' => $args['kode'] ]);
    if($db->delete("buku", [ "kode" => $args['kode'] ]) ) {
      $payload = [
        "status" => 200,
        "message" => "Buku dengan judul ". $nama." Berhasil Dihapus"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } else {
      $payload = [
        "status" => 500,
        "message" => "Gagal Menghapus Buku"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
    }
  } else {
    $payload = [
      "status" => 404,
      "message" => "Buku tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(404);
  }  
});

// MENAMPILKAN  DATA TRANSAKSI BUKU
$app->get('/transaksi', function ($request, $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');

  $transactions = $db->select("transaksi", "*");
  $respon = json_encode($transactions);
  for ($i=0;$i<count($transactions); $i++ ) {  
    $newDate = date("d M Y", strtotime($transactions[$i]['tanggal_transaksi']));
    $transactions[$i]['tanggal_transaksi'] = $newDate;
  }

  # Menampilkan data menggunakan interface
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'transaksi.html',
    'transactions' => $transactions
    ]);
});

// MENAMPILKAN DATA TRANSAKSI BUKU API
$app->get('/api/transaksi', function (Request $request, Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $transactions = $db->select("transaksi", "*");
  for ($i=0;$i<count($transactions); $i++ ) {  
    $newDate = date("d M Y", strtotime($transactions[$i]['tanggal_transaksi']));
    $transactions[$i]['tanggal_transaksi'] = $newDate;
  }

  if($transactions) {
    $payload = [
      "status" => 200,
      $transactions
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Transaksi buku tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// LOGIN PENGUNJUNG
$app->get('/', function ($request, $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');
   
  // mengambil data dari tabel pengunjung
  $visitors = $db->select("pengunjung", "*");
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'index.html',
    'logs' => $visitors
  ]);
});

//SAVE PENGUNJUNG HTML
$app->post('/', function (Request $request, Response $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();
  
  $id = $form_data["id-pengunjung"];
  $isMember = $db->select("anggota", "*", [ "id" => $id ]);
  if($isMember) {
    $nama = $db->select("anggota", "nama", [ "id" => $id ]);
    $db->insert("pengunjung", [
      'id_anggota' => $id,
      'nama_anggota' => $nama[0],
      'waktu_kedatangan' => date("Y-m-d H:i:s")
    ]);
  } else {
    echo("Belum");
  }
  
  // mengambil data dari tabel pengunjung
  $visitors = $db->select("pengunjung", "*");
  $respon = json_encode($visitors);
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'index.html',
    'logs' => $visitors
  ]);  
});

//Save Pengunjung JSON
$app->post('/api/pengunjung/{id}', function (Request $request,Response $response, $args) {
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  $id = $args["id"]; 
  $isMember = $db->select("anggota", "*", [ "id" => $id ]);
  if($isMember) {
    $nama = $db->select("anggota", "nama", [ "id" => $id ]);
    if($db->insert("pengunjung", ['id_anggota' => $id,'nama_anggota' => $nama[0],'waktu_kedatangan' => date("Y-m-d H:i:s")])) {
      $payload = [
        "status" => 200,
        "message" => "TAMBAH PENGUNJUNG BERHASIL"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } else {
      $payload = [
        "status" => 500,
        "message" => "TAMBAH PENGUNJUNG GAGAL"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
  } else {
    $payload = [
      "status" => 404,
      "message" => "PENGUNJUNG BELUM TERDAFTAR"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }  
});

// MENAMPILKAN SELURUH LOG PENGUNJUNG RETURN HTML
$app->get('/pengunjung', function (Request $request, Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $visitor = $db->select("pengunjung", "*");

  // mengembalikan data yang diambil untuk ditampilkan ke buku.html
  return $this->get('view')->render($response, 'pengunjung.html', [
    'content' => 'pengunjung.html',
    'visitor' => $visitor,
  ]);
});

// MENAMPILKAN SELURUH LOG PENGUNJUNG JSON
$app->get('/api/pengunjung', function (Request $request, Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $visitor = $db->select("pengunjung", "*");
  if($visitor) {
    $payload = [
      "status" => 200,
      $visitor
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Pengunjung tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// MENAMPILKAN KETERSEDIAAN BUKU (DETAIL BUKU)
$app->get('/detail/buku/{kode}', function ($request, $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari tabel buku
  $book = $db->select(
    "buku", "*",
    [ 'kode' => $args['kode'] ]
  );
  // menampilkan data dalam bentuk JSON
  $respon = json_encode($book[0]);
  
  // menampilkan data ke dalam detail-buku.html
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'detail-buku.html',
    'book' => $book[0]
  ]);
});

//MENAMPILKAN DETAIL BUKU SESUAI ID BUKU JSON
$app->get('/api/buku/detail/{kode}', function (Request $request, Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $namaBuku = $db->select("buku", "*",[ "kode" => $args['kode'] ]);
  if($namaBuku) {
    $payload = [
      "status" => 200,
      $namaBuku
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Buku tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// PUSTAKAWAN BARU
$app->get('/pustakawan/signup', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  return $this->get('view')->render($response, 'template.html', [
    'content' => 'pustakawan-signup.html',
  ]);
});

// SAVE PUSTAKAWAN BARU
$app->post('/pustakawan/signup', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  $pustakawan = [
    'id' => $form_data["id-pustakawan"],
    'jabatan' => $form_data["jabatan"],
    'nama' => $form_data["nama-pustakawan"],
    'alamat' => $form_data["alamat"],
    'no_telepon' => $form_data["phone"]
  ];
  // save it permanently
  $db->insert('pustakawan', $pustakawan);

  // mengambil data dari database
  $workers = $db->select("pustakawan", "*");
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'pustakawan.html',
    'workers' => $workers,
  ]);
});

// SAVE PUSTAKAWAN BARU API
$app->post('/api/pustakawan/signup', function ($request, $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');
  $form_data = $request->getParsedBody();

  $pustakawan = [
    'id' => $form_data["id-pustakawan"],
    'jabatan' => $form_data["jabatan"],
    'nama' => $form_data["nama-pustakawan"],
    'alamat' => $form_data["alamat"],
    'no_telepon' => $form_data["phone"]
  ];

  // save it permanently
  if($db->insert('pustakawan', $pustakawan)) {
    $db->insert('pustakawan', $pustakawan);
    $payload = [
      "status" => 200,
      "message" => "TAMBAH Pustakawan BERHASIL"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 500,
      "message" => "TAMBAH Pustakawan gagal"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
  }
});  

// MENAMPILKAN DATA PUSTAKAWAN
$app->get('/pustakawan', function ($request, $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  // mengambil data dari database
  $workers = $db->select("pustakawan", "*");
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'pustakawan.html',
    'workers' => $workers,
  ]);
});

// MENAMPILKAN DATA PUSTAKAWAN API
$app->get('/api/pustakawan', function (Request $request, Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $workers = $db->select("pustakawan", "*");

  if($workers) {
    $payload = [
      "status" => 200,
      $workers
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
  } else {
    $payload = [
      "status" => 404,
      "message" => "Pustakawan tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
  }
});

// MENGHAPUS PUSTAKAWAN
$app->delete('/pustakawan/remove/{kode}', function (Request $request,Response $response, $args) {
  // koneksi ke database
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('pustakawan', [ 'id' => $args['kode'] ]);
  if($exists){
    $db->delete("pustakawan", [ "id" => $args['kode'] ]);
  }

  $workers = $db->select("pustakawan", "*");
  return $this->get('view')->render($response, 'template.html', [
    'content' => 'pustakawan.html',
    'workers' => $workers,
  ]);
});

// MENGHAPUS PUSTAKAWAN API
$app->delete('/api/pustakawan/remove/{kode}', function (Request $request,Response $response, $args) {
  // get the session and database object from the container
  $session = $this->get('session');
  $db = $this->get('db');

  $exists = $db->has('pustakawan', [ 'id' => $args['kode'] ]);

  if($exists){
    $nama = $db->select("pustakawan", "nama", [ "id" => $args['kode'] ]);
    if(  $db->delete("pustakawan", [ "id" => $args['kode'] ]) ) {
      $payload = [
        "status" => 200,
        "message" => "Pustakawan dengan nama ". $nama." Berhasil Dihapus"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
    } else {
      $payload = [
        "status" => 500,
        "message" => "Gagal Menghapus Pustakawan"
      ];
      $response->getBody()->write(json_encode($payload));
      return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(500);
    }
  } else {
    $payload = [
      "status" => 404,
      "message" => "Pustakawan tidak ditemukan"
    ];
    $response->getBody()->write(json_encode($payload));
    return $response
      ->withHeader('Content-Type', 'application/json')
      ->withStatus(404);
  }
});

// Run the app
$app->run();