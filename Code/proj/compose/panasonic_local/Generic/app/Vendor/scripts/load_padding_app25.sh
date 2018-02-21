#! /bin/sh
. ./env.sh
# FIXME: 
# next year is used after Oct
YEAR_NOW=$(date +%Y)
((YEAR_FROM=YEAR_NOW-1))
((YEAR_TO=YEAR_NOW+1))

timestamp=$(date +%y%m%d_%H%M%S)

OUTFILE_H1=/tmp/_sql_padding_h1_"$timestamp".out
OUTFILE_H2=/tmp/_sql_padding_h2_"$timestamp".out
CSVIMPORTFILE=/tmp/_insert_padding_"$timestamp".csv

rm -f /tmp/_sql_padding_*
rm -f /tmp/_insert_padding_*

COLS_FROM="TGBranch,TGCustomer,TGCstcode,TGJobtype,TGyear,TGdate,0,0,0,TGSls" #,'H1','total'"
COLS_FIRST_HALF_YEAR="'H1','total'"
COLS_SECOND_HALF_YEAR="'H2','total'"

IFS=',' read -ra COLS <<< $COLS_FROM
COLS_FROM_A=""
for c in "${COLS[@]}"
do
  if [ -n "$COLS_FROM_A" ]; then
    COLS_FROM_A="$COLS_FROM_A,"
  fi
  if [[ ! $c =~ [0-9\']+ ]]; then
    COLS_FROM_A="$COLS_FROM_A""a.$c"
  else 
    COLS_FROM_A="$COLS_FROM_A$c"
  fi
done

#echo $COLS_FROM_A
#exit 

COLS_TO="SBranch,Customer,Cstcode,Jobtype,Year,Invdate,Amount,Cost,GP,Sls,Half,Entity"
IFS=',' read -ra COLS <<< $COLS_TO
HEADER="\""
for c in "${COLS[@]}"
do
  HEADER="$HEADER$c\",\""
done
HEADER="$HEADER\""
echo $HEADER > $CSVIMPORTFILE

for (( year=$YEAR_FROM; year<=$YEAR_TO; year++ ))
do
    echo "year=$year"
    OUTFILE_H1=/tmp/_sql_padding_h1_"$year"_"$timestamp".out
    OUTFILE_H2=/tmp/_sql_padding_h2_"$year"_"$timestamp".out
    COLS_FROM_A_FIRST="$COLS_FROM_A,"$COLS_FIRST_HALF_YEAR

    $MYSQLPATH/mysql -h $DBHOST -u root -e "select distinct $COLS_FROM_A_FIRST from attrapp25s a left join gpdtl b on a.TGyear=b.Year and a.TGMonth=MONTH(b.Invdate) and a.TGCstcode=b.Cstcode where a.TGyear=$year and a.TGmonth<=6 and b.id is NULL into outfile '$OUTFILE_H1' fields terminated by ',' enclosed by '\"' lines terminated by '\n'" genericdata 
    COLS_FROM_A_SECOND="$COLS_FROM_A,"$COLS_SECOND_HALF_YEAR

    $MYSQLPATH/mysql -h $DBHOST -u root -e "select distinct $COLS_FROM_A_SECOND from attrapp25s a left join gpdtl b on a.TGyear=b.Year and a.TGMonth=MONTH(b.Invdate) and a.TGCstcode=b.Cstcode where a.TGyear=$year and a.TGmonth>=7 and b.id is NULL into outfile '$OUTFILE_H2' fields terminated by ',' enclosed by '\"' lines terminated by '\n'" genericdata 
    cat $OUTFILE_H1 $OUTFILE_H2 >> $CSVIMPORTFILE
    rm -f $OUTFILE_H1 $OUTFILE_H2
done

echo "inserting zero data..."
$MYSQLPATH/mysql -h $DBHOST -u root -e "load data infile '$CSVIMPORTFILE' into table gpdtl fields terminated by ',' enclosed by '\"' lines terminated by '\n' ignore 1 rows($COLS_TO)" genericdata
$MYSQLPATH/mysql -h $DBHOST -u root -e "commit" genericdata

