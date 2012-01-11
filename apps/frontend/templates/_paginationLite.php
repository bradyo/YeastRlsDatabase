<?php
$urlBase = url_for($sf_params->get('module').'/'.$sf_params->get('action'));
?>

<?php if (count($pager->getPages()) > 1): ?>
  <div class="pagination" style="text-align:left">
    Page:
    <?php foreach ($pager->getPages() as $page): ?>
      <?php if ($page == $pager->getPage()): ?>
        <?php echo $page ?>
      <?php else: ?>
        <a href="<?php echo $urlBase.'?'.filter_url('page', $page) ?>"><?php echo $page ?></a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
<?php endif; ?>