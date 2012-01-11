<?php use_helper('UrlFilter') ?>
<?php use_javascript('jquery.expander.js') ?>
<?php use_javascript('yeastRls.js') ?>

<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<div id="filterDiv" style="margin-top:1em; margin-bottom:1em">
  
  <form name="filterForm" action="<?php echo url_for('yeast-rls/sets') ?>" method="get" >
    <table class="form">
      <tr>
        <td class="label"><label>Search:</label></td>
        <td><?php echo $filterForm['search'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Experiment:</label></td>
        <td><?php echo $filterForm['experiment'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Strain:</label></td>
        <td><?php echo $filterForm['strain'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Genotype:</label></td>
        <td><?php echo $filterForm['genotype'] ?></td>
      </tr>
      <tr>
        <td class="label"><label>Media:</label></td>
        <td><?php echo $filterForm['media'] ?></td>
      </tr>
      
      <tr>
        <td class="label"><label>Lifespan Mean:</label></td>
        <td>
          <?php echo $filterForm['lifespan_mean_op'] ?>
          <?php echo $filterForm['lifespan_mean'] ?>
        </td>
      </tr>
      <tr>
        <td class="label"><label>Sort By:</label></td>
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

<form id="exportForm" name="exportForm" action="<?php echo url_for('yeast-rls/sets-export').'?'.url_params()  ?>" method="post">

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

  <table class="dataTable" style="width:100%">
    <thead>
      <tr>
        <th><input type="checkbox" id="checkAll" /></th>
        <th>Experiment</th>
        <th>Name</th>
        <th>Strain</th>
        <th>Background</th>
        <th>Mating Type</th>
        <th>Genotype</th>
        <th>Media</th>
        <th>Temp (&deg;C)</th>
        <th>Count</th>
        <th>Lifespan Mean</th>
        <th>Std Dev</th>
        <th style="text-align:center">More</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr>
            <td style="width:0.5em">
              <input type="checkbox" name="export[<?php echo $row['id'] ?>]" />
            </td>

            <td><?php echo $row['experiment'] ?></td>
            <td><?php echo $row['name'] ?></td>
            <td><?php echo $row['strain'] ?></td>
            <td><?php echo $row['background'] ?></td>
            <td><?php echo $row['mating_type'] ?></td>
            <td><?php echo $row['genotype'] ?></td>
            <td><?php echo $row['media'] ?></td>
            <td><?php echo $row['temperature'] ?></td>
            <td><?php echo $row['lifespan_count'] ?></td>
            <td><?php echo number_format($row['lifespan_mean'], 2) ?></td>
            <td><?php echo number_format($row['lifespan_stdev'], 2) ?></td>

            <td style="text-align: center; width: 40px">
              <a href="<?php echo url_for('yeast-rls/set').'?id='.$row['id'] ?>">
                <img src="<?php echo sfConfig::get('sf_relative_url_root')
                  ?>/images/view.png" border="0px" alt="view" />
              </a>
            </td>
          </tr>
        <?php endforeach ?>
      <?php else: ?>
        <tr>
          <td colspan="13">No results matching query</td>
        </tr>
      <?php endif ?>
    </tbody>
  </table>


  <div style="clear:both; margin-bottom: 1em; margin:0; padding: 0;">
    <div style="float:left;">
      <?php include_partial('global/paginationLite', array('pager' => $pager)) ?>
    </div>
  </div>

</form>
