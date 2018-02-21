function ButtonClicked()
{
    console.log('button clicked!');
    document.getElementById("submitButton").style.display = "none"; // to undisplay
    document.getElementById("loadingImg").style.display = ""; // to display
    return true;
}
var FirstLoading = true;
function RestoreSubmitButton()
{
    if( FirstLoading )
    {
        FirstLoading = false;
        return;
    }
    document.getElementById("formsubmitbutton").style.display = ""; // to display
    document.getElementById("buttonreplacement").style.display = "none"; // to undisplay
}
// To disable restoring submit button, disable or delete next line.
document.onfocus = RestoreSubmitButton;

function ButtonSpinner(){
        $(this).toggleClass('active');
}

$(function(){
    $('a, button').click(function() {
        $(this).toggleClass('active');
    });
});
