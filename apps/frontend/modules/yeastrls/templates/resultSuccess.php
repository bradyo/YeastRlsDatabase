<?php include_partial('header', array('buildDate' => $buildDate)) ?>

<h2>Result Details (ID = <?php echo $result['id'] ?>)</h2>

<table class="compact-view">
  <tr>
    <th>Pooled By</th>
    <td><?php echo $result['pooled_by'] ?></td>
  </tr>
  <tr>
    <th>Experiments</th>
    <td><?php echo str_replace(',', ', ', $result['experiments']) ?></td>
  </tr>
</table>
<br />

<table class="result dataTable">
  <thead>
    <th>Pooled Sets</th>
    <th>Pooled Reference Sets</th>
    <th>Lifespan Comparison</th>
  </thead>

  <tr>
    <td>
      <table class="set-view" style="width:100%">
        <tr>
          <th style="width:12em">Name</th>
          <td><?php echo $result['set_name'] ?></td>
        </tr>
        <tr>
          <th>Background</th>
          <td><?php echo $result['set_background'] ?></td>
        </tr>
        <tr>
          <th>Mating Type</th>
          <td><?php echo $result['set_mating_type'] ?></td>
        </tr>
        <tr>
          <th>Media</th>
          <td><?php echo $result['set_media'] ?></td>
        </tr>
        <tr>
          <th>Temperature (&deg;C)</th>
          <td><?php echo $result['set_temperature'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Count</th>
          <td><?php echo $result['set_lifespan_count'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Mean</th>
          <td><?php echo $result['set_lifespan_mean'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Std Dev</th>
          <td><?php echo $result['set_lifespan_stdev'] ?></td>
        </tr>
      </table>

      <strong>Lifespans:</strong><br/>
      <textarea cols="40" rows="5"
        ><?php echo join("\t", explode(',', $result['set_lifespans'])) ?></textarea><br />

      <img src="<?php echo url_for('yeast-rls/plot')
        . '/type/result/filename/histogram'.$result['id'].'_set.png' ?>" alt="">
    </td>

    <td>
      <table class="set-view" style="width:100%">
        <tr>
          <th style="width:12em">Name</th>
          <td><?php echo $result['ref_name'] ?></td>
        </tr>
        <tr>
          <th>Background</th>
          <td><?php echo $result['ref_background'] ?></td>
        </tr>
        <tr>
          <th>Mating Type</th>
          <td><?php echo $result['ref_mating_type'] ?></td>
        </tr>
        <tr>
          <th>Media</th>
          <td><?php echo $result['ref_media'] ?></td>
        </tr>
        <tr>
          <th>Temperature (&deg;C)</th>
          <td><?php echo $result['ref_temperature'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Count</th>
          <td><?php echo $result['ref_lifespan_count'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Mean</th>
          <td><?php echo $result['ref_lifespan_mean'] ?></td>
        </tr>
        <tr>
          <th>Lifespans Std Dev</th>
          <td><?php echo $result['ref_lifespan_stdev'] ?></td>
        </tr>
      </table>

      <strong>Lifespans:</strong><br/>
      <textarea cols="40" rows="5"
        ><?php echo join("\t", explode(',', $result['ref_lifespans'])) ?></textarea><br />
      <img src="<?php echo url_for('yeast-rls/plot')
        . '/type/result/filename/histogram'.$result['id'].'_ref.png' ?>" alt="">
    </td>

    <td>
      <table class="set-view" style="width:100%">
        <tr>
          <th style="width:12em">Percent Change</th>
          <td><?php echo $result['percent_change'] ?></td>
        </tr>
        <tr>
          <th>Ranksum U-statistic</th>
          <td><?php echo $result['ranksum_u'] ?></td>
        </tr>
        <tr>
          <th>Ranksum p-value</th>
          <td><?php echo $result['ranksum_p'] ?></td>
        </tr>
      </table>
      <img src="<?php echo url_for('yeast-rls/plot')
        . '/type/result/filename/survival'.$result['id'].'.png' ?>" alt="">
    </td>
  </tr>
</table>


<h2>Composite Sets</h2>

<table class="result dataTable">
  <thead>
    <th>Sets</th>
    <th>References</th>
  </thead>
  <tr>
    <td>
      <?php foreach ($sets as $set): ?>
        <?php include_partial('setDetails', array('set'=>$set)) ?>

        <img src="<?php echo url_for('yeast-rls/plot')
        	. '/type/set/filename/histogram'.$set['id'].'.png' ?>" alt=""><br />
        <br />
        <hr style="color: #999999; margin:1em" />

      <?php endforeach ?>
    </td>
    <td>
      <?php foreach ($refSets as $refSet): ?>
        <?php include_partial('setDetails', array('set' => $refSet)) ?>

        <img src="<?php echo url_for('yeast-rls/plot')
        	. '/type/set/filename/histogram'.$refSet['id'].'.png' ?>" alt=""><br />
        <br />
        <hr style="color: #999999; margin:1em" />
      <?php endforeach ?>
    </td>
  </tr>
</table>
