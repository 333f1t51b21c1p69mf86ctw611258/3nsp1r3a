<div id="myDialog" class='menu' title="Edit DB column name">
    <p hidden>Cell ID</p>
    <input id="cellId" type="hidden" name="fieldname" readonly>
    <p>Enter the column name</p>
    <input id="inputColumn" type="text" name="fieldname">
    <p>Specify data type</p>
    <select id="inputType">
        <option value="Text">Text</option>
        <option value="Date">Date</option>
        <option value="Currency">Currency</option>
        <option value="Integer">Integer</option>
        <option value="Decimal1">Decimal(tenth)</option>
        <option value="Decimal2">Decimal(hundredth)</option>
        <option value="Decimal3">Decimal(thousands)</option>
        <!-- <option class="ui-state-disabled" value="Dropdown">Dropdown</option> -->
    </select>
    <!-- below are hidden param, used a temporary data store -->
    <p hidden>Specify CSS ID (optional, same as column name by default)</p>
    <input id="inputCSSID" type="hidden" name="fieldcssid">
    <p hidden>Specify CSS class(optional, none by default)</p>
    <input id="inputCSSClass" type="hidden" name="fieldcssclass">
 
    <!-- below specifies formular to embed in spreadsheet 
         curerntly all values are specified manually -->
    <p>Specify formula (e.g. cell1+cell2)</p>
    <input id="inputFormula" type="text" name="fieldformula">
   
</div>

