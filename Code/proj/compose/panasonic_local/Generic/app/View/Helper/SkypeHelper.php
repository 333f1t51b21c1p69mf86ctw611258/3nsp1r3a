<?php
App::uses('AppHelper', 'View/Helper');

class SkypeHelper extends App1AppHelper {
    public function genSkypeButton(){
        $skype =<<<END
</script>
<script src="https://www.paypalobjects.com/js/external/paypal-button.min.js?merchant=yono@enspirea.com" 
    data-button="buynow" 
    data-name="Seminar Fee" 
    data-quantity="1" 
    data-amount="1" 
    data-callback="http://www.google.com"
    data-env="sandbox"
></script>
END;
        return $skype
    }
} 
