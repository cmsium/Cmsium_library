<?php
/** @var array $Masks Массив масок*/
$Masks  = [
    'UserNameAuth' => [
                         'user_name' => ['func' => 'LatinName',
                                         'props' => ['min' => 3,'max'=> 15],
                                         'required' => true],
                         'password' => ['func' => 'LatinName',
                                        'props' => ['min' => 3,'max'=> 15],
                                        'required' => true],
    ],
    'PhoneAuth' => [
                         'phone' => ['func' => 'StrNumbers',
                                               'props' => ['min' => 7,'max'=> 11],
                                               'required' => true],
                         'password' => ['func' => 'LatinName',
                                                  'props' => ['min' => 3,'max'=> 15],
                                                  'required' => true],
    ],
     'MailAuth' => [
                         'e-mail' => ['func' => 'E_Mail',
                                                'props' => [],
                                                'required' => true],
                         'password' => ['func' => 'LatinName',
                                        'props' => ['min' => 3,'max'=> 15],
                                        'required' => true],
     ],
    'StaffRegist' => [
                           'user_name' => ['func' => 'LatinName',
                                           'props' => ['min' => 3,'max'=> 15],
                                           'required' => true],
                           'password' => ['func' => 'LatinName',
                                          'props' => ['min' => 3,'max'=> 15],
                                          'required' => true],
                           'first_name' => ['func' => 'LatinName',
                                            'props' => ['min' => 3,'max'=> 15],
                                            'required' => true],
                           'last_name' => ['func' => 'LatinName',
                                           'props' => ['min' => 3,'max'=> 15],
                                           'required' => true],
                           'middle_name' => ['func' => 'LatinName',
                                             'props' => ['min' => 3,'max'=> 15],
                                             'required' => true],
                           'date_of_birth' => ['func' => 'DateType',
                                               'props' => ['format' => 'd.m.Y', 'output' => 'int'],
                                               'required' => true],
                           'sys_role' => ['func' => 'ValueFromList',
                                           'props' => ['list' => ['admin','user','student']],
                                           'required' => true],
                           'phone' => ['func' => 'StrNumbers',
                                        'props' => ['min' => 7, 'max' => 11],
                                        'required' => true],
                           'e-mail' => ['func' => 'E_Mail',
                                        'props' => [],
                                        'required' => true],
    ],
        'PersonRegist' => [
                           'user_name' => ['func' => 'LatinName',
                                           'props' => ['min' => 3,'max'=> 15],
                                           'required' => true],
                           'password' => ['func' => 'LatinName',
                                           'props' => ['min' => 3,'max'=> 15],
                                           'required' => true],
                           'first_name' => ['func' => 'LatinName',
                                            'props' => ['min' => 3,'max'=> 15],
                                            'required' => true],
                           'last_name' => ['func' => 'LatinName',
                                           'props' => ['min' => 3,'max'=> 15],
                                           'required' => true],
                           'middle_name' => ['func' => 'LatinName',
                                             'props' => ['min' => 3,'max'=> 15],
                                             'required' => true],
                           'date_of_birth' => ['func' => 'DateType',
                                               'props' => ['format' => 'd.m.Y', 'output' => 'int'],
                                               'required' => true],
                           'phone' => ['func' => 'StrNumbers',
                                       'props' => ['min' => 7, 'max' => 11],
                                       'required' => true],
                           'e-mail' => ['func' => 'E_Mail',
                                        'props' => [],
                                        'required' => true],
                           'document_type' => ['func' => 'RangedInt',
                                               'props' => ['min' => 1,'max'=> 6],
                                               'required' => true],
                           'document_number' => ['func' => 'AlphaNumeric',
                                                 'props' => ['min' => 6,'max'=> 10],
                                                 'required' => true],
                           'location_registrated' => ['func' => 'Text',
                                                      'props' => ['min' => 10,'max'=> 100],
                                                      'required' => true],
                           'real_location' => ['func' => 'Text',
                                               'props' => ['min' => 10,'max'=> 100],
                                               'required' => true],

        ]
];

?>