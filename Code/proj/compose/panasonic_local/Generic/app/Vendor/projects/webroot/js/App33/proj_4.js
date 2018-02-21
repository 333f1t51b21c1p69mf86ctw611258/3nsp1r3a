// auto completion between category and GL_Number
var substringMatcher = function(strs) {
  return function findMatches(q, cb) {
    var matches, substrRegex;
 
    // an array that will be populated with substring matches
    matches = [];
 
    // regex used to determine if a string contains the substring `q`
    substrRegex = new RegExp(q, 'i');
 
    // iterate through the pool of strings and for any string that
    // contains the substring `q`, add it to the `matches` array
    $.each(strs, function(i, str) {
      if (substrRegex.test(str)) {
        // the typeahead jQuery plugin expects suggestions to a
        // JavaScript object, refer to typeahead docs for more info
        matches.push({ value: str });
      }
    });
 
    cb(matches);
  };
};

function getTypeaheadIds(){
    var typeaheadIds = '';
    // FIXME change the maximum number when Excel layout is updated
    for( i=2; i<=19; i++ ){
        if( typeaheadIds.length != 0 ){
            typeaheadIds += ',';
        }
        typeaheadIds += ' #1_Item'+i+'_name_1';
    }
    return typeaheadIds;
} 
/*
 * returnVal = {
 *   table_name: val,
 *   column_name: val,
 *   typeahead_ids: val, # '#1_Model_No_1, #1_Model_No_2, ....'
 *   data:{ val1, val2, ...., valN }; 
 */
function setTypeahead(returnVal){
    var items = returnVal["data"];

    var typeahead_ids = getTypeaheadIds(); 
/*
    $(typeahead_ids).typeahead({
      hint: true,
      minLength: 1,
      highlight: true,
    },
    {
      name: 'states',
      displayKey: 'value',
      source: substringMatcher(items),
    });
*/
}
/*
 * returnVal = {
 *   source_cssid: val,
 *   key_col_name: val,
 *   key_col_value: val,
 *   data: { colName1:val, colName2:val, .... }
 * }
 */
function setAssociatedFields(returnVal){
    // FIXME
    // association is hardcoded for Matutani
    //  for Matsutani, pick 2nd array for _ concatenated string
    //  e.g. 1_Item3_name_1 => 'Item3'
    // CUSTOMIZED BLOCK BEGIN
    var source_cssid = returnVal["source_cssid"];
    var cssid_array = source_cssid.split("_");
    var source_index = cssid_array[ 1 ];
    // CUSTOMIZED BLOCK END

    var code_id   = '#1_'+source_index+'_Code_1';
    var code_val   = returnVal["data"]["GL_Number"];
    $(code_id).val(code_val);
}

$(document).ready(function(){
    // fetch data only once
    var machines = fetchAllForAColumnFromDB('attrapp35s', 'category', '', setTypeahead);

    // when item fields get out of focus, fetch associated data
    var typeahead_ids = getTypeaheadIds(); 
    //$(typeahead_ids).focusout(function(){
    $(typeahead_ids).change(function(){
        //var keyVal = $(this).val();
        var keyVal = $(this).find("option:selected").attr("value");
        fetchOneRecordFromDB('attrapp35s', 'category', keyVal, this.id, setAssociatedFields);
    });
});

/* Get a single column of records for a given table. 
   If colConstraint is given, add 'colName = colConstraint' */
function fetchAllForAColumnFromDB(tableName, colName, colConstraint, callbackFn)
{
    var data = {table: tableName, 
                column: colName,
                constraint: colConstraint
                };

    $.ajax({
        async: true,
        type: "POST",
        url: '/Generic/App33/fetch_column_val_list.json',
        data: data,
        dataType: "json",
        success: function(result){
            return callbackFn(result);
        },
        error: function(xhr, textStatus, errorThrown){
            return; // callbackFn(NULL);
        }
    });
}

/* Get a record with a given constraint from table. */
function fetchOneRecordFromDB(tableName, keyColName, keyColConstraint, sourceCssid, callbackFn)
{
    var data = {table: tableName, 
                keyColumn: keyColName,
                keyConstraint: keyColConstraint,
                sourceCssid: sourceCssid
                };

    $.ajax({
        async: true,
        type: "POST",
        url: '/Generic/App33/fetch_one_record.json',
        data: data,
        dataType: "json",
        success: function(result){
            return callbackFn(result);
        },
        error: function(xhr, textStatus, errorThrown){
            return; // callbackFn(NULL);
        }
    });
}
