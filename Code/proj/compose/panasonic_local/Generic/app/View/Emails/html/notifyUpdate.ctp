<html>
<head>
    <title><?php echo 'Briode: Update Notiifcation';?></title>
</head>
<body>
    Dear all,
<BR>
<BR>
    Following update(s) were made in Briode system.
<BR>
<BR>
    <?php
        $data = $this->getVar('data');
        $map = $data['contents']['map'];
        foreach( $data['contents']['header'] as $header ){
            $value = $map[$header];
            echo $header. ":&nbsp;". $value."<BR>";
        }
    ?>
<BR>

</body>
</html>
