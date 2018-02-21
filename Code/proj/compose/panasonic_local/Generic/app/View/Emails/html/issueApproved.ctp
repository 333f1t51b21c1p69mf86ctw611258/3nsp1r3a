<html>
<head>
    <title><?php echo 'Briode: one case was approved';?></title>
</head>
<body>
    Dear:
    <?php 
        $data = $this->getVar('data');
        $recipient_str = '';
        foreach ($data['recipient'] as $r) {
            $recipient_str .= $r.' ';
        }
        echo $recipient_str;
    ?>
<BR>
<BR>
    One case was approved. Please take a look.
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
