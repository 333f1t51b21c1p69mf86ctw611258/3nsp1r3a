/* 
    RM cannot approve if any one of Discount Rates are over or equal to 55%.
    Otherwise, RM can approve only when the following conditions are met:
        Total value of free items (discount rate=100%) is less than 5% of total revenue
         (see link below to locate the numbers)
        https://enspirea.atlassian.net/wiki/display/RingiProject/Panasonic+Phase+2+Requirement
*/

function _checkDiscountRate() {
    /* calculate discount rate for all items whose quantity is given */
    var approve_threshold =  100.0 * parseFloat($('#projparam_conditional_approval_threshold')[0].value);
    //alert('approve_threshold='+approve_threshold);
    console.log('approve_threshold='+approve_threshold);
    for (i=1; i<=25; i++) {
        var qty_id = '#1_Qty_'+i,
            qty_val = parseInt($(qty_id).val()) || 0;

        if (qty_val == 0.0) {
            continue;
        }

        /* Quantity is non-zero - return False if Discount rate is > 54 */
        var discount_rate_id = '#1_Discount_Rate_'+i,
            discount_rate = parseInt($(discount_rate_id).val());
        if (discount_rate > approve_threshold && discount_rate < 100) {
            //alert('discount rate for '+ discount_rate_id +' is big - ' + discount_rate);
            console.log('UPPER APPROVAL REQUIRED:');
            console.log(' Cause: discount rate for '+ discount_rate_id
                +' is bigger than threshold '+ approve_threshold
                +', discount_rate=' + discount_rate);
            return false;
        }
    }
    console.log('discount rate check passed');
    return true;
}

function _totalRevenue() {
    var total_rev_id = '#2_Total_Revenue_Total_1',
        total_rev_str = $(total_rev_id).val(),
        total_rev = parseFloat($(total_rev_id).val());

    console.log('total_rev_str is '+ total_rev_str);
    console.log('totalRevenue is '+ total_rev);

    //alert('total_rev_str is '+ total_rev_str);
    //alert('totalRevenue is '+ total_rev);
    return total_rev;
}

function _checkNoZeroTotalCost() {
    for (i=1; i<=25; i++) {
        var cost_id = '#1_Cost_Total_'+i,
            cost_val = parseInt($(cost_id).val()) || 0,
            qty_id = '#1_Qty_'+i,
            qty_val = parseInt($(qty_id).val()) || 0;

        if (qty_val != 0 && cost_val == 0){
            console.log('UPPER APPROVAL REQUIRED:');
            console.log(' Cause: nonzero total check fails at line :'+ i);
            return false;
        }
    }
    console.log('nonzero total check passed');
    return true;
}

function _totalFreeItems() {
    var total = 0.0;

    for (i=1; i<=25; i++) {
        var qty_id = '#1_Qty_'+i,
            qty_val = parseInt($(qty_id).val()) || 0,
            discount_rate_id = '#1_Discount_Rate_'+i,
            discount_rate = parseInt($(discount_rate_id).val());

        if (qty_val == 0) {
            continue;
        }

        console.log('discount rate='+discount_rate);
        //alert('discount rate='+discount_rate);
        if (discount_rate != 100) {
            continue;
        }

        var cost_item_id = '#1_Accessory1_'+i,
            cost_item = parseFloat($(cost_item_id).val()) || 0.0;
        total += cost_item*qty_val;
    }

    console.log('totalFreeItem is '+ total);
    //alert('totalFreeItem is '+ total);
    return total;
}

function _checkFreeItemRatio() {
    var free_item_ratio_flag = _totalFreeItems()/_totalRevenue() < 0.05;

    if (!free_item_ratio_flag) {
        console.log('!! free item ration check failed');
    } else {
        console.log('free item ration check passed');
    }
    return free_item_ratio_flag;
}

// Any Model Number which has a Landed Cost 
// cannot have a value of $0 for the Win Win Price. 
function _checkNoZeroWinwin() {
    for (i=1; i<=25; i++) {
        var landedcost_id = '#1_Landed_Cost_'+i,
            landedcost_val = parseFloat($(landedcost_id).val()) || 0.0,
            winwin_id = '#1_Win_Win_'+i,
            winwin_val = parseFloat($(winwin_id).val()) || 0.0;

        if (landedcost_val == 0.0) {
            continue;
        }
       
        // landedcost is nonzero
        // winwin must be positive, otherwise higher mgmt approval is required
        if (winwin_val <= 0.0){
            console.log('UPPER APPROVAL REQUIRED:');
            console.log(' Cause: winwin value must be nonzero');
            return false;
        }
    }
    return true;
}

function _checkNetProfitPercent() {
    var netprofitPctId = '#2_Total_Net_Profit_1',
        netprofitPctVal = parseFloat($(netprofitPctId).val()) || 0.0;

    if (netprofitPctVal < 5){
        console.log('UPPER APPROVAL REQUIRED:');
        console.log(' Cause: Net profit less than 5%'); 
        return false; 
    }
    return true;    
}


function check_approve_condition() {
    var discount = _checkDiscountRate(),
        freeitem = _checkFreeItemRatio(),
        nozerowinwin = _checkNoZeroWinwin(),
        nozerototal = _checkNoZeroTotalCost();
        netprofitbiggerthan5 = _checkNetProfitPercent();
    console.log('discount, freeitem='+discount+', '+freeitem);
    //alert('discount, freeitem='+discount+', '+freeitem);

    return  discount && freeitem && nozerowinwin && nozerototal && netprofitbiggerthan5;
}

