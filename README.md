# formatter

Adalah library yang bertugas memformat satu atau banyak object agar berbentuk
sesuai dengan yang dibutuhkan developer.

Library ini mengubah data `text Date` menjadi `Date`, `text Image` menjadi `Media`,
`text Text` menjadi `Text` ( yang memiliki fitur-figur umum ), dan mengisi suatu
kolom dengan sumber data dari database.

Semua konfigurasi formatter disimpan di konfigurasi aplikasi dengan nama `formatter`
yang berisi nama format dan daftar properti serta type nya. Bentuk di bawah adalah
format sederhana dari konfigurasi formatter:

```php
<?php
// ./etc/config.php

return [
    'name' => 'Phun',
    ...
    'formatterOption' => [
    	'objectify' => true,
    	'defaultMedia' => '/media/aa/bb/cc/lorem.jpg'
    ],
    'formatter' => [
        'user' => [
            'avatar'    => 'media',
            'password'  => 'delete',
            'created'   => 'date',
            'validated' => 'boolean',
            'about' => 'text',
            'community' => [
                'type'  => 'chain',
                'model' => 'Community\\Model\\Community',
                'chain'     => [
                    'model'     => 'Community\\Model\\Chain',
                    'object'    => 'user',
                    'parent'    => 'community'
                ],
                'field' => [
                    'name'  => 'name',
                    'type'  => 'text'
                ],
                'format'    => 'community'
            ],
            'page' => [
                'type'      => 'router',
                'router'    => [
                    'for'      => 'siteUser'
                ]
            ],
            'untouched' => [
                'rename' => 'touched'
            ]
        ]
    ]
];
```

Nilai konfigurasi `formatterOption->objectify` bertujuan mengubah nilai yang seharusnya
object dan tidak di `fetch` menjadi object dengan hanya satu property, yaitu `id`.

Formatter ini kemudian bisa digunakan darimana saja dengan memanggil `format` untuk
memformat satu object, atau `formatMany` untuk memformat banyak object sekaligus.

Silahkan mengacu pada wiki untuk penjelasan lebih detail tentang konfigurasi, dan
cara penggunaan.

Contoh di bawah adalah contoh sederhana penggunaan formatter pada kontroler:

```php
...
public function indexAction(){
    $user = User\Model\User::get('id = 1', false);
    $user = \Formatter::format('user', $user, true);
    
    deb($user);
}
```

Walaupun library ini sangat *powerfull*, tapi sebaiknya jangan digunakan terlalu
sering karena pada kondisi tertentu akan menggunakan resource yang cukup berat.
Jika ada kondisi dimana developer perlu memformat type object yang sama berulang-ulang,
sangat disarankan mengelompokan object-object tersebut dalam satu array dan lakukan
format sekali.