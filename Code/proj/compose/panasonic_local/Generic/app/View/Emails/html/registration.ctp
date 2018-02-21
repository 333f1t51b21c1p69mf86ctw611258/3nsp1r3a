<html>
<head>
    <title><?php echo $title_for_layout;?></title>
</head>
<body>
    Dear:
    <?php 
        $data = $this->getVar('data')['recipient'];
        $recipient_str = '';
        foreach ($data['recipient'] as $r) {
            $recipient_str .= $r.' ';
        }
        echo $recipient_str;
    ?>
<BR>
<BR>
    Please complete the user registration by typing in the folliwng passcode:
    <?php echo $this->getVar('data')['passcode'];?><BR>

</body>
</html>
