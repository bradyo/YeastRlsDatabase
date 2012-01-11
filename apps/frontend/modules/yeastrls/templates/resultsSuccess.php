<?php use_helper('UrlFilter') ?>
<?php use_javascript('jquery.expander.js') ?>
<?php use_javascript('yeastRls.js') ?>
<?php use_javascript('preview.js') ?>
<?php use_stylesheet('preview.css') ?>

<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<div id="filterDiv" style="margin-top:1em; margin-bottom:1em">
  
  <form name="filterForm" action="<?php echo url_for('yeast-rls/results') ?>" method="get" >
    <div id="filterMoreDiv">
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
          <td class="label"><label>Experiment:</label></td>
          <td>
            <?php echo $filterForm['experiment'] ?>
          </td>
        </tr>
        <tr>
          <td class="label"><label>Media:</label></td>
          <td>
            <?php echo $filterForm['media'] ?>
          </td>
        </tr>
        <tr>
          <td class="label"><label>Percent Change:</label></td>
          <td>
            <?php echo $filterForm['percent_change_op'] ?>
            <?php echo $filterForm['percent_change'] ?> %
          </td>
        </tr>
        <tr>
          <td class="label"><label>Ranksum p:</label></td>
          <td>
            <?php echo $filterForm['ranksum_p_op'] ?>
            <?php echo $filterForm['ranksum_p'] ?>
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
        <?php if ($sf_request->getGetParameter('pooled_by', 'file') == 'file'): ?>
            <tr>
                <td></td>
                <td>
                    <label>
                        <?php echo $filterForm['epistasis'] ?>
                        Show only epistasis experiments (double deletions have both single deletions)
                    </label>
                </td>
            </tr>
        <?php endif ?>

        <tr>
          <td></td>
          <td>
            <input type="hidden" name="pooled_by"
              value="<?php echo $sf_request->getGetParameter('pooled_by', 'file') ?>" />
            <button type="submit" name="submit" value="apply">Apply</button>
          </td>
        </tr>
      </table>
    </div>
  </form>
</div>
<hr />

<form id="exportForm" name="exportForm" action="<?php echo url_for('yeastrls/results-export').'?'.url_params() ?>" method="post">

  <div style="clear: both; margin-top: 1em; text-align: center; height: 2em">
    <div style="float:right; height:2em">
      <div style="margin: 2px; display: inline; border: 1px solid black; padding: 2px" class="significant1">
        &nbsp;yellow: p &lt; 0.05
      </div>
      <div style="margin: 2px; display: inline; border: 1px solid black; padding: 2px" class="significant2">
        &nbsp;green: p &lt; 0.01
      </div>
      &nbsp;
      &nbsp;

      <strong>Pooled By:</strong>
      <?php foreach ($poolingOptions as $key => $label): ?>
        <?php if ($pooledBy == $key): ?>
          <?php echo $label ?>
        <?php else: ?>
          <a href="<?php echo url_for('yeastrls/results').'?'.filter_url('pooled_by', $key) ?>"
             ><?php echo $label ?></a>
        <?php endif ?>
      <?php endforeach ?>
    </div>

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

  
  <table id="rlsTable" class="dataTable" style="width:100%">
    <thead>
      <tr>
        <th><input type="checkbox" id="checkAll" /></th>
        <th>Experiment</th>
        <th>Name</th>
        <th>Genotype</th>
        <th>Media</th>
        <th>&deg;C</th>
        <th>N</th>
        <th>Mean</th>
        <th>StdDev</th>
        <th>Reference</th>
        <th>Count</th>
        <th>Mean</th>
        <th>StdDev</th>
        <th>Change</th>
        <th>Ranksum p</th>
        <th style="text-align:center">More</th>
      </tr>
    </thead>
    <tbody>
      <?php if (count($rows) > 0): ?>
        <?php foreach ($rows as $row): ?>
          <tr
            <?php if (is_numeric($row['ranksum_p'])): ?>
              <?php if (doubleval($row['ranksum_p']) < 0.01): ?>
                class="significant2"
              <?php elseif (doubleval($row['ranksum_p']) < 0.05): ?>
                class="significant1"
              <?php endif ?>
            <?php endif ?>
            >

            <td style="width:0.5em">
              <input type="checkbox" name="export[<?php echo $row['id'] ?>]" />
            </td>
            <td>
              <?php $n = count(explode(',', $row['experiments'])) ?>
              <?php if ($n == 1): ?>
                  <?php echo $row['experiments'] ?>
              <?php else: ?>
                <?php echo '(' . $n . ' experiments)' ?>
                <!--
                <span class="expandable">
                  <br />
                  <?php foreach (explode(',', $row['experiments']) as $experiment): ?>
                    <?php echo $experiment ?><br />
                  <?php endforeach ?>
                </span>
                -->

              <?php endif ?>
            </td>
            <td><?php echo $row['set_name'] ?></td>
            <td><?php echo $row['set_genotype'] ?></td>
            <td><?php echo $row['set_media'] ?></td>
            <td><?php echo $row['set_temperature'] ?></td>
            <td><?php echo $row['set_lifespan_count'] ?></td>
            <td><?php echo number_format($row['set_lifespan_mean'], 2) ?></td>
            <td><?php echo number_format($row['set_lifespan_stdev'], 2) ?></td>
            <td><?php echo $row['ref_name'] ?></td>
            <td><?php echo $row['ref_lifespan_count'] ?></td>
            <td>
              <?php if (!empty($row['ref_lifespan_mean'])): ?>
                <?php echo number_format($row['ref_lifespan_mean'], 2) ?>
              <?php endif ?>
            </td>
            <td>
              <?php if (!empty($row['ref_lifespan_stdev'])): ?>
                <?php echo number_format($row['ref_lifespan_stdev'], 2) ?>
              <?php endif ?>
            </td>
            <td>
              <?php if (!empty($row['percent_change'])): ?>
                <?php echo number_format($row['percent_change'], 2) ?>
              <?php endif ?>
            </td>
            <td>
              <?php if (is_numeric($row['ranksum_p'])): ?>
                <?php printf("%.6G", $row['ranksum_p']) ?>
              <?php endif ?>
            </td>

            <td style="text-align: center; width: 40px">

              <a class="preview" href="<?php echo url_for('yeast-rls/plot')
                . '/type/result/filename/survival'.$row['id'].'.png' ?>">                
                <img src="<?php echo sfConfig::get('sf_relative_url_root')
                    ?>/images/graph.png" border="0px" alt="plot" />
              </a>
              <a href="<?php echo url_for('yeastrls/result').'?id='.$row['id'] ?>">
                <img src="<?php echo sfConfig::get('sf_relative_url_root')
                    ?>/images/view.png" border="0px" alt="view" />
              </a>
            </td>
          </tr>
        <?php endforeach; ?>

      <?php else: ?>
        <tr>
          <td colspan="0" style="background-color:white">No results matching query</td>
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
