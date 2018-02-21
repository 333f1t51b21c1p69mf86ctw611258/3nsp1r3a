<?php
    // following two lines need to be modified for each app
    $appName = 'Generic';
    $appDB = 'genericdata';
    $appRoot = $_SERVER['DOCUMENT_ROOT'].DS.$appName.DS.'app';
   
    $uploadRoot = $appRoot.DS.'uploads';
    $uploadFilebody = 'upload';
    $activeFilebody = 'active';

    $config['Generic'] = array(
        'database' => $appDB,
        'uploadpath' => $uploadRoot,
        'uploadFilenameBody' => $uploadFilebody,
        'uploadXlsFullpath' => $uploadRoot.DS.$uploadFilebody.'.xls',
        'uploadPHPFullpath' => $uploadRoot.DS.$uploadFilebody.'.php',
        'uploadPHPMapperFullpath' => $uploadRoot.DS.$uploadFilebody.'Mapper.php',

        'activeFilenameBody' => $activeFilebody,
        'activeXlsFullpath' => $uploadRoot.DS.$activeFilebody.'.xls',
        'activePHPFullpath' => $uploadRoot.DS.$activeFilebody.'.php',
        'activePHPMapperFullpath' => $uploadRoot.DS.$activeFilebody.'Mapper.php',

        'userListExcelFileFullpath' => $uploadRoot.DS.$uploadFilebody.'Userlist.xlsx',
        'exportXlsFilename' => 'Export.xls',
        'exportOutURL' => 'php://output',
        'reportRoot' => $uploadRoot.DS.'reports',

        'AppRoot'    => $appRoot,
        'ScriptRoot'    => $appRoot.DS.'Vendor'.DS.'scripts'.DS,
        'AttachmentRoot'  => $appRoot.DS.'attachments'.DS,

        'MaxLayer'          => 5,
        'email_from'    => 'admin@briode.com'
    );


