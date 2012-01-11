<table class="set-view">
  <tr>
    <th style="width:12em">Set Name</th>
    <td>
      <a href="<?php echo url_for('yeast-rls/set').'?id='.$set['id'] ?>">
        <?php echo $set['name'] ?>
      </a>
    </td>
  </tr>
  <tr>
    <th>Experiment</th>
    <td>
      <a href="<?php echo url_for('yeast-rls/sets').'?experiment='.$set['experiment'] ?>">
        <?php echo $set['experiment'] ?>
      </a>
    </td>
  </tr>

  <?php if (!empty($set['yeast_strain_id'])): ?>
    <tr>
      <th>Strain Name</th>
      <td>
        <a href="<?php echo url_for('yeast/show').'?id='.$set['yeast_strain_id'] ?>">
          <?php echo $set['strain'] ?>
        </a>
      </td>
    </tr>
    <tr>
      <th style="padding-left:1em">Background</th>
      <td><?php echo $set['background'] ?></td>
    </tr>
    <tr>
      <th style="padding-left:1em">Mating Type</th>
      <td><?php echo $set['mating_type'] ?></td>
    </tr>
    <tr>
      <th style="padding-left:1em">Genotype</th>
      <td><?php echo $set['genotype'] ?></td>
    </tr>
  <?php endif ?>

  <tr>
    <th>Media</th>
    <td><?php echo $set['media'] ?></td>
  </tr>
  <tr>
    <th>Temperature (&deg;C)</th>
    <td><?php echo $set['temperature'] ?></td>
  </tr>
  <tr>
    <th>Lifespans Count</th>
    <td><?php echo $set['lifespan_count'] ?></td>
  </tr>
  <tr>
    <th>Lifespans Mean</th>
    <td><?php echo $set['lifespan_mean'] ?></td>
  </tr>
  <tr>
    <th>Lifespans Std Dev</th>
    <td><?php echo $set['lifespan_stdev'] ?></td>
  </tr>
</table>


<strong>Lifespans:</strong><br/>
<textarea cols="40" rows="5"
  ><?php echo join("\t", explode(',', $set['lifespans'])) ?></textarea><br />
