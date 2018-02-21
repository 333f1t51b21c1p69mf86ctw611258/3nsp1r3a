var selectionColorString = "yellow";
var selectionColorRGB = null;

$(document).ready(function () {
    var container = $("#myDialog");
    container.hide();

    $("td.editDbConfig").click(function (e) {
        var valueInField = $.trim($(this).html());
        var container = $("#myDialog");

        //if (container.has(e.target).length === 0)
        if (container.is(':visible')) {
            var color= $(this).css("background-color");
            if( $(this).css("background-color") == selectionColorRGB ){
                $("#myDialog").dialog("close");
            }
        } else {
            origBgColor = $(this).css("background-color");
            plugin = getPlugin();
            $(this).css("background-color", selectionColorString);
            selectInput(this,valueInField,origBgColor,plugin);
        }
    });
    $(document).keyup(function(e) {
        if (e.keyCode == 13) { $('#myDialogOk').click(); }     // enter
        if (e.keyCode == 27) { $('#myDialogCancel').click(); }   // esc
    });

    function smallLetterCheck(element){
        element.css('border', '');
        var pattern = /^(\d+(\.\d{1,2})?)?$/;
        var y = element.val();
        var x = y.replace(/,/g,'');
        if (x.match(pattern)){
            return true;
        }
        else{
            element.css("border", "2px solid #ff0000");
            element.focus();
            element.select();
            alert('Please input a numerical value.');
            return false;
        }
    }

});
function getPlugin(){
    var currentLocation = window.location.href;
    var pattern = /^[htps]+:\/\/.+\/Generic\/([a-zA-Z0-9]+)\/.+$/;
    var matches = currentLocation.match(pattern);
    if (matches){
        return matches[1];
    }
}

function saveUpdate(tdCellId, dbConfigString, userFormula, plugin)
{
    var data = {id: tdCellId,
                config: dbConfigString,
                formula: userFormula};
        
    $.ajax({
        type: "POST",
        url: "/Generic/" + plugin + "/saveUpdate.json",
        data: data,
        dataType: "json",
        success: function(result){
            // show feedback to user
        },
        error: function(){
            // show feedback to user
        }
    });
}

function dataTypeToDbType(dataType) {
    var result = [];

    switch (dataType) {
        case "Text":
            result.push("input");
            result.push("string");
            result.push("string");
            break;
        case "Date":
            result.push("date");
            result.push("date");
            result.push("date");
            break;
        case "Static label":
            result.push("label");
            result.push("string");
            result.push("string");
            break;
        case "Integer":
            result.push("input");
            result.push("int");
            result.push("integer");
            break;
        case "Currency":
            result.push("input");
            result.push("currency");
            result.push("currency");
            break;
        case "Decimal1":
            result.push("input");
            result.push("decimal1");
            result.push("one-digit");
            break;
        case "Decimal2":
            result.push("input");
            result.push("decimal2");
            result.push("two-digits");
            break;
        case "Decimal3":
            result.push("input");
            result.push("decimal3");
            result.push("three-digits");
            break;
        default:
            result.push("input");
            result.push("string");
            result.push("string");
            break;
    }
    return result;
}

function dbTypeToDataType(control, dbType) {
    if( dbType.length == 0){
        return controlToDataType(control);
    }

    var type = '';

    if( dbType == "string" ){
        type = "Text";
    }else if (dbType == "int") {
        type = "Integer";
    }else if (dbType == "currency") {
        type = "Currency";
    }else if (dbType == "decimal1") {
        type = "Decimal1";
    }else if (dbType == "decimal2") {
        type = "Decimal2";
    }else if (dbType == "decimal3") {
        type = "Decimal3";
    }else if (dbType == "date") {
        type = "Date";
    }

    return type;
}

function controlToDataType(control){
    if (control == "input") {
        type = "Text";
    } else if (control == "date") {
        type = "Date";
    } else if (control == "label") {
        type = "Static label";
    } 
    return type;
}

function typeToCssClass(userSpecifiedType){
    retval = "";
    switch (userSpecifiedType) {
        case "Text":
        case "Static label":
            retval = "string";
            break;
        case "Date":
            retval = "date";
            break;
        case "Integer":
            retval = "integer";
            break;
        case "Currency":
            retval = "currency";
            break;
        case "Decimal1":
            retval = "one-digit";
            break;
        case "Decimal2":
            retval = "two-digits";
            break;
        case "Decimal3":
            retval = "three-digits";
            break;
        default:
            break;
    }
    return retval;
}

// check all specified classes, remove duplicates
function setTypeToCssClass(inputType,classesSetOnDialog){
    var classes = classesSetOnDialog.split(',');
    var cssClassForType = typeToCssClass(inputType);

    // if type not in classes, set one and return
    if($.inArray(cssClassForType, classes) == -1){
        classes.push(cssClassForType);
    }
  
    retval = ""; 
    if( classes.length == 1){
        retval = classes[0];   
    }else{
        retval = classes.join();
    }
    return retval;
}

// format of column: control:name:type:cssid:cssclasses 
function readSetColumnValue(colValue, idOfInterest) {
    //var colValue = $("#inputColumn").val();

    var repl = /^([a-zA-Z]+):([a-zA-Z0-9_\.]+):([a-zA-Z0-9_]+)[:]?([a-zA-Z0-9-_\.]*)[:]?([a-zA-Z0-9-_]*)$/g;
    var control = colValue.replace(repl, "$1");
    var name = colValue.replace(repl, "$2");
    var type = colValue.replace(repl, "$3");
    var cssid = colValue.replace(repl, "$4");
    var cssclass = colValue.replace(repl, "$5");

    $("#cellId").val(idOfInterest);
    if( control.length == 0 || name.length == 0 || name.length == 0 ){
        // reset values
        $("#inputColumn").val('');
        $("#inputType").val('');
        $("#inputCSSID").val('');
        $("#inputCSSClass").val('');
        $("#inputFormula").val('');
        
        return;
    }

    $("#inputColumn").val(name);
    //var typeObj = $("#inputType");
    var typeObj = document.getElementById("inputType");
    var inputType = dbTypeToDataType(control, type);
    for(var i=0; i<typeObj.length; i++) {
      if ( typeObj.options[i].text == inputType ) {
        typeObj.selectedIndex = i;
        break;
      }
    }
    $("#inputCSSID").val(cssid);
    $("#inputCSSClass").val(setTypeToCssClass(inputType,cssclass));

    $("#inputFormula").val($('#'+idOfInterest).attr('formula'));
}
// e.g. rgb(255, 255, 0)
function rgbToHex(rgbColor){
    var regex = /^rgb\(([0-9\s]+),[\s]+([0-9]+),[\s]+([0-9]+)\)$/;
    var rgbMatch = rgbColor.match(regex);
    var hex = "#" + ((1 << 24) + (parseInt(rgbMatch[1]) << 16) + (parseInt(rgbMatch[2]) << 8) + parseInt(rgbMatch[3])).toString(16).slice(1);
    return hex;
}

function selectInput(targetObj, valueInField, origBgColor, plugin) {
    //$("#inputColumn").val(valueInField);
    var idOfInterest = $(targetObj).attr('id');
    var bgColor = origBgColor;
    readSetColumnValue(valueInField, idOfInterest);

    var execute = function () {
        var columnName = '';
        var columnType = '';
        var columnCSSID = '';
        var columnCSSClass = '';
        var columnFormula = '';

        $("#inputColumn").each(function () {
            columnName = (this.length != 0) ? $(this).val() : null;
        });
        $("#inputType").each(function () {
            columnType = (this.length != 0) ? $(this).val() : null;
        });
        $("#inputCSSID").each(function () {
            columnCSSID = (this.length != 0) ? $(this).val() : columnName;
        });
        $("#inputCSSClass").each(function () {
            columnCSSClass = (this.length != 0) ? $(this).val() : null;
        });
        $("#inputFormula").each(function () {
            columnFormula = (this.length != 0) ? $(this).val() : null;
        });

        // reset the value to the field
        var control = "";
        var name = columnName;
        var type = "";
        var cssid = columnCSSID;
        var cssclass = columnCSSClass;
        var userFormula = columnFormula;

        controlAndDbType = dataTypeToDbType(columnType);
        control = controlAndDbType[0];
        type = controlAndDbType[1];
        // TODO: validate uniqueness
        cssid = columnName;
        cssclass = controlAndDbType[2];

        var dbConfigPacked = control + ':' + name + ':' + type + ':' + cssid + ':' + cssclass; 
        $(targetObj).html(dbConfigPacked);
        $(targetObj).attr('formula', userFormula);

        $(targetObj).css("background-color",  rgbToHex(bgColor));
        $("#myDialog").dialog("close");

        var myUrl = 'http://localhost/AppGenerics/';
        var myFilename = 'test.txt';

        saveUpdate(idOfInterest, dbConfigPacked, userFormula, plugin);
    };
    var cancel = function () {
        $(targetObj).css("background-color",  rgbToHex(bgColor));
        $("#myDialog").dialog("close");
    };
    var dialogOpts = {
        buttons: {
            "Ok": { text: "Ok",
                    id: "myDialogOk", 
                    click: execute},
            "Cancel": { text: "Cancel",
                        id: "myDialogCancel", 
                        click: cancel},
        }
    };
    $("#myDialog").dialog(dialogOpts);
}
