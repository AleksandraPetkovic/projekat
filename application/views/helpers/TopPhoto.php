<?php

class Zend_View_Helper_TopPhoto extends Zend_View_Helper_Abstract {

    public function topPhoto() {?>

    <section class="frid clearfix">
        <figure>
            <img src="<?php echo $this->view->baseUrl('/front/img/hot-stone.jpg'); ?>" alt="" class="img-responsive"/>
        </figure>

        <?php
    }
}

