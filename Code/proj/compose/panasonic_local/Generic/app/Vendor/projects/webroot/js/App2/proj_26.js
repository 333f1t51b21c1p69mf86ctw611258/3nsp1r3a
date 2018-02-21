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
    for( i=1; i<=25; i++ ){
        if( typeaheadIds.length != 0 ){
            typeaheadIds += ',';
        }
        typeaheadIds += ' #1_Model_No_'+i;
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
    // association is hardcoded for Panasonic
    var source_cssid = returnVal["source_cssid"];
    var cssid_array = source_cssid.split("_");
    var source_index = cssid_array[ cssid_array.length - 1 ];

    var fob_japan_id   = '#1_FOB_Japan_'   + source_index;
    var landed_cost_id = '#1_Landed_Cost_' + source_index;
    var accessory_1_id = '#1_Accessory1_'  + source_index;
    var accessory_2_id = '#1_Accessory2_'  + source_index;
    var list_price_id  = '#1_List_Price_'  + source_index;

    var fob_japan_val   = returnVal["data"]["FOB_Japan"];
    var landed_cost_val = returnVal["data"]["Landed_Cost"];
    var accessory_1_val = returnVal["data"]["Accessory_Cost_1"];
    var accessory_2_val = returnVal["data"]["Accessory_Cost_2"];
    var list_price_val  = returnVal["data"]["List_Price"];

    $(fob_japan_id).val(fob_japan_val);
    $(landed_cost_id).val(landed_cost_val);
    $(accessory_1_id).val(accessory_1_val);
    $(accessory_2_id).val(accessory_2_val);
    $(list_price_id).val(list_price_val);
}

$(document).ready(function(){
    // fetch data only once
    var machines = fetchAllForAColumnFromDB('attrapp32s', 'Row_Labels', '', setTypeahead);

    // when item fields get out of focus, fetch associated data
    var typeahead_ids = getTypeaheadIds(); 
    $(typeahead_ids).focusout(function(){
        var keyVal = $(this).val();
        fetchOneRecordFromDB('attrapp32s', 'Row_Labels', keyVal, this.id, setAssociatedFields);
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
        url: '/Generic/App2/fetch_column_val_list.json',
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
        url: '/Generic/App2/fetch_one_record.json',
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
