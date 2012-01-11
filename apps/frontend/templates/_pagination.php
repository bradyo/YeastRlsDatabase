<?php if (count($pager->getPages()) > 1): ?>
  <div class="pagination" style="text-align:left">
    <a href="<?php echo url_for(filter_url('page', 1)) ?>">First</a>
    <a href="<?php echo url_for(filter_url('page', $pager->getPagePrevious())) ?>">Previous</a>
    <?php foreach ($pager->getPages() as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <?php echo $page ?>
      <?php else: ?>
        <a href="<?php echo url_for(filter_url('page', $page)) ?>"><?php echo $page ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
    <a href="<?php echo url_for(filter_url('page', $pager->getPageNext())) ?>">Next</a>
    <a href="<?php echo url_for(filter_url('page', $pager->getPageLast())) ?>">Last</a>
  </div>
<?php endif; ?>