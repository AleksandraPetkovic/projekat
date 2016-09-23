<?php

class Zend_View_Helper_TopPhoto extends Zend_View_Helper_Abstract {
    
    public function topPhoto() {

        $cmsSitemapPageDbTable = new Application_Model_DbTable_CmsSitemapPages();
        
        $topPhotoSitemapPages = $cmsSitemapPageDbTable->search(array(
            'filters' => array(
                'parent_id' => 0,
                'status' => Application_Model_DbTable_CmsSitemapPages::STATUS_ENABLED
            ),
            'orders' => array(
                'order_number' => 'ASC'
            )
        ));
        //resetovanje placeholder-a
        $this->view->placeholder('topPhoto')->exchangeArray(array());
        $this->view->placeholder('topPhoto')->captureStart();
        ?>


    <section class="frid clearfix">
        <figure>
            <img src="<?php echo $this->baseUrl('/front/img/hot-stone.jpg'); ?>" alt="" class="img-responsive"/>
        </figure>
        <?php foreach ($topPhotoSitemapPages as $sitemapPage) { ?>
        <div class="gridView">
            <h3 class="text-uppercase text-center"><?php echo $this->escape($sitemapPage['title']); ?></h3>
        </div>
        <?php }?>
    </section>

        <?php
        $this->view->placeholder('topPhoto')->captureEnd();
        return $this->view->placeholder('topPhoto')->toString();
    }
}

