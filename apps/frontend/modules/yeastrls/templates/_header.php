<?php

$currentPage = 'yeastrls/'.$sf_params->get('action');
$pages = array(
  'Sets' => 'yeastrls/sets',
  'Results' => 'yeastrls/results',
  'Across Medias' => 'yeastrls/medias',
  'Across Mating Types' => 'yeastrls/mating-types',
  'Build Log' => 'yeastrls/log',
  'Experiments' => 'rlsExperiment/list',
  );

?>

<h1 style="margin:0">Yeast Replicative Lifespan Database</h1>
<hr />

<div>
  <div style="float:left">
    <strong>View:</strong>
    <?php foreach ($pages as $name => $page): ?>
      <?php if ($page == $currentPage): ?>
        <?php echo $name ?>&nbsp;
      <?php else: ?>
        <?php echo link_to($name, $page) ?>&nbsp;
      <?php endif ?>
    <?php endforeach ?>
  </div>
  <div style="float: right">
    <strong>Build <?php echo $buildDate ?>:</strong>
      <a href="<?php echo url_for('yeast-rls/download').'/filename/rls.csv' ?>"
         >Download Csv</a>&nbsp;
      <a href="<?php echo url_for('yeast-rls/download').'/filename/rls.db' ?>"
         >Download DB</a>&nbsp;
  </div>
  <div style="clear: both"></div>
</div>
<hr />