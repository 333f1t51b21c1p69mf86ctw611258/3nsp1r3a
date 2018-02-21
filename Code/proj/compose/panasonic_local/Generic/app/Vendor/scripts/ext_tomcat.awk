{
    if( $7 ~ /frameset/ ){
        ts_tmp = $4;
        sub(/\[/,"", ts_tmp);
        split(ts_tmp, ts, ":");
        # first is year
        split(ts[1], date, "/");
        if( date[2]=="Jan" ){ date[2]="01"; }
        else if( date[2]=="Feb" ){ date[2]="02"; }
        else if( date[2]=="Mar" ){ date[2]="03"; }
        else if( date[2]=="Apr" ){ date[2]="04"; }
        else if( date[2]=="May" ){ date[2]="05"; }
        else if( date[2]=="Jun" ){ date[2]="06"; }
        else if( date[2]=="Jul" ){ date[2]="07"; }
        else if( date[2]=="Aug" ){ date[2]="08"; }
        else if( date[2]=="Sep" ){ date[2]="09"; }
        else if( date[2]=="Oct" ){ date[2]="10"; }
        else if( date[2]=="Nov" ){ date[2]="11"; }
        else if( date[2]=="Dec" ){ date[2]="12"; }
        printf( "%s/%s/%s %s:%s:%s %s %s\n", date[3], date[2], date[1], ts[2], ts[3], ts[4], $1, $7 );
    }
}
    
