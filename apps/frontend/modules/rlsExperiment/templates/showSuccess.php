<?php include_partial('header') ?>

<h2>
  <?php echo link_to('RLS Experiments' , 'rlsExperiment/list') ?>
  >
  Experiment
  <?php if ($experiment['number']): ?>
    <?php echo $experiment['number'] ?>
  <?php else: ?>
    [<?php echo $experiment['status'] ?>]
  <?php endif ?>

  <?php if ($isExecutive || $isManager): ?>
    <?php echo link_to("Edit", 'rlsExperiment/edit?id=' . $experiment['id']) ?>
  <?php endif ?>
</h2>

<?php if ($isExecutive && $experiment['status'] == 'pending'): ?>
  <div class="right-sidebar">
    <strong>Executive Action Required:</strong>
    <br />
    <br />

    <p>This experiment has not been accepted into the dissection queue.
      Accepting the experiment will notify the experiment manager
      to create a template for it and begin dissection.
    </p>
    <br />

    <form action="<?php echo url_for('rlsExperiment/update') . '?id='.$experiment['id'] ?>"
          method="post">
      <label for="status">Status:</label>
      <br />
      <select name="status">
        <option>accepted</option>
        <option>rejected</option>
      </select>
      <br />
      <br />

      <label for="message">Message:</label>
      <textarea cols="30" rows="3" name="message"></textarea>
      <br />
      <br />

      <input name="workflowSubmit" type="submit" value="submit" />
    </form>
  </div>
<?php endif ?>

<table class="compact-view">
  <tr style="width:8em">
    <th>Status</th>
    <td><?php echo $experiment['status'] ?></td>
  </tr>
  <tr>
    <th>Facility</th>
    <td><?php echo $experiment['facility'] ?></td>
  </tr>
  <tr>
    <th>Number</th>
    <td><?php echo $experiment['number'] ?></td>
  </tr>
  <?php if ($hasReport || $hasCsv): ?>
    <tr>
      <th>Results</th>
      <td>
        <?php if ($hasReport): ?>
          <a href="<?php echo url_for('rlsExperiment/downloadReport') . '/filename/'
            . $experiment['number'] . '.xlsx' ?>"
            >Report (xlsx)</a>&nbsp;
        <?php endif ?>
        <?php if ($hasCsv): ?>
          <a href="<?php echo url_for('rlsExperiment/downloadCsv') . '/filename/'
            . $experiment['number'] . '.csv' ?>"
            >Database file (csv)</a>&nbsp;
        <?php endif ?>
      </td>
    </tr>
  <?php endif ?>
  <tr>
    <th>Description</th>
    <td><?php echo $experiment['description'] ?></td>
  </tr>
  <tr>
    <th>Requestor</th>
    <td><?php echo $experiment['requested_by'] ?></td>
  </tr>
  <tr>
    <th>Requested</th>
    <td><?php echo $experiment['requested_at'] ?></td>
  </tr>
  <tr>
    <th>Reviewed</th>
    <td><?php echo $experiment['reviewed_at'] ?></td>
  </tr>
  <tr>
    <th>Completed</th>
    <td><?php echo $experiment['completed_at'] ?></td>
  </tr>
</table>
<br />


<h2>Experiment Key Data</h2>
<?php if ($showKey): ?>
  <table class="dataTable">
    <thead>
      <tr>
        <th>id</th>
        <th>reference</th>
        <th>label</th>
        <th>strain</th>
        <th>media</th>
        <th>temperature</th>
        <th>cells</th>
        <th>background</th>
        <th>mating type</th>
        <th>short genotype</th>
				<th>full genotype</th>
        <th>freezer code</th>
      </tr>
    </thead>

    <?php foreach ($keyRows as $keyRow): ?>
      <tr>
        <td><?php echo $keyRow['id'] ?></td>
        <td><?php echo $keyRow['reference'] ?></td>
        <td><?php echo $keyRow['label'] ?></td>
        <td>
          <a href="<?php echo url_for('yeast/show').'?name='.$keyRow['strain'] ?>"
           ><?php echo $keyRow['strain'] ?></a>
        </td>
        <td><?php echo $keyRow['media'] ?></td>
        <td><?php echo $keyRow['temperature'] ?></td>
        <td><?php echo $keyRow['cells'] ?></td>
        <td><?php echo $keyRow['strain_background'] ?></td>
        <td><?php echo $keyRow['strain_mating_type'] ?></td>
        <td><?php echo $keyRow['strain_short_genotype'] ?></td>
        <td><?php echo $keyRow['strain_full_genotype'] ?></td>
        <td><?php echo $keyRow['strain_freezer_code'] ?></td>
      </tr>
    <?php endforeach ?>
  </table>
<?php else: ?>
  <p>Key data is visible only to requestor, manager, and executive until experiment
    is completed.</p>
<?php endif ?>
