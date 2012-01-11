<?php use_helper('UrlFilter') ?>
<?php use_javascript('jquery.expander.js') ?>
<?php use_javascript('yeastRls.js') ?>
<?php use_javascript('preview.js') ?>
<?php use_stylesheet('preview.css') ?>

<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<div id="filterDiv" style="margin-top:1em; margin-bottom:1em">
  <form name="filterForm" action="<?php echo url_for('yeast-rls/medias') ?>" method="get" >
    <table class="form">
      <tr>
        <td class="label"><label>Search:</label></td>
        <td><?php echo $filterForm['search'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Genotype:</label></td>
        <td><?php echo $filterForm['genotype'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Background:</label></td>
        <td><?php echo $filterForm['background'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Mating Type:</label></td>
        <td><?php echo $filterForm['mating_type'] ?></td>
      </tr>

      <tr>
        <td class="label"><label>Sort by:</label></td>
        <td>
          <?php echo $filterForm['sort_by'] ?>
          <?php echo $filterForm['sort_order'] ?>
        </td>
      </tr>
      <tr>
            <td></td>
            <td>
                <label>
                    <?php echo $filterForm['single'] ?>
                    Show only single deletions
                </label>
            </td>
        </tr>
      <tr>
        <td></td>
        <td>
          <button type="submit" name="submit" value="apply">Apply</button>
        </td>
      </tr>
    </table>
  </form>
</div>
<hr />


<form id="exportForm" name="exportForm" action="<?php echo url_for('yeast-rls/medias-export').'?'.url_params() ?>" method="post">

  <div style="clear: both; margin-top: 1em; text-align: center; height: 2em">
    <div style="float: left; height: 2em;">
      <strong>Export:</strong>
      <select name="exportType">
        <option value="selected">Selected</option>
        <option value="all">All</option>
      </select>
      <button type="submit" name="submit" value="export">Export</button>
    </div>
    <div class="clear"></div>
  </div>

  <table id="rlsTable" class="dataTable" style="width:100%;">
    <thead>
      <tr>
        <th colspan="5" style="background:white"></th>
        <?php foreach ($medias as $media): ?>
          <th colspan="7"><?php echo $media ?> Lifespans</th>
        <?php endforeach ?>
        <th style="background:white"></th>
      </tr>
      <tr>
        <th><input type="checkbox" id="checkAll" /></th>
        <th>Genotype</th>
        <th>Background</th>
        <th>Mating Type</th>
        <th>T(&deg;C)</th>
        <?php foreach ($medias as $media): ?>
          <th>Mean</th>
          <th>Count</th>
          <th>WT Mean</th>
          <th>WT Count</th>
          <th>Percent Change</th>
          <th>Ranksum p</th>
          <th>More</th>
        <?php endforeach ?>
        <th style="text-align:center">More</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td style="width:0.5em">
              <input type="checkbox" name="export[<?php echo $row[0] ?>]" />
            </td>
            <td><?php echo $row[1] ?></td>
            <td><?php echo $row[2] ?></td>
            <td><?php echo $row[3] ?></td>
            <td><?php echo $row[4] ?></td>

            <?php for ($i = 0; $i < 4; $i++): ?>
              <?php $offset = 5 + $i * 7 ?>

              <td>
                <?php if (!empty($row[$offset+1])): ?>
                  <?php echo number_format($row[$offset+1], 2) ?>
                <?php endif ?>
              </td>
              <td><?php echo $row[$offset+2] ?></td>
              <td>
                <?php if (!empty($row[$offset+3])): ?>
                  <?php echo number_format($row[$offset+3], 2) ?>
                <?php endif ?>
              </td>
              <td><?php echo $row[$offset+4] ?></td>
              <td>
                <?php if (!empty($row[$offset+5])): ?>
                  <?php echo number_format($row[$offset+5], 2) ?>
                <?php endif ?>
              </td>
              <td>
                <?php if (!empty($row[$offset+6])): ?>
                  <?php printf("%.6G", $row[$offset+6]) ?>
                <?php endif ?>
              </td>

              <td style="text-align:center">
                <?php if (!empty($row[$offset])): ?>
									<a class="preview" href="<?php echo url_for('yeast-rls/plot')
                    .'/type/result/filename/survival'.$row[$offset].'.png' ?>">
                    <img src="<?php echo sfConfig::get('sf_relative_url_root') ?>/images/graph.png" border="0px" alt="plot" />
                  </a>
                  <a href="<?php echo url_for('yeastrls/result').'?id='.$row[$offset] ?>">
                    <img src="<?php echo sfConfig::get('sf_relative_url_root') ?>/images/view.png" border="0px" alt="view" />
                  </a>
                <?php endif ?>
              </td>
            <?php endfor ?>

            <td style="text-align:center">
              <a class="preview" href="<?php echo url_for('yeast-rls/plot')
                .'/type/cross_media/filename/survival'.$row[0].'.png' ?>">
                <img src="<?php echo sfConfig::get('sf_relative_url_root') ?>/images/graph.png" border="0px" alt="plot" />
              </a>
              <!--
              <a href="<?php echo url_for('yeastrls/show').'?id='.$row['id'] ?>">
                <img src="<?php echo sfConfig::get('sf_relative_url_root') ?>/images/view.png" border="0px" alt="view" />
              </a>
              -->
            </td>
          </tr>
        <?php endforeach; ?>
          
      <?php else: ?>
        <tr>
          <td colspan="0" style="background:white">No results matching query</td>
        </tr>
      <?php endif ?>
        
    </tbody>
  </table>
</form>


<div style="clear:both; margin-bottom: 1em; margin:0; padding: 0;">
  <?php include_partial('global/paginationLite', array('pager' => $pager)) ?>
</div>
