<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<h2>Set Details (ID = <?php echo $set['id'] ?>)</h2>

<?php include_partial('setDetails', array('set' => $set)) ?>


<h2>This set is pooled into the following results:</h2>

<h3>As Test:</h3>
<?php if (count($asSetPooledResultIds) > 0): ?>
  <table class="compact-view">
    <?php foreach ($asSetPooledResultIds as $pooledBy => $resultIds): ?>
      <tr>
        <th><?php echo $pooledBy ?></th>
        <td>
          <?php foreach ($resultIds as $resultId): ?>
            <a href="<?php echo url_for('yeast-rls/result').'?id='.$resultId ?>"
               ><?php echo $resultId ?></a>&nbsp;
          <?php endforeach ?>
        </td>
      </tr>
    <?php endforeach ?>
  </table>
<?php else: ?>
  none
<?php endif ?>

<h3>As Reference:</h3>
<?php if (count($asRefPooledResultIds) > 0): ?>
  <table class="compact-view">
    <?php foreach ($asRefPooledResultIds as $pooledBy => $resultIds): ?>
      <tr>
        <th><?php echo $pooledBy ?></th>
        <td>
          <?php foreach ($resultIds as $resultId): ?>
            <a href="<?php echo url_for('yeast-rls/result').'?id='.$resultId ?>"
               ><?php echo $resultId ?></a>&nbsp;
          <?php endforeach ?>
        </td>
      </tr>
    <?php endforeach ?>
  </table>
<?php else: ?>
  none
<?php endif ?>
